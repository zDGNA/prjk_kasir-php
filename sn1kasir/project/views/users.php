<?php
require_once '../controllers/AuthController.php';
require_once '../models/User.php';

$auth = new AuthController();
$auth->requireRole('admin'); // Only admin can access this page

$current_user = $auth->getCurrentUser();
$user = new User();

// Handle form submissions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $user->username = $_POST['username'];
                $user->password = $_POST['password'];
                $user->full_name = $_POST['full_name'];
                $user->role = $_POST['role'];
                $user->email = $_POST['email'];
                $user->phone = $_POST['phone'];
                $user->status = $_POST['status'];
                
                if ($user->create()) {
                    $message = 'User berhasil ditambahkan';
                    $messageType = 'success';
                } else {
                    $message = 'Gagal menambahkan user';
                    $messageType = 'error';
                }
                break;
                
            case 'update':
                $user->id = $_POST['id'];
                $user->username = $_POST['username'];
                $user->full_name = $_POST['full_name'];
                $user->role = $_POST['role'];
                $user->email = $_POST['email'];
                $user->phone = $_POST['phone'];
                $user->status = $_POST['status'];
                
                if ($user->update()) {
                    $message = 'User berhasil diupdate';
                    $messageType = 'success';
                } else {
                    $message = 'Gagal mengupdate user';
                    $messageType = 'error';
                }
                break;
                
            case 'delete':
                $user->id = $_POST['id'];
                if ($user->delete()) {
                    $message = 'User berhasil dihapus';
                    $messageType = 'success';
                } else {
                    $message = 'Gagal menghapus user';
                    $messageType = 'error';
                }
                break;
        }
    }
}

// Get all users
$stmt = $user->read();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get user for editing
$editUser = null;
if (isset($_GET['edit'])) {
    $user->id = $_GET['edit'];
    if ($user->readOne()) {
        $editUser = [
            'id' => $user->id,
            'username' => $user->username,
            'full_name' => $user->full_name,
            'role' => $user->role,
            'email' => $user->email,
            'phone' => $user->phone,
            'status' => $user->status
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen User - Sistem Kasir</title>
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #1e1e2f;
        color: #dcdcdc;
        line-height: 1.6;
    }

        .navbar {
            background: linear-gradient(135deg, #2a2a45 0%, #3a2e5a 100%);
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

    .navbar-content {
        max-width: 100%;
        margin: 0 auto;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0 20px;
    }

    .navbar-brand {
        font-size: 24px;
        font-weight: bold;
    }

    .navbar-menu {
        display: flex;
        list-style: none;
        gap: 20px;
    }

    .navbar-menu a {
        color: white;
        text-decoration: none;
        padding: 8px 16px;
        border-radius: 5px;
        transition: background 0.3s;
    }

    .navbar-menu a:hover,
    .navbar-menu a.active {
        background: rgba(255,255,255,0.2);
    }

    .user-info {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }

    .page-header h1 {
        color: #ffffff;
    }

    .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.3s;
    }

    .btn-primary {
        background: #667eea;
        color: white;
    }

    .btn-primary:hover {
        background: #5a6fd8;
    }

    .btn-success {
        background: #28a745;
        color: white;
    }

    .btn-success:hover {
        background: #218838;
    }

    .btn-warning {
        background: #ffc107;
        color: #212529;
    }

    .btn-warning:hover {
        background: #e0a800;
    }

    .btn-danger {
        background: #dc3545;
        color: white;
    }

    .btn-danger:hover {
        background: #c82333;
    }

    .btn-sm {
        padding: 5px 10px;
        font-size: 12px;
    }

    .card {
        background: #2e2e3e;
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.4);
        overflow: hidden;
        margin-bottom: 20px;
    }

    .card-header {
        padding: 20px;
        background: #262636;
        border-bottom: 1px solid #3a3a4f;
    }

    .card-header h3 {
        margin: 0;
        color: #ffffff;
    }

    .card-body {
        padding: 20px;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: 500;
        color: #dcdcdc;
    }

    .form-control {
        width: 100%;
        padding: 10px;
        border: 1px solid #444;
        border-radius: 5px;
        font-size: 14px;
        background: #1e1e2f;
        color: #f1f1f1;
    }

    .form-control:focus {
        outline: none;
        border-color: #667eea;
    }

    .table-responsive {
        overflow-x: auto;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        color: #dcdcdc;
    }

    .table th,
    .table td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #3a3a4f;
    }

    .table th {
        background: #2a2a3d;
        font-weight: 600;
        color: #fff;
    }

    .table tbody tr:hover {
        background: #34344f;
    }

    .badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
    }

    .badge-success {
        background: #145c2a;
        color: #aef3c2;
    }

    .badge-warning {
        background: #4b3f00;
        color: #ffd666;
    }

    .badge-info {
        background: #1b4f5f;
        color: #91eaf4;
    }

    .badge-danger {
        background: #5a1e22;
        color: #f5aeb0;
    }

    .alert {
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
    }

    .alert-success {
        background: #2a4631;
        color: #aef3c2;
        border: 1px solid #3d6b4d;
    }

    .alert-error {
        background: #512c2c;
        color: #f5aeb0;
        border: 1px solid #833e3e;
    }

    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.6);
    }

    .modal-content {
        background: #2a2a3d;
        margin: 5% auto;
        padding: 0;
        border-radius: 10px;
        width: 90%;
        max-width: 500px;
        max-height: 90vh;
        overflow-y: auto;
        color: #fff;
    }

    .modal-header {
        padding: 20px;
        border-bottom: 1px solid #3b3b52;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-header h4 {
        margin: 0;
    }

    .close {
        font-size: 24px;
        cursor: pointer;
        color: #aaa;
    }

    .close:hover {
        color: #fff;
    }

    .modal-body {
        padding: 20px;
    }

    @media (max-width: 768px) {
        .navbar-content {
            flex-direction: column;
            gap: 10px;
        }

        .navbar-menu {
            flex-wrap: wrap;
            justify-content: center;
        }

        .page-header {
            flex-direction: column;
            gap: 15px;
            align-items: flex-start;
        }

        .form-grid {
            grid-template-columns: 1fr;
        }

        .table-responsive {
            font-size: 12px;
        }
    }
