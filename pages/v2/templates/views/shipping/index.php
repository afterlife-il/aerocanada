<?php
/** Shipping & Delivery Notes View (NEW MODULE) */
use AeroCanada\Core\{View, Auth, CSRF};
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Shipping & Deliveries</h1>
        <p class="text-muted mb-0">Track shipments and delivery notes</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addShipmentModal">
            <i class="fa-solid fa-truck me-1"></i> New Shipment
        </button>
    </div>
</div>

<!-- Shipping Pipeline -->
<div class="row g-3 mb-4">
    <div class="col">
        <div class="card border-top border-4 border-secondary shadow-sm text-center">
            <div class="card-body py-3">
                <i class="fa-solid fa-box-open fa-2x text-secondary mb-2"></i>
                <h4 class="mb-0" id="countPreparing">0</h4>
                <small class="text-muted">Preparing</small>
            </div>
        </div>
    </div>
    <div class="col d-flex align-items-center justify-content-center" style="max-width:40px">
        <i class="fa-solid fa-arrow-right text-muted"></i>
    </div>
    <div class="col">
        <div class="card border-top border-4 border-info shadow-sm text-center">
            <div class="card-body py-3">
                <i class="fa-solid fa-truck-fast fa-2x text-info mb-2"></i>
                <h4 class="mb-0" id="countShipped">0</h4>
                <small class="text-muted">Shipped</small>
            </div>
        </div>
    </div>
    <div class="col d-flex align-items-center justify-content-center" style="max-width:40px">
        <i class="fa-solid fa-arrow-right text-muted"></i>
    </div>
    <div class="col">
        <div class="card border-top border-4 border-primary shadow-sm text-center">
            <div class="card-body py-3">
                <i class="fa-solid fa-plane fa-2x text-primary mb-2"></i>
                <h4 class="mb-0" id="countInTransit">0</h4>
                <small class="text-muted">In Transit</small>
            </div>
        </div>
    </div>
    <div class="col d-flex align-items-center justify-content-center" style="max-width:40px">
        <i class="fa-solid fa-arrow-right text-muted"></i>
    </div>
    <div class="col">
        <div class="card border-top border-4 border-success shadow-sm text-center">
            <div class="card-body py-3">
                <i class="fa-solid fa-circle-check fa-2x text-success mb-2"></i>
                <h4 class="mb-0" id="countDelivered">0</h4>
                <small class="text-muted">Delivered</small>
            </div>
        </div>
    </div>
</div>

<!-- Shipments Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0" id="shippingTable" style="width:100%">
            <thead class="table-light">
                <tr>
                    <th>Shipping #</th>
                    <th>Invoice</th>
                    <th>Company</th>
                    <th>Shipper</th>
                    <th>Tracking</th>
                    <th>Ship Date</th>
                    <th>ETA</th>
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
    ACI.dataTable('#shippingTable', '/pages/v2/api/shipping.php?action=datatable', [
        { data: 'shipping_number', render: function(d) { return '<strong>' + d + '</strong>'; }},
        { data: 'invoice_number' },
        { data: 'company_name' },
        { data: 'shipper_name' },
        { data: 'tracking_number', render: function(d) {
            return d ? '<code>' + d + '</code>' : '<span class="text-muted">N/A</span>';
        }},
        { data: 'ship_date' },
        { data: 'estimated_arrival' },
        { data: 'status', render: function(d) {
            const colors = {'preparing':'secondary','shipped':'info','in_transit':'primary','delivered':'success','returned':'danger'};
            const icons = {'preparing':'box-open','shipped':'truck','in_transit':'plane','delivered':'check-circle','returned':'rotate-left'};
            return '<span class="badge bg-' + (colors[d]||'secondary') + '"><i class="fa-solid fa-' + (icons[d]||'question') + ' me-1"></i>' + (d||'').replace('_',' ') + '</span>';
        }},
        { data: null, orderable: false, className: 'text-center', render: function(data, t, row) {
            return '<div class="btn-group btn-group-sm">' +
                '<button class="btn btn-outline-primary" title="View"><i class="fa-solid fa-eye"></i></button>' +
                '<button class="btn btn-outline-success" title="Delivery Note PDF"><i class="fa-solid fa-file-pdf"></i></button>' +
                '<button class="btn btn-outline-warning" title="Update Status"><i class="fa-solid fa-truck-ramp-box"></i></button>' +
                '</div>';
        }}
    ]);
});
</script>
