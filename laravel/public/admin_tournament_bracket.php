<?php
/**
 * ============================================
 * LEGACY BILLIARD - ADMIN TOURNAMENT BRACKET
 * 🏆 ULTIMATE EDITION - FULL FEATURED 🏆
 * ============================================
 * Author: AI Assistant
 * Version: 2.0 ULTIMATE
 * Last Updated: December 5, 2025
 * ============================================
 * FEATURES:
 * - Dynamic Tournament Bracket (8 Players)
 * - Pool System (A & B - 4 players each)
 * - Semifinal → Final Pool → Grand Final
 * - Real-time Winner Selection
 * - Beautiful Animated UI
 * - Match Statistics
 * - Participant Management
 * - Tournament History Log
 * ============================================
 */

require_once 'config.php';
require_once 'admin_check.php';

$tournament_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($tournament_id <= 0) {
    $_SESSION['error_message'] = '❌ Invalid tournament ID';
    header('Location: admin_tournaments.php');
    exit;
}

// ============================================
// HANDLE GENERATE BRACKET ACTION
// ============================================
if (isset($_GET['action']) && $_GET['action'] === 'generate_bracket') {
    // Check if bracket already exists
    $check_query = "SELECT COUNT(*) as count FROM tournament_matches WHERE tournament_id = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("i", $tournament_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result()->fetch_assoc();
    $check_stmt->close();
    
    if ($check_result['count'] > 0) {
        $_SESSION['error_message'] = '⚠️ Bracket already generated for this tournament!';
    } else {
        // Get participants grouped by pool
        $participants_query = "SELECT user_id, pool, seed_position FROM tournament_participants WHERE tournament_id = ? ORDER BY pool, seed_position";
        $stmt = $conn->prepare($participants_query);
        $stmt->bind_param("i", $tournament_id);
        $stmt->execute();
        $participants_result = $stmt->get_result();
        
        $participants = ['A' => [], 'B' => []];
        while ($row = $participants_result->fetch_assoc()) {
            $participants[$row['pool']][] = $row['user_id'];
        }
        $stmt->close();
        
        // Validate participant count
        if (count($participants['A']) == 4 && count($participants['B']) == 4) {
            $conn->begin_transaction();
            
            try {
                // ============================================
                // GENERATE SEMIFINALS - POOL A (2 MATCHES)
                // ============================================
                $stmt = $conn->prepare("INSERT INTO tournament_matches (tournament_id, round, match_number, pool, player1_id, player2_id, status, created_at) VALUES (?, 'semifinal', ?, 'A', ?, ?, 'pending', NOW())");
                
                // Match 1: Seed 1 vs Seed 2
                $match_num = 1;
                $stmt->bind_param("iiii", $tournament_id, $match_num, $participants['A'][0], $participants['A'][1]);
                $stmt->execute();
                
                // Match 2: Seed 3 vs Seed 4
                $match_num = 2;
                $stmt->bind_param("iiii", $tournament_id, $match_num, $participants['A'][2], $participants['A'][3]);
                $stmt->execute();
                $stmt->close();
                
                // ============================================
                // GENERATE SEMIFINALS - POOL B (2 MATCHES)
                // ============================================
                $stmt = $conn->prepare("INSERT INTO tournament_matches (tournament_id, round, match_number, pool, player1_id, player2_id, status, created_at) VALUES (?, 'semifinal', ?, 'B', ?, ?, 'pending', NOW())");
                
                // Match 3: Seed 1 vs Seed 2
                $match_num = 3;
                $stmt->bind_param("iiii", $tournament_id, $match_num, $participants['B'][0], $participants['B'][1]);
                $stmt->execute();
                
                // Match 4: Seed 3 vs Seed 4
                $match_num = 4;
                $stmt->bind_param("iiii", $tournament_id, $match_num, $participants['B'][2], $participants['B'][3]);
                $stmt->execute();
                $stmt->close();
                
                // ============================================
                // GENERATE POOL FINALS (2 MATCHES - EMPTY)
                // ============================================
                $stmt = $conn->prepare("INSERT INTO tournament_matches (tournament_id, round, match_number, pool, status, created_at) VALUES (?, 'final_pool', 1, ?, 'pending', NOW())");
                
                $pool_a = 'A';
                $stmt->bind_param("is", $tournament_id, $pool_a);
                $stmt->execute();
                
                $pool_b = 'B';
                $stmt->bind_param("is", $tournament_id, $pool_b);
                $stmt->execute();
                $stmt->close();
                
                // ============================================
                // GENERATE GRAND FINAL (1 MATCH - EMPTY)
                // ============================================
                $stmt = $conn->prepare("INSERT INTO tournament_matches (tournament_id, round, match_number, status, created_at) VALUES (?, 'grand_final', 1, 'pending', NOW())");
                $stmt->bind_param("i", $tournament_id);
                $stmt->execute();
                $stmt->close();
                
                // ============================================
                // UPDATE TOURNAMENT STATUS TO ONGOING
                // ============================================
                $update_tournament = $conn->prepare("UPDATE tournaments SET status = 'ongoing', updated_at = NOW() WHERE id = ?");
                $update_tournament->bind_param("i", $tournament_id);
                $update_tournament->execute();
                $update_tournament->close();
                
                $conn->commit();
                $_SESSION['success_message'] = '🎉 BRACKET GENERATED SUCCESSFULLY! Tournament is now ONGOING!';
                
            } catch (Exception $e) {
                $conn->rollback();
                $_SESSION['error_message'] = '❌ Error generating bracket: ' . $e->getMessage();
            }
        } else {
            $_SESSION['error_message'] = '⚠️ Need exactly 4 participants in each pool! Current: Pool A (' . count($participants['A']) . '), Pool B (' . count($participants['B']) . ')';
        }
    }
    
    header("Location: admin_tournament_bracket.php?id=$tournament_id");
    exit;
}

