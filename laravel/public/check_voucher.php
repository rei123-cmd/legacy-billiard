<?php
require_once 'config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Silakan login terlebih dahulu']);
    exit;
}

$kode_voucher = isset($_POST['kode_voucher']) ? clean_input($_POST['kode_voucher']) : '';
$harga = isset($_POST['harga']) ? (float)$_POST['harga'] : 0;

if (empty($kode_voucher)) {
    echo json_encode(['success' => false, 'message' => 'Masukkan kode voucher']);
    exit;
}

// Check voucher
$query = "SELECT * FROM voucher 
          WHERE kode = ? AND aktif = 1 
          AND tanggal_mulai <= CURDATE() 
          AND tanggal_selesai >= CURDATE()";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $kode_voucher);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Kode voucher tidak valid atau sudah expired']);
    $stmt->close();
    exit;
}

$voucher = $result->fetch_assoc();

// Check min transaction
if ($harga < $voucher['min_transaksi']) {
    echo json_encode([
        'success' => false, 
        'message' => 'Minimal transaksi Rp ' . number_format($voucher['min_transaksi'], 0, ',', '.')
    ]);
    $stmt->close();
    exit;
}

// Check kuota
if ($voucher['kuota'] !== null && $voucher['terpakai'] >= $voucher['kuota']) {
    echo json_encode(['success' => false, 'message' => 'Kuota voucher sudah habis']);
    $stmt->close();
    exit;
}

// Calculate discount
$diskon = 0;
if ($voucher['tipe_diskon'] === 'persen') {
    $diskon = ($harga * $voucher['nilai_diskon']) / 100;
    if ($voucher['max_diskon'] && $diskon > $voucher['max_diskon']) {
        $diskon = $voucher['max_diskon'];
    }
} else {
    $diskon = $voucher['nilai_diskon'];
}

echo json_encode([
    'success' => true,
    'message' => 'Voucher berhasil diterapkan!',
    'voucher' => [
        'kode' => $voucher['kode'],
        'nama' => $voucher['nama'],
        'tipe' => $voucher['tipe_diskon'],
        'nilai' => $voucher['nilai_diskon'],
        'diskon' => $diskon,
        'harga_akhir' => $harga - $diskon
    ]
]);

$stmt->close();
$conn->close();
?>
