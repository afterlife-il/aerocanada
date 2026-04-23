<?php
/** Parts List View */
use AeroCanada\Core\{View, Auth, CSRF};
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Parts Catalog</h1>
        <p class="text-muted mb-0">Manage aircraft parts and components</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-secondary" id="btnExportCSV">
            <i class="fa-solid fa-file-csv me-1"></i> Export
        </button>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPartModal">
            <i class="fa-solid fa-plus me-1"></i> Add Part
        </button>
    </div>
</div>

<!-- Stats Row -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                    <i class="fa-solid fa-cogs text-primary fa-lg"></i>
                </div>
                <div>
                    <div class="h4 mb-0" id="statTotalParts">--</div>
                    <small class="text-muted">Total Parts</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                    <i class="fa-solid fa-check-circle text-success fa-lg"></i>
                </div>
                <div>
                    <div class="h4 mb-0" id="statAvailable">--</div>
                    <small class="text-muted">Available</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3">
                    <i class="fa-solid fa-archive text-warning fa-lg"></i>
                </div>
                <div>
                    <div class="h4 mb-0" id="statArchived">--</div>
                    <small class="text-muted">Archived</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-info bg-opacity-10 p-3 me-3">
                    <i class="fa-solid fa-plane text-info fa-lg"></i>
                </div>
                <div>
                    <div class="h4 mb-0" id="statAircraft">--</div>
                    <small class="text-muted">Aircraft Types</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Parts Table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fa-solid fa-list me-2 text-muted"></i>Parts List</h5>
        <div class="d-flex gap-2">
            <select class="form-select form-select-sm" id="filterStatus" style="width: auto;">
                <option value="">All Status</option>
                <option value="Available" selected>Available</option>
                <option value="archive">Archived</option>
            </select>
            <select class="form-select form-select-sm" id="filterAircraft" style="width: auto;">
                <option value="">All Aircraft</option>
                <?php if (!empty($aircrafts)): foreach ($aircrafts as $ac): ?>
                    <option value="<?= $ac['Fld_AC_ID'] ?>"><?= View::e($ac['Fld_AC_Model']) ?></option>
                <?php endforeach; endif; ?>
            </select>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0" id="partsTable" style="width:100%">
            <thead class="table-light">
                <tr>
                    <th>P/N</th>
                    <th>Description</th>
                    <th>MFG/OEM</th>
                    <th>Aircraft</th>
                    <th>List Price</th>
                    <th>ATA Ch.</th>
                    <th>Status</th>
                    <th class="text-center" style="width:120px">Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<!-- Add Part Modal -->
