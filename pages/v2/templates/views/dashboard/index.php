<?php
/**
 * Dashboard View - AeroCanada ERP v2
 *
 * Expected variables:
 *   $stats - associative array with keys:
 *     total_parts, active_companies, stock_items, open_rfqs, pending_quotes, monthly_revenue
 *     Each may include: value, trend_pct, trend_dir ('up'|'down')
 *   $recent_activity - array of activity items
 *   $top_suppliers   - array of top supplier rows
 */

$s = $stats ?? [];
$userName = $_SESSION['aci_user_name'] ?? 'User';
$firstName = explode(' ', $userName)[0];
?>

<!-- Welcome Banner -->
<div class="aci-welcome-banner">
    <h2>Welcome back, <?= htmlspecialchars($firstName) ?></h2>
    <p><i class="fa-regular fa-calendar me-1"></i> <?= date('l, F j, Y') ?> &mdash; AeroCanada Industries ERP</p>
</div>

<!-- Row 1: Stat Cards -->
<div class="row g-3 mb-4">

    <!-- Total Parts -->
    <div class="col-xl-2 col-lg-4 col-sm-6">
        <div class="aci-stat-card stat-parts">
            <div class="d-flex align-items-start justify-content-between">
                <div>
                    <div class="stat-label">Total Parts</div>
                    <div class="stat-value"><?= number_format($s['total_parts']['value'] ?? 0) ?></div>
                    <?php if (!empty($s['total_parts']['trend_pct'])): ?>
                        <span class="stat-trend <?= $s['total_parts']['trend_dir'] ?? 'up' ?>">
                            <i class="fa-solid fa-arrow-<?= $s['total_parts']['trend_dir'] ?? 'up' ?>"></i>
                            <?= $s['total_parts']['trend_pct'] ?>%
                        </span>
                    <?php endif; ?>
                </div>
                <div class="stat-icon">
                    <i class="fa-solid fa-cogs"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Companies -->
    <div class="col-xl-2 col-lg-4 col-sm-6">
        <div class="aci-stat-card stat-companies">
            <div class="d-flex align-items-start justify-content-between">
                <div>
                    <div class="stat-label">Active Companies</div>
                    <div class="stat-value"><?= number_format($s['active_companies']['value'] ?? 0) ?></div>
                    <?php if (!empty($s['active_companies']['trend_pct'])): ?>
                        <span class="stat-trend <?= $s['active_companies']['trend_dir'] ?? 'up' ?>">
                            <i class="fa-solid fa-arrow-<?= $s['active_companies']['trend_dir'] ?? 'up' ?>"></i>
                            <?= $s['active_companies']['trend_pct'] ?>%
                        </span>
                    <?php endif; ?>
                </div>
                <div class="stat-icon">
                    <i class="fa-solid fa-building"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Items -->
    <div class="col-xl-2 col-lg-4 col-sm-6">
        <div class="aci-stat-card stat-stock">
            <div class="d-flex align-items-start justify-content-between">
                <div>
                    <div class="stat-label">Stock Items</div>
                    <div class="stat-value"><?= number_format($s['stock_items']['value'] ?? 0) ?></div>
                    <?php if (!empty($s['stock_items']['trend_pct'])): ?>
                        <span class="stat-trend <?= $s['stock_items']['trend_dir'] ?? 'up' ?>">
                            <i class="fa-solid fa-arrow-<?= $s['stock_items']['trend_dir'] ?? 'up' ?>"></i>
                            <?= $s['stock_items']['trend_pct'] ?>%
                        </span>
                    <?php endif; ?>
                </div>
                <div class="stat-icon">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Open RFQs -->
    <div class="col-xl-2 col-lg-4 col-sm-6">
        <div class="aci-stat-card stat-rfq">
            <div class="d-flex align-items-start justify-content-between">
                <div>
                    <div class="stat-label">Open RFQs</div>
                    <div class="stat-value"><?= number_format($s['open_rfqs']['value'] ?? 0) ?></div>
                    <?php if (!empty($s['open_rfqs']['trend_pct'])): ?>
                        <span class="stat-trend <?= $s['open_rfqs']['trend_dir'] ?? 'up' ?>">
                            <i class="fa-solid fa-arrow-<?= $s['open_rfqs']['trend_dir'] ?? 'up' ?>"></i>
                            <?= $s['open_rfqs']['trend_pct'] ?>%
                        </span>
                    <?php endif; ?>
                </div>
                <div class="stat-icon">
                    <i class="fa-solid fa-file-lines"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Quotes -->
    <div class="col-xl-2 col-lg-4 col-sm-6">
        <div class="aci-stat-card stat-quotes">
            <div class="d-flex align-items-start justify-content-between">
                <div>
                    <div class="stat-label">Pending Quotes</div>
                    <div class="stat-value"><?= number_format($s['pending_quotes']['value'] ?? 0) ?></div>
                    <?php if (!empty($s['pending_quotes']['trend_pct'])): ?>
                        <span class="stat-trend <?= $s['pending_quotes']['trend_dir'] ?? 'up' ?>">
                            <i class="fa-solid fa-arrow-<?= $s['pending_quotes']['trend_dir'] ?? 'up' ?>"></i>
                            <?= $s['pending_quotes']['trend_pct'] ?>%
                        </span>
                    <?php endif; ?>
                </div>
                <div class="stat-icon">
                    <i class="fa-solid fa-file-invoice"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Revenue -->
    <div class="col-xl-2 col-lg-4 col-sm-6">
        <div class="aci-stat-card stat-revenue">
            <div class="d-flex align-items-start justify-content-between">
                <div>
                    <div class="stat-label">Monthly Revenue</div>
                    <div class="stat-value">$<?= number_format($s['monthly_revenue']['value'] ?? 0) ?></div>
                    <?php if (!empty($s['monthly_revenue']['trend_pct'])): ?>
                        <span class="stat-trend <?= $s['monthly_revenue']['trend_dir'] ?? 'up' ?>">
                            <i class="fa-solid fa-arrow-<?= $s['monthly_revenue']['trend_dir'] ?? 'up' ?>"></i>
                            <?= $s['monthly_revenue']['trend_pct'] ?>%
                        </span>
                    <?php endif; ?>
                </div>
                <div class="stat-icon">
                    <i class="fa-solid fa-dollar-sign"></i>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Row 2: Activity Feed + Quick Actions -->