// ============================================
// HANDLE SET WINNER ACTION
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'set_winner') {
        $match_id = intval($_POST['match_id']);
        $winner_id = intval($_POST['winner_id']);
        $player1_score = intval($_POST['player1_score']);
        $player2_score = intval($_POST['player2_score']);
        
        // Validate scores
        if ($player1_score < 0 || $player2_score < 0) {
            $_SESSION['error_message'] = '⚠️ Invalid scores! Scores cannot be negative.';
            header("Location: admin_tournament_bracket.php?id=$tournament_id");
            exit;
        }
        
        if ($player1_score == $player2_score) {
            $_SESSION['error_message'] = '⚠️ Invalid scores! There must be a clear winner.';
            header("Location: admin_tournament_bracket.php?id=$tournament_id");
            exit;
        }
        
        $conn->begin_transaction();
        
        try {
            // Get match details
            $match_query = "SELECT * FROM tournament_matches WHERE id = ?";
            $stmt = $conn->prepare($match_query);
            $stmt->bind_param("i", $match_id);
            $stmt->execute();
            $match = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            
            if (!$match) {
                throw new Exception("Match not found");
            }
            
            // Validate winner is one of the players
            if ($winner_id != $match['player1_id'] && $winner_id != $match['player2_id']) {
                throw new Exception("Invalid winner selection");
            }
            
            // Ensure the correct player won based on scores
            if ($player1_score > $player2_score && $winner_id != $match['player1_id']) {
                throw new Exception("Score mismatch with winner selection");
            }
            if ($player2_score > $player1_score && $winner_id != $match['player2_id']) {
                throw new Exception("Score mismatch with winner selection");
            }
            
            // Update match result
            $update_stmt = $conn->prepare("UPDATE tournament_matches SET winner_id = ?, player1_score = ?, player2_score = ?, status = 'completed', completed_at = NOW() WHERE id = ?");
            $update_stmt->bind_param("iiii", $winner_id, $player1_score, $player2_score, $match_id);
            $update_stmt->execute();
            $update_stmt->close();
            
            // ============================================
            // ADVANCE WINNER TO NEXT ROUND
            // ============================================
            $next_round = '';
            $next_pool = $match['pool'];
            
            if ($match['round'] === 'semifinal') {
                // Winner advances to Pool Final
                $next_round = 'final_pool';
                
                $next_stmt = $conn->prepare("SELECT id, player1_id FROM tournament_matches WHERE tournament_id = ? AND round = ? AND pool = ? LIMIT 1");
                $next_stmt->bind_param("iss", $tournament_id, $next_round, $next_pool);
                $next_stmt->execute();
                $next_match = $next_stmt->get_result()->fetch_assoc();
                $next_stmt->close();
                
                if ($next_match) {
                    // Assign winner to next match
                    $player_field = $next_match['player1_id'] ? 'player2_id' : 'player1_id';
                    $update_next = $conn->prepare("UPDATE tournament_matches SET $player_field = ? WHERE id = ?");
                    $update_next->bind_param("ii", $winner_id, $next_match['id']);
                    $update_next->execute();
                    $update_next->close();
                }
                
            } elseif ($match['round'] === 'final_pool') {
                // Winner advances to Grand Final
                $next_round = 'grand_final';
                
                $next_stmt = $conn->prepare("SELECT id, player1_id FROM tournament_matches WHERE tournament_id = ? AND round = ? LIMIT 1");
                $next_stmt->bind_param("is", $tournament_id, $next_round);
                $next_stmt->execute();
                $next_match = $next_stmt->get_result()->fetch_assoc();
                $next_stmt->close();
                
                if ($next_match) {
                    // Assign pool champion to grand final
                    $player_field = $next_match['player1_id'] ? 'player2_id' : 'player1_id';
                    $update_next = $conn->prepare("UPDATE tournament_matches SET $player_field = ? WHERE id = ?");
                    $update_next->bind_param("ii", $winner_id, $next_match['id']);
                    $update_next->execute();
                    $update_next->close();
                }
                
            } elseif ($match['round'] === 'grand_final') {
                // Tournament completed - set champion
                $update_tournament = $conn->prepare("UPDATE tournaments SET status = 'completed', winner_id = ?, updated_at = NOW() WHERE id = ?");
                $update_tournament->bind_param("ii", $winner_id, $tournament_id);
                $update_tournament->execute();
                $update_tournament->close();
            }
            
            $conn->commit();
            $_SESSION['success_message'] = '✅ Winner set successfully! Match result recorded.';
            
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['error_message'] = '❌ Error: ' . $e->getMessage();
        }
        
        header("Location: admin_tournament_bracket.php?id=$tournament_id");
        exit;
    }
}

// ============================================
// HANDLE RESET MATCH ACTION
// ============================================
if (isset($_GET['action']) && $_GET['action'] === 'reset_match' && isset($_GET['match_id'])) {
    $match_id = intval($_GET['match_id']);
    
    $conn->begin_transaction();
    
    try {
        // Get match details
        $match_query = "SELECT * FROM tournament_matches WHERE id = ?";
        $stmt = $conn->prepare($match_query);
        $stmt->bind_param("i", $match_id);
        $stmt->execute();
        $match = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if ($match && $match['status'] === 'completed') {
            // Reset match
            $reset_stmt = $conn->prepare("UPDATE tournament_matches SET winner_id = NULL, player1_score = NULL, player2_score = NULL, status = 'pending', completed_at = NULL WHERE id = ?");
            $reset_stmt->bind_param("i", $match_id);
            $reset_stmt->execute();
            $reset_stmt->close();
            
            // Remove winner from next round if exists
            if ($match['round'] === 'semifinal') {
                $next_stmt = $conn->prepare("UPDATE tournament_matches SET player1_id = NULL, player2_id = NULL WHERE tournament_id = ? AND round = 'final_pool' AND pool = ?");
                $next_stmt->bind_param("is", $tournament_id, $match['pool']);
                $next_stmt->execute();
                $next_stmt->close();
                
            } elseif ($match['round'] === 'final_pool') {
                $next_stmt = $conn->prepare("UPDATE tournament_matches SET player1_id = NULL, player2_id = NULL WHERE tournament_id = ? AND round = 'grand_final'");
                $next_stmt->bind_param("i", $tournament_id);
                $next_stmt->execute();
                $next_stmt->close();
            }
            
            $conn->commit();
            $_SESSION['success_message'] = '♻️ Match reset successfully!';
        }
        
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error_message'] = '❌ Error resetting match: ' . $e->getMessage();
    }
    
    header("Location: admin_tournament_bracket.php?id=$tournament_id");
    exit;
}

// ============================================
// GET TOURNAMENT DATA
// ============================================
$tournament_query = "
    SELECT t.*, 
           COUNT(DISTINCT tp.id) as total_participants,
           w.nama as champion_name,
           w.foto_profil as champion_photo
    FROM tournaments t
    LEFT JOIN tournament_participants tp ON t.id = tp.tournament_id
    LEFT JOIN users w ON t.winner_id = w.id
    WHERE t.id = ?
    GROUP BY t.id
";
$stmt = $conn->prepare($tournament_query);
$stmt->bind_param("i", $tournament_id);
$stmt->execute();
$tournament = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$tournament) {
    $_SESSION['error_message'] = '❌ Tournament not found';
    header('Location: admin_tournaments.php');
    exit;
}

