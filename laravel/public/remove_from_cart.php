<?php
require_once 'config.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$cart_id = (int)$_POST['cart_id'];

$query = "DELETE FROM cart WHERE id = $cart_id AND user_id = $user_id";

if ($conn->query($query)) {
    echo json_encode(['success' => true, 'message' => 'Item berhasil dihapus']);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menghapus item']);
}

$conn->close();
?>