<div class="modal fade" id="addPartModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="addPartForm" method="POST" action="/pages/v2/api/parts.php">
                <?= CSRF::field() ?>
                <input type="hidden" name="action" value="create">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fa-solid fa-plus me-2"></i>Add New Part</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Part Number *</label>
                            <input type="text" class="form-control" name="Fld_Part_Nbr" required placeholder="e.g. 5827310-3">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Alt P/N</label>
                            <input type="text" class="form-control" name="alt_pn" placeholder="Alternative part number">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Description *</label>
                            <input type="text" class="form-control" name="Fld_Part_Desc" required placeholder="Part description">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Manufacturer / OEM</label>
                            <input type="text" class="form-control aci-autocomplete" name="Fld_Part_MFG" data-source="companies" placeholder="Type to search...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Aircraft Type</label>
                            <select class="form-select" name="Fld_AC_ID">
                                <option value="">-- Select --</option>
                                <?php if (!empty($aircrafts)): foreach ($aircrafts as $ac): ?>
                                    <option value="<?= $ac['Fld_AC_ID'] ?>"><?= View::e($ac['Fld_AC_Model'] . ' ' . ($ac['Fld_AC_Series'] ?? '')) ?></option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">List Price</label>
                            <div class="input-group">
                                <input type="number" step="0.01" class="form-control" name="Fld_Part_List_Price" placeholder="0.00">
                                <select class="form-select" name="Fld_Part_Price_Currency_ID" style="max-width:100px">
                                    <?php if (!empty($currencies)): foreach ($currencies as $cur): ?>
                                        <option value="<?= $cur['Fld_Currency_ID'] ?>"><?= View::e($cur['Fld_Currency_Text'] ?? 'USD') ?></option>
                                    <?php endforeach; endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">ATA Chapter</label>
                            <input type="text" class="form-control" name="ata_chapter" placeholder="e.g. 32">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">CAGE Code</label>
                            <input type="text" class="form-control" name="cage_code" placeholder="e.g. 7S835">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">MOQ</label>
                            <input type="number" class="form-control" name="moq" placeholder="Min. order qty">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">OEM Lead Time</label>
                            <input type="text" class="form-control" name="oem_lead_time" placeholder="e.g. 45 days">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Core Value</label>
                            <input type="number" step="0.01" class="form-control" name="core_value" placeholder="0.00">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Remarks</label>
                            <textarea class="form-control" name="Fld_Remark" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-save me-1"></i> Save Part
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Part Detail Modal -->
<div class="modal fade" id="partDetailModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="partDetailTitle">Part Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="partDetailContent">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="text-muted mt-2">Loading...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable
    const table = ACI.dataTable('#partsTable', '/pages/v2/api/parts.php?action=datatable', [
        { data: 'Fld_Part_Nbr', title: 'P/N' },
        { data: 'Fld_Part_Desc', title: 'Description' },
        { data: 'mfg_name', title: 'MFG/OEM' },
        { data: 'aircraft_model', title: 'Aircraft' },
        { data: 'list_price_fmt', title: 'List Price', className: 'text-end' },
        { data: 'ata_chapter', title: 'ATA Ch.' },
        {
            data: 'status',
            title: 'Status',
            render: function(data) {
                const cls = data === 'Available' ? 'success' : 'secondary';
                return '<span class="badge bg-' + cls + '">' + data + '</span>';
            }
        },
        {
            data: null,
            orderable: false,
            className: 'text-center',
            render: function(data, type, row) {
                return '<div class="btn-group btn-group-sm">' +
                    '<button class="btn btn-outline-primary btn-detail" data-id="' + row.Fld_Part_ID + '" title="View"><i class="fa-solid fa-eye"></i></button>' +
                    '<button class="btn btn-outline-warning btn-edit" data-id="' + row.Fld_Part_ID + '" title="Edit"><i class="fa-solid fa-pen"></i></button>' +
                    '<button class="btn btn-outline-danger btn-archive" data-id="' + row.Fld_Part_ID + '" title="Archive"><i class="fa-solid fa-archive"></i></button>' +
                    '</div>';
            }
        }
    ]);

    // Filter handlers
    document.getElementById('filterStatus').addEventListener('change', function() { table.ajax.reload(); });
    document.getElementById('filterAircraft').addEventListener('change', function() { table.ajax.reload(); });

    // Add Part form submit
    document.getElementById('addPartForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const form = this;
        const data = new FormData(form);

        fetch(form.action, { method: 'POST', body: data })
            .then(r => r.json())
            .then(res => {
                if (res.ok) {
                    ACI.toast('Part added successfully!', 'success');
                    bootstrap.Modal.getInstance(document.getElementById('addPartModal')).hide();
                    form.reset();
                    table.ajax.reload();
                } else {
                    ACI.toast(res.error || 'Error adding part', 'error');
                }
            });
    });

    // Part detail view
    document.getElementById('partsTable').addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-detail');
        if (!btn) return;
        const id = btn.dataset.id;
        const modal = new bootstrap.Modal(document.getElementById('partDetailModal'));
        document.getElementById('partDetailContent').innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>';
        modal.show();

        fetch('/pages/v2/api/parts.php?action=detail&id=' + id)
            .then(r => r.json())
            .then(res => {
                if (res.ok) {
                    document.getElementById('partDetailTitle').textContent = 'P/N: ' + res.data.Fld_Part_Nbr;
                    document.getElementById('partDetailContent').innerHTML = buildPartDetail(res.data);
                }
            });
    });

    // Archive handler
    document.getElementById('partsTable').addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-archive');
        if (!btn) return;
        ACI.confirmDelete('Archive this part?').then(function() {
            fetch('/pages/v2/api/parts.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': ACI.csrfToken()},
                body: JSON.stringify({ action: 'archive', id: btn.dataset.id })
            })
            .then(r => r.json())
            .then(res => {
                if (res.ok) {
                    ACI.toast('Part archived', 'success');
                    table.ajax.reload();
                }
            });
        });
    });

    function buildPartDetail(p) {
        return `
        <div class="row g-4">
            <div class="col-md-6">
                <h6 class="text-muted text-uppercase small">General Information</h6>
                <table class="table table-sm">
                    <tr><th width="40%">Part Number</th><td><strong>${p.Fld_Part_Nbr || ''}</strong></td></tr>
                    <tr><th>Alt P/N</th><td>${p.alt_pn || '-'}</td></tr>
                    <tr><th>Description</th><td>${p.Fld_Part_Desc || ''}</td></tr>
                    <tr><th>Manufacturer</th><td>${p.mfg_name || '-'}</td></tr>
                    <tr><th>Aircraft</th><td>${p.aircraft_model || '-'}</td></tr>
                    <tr><th>ATA Chapter</th><td>${p.ata_chapter || '-'}</td></tr>
                    <tr><th>CAGE Code</th><td>${p.cage_code || '-'}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6 class="text-muted text-uppercase small">Pricing & Lead Time</h6>
                <table class="table table-sm">
                    <tr><th width="40%">List Price</th><td>${p.list_price_fmt || '-'}</td></tr>
                    <tr><th>Core Value</th><td>${p.core_value || '-'}</td></tr>
                    <tr><th>MOQ</th><td>${p.moq || '-'}</td></tr>
                    <tr><th>OEM Lead Time</th><td>${p.oem_lead_time || '-'}</td></tr>
                    <tr><th>Status</th><td><span class="badge bg-${p.status==='Available'?'success':'secondary'}">${p.status}</span></td></tr>
                    <tr><th>Added</th><td>${p.Fld_Add_PN_Date || '-'}</td></tr>
                    <tr><th>Remarks</th><td>${p.Fld_Remark || '-'}</td></tr>
                </table>
            </div>
        </div>`;
    }
});
</script>