// ============================================
// GET ALL MATCHES WITH PLAYER INFO
// ============================================
$matches_query = "
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
$stmt = $conn->prepare($matches_query);
$stmt->bind_param("i", $tournament_id);
$stmt->execute();
$matches_result = $stmt->get_result();

$matches = [
    'semifinal' => ['A' => [], 'B' => []],
    'final_pool' => ['A' => [], 'B' => []],
    'grand_final' => []
];

$completed_matches = 0;
$total_matches = 0;

while ($row = $matches_result->fetch_assoc()) {
    $total_matches++;
    if ($row['status'] === 'completed') {
        $completed_matches++;
    }
    
    if ($row['round'] === 'grand_final') {
        $matches['grand_final'][] = $row;
    } else {
        $pool = $row['pool'];
        $matches[$row['round']][$pool][] = $row;
    }
}
$stmt->close();

$has_bracket = (count($matches['semifinal']['A']) > 0 || count($matches['semifinal']['B']) > 0);
$bracket_progress = $total_matches > 0 ? round(($completed_matches / $total_matches) * 100) : 0;

// ============================================
// GET PARTICIPANTS WITH STATS
// ============================================
$participants_query = "
    SELECT 
        u.id, u.nama, u.email, u.foto_profil,
        tp.pool, tp.seed_position,
        (SELECT COUNT(*) FROM tournament_matches WHERE (player1_id = u.id OR player2_id = u.id) AND winner_id = u.id AND tournament_id = ?) as wins,
        (SELECT COUNT(*) FROM tournament_matches WHERE (player1_id = u.id OR player2_id = u.id) AND winner_id != u.id AND status = 'completed' AND tournament_id = ?) as losses
    FROM tournament_participants tp
    JOIN users u ON tp.user_id = u.id
    WHERE tp.tournament_id = ?
    ORDER BY tp.pool, tp.seed_position
";
$stmt = $conn->prepare($participants_query);
$stmt->bind_param("iii", $tournament_id, $tournament_id, $tournament_id);
$stmt->execute();
$participants_result = $stmt->get_result();

$participants = ['A' => [], 'B' => []];
while ($row = $participants_result->fetch_assoc()) {
    $participants[$row['pool']][] = $row;
}
$stmt->close();

