<?php
require_once 'config.php';
require_once 'admin_check.php';

// Handle Status Update
if (isset($_POST['update_status'])) {
    $meja_id = (int)$_POST['meja_id'];
    $status = $_POST['status'];
    
    $query = "UPDATE meja SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $status, $meja_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Table status updated successfully!';
    } else {
        $_SESSION['error'] = 'Failed to update table status!';
    }
    $stmt->close();
    header('Location: admin_tables.php');
    exit;
}

// Get all tables
$query = "SELECT m.*, c.nama_cabang 
          FROM meja m 
          JOIN cabang c ON m.cabang_id = c.id 
          ORDER BY c.nama_cabang, m.lantai, m.nama_meja";
$tables = $conn->query($query);

// Group by branch
$tables_by_branch = [];
while ($table = $tables->fetch_assoc()) {
    $tables_by_branch[$table['nama_cabang']][] = $table;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Tables - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --admin-primary: #1a1a2e;
            --admin-secondary: #16213e;
            --admin-accent: #0f3460;
            --admin-gold: #D4AF37;
            --admin-text: #ffffff;
            --admin-text-gray: #b0b0b0;
        }

        body {
            background: #0a0a0a;
            color: var(--admin-text);
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
        }

        .admin-header h1 {
            color: var(--admin-gold);
            font-weight: 900;
            margin: 0;
            font-size: 2.5rem;
        }

        .branch-section {
            background: linear-gradient(135deg, var(--admin-primary) 0%, var(--admin-secondary) 100%);
            padding: 2rem;
            border-radius: 15px;
            border: 2px solid var(--admin-accent);
            margin-bottom: 2rem;
        }

        .branch-title {
            color: var(--admin-gold);
            font-weight: 700;
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            padding-bottom: 0.8rem;
            border-bottom: 2px solid var(--admin-accent);
        }

        .table-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        .table-card {
            background: var(--admin-accent);
            border: 2px solid var(--admin-accent);
            border-radius: 12px;
            padding: 1.5rem;
            transition: all 0.3s ease;
            position: relative;
        }

        .table-card:hover {
            transform: translateY(-5px);
            border-color: var(--admin-gold);
            box-shadow: 0 10px 30px rgba(212, 175, 55, 0.2);
        }

        .table-name {
            color: var(--admin-gold);
            font-weight: 700;
            font-size: 1.3rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .table-info {
            color: var(--admin-text-gray);
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .status-selector {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .status-btn {
            flex: 1;
            padding: 0.6rem;
            border: 2px solid transparent;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
        }

        .status-btn.tersedia {
            background: #28a745;
            color: #fff;
        }

        .status-btn.terisi {
            background: #ffc107;
            color: #000;
        }

        .status-btn.maintenance {
            background: #dc3545;
            color: #fff;
        }

        .status-btn.active {
            border-color: var(--admin-gold);
            box-shadow: 0 0 20px rgba(212, 175, 55, 0.5);
        }

        .btn-save {
            width: 100%;
            background: var(--admin-gold);
            color: #000;
            padding: 0.7rem;
            border: none;
            border-radius: 8px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(212, 175, 55, 0.4);
        }

        .floor-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: var(--admin-primary);
            color: var(--admin-gold);
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 700;
        }

        @media (max-width: 768px) {
            .admin-content {
                margin-left: 70px;
                padding: 1rem;
            }

            .table-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include 'admin_sidebar.php'; ?>

    <div class="admin-content">
        <div class="admin-header">
            <h1><i class="fas fa-table"></i> Manage Tables</h1>
            <p style="color: var(--admin-text-gray); margin: 0.5rem 0 0 0;">Set maintenance mode or availability status for each table</p>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php foreach ($tables_by_branch as $branch_name => $branch_tables): ?>
            <div class="branch-section">
                <h3 class="branch-title"><i class="fas fa-building"></i> <?= htmlspecialchars($branch_name) ?></h3>
                
                <div class="table-grid">
                    <?php foreach ($branch_tables as $table): ?>
                        <div class="table-card">
                            <span class="floor-badge">Floor <?= $table['lantai'] ?></span>
                            
                            <h4 class="table-name">
                                <i class="fas fa-chess-board"></i>
                                <?= htmlspecialchars($table['nama_meja']) ?>
                            </h4>

                            <div class="table-info">
                                <i class="fas fa-info-circle"></i> ID: #<?= $table['id'] ?>
                            </div>

                            <form method="POST">
                                <input type="hidden" name="meja_id" value="<?= $table['id'] ?>">
                                
                                <div class="status-selector">
                                    <label class="status-btn tersedia <?= $table['status'] == 'tersedia' ? 'active' : '' ?>">
                                        <input type="radio" name="status" value="tersedia" <?= $table['status'] == 'tersedia' ? 'checked' : '' ?> style="display: none;">
                                        <i class="fas fa-check-circle"></i> Available
                                    </label>

                                    <label class="status-btn terisi <?= $table['status'] == 'terisi' ? 'active' : '' ?>">
                                        <input type="radio" name="status" value="terisi" <?= $table['status'] == 'terisi' ? 'checked' : '' ?> style="display: none;">
                                        <i class="fas fa-users"></i> Occupied
                                    </label>

                                    <label class="status-btn maintenance <?= $table['status'] == 'maintenance' ? 'active' : '' ?>">
                                        <input type="radio" name="status" value="maintenance" <?= $table['status'] == 'maintenance' ? 'checked' : '' ?> style="display: none;">
                                        <i class="fas fa-tools"></i> Maintenance
                                    </label>
                                </div>

                                <button type="submit" name="update_status" class="btn-save">
                                    <i class="fas fa-save"></i> Save Status
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-highlight selected status
        document.querySelectorAll('.status-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                this.closest('.status-selector').querySelectorAll('.status-btn').forEach(b => {
                    b.classList.remove('active');
                });
                this.classList.add('active');
            });
        });
    </script>
</body>
</html>