<div class="row g-3 mb-4">

    <!-- Recent Activity -->
    <div class="col-lg-8">
        <div class="aci-card">
            <div class="aci-card-header">
                <h6><i class="fa-solid fa-clock-rotate-left me-2 text-aci-muted"></i>Recent Activity</h6>
                <a href="/pages/v2/activity" class="btn btn-aci-outline btn-sm">View All</a>
            </div>
            <div class="aci-card-body">
                <?php if (!empty($recent_activity)): ?>
                    <?php foreach (array_slice($recent_activity, 0, 8) as $activity): ?>
                        <div class="aci-activity-item">
                            <div class="aci-activity-dot" style="background: <?= htmlspecialchars($activity['color'] ?? '#9CA3AF') ?>;"></div>
                            <div class="flex-grow-1">
                                <div class="activity-text">
                                    <strong><?= htmlspecialchars($activity['user'] ?? '') ?></strong>
                                    <?= htmlspecialchars($activity['action'] ?? '') ?>
                                    <?php if (!empty($activity['target'])): ?>
                                        <a href="<?= htmlspecialchars($activity['url'] ?? '#') ?>"><?= htmlspecialchars($activity['target']) ?></a>
                                    <?php endif; ?>
                                </div>
                                <div class="activity-time"><?= htmlspecialchars($activity['time'] ?? '') ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-4 text-muted">
                        <i class="fa-solid fa-inbox" style="font-size:2rem;opacity:0.3;"></i>
                        <p class="mt-2 mb-0" style="font-size:0.85rem;">No recent activity</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="col-lg-4">
        <div class="aci-card">
            <div class="aci-card-header">
                <h6><i class="fa-solid fa-bolt me-2 text-aci-gold"></i>Quick Actions</h6>
            </div>
            <div class="aci-card-body d-flex flex-column gap-2">

                <a href="/pages/v2/rfq/create" class="aci-quick-action">
                    <div class="qa-icon" style="background:#FEE2E2;color:#DC2626;">
                        <i class="fa-solid fa-file-circle-plus"></i>
                    </div>
                    <div>
                        <strong style="font-size:0.85rem;">New RFQ</strong>
                        <div style="font-size:0.75rem;color:var(--aci-gray-500);">Create a request for quotation</div>
                    </div>
                </a>

                <a href="/pages/v2/parts/create" class="aci-quick-action">
                    <div class="qa-icon" style="background:#DBEAFE;color:#2563EB;">
                        <i class="fa-solid fa-cog"></i>
                    </div>
                    <div>
                        <strong style="font-size:0.85rem;">Add Part</strong>
                        <div style="font-size:0.75rem;color:var(--aci-gray-500);">Register a new aviation part</div>
                    </div>
                </a>

                <a href="/pages/v2/companies/create" class="aci-quick-action">
                    <div class="qa-icon" style="background:#D1FAE5;color:#059669;">
                        <i class="fa-solid fa-building-circle-arrow-right"></i>
                    </div>
                    <div>
                        <strong style="font-size:0.85rem;">Add Company</strong>
                        <div style="font-size:0.75rem;color:var(--aci-gray-500);">Register a customer or supplier</div>
                    </div>
                </a>

                <a href="/pages/v2/quotes/customer/create" class="aci-quick-action">
                    <div class="qa-icon" style="background:#EDE9FE;color:#8B5CF6;">
                        <i class="fa-solid fa-file-invoice"></i>
                    </div>
                    <div>
                        <strong style="font-size:0.85rem;">New Quote</strong>
                        <div style="font-size:0.75rem;color:var(--aci-gray-500);">Prepare a customer quotation</div>
                    </div>
                </a>

                <a href="/pages/v2/invoices/proforma/create" class="aci-quick-action">
                    <div class="qa-icon" style="background:#FEF3C7;color:#D97706;">
                        <i class="fa-solid fa-file-invoice-dollar"></i>
                    </div>
                    <div>
                        <strong style="font-size:0.85rem;">New Invoice</strong>
                        <div style="font-size:0.75rem;color:var(--aci-gray-500);">Generate a proforma invoice</div>
                    </div>
                </a>

            </div>
        </div>
    </div>

