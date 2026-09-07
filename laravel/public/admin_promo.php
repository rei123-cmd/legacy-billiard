<?php
require_once 'config.php';
require_once 'admin_check.php';

// Handle Delete Promo
if (isset($_GET['delete'])) {
    $promo_id = (int)$_GET['delete'];
    $query = "DELETE FROM promo WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $promo_id);
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Promo deleted successfully!';
    }
    $stmt->close();
    header('Location: admin_promo.php');
    exit;
}

// Handle Add/Edit Promo
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $judul = clean_input($_POST['judul']);
    $deskripsi = clean_input($_POST['deskripsi']);
    $kode_promo = clean_input($_POST['kode_promo']);
    $tipe_diskon = $_POST['tipe_diskon'];
    $nilai_diskon = (float)$_POST['nilai_diskon'];
    $min_pembelian = (float)$_POST['min_pembelian'];
    $max_diskon = !empty($_POST['max_diskon']) ? (float)$_POST['max_diskon'] : null;
    $tanggal_mulai = $_POST['tanggal_mulai'];
    $tanggal_selesai = $_POST['tanggal_selesai'];
    $created_by = $_SESSION['user_id'];
    
    if (isset($_POST['promo_id']) && !empty($_POST['promo_id'])) {
        // Update
        $promo_id = (int)$_POST['promo_id'];
        $query = "UPDATE promo SET judul=?, deskripsi=?, kode_promo=?, tipe_diskon=?, nilai_diskon=?, 
                  min_pembelian=?, max_diskon=?, tanggal_mulai=?, tanggal_selesai=? WHERE id=?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssdddssi", $judul, $deskripsi, $kode_promo, $tipe_diskon, $nilai_diskon, 
                          $min_pembelian, $max_diskon, $tanggal_mulai, $tanggal_selesai, $promo_id);
    } else {
        // Insert
        $query = "INSERT INTO promo (judul, deskripsi, kode_promo, tipe_diskon, nilai_diskon, min_pembelian, 
                  max_diskon, tanggal_mulai, tanggal_selesai, jenis_promo, created_by) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'spesial', ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssdddssi", $judul, $deskripsi, $kode_promo, $tipe_diskon, $nilai_diskon, 
                          $min_pembelian, $max_diskon, $tanggal_mulai, $tanggal_selesai, $created_by);
    }
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Promo saved successfully!';
    } else {
        $_SESSION['error'] = 'Failed to save promo!';
    }
    $stmt->close();
    header('Location: admin_promo.php');
    exit;
}

