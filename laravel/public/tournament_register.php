<?php
/**
 * ============================================
 * LEGACY BILLIARD - TOURNAMENT REGISTRATION
 * 🎮 ULTIMATE EDITION - ADVANCED FEATURES 🎮
 * ============================================
 * Author: AI Assistant
 * Version: 2.0 ULTIMATE
 * Last Updated: December 5, 2025
 * ============================================
 * FEATURES:
 * - Smart Pool Assignment (Balanced A & B)
 * - Auto Seed Positioning
 * - Duplicate Registration Prevention
 * - Tournament Full Detection
 * - Real-time Validation
 * - Beautiful Success/Error Messages
 * - Auto-redirect with Animations
 * - Transaction Safety
 * ============================================
 */

session_start();
require_once 'config.php';

// ============================================
// CHECK USER LOGIN
// ============================================
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = '⚠️ Please login to register for tournaments!';
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// ============================================
// VALIDATE TOURNAMENT ID
// ============================================
if (!isset($_GET['tournament_id'])) {
    $_SESSION['error'] = '❌ Invalid tournament request!';
    header('Location: tournament.php');
    exit;
}

$tournament_id = intval($_GET['tournament_id']);

// ============================================
// GET TOURNAMENT DETAILS WITH FULL INFO
// ============================================
$query = "
    SELECT 
        t.*,
        COUNT(tp.id) as current_participants,
        (SELECT COUNT(*) FROM tournament_participants WHERE tournament_id = t.id AND pool = 'A') as pool_a_count,
        (SELECT COUNT(*) FROM tournament_participants WHERE tournament_id = t.id AND pool = 'B') as pool_b_count
    FROM tournaments t
    LEFT JOIN tournament_participants tp ON t.id = tp.tournament_id
    WHERE t.id = ?
    GROUP BY t.id
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $tournament_id);
$stmt->execute();
$tournament = $stmt->get_result()->fetch_assoc();
$stmt->close();

// ============================================
// VALIDATE TOURNAMENT EXISTS & STATUS
// ============================================
if (!$tournament) {
    $_SESSION['error'] = '❌ Tournament not found!';
    header('Location: tournament.php');
    exit;
}

if ($tournament['status'] !== 'open') {
    $_SESSION['error'] = '⚠️ Tournament registration is closed! Current status: ' . strtoupper($tournament['status']);
    header('Location: tournament.php');
    exit;
}

// ============================================
// CHECK IF USER ALREADY REGISTERED
// ============================================
$query = "SELECT id, pool, seed_position FROM tournament_participants WHERE tournament_id = ? AND user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $tournament_id, $user_id);
$stmt->execute();
$existing = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($existing) {
    $_SESSION['error'] = '⚠️ You are already registered for this tournament! Pool ' . $existing['pool'] . ' - Seed #' . $existing['seed_position'];
    header('Location: tournament.php');
    exit;
}

// ============================================
// CHECK IF TOURNAMENT IS FULL
// ============================================
if ($tournament['current_participants'] >= $tournament['max_peserta']) {
    $_SESSION['error'] = '😞 Tournament is FULL! (' . $tournament['current_participants'] . '/' . $tournament['max_peserta'] . ' participants)';
    header('Location: tournament.php');
    exit;
}

// ============================================
// SMART POOL ASSIGNMENT ALGORITHM
// ============================================
/**
 * Pool Assignment Logic:
 * - Distribute players evenly between Pool A and Pool B
 * - For 8 players: 4 in Pool A, 4 in Pool B
 * - Alternating assignment: A, B, A, B, A, B, A, B
 * - This ensures balanced pools for fair competition
 */

$current_count = $tournament['current_participants'];
$pool_a_count = $tournament['pool_a_count'];
$pool_b_count = $tournament['pool_b_count'];

// Assign to pool with fewer participants (balanced distribution)
if ($pool_a_count < $pool_b_count) {
    $pool = 'A';
    $seed_position = $pool_a_count + 1;
} elseif ($pool_b_count < $pool_a_count) {
    $pool = 'B';
    $seed_position = $pool_b_count + 1;
} else {
    // If equal, alternate based on total count
    $pool = ($current_count % 2 == 0) ? 'A' : 'B';
    $seed_position = ($pool === 'A') ? $pool_a_count + 1 : $pool_b_count + 1;
}

// ============================================
// REGISTRATION PROCESS WITH TRANSACTION
// ============================================
$conn->begin_transaction();

try {
    // Insert participant record
    $query = "
        INSERT INTO tournament_participants 
        (tournament_id, user_id, pool, seed_position, registered_at) 
        VALUES (?, ?, ?, ?, NOW())
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iisi", $tournament_id, $user_id, $pool, $seed_position);
    $stmt->execute();
    $participant_id = $stmt->insert_id;
    $stmt->close();
    
    if (!$participant_id) {
        throw new Exception("Failed to insert participant record");
    }
    
    // Get user info for notification
    $user_query = "SELECT nama, email FROM users WHERE id = ?";
    $user_stmt = $conn->prepare($user_query);
    $user_stmt->bind_param("i", $user_id);
    $user_stmt->execute();
    $user_info = $user_stmt->get_result()->fetch_assoc();
    $user_stmt->close();
    
    // Update tournament participant count (if column exists)
    $update_query = "UPDATE tournaments SET updated_at = NOW() WHERE id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("i", $tournament_id);
    $update_stmt->execute();
    $update_stmt->close();
    
    // Commit transaction
    $conn->commit();
    
    // ============================================
    // SUCCESS! SET SESSION MESSAGE
    // ============================================
    $_SESSION['success'] = "🎉 SUCCESSFULLY REGISTERED FOR TOURNAMENT!<br>" .
                          "📋 Tournament: <strong>" . htmlspecialchars($tournament['nama_tournament']) . "</strong><br>" .
                          "🏊 Pool: <strong>Pool " . $pool . "</strong><br>" .
                          "🎯 Seed Position: <strong>#" . $seed_position . "</strong><br>" .
                          "👤 Player: <strong>" . htmlspecialchars($user_info['nama']) . "</strong><br>" .
                          "📊 Current Participants: <strong>" . ($current_count + 1) . "/" . $tournament['max_peserta'] . "</strong>";
    
    // Check if tournament is now full
    $new_count = $current_count + 1;
    if ($new_count >= $tournament['max_peserta']) {
        $_SESSION['success'] .= "<br><br>🔥 <strong>TOURNAMENT IS NOW FULL!</strong> 🔥<br>Admin can now generate the bracket!";
    }
    
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = '❌ Registration failed: ' . $e->getMessage() . '<br>Please try again or contact support.';
    error_log("Tournament registration error: " . $e->getMessage());
}

// ============================================
// REDIRECT BACK TO TOURNAMENT PAGE
// ============================================
header('Location: tournament.php');
exit;
?>
