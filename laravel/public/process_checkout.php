<?php

require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: cart.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$no_telepon = isset($_POST['no_telepon']) ? clean_input($_POST['no_telepon']) : '';
$payment_method = isset($_POST['payment_method']) ? clean_input($_POST['payment_method']) : '';

// Validate
if (empty($no_telepon) || empty($payment_method)) {
    $_SESSION['error'] = 'Mohon lengkapi semua data!';
    header('Location: checkout.php');
    exit;
}

// Get user info
$user_query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get cart items
$cart_query = "SELECT * FROM cart WHERE user_id = ?";
$stmt = $conn->prepare($cart_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart_result = $stmt->get_result();

if ($cart_result->num_rows === 0) {
    $_SESSION['error'] = 'Keranjang kosong!';
    header('Location: cart.php');
    exit;
}


$conn->begin_transaction();

try {
    $booking_ids = [];
    
    while ($item = $cart_result->fetch_assoc()) {
       
        $waktu_mulai = new DateTime($item['tanggal_booking'] . ' ' . $item['waktu_mulai']);
        $waktu_selesai = clone $waktu_mulai;
        $waktu_selesai->modify('+' . $item['durasi_jam'] . ' hours');
        
        $waktu_mulai_time = $waktu_mulai->format('H:i:s');
        $waktu_selesai_time = $waktu_selesai->format('H:i:s');
        
      
        $overlap_query = "
            SELECT id, waktu_mulai, waktu_selesai 
            FROM booked_slots 
            WHERE meja_id = ? 
            AND tanggal_booking = ? 
            AND (
                (waktu_mulai < ? AND waktu_selesai > ?) OR
                (waktu_mulai < ? AND waktu_selesai > ?) OR
                (waktu_mulai >= ? AND waktu_selesai <= ?)
            )
        ";
        
        $check_stmt = $conn->prepare($overlap_query);
        $check_stmt->bind_param(
            "isssssss",
            $item['meja_id'],
            $item['tanggal_booking'],
            $waktu_selesai_time, $waktu_mulai_time,
            $waktu_selesai_time, $waktu_mulai_time,
            $waktu_mulai_time, $waktu_selesai_time
        );
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $conflicting = $check_result->fetch_assoc();
            throw new Exception(
                '⚠️ MAAF! Meja ' . $item['nama_meja'] . 
                ' untuk tanggal ' . date('d/m/Y', strtotime($item['tanggal_booking'])) . 
                ' sudah dibooking di jam ' . substr($conflicting['waktu_mulai'], 0, 5) . 
                ' - ' . substr($conflicting['waktu_selesai'], 0, 5) . 
                ' oleh orang lain. Silakan pilih waktu lain!'
            );
        }
        $check_stmt->close();
       
        $total_harga = $item['harga'] - $item['diskon'];
        
        $booking_query = "INSERT INTO booking 
            (user_id, meja_id, nama_pemesan, telepon_pemesan, tanggal_booking, waktu_mulai, durasi_jam, paket_type, total_harga, voucher_code, diskon, status, payment_status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'confirmed', 'unpaid')";
        
        $booking_stmt = $conn->prepare($booking_query);
        $booking_stmt->bind_param(
            "iissssisdsd", 
            $user_id, 
            $item['meja_id'], 
            $user['nama'], 
            $no_telepon, 
            $item['tanggal_booking'], 
            $waktu_mulai_time, 
            $item['durasi_jam'], 
            $item['paket_type'], 
            $total_harga,
            $item['voucher_code'],
            $item['diskon']
        );
        
        if (!$booking_stmt->execute()) {
            throw new Exception('Gagal membuat booking: ' . $booking_stmt->error);
        }
        
        $booking_id = $booking_stmt->insert_id;
        $booking_ids[] = $booking_id;
        $booking_stmt->close();
        
    
        $slot_query = "INSERT INTO booked_slots 
            (meja_id, tanggal_booking, waktu_mulai, waktu_selesai, booking_id) 
            VALUES (?, ?, ?, ?, ?)";
        
        $slot_stmt = $conn->prepare($slot_query);
        $slot_stmt->bind_param(
            "isssi", 
            $item['meja_id'], 
            $item['tanggal_booking'], 
            $waktu_mulai_time, 
            $waktu_selesai_time, 
            $booking_id
        );
        
        if (!$slot_stmt->execute()) {
            throw new Exception('Gagal memesan slot: ' . $slot_stmt->error);
        }
        $slot_stmt->close();
    }
  
    $clear_cart = "DELETE FROM cart WHERE user_id = ?";
    $clear_stmt = $conn->prepare($clear_cart);
    $clear_stmt->bind_param("i", $user_id);
    $clear_stmt->execute();
    $clear_stmt->close();
    

    $conn->commit();
    
    // Set success session
    $_SESSION['booking_success'] = true;
    $_SESSION['booking_ids'] = $booking_ids;
    $_SESSION['payment_method'] = $payment_method;
    
    header('Location: booking_success.php?booking_ids=' . implode(',', $booking_ids));
    exit;
    
} catch (Exception $e) {
 
    $conn->rollback();
    $_SESSION['error'] = $e->getMessage();
    header('Location: checkout.php');
    exit;
}

$stmt->close();
$conn->close();
?>