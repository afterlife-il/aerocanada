<?php
/** Companies List View */
use AeroCanada\Core\{View, Auth, CSRF};
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Companies</h1>
        <p class="text-muted mb-0">Suppliers, customers, and business partners</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-secondary" id="btnExportCompanies">
            <i class="fa-solid fa-file-csv me-1"></i> Export
        </button>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCompanyModal">
            <i class="fa-solid fa-plus me-1"></i> Add Company
        </button>
    </div>
</div>

<!-- Filters Row -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-2">
        <div class="row g-2 align-items-center">
            <div class="col-md-3">
                <select class="form-select form-select-sm" id="filterContact">
                    <option value="">All Contacts (ACI)</option>
                    <?php if (!empty($employees)): foreach ($employees as $emp): ?>
                        <option value="<?= $emp['Employee_ID'] ?>"><?= View::e($emp['Employee_Name']) ?></option>
                    <?php endforeach; endif; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" id="filterRating">
                    <option value="">All Ratings</option>
                    <option value="5">5 Stars</option>
                    <option value="4">4 Stars</option>
                    <option value="3">3 Stars</option>
                    <option value="2">2 Stars</option>
                    <option value="1">1 Star</option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" id="filterType">
                    <option value="">All Types</option>
                    <?php if (!empty($companyTypes)): foreach ($companyTypes as $ct): ?>
                        <option value="<?= $ct['Fld_Company_Type_ID'] ?>"><?= View::e($ct['Fld_Company_Type_Text']) ?></option>
                    <?php endforeach; endif; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" id="filterStatus">
                    <option value="Available">Active</option>
                    <option value="">All</option>
                    <option value="archive">Archived</option>
                </select>
            </div>
            <div class="col-md-3 text-end">
                <span class="badge bg-light text-dark" id="companyCount">--</span> companies
            </div>
        </div>
    </div>
</div>

<!-- Companies Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0" id="companiesTable" style="width:100%">
            <thead class="table-light">
                <tr>
                    <th style="width:50px">Logo</th>
                    <th>Company Name</th>
                    <th>CAGE Code</th>
                    <th>Type</th>
                    <th>Country</th>
                    <th>Local Time</th>
                    <th>ACI Contact</th>
                    <th>Rating</th>
                    <th class="text-center" style="width:140px">Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<!-- Company Detail Offcanvas (side panel) -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="companyDetail" style="width:700px">
    <div class="offcanvas-header bg-primary text-white">
        <h5 class="offcanvas-title" id="companyDetailTitle">Company Details</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body" id="companyDetailBody">
        <div class="text-center py-5">
            <div class="spinner-border text-primary"></div>
        </div>
    </div>
</div>

