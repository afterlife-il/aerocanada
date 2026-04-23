# AeroCanada v2 — Migration Guide

## Quick Start

Access the new v2 interface at:
```
https://aerocanada-industries.com/pages/v2/index.php?page=dashboard
```

The v2 system runs **alongside** the existing v1 code. Both share the same database. No data migration needed.

---

## Architecture Overview

```
pages/v2/
├── bootstrap.php          # App bootstrap (session, config, autoloader)
├── config.php             # All configuration (DB, mail, security)
├── autoload.php           # PSR-4 autoloader
├── index.php              # Main entry point / router
│
├── core/                  # Framework core (7 files)
│   ├── Database.php       # PDO wrapper with prepared statements
│   ├── Auth.php           # Authentication, roles, rate limiting
│   ├── CSRF.php           # CSRF token protection
│   ├── Router.php         # URL routing
│   ├── Module.php         # Base CRUD module class
│   ├── View.php           # Template rendering engine
│   └── FileUpload.php     # Secure file upload handler
│
├── modules/               # Business logic (10 modules)
│   ├── Parts/Parts.php
│   ├── Companies/Companies.php
│   ├── Stock/Stock.php
│   ├── RFQ/RFQ.php
│   ├── Quotations/Quotations.php
│   ├── Suppliers/Suppliers.php
│   ├── Invoices/Invoices.php       # NEW
│   ├── Shipping/Shipping.php       # NEW
│   ├── Dashboard/Dashboard.php
│   └── Users/Users.php
│
├── api/                   # JSON API endpoints (10 files)
│   ├── parts.php
│   ├── companies.php
│   ├── stock.php
│   ├── rfq.php
│   ├── quotations.php
│   ├── suppliers.php
│   ├── invoices.php        # NEW
│   ├── shipping.php        # NEW
│   ├── dashboard.php
│   └── users.php
│
├── templates/
│   ├── layouts/
│   │   ├── main.php        # Main app layout (sidebar + navbar)
│   │   └── auth.php        # Login page layout
│   └── views/              # Page templates (11 modules)
│       ├── dashboard/index.php
│       ├── parts/index.php
│       ├── companies/index.php
│       ├── stock/index.php
│       ├── rfq/index.php
│       ├── quotations/index.php
│       ├── suppliers/index.php
│       ├── invoices/index.php
│       ├── shipping/index.php
│       ├── settings/index.php
│       └── users/index.php
│
└── assets/
    ├── css/aerocanada.css  # Aviation-themed CSS (600+ lines)
    └── js/aerocanada.js    # App JavaScript (toast, datatables, CSRF)
```

---

## What's New in v2

### Security Improvements
- **PDO prepared statements** everywhere (no more SQL injection)
- **bcrypt password hashing** (auto-upgrades plain-text passwords on login)
- **CSRF tokens** on all forms
- **Rate limiting** (5 failed logins = 15 min lockout)
- **Session security** (HTTPOnly, SameSite, secure cookies, session timeout)
- **Secure file uploads** (type validation, size limits, name sanitization)
- **XSS protection** (View::e() helper for HTML escaping)

### New Modules
- **Invoices** — Proforma invoices, commercial invoices, credit notes
- **Shipping** — Shipment tracking, delivery notes, status pipeline
- **Dashboard** — Real-time stats, charts, activity feed

### UI Improvements
- **Bootstrap 5.3** (upgraded from Bootstrap 3)
- **Font Awesome 6** icons
- **Modern DataTables** with Bootstrap 5 theme
- **Aviation-themed design** (AeroCanada red #BE0831 + Navy #1B2A4A)
- **Responsive sidebar** with collapsible navigation
- **Offcanvas detail panels** (company details slide in from right)
- **Toast notifications** for success/error feedback
- **Status badges** color-coded by state

### Architecture
- **Modular design** — each feature is an independent module
- **MVC separation** — logic in modules, SQL in Database class, HTML in templates
- **API-first** — all data operations through JSON API endpoints
- **Easy extensibility** — add a new module in 3 steps (see below)

---

## How to Add a New Module

1. Create module class: `modules/MyModule/MyModule.php`
2. Create API endpoint: `api/mymodule.php`
3. Create view: `templates/views/mymodule/index.php`
4. Add route in `index.php`
5. Add menu item in `templates/layouts/main.php`

---

## Backward Compatibility

The v2 system preserves:
- Same session variables (`conectroy`, `nom_utilisateur`, `id_utilisateur`, `statut`)
- Same database tables (no schema changes for existing tables)
- Same `mysql2_query()` function available via legacy bridge
- v1 pages continue to work unchanged

### New Database Tables (auto-created)
- `tbl_Invoices` + `tbl_Invoice_Items`
- `tbl_Shipping` + `tbl_Shipping_Items`
- `tbl_login_attempts` (rate limiting)

---

## Deployment Steps

1. Upload the entire `v2/` folder to `pages/v2/` on the server
2. Ensure PHP 7.4+ is installed (PHP 8.1+ recommended)
3. The PDO MySQL extension must be enabled
4. Access `pages/v2/index.php` to verify
5. Passwords will be automatically upgraded to bcrypt on next login

### Environment Variables (Optional)
Set these for production instead of using defaults in config.php:
```
DB_HOST=localhost
DB_PORT=3306
DB_NAME=aerocanada
DB_USER=aerocanada-indus
DB_PASS=your_secure_password
SMTP_HOST=smtp.example.com
SMTP_USER=user
SMTP_PASS=pass
```

---

## File Count Summary

| Category | Files |
|----------|-------|
| Core framework | 7 |
| Module classes | 10 |
| API endpoints | 10 |
| View templates | 11 |
| Layout templates | 2 |
| Assets (CSS/JS) | 2 |
| Config/bootstrap | 3 |
| Documentation | 2 |
| **Total** | **47** |
