<?php
/** RFQ List View */
use AeroCanada\Core\{View, Auth, CSRF};
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Request for Quotations</h1>
        <p class="text-muted mb-0">Manage customer RFQs and sourcing</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRFQModal">
            <i class="fa-solid fa-plus me-1"></i> New RFQ
        </button>
    </div>
</div>

<!-- Quick Filters -->
<div class="row g-3 mb-4">
    <div class="col-md-2">
        <div class="card border-start border-4 border-danger shadow-sm">
            <div class="card-body py-2 text-center cursor-pointer filter-card" data-priority="AOG">
                <div class="h5 mb-0 text-danger" id="countAOG">0</div>
                <small class="text-muted">AOG</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-start border-4 border-warning shadow-sm">
            <div class="card-body py-2 text-center cursor-pointer filter-card" data-priority="Urgent">
                <div class="h5 mb-0 text-warning" id="countUrgent">0</div>
                <small class="text-muted">Urgent</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-start border-4 border-primary shadow-sm">
            <div class="card-body py-2 text-center cursor-pointer filter-card" data-priority="Normal">
                <div class="h5 mb-0 text-primary" id="countNormal">0</div>
                <small class="text-muted">Normal</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-start border-4 border-info shadow-sm">
            <div class="card-body py-2 text-center cursor-pointer filter-card" data-priority="Low">
                <div class="h5 mb-0 text-info" id="countLow">0</div>
                <small class="text-muted">Low</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body py-2">
                <div class="row align-items-center">
                    <div class="col-6">
                        <select class="form-select form-select-sm" id="filterEmployee">
                            <option value="">All Sales</option>
                            <?php if (!empty($employees)): foreach ($employees as $emp): ?>
                                <option value="<?= $emp['Employee_ID'] ?>"><?= View::e($emp['Employee_Name']) ?></option>
                            <?php endforeach; endif; ?>
                        </select>
                    </div>
                    <div class="col-6">
                        <select class="form-select form-select-sm" id="filterPeriod">
                            <option value="">All Time</option>
                            <option value="today">Today</option>
                            <option value="week">This Week</option>
                            <option value="month" selected>This Month</option>
                            <option value="quarter">This Quarter</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- RFQ Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0" id="rfqTable" style="width:100%">
            <thead class="table-light">
                <tr>
                    <th>RFQ ID</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Contact</th>
                    <th>P/N</th>
                    <th>Qty</th>
                    <th>Type</th>
                    <th>Priority</th>
                    <th>Sales</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const table = ACI.dataTable('#rfqTable', '/pages/v2/api/rfq.php?action=datatable', [
        { data: 'Fld_RFQ_ID', render: function(d) { return '<strong>' + d + '</strong>'; }},
        { data: 'date' },
        { data: 'customer_name' },
        { data: 'contact_name' },
        { data: 'pn_rfq', render: function(d, t, row) {
            return '<code>' + (d||'') + '</code><br><small class="text-muted">' + (row.description_rfq||'') + '</small>';
        }},
        { data: 'Fld_Qty', className: 'text-center' },
        { data: 'rfq_type' },
        { data: 'priority_text', render: function(d) {
            const colors = {'AOG':'danger','Urgent':'warning','Normal':'primary','Low':'info','Routine':'secondary'};
            return '<span class="badge bg-' + (colors[d] || 'secondary') + '">' + (d||'-') + '</span>';
        }},
        { data: 'employee_name' },
        { data: null, orderable: false, className: 'text-center', render: function(data, t, row) {
            return '<div class="btn-group btn-group-sm">' +
                '<button class="btn btn-outline-primary" title="View RFQ"><i class="fa-solid fa-eye"></i></button>' +
                '<button class="btn btn-outline-success" title="Create Quote"><i class="fa-solid fa-file-invoice-dollar"></i></button>' +
                '<button class="btn btn-outline-info" title="Email"><i class="fa-solid fa-envelope"></i></button>' +
                '<button class="btn btn-outline-danger btn-delete" data-id="' + row.ID + '" title="Delete"><i class="fa-solid fa-trash"></i></button>' +
                '</div>';
        }}
    ]);
});
</script>