<!-- Add Company Modal -->
<div class="modal fade" id="addCompanyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="addCompanyForm" method="POST" action="/pages/v2/api/companies.php" enctype="multipart/form-data">
                <?= CSRF::field() ?>
                <input type="hidden" name="action" value="create">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fa-solid fa-building me-2"></i>Add New Company</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs mb-3" role="tablist">
                        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tabGeneral">General</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabAddress">Address</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabPayment">Payment</a></li>
                    </ul>
                    <div class="tab-content">
                        <!-- General Tab -->
                        <div class="tab-pane active" id="tabGeneral">
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label class="form-label fw-semibold">Company Name *</label>
                                    <input type="text" class="form-control" name="Fld_Company_Name" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">CAGE Code</label>
                                    <input type="text" class="form-control" name="cage_code">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Company Type</label>
                                    <select class="form-select" name="Fld_Company_Type_ID">
                                        <option value="">-- Select --</option>
                                        <?php if (!empty($companyTypes)): foreach ($companyTypes as $ct): ?>
                                            <option value="<?= $ct['Fld_Company_Type_ID'] ?>"><?= View::e($ct['Fld_Company_Type_Text']) ?></option>
                                        <?php endforeach; endif; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Rating</label>
                                    <select class="form-select" name="companyrating">
                                        <option value="">--</option>
                                        <option value="5">5 Stars</option>
                                        <option value="4">4 Stars</option>
                                        <option value="3">3 Stars</option>
                                        <option value="2">2 Stars</option>
                                        <option value="1">1 Star</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">ACI Contact</label>
                                    <select class="form-select" name="Employee_ID">
                                        <?php if (!empty($employees)): foreach ($employees as $emp): ?>
                                            <option value="<?= $emp['Employee_ID'] ?>" <?= ($emp['Employee_ID'] == Auth::userId()) ? 'selected' : '' ?>><?= View::e($emp['Employee_Name']) ?></option>
                                        <?php endforeach; endif; ?>
                                    </select>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label fw-semibold">Website</label>
                                    <input type="url" class="form-control" name="internet" placeholder="https://">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Logo</label>
                                    <input type="file" class="form-control" name="logocompany" accept="image/*">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">VAT Number</label>
                                    <input type="text" class="form-control" name="Fld_VAT_Nbr">
                                </div>
                            </div>
                        </div>
                        <!-- Address Tab -->
                        <div class="tab-pane" id="tabAddress">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Address Title</label>
                                    <input type="text" class="form-control" name="title_address1" placeholder="e.g. Head Office">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Address Type</label>
                                    <select class="form-select" name="Fld_Company_Address_Type1">
                                        <option value="">-- Select --</option>
                                        <?php if (!empty($addressTypes)): foreach ($addressTypes as $at): ?>
                                            <option value="<?= $at['Fld_Division_ID'] ?>"><?= View::e($at['Fld_Division_Text']) ?></option>
                                        <?php endforeach; endif; ?>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Street</label>
                                    <input type="text" class="form-control" name="Fld_Company_Street1">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">City</label>
                                    <input type="text" class="form-control" name="Fld_Company_City1">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">State/Province</label>
                                    <input type="text" class="form-control" name="Fld_Company_State1">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Zip Code</label>
                                    <input type="text" class="form-control" name="Fld_Company_ZipCode1">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Country</label>
                                    <input type="text" class="form-control" name="Fld_Company_Country1">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Phone</label>
                                    <input type="tel" class="form-control" name="Fld_Company_Phone1">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Email</label>
                                    <input type="email" class="form-control" name="Fld_Company_Email1">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Timezone</label>
                                    <input type="text" class="form-control" name="UTC_timezone1" placeholder="e.g. America/Toronto">
                                </div>
                            </div>
                        </div>
                        <!-- Payment Tab -->
                        <div class="tab-pane" id="tabPayment">
                            <div class="row g-3">
                                <div class="col-12"><h6>Customer Payment Terms</h6></div>
                                <div class="col-md-4">
                                    <label class="form-label">Term</label>
                                    <select class="form-select" name="customer_payment_term_id">
                                        <option value="">--</option>
                                        <?php if (!empty($paymentTerms)): foreach ($paymentTerms as $pt): ?>
                                            <option value="<?= $pt['Fld_Payment_Term_ID'] ?>"><?= View::e($pt['Fld_Payment_Text']) ?></option>
                                        <?php endforeach; endif; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Amount</label>
                                    <input type="number" step="0.01" class="form-control" name="customer_payment_term_amount">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Currency</label>
                                    <select class="form-select" name="customer_payment_term_currencyid">
                                        <?php if (!empty($currencies)): foreach ($currencies as $cur): ?>
                                            <option value="<?= $cur['Fld_Currency_ID'] ?>"><?= View::e($cur['Fld_Currency_Text'] ?? 'USD') ?></option>
                                        <?php endforeach; endif; ?>
                                    </select>
                                </div>
                                <div class="col-12"><hr><h6>ACI Payment Terms</h6></div>
                                <div class="col-md-4">
                                    <label class="form-label">Term</label>
                                    <select class="form-select" name="aci_payment_term_id">
                                        <option value="">--</option>
                                        <?php if (!empty($paymentTerms)): foreach ($paymentTerms as $pt): ?>
                                            <option value="<?= $pt['Fld_Payment_Term_ID'] ?>"><?= View::e($pt['Fld_Payment_Text']) ?></option>
                                        <?php endforeach; endif; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Amount</label>
                                    <input type="number" step="0.01" class="form-control" name="aci_payment_term_amount">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Currency</label>
                                    <select class="form-select" name="aci_payment_term_currencyid">
                                        <?php if (!empty($currencies)): foreach ($currencies as $cur): ?>
                                            <option value="<?= $cur['Fld_Currency_ID'] ?>"><?= View::e($cur['Fld_Currency_Text'] ?? 'USD') ?></option>
                                        <?php endforeach; endif; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save me-1"></i> Save Company</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const table = ACI.dataTable('#companiesTable', '/pages/v2/api/companies.php?action=datatable', [
        {
            data: 'logocompany',
            orderable: false,
            render: function(data) {
                if (data) return '<img src="../logo_company/' + data + '" style="max-height:30px;max-width:50px" alt="">';
                return '<i class="fa-solid fa-building text-muted"></i>';
            }
        },
        { data: 'Fld_Company_Name', render: function(data, t, row) {
            return '<a href="#" class="fw-semibold text-decoration-none btn-detail" data-id="' + row.Fld_Company_ID + '">' + data + '</a>';
        }},
        { data: 'cage_code' },
        { data: 'company_type' },
        { data: 'country' },
        { data: 'local_time', render: function(data) { return data || '<span class="text-muted">N/A</span>'; }},
        { data: 'aci_contact_name' },
        { data: 'companyrating', render: function(data) {
            if (!data) return '-';
            let stars = '';
            for (let i = 0; i < 5; i++) stars += '<i class="fa-' + (i < data ? 'solid' : 'regular') + ' fa-star text-warning"></i>';
            return stars;
        }},
        {
            data: null, orderable: false, className: 'text-center',
            render: function(data, t, row) {
                return '<div class="btn-group btn-group-sm">' +
                    '<button class="btn btn-outline-primary btn-detail" data-id="' + row.Fld_Company_ID + '" title="View"><i class="fa-solid fa-eye"></i></button>' +
                    '<button class="btn btn-outline-success btn-add-contact" data-id="' + row.Fld_Company_ID + '" title="Add Contact"><i class="fa-solid fa-user-plus"></i></button>' +
                    '<button class="btn btn-outline-danger btn-archive" data-id="' + row.Fld_Company_ID + '" title="Archive"><i class="fa-solid fa-archive"></i></button>' +
                    '</div>';
            }
        }
    ]);

    // Filter handlers
    ['filterContact', 'filterRating', 'filterType', 'filterStatus'].forEach(function(id) {
        document.getElementById(id).addEventListener('change', function() { table.ajax.reload(); });
    });

    // Company detail offcanvas
    document.getElementById('companiesTable').addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-detail');
        if (!btn) return;
        e.preventDefault();
        const id = btn.dataset.id;
        const offcanvas = new bootstrap.Offcanvas(document.getElementById('companyDetail'));
        document.getElementById('companyDetailBody').innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>';
        offcanvas.show();

        fetch('/pages/v2/api/companies.php?action=detail&id=' + id)
            .then(r => r.json())
            .then(res => {
                if (res.ok) {
                    document.getElementById('companyDetailTitle').textContent = res.data.company.Fld_Company_Name;
                    document.getElementById('companyDetailBody').innerHTML = buildCompanyDetail(res.data);
                }
            });
    });

    // Form submit
    document.getElementById('addCompanyForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const form = this;
        fetch(form.action, { method: 'POST', body: new FormData(form) })
            .then(r => r.json())
            .then(res => {
                if (res.ok) {
                    ACI.toast('Company added!', 'success');
                    bootstrap.Modal.getInstance(document.getElementById('addCompanyModal')).hide();
                    form.reset();
                    table.ajax.reload();
                } else {
                    ACI.toast(res.error || 'Error', 'error');
                }
            });
    });

    function buildCompanyDetail(d) {
        const c = d.company;
        const addrs = d.addresses || [];
        const contacts = d.contacts || [];

        let html = '<div class="mb-4">';
        if (c.logocompany) {
            html += '<img src="../logo_company/' + c.logocompany + '" class="mb-3" style="max-height:60px">';
        }
        html += '<div class="row g-3">';
        html += '<div class="col-6"><strong>CAGE Code:</strong> ' + (c.cage_code || '-') + '</div>';
        html += '<div class="col-6"><strong>Website:</strong> ' + (c.internet ? '<a href="' + c.internet + '" target="_blank">' + c.internet + '</a>' : '-') + '</div>';
        html += '<div class="col-6"><strong>VAT:</strong> ' + (d.vat || '-') + '</div>';
        html += '<div class="col-6"><strong>Type:</strong> ' + (d.type || '-') + '</div>';
        html += '</div></div>';

        // Addresses
        html += '<h6 class="border-bottom pb-2 mt-4"><i class="fa-solid fa-map-marker-alt me-2 text-primary"></i>Addresses</h6>';
        if (addrs.length) {
            addrs.forEach(function(a) {
                html += '<div class="card card-body mb-2"><strong>' + (a.title_address || 'Address') + '</strong><br>';
                html += [a.Fld_Company_Street, a.Fld_Company_City, a.Fld_Company_State, a.Fld_Company_ZipCode, a.Fld_Company_Country].filter(Boolean).join(', ');
                html += '<br><small class="text-muted">' + [a.Fld_Company_Phone, a.Fld_Company_Email].filter(Boolean).join(' | ') + '</small></div>';
            });
        } else {
            html += '<p class="text-muted">No addresses</p>';
        }

        // Contacts
        html += '<h6 class="border-bottom pb-2 mt-4"><i class="fa-solid fa-users me-2 text-success"></i>Contacts</h6>';
        if (contacts.length) {
            html += '<div class="table-responsive"><table class="table table-sm"><thead><tr><th>Name</th><th>Title</th><th>Phone</th><th>Email</th></tr></thead><tbody>';
            contacts.forEach(function(ct) {
                html += '<tr><td>' + (ct.Fld_Contact_Name || '') + '</td><td>' + (ct.Fld_Contact_Title || '') + '</td><td>' + (ct.Fld_Contact_Phone || '') + '</td><td>' + (ct.Fld_Contact_Email || '') + '</td></tr>';
            });
            html += '</tbody></table></div>';
        } else {
            html += '<p class="text-muted">No contacts</p>';
        }

        return html;
    }
});
</script>
