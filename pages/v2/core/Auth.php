<?php
/**
 * AeroCanada v2 — Secure Authentication
 * - Password hashing (bcrypt)
 * - Session security (regeneration, secure cookies)
 * - Role-based access control
 * - Login rate limiting
 * - Audit trail
 */

namespace AeroCanada\Core;

class Auth
{
    private Database $db;

    // Roles hierarchy (higher = more privileges)
    public const ROLE_USER       = 'User';
    public const ROLE_SALES      = 'Sales';
    public const ROLE_MANAGER    = 'Manager';
    public const ROLE_ADMIN      = 'Admin';
    public const ROLE_SUPERADMIN = 'SuperAdmin';

    private const MAX_LOGIN_ATTEMPTS = 5;
    private const LOCKOUT_MINUTES    = 15;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Attempt login. Returns user array on success, null on failure.
     */
    public function login(string $email, string $password): ?array
    {
        $email = trim(strtolower($email));

        // Rate limiting check
        if ($this->isLockedOut($email)) {
            return null;
        }

        // Fetch user
        $user = $this->db->fetch(
            'SELECT * FROM tbl_Employee WHERE LOWER(email) = ?',
            [$email]
        );

        if (!$user) {
            $this->recordFailedAttempt($email);
            return null;
        }

        // Password verification — support both legacy plaintext and bcrypt
        $passwordValid = false;

        if (password_get_info($user['pw'])['algo'] !== null) {
            // Modern bcrypt hash
            $passwordValid = password_verify($password, $user['pw']);
        } else {
            // Legacy plaintext — verify and upgrade to bcrypt
            $passwordValid = ($user['pw'] === $password);
            if ($passwordValid) {
                $this->upgradePassword($user['Employee_ID'], $password);
            }
        }

        if (!$passwordValid) {
            $this->recordFailedAttempt($email);
            return null;
        }

        // Clear failed attempts
        $this->clearFailedAttempts($email);

        // Regenerate session ID for security
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }

        // Set session — preserve backward compatibility with old variable names
        $_SESSION['conectroy']       = 'parfait';
        $_SESSION['nom_utilisateur'] = $user['Employee_Name'];
        $_SESSION['id_utilisateur']  = $user['Employee_ID'];
        $_SESSION['statut']          = $user['statut'];
        $_SESSION['leftmenu']        = 'open';
        $_SESSION['user_email']      = $user['email'];
        $_SESSION['last_activity']   = time();

        // Audit log
        $this->logConnection($user['Employee_ID']);

