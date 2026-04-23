<?php
/** Stock / Inventory View */
use AeroCanada\Core\{View, Auth, CSRF};
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Inventory</h1>
        <p class="text-muted mb-0">Aircraft parts stock management</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-secondary"><i class="fa-solid fa-file-csv me-1"></i> Export</button>
        <button class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#importStockModal">
            <i class="fa-solid fa-upload me-1"></i> Import CSV
        </button>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStockModal">
            <i class="fa-solid fa-plus me-1"></i> Add Stock
        </button>
    </div>
</div>

<!-- Stock Stats -->
<div class="row g-3 mb-4">
    <div class="col-md-2">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <div class="h4 mb-0 text-primary" id="statTotalItems">--</div>
                <small class="text-muted">Total Items</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <div class="h4 mb-0 text-success" id="statNewParts">--</div>
                <small class="text-muted">New (NE)</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <div class="h4 mb-0 text-info" id="statOHParts">--</div>
                <small class="text-muted">Overhauled</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <div class="h4 mb-0 text-warning" id="statSVParts">--</div>
                <small class="text-muted">Serviceable</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <div class="h4 mb-0 text-danger" id="statARParts">--</div>
                <small class="text-muted">As-Removed</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <div class="h5 mb-0 text-dark" id="statTotalValue">--</div>
                <small class="text-muted">Total Value</small>
            </div>
        </div>
    </div>
</div>

<!-- Stock Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover table-sm mb-0" id="stockTable" style="width:100%">
            <thead class="table-light">
                <tr>
                    <th>P/N</th>
                    <th>S/N</th>
                    <th>Description</th>
                    <th>Condition</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Supplier</th>
                    <th>Location</th>
                    <th>Entry Date</th>
                    <th>Shelf Life</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    ACI.dataTable('#stockTable', '/pages/v2/api/stock.php?action=datatable', [
        { data: 'part_number' },
        { data: 'Fld_Part_SN' },
        { data: 'part_description' },
        { data: 'condition_text', render: function(d) {
            const colors = {'NE':'success','OH':'info','SV':'primary','AR':'warning','RP':'secondary'};
            return '<span class="badge bg-' + (colors[d] || 'secondary') + '">' + (d||'-') + '</span>';
        }},
        { data: 'Fld_Qty', className: 'text-center' },
        { data: 'price_fmt', className: 'text-end' },
        { data: 'supplier_name' },
        { data: 'location_name' },
        { data: 'Fld_Entry_Date' },
        { data: 'Fld_Shelf_Life_Limit', render: function(d) {
            if (!d) return '-';
            const exp = new Date(d);
            const now = new Date();
            const cls = exp < now ? 'danger' : 'success';
            return '<span class="text-' + cls + '">' + d + '</span>';
        }},
        { data: null, orderable: false, className: 'text-center', render: function(data, t, row) {
            return '<div class="btn-group btn-group-sm">' +
                '<button class="btn btn-outline-primary btn-sm" title="View"><i class="fa-solid fa-eye"></i></button>' +
                '<button class="btn btn-outline-warning btn-sm" title="Edit"><i class="fa-solid fa-pen"></i></button>' +
                '</div>';
        }}
    ]);
});
</script>
