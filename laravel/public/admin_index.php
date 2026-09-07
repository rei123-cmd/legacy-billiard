<?php


require_once 'admin_check.php';



$stats = [];

// 1. Total Users (exclude admins)
$result = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'user'");
$stats['total_users'] = $result->fetch_assoc()['total'];

// 2. Total Bookings (all time)
$result = $conn->query("SELECT COUNT(*) as total FROM booking");
$stats['total_bookings'] = $result->fetch_assoc()['total'];

// 3. Today's Bookings
$result = $conn->query("SELECT COUNT(*) as total FROM booking WHERE DATE(created_at) = CURDATE()");
$stats['today_bookings'] = $result->fetch_assoc()['total'];

// 4. Pending Bookings
$result = $conn->query("SELECT COUNT(*) as total FROM booking WHERE status = 'pending'");
$stats['pending_bookings'] = $result->fetch_assoc()['total'];

// 5. Confirmed Bookings
$result = $conn->query("SELECT COUNT(*) as total FROM booking WHERE status = 'confirmed'");
$stats['confirmed_bookings'] = $result->fetch_assoc()['total'];

// 6. Total Revenue (paid bookings only)
$result = $conn->query("SELECT COALESCE(SUM(total_harga), 0) as total FROM booking WHERE payment_status = 'paid'");
$stats['total_revenue'] = $result->fetch_assoc()['total'];

// 7. Today's Revenue
$result = $conn->query("SELECT COALESCE(SUM(total_harga), 0) as total FROM booking WHERE payment_status = 'paid' AND DATE(created_at) = CURDATE()");
$stats['today_revenue'] = $result->fetch_assoc()['total'];

// 8. Total Tables
$result = $conn->query("SELECT COUNT(*) as total FROM meja");
$stats['total_tables'] = $result->fetch_assoc()['total'];

// 9. Available Tables
$result = $conn->query("SELECT COUNT(*) as total FROM meja WHERE status = 'tersedia'");
$stats['available_tables'] = $result->fetch_assoc()['total'];

// 10. Occupied Tables
$result = $conn->query("SELECT COUNT(*) as total FROM meja WHERE status = 'terisi'");
$stats['occupied_tables'] = $result->fetch_assoc()['total'];

// 11. Active Tournaments
$result = $conn->query("SELECT COUNT(*) as total FROM tournaments WHERE status IN ('open', 'ongoing')");
$stats['active_tournaments'] = $result->fetch_assoc()['total'];

// 12. Completed Tournaments
$result = $conn->query("SELECT COUNT(*) as total FROM tournaments WHERE status = 'completed'");
$stats['completed_tournaments'] = $result->fetch_assoc()['total'];

// 13. Active Promo
$result = $conn->query("SELECT COUNT(*) as total FROM promo WHERE aktif = 1 AND (tanggal_selesai IS NULL OR tanggal_selesai >= CURDATE())");
$stats['active_promo'] = $result->fetch_assoc()['total'];

// 14. Active Vouchers
$result = $conn->query("SELECT COUNT(*) as total FROM voucher WHERE aktif = 1 AND tanggal_selesai >= CURDATE()");
$stats['active_vouchers'] = $result->fetch_assoc()['total'];

// 15. Gallery Images
$result = $conn->query("SELECT COUNT(*) as total FROM gallery_images WHERE is_active = 1");
$stats['gallery_images'] = $result->fetch_assoc()['total'];



