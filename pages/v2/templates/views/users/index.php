<?php
/** Users Management View */
use AeroCanada\Core\{View, Auth, CSRF};
Auth::requireRole('SuperAdmin');
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">User Management</h1>
        <p class="text-muted mb-0">Manage employee accounts and access</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
        <i class="fa-solid fa-user-plus me-1"></i> Add User
    </button>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0" id="usersTable" style="width:100%">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Position</th>
                    <th>Phone</th>
                    <th>Mobile</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($users)): foreach ($users as $u): ?>
                <tr>
                    <td><strong><?= View::e($u['Employee_Name']) ?></strong></td>
                    <td><?= View::e($u['email']) ?></td>
                    <td>
                        <span class="badge bg-<?= $u['statut'] === 'SuperAdmin' ? 'danger' : 'primary' ?>">
                            <?= View::e($u['statut']) ?>
                        </span>
                    </td>
                    <td><?= View::e($u['position'] ?? '') ?></td>
                    <td><?= View::e($u['tel'] ?? '') ?></td>
                    <td><?= View::e($u['mobile'] ?? '') ?></td>
                    <td class="text-center">
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary btn-edit" data-id="<?= $u['Employee_ID'] ?>" title="Edit">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <?php if ($u['Employee_ID'] != Auth::userId()): ?>
                            <button class="btn btn-outline-danger btn-delete" data-id="<?= $u['Employee_ID'] ?>" title="Delete">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/pages/v2/api/users.php">
                <?= CSRF::field() ?>
                <input type="hidden" name="action" value="create">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fa-solid fa-user-plus me-2"></i>Add New User</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Full Name *</label>
                            <input type="text" class="form-control" name="Employee_Name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email *</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Password *</label>
                            <input type="password" class="form-control" name="pw" required minlength="8">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Role *</label>
                            <select class="form-select" name="statut" required>
                                <option value="User">User</option>
                                <option value="Sales">Sales</option>
                                <option value="Manager">Manager</option>
                                <option value="Admin">Admin</option>
                                <option value="SuperAdmin">SuperAdmin</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Position</label>
                            <input type="text" class="form-control" name="position">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Phone</label>
                            <input type="tel" class="form-control" name="tel">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Mobile</label>
                            <input type="tel" class="form-control" name="mobile">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save me-1"></i> Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>