</div>

<!-- Row 3: RFQ by Priority + Top Suppliers -->
<div class="row g-3 mb-4">

    <!-- RFQ by Priority Chart -->
    <div class="col-lg-6">
        <div class="aci-card">
            <div class="aci-card-header">
                <h6><i class="fa-solid fa-chart-bar me-2 text-aci-muted"></i>RFQ by Priority</h6>
                <select class="form-select form-select-sm" style="width:auto;font-size:0.8rem;">
                    <option>This Month</option>
                    <option>Last 3 Months</option>
                    <option>This Year</option>
                </select>
            </div>
            <div class="aci-card-body">
                <div id="chart-rfq-priority" style="height:280px;display:flex;align-items:center;justify-content:center;">
                    <div class="text-center text-muted">
                        <i class="fa-solid fa-chart-pie" style="font-size:2.5rem;opacity:0.15;"></i>
                        <p class="mt-2 mb-0" style="font-size:0.8rem;">Chart loads here (Chart.js / ApexCharts)</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Suppliers -->
    <div class="col-lg-6">
        <div class="aci-card">
            <div class="aci-card-header">
                <h6><i class="fa-solid fa-ranking-star me-2 text-aci-gold"></i>Top Suppliers</h6>
                <a href="/pages/v2/reports/suppliers" class="btn btn-aci-outline btn-sm">Full Report</a>
            </div>
            <div class="aci-card-body p-0">
                <table class="table table-sm mb-0" style="font-size:0.85rem;">
                    <thead>
                        <tr>
                            <th class="ps-3" style="border-top:none;">Company</th>
                            <th style="border-top:none;">Orders</th>
                            <th style="border-top:none;">Response</th>
                            <th class="pe-3" style="border-top:none;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($top_suppliers)): ?>
                            <?php foreach (array_slice($top_suppliers, 0, 6) as $supplier): ?>
                                <tr>
                                    <td class="ps-3">
                                        <strong><?= htmlspecialchars($supplier['name'] ?? '') ?></strong>
                                    </td>
                                    <td><?= number_format($supplier['orders'] ?? 0) ?></td>
                                    <td><?= htmlspecialchars($supplier['response_time'] ?? 'N/A') ?></td>
                                    <td class="pe-3">
                                        <span class="aci-badge aci-badge-<?= htmlspecialchars($supplier['status_class'] ?? 'active') ?>">
                                            <?= htmlspecialchars($supplier['status'] ?? 'Active') ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center py-3 text-muted">No supplier data available</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- Row 4: Stock Value + Monthly RFQ Trend -->
<div class="row g-3 mb-4">

    <!-- Stock Value Breakdown -->
    <div class="col-lg-5">
        <div class="aci-card">
            <div class="aci-card-header">
                <h6><i class="fa-solid fa-warehouse me-2 text-aci-muted"></i>Stock Value Breakdown</h6>
            </div>
            <div class="aci-card-body">
                <div id="chart-stock-value" style="height:260px;display:flex;align-items:center;justify-content:center;">
                    <div class="text-center text-muted">
                        <i class="fa-solid fa-chart-pie" style="font-size:2.5rem;opacity:0.15;"></i>
                        <p class="mt-2 mb-0" style="font-size:0.8rem;">Chart loads here</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly RFQ Trend -->
    <div class="col-lg-7">
        <div class="aci-card">
            <div class="aci-card-header">
                <h6><i class="fa-solid fa-chart-line me-2 text-aci-muted"></i>Monthly RFQ Trend</h6>
                <select class="form-select form-select-sm" style="width:auto;font-size:0.8rem;">
                    <option>Last 6 Months</option>
                    <option>Last 12 Months</option>
                    <option>This Year</option>
                </select>
            </div>
            <div class="aci-card-body">
                <div id="chart-rfq-trend" style="height:260px;display:flex;align-items:center;justify-content:center;">
                    <div class="text-center text-muted">
                        <i class="fa-solid fa-chart-line" style="font-size:2.5rem;opacity:0.15;"></i>
                        <p class="mt-2 mb-0" style="font-size:0.8rem;">Chart loads here</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
