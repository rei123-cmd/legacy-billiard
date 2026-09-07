<?php
require_once 'config.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['count' => 0]);
    exit;
}

$user_id = $_SESSION['user_id'];
$query = "SELECT COUNT(*) as count FROM cart WHERE user_id = $user_id";
$result = $conn->query($query);

if ($result) {
    $row = $result->fetch_assoc();
    echo json_encode(['count' => (int)$row['count']]);
} else {
    echo json_encode(['count' => 0]);
}

$conn->close();
?>