        return $user;
    }

    /**
     * Logout user.
     */
    public function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }

        session_destroy();
    }

    /**
     * Check if user is authenticated.
     */
    public static function check(): bool
    {
        return isset($_SESSION['conectroy']) && $_SESSION['conectroy'] === 'parfait';
    }

    /**
     * Require authentication — redirect or 401 for AJAX.
     */
    public static function requireAuth(): void
    {
        if (!self::check()) {
            if (self::isAjax()) {
                http_response_code(401);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['ok' => false, 'error' => 'AUTH_REQUIRED']);
                exit;
            }
            $loginUrl = self::v2BaseUrl() . '/index.php?page=login&url=' . urlencode($_SERVER['REQUEST_URI'] ?? '');
            header('Location: ' . $loginUrl);
            exit;
        }

        // Session timeout (1 hour)
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > 3600) {
            session_unset();
            session_destroy();
            header('Location: ' . self::v2BaseUrl() . '/index.php?page=login&timeout=1');
            exit;
        }
        $_SESSION['last_activity'] = time();
    }

    /**
     * Require specific role(s).
     */
    public static function requireRole($roles): void
    {
        self::requireAuth();

        if (is_string($roles)) {
            $roles = [$roles];
        }
        // SuperAdmin always passes
        $roles[] = self::ROLE_SUPERADMIN;

        if (!in_array($_SESSION['statut'] ?? '', $roles, true)) {
            http_response_code(403);
            die('Access denied — insufficient privileges.');
        }
    }

    /**
     * Check if current user has a specific role.
     */
    public static function hasRole(string $role): bool
    {
        if (($_SESSION['statut'] ?? '') === self::ROLE_SUPERADMIN) {
            return true;
        }
        return ($_SESSION['statut'] ?? '') === $role;
    }

    /**
     * Current user ID.
     */
    public static function userId(): ?int
    {
        return isset($_SESSION['id_utilisateur']) ? (int)$_SESSION['id_utilisateur'] : null;
    }

    /**
     * Current user name.
     */
    public static function userName(): string
    {
        return $_SESSION['nom_utilisateur'] ?? 'Unknown';
    }

    /**
     * Hash a password using bcrypt.
     */
    public function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    /**
     * Change user password (requires old password verification).
     */
    public function changePassword(int $userId, string $oldPassword, string $newPassword): bool
    {
        $user = $this->db->fetch(
            'SELECT pw FROM tbl_Employee WHERE Employee_ID = ?',
            [$userId]
        );

        if (!$user) {
            return false;
        }

        // Verify old password
        $valid = false;
        if (password_get_info($user['pw'])['algo'] !== null) {
            $valid = password_verify($oldPassword, $user['pw']);
        } else {
            $valid = ($user['pw'] === $oldPassword);
        }

        if (!$valid) {
            return false;
        }

        $hash = $this->hashPassword($newPassword);
        $this->db->update('tbl_Employee', ['pw' => $hash], 'Employee_ID = ?', [$userId]);
        return true;
    }

    /**
     * Get the base URL path to the v2/ directory (no trailing slash).
     * Works regardless of which script is running (index.php, api/*, etc.)
     */
    public static function v2BaseUrl(): string
    {
        // __DIR__ is always .../v2/core, so v2/ is one level up
        $v2Dir  = dirname(__DIR__); // absolute path to v2/
        $docRoot = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/\\');

        if ($docRoot && strpos($v2Dir, $docRoot) === 0) {
            return str_replace('\\', '/', substr($v2Dir, strlen($docRoot)));
        }

        // Fallback: parse from SCRIPT_NAME
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        $pos = strpos($script, '/v2/');
        if ($pos !== false) {
            return substr($script, 0, $pos + 3); // include /v2
        }

        return '/pages/v2'; // safe default
    }

    // ---- Private helpers ----

    private function upgradePassword(int $userId, string $plaintext): void
    {
        $hash = $this->hashPassword($plaintext);
        $this->db->update('tbl_Employee', ['pw' => $hash], 'Employee_ID = ?', [$userId]);
    }

    private function logConnection(int $userId): void
    {
        $this->db->insert('tbl_connection', [
            'Employee_ID'     => $userId,
            'ip_address'      => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
            'connection_date' => date('Y-m-d H:i:s'),
            'session_id'      => session_id(),
        ]);
    }

    private function ensureLoginAttemptsTable(): void
    {
        static $checked = false;
        if ($checked) return;
        $this->db->pdo()->exec('
            CREATE TABLE IF NOT EXISTS tbl_login_attempts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(255) NOT NULL,
                ip_address VARCHAR(45) NOT NULL,
                attempt_time DATETIME NOT NULL,
                INDEX idx_email_time (email, attempt_time)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ');
        $checked = true;
    }

    private function isLockedOut(string $email): bool
    {
        $this->ensureLoginAttemptsTable();
        $cutoff = date('Y-m-d H:i:s', time() - (self::LOCKOUT_MINUTES * 60));
        $count  = $this->db->fetchColumn(
            'SELECT COUNT(*) FROM tbl_login_attempts WHERE email = ? AND attempt_time > ?',
            [$email, $cutoff]
        );
        return $count >= self::MAX_LOGIN_ATTEMPTS;
    }

    private function recordFailedAttempt(string $email): void
    {
        $this->ensureLoginAttemptsTable();

        $this->db->insert('tbl_login_attempts', [
            'email'        => $email,
            'ip_address'   => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
            'attempt_time' => date('Y-m-d H:i:s'),
        ]);
    }

    private function clearFailedAttempts(string $email): void
    {
        try {
            $this->db->delete('tbl_login_attempts', 'email = ?', [$email]);
        } catch (\Exception $e) {
            // Table might not exist yet
        }
    }

    private static function isAjax(): bool
    {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            return true;
        }
        if (!empty($_SERVER['HTTP_ACCEPT']) &&
            stripos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
            return true;
        }
        return false;
    }
}
