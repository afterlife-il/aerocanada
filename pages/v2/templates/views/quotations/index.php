<?php
/** Customer Quotations View */
use AeroCanada\Core\{View, Auth, CSRF};
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Customer Quotations</h1>
        <p class="text-muted mb-0">Quotes sent to customers</p>
    </div>
    <div class="d-flex gap-2">
        <div class="btn-group">
            <button class="btn btn-outline-secondary" data-filter="today">Today</button>
            <button class="btn btn-outline-secondary" data-filter="week">This Week</button>
            <button class="btn btn-outline-secondary active" data-filter="month">This Month</button>
            <button class="btn btn-outline-secondary" data-filter="all">All</button>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0" id="quotationsTable" style="width:100%">
            <thead class="table-light">
                <tr>
                    <th>RFQ ID</th>
                    <th>Date</th>
                    <th>P/N</th>
                    <th>Customer</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Currency</th>
                    <th>Condition</th>
                    <th>Lead Time</th>
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
    ACI.dataTable('#quotationsTable', '/pages/v2/api/quotations.php?action=datatable', [
        { data: 'Fld_RFQ_ID' },
        { data: 'Fld_Quote_Date' },
        { data: 'part_number' },
        { data: 'customer_name' },
        { data: 'Fld_Qty', className: 'text-center' },
        { data: 'Fld_Price', className: 'text-end', render: function(d) { return d ? Number(d).toFixed(2) : '-'; }},
        { data: 'currency_text' },
        { data: 'condition_text' },
        { data: 'lead_time' },
        { data: 'rfqvalid', render: function(d) {
            return d === 'ok' ? '<span class="badge bg-success">Validated</span>' : '<span class="badge bg-warning">Pending</span>';
        }},
        { data: null, orderable: false, className: 'text-center', render: function(data, t, row) {
            return '<div class="btn-group btn-group-sm">' +
                '<button class="btn btn-outline-primary" title="View"><i class="fa-solid fa-eye"></i></button>' +
                '<button class="btn btn-outline-success" title="Send Email"><i class="fa-solid fa-envelope"></i></button>' +
                '<button class="btn btn-outline-info" title="PDF"><i class="fa-solid fa-file-pdf"></i></button>' +
                '</div>';
        }}
    ]);
});
</script>
