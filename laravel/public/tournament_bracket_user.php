<?php
/**
 * ══════════════════════════════════════════════════════════════
 * 🏆 LEGACY BILLIARD - MEGA ULTIMATE TOURNAMENT BRACKET 🏆
 * ══════════════════════════════════════════════════════════════
 * Version: 6.0 MEGA ULTIMATE PARAH EDITION
 * Author: AI Assistant  
 * Date: December 5, 2025 01:04 AM WIB
 * ══════════════════════════════════════════════════════════════
 * 🔥 ULTIMATE FEATURES:
 * ✅ Real-time Auto-Refresh (10 detik)
 * ✅ Particles.js Background Animation
 * ✅ Premium Esports Design (Valorant/Dota 2 Style)
 * ✅ 3D Card Hover Effects
 * ✅ Animated Gradient Borders
 * ✅ Live Match Pulse Animations
 * ✅ Champion Trophy Animation
 * ✅ Winner Confetti Effect
 * ✅ Progress Bar Real-time
 * ✅ Player Stats Dashboard
 * ✅ Pool System (A & B)
 * ✅ Grand Final Center Display
 * ✅ User Highlight (If Participant)
 * ✅ Responsive Mobile Design
 * ✅ Smooth Horizontal Scroll
 * ✅ Notification Toast on Update
 * ══════════════════════════════════════════════════════════════
 */

session_start();
require_once 'config.php';

$is_logged_in = isset($_SESSION['user_id']);
$user_id = $is_logged_in ? $_SESSION['user_id'] : null;

if (!isset($_GET['id'])) {
    header('Location: tournament.php');
    exit;
}

$tournament_id = intval($_GET['id']);

// ══════════════════════════════════════════════════════════════
// GET TOURNAMENT DETAILS WITH WINNER
// ══════════════════════════════════════════════════════════════
$query = "
    SELECT t.*,
           COUNT(DISTINCT tp.id) as total_participants,
           w.nama as winner_name,
           w.foto_profil as winner_photo,
           w.email as winner_email
    FROM tournaments t
    LEFT JOIN tournament_participants tp ON t.id = tp.tournament_id
    LEFT JOIN users w ON t.winner_id = w.id
    WHERE t.id = ?
    GROUP BY t.id
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $tournament_id);
$stmt->execute();
$tournament = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$tournament) {
    header('Location: tournament.php');
    exit;
}

// ══════════════════════════════════════════════════════════════
// GET PARTICIPANTS WITH DETAILED STATS
// ══════════════════════════════════════════════════════════════
$participants = ['A' => [], 'B' => []];
$query = "
    SELECT 
        tp.*,
        u.nama,
        u.email,
        u.foto_profil,
        (SELECT COUNT(*) FROM tournament_matches 
         WHERE (player1_id = u.id OR player2_id = u.id) 
         AND winner_id = u.id 
         AND tournament_id = ?) as wins,
        (SELECT COUNT(*) FROM tournament_matches 
         WHERE (player1_id = u.id OR player2_id = u.id) 
         AND winner_id != u.id 
         AND status = 'completed' 
         AND tournament_id = ?) as losses,
        (SELECT SUM(CASE 
            WHEN player1_id = u.id THEN player1_score 
            WHEN player2_id = u.id THEN player2_score 
            ELSE 0 END) 
         FROM tournament_matches 
         WHERE (player1_id = u.id OR player2_id = u.id) 
         AND status = 'completed' 
         AND tournament_id = ?) as total_score
    FROM tournament_participants tp
    JOIN users u ON tp.user_id = u.id
    WHERE tp.tournament_id = ?
    ORDER BY tp.pool, tp.seed_position
";
$stmt = $conn->prepare($query);
$stmt->bind_param("iiii", $tournament_id, $tournament_id, $tournament_id, $tournament_id);
$stmt->execute();
$participants_result = $stmt->get_result();

while ($row = $participants_result->fetch_assoc()) {
    $participants[$row['pool']][] = $row;
}
$stmt->close();

// ══════════════════════════════════════════════════════════════
// GET ALL MATCHES BY ROUND
// ══════════════════════════════════════════════════════════════
$matches = [
    'semifinal' => ['A' => [], 'B' => []],
    'final_pool' => ['A' => [], 'B' => []],
    'grand_final' => []
];

$query = "
    SELECT tm.*,
           p1.nama as player1_name,
           p1.foto_profil as player1_photo,
           p1.email as player1_email,
           p2.nama as player2_name,
           p2.foto_profil as player2_photo,
           p2.email as player2_email,
           w.nama as winner_name,
           w.foto_profil as winner_photo
    FROM tournament_matches tm
    LEFT JOIN users p1 ON tm.player1_id = p1.id
    LEFT JOIN users p2 ON tm.player2_id = p2.id
    LEFT JOIN users w ON tm.winner_id = w.id
    WHERE tm.tournament_id = ?
    ORDER BY 
        FIELD(tm.round, 'semifinal', 'final_pool', 'grand_final'),
        tm.pool,
        tm.match_number
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $tournament_id);
$stmt->execute();
$matches_result = $stmt->get_result();

$total_matches = 0;
$completed_matches = 0;

while ($row = $matches_result->fetch_assoc()) {
    $total_matches++;
    if ($row['status'] === 'completed') {
        $completed_matches++;
    }
    
    if ($row['round'] === 'grand_final') {
        $matches['grand_final'][] = $row;
    } else {
        $pool = $row['pool'];
        if (isset($matches[$row['round']][$pool])) {
            $matches[$row['round']][$pool][] = $row;
        }
    }
}
$stmt->close();

$has_bracket = ($total_matches > 0);
$bracket_progress = $total_matches > 0 ? round(($completed_matches / $total_matches) * 100) : 0;

// ══════════════════════════════════════════════════════════════
// CHECK IF USER IS PARTICIPANT
// ══════════════════════════════════════════════════════════════
$is_participant = false;
$user_pool = null;
if ($is_logged_in) {
    foreach ($participants['A'] as $p) {
        if ($p['user_id'] == $user_id) {
            $is_participant = true;
            $user_pool = 'A';
            break;
        }
    }
    if (!$is_participant) {
        foreach ($participants['B'] as $p) {
            if ($p['user_id'] == $user_id) {
                $is_participant = true;
                $user_pool = 'B';
                break;
            }
        }
    }
}

