<?php
/** Invoices View (NEW MODULE) */
use AeroCanada\Core\{View, Auth, CSRF};
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Invoices</h1>
        <p class="text-muted mb-0">Proforma invoices, commercial invoices, and credit notes</p>
    </div>
    <div class="d-flex gap-2">
        <div class="btn-group">
            <button class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                <i class="fa-solid fa-plus me-1"></i> New Invoice
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#" data-type="proforma"><i class="fa-solid fa-file-invoice me-2"></i>Proforma Invoice</a></li>
                <li><a class="dropdown-item" href="#" data-type="commercial"><i class="fa-solid fa-file-invoice-dollar me-2"></i>Commercial Invoice</a></li>
                <li><a class="dropdown-item" href="#" data-type="credit_note"><i class="fa-solid fa-file-circle-minus me-2"></i>Credit Note</a></li>
            </ul>
        </div>
    </div>
</div>

<!-- Invoice Type Tabs -->
<ul class="nav nav-pills mb-4" role="tablist">
    <li class="nav-item">
        <button class="nav-link active" data-filter="all">All <span class="badge bg-light text-dark ms-1" id="countAll">0</span></button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-filter="proforma">Proforma <span class="badge bg-info ms-1" id="countProforma">0</span></button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-filter="commercial">Commercial <span class="badge bg-success ms-1" id="countCommercial">0</span></button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-filter="credit_note">Credit Notes <span class="badge bg-warning ms-1" id="countCredit">0</span></button>
    </li>
</ul>

<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1 small">Draft</p>
                        <h4 class="mb-0" id="statDraft">0</h4>
                    </div>
                    <div class="rounded-circle bg-secondary bg-opacity-10 p-3">
                        <i class="fa-solid fa-file-pen text-secondary fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1 small">Sent</p>
                        <h4 class="mb-0" id="statSent">0</h4>
                    </div>
                    <div class="rounded-circle bg-info bg-opacity-10 p-3">
                        <i class="fa-solid fa-paper-plane text-info fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1 small">Paid</p>
                        <h4 class="mb-0 text-success" id="statPaid">0</h4>
                    </div>
                    <div class="rounded-circle bg-success bg-opacity-10 p-3">
                        <i class="fa-solid fa-check-circle text-success fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1 small">Overdue</p>
                        <h4 class="mb-0 text-danger" id="statOverdue">0</h4>
                    </div>
                    <div class="rounded-circle bg-danger bg-opacity-10 p-3">
                        <i class="fa-solid fa-exclamation-triangle text-danger fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Invoices Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0" id="invoicesTable" style="width:100%">
            <thead class="table-light">
                <tr>
                    <th>Invoice #</th>
                    <th>Type</th>
                    <th>Company</th>
                    <th>Date</th>
                    <th>Due Date</th>
                    <th class="text-end">Total</th>
                    <th>Status</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    ACI.dataTable('#invoicesTable', '/pages/v2/api/invoices.php?action=datatable', [
        { data: 'invoice_number', render: function(d) { return '<strong>' + d + '</strong>'; }},
        { data: 'invoice_type', render: function(d) {
            const labels = {'proforma':'Proforma','commercial':'Commercial','credit_note':'Credit Note'};
            const colors = {'proforma':'info','commercial':'success','credit_note':'warning'};
            return '<span class="badge bg-' + (colors[d]||'secondary') + '">' + (labels[d]||d) + '</span>';
        }},
        { data: 'company_name' },
        { data: 'invoice_date' },
        { data: 'due_date', render: function(d) {
            if (!d) return '-';
            const due = new Date(d);
            const now = new Date();
            return '<span class="' + (due < now ? 'text-danger fw-bold' : '') + '">' + d + '</span>';
        }},
        { data: 'total', className: 'text-end', render: function(d, t, row) {
            return '<strong>' + Number(d).toLocaleString('en-US', {minimumFractionDigits:2}) + '</strong> ' + (row.currency||'USD');
        }},
        { data: 'status', render: function(d) {
            const colors = {'draft':'secondary','sent':'info','paid':'success','overdue':'danger','cancelled':'dark'};
            return '<span class="badge bg-' + (colors[d]||'secondary') + '">' + d + '</span>';
        }},
        { data: null, orderable: false, className: 'text-center', render: function(data, t, row) {
            return '<div class="btn-group btn-group-sm">' +
                '<button class="btn btn-outline-primary" title="View"><i class="fa-solid fa-eye"></i></button>' +
                '<button class="btn btn-outline-success" title="PDF"><i class="fa-solid fa-file-pdf"></i></button>' +
                '<button class="btn btn-outline-info" title="Send"><i class="fa-solid fa-envelope"></i></button>' +
                '</div>';
        }}
    ]);
});
</script>
