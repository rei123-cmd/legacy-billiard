<?php
// ============================================
// TOURNAMENT PAGE - COMPLETE VERSION
// Legacy Billiard Tournament System
// ============================================

require_once 'config.php';

// ============================================
// SESSION & USER CHECK
// ============================================

$is_logged_in = isset($_SESSION['user_id']);
$user_id = $is_logged_in ? $_SESSION['user_id'] : null;
$user_name = $is_logged_in ? $_SESSION['username'] : null;
$user_role = isset($_SESSION['role']) ? $_SESSION['role'] : 'guest';

// ============================================
// HANDLE AJAX REQUESTS
// ============================================

if (isset($_GET['action']) && $_GET['action'] === 'register' && $is_logged_in) {
    header('Content-Type: application/json');
    
    $tournament_id = isset($_POST['tournament_id']) ? intval($_POST['tournament_id']) : 0;
    
    if ($tournament_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid tournament ID']);
        exit;
    }
    
    // Check if tournament exists and is open
    $check_tournament = $conn->prepare("SELECT * FROM tournaments WHERE id = ? AND status = 'open'");
    $check_tournament->bind_param("i", $tournament_id);
    $check_tournament->execute();
    $tournament = $check_tournament->get_result()->fetch_assoc();
    
    if (!$tournament) {
        echo json_encode(['success' => false, 'message' => 'Tournament not found or not open for registration']);
        exit;
    }
    
    // Check if user already registered
    $check_registered = $conn->prepare("SELECT id FROM tournament_participants WHERE tournament_id = ? AND user_id = ?");
    $check_registered->bind_param("ii", $tournament_id, $user_id);
    $check_registered->execute();
    
    if ($check_registered->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'You are already registered for this tournament']);
        exit;
    }
    
    // Check if tournament is full
    $count_participants = $conn->prepare("SELECT COUNT(*) as total FROM tournament_participants WHERE tournament_id = ?");
    $count_participants->bind_param("i", $tournament_id);
    $count_participants->execute();
    $participant_count = $count_participants->get_result()->fetch_assoc()['total'];
    
    if ($participant_count >= $tournament['max_peserta']) {
        echo json_encode(['success' => false, 'message' => 'Tournament is full']);
        exit;
    }
    
    // Determine pool assignment (A or B)
    $pool = ($participant_count % 2 == 0) ? 'A' : 'B';
    $seed_position = floor($participant_count / 2) + 1;
    
    // Register user
    $register = $conn->prepare("INSERT INTO tournament_participants (tournament_id, user_id, pool, seed_position, status) VALUES (?, ?, ?, ?, 'active')");
    $register->bind_param("iisi", $tournament_id, $user_id, $pool, $seed_position);
    
    if ($register->execute()) {
        // Send notification
        $notification_message = "You have successfully registered for " . $tournament['nama_tournament'];
        $send_notification = $conn->prepare("INSERT INTO tournament_notifications (user_id, tournament_id, type, message) VALUES (?, ?, 'registration', ?)");
        $send_notification->bind_param("iis", $user_id, $tournament_id, $notification_message);
        $send_notification->execute();
        
        echo json_encode(['success' => true, 'message' => 'Successfully registered for tournament!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Registration failed. Please try again.']);
    }
    exit;
}

// ============================================
// FILTER PARAMETERS
// ============================================

$filter_status = isset($_GET['status']) ? $_GET['status'] : 'all';
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'date_desc';

// ============================================
// BUILD TOURNAMENT QUERY WITH FILTERS
// ============================================

$where_conditions = [];
$params = [];
$types = '';

// Status filter
if ($filter_status !== 'all') {
    $where_conditions[] = "t.status = ?";
    $params[] = $filter_status;
    $types .= 's';
}

// Search filter
if (!empty($search_query)) {
    $where_conditions[] = "(t.nama_tournament LIKE ? OR t.deskripsi LIKE ?)";
    $search_param = "%{$search_query}%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ss';
}

// Build WHERE clause
$where_clause = '';
if (count($where_conditions) > 0) {
    $where_clause = "WHERE " . implode(" AND ", $where_conditions);
}

// Build ORDER BY clause
$order_clause = '';
switch ($sort_by) {
    case 'date_asc':
        $order_clause = "ORDER BY t.tanggal_mulai ASC";
        break;
    case 'date_desc':
        $order_clause = "ORDER BY t.tanggal_mulai DESC";
        break;
    case 'name_asc':
        $order_clause = "ORDER BY t.nama_tournament ASC";
        break;
    case 'name_desc':
        $order_clause = "ORDER BY t.nama_tournament DESC";
        break;
    case 'participants':
        $order_clause = "ORDER BY current_peserta DESC";
        break;
    case 'status':
        $order_clause = "ORDER BY CASE 
            WHEN t.status = 'open' THEN 1
            WHEN t.status = 'ongoing' THEN 2
            WHEN t.status = 'completed' THEN 3
            ELSE 4
        END, t.tanggal_mulai ASC";
        break;
    default:
        $order_clause = "ORDER BY CASE 
            WHEN t.status = 'open' THEN 1
            WHEN t.status = 'ongoing' THEN 2
            WHEN t.status = 'completed' THEN 3
            ELSE 4
        END, t.tanggal_mulai DESC";
}

