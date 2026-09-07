<?php
require_once 'config.php';
require_once 'admin_check.php';

// Handle Delete Tournament
if (isset($_GET['delete'])) {
    $tournament_id = (int)$_GET['delete'];
    $query = "DELETE FROM tournaments WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $tournament_id);
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Tournament deleted successfully!';
    } else {
        $_SESSION['error'] = 'Failed to delete tournament!';
    }
    $stmt->close();
    header('Location: admin_tournaments.php');
    exit;
}

// Handle Cancel Tournament
if (isset($_GET['cancel'])) {
    $tournament_id = (int)$_GET['cancel'];
    $query = "UPDATE tournaments SET status = 'cancelled' WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $tournament_id);
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Tournament cancelled successfully!';
    }
    $stmt->close();
    header('Location: admin_tournaments.php');
    exit;
}

// Get all tournaments
$query = "SELECT t.*, 
          (SELECT COUNT(*) FROM tournament_participants WHERE tournament_id = t.id) as registered_count
          FROM tournaments t 
          ORDER BY t.created_at DESC";
$tournaments = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Tournaments - Admin</title>
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
            box-shadow: 0 10px 30px rgba(212, 175, 55, 0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .admin-header h1 {
            color: var(--admin-gold);
            font-weight: 900;
            margin: 0;
            font-size: 2.5rem;
        }

        .btn-create {
            background: linear-gradient(135deg, var(--admin-gold), #C5A028);
            color: #000;
            padding: 0.8rem 2rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 700;
            transition: all 0.3s ease;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-create:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(212, 175, 55, 0.5);
            color: #000;
        }

        .tournament-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
        }

        .tournament-card {
            background: linear-gradient(135deg, var(--admin-primary) 0%, var(--admin-secondary) 100%);
            border: 2px solid var(--admin-accent);
            border-radius: 15px;
            overflow: hidden;
            transition: all 0.3s ease;
            position: relative;
        }

        .tournament-card:hover {
            transform: translateY(-10px);
            border-color: var(--admin-gold);
            box-shadow: 0 15px 40px rgba(212, 175, 55, 0.3);
        }

        .tournament-banner {
            height: 180px;
            background: linear-gradient(135deg, #0f3460 0%, #1a1a2e 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            border-bottom: 2px solid var(--admin-accent);
            position: relative;
            overflow: hidden;
        }

        .tournament-banner::before {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(212, 175, 55, 0.2) 0%, transparent 70%);
            animation: rotate 10s linear infinite;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .tournament-banner i {
            font-size: 5rem;
            color: var(--admin-gold);
            position: relative;
            z-index: 2;
        }

        .tournament-body {
            padding: 1.5rem;
        }

        .tournament-title {
            color: var(--admin-gold);
            font-weight: 700;
            font-size: 1.3rem;
            margin-bottom: 0.8rem;
        }

        .tournament-info {
            display: flex;
            flex-direction: column;
            gap: 0.8rem;
            margin-bottom: 1.5rem;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            color: var(--admin-text-gray);
            font-size: 0.9rem;
        }

        .info-item i {
            color: var(--admin-gold);
            width: 20px;
            text-align: center;
        }

        .tournament-participants {
            background: var(--admin-accent);
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            text-align: center;
        }

        .participants-count {
            font-size: 2rem;
            font-weight: 900;
            color: var(--admin-gold);
        }

        .participants-label {
            color: var(--admin-text-gray);
            font-size: 0.85rem;
        }

        .status-badge {
            display: inline-block;
            padding: 0.4rem 1rem;
            border-radius: 50px;
            font-weight: 700;
            font-size: 0.85rem;
            margin-bottom: 1rem;
        }

        .status-open {
            background: #28a745;
            color: #fff;
        }

        .status-ongoing {
            background: #ffc107;
            color: #000;
        }

        .status-completed {
            background: #17a2b8;
            color: #fff;
        }

        .status-cancelled {
            background: #dc3545;
            color: #fff;
        }

        .tournament-actions {
            display: flex;
            gap: 0.8rem;
        }

        .btn-action {
            flex: 1;
            padding: 0.7rem;
            border-radius: 8px;
            text-decoration: none;
            text-align: center;
            font-weight: 700;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 0.9rem;
        }

        .btn-manage {
            background: var(--admin-gold);
            color: #000;
        }

        .btn-manage:hover {
            background: #F5D068;
            transform: translateY(-2px);
        }

        .btn-cancel {
            background: #dc3545;
            color: #fff;
        }

        .btn-cancel:hover {
            background: #c82333;
            transform: translateY(-2px);
        }

        .btn-delete {
            background: #6c757d;
            color: #fff;
        }

        .btn-delete:hover {
            background: #545b62;
            transform: translateY(-2px);
        }

        .alert-custom {
            background: var(--admin-accent);
            border: 2px solid var(--admin-gold);
            color: var(--admin-text);
            border-radius: 10px;
            padding: 1rem 1.5rem;
            margin-bottom: 2rem;
        }

        .alert-success {
            border-color: #28a745;
        }

        .alert-error {
            border-color: #dc3545;
        }

        .no-tournaments {
            text-align: center;
            padding: 4rem 2rem;
            background: var(--admin-primary);
            border: 2px dashed var(--admin-accent);
            border-radius: 15px;
        }

        .no-tournaments i {
            font-size: 5rem;
            color: var(--admin-accent);
            margin-bottom: 1rem;
        }

        .no-tournaments h3 {
            color: var(--admin-text-gray);
            margin-bottom: 1rem;
        }

        @media (max-width: 768px) {
            .admin-content {
                margin-left: 70px;
                padding: 1rem;
            }

            .admin-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .tournament-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include 'admin_sidebar.php'; ?>

    <div class="admin-content">
        <!-- Header -->
        <div class="admin-header">
            <h1><i class="fas fa-trophy"></i> Tournaments</h1>
            <a href="admin_create_tournament.php" class="btn-create">
                <i class="fas fa-plus-circle"></i> Create New Tournament
            </a>
        </div>

        <!-- Alerts -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert-custom alert-success">
                <i class="fas fa-check-circle"></i> <?= $_SESSION['success'] ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert-custom alert-error">
                <i class="fas fa-exclamation-circle"></i> <?= $_SESSION['error'] ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Tournament Grid -->
        <?php if ($tournaments->num_rows > 0): ?>
            <div class="tournament-grid">
                <?php while ($tournament = $tournaments->fetch_assoc()): ?>
                    <div class="tournament-card">
                        <div class="tournament-banner">
                            <i class="fas fa-trophy"></i>
                        </div>

                        <div class="tournament-body">
                            <span class="status-badge status-<?= $tournament['status'] ?>">
                                <?= strtoupper($tournament['status']) ?>
                            </span>

                            <h3 class="tournament-title"><?= htmlspecialchars($tournament['nama_tournament']) ?></h3>

                            <div class="tournament-info">
                                <div class="info-item">
                                    <i class="fas fa-calendar"></i>
                                    <span><?= date('d M Y', strtotime($tournament['tanggal_mulai'])) ?></span>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-clock"></i>
                                    <span><?= date('H:i', strtotime($tournament['waktu_mulai'])) ?> WIB</span>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-gift"></i>
                                    <span><?= htmlspecialchars($tournament['hadiah']) ?></span>
                                </div>
                            </div>

                            <div class="tournament-participants">
                                <div class="participants-count">
                                    <?= $tournament['registered_count'] ?>/<?= $tournament['max_peserta'] ?>
                                </div>
                                <div class="participants-label">Participants</div>
                            </div>

                            <div class="tournament-actions">
                                <a href="admin_tournament_bracket.php?id=<?= $tournament['id'] ?>" class="btn-action btn-manage">
                                    <i class="fas fa-sitemap"></i> Manage
                                </a>
                                
                                <?php if ($tournament['status'] == 'open' || $tournament['status'] == 'ongoing'): ?>
                                    <a href="?cancel=<?= $tournament['id'] ?>" class="btn-action btn-cancel" onclick="return confirm('Cancel this tournament?')">
                                        <i class="fas fa-ban"></i> Cancel
                                    </a>
                                <?php endif; ?>

                                <a href="?delete=<?= $tournament['id'] ?>" class="btn-action btn-delete" onclick="return confirm('Delete this tournament permanently?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-tournaments">
                <i class="fas fa-trophy"></i>
                <h3>No Tournaments Yet</h3>
                <p style="color: var(--admin-text-gray); margin-bottom: 1.5rem;">Create your first tournament to get started!</p>
                <a href="admin_create_tournament.php" class="btn-create">
                    <i class="fas fa-plus-circle"></i> Create Tournament
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
