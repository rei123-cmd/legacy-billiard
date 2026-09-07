<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Allow AJAX from any origin

require_once 'config.php';

$tournament_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$last_update = isset($_GET['last_update']) ? intval($_GET['last_update']) : 0;

// Validation
if ($tournament_id <= 0) {
    echo json_encode([
        'error' => 'Invalid tournament ID', 
        'has_updates' => false,
        'debug' => 'Tournament ID is required'
    ]);
    exit;
}

try {
    // Get latest match update time
    $query = "
        SELECT 
            MAX(UNIX_TIMESTAMP(COALESCE(completed_at, created_at))) as latest_update,
            COUNT(*) as total_matches,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_matches
        FROM tournament_matches
        WHERE tournament_id = ?
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $tournament_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $latest_update = $result['latest_update'] ?? 0;
    
    // Check if there are updates
    $has_updates = ($latest_update * 1000) > $last_update;

    // Get tournament status
    $status_query = "SELECT status, nama_tournament FROM tournaments WHERE id = ?";
    $status_stmt = $conn->prepare($status_query);
    $status_stmt->bind_param("i", $tournament_id);
    $status_stmt->execute();
    $status_result = $status_stmt->get_result()->fetch_assoc();
    $status_stmt->close();

    // Response
    echo json_encode([
        'has_updates' => $has_updates,
        'latest_update' => $latest_update * 1000,
        'status' => $status_result['status'] ?? 'unknown',
        'tournament_name' => $status_result['nama_tournament'] ?? '',
        'total_matches' => $result['total_matches'] ?? 0,
        'completed_matches' => $result['completed_matches'] ?? 0,
        'timestamp' => time(),
        'success' => true
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'error' => 'Database error',
        'has_updates' => false,
        'message' => $e->getMessage(),
        'success' => false
    ]);
}
?>
