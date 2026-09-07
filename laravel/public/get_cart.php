<?php
require_once 'config.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['items' => []]);
    exit;
}

$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM cart WHERE user_id = $user_id ORDER BY created_at DESC";
$result = $conn->query($query);

$items = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
}

echo json_encode(['items' => $items]);
$conn->close();
?>