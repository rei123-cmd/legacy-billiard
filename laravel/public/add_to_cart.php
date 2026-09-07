<?php

require_once 'config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Silakan login terlebih dahulu']);
    exit;
}

$user_id = $_SESSION['user_id'];
$meja_id = isset($_POST['meja_id']) ? (int)$_POST['meja_id'] : 0;
$nama_meja = isset($_POST['nama_meja']) ? clean_input($_POST['nama_meja']) : '';
$tanggal_booking = isset($_POST['tanggal_booking']) ? clean_input($_POST['tanggal_booking']) : '';
$waktu_mulai = isset($_POST['waktu_mulai']) ? clean_input($_POST['waktu_mulai']) : '';
$durasi_jam = isset($_POST['durasi_jam']) ? (int)$_POST['durasi_jam'] : 0;
$paket_type = isset($_POST['paket_type']) ? clean_input($_POST['paket_type']) : '';
$voucher_code = isset($_POST['voucher_code']) ? clean_input($_POST['voucher_code']) : '';

// Validate required fields
if (empty($meja_id) || empty($tanggal_booking) || empty($waktu_mulai) || empty($paket_type) || empty($durasi_jam)) {
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap! Silakan isi semua field.']);
    exit;
}

$waktu_mulai_obj = new DateTime($tanggal_booking . ' ' . $waktu_mulai);
$waktu_selesai_obj = clone $waktu_mulai_obj;
$waktu_selesai_obj->modify('+' . $durasi_jam . ' hours');

$waktu_mulai_time = $waktu_mulai_obj->format('H:i:s');
$waktu_selesai_time = $waktu_selesai_obj->format('H:i:s');


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

$stmt = $conn->prepare($overlap_query);
$stmt->bind_param(
    "isssssss", 
    $meja_id, 
    $tanggal_booking,
    $waktu_selesai_time, $waktu_mulai_time,  // Check if existing ends after our start
    $waktu_selesai_time, $waktu_mulai_time,  // Check if existing starts before our end
    $waktu_mulai_time, $waktu_selesai_time   // Check if existing is within our range
);
$stmt->execute();
$overlap_result = $stmt->get_result();

if ($overlap_result->num_rows > 0) {
    $conflicting = $overlap_result->fetch_assoc();
    echo json_encode([
        'success' => false, 
        'message' => '⚠️ WAKTU BENTROK! Meja ini sudah dibooking dari ' . 
                     substr($conflicting['waktu_mulai'], 0, 5) . ' - ' . 
                     substr($conflicting['waktu_selesai'], 0, 5) . 
                     '. Silakan pilih waktu lain!'
    ]);
    $stmt->close();
    exit;
}
$stmt->close();


$tanggal_obj = new DateTime($tanggal_booking);
$day_of_week = (int)$tanggal_obj->format('N');
$is_weekend = ($day_of_week == 5 || $day_of_week == 6);

$harga = 0;

if ($paket_type === 'perjam') {
    $harga = ($is_weekend ? 55000 : 40000) * $durasi_jam;
} elseif ($paket_type === 'promo_siang') {
    $harga = 60000;
    $durasi_jam = 3;
} elseif ($paket_type === 'promo_malam') {
    $harga = $is_weekend ? 100000 : 80000;
    $durasi_jam = 4;
}


$diskon = 0;
$voucher_valid = false;

if (!empty($voucher_code)) {
    $voucher_query = "SELECT * FROM voucher WHERE kode = ? AND aktif = 1 AND tanggal_mulai <= CURDATE() AND tanggal_selesai >= CURDATE()";
    $stmt = $conn->prepare($voucher_query);
    $stmt->bind_param("s", $voucher_code);
    $stmt->execute();
    $voucher_result = $stmt->get_result();
    
    if ($voucher_result->num_rows > 0) {
        $voucher = $voucher_result->fetch_assoc();
        
        if ($harga >= $voucher['min_transaksi']) {
            $voucher_valid = true;
            
            if ($voucher['tipe_diskon'] === 'persen') {
                $diskon = ($harga * $voucher['nilai_diskon']) / 100;
                if ($voucher['max_diskon'] && $diskon > $voucher['max_diskon']) {
                    $diskon = $voucher['max_diskon'];
                }
            } else {
                $diskon = $voucher['nilai_diskon'];
            }
        }
    }
    $stmt->close();
}

$query = "INSERT INTO cart (user_id, meja_id, nama_meja, tanggal_booking, waktu_mulai, durasi_jam, paket_type, harga, voucher_code, diskon) 
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($query);
$voucher_to_save = $voucher_valid ? $voucher_code : null;
$stmt->bind_param("iisssisdsd", $user_id, $meja_id, $nama_meja, $tanggal_booking, $waktu_mulai_time, $durasi_jam, $paket_type, $harga, $voucher_to_save, $diskon);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true, 
        'message' => '✅ Berhasil ditambahkan ke keranjang!',
        'total' => $harga - $diskon,
        'diskon' => $diskon,
        'waktu_mulai' => $waktu_mulai_time,
        'waktu_selesai' => $waktu_selesai_time,
        'durasi' => $durasi_jam
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menambahkan: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>