$page_title = "Tournament Bracket - " . htmlspecialchars($tournament['nama_tournament']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    
    <style>
        :root {
            --primary-gold: #D4AF37;
            --secondary-gold: #F5D068;
            --dark-bg: #0a0a0a;
            --card-bg: #1a1a2e;
            --sidebar-bg: #16213e;
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
            --info: #17a2b8;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            background: var(--dark-bg);
            color: #fff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
        }

        .bracket-page {
            margin-left: 260px;
            padding: 2rem;
            min-height: 100vh;
        }

        /* ========================================== */
        /* TOURNAMENT HEADER */
        /* ========================================== */
        .tournament-header {
            background: linear-gradient(135deg, var(--card-bg), var(--sidebar-bg));
            border: 3px solid var(--primary-gold);
            border-radius: 20px;
            padding: 2.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 15px 50px rgba(212, 175, 55, 0.3);
            position: relative;
            overflow: hidden;
        }

        .tournament-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(212, 175, 55, 0.1) 0%, transparent 70%);
            animation: rotate 30s linear infinite;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .tournament-header h1 {
            color: var(--primary-gold);
            font-size: 3rem;
            font-weight: 900;
            margin-bottom: 1rem;
            text-shadow: 0 0 30px rgba(212, 175, 55, 0.5);
            position: relative;
            z-index: 1;
        }

        .tournament-header h1 i {
            margin-right: 1rem;
            animation: pulse 2s ease-in-out infinite;
        }

        .info-badges {
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
            position: relative;
            z-index: 1;
        }

        .info-badge {
            background: rgba(212, 175, 55, 0.15);
            border: 2px solid var(--primary-gold);
            padding: 1rem 1.8rem;
            border-radius: 15px;
            font-size: 1.15rem;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .info-badge::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(212, 175, 55, 0.3), transparent);
            transition: left 0.5s ease;
        }

        .info-badge:hover::before {
            left: 100%;
        }

        .info-badge:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(212, 175, 55, 0.4);
        }

        .info-badge i {
            color: var(--primary-gold);
            margin-right: 0.8rem;
            font-size: 1.3rem;
        }

        .status-badge {
            padding: 0.8rem 1.5rem;
            border-radius: 50px;
            font-weight: 900;
            font-size: 1.1rem;
            display: inline-flex;
            align-items: center;
            gap: 0.8rem;
        }

        .status-badge.ongoing {
            background: linear-gradient(135deg, var(--warning), #ff9800);
            color: #000;
            animation: pulse 2s ease-in-out infinite;
        }

        .status-badge.completed {
            background: linear-gradient(135deg, var(--success), #20c997);
            color: #fff;
        }

        .status-badge.open {
            background: linear-gradient(135deg, var(--info), #0dcaf0);
            color: #fff;
        }

        /* ========================================== */
        /* PROGRESS BAR */
        /* ========================================== */
        .progress-section {
            background: var(--card-bg);
            border: 2px solid rgba(212, 175, 55, 0.3);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .progress-section h3 {
            color: var(--primary-gold);
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .progress {
            height: 35px;
            background: rgba(0, 0, 0, 0.5);
            border-radius: 50px;
            overflow: hidden;
            box-shadow: inset 0 5px 15px rgba(0, 0, 0, 0.5);
        }

        .progress-bar {
            background: linear-gradient(90deg, var(--primary-gold), var(--secondary-gold));
            font-weight: 900;
            font-size: 1.1rem;
            line-height: 35px;
            text-shadow: 0 2px 5px rgba(0, 0, 0, 0.5);
            transition: width 1s ease;
            box-shadow: 0 0 20px rgba(212, 175, 55, 0.8);
        }

        /* ========================================== */
        /* ALERTS */
        /* ========================================== */
        .alert {
            border-radius: 15px;
            padding: 1.3rem 1.8rem;
            margin-bottom: 2rem;
            font-weight: 600;
            font-size: 1.1rem;
            border: 2px solid;
            animation: slideInDown 0.5s ease;
        }

        @keyframes slideInDown {
            from { opacity: 0; transform: translateY(-30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .alert-success {
            background: rgba(40, 167, 69, 0.2);
            border-color: var(--success);
            color: var(--success);
        }

        .alert-danger {
            background: rgba(220, 53, 69, 0.2);
            border-color: var(--danger);
            color: var(--danger);
        }

        .alert i {
            margin-right: 0.8rem;
            font-size: 1.3rem;
        }

        /* ========================================== */
        /* CHAMPION BANNER */
        /* ========================================== */
        .champion-banner {
            background: linear-gradient(135deg, rgba(212, 175, 55, 0.3), rgba(245, 208, 104, 0.2));
            border: 4px solid var(--primary-gold);
            border-radius: 25px;
            padding: 3rem;
            text-align: center;
            margin-bottom: 2rem;
            box-shadow: 0 20px 60px rgba(212, 175, 55, 0.5);
            animation: championGlow 2s ease-in-out infinite;
        }

        @keyframes championGlow {
            0%, 100% { box-shadow: 0 20px 60px rgba(212, 175, 55, 0.5); }
            50% { box-shadow: 0 20px 80px rgba(212, 175, 55, 0.8); }
        }

        .champion-banner h2 {
            color: var(--primary-gold);
            font-size: 3rem;
            font-weight: 900;
            margin-bottom: 2rem;
            text-shadow: 0 0 30px rgba(212, 175, 55, 0.8);
        }

        .champion-banner h2 i {
            font-size: 3.5rem;
            margin-right: 1rem;
            animation: bounce 2s ease-in-out infinite;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }

        .champion-info {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 2rem;
        }

        .champion-avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 5px solid var(--primary-gold);
            box-shadow: 0 10px 40px rgba(212, 175, 55, 0.6);
            object-fit: cover;
        }

        .champion-name {
            font-size: 2.5rem;
            font-weight: 900;
            color: #fff;
            text-shadow: 0 0 20px rgba(255, 255, 255, 0.5);
        }

        /* ========================================== */
        /* BRACKET CONTAINER */
        /* ========================================== */
        .bracket-container {
            display: flex;
            gap: 3rem;
            overflow-x: auto;
            padding: 2rem 0;
            scroll-behavior: smooth;
        }

        .bracket-container::-webkit-scrollbar {
            height: 12px;
        }

        .bracket-container::-webkit-scrollbar-track {
            background: rgba(212, 175, 55, 0.1);
            border-radius: 10px;
        }

        .bracket-container::-webkit-scrollbar-thumb {
            background: linear-gradient(90deg, var(--primary-gold), var(--secondary-gold));
            border-radius: 10px;
        }

        /* ========================================== */
        /* POOL SECTIONS */
        /* ========================================== */
        .pool-section {
            min-width: 480px;
            flex: 1;
        }

        .pool-header {
            background: linear-gradient(135deg, var(--primary-gold), var(--secondary-gold));
            color: #000;
            padding: 2rem;
            border-radius: 20px;
            text-align: center;
            font-size: 2rem;
            font-weight: 900;
            margin-bottom: 2rem;
            box-shadow: 0 15px 40px rgba(212, 175, 55, 0.5);
            position: relative;
            overflow: hidden;
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
            animation: slide 20s linear infinite;
        }

        @keyframes slide {
            from { transform: translate(0, 0); }
            to { transform: translate(50px, 50px); }
        }

        .pool-header i {
            margin-right: 1rem;
            font-size: 2.2rem;
        }

        .pool-stats {
            background: rgba(212, 175, 55, 0.1);
            border: 2px solid rgba(212, 175, 55, 0.3);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .pool-stats h4 {
            color: var(--primary-gold);
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .participant-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.8rem;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            margin-bottom: 0.5rem;
        }

        .participant-avatar-small {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 2px solid var(--primary-gold);
            object-fit: cover;
        }

        .participant-name-small {
            flex: 1;
            font-weight: 600;
        }

        .participant-seed {
            background: var(--primary-gold);
            color: #000;
            padding: 0.3rem 0.8rem;
            border-radius: 50px;
            font-weight: 900;
            font-size: 0.9rem;
        }

        .participant-record {
            font-size: 0.9rem;
            color: #aaa;
        }

        /* ========================================== */
        /* ROUND TITLES */
        /* ========================================== */
        .round-title {
            color: var(--primary-gold);
            font-size: 1.5rem;
            font-weight: 700;
            text-align: center;
            margin: 2.5rem 0 1.5rem;
            padding: 1.2rem;
            background: rgba(212, 175, 55, 0.1);
            border: 2px solid rgba(212, 175, 55, 0.3);
            border-radius: 15px;
            position: relative;
        }

        .round-title::before,
        .round-title::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 50px;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--primary-gold));
        }

        .round-title::before {
            left: 10px;
        }

        .round-title::after {
            right: 10px;
            background: linear-gradient(90deg, var(--primary-gold), transparent);
        }

        .round-title i {
            margin-right: 0.8rem;
            font-size: 1.7rem;
        }

        /* ========================================== */
        /* MATCH CARDS */
        /* ========================================== */
        .match-card {
            background: linear-gradient(135deg, var(--card-bg), var(--sidebar-bg));
            border: 3px solid rgba(212, 175, 55, 0.3);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            position: relative;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }

        .match-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 50px rgba(212, 175, 55, 0.4);
            border-color: var(--primary-gold);
        }

        .match-card.completed {
            border-color: var(--success);
            background: linear-gradient(135deg, rgba(40, 167, 69, 0.1), var(--card-bg));
        }

        .match-card.in-progress {
            border-color: var(--warning);
            animation: pulse 2s ease-in-out infinite;
        }

        .match-label {
            position: absolute;
            top: -18px;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, var(--primary-gold), var(--secondary-gold));
            color: #000;
            padding: 0.6rem 1.8rem;
            border-radius: 50px;
            font-weight: 900;
            font-size: 1rem;
            letter-spacing: 1px;
            box-shadow: 0 5px 20px rgba(212, 175, 55, 0.5);
        }

        .match-label i {
            margin-right: 0.5rem;
        }

        .match-time {
            text-align: center;
            color: #888;
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
            font-style: italic;
        }

        .match-time i {
            margin-right: 0.5rem;
        }

        /* ========================================== */
        /* PLAYER ROWS */
        /* ========================================== */
        .player-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.5rem;
            background: rgba(212, 175, 55, 0.05);
            border-radius: 15px;
            margin-bottom: 1rem;
            border: 3px solid transparent;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .player-row::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(212, 175, 55, 0.1), transparent);
            transition: left 0.5s ease;
        }

        .player-row:hover::before {
            left: 100%;
        }

        .player-row.winner {
            background: rgba(40, 167, 69, 0.2);
            border-color: var(--success);
            box-shadow: 0 5px 20px rgba(40, 167, 69, 0.3);
        }

        .player-row.winner::after {
            content: '👑 WINNER';
            position: absolute;
            top: -10px;
            right: 20px;
            background: linear-gradient(135deg, var(--success), #20c997);
            color: #fff;
            padding: 0.4rem 1rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 900;
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.5);
        }

        .player-row.loser {
            opacity: 0.5;
            background: rgba(220, 53, 69, 0.1);
            border-color: transparent;
        }

        .player-info {
            display: flex;
            align-items: center;
            gap: 1.2rem;
            flex: 1;
        }

        .player-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: 3px solid var(--primary-gold);
            object-fit: cover;
            box-shadow: 0 5px 15px rgba(212, 175, 55, 0.4);
            transition: all 0.3s ease;
        }

        .player-row:hover .player-avatar {
            transform: scale(1.1);
            box-shadow: 0 8px 25px rgba(212, 175, 55, 0.6);
        }

        .player-avatar-placeholder {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: 3px dashed rgba(212, 175, 55, 0.3);
            background: rgba(255, 255, 255, 0.05);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: #666;
        }

        .player-details {
            flex: 1;
        }

        .player-name {
            font-weight: 700;
            color: #fff;
            font-size: 1.2rem;
            margin-bottom: 0.3rem;
        }

        .player-email {
            font-size: 0.9rem;
            color: #888;
        }

        .player-row.loser .player-name {
            text-decoration: line-through;
            color: #999;
        }

        .score-input {
            width: 90px;
            text-align: center;
            background: rgba(0, 0, 0, 0.5);
            border: 3px solid var(--primary-gold);
            color: #fff;
            border-radius: 15px;
            padding: 0.8rem;
            font-weight: 900;
            font-size: 1.5rem;
            transition: all 0.3s ease;
        }

        .score-input:focus {
            outline: none;
            border-color: var(--secondary-gold);
            box-shadow: 0 0 20px rgba(212, 175, 55, 0.5);
            transform: scale(1.05);
        }

        .score-display {
            font-size: 2rem;
            font-weight: 900;
            color: var(--primary-gold);
            min-width: 60px;
            text-align: center;
            text-shadow: 0 0 15px rgba(212, 175, 55, 0.5);
        }

        .player-row.winner .score-display {
            color: var(--success);
            text-shadow: 0 0 15px rgba(40, 167, 69, 0.5);
        }

        /* ========================================== */
        /* VS TEXT */
        /* ========================================== */
        .vs-text {
            text-align: center;
            color: var(--primary-gold);
            font-weight: 900;
            font-size: 1.5rem;
            margin: 1rem 0;
            position: relative;
            text-shadow: 0 0 15px rgba(212, 175, 55, 0.5);
        }

        .vs-text::before,
        .vs-text::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 40%;
            height: 2px;
            background: linear-gradient(to right, transparent, var(--primary-gold), transparent);
        }

        .vs-text::before {
            left: 0;
        }

        .vs-text::after {
            right: 0;
        }

        /* ========================================== */
        /* WINNER CONTROLS */
        /* ========================================== */
        .winner-controls {
            text-align: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 2px solid rgba(212, 175, 55, 0.2);
        }

        .winner-controls label {
            color: var(--primary-gold);
            font-weight: 700;
            font-size: 1.2rem;
            margin-bottom: 1.5rem;
            display: block;
        }

        .winner-controls label i {
            margin-right: 0.8rem;
            font-size: 1.4rem;
        }

        .winner-buttons {
            display: flex;
            gap: 1.5rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-winner {
            background: linear-gradient(135deg, var(--success), #20c997);
            border: none;
            color: #fff;
            padding: 1.2rem 2.5rem;
            border-radius: 50px;
            font-weight: 900;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(40, 167, 69, 0.4);
            position: relative;
            overflow: hidden;
        }

        .btn-winner::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .btn-winner:hover::before {
            width: 300px;
            height: 300px;
        }

        .btn-winner:hover {
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 15px 50px rgba(40, 167, 69, 0.6);
        }

        .btn-winner i {
            margin-right: 0.8rem;
        }

        /* ========================================== */
        /* MATCH RESULT */
        /* ========================================== */
        .match-result {
            text-align: center;
            padding: 1.5rem;
            border-radius: 15px;
            font-weight: 700;
            font-size: 1.2rem;
            margin-top: 1.5rem;
            background: rgba(40, 167, 69, 0.2);
            color: var(--success);
            border: 3px solid var(--success);
            box-shadow: 0 5px 20px rgba(40, 167, 69, 0.3);
        }

        .match-result i {
            margin-right: 0.8rem;
            font-size: 1.5rem;
        }

        /* ========================================== */
        /* MATCH WAITING */
        /* ========================================== */
        .match-waiting {
            text-align: center;
            color: #888;
            padding: 2rem;
            font-style: italic;
            font-size: 1.1rem;
            background: rgba(255, 255, 255, 0.02);
            border-radius: 15px;
            margin-top: 1.5rem;
        }

        .match-waiting i {
            margin-right: 0.8rem;
            font-size: 1.3rem;
            animation: spin 2s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* ========================================== */
        /* MATCH ACTIONS */
        /* ========================================== */
        .match-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 2px solid rgba(212, 175, 55, 0.2);
        }

        .btn-action {
            padding: 0.8rem 1.5rem;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1rem;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.8rem;
        }

        .btn-reset {
            background: linear-gradient(135deg, var(--warning), #ff9800);
            color: #000;
            box-shadow: 0 5px 15px rgba(255, 193, 7, 0.4);
        }

        .btn-reset:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 8px 25px rgba(255, 193, 7, 0.6);
        }

        /* ========================================== */
        /* GRAND FINAL SECTION */
        /* ========================================== */
        .grand-final-section {
            min-width: 600px;
            flex: 1.2;
        }

        .grand-final-title {
            font-size: 3.5rem;
            font-weight: 900;
            color: var(--primary-gold);
            text-align: center;
            margin-bottom: 3rem;
            text-shadow: 0 0 40px rgba(212, 175, 55, 0.8);
            animation: pulse 2s ease-in-out infinite;
            position: relative;
        }

        .grand-final-title i {
            display: block;
            font-size: 4rem;
            margin-bottom: 1rem;
            animation: bounce 2s ease-in-out infinite;
        }

        /* ========================================== */
        /* EMPTY STATE */
        /* ========================================== */
        .empty-state {
            text-align: center;
            padding: 6rem 3rem;
            background: linear-gradient(135deg, var(--card-bg), var(--sidebar-bg));
            border-radius: 25px;
            border: 4px solid var(--primary-gold);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
        }

        .empty-icon {
            font-size: 8rem;
            color: rgba(212, 175, 55, 0.3);
            margin-bottom: 2.5rem;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }

        .empty-state h3 {
            color: var(--primary-gold);
            font-size: 3rem;
            margin-bottom: 1.5rem;
            font-weight: 900;
        }

        .empty-state p {
            font-size: 1.3rem;
            color: #aaa;
            margin-bottom: 2rem;
        }

        .btn-generate {
            display: inline-block;
            background: linear-gradient(135deg, var(--success), #20c997);
            color: #fff;
            padding: 2rem 4rem;
            border-radius: 50px;
            font-weight: 900;
            font-size: 1.6rem;
            text-decoration: none;
            border: none;
            cursor: pointer;
            box-shadow: 0 15px 50px rgba(40, 167, 69, 0.5);
            margin-top: 2rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-generate::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .btn-generate:hover::before {
            width: 400px;
            height: 400px;
        }

        .btn-generate:hover {
            transform: translateY(-8px) scale(1.05);
            box-shadow: 0 20px 70px rgba(40, 167, 69, 0.7);
            color: #fff;
        }

        .btn-generate i {
            margin-right: 1rem;
            font-size: 1.8rem;
        }

        /* ========================================== */
        /* RESPONSIVE */
        /* ========================================== */
        @media (max-width: 1400px) {
            .bracket-page {
                margin-left: 0;
                padding: 1rem;
            }
        }

        @media (max-width: 768px) {
            .tournament-header h1 {
                font-size: 2rem;
            }

            .pool-section,
            .grand-final-section {
                min-width: 350px;
            }

            .pool-header {
                font-size: 1.5rem;
                padding: 1.5rem;
            }

            .grand-final-title {
                font-size: 2.5rem;
            }

            .match-card {
                padding: 1.5rem;
            }

            .player-avatar {
                width: 50px;
                height: 50px;
            }

            .score-input {
                width: 70px;
                font-size: 1.2rem;
            }

            .winner-buttons {
                flex-direction: column;
            }

            .btn-winner {
                width: 100%;
            }
        }

        /* ========================================== */
        /* ANIMATIONS */
        /* ========================================== */
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .fade-in {
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* ========================================== */
        /* BACK BUTTON */
        /* ========================================== */
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 0.8rem;
            background: rgba(212, 175, 55, 0.2);
            border: 2px solid var(--primary-gold);
            color: var(--primary-gold);
            padding: 1rem 2rem;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1.1rem;
            text-decoration: none;
            transition: all 0.3s ease;
            margin-bottom: 2rem;
        }

        .btn-back:hover {
            background: var(--primary-gold);
            color: #000;
            transform: translateX(-5px);
            box-shadow: 0 5px 20px rgba(212, 175, 55, 0.4);
        }
    </style>
</head>
<body>
    <?php include 'admin_sidebar.php'; ?>
    
    <div class="bracket-page animate__animated animate__fadeIn">
        <!-- Back Button -->
        <a href="admin_tournaments.php" class="btn-back">
            <i class="fas fa-arrow-left"></i>
            Back to Tournaments
        </a>
        
        <!-- Tournament Header -->
        <div class="tournament-header">
            <h1>
                <i class="fas fa-sitemap"></i>
                <?= htmlspecialchars($tournament['nama_tournament']) ?>
            </h1>
            <div class="info-badges">
                <div class="info-badge">
                    <i class="fas fa-users"></i>
                    <?= $tournament['total_participants'] ?> / <?= $tournament['max_peserta'] ?> Participants
                </div>
                <div class="info-badge">
                    <i class="fas fa-calendar"></i>
                    <?= date('d M Y', strtotime($tournament['tanggal_mulai'])) ?>
                </div>
                <div class="info-badge">
                    <i class="fas fa-trophy"></i>
                    <?= htmlspecialchars($tournament['hadiah'] ?? 'TBA') ?>
                </div>
                <div class="status-badge <?= $tournament['status'] ?>">
                    <i class="fas fa-circle"></i>
                    <?= strtoupper($tournament['status']) ?>
                </div>
            </div>
        </div>
        
        <!-- Alerts -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success animate__animated animate__bounceIn">
                <i class="fas fa-check-circle"></i>
                <?= htmlspecialchars($_SESSION['success_message']) ?>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger animate__animated animate__shakeX">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($_SESSION['error_message']) ?>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
        
        <?php if ($has_bracket): ?>
            <!-- Progress Section -->
            <div class="progress-section animate__animated animate__fadeInUp">
                <h3>
                    <i class="fas fa-chart-line"></i>
                    Tournament Progress: <?= $completed_matches ?> / <?= $total_matches ?> Matches Completed
                </h3>
                <div class="progress">
                    <div class="progress-bar" role="progressbar" style="width: <?= $bracket_progress ?>%">
                        <?= $bracket_progress ?>%
                    </div>
                </div>
            </div>
            
            <!-- Champion Banner (if completed) -->
            <?php if ($tournament['status'] === 'completed' && $tournament['champion_name']): ?>
                <div class="champion-banner animate__animated animate__zoomIn">
                    <h2>
                        <i class="fas fa-crown"></i>
                        TOURNAMENT CHAMPION
                    </h2>
                    <div class="champion-info">
                        <img src="images/<?= $tournament['champion_photo'] ?? 'avataruser.jpg' ?>" alt="Champion" class="champion-avatar">
                        <div class="champion-name"><?= htmlspecialchars($tournament['champion_name']) ?></div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Bracket Container -->
            <div class="bracket-container">
                <!-- POOL A -->
                <div class="pool-section animate__animated animate__fadeInLeft">
                    <div class="pool-header">
                        <i class="fas fa-layer-group"></i>
                        POOL A
                    </div>
                    
                    <!-- Pool A Stats -->
                    <?php if (count($participants['A']) > 0): ?>
                        <div class="pool-stats">
                            <h4><i class="fas fa-users"></i> Participants</h4>
                            <?php foreach ($participants['A'] as $p): ?>
                                <div class="participant-item">
                                    <img src="images/<?= $p['foto_profil'] ?? 'avataruser.jpg' ?>" alt="Player" class="participant-avatar-small">
                                    <div class="participant-name-small"><?= htmlspecialchars($p['nama']) ?></div>
                                    <div class="participant-seed">Seed <?= $p['seed_position'] ?></div>
                                    <div class="participant-record"><?= $p['wins'] ?>W - <?= $p['losses'] ?>L</div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Semifinals Pool A -->
                    <?php if (count($matches['semifinal']['A']) > 0): ?>
                        <div class="round-title">
                            <i class="fas fa-chess-knight"></i>
                            SEMIFINALS
                        </div>
                        <?php foreach ($matches['semifinal']['A'] as $match): ?>
                            <?php include 'match_card_template.php'; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    
                    <!-- Final Pool A -->
                    <?php if (count($matches['final_pool']['A']) > 0): ?>
                        <div class="round-title">
                            <i class="fas fa-crown"></i>
                            POOL A FINAL
                        </div>
                        <?php foreach ($matches['final_pool']['A'] as $match): ?>
                            <?php include 'match_card_template.php'; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <!-- GRAND FINAL -->
                <div class="grand-final-section animate__animated animate__zoomIn">
                    <div class="grand-final-title">
                        <i class="fas fa-trophy"></i>
                        GRAND FINAL
                    </div>
                    
                    <?php if (count($matches['grand_final']) > 0): ?>
                        <?php $match = $matches['grand_final'][0]; ?>
                        <?php include 'match_card_template.php'; ?>
                    <?php else: ?>
                        <div class="match-waiting">
                            <i class="fas fa-hourglass-half"></i>
                            Awaiting Pool Champions...
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- POOL B -->
                <div class="pool-section animate__animated animate__fadeInRight">
                    <div class="pool-header">
                        <i class="fas fa-layer-group"></i>
                        POOL B
                    </div>
                    
                    <!-- Pool B Stats -->
                    <?php if (count($participants['B']) > 0): ?>
                        <div class="pool-stats">
                            <h4><i class="fas fa-users"></i> Participants</h4>
                            <?php foreach ($participants['B'] as $p): ?>
                                <div class="participant-item">
                                    <img src="images/<?= $p['foto_profil'] ?? 'avataruser.jpg' ?>" alt="Player" class="participant-avatar-small">
                                    <div class="participant-name-small"><?= htmlspecialchars($p['nama']) ?></div>
                                    <div class="participant-seed">Seed <?= $p['seed_position'] ?></div>
                                    <div class="participant-record"><?= $p['wins'] ?>W - <?= $p['losses'] ?>L</div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Semifinals Pool B -->
                    <?php if (count($matches['semifinal']['B']) > 0): ?>
                        <div class="round-title">
                            <i class="fas fa-chess-knight"></i>
                            SEMIFINALS
                        </div>
                        <?php foreach ($matches['semifinal']['B'] as $match): ?>
                            <?php include 'match_card_template.php'; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    
                    <!-- Final Pool B -->
                    <?php if (count($matches['final_pool']['B']) > 0): ?>
                        <div class="round-title">
                            <i class="fas fa-crown"></i>
                            POOL B FINAL
                        </div>
                        <?php foreach ($matches['final_pool']['B'] as $match): ?>
                            <?php include 'match_card_template.php'; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <!-- Empty State -->
            <div class="empty-state animate__animated animate__zoomIn">
                <i class="fas fa-hourglass-half empty-icon"></i>
                <h3>Bracket Not Generated Yet</h3>
                
                <?php if ($tournament['total_participants'] >= $tournament['max_peserta']): ?>
                    <p style="font-size: 1.6rem; font-weight: 600; color: var(--success);">
                        ✅ Tournament is ready with <?= $tournament['total_participants'] ?> / <?= $tournament['max_peserta'] ?> participants!
                    </p>
                    <p style="font-size: 1.3rem; color: #aaa;">
                        Click the button below to generate the tournament bracket and start the competition.
                    </p>
                    
                    <a href="admin_tournament_bracket.php?id=<?= $tournament_id ?>&action=generate_bracket" 
                       onclick="return confirm('⚠️ GENERATE TOURNAMENT BRACKET?\n\nThis will create matches for all participants and start the tournament. This action CANNOT be undone!\n\nAre you sure you want to continue?')"
                       class="btn-generate">
                        <i class="fas fa-sitemap"></i>
                        GENERATE BRACKET NOW
                    </a>
                <?php else: ?>
                    <p style="font-size: 1.4rem; color: var(--danger); font-weight: 700;">
                        ⚠️ Waiting for more participants
                    </p>
                    <p style="font-size: 1.6rem; font-weight: 700;">
                        Current: <?= $tournament['total_participants'] ?> / <?= $tournament['max_peserta'] ?> players
                    </p>
                    <p style="font-size: 1.2rem; color: #aaa;">
                        Need <?= $tournament['max_peserta'] - $tournament['total_participants'] ?> more participant(s) to generate bracket
                    </p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Console styling
        console.log('%c🏆 LEGACY BILLIARD - TOURNAMENT BRACKET 🏆', 'background: linear-gradient(135deg, #D4AF37, #F5D068); color: #000; font-size: 24px; font-weight: bold; padding: 20px; border-radius: 10px;');
        console.log('%cULTIMATE EDITION v2.0', 'background: #28a745; color: #fff; font-size: 16px; font-weight: bold; padding: 10px; border-radius: 5px;');
        console.log('%cMatches Loaded: <?= $total_matches ?> | Completed: <?= $completed_matches ?> | Progress: <?= $bracket_progress ?>%', 'color: #D4AF37; font-size: 14px; font-weight: bold; padding: 5px;');
        
        // Auto-dismiss alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s ease';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);
        
        // Smooth scroll for bracket container
        const bracketContainer = document.querySelector('.bracket-container');
        if (bracketContainer) {
            // Auto-scroll to grand final if tournament is near completion
            const progress = <?= $bracket_progress ?>;
            if (progress > 70) {
                setTimeout(() => {
                    const grandFinal = document.querySelector('.grand-final-section');
                    if (grandFinal) {
                        grandFinal.scrollIntoView({ behavior: 'smooth', block: 'center', inline: 'center' });
                    }
                }, 1000);
            }
        }
        
        // Confirm before resetting match
        document.querySelectorAll('.btn-reset').forEach(btn => {
            btn.addEventListener('click', function(e) {
                if (!confirm('⚠️ RESET MATCH?\n\nThis will clear the match result and remove the winner from the next round.\n\nAre you sure?')) {
                    e.preventDefault();
                }
            });
        });
        
        // Form validation for winner selection
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const score1 = parseInt(form.querySelector('input[name="player1_score"]')?.value || 0);
                const score2 = parseInt(form.querySelector('input[name="player2_score"]')?.value || 0);
                
                if (score1 === score2) {
                    e.preventDefault();
                    alert('⚠️ INVALID SCORES!\n\nScores cannot be equal. There must be a clear winner.');
                    return false;
                }
                
                const winnerId = e.submitter?.value;
                const player1Id = form.querySelector('input[name="player1_score"]')?.closest('.player-row')?.dataset?.playerId;
                const player2Id = form.querySelector('input[name="player2_score"]')?.closest('.player-row')?.dataset?.playerId;
                
                if (score1 > score2 && winnerId != player1Id && player1Id) {
                    e.preventDefault();
                    alert('⚠️ SCORE MISMATCH!\n\nPlayer 1 has higher score but Player 2 is selected as winner.');
                    return false;
                }
                
                if (score2 > score1 && winnerId != player2Id && player2Id) {
                    e.preventDefault();
                    alert('⚠️ SCORE MISMATCH!\n\nPlayer 2 has higher score but Player 1 is selected as winner.');
                    return false;
                }
            });
        });
        
        // Add loading animation to generate button
        const generateBtn = document.querySelector('.btn-generate');
        if (generateBtn) {
            generateBtn.addEventListener('click', function() {
                if (this.href && this.href.includes('action=generate_bracket')) {
                    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> GENERATING BRACKET...';
                    this.style.pointerEvents = 'none';
                }
            });
        }
        
        // Highlight active match
        document.querySelectorAll('.match-card').forEach(card => {
            if (card.querySelector('.winner-controls')) {
                card.classList.add('in-progress');
            }
        });
    </script>
