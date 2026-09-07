<?php
// ============================================
// LOGIN PROCESS WITH ROLE-BASED REDIRECT
// ============================================

require_once 'config.php';
header('Content-Type: application/json');

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false, 
        'message' => 'Invalid request method. Only POST requests are allowed.'
    ]);
    exit;
}

// Get and sanitize input
$email = isset($_POST['email']) ? clean_input($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

// Validate input
if (empty($email) || empty($password)) {
    echo json_encode([
        'success' => false, 
        'message' => 'Email dan password harus diisi!'
    ]);
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false, 
        'message' => 'Format email tidak valid!'
    ]);
    exit;
}

// Prepare SQL query to prevent SQL injection
$stmt = $conn->prepare("SELECT id, nama, email, password, role, telepon, foto_profil FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

// Check if user exists
if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();
    
    // Verify password
    if (password_verify($password, $user['password'])) {
        // Regenerate session ID for security
        session_regenerate_id(true);
        
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['nama'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['user_phone'] = $user['telepon'];
        $_SESSION['user_photo'] = $user['foto_profil'];
        $_SESSION['login_time'] = time();
        
        // Update last login time
        $update_login = $conn->prepare("UPDATE users SET updated_at = NOW() WHERE id = ?");
        $update_login->bind_param("i", $user['id']);
        $update_login->execute();
        
        // Redirect based on role
        if ($user['role'] === 'admin') {
            // Admin redirect
            echo json_encode([
                'success' => true,
                'message' => 'Login berhasil! Selamat datang Administrator ' . htmlspecialchars($user['nama']) . '!',
                'redirect' => 'admin_index.php',
                'role' => 'admin',
                'user' => [
                    'id' => $user['id'],
                    'name' => $user['nama'],
                    'email' => $user['email']
                ]
            ]);
        } else {
            // Regular user redirect
            echo json_encode([
                'success' => true,
                'message' => 'Login berhasil! Selamat datang ' . htmlspecialchars($user['nama']) . '!',
                'redirect' => 'index.php',
                'role' => 'user',
                'user' => [
                    'id' => $user['id'],
                    'name' => $user['nama'],
                    'email' => $user['email']
                ]
            ]);
        }
    } else {
        // Password incorrect
        echo json_encode([
            'success' => false, 
            'message' => 'Password yang Anda masukkan salah!'
        ]);
    }
} else {
    // Email not found
    echo json_encode([
        'success' => false, 
        'message' => 'Email tidak terdaftar dalam sistem kami!'
    ]);
}

// Close statement and connection
$stmt->close();
$conn->close();
?>
