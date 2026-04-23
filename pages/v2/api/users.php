<?php
/**
 * AeroCanada v2 — Users API
 */
require_once __DIR__ . '/../bootstrap.php';
use AeroCanada\Core\{Auth, CSRF, Database};

Auth::requireRole('SuperAdmin');
$db = Database::getInstance();
$action = $_REQUEST['action'] ?? '';
header('Content-Type: application/json; charset=utf-8');

try {
    switch ($action) {
        case 'create':
            CSRF::verify();
            $name = trim($_POST['Employee_Name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $pw = $_POST['pw'] ?? '';

            if (empty($name) || empty($email) || empty($pw)) {
                throw new \Exception('Name, email, and password are required');
            }
            if (strlen($pw) < 8) {
                throw new \Exception('Password must be at least 8 characters');
            }

            // Check duplicate email
            $exists = $db->fetchColumn('SELECT COUNT(*) FROM tbl_Employee WHERE email = ?', [$email]);
            if ($exists) throw new \Exception('Email already exists');

            $auth = new Auth();
            $id = $db->insert('tbl_Employee', [
                'Employee_Name' => $name,
                'email'         => $email,
                'pw'            => $auth->hashPassword($pw),
                'statut'        => $_POST['statut'] ?? 'User',
                'position'      => $_POST['position'] ?? '',
                'tel'           => $_POST['tel'] ?? '',
                'mobile'        => $_POST['mobile'] ?? '',
                'skype'         => $_POST['skype'] ?? '',
            ]);

            echo json_encode(['ok' => true, 'id' => $id]);
            break;

        case 'update':
            CSRF::verify();
            $id = (int)($_POST['Employee_ID'] ?? 0);
            $data = [
                'Employee_Name' => $_POST['Employee_Name'] ?? '',
                'email'         => $_POST['email'] ?? '',
                'statut'        => $_POST['statut'] ?? '',
                'position'      => $_POST['position'] ?? '',
                'tel'           => $_POST['tel'] ?? '',
                'mobile'        => $_POST['mobile'] ?? '',
            ];
            if (!empty($_POST['pw'])) {
                $auth = new Auth();
                $data['pw'] = $auth->hashPassword($_POST['pw']);
            }
            $db->update('tbl_Employee', $data, 'Employee_ID = ?', [$id]);
            echo json_encode(['ok' => true]);
            break;

        case 'delete':
            CSRF::verify();
            $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
            if ($id == Auth::userId()) throw new \Exception('Cannot delete yourself');
            $db->delete('tbl_Employee', 'Employee_ID = ?', [$id]);
            echo json_encode(['ok' => true]);
            break;

        case 'change_password':
            CSRF::verify();
            $auth = new Auth();
            $userId = Auth::userId();
            $ok = $auth->changePassword($userId, $_POST['old_password'] ?? '', $_POST['new_password'] ?? '');
            if (!$ok) throw new \Exception('Current password is incorrect');
            echo json_encode(['ok' => true, 'message' => 'Password changed successfully']);
            break;

        default:
            echo json_encode(['ok' => false, 'error' => 'Unknown action']);
    }
} catch (\Exception $e) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
