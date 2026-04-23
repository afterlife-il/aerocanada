<?php
/** Settings View (SuperAdmin only) */
use AeroCanada\Core\{View, Auth, CSRF};
?>

<div class="mb-4">
    <h1 class="h3 mb-0">Settings</h1>
    <p class="text-muted">System configuration and reference data</p>
</div>

<div class="row g-4">
    <!-- Reference Data Cards -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                        <i class="fa-solid fa-plane text-primary"></i>
                    </div>
                    <h5 class="mb-0">Aircraft Types</h5>
                </div>
                <p class="text-muted small">Manage aircraft models and series</p>
                <a href="aircrafts.php" class="btn btn-outline-primary btn-sm">Manage</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                        <i class="fa-solid fa-dollar-sign text-success"></i>
                    </div>
                    <h5 class="mb-0">Currencies</h5>
                </div>
                <p class="text-muted small">Manage currency codes and rates</p>
                <a href="currency.php" class="btn btn-outline-success btn-sm">Manage</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3">
                        <i class="fa-solid fa-building text-warning"></i>
                    </div>
                    <h5 class="mb-0">Company Types</h5>
                </div>
                <p class="text-muted small">Supplier, customer, MRO, etc.</p>
                <a href="company_type.php" class="btn btn-outline-warning btn-sm">Manage</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="rounded-circle bg-info bg-opacity-10 p-3 me-3">
                        <i class="fa-solid fa-map-marker-alt text-info"></i>
                    </div>
                    <h5 class="mb-0">Address Types</h5>
                </div>
                <p class="text-muted small">Billing, shipping, warehouse, etc.</p>
                <a href="address_type.php" class="btn btn-outline-info btn-sm">Manage</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="rounded-circle bg-danger bg-opacity-10 p-3 me-3">
                        <i class="fa-solid fa-tags text-danger"></i>
                    </div>
                    <h5 class="mb-0">RFQ Settings</h5>
                </div>
                <p class="text-muted small">Types, priorities, terms, conditions</p>
                <div class="btn-group btn-group-sm">
                    <a href="rfq_type.php" class="btn btn-outline-danger">Types</a>
                    <a href="rfq_priority.php" class="btn btn-outline-danger">Priorities</a>
                    <a href="rfq_terms.php" class="btn btn-outline-danger">Terms</a>
                    <a href="rfq_conditions.php" class="btn btn-outline-danger">Conditions</a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="rounded-circle bg-secondary bg-opacity-10 p-3 me-3">
                        <i class="fa-solid fa-truck text-secondary"></i>
                    </div>
                    <h5 class="mb-0">Shippers</h5>
                </div>
                <p class="text-muted small">Shipping companies and forwarders</p>
                <a href="shippers.php" class="btn btn-outline-secondary btn-sm">Manage</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="rounded-circle bg-dark bg-opacity-10 p-3 me-3">
                        <i class="fa-solid fa-certificate text-dark"></i>
                    </div>
                    <h5 class="mb-0">Release Codes</h5>
                </div>
                <p class="text-muted small">FAA, EASA release certificates</p>
                <a href="release.php" class="btn btn-outline-dark btn-sm">Manage</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                        <i class="fa-solid fa-users text-primary"></i>
                    </div>
                    <h5 class="mb-0">Users</h5>
                </div>
                <p class="text-muted small">Manage user accounts and roles</p>
                <a href="v2/index.php?page=users" class="btn btn-outline-primary btn-sm">Manage</a>
            </div>
        </div>
    </div>
</div>
