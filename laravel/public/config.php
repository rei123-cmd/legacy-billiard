<?php

$host = getenv('DB_HOST') ?: 'localhost';
$dbname = getenv('DB_DATABASE') ?: 'legacy_billiard';
$username = getenv('DB_USERNAME') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';
$port = getenv('DB_PORT') ?: 3306;

// Connect to database dengan port
$conn = new mysqli($host, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8mb4");

// Fungsi untuk membersihkan input
function clean_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $conn->real_escape_string($data);
}

// Fungsi untuk cek login
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Fungsi untuk get user data
function get_user_data() {
    global $conn;
    if (!is_logged_in()) {
        return null;
    }
    
    $user_id = $_SESSION['user_id'];
    $query = "SELECT * FROM users WHERE id = $user_id";
    $result = $conn->query($query);
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return null;
}

// Start session HANYA kalau belum
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>