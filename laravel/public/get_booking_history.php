<?php
require_once 'config.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'bookings' => []]);
    exit;
}

$user_id = $_SESSION['user_id'];
$query = "SELECT b.*, m.nama_meja 
          FROM booking b 
          LEFT JOIN meja m ON b.meja_id = m.id 
          WHERE b.user_id = $user_id 
          ORDER BY b.created_at DESC 
          LIMIT 20";
$result = $conn->query($query);

$bookings = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $bookings[] = $row;
    }
}

echo json_encode(['success' => true, 'bookings' => $bookings]);
$conn->close();
?>