$page_title = "Tournament Bracket - " . htmlspecialchars($tournament['nama_tournament']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&family=Rajdhani:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Particles.js -->
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --gold: #FFD700;
            --gold-light: #FFED4E;
            --gold-dark: #B8860B;
            --orange: #FF8C00;
            --red: #FF0055;
            --green: #00FF88;
            --cyan: #00F0FF;
            --purple: #B026FF;
            --dark: #0A0A0A;
            --dark-card: #1A1A2E;
            --dark-sidebar: #16213E;
        }

        body {
            font-family: 'Rajdhani', sans-serif;
            background: var(--dark);
            color: #fff;
            overflow-x: hidden;
            position: relative;
        }

        /* ══════════════════════════════════════════════════════════════
           PARTICLES BACKGROUND
           ══════════════════════════════════════════════════════════════ */
        #particles-js {
            position: fixed;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: 0;
            background: linear-gradient(135deg, #0A0A0A 0%, #1A0033 50%, #0A0A0A 100%);
        }

        .content-wrapper {
            position: relative;
            z-index: 1;
        }

        /* ══════════════════════════════════════════════════════════════
           PREMIUM NAVBAR
           ══════════════════════════════════════════════════════════════ */
        .navbar-ultra {
            background: linear-gradient(135deg, rgba(26, 26, 46, 0.98), rgba(22, 33, 62, 0.98));
            backdrop-filter: blur(20px);
            padding: 1.5rem 0;
            position: sticky;
            top: 0;
            z-index: 999;
            border-bottom: 3px solid var(--gold);
            box-shadow: 0 10px 50px rgba(255, 215, 0, 0.3);
        }

        .nav-container {
            max-width: 1800px;
            margin: 0 auto;
            padding: 0 3rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .nav-logo {
            font-family: 'Orbitron', sans-serif;
            font-size: 2rem;
            font-weight: 900;
            background: linear-gradient(135deg, var(--gold), var(--orange));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 1rem;
            animation: logoGlow 2s ease-in-out infinite;
        }

        @keyframes logoGlow {
            0%, 100% { filter: drop-shadow(0 0 10px var(--gold)); }
            50% { filter: drop-shadow(0 0 25px var(--gold)); }
        }

        .nav-logo i {
            font-size: 2.5rem;
            animation: spin 10s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .nav-link {
            color: #fff;
            text-decoration: none;
            font-weight: 700;
            font-size: 1.1rem;
            padding: 0.8rem 1.8rem;
            border-radius: 50px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 215, 0, 0.3), transparent);
            transition: left 0.5s ease;
        }

        .nav-link:hover::before {
            left: 100%;
        }

        .nav-link:hover {
            background: rgba(255, 215, 0, 0.15);
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(255, 215, 0, 0.4);
        }

        .nav-link.active {
            background: linear-gradient(135deg, var(--gold), var(--orange));
            color: #000;
        }

        /* ══════════════════════════════════════════════════════════════
           TOURNAMENT HERO HEADER
           ══════════════════════════════════════════════════════════════ */
        .hero-ultra {
            max-width: 1800px;
            margin: 3rem auto;
            padding: 0 3rem;
        }

        .hero-card {
            background: linear-gradient(135deg, rgba(26, 26, 46, 0.95), rgba(22, 33, 62, 0.95));
            backdrop-filter: blur(20px);
            border: 3px solid var(--gold);
            border-radius: 30px;
            padding: 4rem;
            position: relative;
            overflow: hidden;
            box-shadow: 
                0 20px 60px rgba(255, 215, 0, 0.3),
                inset 0 0 100px rgba(255, 215, 0, 0.05);
        }

        .hero-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 215, 0, 0.1) 0%, transparent 70%);
            animation: rotate 30s linear infinite;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .hero-title {
            font-family: 'Orbitron', sans-serif;
            font-size: 4rem;
            font-weight: 900;
            background: linear-gradient(135deg, var(--gold) 0%, var(--orange) 50%, var(--red) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-align: center;
            margin-bottom: 2rem;
            position: relative;
            z-index: 1;
            animation: titlePulse 3s ease-in-out infinite;
        }

        @keyframes titlePulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.02); }
        }

        .hero-title i {
            display: inline-block;
            animation: bounce 2s ease-in-out infinite;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-15px); }
        }

        .hero-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
            position: relative;
            z-index: 1;
        }

        .stat-card {
            background: linear-gradient(135deg, rgba(255, 215, 0, 0.1), rgba(255, 140, 0, 0.1));
            border: 2px solid var(--gold);
            border-radius: 20px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 215, 0, 0.3), transparent);
            transition: left 0.5s ease;
        }

        .stat-card:hover::before {
            left: 100%;
        }

        .stat-card:hover {
            transform: translateY(-10px) scale(1.05);
            box-shadow: 0 20px 60px rgba(255, 215, 0, 0.5);
            border-color: var(--orange);
        }

        .stat-icon {
            font-size: 3rem;
            color: var(--gold);
            margin-bottom: 1rem;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .stat-value {
            font-family: 'Orbitron', sans-serif;
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--gold);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 1.2rem;
            color: #aaa;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        /* ══════════════════════════════════════════════════════════════
           STATUS BADGE
           ══════════════════════════════════════════════════════════════ */
        .status-mega {
            display: inline-block;
            padding: 1.5rem 3rem;
            border-radius: 50px;
            font-family: 'Orbitron', sans-serif;
            font-size: 1.5rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 3px;
            margin-top: 2rem;
        }

        .status-mega.ongoing {
            background: linear-gradient(135deg, #FFAA00, #FF6B35);
            box-shadow: 0 10px 40px rgba(255, 170, 0, 0.6);
            animation: statusPulse 2s ease-in-out infinite;
        }

        @keyframes statusPulse {
            0%, 100% { transform: scale(1); box-shadow: 0 10px 40px rgba(255, 170, 0, 0.6); }
            50% { transform: scale(1.05); box-shadow: 0 15px 60px rgba(255, 170, 0, 0.9); }
        }

        .status-mega.completed {
            background: linear-gradient(135deg, var(--green), #00CC77);
            box-shadow: 0 10px 40px rgba(0, 255, 136, 0.6);
        }

        .status-mega.open {
            background: linear-gradient(135deg, var(--cyan), #0088FF);
            box-shadow: 0 10px 40px rgba(0, 240, 255, 0.6);
        }

        .status-mega i {
            margin-right: 1rem;
            animation: blink 1s ease-in-out infinite;
        }

        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }

        /* ══════════════════════════════════════════════════════════════
           LIVE INDICATOR
           ══════════════════════════════════════════════════════════════ */
        .live-mega {
            max-width: 1800px;
            margin: 3rem auto;
            padding: 0 3rem;
        }

        .live-card {
            background: linear-gradient(135deg, rgba(255, 0, 85, 0.2), rgba(255, 107, 53, 0.2));
            border: 3px solid var(--red);
            border-radius: 20px;
            padding: 2rem 3rem;
            display: flex;
            align-items: center;
            gap: 2rem;
            animation: liveGlow 2s ease-in-out infinite;
        }

        @keyframes liveGlow {
            0%, 100% { box-shadow: 0 10px 40px rgba(255, 0, 85, 0.4); }
            50% { box-shadow: 0 15px 60px rgba(255, 0, 85, 0.7); }
        }

        .live-dot {
            width: 25px;
            height: 25px;
            background: var(--red);
            border-radius: 50%;
            animation: pulse 1s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.3); opacity: 0.7; }
        }

        .live-text {
            font-family: 'Orbitron', sans-serif;
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--red);
            text-transform: uppercase;
            letter-spacing: 2px;
            flex: 1;
        }

        .auto-refresh {
            font-size: 1.1rem;
            color: #888;
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }

        .auto-refresh i {
            animation: spin 2s linear infinite;
        }

        /* ══════════════════════════════════════════════════════════════
           PROGRESS BAR
           ══════════════════════════════════════════════════════════════ */
        .progress-ultra {
            max-width: 1800px;
            margin: 3rem auto;
            padding: 0 3rem;
        }

        .progress-card {
            background: linear-gradient(135deg, rgba(26, 26, 46, 0.95), rgba(22, 33, 62, 0.95));
            border: 2px solid var(--gold);
            border-radius: 20px;
            padding: 2.5rem;
            backdrop-filter: blur(20px);
        }

        .progress-title {
            font-family: 'Orbitron', sans-serif;
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--gold);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .progress-bar-container {
            height: 50px;
            background: rgba(0, 0, 0, 0.7);
            border-radius: 50px;
            overflow: hidden;
            position: relative;
            box-shadow: inset 0 5px 20px rgba(0, 0, 0, 0.5);
        }

        .progress-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--gold), var(--orange), var(--red));
            background-size: 200% 100%;
            animation: progressShine 3s ease-in-out infinite;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Orbitron', sans-serif;
            font-size: 1.5rem;
            font-weight: 900;
            color: #000;
            text-shadow: 0 2px 5px rgba(255, 215, 0, 0.5);
            box-shadow: 0 0 30px rgba(255, 215, 0, 0.8);
            transition: width 1s ease;
            position: relative;
        }

        @keyframes progressShine {
            0%, 100% { background-position: 0% 0%; }
            50% { background-position: 100% 0%; }
        }

        .progress-bar-fill::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.5), transparent);
            animation: progressGloss 2s ease-in-out infinite;
        }

        @keyframes progressGloss {
            from { left: -100%; }
            to { left: 200%; }
        }

        /* ══════════════════════════════════════════════════════════════
           PARTICIPANT ALERT
           ══════════════════════════════════════════════════════════════ */
        .participant-alert {
            max-width: 1800px;
            margin: 2rem auto;
            padding: 0 3rem;
        }

        .alert-card {
            background: linear-gradient(135deg, rgba(0, 240, 255, 0.2), rgba(176, 38, 255, 0.2));
            border: 3px solid var(--cyan);
            border-radius: 20px;
            padding: 2rem 3rem;
            text-align: center;
            font-size: 1.4rem;
            font-weight: 700;
            animation: alertGlow 2s ease-in-out infinite;
        }

        @keyframes alertGlow {
            0%, 100% { box-shadow: 0 10px 40px rgba(0, 240, 255, 0.4); }
            50% { box-shadow: 0 15px 60px rgba(0, 240, 255, 0.7); }
        }

        /* ══════════════════════════════════════════════════════════════
           CHAMPION BANNER
           ══════════════════════════════════════════════════════════════ */
        .champion-ultra {
            max-width: 1800px;
            margin: 4rem auto;
            padding: 0 3rem;
        }

        .champion-card {
            background: linear-gradient(135deg, rgba(255, 215, 0, 0.2), rgba(255, 140, 0, 0.2));
            border: 5px solid var(--gold);
            border-radius: 30px;
            padding: 5rem;
            text-align: center;
            position: relative;
            overflow: hidden;
            box-shadow: 
                0 30px 80px rgba(255, 215, 0, 0.6),
                inset 0 0 150px rgba(255, 215, 0, 0.1);
        }

        .champion-card::before {
            content: '';
            position: absolute;
            top: -100%;
            left: -100%;
            width: 300%;
            height: 300%;
            background: repeating-conic-gradient(
                from 0deg,
                transparent 0deg 30deg,
                rgba(255, 215, 0, 0.1) 30deg 60deg
            );
            animation: championRotate 20s linear infinite;
        }

        @keyframes championRotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .champion-title {
            font-family: 'Orbitron', sans-serif;
            font-size: 4.5rem;
            font-weight: 900;
            background: linear-gradient(135deg, var(--gold), var(--orange), var(--red));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 3rem;
            position: relative;
            z-index: 1;
            animation: championGlow 2s ease-in-out infinite;
        }

        @keyframes championGlow {
            0%, 100% { filter: drop-shadow(0 0 20px var(--gold)); }
            50% { filter: drop-shadow(0 0 40px var(--gold)); }
        }

        .champion-title i {
            font-size: 5rem;
            display: block;
            margin-bottom: 1rem;
            animation: trophySpin 3s ease-in-out infinite;
        }

        @keyframes trophySpin {
            0%, 100% { transform: rotate(0deg) scale(1); }
            50% { transform: rotate(10deg) scale(1.1); }
        }

        .champion-display {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 3rem;
            position: relative;
            z-index: 1;
        }

        .champion-avatar {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            border: 5px solid var(--gold);
            object-fit: cover;
            box-shadow: 
                0 20px 60px rgba(255, 215, 0, 0.7),
                0 0 100px rgba(255, 215, 0, 0.5);
            animation: championFloat 3s ease-in-out infinite;
        }

        @keyframes championFloat {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-20px) scale(1.05); }
        }

        .champion-name {
            font-family: 'Orbitron', sans-serif;
            font-size: 3.5rem;
            font-weight: 900;
            color: var(--gold);
            text-shadow: 
                0 0 30px rgba(255, 215, 0, 0.8),
                0 0 60px rgba(255, 215, 0, 0.5);
        }

        /* ══════════════════════════════════════════════════════════════
           BRACKET CONTAINER
           ══════════════════════════════════════════════════════════════ */
        .bracket-ultra {
            width: 100%;
            padding: 4rem 3rem;
            overflow-x: auto;
            overflow-y: hidden;
        }

        .bracket-ultra::-webkit-scrollbar {
            height: 15px;
        }

        .bracket-ultra::-webkit-scrollbar-track {
            background: rgba(255, 215, 0, 0.1);
            border-radius: 10px;
        }

        .bracket-ultra::-webkit-scrollbar-thumb {
            background: linear-gradient(90deg, var(--gold), var(--orange));
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(255, 215, 0, 0.5);
        }

        .bracket-grid {
            display: flex;
            gap: 4rem;
            min-width: min-content;
            padding: 2rem 0;
        }

        /* ══════════════════════════════════════════════════════════════
           POOL SECTIONS
           ══════════════════════════════════════════════════════════════ */
        .pool-ultra {
            min-width: 550px;
            flex: 1;
        }

        .pool-header {
            background: linear-gradient(135deg, var(--gold), var(--orange));
            color: #000;
            padding: 2.5rem;
            border-radius: 25px;
            text-align: center;
            font-family: 'Orbitron', sans-serif;
            font-size: 2.5rem;
            font-weight: 900;
            margin-bottom: 3rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(255, 215, 0, 0.6);
            animation: poolGlow 3s ease-in-out infinite;
        }

        @keyframes poolGlow {
            0%, 100% { box-shadow: 0 20px 60px rgba(255, 215, 0, 0.6); }
            50% { box-shadow: 0 25px 80px rgba(255, 215, 0, 0.9); }
        }

        .pool-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: repeating-linear-gradient(
                45deg,
                transparent,
                transparent 10px,
                rgba(255, 255, 255, 0.1) 10px,
                rgba(255, 255, 255, 0.1) 20px
            );
            animation: poolSlide 15s linear infinite;
        }

        @keyframes poolSlide {
            from { transform: translate(0, 0); }
            to { transform: translate(50px, 50px); }
        }

        .pool-header i {
            margin-right: 1rem;
            animation: poolSpin 4s ease-in-out infinite;
        }

        @keyframes poolSpin {
            0%, 100% { transform: rotate(0deg); }
            50% { transform: rotate(180deg); }
        }

        /* ══════════════════════════════════════════════════════════════
           PARTICIPANTS LIST
           ══════════════════════════════════════════════════════════════ */
        .participants-box {
            background: linear-gradient(135deg, rgba(255, 215, 0, 0.1), rgba(255, 140, 0, 0.1));
            border: 2px solid var(--gold);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 3rem;
            backdrop-filter: blur(10px);
        }

        .participants-title {
            font-family: 'Orbitron', sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--gold);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }

        .participant-item {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            padding: 1.2rem;
            background: rgba(26, 26, 46, 0.7);
            border: 2px solid transparent;
            border-radius: 15px;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .participant-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 215, 0, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .participant-item:hover::before {
            left: 100%;
        }

        .participant-item:hover {
            transform: translateX(10px);
            border-color: var(--gold);
            background: rgba(255, 215, 0, 0.1);
            box-shadow: 0 10px 30px rgba(255, 215, 0, 0.4);
        }

        .participant-item.highlight {
            background: linear-gradient(135deg, rgba(0, 240, 255, 0.2), rgba(176, 38, 255, 0.2));
            border-color: var(--cyan);
            animation: highlightPulse 2s ease-in-out infinite;
        }

        @keyframes highlightPulse {
            0%, 100% { box-shadow: 0 0 20px rgba(0, 240, 255, 0.5); }
            50% { box-shadow: 0 0 40px rgba(0, 240, 255, 0.8); }
        }

        .participant-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: 3px solid var(--gold);
            object-fit: cover;
            box-shadow: 0 5px 20px rgba(255, 215, 0, 0.5);
            transition: all 0.3s ease;
        }

        .participant-item:hover .participant-avatar {
            transform: scale(1.15) rotate(5deg);
            box-shadow: 0 10px 40px rgba(255, 215, 0, 0.8);
        }

        .participant-info {
            flex: 1;
        }

        .participant-name {
            font-family: 'Rajdhani', sans-serif;
            font-size: 1.3rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 0.3rem;
        }

        .participant-item.highlight .participant-name {
            color: var(--cyan);
        }

        .participant-item.highlight .participant-name::after {
            content: ' ⭐';
        }

        .participant-record {
            font-size: 1rem;
            color: #888;
        }

        .participant-seed {
            background: linear-gradient(135deg, var(--gold), var(--orange));
            color: #000;
            padding: 0.6rem 1.2rem;
            border-radius: 50px;
            font-family: 'Orbitron', sans-serif;
            font-weight: 800;
            font-size: 1rem;
            box-shadow: 0 5px 20px rgba(255, 215, 0, 0.5);
        }

        /* ══════════════════════════════════════════════════════════════
           ROUND TITLES
           ══════════════════════════════════════════════════════════════ */
        .round-section {
            text-align: center;
            margin: 3rem 0 2rem;
        }

        .round-title {
            display: inline-block;
            font-family: 'Orbitron', sans-serif;
            font-size: 1.8rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--gold), var(--orange));
            color: #000;
            padding: 1.5rem 3rem;
            border-radius: 50px;
            box-shadow: 0 10px 40px rgba(255, 215, 0, 0.5);
            animation: roundFloat 3s ease-in-out infinite;
        }

        @keyframes roundFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .round-title i {
            margin-right: 1rem;
            animation: roundSpin 2s ease-in-out infinite;
        }

        @keyframes roundSpin {
            0%, 100% { transform: rotate(0deg); }
            50% { transform: rotate(360deg); }
        }

        /* ══════════════════════════════════════════════════════════════
           MATCH CARDS (See next section - character limit)
           ══════════════════════════════════════════════════════════════ */
                /* ══════════════════════════════════════════════════════════════
           MATCH CARDS ULTRA PREMIUM
           ══════════════════════════════════════════════════════════════ */
        .match-ultra {
            background: linear-gradient(135deg, rgba(26, 26, 46, 0.95), rgba(22, 33, 62, 0.95));
            backdrop-filter: blur(20px);
            border: 3px solid rgba(255, 215, 0, 0.3);
            border-radius: 25px;
            padding: 2.5rem;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
            transition: all 0.4s ease;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.7);
        }

        .match-ultra::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 215, 0, 0.05) 0%, transparent 70%);
            animation: rotate 30s linear infinite;
        }

        .match-ultra:hover {
            transform: translateY(-10px) scale(1.02);
            border-color: var(--gold);
            box-shadow: 
                0 25px 80px rgba(255, 215, 0, 0.5),
                inset 0 0 50px rgba(255, 215, 0, 0.1);
        }

        .match-ultra.completed {
            border-color: var(--green);
            background: linear-gradient(135deg, rgba(0, 255, 136, 0.1), rgba(22, 33, 62, 0.95));
        }

        .match-ultra.live {
            border-color: var(--red);
            animation: matchLiveGlow 2s ease-in-out infinite;
        }

        @keyframes matchLiveGlow {
            0%, 100% { box-shadow: 0 15px 50px rgba(255, 0, 85, 0.5); }
            50% { box-shadow: 0 20px 70px rgba(255, 0, 85, 0.8); }
        }

        .match-label {
            position: absolute;
            top: -20px;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, var(--gold), var(--orange));
            color: #000;
            padding: 0.8rem 2.5rem;
            border-radius: 50px;
            font-family: 'Orbitron', sans-serif;
            font-weight: 900;
            font-size: 1.2rem;
            letter-spacing: 2px;
            box-shadow: 0 10px 30px rgba(255, 215, 0, 0.6);
            z-index: 2;
        }

        .match-time {
            text-align: center;
            color: #888;
            font-size: 1rem;
            margin-bottom: 2rem;
            font-style: italic;
            position: relative;
            z-index: 1;
        }

        /* ══════════════════════════════════════════════════════════════
           PLAYER ROWS ULTRA
           ══════════════════════════════════════════════════════════════ */
        .player-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 2rem;
            padding: 2rem;
            background: linear-gradient(135deg, rgba(255, 215, 0, 0.05), rgba(255, 140, 0, 0.05));
            border: 3px solid transparent;
            border-radius: 20px;
            margin-bottom: 1.5rem;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .player-row::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 215, 0, 0.2), transparent);
            transition: left 0.6s ease;
        }

        .player-row:hover::before {
            left: 100%;
        }

        .player-row.winner {
            background: linear-gradient(135deg, rgba(0, 255, 136, 0.2), rgba(0, 204, 119, 0.2));
            border-color: var(--green);
            box-shadow: 0 10px 40px rgba(0, 255, 136, 0.4);
        }

        .player-row.winner::after {
            content: '👑 WINNER';
            position: absolute;
            top: -15px;
            right: 30px;
            background: linear-gradient(135deg, var(--green), #00CC77);
            color: #fff;
            padding: 0.6rem 1.5rem;
            border-radius: 50px;
            font-family: 'Orbitron', sans-serif;
            font-weight: 800;
            font-size: 0.9rem;
            letter-spacing: 1px;
            box-shadow: 0 10px 30px rgba(0, 255, 136, 0.6);
            animation: winnerBadge 2s ease-in-out infinite;
        }

        @keyframes winnerBadge {
            0%, 100% { transform: scale(1) rotate(-3deg); }
            50% { transform: scale(1.05) rotate(3deg); }
        }

        .player-row.loser {
            opacity: 0.5;
            background: rgba(255, 0, 85, 0.1);
        }

        .player-info {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            flex: 1;
        }

        .player-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 4px solid var(--gold);
            object-fit: cover;
            box-shadow: 0 10px 30px rgba(255, 215, 0, 0.5);
            transition: all 0.4s ease;
        }

        .player-row:hover .player-avatar {
            transform: scale(1.15) rotate(5deg);
            box-shadow: 0 15px 50px rgba(255, 215, 0, 0.8);
        }

        .player-row.winner .player-avatar {
            border-color: var(--green);
            box-shadow: 0 10px 40px rgba(0, 255, 136, 0.7);
        }

        .player-details {
            flex: 1;
        }

        .player-name {
            font-family: 'Rajdhani', sans-serif;
            font-size: 1.6rem;
            font-weight: 800;
            color: #fff;
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 10px rgba(255, 215, 0, 0.3);
        }

        .player-row.loser .player-name {
            text-decoration: line-through;
            color: #666;
        }

        .player-email {
            font-size: 1rem;
            color: #888;
        }

        .score-display {
            font-family: 'Orbitron', sans-serif;
            font-size: 3rem;
            font-weight: 900;
            color: var(--gold);
            min-width: 80px;
            text-align: center;
            text-shadow: 0 0 20px rgba(255, 215, 0, 0.6);
        }

        .player-row.winner .score-display {
            color: var(--green);
            text-shadow: 0 0 30px rgba(0, 255, 136, 0.8);
            animation: scoreGlow 2s ease-in-out infinite;
        }

        @keyframes scoreGlow {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        /* ══════════════════════════════════════════════════════════════
           VS TEXT
           ══════════════════════════════════════════════════════════════ */
        .vs-section {
            text-align: center;
            margin: 1.5rem 0;
            position: relative;
        }

        .vs-text {
            display: inline-block;
            font-family: 'Orbitron', sans-serif;
            font-size: 2rem;
            font-weight: 900;
            background: linear-gradient(135deg, var(--gold), var(--orange), var(--red));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: 5px;
            animation: vsGlow 2s ease-in-out infinite;
        }

        @keyframes vsGlow {
            0%, 100% { filter: drop-shadow(0 0 10px var(--gold)); }
            50% { filter: drop-shadow(0 0 25px var(--gold)); }
        }

        /* ══════════════════════════════════════════════════════════════
           MATCH RESULT
           ══════════════════════════════════════════════════════════════ */
        .match-result {
            text-align: center;
            padding: 2rem;
            background: linear-gradient(135deg, rgba(0, 255, 136, 0.2), rgba(0, 204, 119, 0.2));
            border: 3px solid var(--green);
            border-radius: 20px;
            margin-top: 2rem;
            position: relative;
            z-index: 1;
        }

        .result-title {
            font-family: 'Orbitron', sans-serif;
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--green);
            margin-bottom: 1rem;
            animation: resultPulse 2s ease-in-out infinite;
        }

        @keyframes resultPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .result-time {
            font-size: 1rem;
            color: #888;
        }

        /* ══════════════════════════════════════════════════════════════
           WAITING STATE
           ══════════════════════════════════════════════════════════════ */
        .match-waiting {
            text-align: center;
            padding: 3rem;
            background: rgba(255, 215, 0, 0.05);
            border: 3px dashed rgba(255, 215, 0, 0.3);
            border-radius: 20px;
            margin-top: 2rem;
            position: relative;
            z-index: 1;
        }

        .waiting-icon {
            font-size: 4rem;
            color: var(--gold);
            margin-bottom: 1rem;
            animation: waitingSpin 3s linear infinite;
        }

        @keyframes waitingSpin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .waiting-text {
            font-size: 1.4rem;
            color: #888;
            font-style: italic;
        }

        /* ══════════════════════════════════════════════════════════════
           LIVE BADGE
           ══════════════════════════════════════════════════════════════ */
        .live-badge {
            background: linear-gradient(135deg, var(--red), var(--orange));
            color: #fff;
            padding: 1rem 2rem;
            border-radius: 15px;
            font-family: 'Orbitron', sans-serif;
            font-weight: 800;
            text-transform: uppercase;
            text-align: center;
            margin-bottom: 2rem;
            animation: liveBadgePulse 1.5s ease-in-out infinite;
            position: relative;
            z-index: 1;
        }

        @keyframes liveBadgePulse {
            0%, 100% { box-shadow: 0 5px 20px rgba(255, 0, 85, 0.5); }
            50% { box-shadow: 0 10px 40px rgba(255, 0, 85, 0.8); }
        }

        .live-badge i {
            margin-right: 0.8rem;
            animation: blink 1s ease-in-out infinite;
        }

        /* ══════════════════════════════════════════════════════════════
           GRAND FINAL SECTION
           ══════════════════════════════════════════════════════════════ */
        .grand-final-section {
            min-width: 700px;
            flex: 1.5;
        }

        .grand-final-header {
            text-align: center;
            margin-bottom: 4rem;
        }

        .grand-final-title {
            font-family: 'Orbitron', sans-serif;
            font-size: 5rem;
            font-weight: 900;
            background: linear-gradient(135deg, var(--gold) 0%, var(--orange) 50%, var(--red) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: grandFinalGlow 3s ease-in-out infinite;
        }

        @keyframes grandFinalGlow {
            0%, 100% { filter: drop-shadow(0 0 30px var(--gold)); }
            50% { filter: drop-shadow(0 0 60px var(--gold)); }
        }

        .grand-final-title i {
            display: block;
            font-size: 6rem;
            margin-bottom: 1.5rem;
            animation: trophyBounce 3s ease-in-out infinite;
        }

        @keyframes trophyBounce {
            0%, 100% { transform: translateY(0) rotate(0deg) scale(1); }
            25% { transform: translateY(-20px) rotate(-5deg) scale(1.05); }
            50% { transform: translateY(0) rotate(0deg) scale(1); }
            75% { transform: translateY(-20px) rotate(5deg) scale(1.05); }
        }

        /* ══════════════════════════════════════════════════════════════
           EMPTY STATE
           ══════════════════════════════════════════════════════════════ */
        .empty-state {
            text-align: center;
            padding: 8rem 4rem;
            background: linear-gradient(135deg, rgba(26, 26, 46, 0.95), rgba(22, 33, 62, 0.95));
            border: 4px solid var(--gold);
            border-radius: 30px;
            max-width: 1200px;
            margin: 4rem auto;
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.7);
        }

        .empty-icon {
            font-size: 10rem;
            color: rgba(255, 215, 0, 0.3);
            margin-bottom: 3rem;
            animation: emptyFloat 4s ease-in-out infinite;
        }

        @keyframes emptyFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-30px); }
        }

        .empty-title {
            font-family: 'Orbitron', sans-serif;
            font-size: 4rem;
            font-weight: 900;
            color: var(--gold);
            margin-bottom: 2rem;
        }

        .empty-text {
            font-size: 1.6rem;
            color: #aaa;
            margin-bottom: 3rem;
            line-height: 1.8;
        }

        /* ══════════════════════════════════════════════════════════════
           REFRESH NOTIFICATION
           ══════════════════════════════════════════════════════════════ */
        .refresh-notif {
            position: fixed;
            top: 100px;
            right: 30px;
            background: linear-gradient(135deg, var(--green), #00CC77);
            color: #fff;
            padding: 1.5rem 2rem;
            border-radius: 20px;
            font-family: 'Orbitron', sans-serif;
            font-weight: 800;
            box-shadow: 0 20px 60px rgba(0, 255, 136, 0.6);
            z-index: 99999;
            display: none;
            align-items: center;
            gap: 1rem;
            animation: slideIn 0.5s ease;
        }

        .refresh-notif.show {
            display: flex;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(100px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .refresh-notif i {
            animation: spin 1s linear infinite;
        }

        /* ══════════════════════════════════════════════════════════════
           RESPONSIVE DESIGN
           ══════════════════════════════════════════════════════════════ */
        @media (max-width: 1400px) {
            .hero-title {
                font-size: 3rem;
            }
            
            .pool-ultra {
                min-width: 450px;
            }
            
            .grand-final-section {
                min-width: 600px;
            }
        }

        @media (max-width: 768px) {
            .nav-container {
                padding: 0 1.5rem;
                flex-direction: column;
                gap: 1rem;
            }
            
            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .hero-title {
                font-size: 2.5rem;
            }
            
            .hero-stats {
                grid-template-columns: 1fr;
            }
            
            .pool-ultra,
            .grand-final-section {
                min-width: 350px;
            }
            
            .player-avatar {
                width: 60px;
                height: 60px;
            }
            
            .score-display {
                font-size: 2rem;
            }

            .grand-final-title {
                font-size: 3rem;
            }
        }
    </style>
</head>
<body>
    <!-- Particles Background -->
    <div id="particles-js"></div>

    <!-- Refresh Notification -->
    <div class="refresh-notif" id="refreshNotif">
        <i class="fas fa-sync-alt"></i>
        <span>Bracket Updated! Refreshing...</span>
    </div>

    <div class="content-wrapper">
        <!-- Premium Navbar -->
        <nav class="navbar-ultra">
            <div class="nav-container">
                <a href="index.php" class="nav-logo">
                    <i class="fas fa-dice-d20"></i>
                    LEGACY BILLIARD
                </a>
                <div class="nav-links">
                    <a href="index.php" class="nav-link">
                        <i class="fas fa-home"></i>
                        Home
                    </a>
                    <a href="tournament.php" class="nav-link active">
                        <i class="fas fa-trophy"></i>
                        Tournaments
                    </a>
                    <?php if ($is_logged_in): ?>
                        <a href="profile.php" class="nav-link">
                            <i class="fas fa-user"></i>
                            Profile
                        </a>
                        <a href="logout.php" class="nav-link">
                            <i class="fas fa-sign-out-alt"></i>
                            Logout
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="nav-link">
                            <i class="fas fa-sign-in-alt"></i>
                            Login
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>

        <!-- Tournament Hero -->
        <div class="hero-ultra">
            <div class="hero-card">
                <h1 class="hero-title">
                    <i class="fas fa-sitemap"></i>
                    <?= htmlspecialchars($tournament['nama_tournament']) ?>
                </h1>
                
                <div class="hero-stats">
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-users"></i></div>
                        <div class="stat-value"><?= $tournament['total_participants'] ?>/<?= $tournament['max_peserta'] ?></div>
                        <div class="stat-label">Participants</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-calendar"></i></div>
                        <div class="stat-value"><?= date('d M', strtotime($tournament['tanggal_mulai'])) ?></div>
                        <div class="stat-label">Tournament Date</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-clock"></i></div>
                        <div class="stat-value"><?= date('H:i', strtotime($tournament['waktu_mulai'])) ?></div>
                        <div class="stat-label">Start Time WIB</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-trophy"></i></div>
                        <div class="stat-value"><?= htmlspecialchars($tournament['hadiah']) ?></div>
                        <div class="stat-label">Prize Pool</div>
                    </div>
                </div>
                
                <div style="text-align: center;">
                    <div class="status-mega <?= $tournament['status'] ?>">
                        <i class="fas fa-circle"></i>
                        <?= strtoupper($tournament['status']) ?>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($tournament['status'] === 'ongoing' && $has_bracket): ?>
            <!-- Live Indicator -->
            <div class="live-mega">
                <div class="live-card">
                    <div class="live-dot"></div>
                    <div class="live-text">
                        <i class="fas fa-broadcast-tower"></i>
                        LIVE TOURNAMENT - REAL-TIME UPDATES
                    </div>
                    <div class="auto-refresh">
                        <i class="fas fa-sync-alt"></i>
                        Auto-refresh every 10s
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($is_participant): ?>
            <!-- Participant Alert -->
            <div class="participant-alert">
                <div class="alert-card">
                    <i class="fas fa-star"></i>
                    <strong>YOU ARE COMPETING IN THIS TOURNAMENT!</strong> Pool <?= $user_pool ?> - GOOD LUCK! 🔥
                </div>
            </div>
        <?php endif; ?>

        <?php if ($has_bracket): ?>
            <!-- Progress Bar -->
            <div class="progress-ultra">
                <div class="progress-card">
                    <div class="progress-title">
                        <i class="fas fa-chart-line"></i>
                        Tournament Progress: <?= $completed_matches ?> / <?= $total_matches ?> Matches Completed
                    </div>
                    <div class="progress-bar-container">
                        <div class="progress-bar-fill" style="width: <?= $bracket_progress ?>%">
                            <?= $bracket_progress ?>%
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($tournament['status'] === 'completed' && $tournament['winner_name']): ?>
                <!-- Champion Banner -->
                <div class="champion-ultra">
                    <div class="champion-card">
                        <div class="champion-title">
                            <i class="fas fa-crown"></i>
                            TOURNAMENT CHAMPION
                        </div>
                        <div class="champion-display">
                            <img src="images/<?= $tournament['winner_photo'] ?? 'avataruser.jpg' ?>" alt="Champion" class="champion-avatar" onerror="this.src='images/avataruser.jpg'">
                            <div class="champion-name"><?= htmlspecialchars($tournament['winner_name']) ?></div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Bracket Container -->
            <div class="bracket-ultra">
                <div class="bracket-grid">
                    <!-- ═══════════════════════════════════════════════
                         POOL A
                         ═══════════════════════════════════════════════ -->
                    <div class="pool-ultra">
                        <div class="pool-header">
                            <i class="fas fa-layer-group"></i>
                            POOL A
                        </div>

                        <!-- Participants List -->
                        <?php if (count($participants['A']) > 0): ?>
                            <div class="participants-box">
                                <div class="participants-title">
                                    <i class="fas fa-users"></i>
                                    COMPETITORS
                                </div>
                                <?php foreach ($participants['A'] as $p): ?>
                                    <div class="participant-item <?= ($is_logged_in && $p['user_id'] == $user_id) ? 'highlight' : '' ?>">
                                        <img src="images/<?= $p['foto_profil'] ?? 'avataruser.jpg' ?>" alt="Player" class="participant-avatar" onerror="this.src='images/avataruser.jpg'">
                                        <div class="participant-info">
                                            <div class="participant-name"><?= htmlspecialchars($p['nama']) ?></div>
                                            <div class="participant-record"><?= $p['wins'] ?>W - <?= $p['losses'] ?>L | Score: <?= $p['total_score'] ?? 0 ?></div>
                                        </div>
                                        <div class="participant-seed">SEED <?= $p['seed_position'] ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Semifinals Pool A -->
                        <?php if (count($matches['semifinal']['A']) > 0): ?>
                            <div class="round-section">
                                <div class="round-title">
                                    <i class="fas fa-chess-knight"></i>
                                    SEMIFINALS
                                </div>
                            </div>
                            <?php foreach ($matches['semifinal']['A'] as $match): ?>
                                <?php include 'match_card_template.php'; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <!-- Final Pool A -->
                        <?php if (count($matches['final_pool']['A']) > 0): ?>
                            <div class="round-section">
                                <div class="round-title">
                                    <i class="fas fa-crown"></i>
                                    POOL A FINAL
                                </div>
                            </div>
                            <?php foreach ($matches['final_pool']['A'] as $match): ?>
                                <?php include 'match_card_template.php'; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- ═══════════════════════════════════════════════
                         GRAND FINAL
                         ═══════════════════════════════════════════════ -->
                    <div class="grand-final-section">
                        <div class="grand-final-header">
                            <div class="grand-final-title">
                                <i class="fas fa-trophy"></i>
                                GRAND FINAL
                            </div>
                        </div>

                        <?php if (count($matches['grand_final']) > 0): ?>
                            <?php $match = $matches['grand_final'][0]; ?>
                            <?php include 'match_card_template.php'; ?>
                        <?php else: ?>
                            <div class="match-waiting">
                                <div class="waiting-icon"><i class="fas fa-hourglass-half"></i></div>
                                <div class="waiting-text">Awaiting Pool Champions...</div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- ═══════════════════════════════════════════════
                         POOL B
                         ═══════════════════════════════════════════════ -->
                    <div class="pool-ultra">
                        <div class="pool-header">
                            <i class="fas fa-layer-group"></i>
                            POOL B
                        </div>

                        <!-- Participants List -->
                        <?php if (count($participants['B']) > 0): ?>
                            <div class="participants-box">
                                <div class="participants-title">
                                    <i class="fas fa-users"></i>
                                    COMPETITORS
                                </div>
                                <?php foreach ($participants['B'] as $p): ?>
                                    <div class="participant-item <?= ($is_logged_in && $p['user_id'] == $user_id) ? 'highlight' : '' ?>">
                                        <img src="images/<?= $p['foto_profil'] ?? 'avataruser.jpg' ?>" alt="Player" class="participant-avatar" onerror="this.src='images/avataruser.jpg'">
                                        <div class="participant-info">
                                            <div class="participant-name"><?= htmlspecialchars($p['nama']) ?></div>
                                            <div class="participant-record"><?= $p['wins'] ?>W - <?= $p['losses'] ?>L | Score: <?= $p['total_score'] ?? 0 ?></div>
                                        </div>
                                        <div class="participant-seed">SEED <?= $p['seed_position'] ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Semifinals Pool B -->
                        <?php if (count($matches['semifinal']['B']) > 0): ?>
                            <div class="round-section">
                                <div class="round-title">
                                    <i class="fas fa-chess-knight"></i>
                                    SEMIFINALS
                                </div>
                            </div>
                            <?php foreach ($matches['semifinal']['B'] as $match): ?>
                                <?php include 'match_card_template.php'; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <!-- Final Pool B -->
                        <?php if (count($matches['final_pool']['B']) > 0): ?>
                            <div class="round-section">
                                <div class="round-title">
                                    <i class="fas fa-crown"></i>
                                    POOL B FINAL
                                </div>
                            </div>
                            <?php foreach ($matches['final_pool']['B'] as $match): ?>
                                <?php include 'match_card_template.php'; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <!-- Empty State -->
            <div class="empty-state">
                <div class="empty-icon"><i class="fas fa-hourglass-half"></i></div>
                <div class="empty-title">BRACKET NOT YET GENERATED</div>
                <div class="empty-text">
                    Tournament bracket will be created once all <?= $tournament['max_peserta'] ?> participants are registered.<br>
                    <strong>Current: <?= $tournament['total_participants'] ?> / <?= $tournament['max_peserta'] ?> players registered</strong>
                </div>
                <div style="max-width: 800px; margin: 0 auto;">
                    <div class="progress-bar-container">
                        <div class="progress-bar-fill" style="width: <?= number_format(($tournament['total_participants'] / $tournament['max_peserta'] * 100), 0) ?>%">
                            <?= number_format(($tournament['total_participants'] / $tournament['max_peserta'] * 100), 0) ?>% Full
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- ══════════════════════════════════════════════════════════════
         JAVASCRIPT - PARTICLES & REAL-TIME
         ══════════════════════════════════════════════════════════════ -->
    <script>
        // Particles.js Configuration
        particlesJS('particles-js', {
            particles: {
                number: { value: 80, density: { enable: true, value_area: 800 } },
                color: { value: '#FFD700' },
                shape: { type: 'circle' },
                opacity: { value: 0.5, random: true },
                size: { value: 3, random: true },
                line_linked: {
                    enable: true,
                    distance: 150,
                    color: '#FFD700',
                    opacity: 0.2,
                    width: 1
                },
                move: {
                    enable: true,
                    speed: 2,
                    direction: 'none',
                    random: true,
                    straight: false,
                    out_mode: 'out',
                    bounce: false
                }
            },
            interactivity: {
                detect_on: 'canvas',
                events: {
                    onhover: { enable: true, mode: 'repulse' },
                    onclick: { enable: true, mode: 'push' },
                    resize: true
                },
                modes: {
                    repulse: { distance: 100, duration: 0.4 },
                    push: { particles_nb: 4 }
                }
            },
            retina_detect: true
        });

        // ══════════════════════════════════════════════════════════════
        // REAL-TIME AUTO-REFRESH SYSTEM
        // ══════════════════════════════════════════════════════════════
        <?php if ($tournament['status'] === 'ongoing' && $has_bracket): ?>
        let lastUpdate = Date.now();
        const refreshInterval = 10000; // 10 seconds

        async function checkForUpdates() {
            try {
                const response = await fetch('check_bracket_updates.php?id=<?= $tournament_id ?>&last_update=' + lastUpdate);
                const data = await response.json();
                
                if (data.has_updates) {
                    // Show notification
                    const notif = document.getElementById('refreshNotif');
                    notif.classList.add('show');
                    
                    // Reload page after 2 seconds
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    lastUpdate = Date.now();
                }
            } catch (error) {
                console.error('❌ Error checking updates:', error);
            }
        }

        // Start auto-refresh
        setInterval(checkForUpdates, refreshInterval);
        console.log('✅ Real-time auto-refresh enabled (10s interval)');
        <?php endif; ?>

        // ══════════════════════════════════════════════════════════════
        // SMOOTH HORIZONTAL SCROLL
        // ══════════════════════════════════════════════════════════════
        const bracketContainer = document.querySelector('.bracket-ultra');
        if (bracketContainer) {
            bracketContainer.addEventListener('wheel', (e) => {
                e.preventDefault();
                bracketContainer.scrollLeft += e.deltaY;
            });
        }

        // ══════════════════════════════════════════════════════════════
        // CONSOLE LOG
        // ══════════════════════════════════════════════════════════════
        console.log('🏆 LEGACY BILLIARD - MEGA ULTIMATE EDITION LOADED');
        console.log('📊 Tournament ID:', <?= $tournament_id ?>);
        console.log('🎮 Has Bracket:', <?= $has_bracket ? 'true' : 'false' ?>);
        console.log('🔥 Status:', '<?= $tournament['status'] ?>');
        console.log('✅ Total Matches:', <?= $total_matches ?>);
        console.log('✅ Completed:', <?= $completed_matches ?>);
        console.log('📈 Progress:', '<?= $bracket_progress ?>%');
    </script>
</body>
</html>