$recent_bookings = $conn->query("
    SELECT 
        b.*,
        u.nama as user_name,
        u.email as user_email,
        m.nama_meja,
        c.nama_cabang
    FROM booking b
    LEFT JOIN users u ON b.user_id = u.id
    LEFT JOIN meja m ON b.meja_id = m.id
    LEFT JOIN cabang c ON m.cabang_id = c.id
    ORDER BY b.created_at DESC
    LIMIT 15
");

$monthly_revenue = $conn->query("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        DATE_FORMAT(created_at, '%b %Y') as month_label,
        COALESCE(SUM(total_harga), 0) as revenue,
        COUNT(*) as bookings,
        COALESCE(SUM(CASE WHEN payment_status = 'paid' THEN total_harga ELSE 0 END), 0) as paid_revenue,
        COALESCE(SUM(CASE WHEN payment_status = 'unpaid' THEN total_harga ELSE 0 END), 0) as unpaid_revenue
    FROM booking 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    GROUP BY month, month_label
    ORDER BY month ASC
");

$revenue_data = [];
while ($row = $monthly_revenue->fetch_assoc()) {
    $revenue_data[] = $row;
}


$status_dist = $conn->query("
    SELECT 
        status,
        COUNT(*) as count,
        COALESCE(SUM(total_harga), 0) as total_value
    FROM booking
    GROUP BY status
    ORDER BY count DESC
");

$status_data = [];
while ($row = $status_dist->fetch_assoc()) {
    $status_data[] = $row;
}


$payment_dist = $conn->query("
    SELECT 
        payment_status,
        COUNT(*) as count,
        COALESCE(SUM(total_harga), 0) as total_value
    FROM booking
    GROUP BY payment_status
");

$payment_data = [];
while ($row = $payment_dist->fetch_assoc()) {
    $payment_data[] = $row;
}


$popular_tables = $conn->query("
    SELECT 
        m.nama_meja,
        c.nama_cabang,
        COUNT(b.id) as booking_count,
        COALESCE(SUM(b.total_harga), 0) as total_revenue
    FROM meja m
    LEFT JOIN booking b ON m.id = b.meja_id
    LEFT JOIN cabang c ON m.cabang_id = c.id
    GROUP BY m.id, m.nama_meja, c.nama_cabang
    ORDER BY booking_count DESC
    LIMIT 10
");

$popular_tables_data = [];
while ($row = $popular_tables->fetch_assoc()) {
    $popular_tables_data[] = $row;
}

$top_users = $conn->query("
    SELECT 
        u.nama,
        u.email,
        u.telepon,
        COUNT(b.id) as booking_count,
        COALESCE(SUM(b.total_harga), 0) as total_spent
    FROM users u
    LEFT JOIN booking b ON u.id = b.user_id
    WHERE u.role = 'user'
    GROUP BY u.id, u.nama, u.email, u.telepon
    HAVING booking_count > 0
    ORDER BY booking_count DESC
    LIMIT 5
");

$top_users_data = [];
while ($row = $top_users->fetch_assoc()) {
    $top_users_data[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="description" content="Legacy Billiard Admin Dashboard - Manage bookings, tables, tournaments and more">
    <meta name="author" content="Legacy Billiard">
    
    <title>Admin Dashboard - Legacy Billiard</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <!-- Chart.js -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.min.css">
    
    <!-- Custom Admin Styles -->
    <style>
      
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-gold: #D4AF37;
            --secondary-gold: #F5D068;
            --dark-bg: #0a0a0a;
            --card-bg: #1a1a2e;
            --card-bg-secondary: #16213e;
            --text-light: #fff;
            --text-muted: #b0b0b0;
            --text-dark: #888;
            --border-color: rgba(212, 175, 55, 0.2);
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
            --info: #17a2b8;
        }

        body {
            background: var(--dark-bg) !important;
            color: var(--text-light) !important;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
        }

      
        .admin-content {
            margin-left: 280px;
            padding: 2rem;
            min-height: 100vh;
            background: var(--dark-bg);
        }

        .admin-topbar {
            background: linear-gradient(135deg, var(--card-bg) 0%, var(--card-bg-secondary) 100%);
            border: 3px solid var(--primary-gold);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 15px 40px rgba(212, 175, 55, 0.3);
            position: relative;
            overflow: hidden;
        }

        .admin-topbar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at top right, rgba(212, 175, 55, 0.1), transparent 70%);
            pointer-events: none;
        }

        .admin-topbar-left {
            position: relative;
            z-index: 2;
        }

        .admin-topbar-left h1 {
            color: var(--primary-gold);
            font-weight: 900;
            font-size: 2.5rem;
            margin: 0 0 0.5rem 0;
            text-shadow: 0 0 30px rgba(212, 175, 55, 0.5);
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .admin-topbar-left h1 i {
            font-size: 2.8rem;
        }

        .admin-topbar-left p {
            color: var(--text-muted);
            margin: 0;
            font-size: 1.1rem;
            font-weight: 500;
        }

        .admin-topbar-right {
            text-align: right;
            position: relative;
            z-index: 2;
        }

        .current-time {
            color: var(--primary-gold);
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.3rem;
            font-family: 'Courier New', monospace;
            text-shadow: 0 0 20px rgba(212, 175, 55, 0.4);
        }

        .current-date {
            color: var(--text-muted);
            font-size: 0.95rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

   
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: linear-gradient(135deg, var(--card-bg) 0%, var(--card-bg-secondary) 100%);
            border: 3px solid var(--primary-gold);
            border-radius: 20px;
            padding: 2rem;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
            cursor: pointer;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(212, 175, 55, 0.15) 0%, transparent 70%);
            transition: transform 0.6s ease;
            pointer-events: none;
        }

        .stat-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 20px 50px rgba(212, 175, 55, 0.5);
            border-color: var(--secondary-gold);
        }

        .stat-card:hover::before {
            transform: rotate(45deg) scale(1.2);
        }

        .stat-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            position: relative;
            z-index: 2;
        }

        .stat-card-header h6 {
            color: var(--text-muted);
            font-size: 0.95rem;
            font-weight: 700;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }

        .stat-card-icon {
            width: 70px;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(212, 175, 55, 0.15);
            border-radius: 18px;
            border: 2px solid rgba(212, 175, 55, 0.4);
            transition: all 0.3s ease;
        }

        .stat-card:hover .stat-card-icon {
            background: rgba(212, 175, 55, 0.25);
            border-color: var(--primary-gold);
            transform: rotate(10deg) scale(1.1);
        }

        .stat-card-icon i {
            font-size: 2.2rem;
            color: var(--primary-gold);
            transition: all 0.3s ease;
        }

        .stat-card:hover .stat-card-icon i {
            color: var(--secondary-gold);
            transform: scale(1.1);
        }

        .stat-card-body {
            position: relative;
            z-index: 2;
        }

        .stat-card-value {
            font-size: 3.5rem;
            color: var(--primary-gold);
            font-weight: 900;
            margin-bottom: 0.5rem;
            text-shadow: 0 0 30px rgba(212, 175, 55, 0.4);
            line-height: 1;
            transition: all 0.3s ease;
        }

        .stat-card:hover .stat-card-value {
            color: var(--secondary-gold);
            text-shadow: 0 0 40px rgba(245, 208, 104, 0.6);
            transform: scale(1.05);
        }

        .stat-card-label {
            color: var(--text-dark);
            font-size: 0.9rem;
            font-weight: 600;
            line-height: 1.4;
        }

        .stat-card-footer {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(212, 175, 55, 0.2);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            color: var(--success);
            position: relative;
            z-index: 2;
        }

        .stat-card-footer i {
            font-size: 1rem;
        }

        .stat-card-footer.negative {
            color: var(--danger);
        }

 
        
        .charts-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .chart-container {
            background: linear-gradient(135deg, var(--card-bg) 0%, var(--card-bg-secondary) 100%);
            border: 3px solid var(--primary-gold);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.4);
            transition: all 0.3s ease;
        }

        .chart-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 50px rgba(212, 175, 55, 0.3);
        }

        .chart-container h3 {
            color: var(--primary-gold);
            font-weight: 700;
            margin-bottom: 2rem;
            font-size: 1.6rem;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            text-shadow: 0 0 20px rgba(212, 175, 55, 0.3);
        }

        .chart-container h3 i {
            font-size: 2rem;
        }

        .chart-wrapper {
            position: relative;
            height: 350px;
        }

        
        .data-table {
            background: linear-gradient(135deg, var(--card-bg) 0%, var(--card-bg-secondary) 100%);
            border: 3px solid var(--primary-gold);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.4);
        }

        .data-table h3 {
            color: var(--primary-gold);
            font-weight: 700;
            margin-bottom: 2rem;
            font-size: 1.6rem;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            text-shadow: 0 0 20px rgba(212, 175, 55, 0.3);
        }

        .data-table h3 i {
            font-size: 2rem;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .table {
            color: var(--text-light);
            margin: 0;
            border-collapse: separate;
            border-spacing: 0;
        }

        .table thead {
            border-bottom: 3px solid var(--primary-gold);
        }

        .table th {
            color: var(--primary-gold);
            font-weight: 700;
            padding: 1.2rem 1rem;
            border: none;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 1px;
            background: rgba(212, 175, 55, 0.05);
            white-space: nowrap;
        }

        .table tbody tr {
            transition: all 0.3s ease;
            border-bottom: 1px solid rgba(212, 175, 55, 0.1);
        }

        .table tbody tr:hover {
            background: rgba(212, 175, 55, 0.08);
            transform: scale(1.01);
        }

        .table td {
            padding: 1.2rem 1rem;
            border: none;
            vertical-align: middle;
            color: var(--text-muted);
        }

        .table td strong {
            color: var(--text-light);
            font-weight: 700;
        }

        .badge {
            padding: 0.6rem 1.2rem;
            border-radius: 50px;
            font-weight: 700;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-block;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
        }

        .badge:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.4);
        }

        .badge-pending {
            background: linear-gradient(135deg, #ffc107, #ffb300);
            color: #000;
        }

        .badge-confirmed {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: #fff;
        }

        .badge-cancelled {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: #fff;
        }

        .badge-completed {
            background: linear-gradient(135deg, #17a2b8, #138496);
            color: #fff;
        }

        .badge-paid {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: #fff;
        }

        .badge-unpaid {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: #fff;
        }

        
        .quick-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .quick-stat-item {
            background: linear-gradient(135deg, var(--card-bg) 0%, var(--card-bg-secondary) 100%);
            border: 2px solid var(--primary-gold);
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s ease;
        }

        .quick-stat-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(212, 175, 55, 0.4);
        }

        .quick-stat-item i {
            font-size: 2.5rem;
            color: var(--primary-gold);
            margin-bottom: 1rem;
        }

        .quick-stat-item h4 {
            color: var(--primary-gold);
            font-size: 2rem;
            font-weight: 900;
            margin-bottom: 0.5rem;
        }

        .quick-stat-item p {
            color: var(--text-muted);
            font-size: 0.85rem;
            margin: 0;
            font-weight: 600;
        }

        
        @media (max-width: 1200px) {
            .charts-row {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .admin-content {
                margin-left: 0;
                padding: 1rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .admin-topbar {
                flex-direction: column;
                text-align: center;
                gap: 1.5rem;
                padding: 1.5rem;
            }

            .admin-topbar-right {
                text-align: center;
            }

            .admin-topbar-left h1 {
                font-size: 2rem;
            }

            .quick-stats {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            }
        }

        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .stat-card {
            animation: fadeInUp 0.6s ease-out backwards;
        }

        .stat-card:nth-child(1) { animation-delay: 0.1s; }
        .stat-card:nth-child(2) { animation-delay: 0.2s; }
        .stat-card:nth-child(3) { animation-delay: 0.3s; }
        .stat-card:nth-child(4) { animation-delay: 0.4s; }
        .stat-card:nth-child(5) { animation-delay: 0.5s; }
        .stat-card:nth-child(6) { animation-delay: 0.6s; }
        .stat-card:nth-child(7) { animation-delay: 0.7s; }
        .stat-card:nth-child(8) { animation-delay: 0.8s; }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }

        
        .loading {
            text-align: center;
            padding: 3rem;
            color: var(--text-muted);
        }

        .loading i {
            font-size: 3rem;
            color: var(--primary-gold);
            animation: pulse 1.5s ease-in-out infinite;
        }


        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--text-dark);
        }

        .empty-state i {
            font-size: 4rem;
            color: var(--primary-gold);
            margin-bottom: 1.5rem;
            display: block;
            opacity: 0.5;
        }

        .empty-state h5 {
            color: var(--text-muted);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            color: var(--text-dark);
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <!-- SIDEBAR -->
    <?php include 'admin_sidebar.php'; ?>

    <!-- MAIN CONTENT -->
    <div class="admin-content">
        <!-- TOP BAR -->
        <div class="admin-topbar">
            <div class="admin-topbar-left">
                <h1>
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard Overview
                </h1>
                <p>Welcome back, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>! Here's what's happening today.</p>
            </div>
            <div class="admin-topbar-right">
                <div class="current-time" id="currentTime">--:--:--</div>
                <div class="current-date" id="currentDate">Loading...</div>
            </div>
        </div>

        <!-- QUICK STATISTICS -->
        <div class="quick-stats">
            <div class="quick-stat-item">
                <i class="fas fa-calendar-day"></i>
                <h4><?= number_format($stats['today_bookings']) ?></h4>
                <p>Today's Bookings</p>
            </div>
            <div class="quick-stat-item">
                <i class="fas fa-money-bill-wave"></i>
                <h4>Rp <?= number_format($stats['today_revenue'] / 1000, 0) ?>K</h4>
                <p>Today's Revenue</p>
            </div>
            <div class="quick-stat-item">
                <i class="fas fa-clock"></i>
                <h4><?= number_format($stats['pending_bookings']) ?></h4>
                <p>Pending Bookings</p>
            </div>
            <div class="quick-stat-item">
                <i class="fas fa-check-circle"></i>
                <h4><?= number_format($stats['confirmed_bookings']) ?></h4>
                <p>Confirmed Bookings</p>
            </div>
            <div class="quick-stat-item">
                <i class="fas fa-table"></i>
                <h4><?= number_format($stats['available_tables']) ?></h4>
                <p>Available Tables</p>
            </div>
        </div>

        <!-- MAIN STATISTICS GRID -->
        <div class="stats-grid">
            <!-- Total Users -->
            <div class="stat-card">
                <div class="stat-card-header">
                    <h6>Total Users</h6>
                    <div class="stat-card-icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <div class="stat-card-body">
                    <div class="stat-card-value"><?= number_format($stats['total_users']) ?></div>
                    <div class="stat-card-label">Registered Members</div>
                </div>
                <div class="stat-card-footer">
                    <i class="fas fa-arrow-up"></i>
                    <span>Active community</span>
                </div>
            </div>

            <!-- Total Bookings -->
            <div class="stat-card">
                <div class="stat-card-header">
                    <h6>Total Bookings</h6>
                    <div class="stat-card-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                </div>
                <div class="stat-card-body">
                    <div class="stat-card-value"><?= number_format($stats['total_bookings']) ?></div>
                    <div class="stat-card-label">All Time Reservations</div>
                </div>
                <div class="stat-card-footer">
                    <i class="fas fa-chart-line"></i>
                    <span>Growing steadily</span>
                </div>
            </div>

            <!-- Total Revenue -->
            <div class="stat-card">
                <div class="stat-card-header">
                    <h6>Total Revenue</h6>
                    <div class="stat-card-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                </div>
                <div class="stat-card-body">
                    <div class="stat-card-value">Rp <?= number_format($stats['total_revenue'], 0, ',', '.') ?></div>
                    <div class="stat-card-label">Paid Transactions</div>
                </div>
                <div class="stat-card-footer">
                    <i class="fas fa-arrow-up"></i>
                    <span>+15% from last month</span>
                </div>
            </div>

            <!-- Total Tables -->
            <div class="stat-card">
                <div class="stat-card-header">
                    <h6>Total Tables</h6>
                    <div class="stat-card-icon">
                        <i class="fas fa-table"></i>
                    </div>
                </div>
                <div class="stat-card-body">
                    <div class="stat-card-value"><?= number_format($stats['total_tables']) ?></div>
                    <div class="stat-card-label"><?= $stats['available_tables'] ?> Available, <?= $stats['occupied_tables'] ?> Occupied</div>
                </div>
                <div class="stat-card-footer">
                    <i class="fas fa-check"></i>
                    <span>Ready to serve</span>
                </div>
            </div>

            <!-- Active Tournaments -->
            <div class="stat-card">
                <div class="stat-card-header">
                    <h6>Tournaments</h6>
                    <div class="stat-card-icon">
                        <i class="fas fa-trophy"></i>
                    </div>
                </div>
                <div class="stat-card-body">
                    <div class="stat-card-value"><?= number_format($stats['active_tournaments']) ?></div>
                    <div class="stat-card-label"><?= $stats['completed_tournaments'] ?> Completed</div>
                </div>
                <div class="stat-card-footer">
                    <i class="fas fa-gamepad"></i>
                    <span>Ongoing competitions</span>
                </div>
            </div>

            <!-- Active Promotions -->
            <div class="stat-card">
                <div class="stat-card-header">
                    <h6>Promotions</h6>
                    <div class="stat-card-icon">
                        <i class="fas fa-tags"></i>
                    </div>
                </div>
                <div class="stat-card-body">
                    <div class="stat-card-value"><?= number_format($stats['active_promo']) ?></div>
                    <div class="stat-card-label">Active Promotions</div>
                </div>
                <div class="stat-card-footer">
                    <i class="fas fa-percent"></i>
                    <span>Special offers</span>
                </div>
            </div>

            <!-- Active Vouchers -->
            <div class="stat-card">
                <div class="stat-card-header">
                    <h6>Vouchers</h6>
                    <div class="stat-card-icon">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                </div>
                <div class="stat-card-body">
                    <div class="stat-card-value"><?= number_format($stats['active_vouchers']) ?></div>
                    <div class="stat-card-label">Valid Vouchers</div>
                </div>
                <div class="stat-card-footer">
                    <i class="fas fa-gift"></i>
                    <span>Available discounts</span>
                </div>
            </div>

            <!-- Gallery Images -->
            <div class="stat-card">
                <div class="stat-card-header">
                    <h6>Gallery</h6>
                    <div class="stat-card-icon">
                        <i class="fas fa-images"></i>
                    </div>
                </div>
                <div class="stat-card-body">
                    <div class="stat-card-value"><?= number_format($stats['gallery_images']) ?></div>
                    <div class="stat-card-label">Active Images</div>
                </div>
                <div class="stat-card-footer">
                    <i class="fas fa-camera"></i>
                    <span>Visual content</span>
                </div>
            </div>
        </div>

        <!-- CHARTS ROW -->
        <div class="charts-row">
            <!-- Revenue Chart -->
            <div class="chart-container">
                <h3>
                    <i class="fas fa-chart-line"></i>
                    Revenue Trend (Last 12 Months)
                </h3>
                <div class="chart-wrapper">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            <!-- Status Distribution Chart -->
            <div class="chart-container">
                <h3>
                    <i class="fas fa-chart-pie"></i>
                    Booking Status Distribution
                </h3>
                <div class="chart-wrapper">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>

        <!-- CHARTS ROW 2 -->
        <div class="charts-row">
            <!-- Payment Distribution Chart -->
            <div class="chart-container">
                <h3>
                    <i class="fas fa-chart-donut"></i>
                    Payment Status Distribution
                </h3>
                <div class="chart-wrapper">
                    <canvas id="paymentChart"></canvas>
                </div>
            </div>

            <!-- Popular Tables Chart -->
            <div class="chart-container">
                <h3>
                    <i class="fas fa-chart-bar"></i>
                    Popular Tables (Top 10)
                </h3>
                <div class="chart-wrapper">
                    <canvas id="tablesChart"></canvas>
                </div>
            </div>
        </div>

        <!-- TOP USERS TABLE -->
        <div class="data-table">
            <h3>
                <i class="fas fa-star"></i>
                Top Users by Bookings
            </h3>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Total Bookings</th>
                            <th>Total Spent</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($top_users_data) > 0): ?>
                            <?php 
                            $rank = 1;
                            foreach ($top_users_data as $user): 
                            ?>
                            <tr>
                                <td>
                                    <strong style="color: var(--primary-gold);">#<?= $rank ?></strong>
                                </td>
                                <td><strong><?= htmlspecialchars($user['nama']) ?></strong></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td><?= htmlspecialchars($user['telepon'] ?? '-') ?></td>
                                <td><strong style="color: var(--primary-gold);"><?= number_format($user['booking_count']) ?></strong></td>
                                <td><strong>Rp <?= number_format($user['total_spent'], 0, ',', '.') ?></strong></td>
                            </tr>
                            <?php 
                            $rank++;
                            endforeach; 
                            ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="empty-state">
                                    <i class="fas fa-user-slash"></i>
                                    <h5>No User Data Available</h5>
                                    <p>There are no users with bookings yet</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- RECENT BOOKINGS TABLE -->
        <div class="data-table">
            <h3>
                <i class="fas fa-history"></i>
                Recent Bookings (Last 15)
            </h3>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Email</th>
                            <th>Cabang</th>
                            <th>Meja</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Duration</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Payment</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($recent_bookings->num_rows > 0): ?>
                            <?php while ($booking = $recent_bookings->fetch_assoc()): ?>
                            <tr>
                                <td><strong style="color: var(--primary-gold);">#<?= $booking['id'] ?></strong></td>
                                <td><strong><?= htmlspecialchars($booking['user_name'] ?? 'Guest') ?></strong></td>
                                <td><?= htmlspecialchars($booking['user_email'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($booking['nama_cabang'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($booking['nama_meja']) ?></td>
                                <td><?= date('d M Y', strtotime($booking['tanggal_booking'])) ?></td>
                                <td><?= date('H:i', strtotime($booking['waktu_mulai'])) ?></td>
                                <td><?= $booking['durasi_jam'] ?> jam</td>
                                <td><strong>Rp <?= number_format($booking['total_harga'], 0, ',', '.') ?></strong></td>
                                <td>
                                    <span class="badge badge-<?= $booking['status'] ?>">
                                        <?= ucfirst($booking['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-<?= $booking['payment_status'] ?>">
                                        <?= ucfirst($booking['payment_status']) ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="11" class="empty-state">
                                    <i class="fas fa-inbox"></i>
                                    <h5>No Recent Bookings</h5>
                                    <p>There are no bookings to display yet</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    <!-- Custom Scripts -->
    <script>
        // ============================================
        // REAL-TIME CLOCK
        // ============================================
        
        function updateClock() {
            const now = new Date();
            
            // Format time: HH:MM:SS
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            const timeString = `${hours}:${minutes}:${seconds}`;
            
            // Format date: Day, DD Month YYYY
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            const dateString = now.toLocaleDateString('id-ID', options);
            
            // Update DOM
            document.getElementById('currentTime').textContent = timeString;
            document.getElementById('currentDate').textContent = dateString;
        }
        
        // Initialize clock
        updateClock();
        setInterval(updateClock, 1000);

        // ============================================
        // REVENUE CHART
        // ============================================
        
        const revenueData = <?= json_encode($revenue_data) ?>;
        
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        const revenueChart = new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: revenueData.map(d => d.month_label),
                datasets: [
                    {
                        label: 'Total Revenue',
                        data: revenueData.map(d => d.revenue),
                        borderColor: '#D4AF37',
                        backgroundColor: 'rgba(212, 175, 55, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 6,
                        pointBackgroundColor: '#D4AF37',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointHoverRadius: 9,
                        pointHoverBackgroundColor: '#F5D068',
                        pointHoverBorderWidth: 3
                    },
                    {
                        label: 'Paid Revenue',
                        data: revenueData.map(d => d.paid_revenue),
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointBackgroundColor: '#28a745',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            color: '#D4AF37',
                            font: {
                                size: 13,
                                weight: 'bold'
                            },
                            padding: 20,
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        backgroundColor: '#1a1a2e',
                        titleColor: '#D4AF37',
                        bodyColor: '#fff',
                        borderColor: '#D4AF37',
                        borderWidth: 2,
                        padding: 15,
                        displayColors: true,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: '#b0b0b0',
                            font: {
                                size: 11,
                                weight: 'bold'
                            },
                            callback: function(value) {
                                return 'Rp ' + (value / 1000000).toFixed(1) + 'M';
                            }
                        },
                        grid: {
                            color: 'rgba(212, 175, 55, 0.1)',
                            borderColor: 'rgba(212, 175, 55, 0.2)'
                        }
                    },
                    x: {
                        ticks: {
                            color: '#b0b0b0',
                            font: {
                                size: 11,
                                weight: 'bold'
                            }
                        },
                        grid: {
                            color: 'rgba(212, 175, 55, 0.05)',
                            borderColor: 'rgba(212, 175, 55, 0.2)'
                        }
                    }
                }
            }
        });

        // ============================================
        // BOOKING STATUS CHART
        // ============================================
        
        const statusData = <?= json_encode($status_data) ?>;
        
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        const statusChart = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: statusData.map(d => d.status.charAt(0).toUpperCase() + d.status.slice(1)),
                datasets: [{
                    data: statusData.map(d => d.count),
                    backgroundColor: [
                        '#ffc107',
                        '#28a745',
                        '#dc3545',
                        '#17a2b8'
                    ],
                    borderColor: '#1a1a2e',
                    borderWidth: 4,
                    hoverOffset: 20
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#b0b0b0',
                            padding: 20,
                            font: {
                                size: 12,
                                weight: 'bold'
                            },
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    tooltip: {
                        backgroundColor: '#1a1a2e',
                        titleColor: '#D4AF37',
                        bodyColor: '#fff',
                        borderColor: '#D4AF37',
                        borderWidth: 2,
                        padding: 15,
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                let value = context.parsed || 0;
                                let total = context.dataset.data.reduce((a, b) => a + b, 0);
                                let percentage = ((value / total) * 100).toFixed(1);
                                return label + ': ' + value + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });

        // ============================================
        // PAYMENT STATUS CHART
        // ============================================
        
        const paymentData = <?= json_encode($payment_data) ?>;
        
        const paymentCtx = document.getElementById('paymentChart').getContext('2d');
        const paymentChart = new Chart(paymentCtx, {
            type: 'doughnut',
            data: {
                labels: paymentData.map(d => d.payment_status.charAt(0).toUpperCase() + d.payment_status.slice(1)),
                datasets: [{
                    data: paymentData.map(d => d.count),
                    backgroundColor: [
                        '#28a745',
                        '#dc3545'
                    ],
                    borderColor: '#1a1a2e',
                    borderWidth: 4,
                    hoverOffset: 20
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#b0b0b0',
                            padding: 20,
                            font: {
                                size: 12,
                                weight: 'bold'
                            },
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    tooltip: {
                        backgroundColor: '#1a1a2e',
                        titleColor: '#D4AF37',
                        bodyColor: '#fff',
                        borderColor: '#D4AF37',
                        borderWidth: 2,
                        padding: 15,
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                let value = context.parsed || 0;
                                let total = context.dataset.data.reduce((a, b) => a + b, 0);
                                let percentage = ((value / total) * 100).toFixed(1);
                                return label + ': ' + value + ' (' + percentage + '%)';
                            },
                            afterLabel: function(context) {
                                let index = context.dataIndex;
                                let totalValue = paymentData[index].total_value;
                                return 'Total: Rp ' + totalValue.toLocaleString('id-ID');
                            }
                        }
                    }
                }
            }
        });

        // ============================================
        // POPULAR TABLES CHART
        // ============================================
        
        const tablesData = <?= json_encode($popular_tables_data) ?>;
        
        const tablesCtx = document.getElementById('tablesChart').getContext('2d');
        const tablesChart = new Chart(tablesCtx, {
            type: 'bar',
            data: {
                labels: tablesData.map(d => d.nama_meja + ' (' + d.nama_cabang + ')'),
                datasets: [{
                    label: 'Number of Bookings',
                    data: tablesData.map(d => d.booking_count),
                    backgroundColor: 'rgba(212, 175, 55, 0.8)',
                    borderColor: '#D4AF37',
                    borderWidth: 2,
                    borderRadius: 8,
                    hoverBackgroundColor: '#F5D068',
                    hoverBorderColor: '#F5D068'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#1a1a2e',
                        titleColor: '#D4AF37',
                        bodyColor: '#fff',
                        borderColor: '#D4AF37',
                        borderWidth: 2,
                        padding: 15,
                        callbacks: {
                            label: function(context) {
                                let label = 'Bookings: ' + context.parsed.y;
                                return label;
                            },
                            afterLabel: function(context) {
                                let index = context.dataIndex;
                                let revenue = tablesData[index].total_revenue;
                                return 'Revenue: Rp ' + revenue.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: '#b0b0b0',
                            font: {
                                size: 11,
                                weight: 'bold'
                            },
                            stepSize: 1
                        },
                        grid: {
                            color: 'rgba(212, 175, 55, 0.1)',
                            borderColor: 'rgba(212, 175, 55, 0.2)'
                        }
                    },
                    x: {
                        ticks: {
                            color: '#b0b0b0',
                            font: {
                                size: 10,
                                weight: 'bold'
                            },
                            maxRotation: 45,
                            minRotation: 45
                        },
                        grid: {
                            display: false,
                            borderColor: 'rgba(212, 175, 55, 0.2)'
                        }
                    }
                }
            }
        });

      
        
        console.log('%c LEGACY BILLIARD ADMIN DASHBOARD ', 'background: #D4AF37; color: #000; font-size: 20px; font-weight: bold; padding: 10px;');
        console.log('%c Dashboard loaded successfully! ', 'background: #28a745; color: #fff; font-size: 14px; padding: 5px;');
        console.log('%c All charts rendered successfully! ', 'background: #17a2b8; color: #fff; font-size: 14px; padding: 5px;');
    </script>
</body>
</html>
