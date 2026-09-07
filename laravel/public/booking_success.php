<?php
require_once 'config.php';

if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

$booking_ids = isset($_GET['booking_ids']) ? explode(',', $_GET['booking_ids']) : [];
if (empty($booking_ids)) {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$ids_string = implode(',', array_map('intval', $booking_ids));

$query = "SELECT b.*, m.nama_meja 
          FROM booking b 
          LEFT JOIN meja m ON b.meja_id = m.id 
          WHERE b.id IN ($ids_string) AND b.user_id = $user_id";
$result = $conn->query($query);

$bookings = [];
while ($row = $result->fetch_assoc()) {
    $bookings[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Berhasil - Legacy Billiard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <main class="success-page">
        <div class="container my-5">
            <div class="success-container">
                <div class="success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h2 class="success-title">Booking Berhasil!</h2>
                <p class="success-message">Terima kasih telah melakukan booking di Legacy Billiard</p>
                
                <div class="booking-details">
                    <h3><i class="fas fa-list-alt"></i> Detail Booking Anda</h3>
                    <hr class="gold-hr">
                    
                    <?php foreach($bookings as $booking): ?>
                    <div class="booking-card">
                        <div class="booking-card-header">
                            <h4><i class="fas fa-dice-d6"></i> <?php echo htmlspecialchars($booking['nama_meja']); ?></h4>
                            <span class="badge bg-success">CONFIRMED</span>
                        </div>
                        <div class="booking-card-body">
                            <div class="info-row">
                                <i class="fas fa-calendar"></i>
                                <span><?php echo date('d F Y', strtotime($booking['tanggal_booking'])); ?></span>
                            </div>
                            <div class="info-row">
                                <i class="fas fa-clock"></i>
                                <span><?php echo $booking['waktu_mulai']; ?> (<?php echo $booking['durasi_jam']; ?> Jam)</span>
                            </div>
                            <div class="info-row">
                                <i class="fas fa-box"></i>
                                <span>
                                    <?php 
                                    $paket = ['perjam' => 'Reguler Per Jam', 'promo_siang' => 'Promo Siang', 'promo_malam' => 'Promo Malam'];
                                    echo $paket[$booking['paket_type']] ?? $booking['paket_type'];
                                    ?>
                                </span>
                            </div>
                            <div class="info-row">
                                <i class="fas fa-money-bill-wave"></i>
                                <strong class="text-gold">Rp <?php echo number_format($booking['total_harga'], 0, ',', '.'); ?></strong>
                            </div>
                            <div class="info-row">
                                <i class="fas fa-info-circle"></i>
                                <span>Status Pembayaran: 
                                    <strong class="<?php echo $booking['payment_status'] === 'paid' ? 'text-success' : 'text-warning'; ?>">
                                        <?php echo $booking['payment_status'] === 'paid' ? 'LUNAS' : 'BELUM LUNAS'; ?>
                                    </strong>
                                </span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="success-actions">
                    <a href="profile.php" class="btn-action btn-primary">
                        <i class="fas fa-user"></i> Lihat Profile
                    </a>
                    <a href="booking.php" class="btn-action btn-secondary">
                        <i class="fas fa-plus"></i> Booking Lagi
                    </a>
                    <a href="index.php" class="btn-action btn-outline">
                        <i class="fas fa-home"></i> Kembali ke Home
                    </a>
                </div>
            </div>
        </div>
    </main>

    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>