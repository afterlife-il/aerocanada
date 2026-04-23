<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <?= \AeroCanada\Core\CSRF::meta() ?>

    <title><?= htmlspecialchars($__title ?? 'AeroCanada ERP') ?> - AeroCanada Industries</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <!-- DataTables + Bootstrap 5 Theme -->
    <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <!-- AeroCanada Theme -->
    <link href="/pages/v2/assets/css/aerocanada.css" rel="stylesheet">
</head>
<?php
    $currentPage = $_GET['page'] ?? 'dashboard';
    $userName    = $_SESSION['nom_utilisateur'] ?? 'User';
    $userRole    = $_SESSION['statut'] ?? 'User';
    $userInitials = strtoupper(substr($userName, 0, 1));
    $parts = explode(' ', $userName);
    if (count($parts) > 1) {
        $userInitials .= strtoupper(substr($parts[1], 0, 1));
    }
    $baseUrl = '/pages/v2/index.php?page=';
?>
<body>

    <div class="aci-wrapper">

        <!-- ============ SIDEBAR ============ -->
        <aside class="aci-sidebar" id="aci-sidebar">

            <!-- Brand -->
            <div class="aci-sidebar-brand">
                <div class="aci-sidebar-brand-icon">
                    <i class="fa-solid fa-plane"></i>
                </div>
                <div class="aci-sidebar-brand-text">
                    <span class="aero">AERO</span><span class="canada">CANADA</span>
                </div>
            </div>

            <!-- Sidebar Search -->
            <div class="aci-sidebar-search">
                <div class="position-relative">
                    <i class="fa-solid fa-magnifying-glass search-icon"></i>
                    <input type="text" class="form-control" placeholder="Search menu..." id="sidebar-search">
                </div>
            </div>

            <!-- Navigation -->
            <nav class="aci-sidebar-nav" id="sidebar-nav">

                <!-- DASHBOARD -->
                <a href="<?= $baseUrl ?>dashboard" class="aci-nav-link <?= $currentPage === 'dashboard' ? 'active' : '' ?>">
                    <i class="fa-solid fa-gauge-high"></i>
                    <span class="aci-sidebar-text">Dashboard</span>
                </a>

                <!-- PARTS -->
                <div class="aci-nav-section-title">
                    <span>Parts & Stock</span>
                </div>
                <a href="<?= $baseUrl ?>parts" class="aci-nav-link <?= $currentPage === 'parts' ? 'active' : '' ?>">
                    <i class="fa-solid fa-cogs"></i>
                    <span class="aci-sidebar-text">Parts Catalog</span>
                </a>
                <a href="<?= $baseUrl ?>stock" class="aci-nav-link <?= $currentPage === 'stock' ? 'active' : '' ?>">
                    <i class="fa-solid fa-warehouse"></i>
                    <span class="aci-sidebar-text">Inventory</span>
                </a>

                <!-- COMPANIES -->
                <div class="aci-nav-section-title">
                    <span>CRM</span>
                </div>
                <a href="<?= $baseUrl ?>companies" class="aci-nav-link <?= $currentPage === 'companies' ? 'active' : '' ?>">
                    <i class="fa-solid fa-building"></i>
                    <span class="aci-sidebar-text">Companies</span>
                </a>

                <!-- RFQ & QUOTES -->
                <div class="aci-nav-section-title">
                    <span>Sales</span>
                </div>
                <a href="<?= $baseUrl ?>rfq" class="aci-nav-link <?= $currentPage === 'rfq' ? 'active' : '' ?>">
                    <i class="fa-solid fa-file-lines"></i>
                    <span class="aci-sidebar-text">RFQs</span>
                </a>
                <a href="<?= $baseUrl ?>quotations" class="aci-nav-link <?= $currentPage === 'quotations' ? 'active' : '' ?>">
                    <i class="fa-solid fa-file-invoice"></i>
                    <span class="aci-sidebar-text">Customer Quotes</span>
                </a>
                <a href="<?= $baseUrl ?>suppliers" class="aci-nav-link <?= $currentPage === 'suppliers' ? 'active' : '' ?>">
                    <i class="fa-solid fa-file-contract"></i>
                    <span class="aci-sidebar-text">Supplier Quotes</span>
                </a>

                <!-- INVOICES -->
                <div class="aci-nav-section-title">
                    <span>Finance</span>
                </div>
                <a href="<?= $baseUrl ?>invoices" class="aci-nav-link <?= $currentPage === 'invoices' ? 'active' : '' ?>">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                    <span class="aci-sidebar-text">Invoices</span>
                </a>

                <!-- SHIPPING -->
                <a href="<?= $baseUrl ?>shipping" class="aci-nav-link <?= $currentPage === 'shipping' ? 'active' : '' ?>">
                    <i class="fa-solid fa-truck-plane"></i>
                    <span class="aci-sidebar-text">Shipping</span>
                </a>

                <!-- SETTINGS (Admin/SuperAdmin) -->
                <?php if (in_array($userRole, ['Admin', 'SuperAdmin'], true)): ?>
                <div class="aci-nav-section-title">
                    <span>Admin</span>
                </div>
                <a href="<?= $baseUrl ?>settings" class="aci-nav-link <?= $currentPage === 'settings' ? 'active' : '' ?>">
                    <i class="fa-solid fa-sliders"></i>
                    <span class="aci-sidebar-text">Settings</span>
                </a>
                <a href="<?= $baseUrl ?>users" class="aci-nav-link <?= $currentPage === 'users' ? 'active' : '' ?>">
                    <i class="fa-solid fa-users-gear"></i>
                    <span class="aci-sidebar-text">Users</span>
                </a>
                <?php endif; ?>

            </nav>

            <!-- Sidebar Footer -->
            <div class="aci-sidebar-footer">
                <span class="aci-sidebar-text">ERP v2.0</span>
            </div>
        </aside>

        <!-- ============ MAIN CONTENT AREA ============ -->
        <div class="aci-content-wrapper">

            <!-- Mobile overlay -->
            <div class="aci-mobile-overlay" id="mobile-overlay"></div>

            <!-- Top Navbar -->
            <header class="aci-navbar">
                <button class="aci-navbar-toggle" id="sidebar-toggle" aria-label="Toggle sidebar">
                    <i class="fa-solid fa-bars"></i>
                </button>

                <div class="aci-breadcrumb">
                    <a href="<?= $baseUrl ?>dashboard"><i class="fa-solid fa-house"></i></a>
                    <span class="separator">/</span>
                    <span class="current"><?= htmlspecialchars($__title ?? 'Page') ?></span>
                </div>

                <div class="aci-navbar-actions">
                    <!-- Global Search -->
                    <div class="aci-navbar-search d-none d-md-block">
                        <i class="fa-solid fa-magnifying-glass search-icon"></i>
                        <input type="text" class="form-control" placeholder="Search parts, companies, RFQs..." id="global-search">
                    </div>

                    <!-- Notifications -->
                    <button class="aci-navbar-icon-btn" id="notifications-btn" title="Notifications">
                        <i class="fa-solid fa-bell"></i>
                    </button>

                    <!-- User Dropdown -->
                    <div class="dropdown">
                        <button class="aci-user-dropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="aci-user-avatar">
                                <?= $userInitials ?>
                            </div>
                            <div class="aci-user-info d-none d-lg-block">
                                <div class="aci-user-name"><?= htmlspecialchars($userName) ?></div>
                                <div class="aci-user-role"><?= htmlspecialchars($userRole) ?></div>
                            </div>
                            <i class="fa-solid fa-chevron-down d-none d-lg-inline" style="font-size: 0.65rem; color: var(--aci-gray-400);"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li class="dropdown-header">
                                <strong><?= htmlspecialchars($userName) ?></strong><br>
                                <small class="text-muted"><?= htmlspecialchars($userRole) ?></small>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="<?= $baseUrl ?>logout">
                                    <i class="fa-solid fa-right-from-bracket me-2"></i> Logout
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="aci-main">
                <?= $__content ?? '' ?>
            </main>

            <!-- Footer -->
            <footer class="aci-footer">
                &copy; <?= date('Y') ?> AeroCanada Industries 770 Inc. &mdash; ERP v2.0
            </footer>

        </div>
    </div>

    <!-- Loading Overlay -->
    <div class="aci-loading-overlay" id="aci-loading">
        <div class="aci-spinner"></div>
    </div>

    <!-- Toast Container -->
    <div class="aci-toast-container" id="aci-toasts"></div>

    <!-- jQuery 3.7 -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Bootstrap 5.3 Bundle (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- DataTables + Bootstrap 5 -->
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>

    <!-- AeroCanada JS -->
    <script src="/pages/v2/assets/js/aerocanada.js"></script>

    <?php if (!empty($__page_scripts)): ?>
        <?php foreach ($__page_scripts as $script): ?>
            <script src="<?= htmlspecialchars($script) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if (!empty($__inline_js)): ?>
        <script><?= $__inline_js ?></script>
    <?php endif; ?>
</body>
</html>
