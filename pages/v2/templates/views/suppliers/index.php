<?php
/** Supplier Quotes View */
use AeroCanada\Core\{View, Auth, CSRF};
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Supplier Quotes</h1>
        <p class="text-muted mb-0">Quotes received from suppliers</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSQModal">
        <i class="fa-solid fa-plus me-1"></i> Add Supplier Quote
    </button>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0" id="sqTable" style="width:100%">
            <thead class="table-light">
                <tr>
                    <th>RFQ ID</th>
                    <th>Supplier</th>
                    <th>P/N</th>
                    <th>S/N</th>
                    <th>Qty</th>
                    <th>Condition</th>
                    <th>Price</th>
                    <th>Lead Time</th>
                    <th>Date</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    ACI.dataTable('#sqTable', '/pages/v2/api/suppliers.php?action=datatable', [
        { data: 'Fld_RFQ_ID' },
        { data: 'supplier_name' },
        { data: 'part_number' },
        { data: 'Fld_Part_SN' },
        { data: 'Fld_Qty', className: 'text-center' },
        { data: 'condition_text', render: function(d) {
            return '<span class="badge bg-secondary">' + (d||'-') + '</span>';
        }},
        { data: 'Fld_Price', className: 'text-end', render: function(d, t, row) {
            return d ? Number(d).toFixed(2) + ' ' + (row.currency_text||'') : '-';
        }},
        { data: 'lead_time' },
        { data: 'Fld_Current_Date' },
        { data: null, orderable: false, className: 'text-center', render: function(data, t, row) {
            return '<div class="btn-group btn-group-sm">' +
                '<button class="btn btn-outline-primary" title="Edit"><i class="fa-solid fa-pen"></i></button>' +
                '<button class="btn btn-outline-danger btn-delete" data-id="' + row.ID + '" title="Delete"><i class="fa-solid fa-trash"></i></button>' +
                '</div>';
        }}
    ]);
});
</script>