// Main query
$tournaments_query = "
    SELECT 
        t.*,
        COUNT(DISTINCT tp.id) as current_peserta,
        (
            SELECT COUNT(*) 
            FROM tournament_participants tp2 
            WHERE tp2.tournament_id = t.id 
            AND tp2.status IN ('active', 'winner', 'finalist')
        ) as active_participants,
        (
            SELECT u.nama
            FROM users u
            WHERE u.id = t.created_by
        ) as organizer_name
    FROM tournaments t
    LEFT JOIN tournament_participants tp ON t.id = tp.tournament_id
    {$where_clause}
    GROUP BY t.id
    {$order_clause}
";

if (count($params) > 0) {
    $stmt = $conn->prepare($tournaments_query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $tournaments = $stmt->get_result();
} else {
    $tournaments = $conn->query($tournaments_query);
}

// ============================================
// GET USER'S TOURNAMENT REGISTRATIONS
// ============================================

$user_tournaments = [];
if ($is_logged_in) {
    $user_tournaments_query = "
        SELECT 
            tp.*,
            t.nama_tournament,
            t.status as tournament_status
        FROM tournament_participants tp
        JOIN tournaments t ON tp.tournament_id = t.id
        WHERE tp.user_id = ?
    ";
    $stmt = $conn->prepare($user_tournaments_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $user_tournaments[$row['tournament_id']] = $row;
    }
    $stmt->close();
}

// ============================================
// GET STATISTICS
// ============================================

$stats = [];

// Total tournaments
$stats['total'] = $conn->query("SELECT COUNT(*) as count FROM tournaments")->fetch_assoc()['count'];

// Open tournaments
$stats['open'] = $conn->query("SELECT COUNT(*) as count FROM tournaments WHERE status = 'open'")->fetch_assoc()['count'];

// Ongoing tournaments
$stats['ongoing'] = $conn->query("SELECT COUNT(*) as count FROM tournaments WHERE status = 'ongoing'")->fetch_assoc()['count'];

// Completed tournaments
$stats['completed'] = $conn->query("SELECT COUNT(*) as count FROM tournaments WHERE status = 'completed'")->fetch_assoc()['count'];

// Total participants
$stats['participants'] = $conn->query("SELECT COUNT(DISTINCT user_id) as count FROM tournament_participants")->fetch_assoc()['count'];

// User's registrations
$stats['user_registrations'] = 0;
if ($is_logged_in) {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM tournament_participants WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stats['user_registrations'] = $stmt->get_result()->fetch_assoc()['count'];
    $stmt->close();
}

// ============================================
// GET FEATURED TOURNAMENT (Next upcoming open tournament)
// ============================================

$featured_tournament = null;
$featured_query = "
    SELECT 
        t.*,
        COUNT(DISTINCT tp.id) as current_peserta
    FROM tournaments t
    LEFT JOIN tournament_participants tp ON t.id = tp.tournament_id
    WHERE t.status = 'open' AND t.tanggal_mulai >= CURDATE()
    GROUP BY t.id
    ORDER BY t.tanggal_mulai ASC
    LIMIT 1
";
$featured_result = $conn->query($featured_query);
if ($featured_result && $featured_result->num_rows > 0) {
    $featured_tournament = $featured_result->fetch_assoc();
}

// ============================================
// GET RECENT WINNERS (From completed tournaments)
// ============================================

$recent_winners = [];
$winners_query = "
    SELECT 
        u.nama,
        u.foto_profil,
        t.nama_tournament,
        t.hadiah,
        tp.registered_at as win_date
    FROM tournament_participants tp
    JOIN users u ON tp.user_id = u.id
    JOIN tournaments t ON tp.tournament_id = t.id
    WHERE tp.status = 'winner' AND t.status = 'completed'
    ORDER BY tp.registered_at DESC
    LIMIT 5
";
$winners_result = $conn->query($winners_query);
if ($winners_result) {
    while ($row = $winners_result->fetch_assoc()) {
        $recent_winners[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="description" content="Join Legacy Billiard's competitive tournaments and showcase your skills. Register now for upcoming billiard tournaments with exciting prizes!">
    <meta name="keywords" content="billiard tournament, pool tournament, billiard competition, legacy billiard">
    
    <title>Tournaments - Legacy Billiard | Competitive Billiard Tournaments</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
    
    <style>
        /* ============================================
           TOURNAMENT PAGE STYLES - ULTRA COMPLETE
           ============================================ */
        
        :root {
            --primary-gold: #D4AF37;
            --secondary-gold: #F5D068;
            --dark-bg: #0a0a0a;
            --card-bg: #1a1a2e;
            --card-bg-secondary: #16213e;
            --text-light: #fff;
            --text-muted: #b0b0b0;
            --text-dark: #888;
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
            --info: #17a2b8;
        }

        body {
            background: var(--dark-bg);
            color: var(--text-light);
        }

        /* ============================================
           HERO SECTION
           ============================================ */
        
        .tournament-hero {
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.95), rgba(26, 26, 46, 0.95)), url('images/bgsemua.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            padding: 10rem 0 6rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .tournament-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at center, rgba(212, 175, 55, 0.1), transparent 70%);
            animation: pulse-bg 3s ease-in-out infinite;
        }

        @keyframes pulse-bg {
            0%, 100% { opacity: 0.5; }
            50% { opacity: 1; }
        }

        .tournament-hero-content {
            position: relative;
            z-index: 2;
        }

        .tournament-hero h1 {
            font-size: 5rem;
            font-weight: 900;
            color: var(--primary-gold);
            margin-bottom: 1.5rem;
            text-shadow: 0 0 40px rgba(212, 175, 55, 0.8);
            animation: fadeInDown 1s ease-out;
        }

        .tournament-hero h1 i {
            margin-right: 1rem;
            animation: bounce 2s ease-in-out infinite;
        }

        .tournament-hero p {
            font-size: 1.5rem;
            color: var(--text-muted);
            max-width: 700px;
            margin: 0 auto 2rem;
            line-height: 1.8;
            animation: fadeInUp 1s ease-out 0.3s backwards;
        }

        .hero-stats {
            display: flex;
            justify-content: center;
            gap: 3rem;
            margin-top: 3rem;
            animation: fadeInUp 1s ease-out 0.6s backwards;
        }

        .hero-stat-item {
            text-align: center;
        }

        .hero-stat-value {
            font-size: 3rem;
            font-weight: 900;
            color: var(--primary-gold);
            text-shadow: 0 0 20px rgba(212, 175, 55, 0.5);
        }

        .hero-stat-label {
            font-size: 0.9rem;
            color: var(--text-dark);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 0.5rem;
        }

        /* ============================================
           FEATURED TOURNAMENT
           ============================================ */
        
        .featured-section {
            padding: 4rem 0;
            background: linear-gradient(180deg, var(--dark-bg) 0%, var(--card-bg) 100%);
        }

        .featured-tournament {
            background: linear-gradient(135deg, var(--card-bg) 0%, var(--card-bg-secondary) 100%);
            border: 4px solid var(--primary-gold);
            border-radius: 30px;
            overflow: hidden;
            box-shadow: 0 30px 60px rgba(212, 175, 55, 0.4);
            position: relative;
        }

        .featured-tournament::before {
            content: 'FEATURED';
            position: absolute;
            top: 2rem;
            right: -3rem;
            background: linear-gradient(135deg, var(--primary-gold), var(--secondary-gold));
            color: #000;
            padding: 0.5rem 4rem;
            font-weight: 900;
            font-size: 0.9rem;
            letter-spacing: 2px;
            transform: rotate(45deg);
            box-shadow: 0 5px 15px rgba(212, 175, 55, 0.5);
            z-index: 3;
        }

        .featured-banner {
            height: 400px;
            background: linear-gradient(135deg, rgba(212, 175, 55, 0.3), rgba(212, 175, 55, 0.1));
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .featured-banner img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .featured-banner::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.3));
        }

        .featured-banner i {
            font-size: 8rem;
            color: var(--primary-gold);
            z-index: 2;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }

        .featured-content {
            padding: 3rem;
        }

        .featured-content h2 {
            font-size: 3rem;
            color: var(--primary-gold);
            font-weight: 900;
            margin-bottom: 1.5rem;
        }

        .featured-content p {
            font-size: 1.2rem;
            color: var(--text-muted);
            margin-bottom: 2rem;
            line-height: 1.8;
        }

        .featured-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .featured-detail-item {
            text-align: center;
            padding: 1.5rem;
            background: rgba(212, 175, 55, 0.1);
            border-radius: 15px;
            border: 2px solid rgba(212, 175, 55, 0.3);
        }

        .featured-detail-item i {
            font-size: 2.5rem;
            color: var(--primary-gold);
            margin-bottom: 1rem;
        }

        .featured-detail-item h4 {
            font-size: 1.8rem;
            color: var(--primary-gold);
            font-weight: 900;
            margin-bottom: 0.5rem;
        }

        .featured-detail-item p {
            color: var(--text-dark);
            margin: 0;
            font-size: 0.9rem;
        }

        /* ============================================
           FILTERS & SEARCH
           ============================================ */
        
        .filters-section {
            padding: 3rem 0;
            background: var(--card-bg);
            border-bottom: 2px solid rgba(212, 175, 55, 0.2);
        }

        .filters-container {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            align-items: center;
            justify-content: space-between;
        }

        .filter-group {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .filter-btn {
            padding: 0.8rem 1.5rem;
            background: rgba(212, 175, 55, 0.1);
            border: 2px solid rgba(212, 175, 55, 0.3);
            border-radius: 50px;
            color: var(--text-muted);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .filter-btn:hover,
        .filter-btn.active {
            background: linear-gradient(135deg, var(--primary-gold), var(--secondary-gold));
            color: #000;
            border-color: var(--primary-gold);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(212, 175, 55, 0.4);
        }

        .search-box {
            position: relative;
            flex: 1;
            max-width: 400px;
        }

        .search-box input {
            width: 100%;
            padding: 0.8rem 1.5rem 0.8rem 3rem;
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(212, 175, 55, 0.3);
            border-radius: 50px;
            color: var(--text-light);
            font-size: 1rem;
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--primary-gold);
            box-shadow: 0 0 20px rgba(212, 175, 55, 0.3);
        }

        .search-box i {
            position: absolute;
            left: 1.2rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary-gold);
        }

        .sort-dropdown {
            padding: 0.8rem 1.5rem;
            background: rgba(212, 175, 55, 0.1);
            border: 2px solid rgba(212, 175, 55, 0.3);
            border-radius: 50px;
            color: var(--text-light);
            font-weight: 600;
            cursor: pointer;
        }

        /* ============================================
           TOURNAMENTS GRID
           ============================================ */
        
        .tournaments-container {
            padding: 4rem 0;
            background: var(--dark-bg);
        }

        .section-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .section-header h2 {
            font-size: 3rem;
            color: var(--primary-gold);
            font-weight: 900;
            margin-bottom: 1rem;
        }

        .section-header p {
            color: var(--text-muted);
            font-size: 1.2rem;
        }

        .tournaments-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
            gap: 2.5rem;
            margin-top: 3rem;
        }

        .tournament-card {
            background: linear-gradient(135deg, var(--card-bg) 0%, var(--card-bg-secondary) 100%);
            border: 3px solid var(--primary-gold);
            border-radius: 25px;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            cursor: pointer;
        }

        .tournament-card:hover {
            transform: translateY(-15px) scale(1.02);
            box-shadow: 0 25px 60px rgba(212, 175, 55, 0.5);
            border-color: var(--secondary-gold);
        }

        .tournament-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at top right, rgba(212, 175, 55, 0.15), transparent 60%);
            opacity: 0;
            transition: opacity 0.4s ease;
            pointer-events: none;
        }

        .tournament-card:hover::before {
            opacity: 1;
        }

        .tournament-banner {
            height: 220px;
            background: linear-gradient(135deg, rgba(212, 175, 55, 0.3), rgba(212, 175, 55, 0.1));
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .tournament-banner img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .tournament-card:hover .tournament-banner img {
            transform: scale(1.1);
        }

        .tournament-banner::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.3));
        }

        .tournament-banner i {
            font-size: 6rem;
            color: var(--primary-gold);
            z-index: 2;
            transition: all 0.4s ease;
        }

        .tournament-card:hover .tournament-banner i {
            transform: scale(1.2) rotate(10deg);
        }

        .tournament-status {
            position: absolute;
            top: 1.5rem;
            right: 1.5rem;
            padding: 0.6rem 1.3rem;
            border-radius: 50px;
            font-weight: 700;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            z-index: 3;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.5);
        }

        .status-open {
            background: linear-gradient(135deg, var(--success), #20c997);
            color: #fff;
            animation: pulse-status 2s ease-in-out infinite;
        }

        @keyframes pulse-status {
            0%, 100% { 
                transform: scale(1);
                box-shadow: 0 5px 20px rgba(40, 167, 69, 0.4);
            }
            50% { 
                transform: scale(1.05);
                box-shadow: 0 8px 25px rgba(40, 167, 69, 0.6);
            }
        }

        .status-ongoing {
            background: linear-gradient(135deg, var(--warning), #ffb300);
            color: #000;
        }

        .status-completed {
            background: linear-gradient(135deg, #6c757d, #5a6268);
            color: #fff;
        }

        .status-cancelled {
            background: linear-gradient(135deg, var(--danger), #c82333);
            color: #fff;
        }

        .tournament-body {
            padding: 2.5rem;
        }

        .tournament-body h3 {
            color: var(--primary-gold);
            font-size: 2rem;
            font-weight: 900;
            margin-bottom: 1rem;
            line-height: 1.3;
        }

        .tournament-body p {
            color: var(--text-muted);
            margin-bottom: 1.5rem;
            line-height: 1.7;
            font-size: 0.95rem;
        }

        .tournament-organizer {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            margin-bottom: 1.5rem;
            padding: 0.8rem;
            background: rgba(212, 175, 55, 0.05);
            border-radius: 10px;
        }

        .tournament-organizer i {
            color: var(--primary-gold);
            font-size: 1.2rem;
        }

        .tournament-organizer span {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .tournament-info {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.2rem;
            margin-bottom: 1.8rem;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            padding: 1rem;
            background: rgba(212, 175, 55, 0.05);
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .info-item:hover {
            background: rgba(212, 175, 55, 0.1);
            transform: translateX(5px);
        }

        .info-item i {
            color: var(--primary-gold);
            font-size: 1.4rem;
            width: 30px;
            text-align: center;
        }

        .info-item-content {
            flex: 1;
        }

        .info-item-label {
            font-size: 0.75rem;
            color: var(--text-dark);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-item-value {
            font-size: 1rem;
            color: var(--text-light);
            font-weight: 700;
        }

        .tournament-progress {
            margin-bottom: 2rem;
        }

        .progress-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.8rem;
        }

        .progress-label {
            font-size: 0.9rem;
            color: var(--text-muted);
            font-weight: 600;
        }

        .progress-percentage {
            font-size: 1.1rem;
            color: var(--primary-gold);
            font-weight: 900;
        }

        .progress {
            height: 18px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50px;
            overflow: hidden;
            box-shadow: inset 0 2px 5px rgba(0, 0, 0, 0.3);
        }

        .progress-bar {
            background: linear-gradient(90deg, var(--primary-gold), var(--secondary-gold));
            border-radius: 50px;
            transition: width 0.6s ease;
            position: relative;
            overflow: hidden;
        }

        .progress-bar::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            animation: shimmer 2s infinite;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        .tournament-prize {
            background: linear-gradient(135deg, rgba(212, 175, 55, 0.2), rgba(212, 175, 55, 0.1));
            padding: 1.5rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            text-align: center;
            border: 2px solid rgba(212, 175, 55, 0.4);
            position: relative;
            overflow: hidden;
        }

        .tournament-prize::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(212, 175, 55, 0.1), transparent 60%);
            animation: rotate-gradient 10s linear infinite;
        }

        @keyframes rotate-gradient {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .tournament-prize-content {
            position: relative;
            z-index: 2;
        }

        .tournament-prize h4 {
            color: var(--primary-gold);
            font-size: 2.5rem;
            font-weight: 900;
            margin: 0 0 0.5rem 0;
            text-shadow: 0 0 20px rgba(212, 175, 55, 0.5);
        }

        .tournament-prize p {
            margin: 0;
            color: var(--text-dark);
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .tournament-actions {
            display: flex;
            gap: 1rem;
        }

        .btn-tournament {
            flex: 1;
            padding: 1.2rem;
            border-radius: 15px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.8rem;
            font-size: 0.95rem;
        }

        .btn-register {
            background: linear-gradient(135deg, var(--primary-gold), var(--secondary-gold));
            color: #000;
            box-shadow: 0 5px 20px rgba(212, 175, 55, 0.4);
        }

        .btn-register:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(212, 175, 55, 0.6);
            background: linear-gradient(135deg, var(--secondary-gold), var(--primary-gold));
        }

        .btn-view {
            background: linear-gradient(135deg, var(--info), #138496);
            color: #fff;
            box-shadow: 0 5px 20px rgba(23, 162, 184, 0.4);
        }

        .btn-view:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(23, 162, 184, 0.6);
        }

        .btn-registered {
            background: linear-gradient(135deg, var(--success), #20c997);
            color: #fff;
            cursor: default;
            box-shadow: 0 5px 20px rgba(40, 167, 69, 0.4);
        }

        .btn-full {
            background: #6c757d;
            color: #fff;
            cursor: not-allowed;
            opacity: 0.7;
        }

        /* ============================================
           WINNERS SECTION
           ============================================ */
        
        .winners-section {
            padding: 4rem 0;
            background: linear-gradient(180deg, var(--dark-bg) 0%, var(--card-bg) 100%);
        }

        .winners-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }

        .winner-card {
            background: linear-gradient(135deg, var(--card-bg) 0%, var(--card-bg-secondary) 100%);
            border: 3px solid var(--primary-gold);
            border-radius: 20px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
        }

        .winner-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(212, 175, 55, 0.4);
        }

        .winner-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 4px solid var(--primary-gold);
            margin: 0 auto 1.5rem;
            object-fit: cover;
            box-shadow: 0 0 30px rgba(212, 175, 55, 0.5);
        }

        .winner-card h4 {
            color: var(--primary-gold);
            font-size: 1.5rem;
            font-weight: 900;
            margin-bottom: 0.5rem;
        }

        .winner-card .tournament-name {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .winner-card .prize {
            color: var(--secondary-gold);
            font-size: 1.3rem;
            font-weight: 700;
        }

        /* ============================================
           EMPTY STATE
           ============================================ */
        
        .empty-state {
            text-align: center;
            padding: 5rem 2rem;
        }

        .empty-state i {
            font-size: 6rem;
            color: rgba(212, 175, 55, 0.3);
            margin-bottom: 2rem;
            animation: float 3s ease-in-out infinite;
        }

        .empty-state h3 {
            color: var(--primary-gold);
            font-size: 2.5rem;
            font-weight: 900;
            margin-bottom: 1rem;
        }

        .empty-state p {
            color: var(--text-muted);
            font-size: 1.2rem;
            margin-bottom: 2rem;
        }

        .empty-state .btn-tournament {
            max-width: 300px;
            margin: 0 auto;
        }

        /* ============================================
           LOADING STATE
           ============================================ */
        
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        .loading-overlay.active {
            display: flex;
        }

        .loading-spinner {
            text-align: center;
        }

        .loading-spinner i {
            font-size: 4rem;
            color: var(--primary-gold);
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .loading-spinner p {
            color: var(--text-light);
            margin-top: 1rem;
            font-size: 1.2rem;
        }

        /* ============================================
           RESPONSIVE DESIGN
           ============================================ */
        
        @media (max-width: 1200px) {
            .tournaments-grid {
                grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .tournament-hero h1 {
                font-size: 3rem;
            }

            .tournament-hero p {
                font-size: 1.1rem;
            }

            .hero-stats {
                flex-direction: column;
                gap: 1.5rem;
            }

            .filters-container {
                flex-direction: column;
                align-items: stretch;
            }

            .search-box {
                max-width: 100%;
            }

            .tournaments-grid {
                grid-template-columns: 1fr;
            }

            .tournament-info {
                grid-template-columns: 1fr;
            }

            .tournament-actions {
                flex-direction: column;
            }

            .featured-content h2 {
                font-size: 2rem;
            }

            .featured-details {
                grid-template-columns: 1fr;
            }

            .winners-grid {
                grid-template-columns: 1fr;
            }
        }

        /* ============================================
           ANIMATIONS
           ============================================ */
        
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes bounce {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-10px);
            }
        }
    </style>
</head>
<body>
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner">
            <i class="fas fa-spinner"></i>
            <p>Processing...</p>
        </div>
    </div>

    <!-- Navbar -->
    <?php include 'navbar.php'; ?>

    <!-- Hero Section -->
    <section class="tournament-hero">
        <div class="tournament-hero-content">
            <div class="container">
                <h1 class="animate__animated animate__fadeInDown">
                    <i class="fas fa-trophy"></i>
                    Tournaments
                </h1>
                <p class="animate__animated animate__fadeInUp">
                    Join our competitive billiard tournaments and showcase your skills! 
                    Compete with the best players and win exciting prizes.
                </p>

                <div class="hero-stats">
                    <div class="hero-stat-item">
                        <div class="hero-stat-value"><?= $stats['total'] ?></div>
                        <div class="hero-stat-label">Total Tournaments</div>
                    </div>
                    <div class="hero-stat-item">
                        <div class="hero-stat-value"><?= $stats['open'] ?></div>
                        <div class="hero-stat-label">Open for Registration</div>
                    </div>
                    <div class="hero-stat-item">
                        <div class="hero-stat-value"><?= $stats['participants'] ?></div>
                        <div class="hero-stat-label">Total Participants</div>
                    </div>
                    <?php if ($is_logged_in): ?>
                    <div class="hero-stat-item">
                        <div class="hero-stat-value"><?= $stats['user_registrations'] ?></div>
                        <div class="hero-stat-label">Your Tournaments</div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Tournament Section -->
    <?php if ($featured_tournament): ?>
    <section class="featured-section">
        <div class="container">
            <div class="featured-tournament animate__animated animate__fadeInUp">
                <div class="featured-banner">
                    <?php if (!empty($featured_tournament['banner_image']) && file_exists('images/tournaments/' . $featured_tournament['banner_image'])): ?>
                        <img src="images/tournaments/<?= htmlspecialchars($featured_tournament['banner_image']) ?>" alt="<?= htmlspecialchars($featured_tournament['nama_tournament']) ?>">
                    <?php else: ?>
                        <i class="fas fa-trophy"></i>
                    <?php endif; ?>
                </div>

                <div class="featured-content">
                    <h2><?= htmlspecialchars($featured_tournament['nama_tournament']) ?></h2>
                    <p><?= htmlspecialchars($featured_tournament['deskripsi'] ?? 'Join our next big tournament!') ?></p>

                    <div class="featured-details">
                        <div class="featured-detail-item">
                            <i class="fas fa-calendar"></i>
                            <h4><?= date('d M Y', strtotime($featured_tournament['tanggal_mulai'])) ?></h4>
                            <p>Tournament Date</p>
                        </div>
                        <div class="featured-detail-item">
                            <i class="fas fa-clock"></i>
                            <h4><?= date('H:i', strtotime($featured_tournament['waktu_mulai'])) ?> WIB</h4>
                            <p>Start Time</p>
                        </div>
                        <div class="featured-detail-item">
                            <i class="fas fa-users"></i>
                            <h4><?= $featured_tournament['current_peserta'] ?> / <?= $featured_tournament['max_peserta'] ?></h4>
                            <p>Participants</p>
                        </div>
                        <div class="featured-detail-item">
                            <i class="fas fa-trophy"></i>
                            <h4><?= htmlspecialchars($featured_tournament['hadiah'] ?? 'TBA') ?></h4>
                            <p>Prize Pool</p>
                        </div>
                    </div>

                    <div class="tournament-actions">
                        <?php
                        $is_featured_registered = isset($user_tournaments[$featured_tournament['id']]);
                        $is_featured_full = $featured_tournament['current_peserta'] >= $featured_tournament['max_peserta'];
                        ?>
                        
                        <?php if (!$is_logged_in): ?>
                            <a href="login.php" class="btn-tournament btn-register">
                                <i class="fas fa-sign-in-alt"></i> Login to Register
                            </a>
                        <?php elseif ($is_featured_registered): ?>
                            <button class="btn-tournament btn-registered" disabled>
                                <i class="fas fa-check-circle"></i> You're Registered
                            </button>
                        <?php elseif ($is_featured_full): ?>
                            <button class="btn-tournament btn-full" disabled>
                                <i class="fas fa-lock"></i> Tournament Full
                            </button>
                        <?php else: ?>
                            <button class="btn-tournament btn-register" onclick="registerTournament(<?= $featured_tournament['id'] ?>)">
                                <i class="fas fa-user-plus"></i> Register Now
                            </button>
                        <?php endif; ?>

                        <a href="tournament_bracket_user.php?id=<?= $featured_tournament['id'] ?>" class="btn-tournament btn-view">
                            <i class="fas fa-eye"></i> View Details
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Filters & Search Section -->
    <section class="filters-section">
        <div class="container">
            <form method="GET" action="" id="filterForm">
                <div class="filters-container">
                    <div class="filter-group">
                        <a href="?status=all&sort=<?= $sort_by ?>&search=<?= $search_query ?>" 
                           class="filter-btn <?= $filter_status === 'all' ? 'active' : '' ?>">
                            <i class="fas fa-list"></i> All
                        </a>
                        <a href="?status=open&sort=<?= $sort_by ?>&search=<?= $search_query ?>" 
                           class="filter-btn <?= $filter_status === 'open' ? 'active' : '' ?>">
                            <i class="fas fa-door-open"></i> Open (<?= $stats['open'] ?>)
                        </a>
                        <a href="?status=ongoing&sort=<?= $sort_by ?>&search=<?= $search_query ?>" 
                           class="filter-btn <?= $filter_status === 'ongoing' ? 'active' : '' ?>">
                            <i class="fas fa-running"></i> Ongoing (<?= $stats['ongoing'] ?>)
                        </a>
                        <a href="?status=completed&sort=<?= $sort_by ?>&search=<?= $search_query ?>" 
                           class="filter-btn <?= $filter_status === 'completed' ? 'active' : '' ?>">
                            <i class="fas fa-check-circle"></i> Completed (<?= $stats['completed'] ?>)
                        </a>
                    </div>

                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input 
                            type="text" 
                            name="search" 
                            placeholder="Search tournaments..." 
                            value="<?= htmlspecialchars($search_query) ?>"
                        >
                        <input type="hidden" name="status" value="<?= $filter_status ?>">
                        <input type="hidden" name="sort" value="<?= $sort_by ?>">
                    </div>

                    <select name="sort" class="sort-dropdown" onchange="this.form.submit()">
                        <option value="status" <?= $sort_by === 'status' ? 'selected' : '' ?>>Sort by Status</option>
                        <option value="date_desc" <?= $sort_by === 'date_desc' ? 'selected' : '' ?>>Newest First</option>
                        <option value="date_asc" <?= $sort_by === 'date_asc' ? 'selected' : '' ?>>Oldest First</option>
                        <option value="name_asc" <?= $sort_by === 'name_asc' ? 'selected' : '' ?>>Name (A-Z)</option>
                        <option value="name_desc" <?= $sort_by === 'name_desc' ? 'selected' : '' ?>>Name (Z-A)</option>
                        <option value="participants" <?= $sort_by === 'participants' ? 'selected' : '' ?>>Most Participants</option>
                    </select>
                </div>
            </form>
        </div>
    </section>

    <!-- Tournaments Grid Section -->
    <section class="tournaments-container">
        <div class="container">
            <div class="section-header">
                <h2>
                    <?php 
                    if ($filter_status !== 'all') {
                        echo ucfirst($filter_status) . ' Tournaments';
                    } else {
                        echo 'All Tournaments';
                    }
                    ?>
                </h2>
                <p>
                    <?php if (!empty($search_query)): ?>
                        Search results for "<?= htmlspecialchars($search_query) ?>"
                    <?php else: ?>
                        Browse through our upcoming and ongoing tournaments
                    <?php endif; ?>
                </p>
            </div>

            <?php if ($tournaments && $tournaments->num_rows > 0): ?>
                <div class="tournaments-grid">
                    <?php while ($tournament = $tournaments->fetch_assoc()): ?>
                        <?php
                        // Calculate percentage
                        $max_peserta = (int)$tournament['max_peserta'];
                        $current_peserta = (int)$tournament['current_peserta'];
                        $percentage = $max_peserta > 0 ? ($current_peserta / $max_peserta * 100) : 0;
                        $is_full = $current_peserta >= $max_peserta;
                        
                        // Check if user is registered
                        $is_registered = isset($user_tournaments[$tournament['id']]);
                        $user_status = $is_registered ? $user_tournaments[$tournament['id']]['status'] : null;
                        
                        // Format date
                        $tanggal = new DateTime($tournament['tanggal_mulai']);
                        $formatted_date = $tanggal->format('d M Y');
                        $formatted_time = date('H:i', strtotime($tournament['waktu_mulai']));
                        
                        // Format prize
                        $hadiah = !empty($tournament['hadiah']) ? $tournament['hadiah'] : 'To be announced';
                        
                        // Get organizer
                        $organizer = $tournament['organizer_name'] ?? 'Legacy Billiard';
                        ?>
                        <div class="tournament-card">
                            <div class="tournament-banner">
                                <?php if (!empty($tournament['banner_image']) && file_exists('images/tournaments/' . $tournament['banner_image'])): ?>
                                    <img src="images/tournaments/<?= htmlspecialchars($tournament['banner_image']) ?>" alt="<?= htmlspecialchars($tournament['nama_tournament']) ?>">
                                <?php else: ?>
                                    <i class="fas fa-trophy"></i>
                                <?php endif; ?>
                                
                                <span class="tournament-status status-<?= strtolower($tournament['status']) ?>">
                                    <?= ucfirst($tournament['status']) ?>
                                </span>
                            </div>

                            <div class="tournament-body">
                                <h3><?= htmlspecialchars($tournament['nama_tournament']) ?></h3>
                                <p><?= htmlspecialchars(substr($tournament['deskripsi'] ?? 'No description available.', 0, 120)) ?>...</p>

                                <div class="tournament-organizer">
                                    <i class="fas fa-user-shield"></i>
                                    <span>Organized by <strong><?= htmlspecialchars($organizer) ?></strong></span>
                                </div>

                                <div class="tournament-info">
                                    <div class="info-item">
                                        <i class="fas fa-calendar"></i>
                                        <div class="info-item-content">
                                            <div class="info-item-label">Date</div>
                                            <div class="info-item-value"><?= $formatted_date ?></div>
                                        </div>
                                    </div>
                                    <div class="info-item">
                                        <i class="fas fa-clock"></i>
                                        <div class="info-item-content">
                                            <div class="info-item-label">Time</div>
                                            <div class="info-item-value"><?= $formatted_time ?> WIB</div>
                                        </div>
                                    </div>
                                    <div class="info-item">
                                        <i class="fas fa-users"></i>
                                        <div class="info-item-content">
                                            <div class="info-item-label">Participants</div>
                                            <div class="info-item-value"><?= $current_peserta ?> / <?= $max_peserta ?></div>
                                        </div>
                                    </div>
                                    <div class="info-item">
                                        <i class="fas fa-gamepad"></i>
                                        <div class="info-item-content">
                                            <div class="info-item-label">Format</div>
                                            <div class="info-item-value">Bracket</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="tournament-progress">
                                    <div class="progress-header">
                                        <span class="progress-label">Registration Progress</span>
                                        <span class="progress-percentage"><?= number_format($percentage, 0) ?>%</span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar" role="progressbar" style="width: <?= $percentage ?>%" aria-valuenow="<?= $percentage ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>

                                <div class="tournament-prize">
                                    <div class="tournament-prize-content">
                                        <h4><?= htmlspecialchars($hadiah) ?></h4>
                                        <p>Prize Pool</p>
                                    </div>
                                </div>

                                <div class="tournament-actions">
                                    <?php if ($tournament['status'] === 'open'): ?>
                                        <?php if (!$is_logged_in): ?>
                                            <a href="login.php" class="btn-tournament btn-register">
                                                <i class="fas fa-sign-in-alt"></i> Login
                                            </a>
                                        <?php elseif ($is_registered): ?>
                                            <button class="btn-tournament btn-registered" disabled>
                                                <i class="fas fa-check-circle"></i> Registered
                                            </button>
                                        <?php elseif ($is_full): ?>
                                            <button class="btn-tournament btn-full" disabled>
                                                <i class="fas fa-lock"></i> Full
                                            </button>
                                        <?php else: ?>
                                            <button class="btn-tournament btn-register" onclick="registerTournament(<?= $tournament['id'] ?>)">
                                                <i class="fas fa-user-plus"></i> Register
                                            </button>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <a href="tournament_bracket_user.php?id=<?= $tournament['id'] ?>" class="btn-tournament btn-view">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-trophy"></i>
                    <h3>No Tournaments Found</h3>
                    <p>
                        <?php if (!empty($search_query)): ?>
                            No tournaments match your search. Try different keywords.
                        <?php elseif ($filter_status !== 'all'): ?>
                            No <?= $filter_status ?> tournaments available at the moment.
                        <?php else: ?>
                            Check back later for upcoming tournaments!
                        <?php endif; ?>
                    </p>
                    <?php if (!empty($search_query) || $filter_status !== 'all'): ?>
                        <a href="tournament.php" class="btn-tournament btn-register">
                            <i class="fas fa-redo"></i> View All Tournaments
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Recent Winners Section -->
    <?php if (count($recent_winners) > 0): ?>
    <section class="winners-section">
        <div class="container">
            <div class="section-header">
                <h2>Hall of Champions</h2>
                <p>Congratulations to our recent tournament winners!</p>
            </div>

            <div class="winners-grid">
                <?php foreach ($recent_winners as $winner): ?>
                <div class="winner-card">
                    <img src="images/<?= htmlspecialchars($winner['foto_profil'] ?? 'avataruser.jpg') ?>" alt="<?= htmlspecialchars($winner['nama']) ?>" class="winner-avatar">
                    <h4><?= htmlspecialchars($winner['nama']) ?></h4>
                    <div class="tournament-name"><?= htmlspecialchars($winner['nama_tournament']) ?></div>
                    <div class="prize">
                        <i class="fas fa-trophy"></i>
                        <?= htmlspecialchars($winner['hadiah'] ?? 'Champion') ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Footer -->
    <?php include 'footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    
    <!-- Custom JS -->
    <script src="main.js"></script>

    <script>
        // ============================================
        // TOURNAMENT REGISTRATION FUNCTION
        // ============================================
        
        async function registerTournament(tournamentId) {
            const loadingOverlay = document.getElementById('loadingOverlay');
            loadingOverlay.classList.add('active');

            try {
                const formData = new FormData();
                formData.append('tournament_id', tournamentId);

                const response = await fetch('tournament.php?action=register', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert(data.message);
                }
            } catch (error) {
                console.error('Registration error:', error);
                alert('An error occurred. Please try again.');
            } finally {
                loadingOverlay.classList.remove('active');
            }
        }

        // ============================================
        // AUTO-SUBMIT SEARCH ON ENTER
        // ============================================
        
        document.querySelector('.search-box input').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('filterForm').submit();
            }
        });

        // ============================================
        // SMOOTH SCROLL
        // ============================================
        
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // ============================================
        // CONSOLE LOG
        // ============================================
        
        console.log('%c LEGACY BILLIARD TOURNAMENTS ', 'background: #D4AF37; color: #000; font-size: 20px; font-weight: bold; padding: 10px;');
        console.log('%c Total Tournaments: <?= $stats['total'] ?> ', 'background: #28a745; color: #fff; font-size: 14px; padding: 5px;');
        console.log('%c Open for Registration: <?= $stats['open'] ?> ', 'background: #17a2b8; color: #fff; font-size: 14px; padding: 5px;');
    </script>
</body>
</html>
