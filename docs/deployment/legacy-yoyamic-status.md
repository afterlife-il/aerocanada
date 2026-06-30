# Legacy Yoyamic Deployment Status

Last updated: 2026-06-30

This inventory is report-based. Reverify live staging hashes before any future deployment.

## Staging Path

`/var/www/vhosts/aerocanada-industries.com/httpdocs/yoyamic`

## Reported Deployments

### 2026-06-18 Stock Phase 1A

Reported deployed files:

- `pages/stockexternaldata.php`
- `pages/Part-Nbr.php`
- `pages/rfq_line_stock.php`

Report: `STOCK_PHASE1A_DEPLOY_REPORT.md`

### 2026-06-24 Stock Ownership Sprint

Reported deployed files:

- `pages/detailcompany.php`
- `pages/Part-Nbr.php`
- `classes/stock.class.php`
- `pages/ajout_stock.php`
- `pages/valid_ajout_stock.php`
- `pages/stock.php`
- `pages/stockdata.php`
- `pages/change_stock.php`
- `pages/modif_external_stock.php`

Report: `STOCK_OWNERSHIP_SPRINT_DEPLOY_REPORT.md`

### 2026-06-24 Stock List Actions

Reported deployed files:

- `pages/stock.php`
- `pages/stockdata.php`

Report: `ACI770_STOCK_LIST_ACTIONS_DEPLOY_REPORT.md`

## Important Caveat

The git working tree remains dirty against the repository baseline. Some changes shown by `git diff` were already deployed to staging according to reports, while other changes are local only. Before deploying any Yoyamic file, compare source and staging hashes and create backups.

## Known Verification Limits

Authenticated browser verification of Yoyamic staging was previously blocked by login/session requirements.