// Get all promos
$query = "SELECT * FROM promo WHERE jenis_promo = 'spesial' ORDER BY created_at DESC";
$promos = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Promo - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --admin-primary: #1a1a2e;
            --admin-secondary: #16213e;
            --admin-accent: #0f3460;
            --admin-gold: #D4AF37;
        }

        body {
            background: #0a0a0a;
            color: #ffffff;
            font-family: 'Inter', sans-serif;
        }

        .admin-content {
            margin-left: 280px;
            padding: 2rem;
            min-height: 100vh;
        }

        .admin-header {
            background: linear-gradient(135deg, var(--admin-primary) 0%, var(--admin-secondary) 100%);
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            border: 2px solid var(--admin-gold);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .admin-header h1 {
            color: var(--admin-gold);
            font-weight: 900;
            margin: 0;
        }

        .btn-add {
            background: var(--admin-gold);
            color: #000;
            padding: 0.8rem 2rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 700;
            border: none;
            cursor: pointer;
        }

        .promo-table {
            background: var(--admin-primary);
            border-radius: 15px;
            border: 2px solid var(--admin-accent);
            overflow: hidden;
        }

        .table {
            color: #ffffff;
            margin: 0;
        }

        .table thead {
            background: var(--admin-accent);
        }

        .table th {
            color: var(--admin-gold);
            font-weight: 700;
            padding: 1rem;
            border: none;
        }

        .table td {
            padding: 1rem;
            border-bottom: 1px solid var(--admin-accent);
            vertical-align: middle;
        }

        .table tbody tr:hover {
            background: var(--admin-accent);
        }

        .badge-active {
            background: #28a745;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
        }

        .badge-inactive {
            background: #dc3545;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
        }

        .btn-action {
            padding: 0.4rem 0.8rem;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            margin: 0 0.2rem;
            font-weight: 600;
        }

        .btn-edit {
            background: #ffc107;
            color: #000;
        }

        .btn-delete {
            background: #dc3545;
            color: #fff;
        }

        .modal-content {
            background: var(--admin-secondary);
            border: 2px solid var(--admin-gold);
            color: #ffffff;
        }

        .modal-header {
            border-bottom: 2px solid var(--admin-accent);
        }

        .form-label {
            color: var(--admin-gold);
            font-weight: 700;
        }

        .form-control, .form-select {
            background: var(--admin-accent);
            border: 2px solid var(--admin-accent);
            color: #ffffff;
        }

        .form-control:focus, .form-select:focus {
            background: var(--admin-accent);
            border-color: var(--admin-gold);
            color: #ffffff;
            box-shadow: 0 0 10px rgba(212, 175, 55, 0.3);
        }

        @media (max-width: 768px) {
            .admin-content {
                margin-left: 70px;
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'admin_sidebar.php'; ?>

    <div class="admin-content">
        <div class="admin-header">
            <h1><i class="fas fa-tags"></i> Manage Promo</h1>
            <button class="btn-add" data-bs-toggle="modal" data-bs-target="#promoModal">
                <i class="fas fa-plus-circle"></i> Add Promo
            </button>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <div class="promo-table">
            <table class="table">
                <thead>
                    <tr>
                        <th>Kode Promo</th>
                        <th>Judul</th>
                        <th>Tipe</th>
                        <th>Nilai</th>
                        <th>Min. Pembelian</th>
                        <th>Periode</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($promo = $promos->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($promo['kode_promo']) ?></strong></td>
                            <td><?= htmlspecialchars($promo['judul']) ?></td>
                            <td><?= strtoupper($promo['tipe_diskon']) ?></td>
                            <td>
                                <?php if ($promo['tipe_diskon'] == 'persen'): ?>
                                    <?= $promo['nilai_diskon'] ?>%
                                <?php else: ?>
                                    Rp <?= number_format($promo['nilai_diskon'], 0, ',', '.') ?>
                                <?php endif; ?>
                            </td>
                            <td>Rp <?= number_format($promo['min_pembelian'], 0, ',', '.') ?></td>
                            <td>
                                <?= date('d/m/Y', strtotime($promo['tanggal_mulai'])) ?><br>
                                s/d <?= date('d/m/Y', strtotime($promo['tanggal_selesai'])) ?>
                            </td>
                            <td>
                                <?php if ($promo['aktif']): ?>
                                    <span class="badge-active">ACTIVE</span>
                                <?php else: ?>
                                    <span class="badge-inactive">INACTIVE</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn-action btn-edit" onclick="editPromo(<?= htmlspecialchars(json_encode($promo)) ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <a href="?delete=<?= $promo['id'] ?>" class="btn-action btn-delete" onclick="return confirm('Delete this promo?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Promo Modal -->
    <div class="modal fade" id="promoModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" style="color: var(--admin-gold);"><i class="fas fa-tags"></i> <span id="modalTitle">Add Promo</span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="promo_id" id="promo_id">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Judul Promo</label>
                                <input type="text" class="form-control" name="judul" id="judul" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Kode Promo</label>
                                <input type="text" class="form-control" name="kode_promo" id="kode_promo" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control" name="deskripsi" id="deskripsi" rows="2"></textarea>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Tipe Diskon</label>
                                <select class="form-select" name="tipe_diskon" id="tipe_diskon" required>
                                    <option value="persen">Persentase (%)</option>
                                    <option value="nominal">Nominal (Rp)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Nilai Diskon</label>
                                <input type="number" step="0.01" class="form-control" name="nilai_diskon" id="nilai_diskon" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Max Diskon (Rp)</label>
                                <input type="number" step="0.01" class="form-control" name="max_diskon" id="max_diskon">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Min. Pembelian (Rp)</label>
                            <input type="number" step="0.01" class="form-control" name="min_pembelian" id="min_pembelian" required>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Tanggal Mulai</label>
                                <input type="date" class="form-control" name="tanggal_mulai" id="tanggal_mulai" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tanggal Selesai</label>
                                <input type="date" class="form-control" name="tanggal_selesai" id="tanggal_selesai" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn-add"><i class="fas fa-save"></i> Save Promo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editPromo(promo) {
            document.getElementById('modalTitle').textContent = 'Edit Promo';
            document.getElementById('promo_id').value = promo.id;
            document.getElementById('judul').value = promo.judul;
            document.getElementById('kode_promo').value = promo.kode_promo;
            document.getElementById('deskripsi').value = promo.deskripsi;
            document.getElementById('tipe_diskon').value = promo.tipe_diskon;
            document.getElementById('nilai_diskon').value = promo.nilai_diskon;
            document.getElementById('max_diskon').value = promo.max_diskon;
            document.getElementById('min_pembelian').value = promo.min_pembelian;
            document.getElementById('tanggal_mulai').value = promo.tanggal_mulai;
            document.getElementById('tanggal_selesai').value = promo.tanggal_selesai;
            
            new bootstrap.Modal(document.getElementById('promoModal')).show();
        }

        // Reset form when modal closes
        document.getElementById('promoModal').addEventListener('hidden.bs.modal', function() {
            document.getElementById('modalTitle').textContent = 'Add Promo';
            document.querySelector('form').reset();
        });
    </script>
</body>
</html>
