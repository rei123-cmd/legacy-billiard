<?php
/**
 * ============================================
 * LEGACY BILLIARD - TOURNAMENT UNREGISTRATION
 * ⚠️ ULTIMATE EDITION - CANCEL REGISTRATION ⚠️
 * ============================================
 */

session_start();
require_once 'config.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = '⚠️ Please login first!';
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$tournament_id = isset($_GET['tournament_id']) ? intval($_GET['tournament_id']) : 0;

if ($tournament_id <= 0) {
    $_SESSION['error'] = '❌ Invalid tournament!';
    header('Location: tournament.php');
    exit;
}

// Check if tournament has started
$status_check = $conn->prepare("SELECT status FROM tournaments WHERE id = ?");
$status_check->bind_param("i", $tournament_id);
$status_check->execute();
$tournament_status = $status_check->get_result()->fetch_assoc();
$status_check->close();

if ($tournament_status['status'] !== 'open') {
    $_SESSION['error'] = '⚠️ Cannot unregister! Tournament has already started or is closed.';
    header('Location: tournament.php');
    exit;
}

// Check if user is registered
$check_query = "SELECT id, pool, seed_position FROM tournament_participants WHERE tournament_id = ? AND user_id = ?";
$check_stmt = $conn->prepare($check_query);
$check_stmt->bind_param("ii", $tournament_id, $user_id);
$check_stmt->execute();
$registration = $check_stmt->get_result()->fetch_assoc();
$check_stmt->close();

if (!$registration) {
    $_SESSION['error'] = '⚠️ You are not registered for this tournament!';
    header('Location: tournament.php');
    exit;
}

// Begin transaction
$conn->begin_transaction();

try {
    // Delete participant record
    $delete_query = "DELETE FROM tournament_participants WHERE tournament_id = ? AND user_id = ?";
    $delete_stmt = $conn->prepare($delete_query);
    $delete_stmt->bind_param("ii", $tournament_id, $user_id);
    $delete_stmt->execute();
    $delete_stmt->close();
    
    // Rebalance seed positions for remaining participants in the same pool
    $rebalance_query = "
        UPDATE tournament_participants 
        SET seed_position = seed_position - 1 
        WHERE tournament_id = ? 
        AND pool = ? 
        AND seed_position > ?
    ";
    $rebalance_stmt = $conn->prepare($rebalance_query);
    $rebalance_stmt->bind_param("isi", $tournament_id, $registration['pool'], $registration['seed_position']);
    $rebalance_stmt->execute();
    $rebalance_stmt->close();
    
    $conn->commit();
    
    $_SESSION['success'] = '✅ Successfully unregistered from tournament! You can register again anytime before it starts.';
    
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = '❌ Failed to unregister: ' . $e->getMessage();
}

header('Location: tournament.php');
exit;
?>
