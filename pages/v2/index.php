<?php
/**
 * AeroCanada v2 — Main Entry Point
 * Routes all v2 requests to the appropriate module view.
 *
 * Usage: v2/index.php?page=dashboard (or parts, companies, stock, rfq, etc.)
 * Or via direct page files: v2/dashboard.php, v2/parts.php, etc.
 */

require_once __DIR__ . '/bootstrap.php';

use AeroCanada\Core\{Auth, View, Database, CSRF};

// Check page parameter
$page = $_GET['page'] ?? 'dashboard';

// Auth check (except login/logout)
if (!in_array($page, ['login', 'logout'], true)) {
    Auth::requireAuth();
}

// Load common data for views
$db = Database::getInstance();

$commonData = [
    'user_name'  => Auth::userName(),
    'user_role'  => $_SESSION['statut'] ?? '',
    'user_id'    => Auth::userId(),
    'menu_state' => $_SESSION['leftmenu'] ?? 'open',
];

// Route to view
switch ($page) {
    case 'dashboard':
        View::render('dashboard.index', array_merge($commonData, [
            'title' => 'Dashboard',
        ]));
        break;

    case 'parts':
        $aircrafts  = $db->fetchAll('SELECT * FROM tbl_Aircraft ORDER BY Fld_AC_Model');
        $currencies = $db->fetchAll('SELECT * FROM tbl_Currency ORDER BY Fld_Currency_Text');
        View::render('parts.index', array_merge($commonData, [
            'title'      => 'Parts Catalog',
            'aircrafts'  => $aircrafts,
            'currencies' => $currencies,
        ]));
        break;

    case 'companies':
        $employees    = $db->fetchAll('SELECT Employee_ID, Employee_Name FROM tbl_Employee ORDER BY Employee_Name');
        $companyTypes = $db->fetchAll('SELECT * FROM tbl_Company_Type ORDER BY Fld_Company_Type_Text');
        $addressTypes = $db->fetchAll('SELECT * FROM tbl_Division ORDER BY Fld_Division_Text');
        $currencies   = $db->fetchAll('SELECT * FROM tbl_Currency ORDER BY Fld_Currency_Text');
        $paymentTerms = $db->fetchAll('SELECT * FROM tbl_Payment ORDER BY Fld_Payment_Text');
        View::render('companies.index', array_merge($commonData, [
            'title'        => 'Companies',
            'employees'    => $employees,
            'companyTypes' => $companyTypes,
            'addressTypes' => $addressTypes,
            'currencies'   => $currencies,
            'paymentTerms' => $paymentTerms,
        ]));
        break;

    case 'stock':
        View::render('stock.index', array_merge($commonData, [
            'title' => 'Inventory',
        ]));
        break;

    case 'rfq':
        $employees = $db->fetchAll('SELECT Employee_ID, Employee_Name FROM tbl_Employee ORDER BY Employee_Name');
        View::render('rfq.index', array_merge($commonData, [
            'title'     => 'Request for Quotations',
            'employees' => $employees,
        ]));
        break;

    case 'quotations':
        View::render('quotations.index', array_merge($commonData, [
            'title' => 'Customer Quotations',
        ]));
        break;

    case 'suppliers':
        View::render('suppliers.index', array_merge($commonData, [
            'title' => 'Supplier Quotes',
        ]));
        break;

    case 'invoices':
        View::render('invoices.index', array_merge($commonData, [
            'title' => 'Invoices',
        ]));
        break;

    case 'shipping':
        View::render('shipping.index', array_merge($commonData, [
            'title' => 'Shipping & Deliveries',
        ]));
        break;

    case 'login':
        $loginData = ['title' => 'Sign In', 'error' => '', 'old_email' => ''];

        // Handle timeout message
        if (!empty($_GET['timeout'])) {
            $loginData['error'] = 'Your session has expired. Please sign in again.';
        }

        // If already authenticated, redirect to dashboard
        if (Auth::check()) {
            header('Location: /pages/v2/index.php?page=dashboard');
            exit;
        }

        // Handle POST (login attempt)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::verify();
            $email    = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $loginData['old_email'] = $email;

            if (empty($email) || empty($password)) {
                $loginData['error'] = 'Please enter your email and password.';
            } else {
                $auth = new Auth();
                $user = $auth->login($email, $password);

                if ($user) {
                    // Login successful — redirect to requested page or dashboard
                    $redirect = $_GET['url'] ?? $_POST['url'] ?? '/pages/v2/index.php?page=dashboard';
                    // Sanitize redirect URL (must be local)
                    if (strpos($redirect, '/') !== 0) {
                        $redirect = '/pages/v2/index.php?page=dashboard';
                    }
                    header('Location: ' . $redirect);
                    exit;
                } else {
                    $loginData['error'] = 'Invalid email or password. Please try again.';
                }
            }
        }

        View::render('auth.login', $loginData, 'auth');
        break;

    case 'logout':
        $auth = new Auth();
        $auth->logout();
        header('Location: /pages/v2/index.php?page=login');
        exit;

    default:
        http_response_code(404);
        echo '<h1>Page not found</h1><p><a href="?page=dashboard">Go to Dashboard</a></p>';
}
