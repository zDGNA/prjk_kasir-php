<?php
require_once '../controllers/AuthController.php';
require_once '../models/Category.php';

$auth = new AuthController();
$auth->requireLogin();

$current_user = $auth->getCurrentUser();
$category = new Category();

// Handle form submissions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $category->name = $_POST['name'];
                $category->description = $_POST['description'];
                $category->status = $_POST['status'];
                
                if ($category->create()) {
                    $message = 'Kategori berhasil ditambahkan';
                    $messageType = 'success';
                } else {
                    $message = 'Gagal menambahkan kategori';
                    $messageType = 'error';
                }
                break;
                
            case 'update':
                $category->id = $_POST['id'];
                $category->name = $_POST['name'];
                $category->description = $_POST['description'];
                $category->status = $_POST['status'];
                
                if ($category->update()) {
                    $message = 'Kategori berhasil diupdate';
                    $messageType = 'success';
                } else {
                    $message = 'Gagal mengupdate kategori';
                    $messageType = 'error';
                }
                break;
                
            case 'delete':
                $category->id = $_POST['id'];
                if ($category->delete()) {
                    $message = 'Kategori berhasil dihapus';
                    $messageType = 'success';
                } else {
                    $message = 'Gagal menghapus kategori';
                    $messageType = 'error';
                }
                break;
        }
    }
}

// Get all categories
$stmt = $category->read();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get category for editing
$editCategory = null;
if (isset($_GET['edit'])) {
    $category->id = $_GET['edit'];
    if ($category->readOne()) {
        $editCategory = [
            'id' => $category->id,
            'name' => $category->name,
            'description' => $category->description,
            'status' => $category->status
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Kategori - Sistem Kasir</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            line-height: 1.6;
        }

        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .navbar-content {
            max-width: 1200px;
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

        .navbar-menu a:hover, .navbar-menu a.active {
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
            color: #333;
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
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-bottom: 20px;
        }

        .card-header {
            padding: 20px;
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
        }

        .card-header h3 {
            margin: 0;
            color: #333;
        }

        .card-body {
            padding: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #333;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #e9ecef;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
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
        }

        .table th,
        .table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }

        .table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }

        .table tbody tr:hover {
            background: #f8f9fa;
        }

        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }

        .badge-success {
            background: #d4edda;
            color: #155724;
        }

        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }

        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
        }

        .modal-content {
            background: white;
            margin: 10% auto;
            padding: 0;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
        }

        .modal-header {
            padding: 20px;
            border-bottom: 1px solid #e9ecef;
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
            color: #6c757d;
        }

        .close:hover {
            color: #333;
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
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-content">
            <div class="navbar-brand">Sistem Kasir</div>
            <ul class="navbar-menu">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="pos.php">Point of Sale</a></li>
                <li><a href="products.php">Produk</a></li>
                <li><a href="categories.php" class="active">Kategori</a></li>
                <li><a href="transactions.php">Transaksi</a></li>
                <?php if($current_user['role'] == 'admin'): ?>
                <li><a href="users.php">Users</a></li>
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
            <h1>Manajemen Kategori</h1>
            <button class="btn btn-primary" onclick="openModal('addModal')">
                + Tambah Kategori
            </button>
        </div>

        <!-- Edit Category Form -->
        <?php if ($editCategory): ?>
        <div class="card">
            <div class="card-header">
                <h3>Edit Kategori</h3>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" value="<?php echo $editCategory['id']; ?>">
                    
                    <div class="form-group">
                        <label>Nama Kategori</label>
                        <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($editCategory['name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($editCategory['description']); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control" required>
                            <option value="active" <?php echo $editCategory['status'] == 'active' ? 'selected' : ''; ?>>Aktif</option>
                            <option value="inactive" <?php echo $editCategory['status'] == 'inactive' ? 'selected' : ''; ?>>Tidak Aktif</option>
                        </select>
                    </div>
                    
                    <div style="display: flex; gap: 10px;">
                        <button type="submit" class="btn btn-success">Update Kategori</button>
                        <a href="categories.php" class="btn btn-warning">Batal</a>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <!-- Categories List -->
        <div class="card">
            <div class="card-header">
                <h3>Daftar Kategori</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Nama Kategori</th>
                                <th>Deskripsi</th>
                                <th>Status</th>
                                <th>Dibuat</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $cat): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($cat['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($cat['description'] ?? '-'); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $cat['status'] == 'active' ? 'success' : 'warning'; ?>">
                                        <?php echo $cat['status'] == 'active' ? 'Aktif' : 'Tidak Aktif'; ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($cat['created_at'])); ?></td>
                                <td>
                                    <a href="categories.php?edit=<?php echo $cat['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                    <button class="btn btn-danger btn-sm" onclick="deleteCategory(<?php echo $cat['id']; ?>)">Hapus</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Category Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h4>Tambah Kategori Baru</h4>
                <span class="close" onclick="closeModal('addModal')">&times;</span>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <input type="hidden" name="action" value="create">
                    
                    <div class="form-group">
                        <label>Nama Kategori</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control" required>
                            <option value="active">Aktif</option>
                            <option value="inactive">Tidak Aktif</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-success">Simpan Kategori</button>
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

        function deleteCategory(id) {
            if (confirm('Apakah Anda yakin ingin menghapus kategori ini?')) {
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