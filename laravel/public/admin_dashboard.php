<?php
require_once 'config.php';

// CHECK IF ADMIN
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

// GET STATISTICS
$stats = [];

// Total Users
$result = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'user'");
$stats['total_users'] = $result->fetch_assoc()['total'];

// Total Bookings
$result = $conn->query("SELECT COUNT(*) as total FROM booking");
$stats['total_bookings'] = $result->fetch_assoc()['total'];

// Pending Bookings
$result = $conn->query("SELECT COUNT(*) as total FROM booking WHERE status = 'pending'");
$stats['pending_bookings'] = $result->fetch_assoc()['total'];

// Total Revenue
$result = $conn->query("SELECT SUM(total_harga) as total FROM booking WHERE payment_status = 'paid'");
$stats['total_revenue'] = $result->fetch_assoc()['total'] ?? 0;

// Total Tables
$result = $conn->query("SELECT COUNT(*) as total FROM meja");
$stats['total_tables'] = $result->fetch_assoc()['total'];

// Active Vouchers
$result = $conn->query("SELECT COUNT(*) as total FROM voucher WHERE aktif = 1 AND tanggal_selesai >= CURDATE()");
$stats['active_vouchers'] = $result->fetch_assoc()['total'];

// Recent Bookings
$recent_bookings = $conn->query("
    SELECT b.*, u.nama as user_name, m.nama_meja 
    FROM booking b 
    LEFT JOIN users u ON b.user_id = u.id 
    LEFT JOIN meja m ON b.meja_id = m.id 
    ORDER BY b.created_at DESC 
    LIMIT 10
");

// Monthly Revenue Chart Data
$monthly_revenue = $conn->query("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        SUM(total_harga) as revenue
    FROM booking 
    WHERE payment_status = 'paid' 
    AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY month
    ORDER BY month ASC
");

$revenue_data = [];
while ($row = $monthly_revenue->fetch_assoc()) {
    $revenue_data[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Legacy Billiard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            background: #0a0a0a !important;
            color: #fff !important;
        }

        /* SIDEBAR */
        .admin-sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 280px;
            height: 100vh;
            background: linear-gradient(180deg, #1a1a2e 0%, #0f0f1e 100%);
            border-right: 2px solid #D4AF37;
            overflow-y: auto;
            z-index: 1000;
        }

        .admin-sidebar .logo {
            padding: 2rem;
            text-align: center;
            border-bottom: 1px solid rgba(212, 175, 55, 0.2);
        }

        .admin-sidebar .logo h3 {
            color: #D4AF37;
            font-weight: 900;
            font-size: 1.5rem;
            margin: 0;
        }

        .admin-sidebar .logo p {
            color: #888;
            font-size: 0.9rem;
            margin: 0.5rem 0 0 0;
        }

        .admin-menu {
            padding: 1rem 0;
        }

        .admin-menu a {
            display: flex;
            align-items: center;
            padding: 1rem 2rem;
            color: #b0b0b0;
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .admin-menu a:hover,
        .admin-menu a.active {
            background: rgba(212, 175, 55, 0.1);
            color: #D4AF37;
            border-left-color: #D4AF37;
        }

        .admin-menu a i {
            width: 25px;
            margin-right: 1rem;
            font-size: 1.2rem;
        }

        /* MAIN CONTENT */
        .admin-content {
            margin-left: 280px;
            padding: 2rem;
            min-height: 100vh;
        }

        /* TOP BAR */
        .admin-topbar {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            border: 2px solid #D4AF37;
            border-radius: 15px;
            padding: 1.5rem 2rem;
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .admin-topbar h1 {
            color: #D4AF37;
            font-weight: 900;
            font-size: 2rem;
            margin: 0;
        }

        .admin-user {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .admin-user img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: 2px solid #D4AF37;
        }

        .admin-user-info h5 {
            margin: 0;
            color: #D4AF37;
            font-weight: 700;
        }

        .admin-user-info p {
            margin: 0;
            color: #888;
            font-size: 0.85rem;
        }

        /* STATS CARDS */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            border: 2px solid #D4AF37;
            border-radius: 15px;
            padding: 1.5rem;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(212, 175, 55, 0.3);
        }

        .stat-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .stat-card-header h6 {
            color: #b0b0b0;
            font-size: 0.9rem;
            font-weight: 600;
            margin: 0;
            text-transform: uppercase;
        }

        .stat-card-header i {
            font-size: 2rem;
            color: #D4AF37;
        }

        .stat-card-value {
            font-size: 2.5rem;
            color: #D4AF37;
            font-weight: 900;
            margin-bottom: 0.5rem;
        }

        .stat-card-label {
            color: #888;
            font-size: 0.85rem;
        }

        /* TABLE */
        .data-table {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            border: 2px solid #D4AF37;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .data-table h3 {
            color: #D4AF37;
            font-weight: 700;
            margin-bottom: 1.5rem;
        }

        .table {
            color: #fff;
        }

        .table thead {
            border-bottom: 2px solid #D4AF37;
        }

        .table th {
            color: #D4AF37;
            font-weight: 700;
            padding: 1rem;
            border: none;
        }

        .table td {
            padding: 1rem;
            border-bottom: 1px solid rgba(212, 175, 55, 0.1);
            vertical-align: middle;
        }

        .table tbody tr:hover {
            background: rgba(212, 175, 55, 0.05);
        }

        .badge {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .badge-warning {
            background: #ffc107;
            color: #000;
        }

        .badge-success {
            background: #28a745;
            color: #fff;
        }

        .badge-danger {
            background: #dc3545;
            color: #fff;
        }

        .badge-info {
            background: #17a2b8;
            color: #fff;
        }

        /* CHART */
        .chart-container {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            border: 2px solid #D4AF37;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .chart-container h3 {
            color: #D4AF37;
            font-weight: 700;
            margin-bottom: 1.5rem;
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .admin-sidebar {
                transform: translateX(-100%);
            }

            .admin-content {
                margin-left: 0;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }

        .btn-action {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            transition: all 0.3s ease;
            margin: 0 0.25rem;
        }

        .btn-edit {
            background: #ffc107;
            color: #000;
        }

        .btn-edit:hover {
            background: #ffb300;
            transform: translateY(-2px);
        }

        .btn-delete {
            background: #dc3545;
            color: #fff;
        }

        .btn-delete:hover {
            background: #c82333;
            transform: translateY(-2px);
        }

        .btn-view {
            background: #17a2b8;
            color: #fff;
        }

        .btn-view:hover {
            background: #138496;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <!-- SIDEBAR -->
    <div class="admin-sidebar">
        <div class="logo">
            <h3><i class="fas fa-dice-d6"></i> LEGACY BILLIARD</h3>
            <p>Admin Panel</p>
        </div>
        <nav class="admin-menu">
            <a href="admin_dashboard.php" class="active">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
            <a href="admin_bookings.php">
                <i class="fas fa-calendar-check"></i>
                <span>Kelola Booking</span>
            </a>
            <a href="admin_users.php">
                <i class="fas fa-users"></i>
                <span>Kelola User</span>
            </a>
            <a href="admin_tables.php">
                <i class="fas fa-table"></i>
                <span>Kelola Meja</span>
            </a>
            <a href="admin_tournaments.php">
                <i class="fas fa-trophy"></i>
                <span>Kelola Tournament</span>
            </a>
            <a href="admin_vouchers.php">
                <i class="fas fa-ticket-alt"></i>
                <span>Kelola Voucher</span>
            </a>
            <a href="admin_promo.php">
                <i class="fas fa-tags"></i>
                <span>Kelola Promo</span>
            </a>
            <a href="admin_gallery.php">
                <i class="fas fa-images"></i>
                <span>Kelola Gallery</span>
            </a>
            <a href="admin_reports.php">
                <i class="fas fa-chart-line"></i>
                <span>Laporan</span>
            </a>
            <a href="admin_settings.php">
                <i class="fas fa-cog"></i>
                <span>Pengaturan</span>
            </a>
            <a href="logout.php">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </nav>
    </div>

    <!-- MAIN CONTENT -->
    <div class="admin-content">
        <!-- TOP BAR -->
        <div class="admin-topbar">
            <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
            <div class="admin-user">
                <img src="images/avataruser.jpg" alt="Admin">
                <div class="admin-user-info">
                    <h5><?= htmlspecialchars($_SESSION['username']) ?></h5>
                    <p>Administrator</p>
                </div>
            </div>
        </div>

        <!-- STATISTICS CARDS -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-card-header">
                    <h6>Total Users</h6>
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-card-value"><?= number_format($stats['total_users']) ?></div>
                <div class="stat-card-label">Registered Users</div>
            </div>

            <div class="stat-card">
                <div class="stat-card-header">
                    <h6>Total Bookings</h6>
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="stat-card-value"><?= number_format($stats['total_bookings']) ?></div>
                <div class="stat-card-label">All Time Bookings</div>
            </div>

            <div class="stat-card">
                <div class="stat-card-header">
                    <h6>Pending Bookings</h6>
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-card-value"><?= number_format($stats['pending_bookings']) ?></div>
                <div class="stat-card-label">Awaiting Confirmation</div>
            </div>

            <div class="stat-card">
                <div class="stat-card-header">
                    <h6>Total Revenue</h6>
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-card-value">Rp <?= number_format($stats['total_revenue'], 0, ',', '.') ?></div>
                <div class="stat-card-label">Paid Bookings</div>
            </div>

            <div class="stat-card">
                <div class="stat-card-header">
                    <h6>Total Tables</h6>
                    <i class="fas fa-table"></i>
                </div>
                <div class="stat-card-value"><?= number_format($stats['total_tables']) ?></div>
                <div class="stat-card-label">Available Tables</div>
            </div>

            <div class="stat-card">
                <div class="stat-card-header">
                    <h6>Active Vouchers</h6>
                    <i class="fas fa-ticket-alt"></i>
                </div>
                <div class="stat-card-value"><?= number_format($stats['active_vouchers']) ?></div>
                <div class="stat-card-label">Valid Vouchers</div>
            </div>
        </div>

        <!-- REVENUE CHART -->
        <div class="chart-container">
            <h3><i class="fas fa-chart-line"></i> Revenue Trend (Last 6 Months)</h3>
            <canvas id="revenueChart" height="80"></canvas>
        </div>

        <!-- RECENT BOOKINGS -->
        <div class="data-table">
            <h3><i class="fas fa-calendar-alt"></i> Recent Bookings</h3>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Meja</th>
                            <th>Tanggal</th>
                            <th>Waktu</th>
                            <th>Durasi</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($booking = $recent_bookings->fetch_assoc()): ?>
                        <tr>
                            <td>#<?= $booking['id'] ?></td>
                            <td><?= htmlspecialchars($booking['user_name'] ?? 'Guest') ?></td>
                            <td><?= htmlspecialchars($booking['nama_meja']) ?></td>
                            <td><?= date('d M Y', strtotime($booking['tanggal_booking'])) ?></td>
                            <td><?= date('H:i', strtotime($booking['waktu_mulai'])) ?></td>
                            <td><?= $booking['durasi_jam'] ?> jam</td>
                            <td>Rp <?= number_format($booking['total_harga'], 0, ',', '.') ?></td>
                            <td>
                                <?php
                                $status_class = [
                                    'pending' => 'warning',
                                    'confirmed' => 'success',
                                    'cancelled' => 'danger',
                                    'completed' => 'info'
                                ];
                                ?>
                                <span class="badge badge-<?= $status_class[$booking['status']] ?>">
                                    <?= ucfirst($booking['status']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-<?= $booking['payment_status'] == 'paid' ? 'success' : 'danger' ?>">
                                    <?= ucfirst($booking['payment_status']) ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn-action btn-view" onclick="viewBooking(<?= $booking['id'] ?>)">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn-action btn-edit" onclick="editBooking(<?= $booking['id'] ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // REVENUE CHART
        const revenueData = <?= json_encode($revenue_data) ?>;
        
        const ctx = document.getElementById('revenueChart').getContext('2d');
        const revenueChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: revenueData.map(d => d.month),
                datasets: [{
                    label: 'Revenue (Rp)',
                    data: revenueData.map(d => d.revenue),
                    borderColor: '#D4AF37',
                    backgroundColor: 'rgba(212, 175, 55, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        labels: {
                            color: '#D4AF37',
                            font: {
                                size: 14,
                                weight: 'bold'
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: '#b0b0b0',
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        },
                        grid: {
                            color: 'rgba(212, 175, 55, 0.1)'
                        }
                    },
                    x: {
                        ticks: {
                            color: '#b0b0b0'
                        },
                        grid: {
                            color: 'rgba(212, 175, 55, 0.1)'
                        }
                    }
                }
            }
        });

        // ACTIONS
        function viewBooking(id) {
            window.location.href = 'admin_booking_detail.php?id=' + id;
        }

        function editBooking(id) {
            window.location.href = 'admin_booking_edit.php?id=' + id;
        }
    </script>
</body>
</html>
