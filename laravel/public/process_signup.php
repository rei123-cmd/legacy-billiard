<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$nama = clean_input($_POST['nama']);
$email = clean_input($_POST['email']);
$telepon = clean_input($_POST['telepon']);
$password = $_POST['password'];

if (empty($nama) || empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Semua field harus diisi']);
    exit;
}

// Cek apakah email sudah terdaftar
$check_query = "SELECT id FROM users WHERE email = '$email'";
$check_result = $conn->query($check_query);

if ($check_result && $check_result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Email sudah terdaftar']);
    exit;
}

// Hash password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Insert user baru
$query = "INSERT INTO users (nama, email, telepon, password) VALUES ('$nama', '$email', '$telepon', '$hashed_password')";

if ($conn->query($query)) {
    echo json_encode([
        'success' => true,
        'message' => 'Pendaftaran berhasil'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan saat pendaftaran'
    ]);
}

$conn->close();
?>