</style>

</head>
<body>
    <nav class="navbar">
        <div class="navbar-content">
            <div class="navbar-brand">Sistem Kasir</div>
            <ul class="navbar-menu">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="pos.php">Pembayaran</a></li>
                <li><a href="products.php">Produk</a></li>
                <li><a href="categories.php">Kategori</a></li>
                <li><a href="transactions.php">Transaksi</a></li>
                <?php if($current_user['role'] == 'admin'): ?>
                <li><a href="users.php"class="active">Users</a></li>
                <li><a href="reports.php">Laporan</a></li>
                <?php endif; ?>
            </ul>
            <div class="user-info">
                <span>Halo, <?php echo htmlspecialchars($current_user['full_name']); ?></span>
                <a href="../controllers/AuthController.php?action=logout" class="btn btn-primary">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>

        <div class="page-header">
            <h1>Manajemen User</h1>
            <button class="btn btn-primary" onclick="openModal('addModal')">
                + Tambah User
            </button>
        </div>

        <!-- Edit User Form -->
        <?php if ($editUser): ?>
        <div class="card">
            <div class="card-header">
                <h3>Edit User</h3>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" value="<?php echo $editUser['id']; ?>">
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($editUser['username']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Nama Lengkap</label>
                            <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($editUser['full_name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Role</label>
                            <select name="role" class="form-control" required>
                                <option value="admin" <?php echo $editUser['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                <option value="cashier" <?php echo $editUser['role'] == 'cashier' ? 'selected' : ''; ?>>Kasir</option>
                                <option value="manager" <?php echo $editUser['role'] == 'manager' ? 'selected' : ''; ?>>Manager</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($editUser['email']); ?>">
                        </div>
                        <div class="form-group">
                            <label>Telepon</label>
                            <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($editUser['phone']); ?>">
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" class="form-control" required>
                                <option value="active" <?php echo $editUser['status'] == 'active' ? 'selected' : ''; ?>>Aktif</option>
                                <option value="inactive" <?php echo $editUser['status'] == 'inactive' ? 'selected' : ''; ?>>Tidak Aktif</option>
                            </select>
                        </div>
                    </div>
                    
                    <div style="display: flex; gap: 10px; margin-top: 20px;">
                        <button type="submit" class="btn btn-success">Update User</button>
                        <a href="users.php" class="btn btn-warning">Batal</a>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <!-- Users List -->
        <div class="card">
            <div class="card-header">
                <h3>Daftar User</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Nama Lengkap</th>
                                <th>Role</th>
                                <th>Email</th>
                                <th>Telepon</th>
                                <th>Status</th>
                                <th>Terdaftar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $userData): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($userData['username']); ?></strong></td>
                                <td><?php echo htmlspecialchars($userData['full_name']); ?></td>
                                <td>
                                    <span class="badge badge-<?php 
                                        echo $userData['role'] == 'admin' ? 'danger' : 
                                            ($userData['role'] == 'manager' ? 'warning' : 'info'); 
                                    ?>">
                                        <?php echo ucfirst($userData['role']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($userData['email'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($userData['phone'] ?? '-'); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $userData['status'] == 'active' ? 'success' : 'warning'; ?>">
                                        <?php echo $userData['status'] == 'active' ? 'Aktif' : 'Tidak Aktif'; ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($userData['created_at'])); ?></td>
                                <td>
                                    <?php if ($userData['id'] != $current_user['id']): ?>
                                    <a href="users.php?edit=<?php echo $userData['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                    <button class="btn btn-danger btn-sm" onclick="deleteUser(<?php echo $userData['id']; ?>)">Hapus</button>
                                    <?php else: ?>
                                    <span class="badge badge-info">Current User</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h4>Tambah User Baru</h4>
                <span class="close" onclick="closeModal('addModal')">&times;</span>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <input type="hidden" name="action" value="create">
                    
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" name="full_name" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Role</label>
                        <select name="role" class="form-control" required>
                            <option value="">Pilih Role</option>
                            <option value="admin">Admin</option>
                            <option value="cashier">Kasir</option>
                            <option value="manager">Manager</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label>Telepon</label>
                        <input type="text" name="phone" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control" required>
                            <option value="active">Aktif</option>
                            <option value="inactive">Tidak Aktif</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-success">Simpan User</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function deleteUser(id) {
            if (confirm('Apakah Anda yakin ingin menghapus user ini?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modals = document.getElementsByClassName('modal');
            for (let i = 0; i < modals.length; i++) {
                if (event.target === modals[i]) {
                    modals[i].style.display = 'none';
                }
            }
        }
    </script>
</body>
</html>