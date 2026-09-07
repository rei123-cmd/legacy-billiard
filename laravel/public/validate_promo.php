<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_POST['kode_promo']) || !isset($_POST['total_harga'])) {
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
    exit;
}

$kode_promo = clean_input($_POST['kode_promo']);
$total_harga = (float)$_POST['total_harga'];

// Get promo data
$query = "SELECT * FROM promo 
          WHERE kode_promo = ? 
          AND aktif = 1 
          AND (tanggal_selesai IS NULL OR tanggal_selesai >= CURDATE())
          LIMIT 1";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $kode_promo);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'Kode promo tidak valid atau sudah kadaluarsa']);
    exit;
}

$promo = $result->fetch_assoc();

// Check minimum purchase
if ($promo['min_pembelian'] > 0 && $total_harga < $promo['min_pembelian']) {
    echo json_encode([
        'success' => false, 
        'message' => 'Minimal pembelian Rp ' . number_format($promo['min_pembelian'], 0, ',', '.')
    ]);
    exit;
}

// Check quota
if ($promo['kuota_penggunaan'] !== null && $promo['jumlah_terpakai'] >= $promo['kuota_penggunaan']) {
    echo json_encode(['success' => false, 'message' => 'Kuota promo sudah habis']);
    exit;
}

// Calculate discount
$diskon = 0;
if ($promo['tipe_diskon'] == 'persen') {
    $diskon = ($total_harga * $promo['nilai_diskon']) / 100;
    
    // Apply max discount limit
    if ($promo['max_diskon'] !== null && $diskon > $promo['max_diskon']) {
        $diskon = $promo['max_diskon'];
    }
} else {
    $diskon = $promo['nilai_diskon'];
}

$total_setelah_diskon = $total_harga - $diskon;

echo json_encode([
    'success' => true,
    'message' => 'Kode promo berhasil diterapkan!',
    'promo_id' => $promo['id'],
    'kode_promo' => $promo['kode_promo'],
    'diskon' => $diskon,
    'total_awal' => $total_harga,
    'total_akhir' => $total_setelah_diskon
]);

$stmt->close();
$conn->close();
?>