</body>
</html>

<?php
// ============================================
// MATCH CARD TEMPLATE (INLINE PHP)
// ============================================
// This template is included for each match
// Variable $match must be available in scope
?>
<?php if (false): // This is template code, included above ?>
<div class="match-card <?= $match['status'] === 'completed' ? 'completed' : '' ?> fade-in">
    <div class="match-label">
        <i class="fas fa-gamepad"></i>
        MATCH <?= $match['match_number'] ?>
    </div>
    
    <?php if ($match['created_at']): ?>
        <div class="match-time">
            <i class="fas fa-clock"></i>
            Created: <?= date('d M Y, H:i', strtotime($match['created_at'])) ?>
            <?php if ($match['completed_at']): ?>
                | Completed: <?= date('d M Y, H:i', strtotime($match['completed_at'])) ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" onsubmit="return confirm('Set match winner? This action will be recorded.');">
        <input type="hidden" name="action" value="set_winner">
        <input type="hidden" name="match_id" value="<?= $match['id'] ?>">
        
        <!-- Player 1 -->
        <div class="player-row <?= $match['winner_id'] == $match['player1_id'] ? 'winner' : ($match['status'] === 'completed' ? 'loser' : '') ?>" data-player-id="<?= $match['player1_id'] ?>">
            <div class="player-info">
                <?php if ($match['player1_id']): ?>
                    <img src="images/<?= $match['player1_photo'] ?? 'avataruser.jpg' ?>" alt="Player 1" class="player-avatar">
                    <div class="player-details">
                        <div class="player-name"><?= htmlspecialchars($match['player1_name']) ?></div>
                        <div class="player-email"><?= htmlspecialchars($match['player1_email']) ?></div>
                    </div>
                <?php else: ?>
                    <div class="player-avatar-placeholder">
                        <i class="fas fa-user-slash"></i>
                    </div>
                    <div class="player-details">
                        <div class="player-name" style="color: #666;">TBD</div>
                        <div class="player-email">To Be Determined</div>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if ($match['status'] === 'pending' && $match['player1_id'] && $match['player2_id']): ?>
                <input type="number" name="player1_score" class="score-input" value="0" min="0" max="99" required>
            <?php else: ?>
                <div class="score-display"><?= $match['player1_score'] ?? '-' ?></div>
            <?php endif; ?>
        </div>
        
        <!-- VS Text -->
        <div class="vs-text">VS</div>
        
        <!-- Player 2 -->
        <div class="player-row <?= $match['winner_id'] == $match['player2_id'] ? 'winner' : ($match['status'] === 'completed' ? 'loser' : '') ?>" data-player-id="<?= $match['player2_id'] ?>">
            <div class="player-info">
                <?php if ($match['player2_id']): ?>
                    <img src="images/<?= $match['player2_photo'] ?? 'avataruser.jpg' ?>" alt="Player 2" class="player-avatar">
                    <div class="player-details">
                        <div class="player-name"><?= htmlspecialchars($match['player2_name']) ?></div>
                        <div class="player-email"><?= htmlspecialchars($match['player2_email']) ?></div>
                    </div>
                <?php else: ?>
                    <div class="player-avatar-placeholder">
                        <i class="fas fa-user-slash"></i>
                    </div>
                    <div class="player-details">
                        <div class="player-name" style="color: #666;">TBD</div>
                        <div class="player-email">To Be Determined</div>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if ($match['status'] === 'pending' && $match['player1_id'] && $match['player2_id']): ?>
                <input type="number" name="player2_score" class="score-input" value="0" min="0" max="99" required>
            <?php else: ?>
                <div class="score-display"><?= $match['player2_score'] ?? '-' ?></div>
            <?php endif; ?>
        </div>
        
        <!-- Match Controls -->
        <?php if ($match['status'] === 'pending' && $match['player1_id'] && $match['player2_id']): ?>
            <div class="winner-controls">
                <label>
                    <i class="fas fa-crown"></i>
                    Select Winner
                </label>
                <div class="winner-buttons">
                    <button type="submit" name="winner_id" value="<?= $match['player1_id'] ?>" class="btn-winner">
                        <i class="fas fa-trophy"></i>
                        <?= htmlspecialchars($match['player1_name']) ?>
                    </button>
                    <button type="submit" name="winner_id" value="<?= $match['player2_id'] ?>" class="btn-winner">
                        <i class="fas fa-trophy"></i>
                        <?= htmlspecialchars($match['player2_name']) ?>
                    </button>
                </div>
            </div>
        <?php elseif ($match['status'] === 'completed'): ?>
            <div class="match-result">
                <i class="fas fa-check-circle"></i>
                Winner: <strong><?= htmlspecialchars($match['winner_name']) ?></strong>
            </div>
            <div class="match-actions">
                <a href="admin_tournament_bracket.php?id=<?= $tournament_id ?>&action=reset_match&match_id=<?= $match['id'] ?>" class="btn-action btn-reset">
                    <i class="fas fa-redo"></i>
                    Reset Match
                </a>
            </div>
        <?php else: ?>
            <div class="match-waiting">
                <i class="fas fa-clock"></i>
                Waiting for Players to Advance...
            </div>
        <?php endif; ?>
    </form>
</div>
<?php endif; ?>
