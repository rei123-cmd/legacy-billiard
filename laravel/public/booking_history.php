<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Get user data
$user_query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get booking history
$booking_query = "SELECT b.*, m.nama_meja, c.nama_cabang
                  FROM booking b
                  LEFT JOIN meja m ON b.meja_id = m.id
                  LEFT JOIN cabang c ON m.cabang_id = c.id
                  WHERE b.user_id = ?
                  ORDER BY b.created_at DESC";

$stmt = $conn->prepare($booking_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$bookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Booking - Legacy Billiard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            background: #0a0a0a;
            color: #fff;
        }
        
        .history-container {
            max-width: 1400px;
            margin: 120px auto 50px;
            padding: 0 20px;
        }
        
        .page-header {
            background: linear-gradient(135deg, #1a1a1a 0%, #0f0f0f 100%);
            border: 2px solid #D4AF37;
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .page-header h1 {
            color: #D4AF37;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }
        
        .page-header p {
            color: #999;
            margin: 0;
        }
        
        .booking-card {
            background: linear-gradient(135deg, #1a1a1a 0%, #0f0f0f 100%);
            border: 2px solid #333;
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
        }
        
        .booking-card:hover {
            border-color: #D4AF37;
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(212, 175, 55, 0.3);
        }
        
        .booking-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #333;
        }
        
        .booking-id {
            font-size: 1.5rem;
            font-weight: 800;
            color: #D4AF37;
        }
        
        .booking-date {
            color: #999;
            font-size: 0.9rem;
        }
        
        .booking-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .detail-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .detail-item i {
            color: #D4AF37;
            font-size: 1.2rem;
            width: 25px;
        }
        
        .detail-label {
            color: #999;
            font-size: 0.85rem;
        }
        
        .detail-value {
            color: #fff;
            font-weight: 600;
        }
        
        .booking-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 1rem;
            border-top: 2px solid #333;
        }
        
        .total-price {
            font-size: 1.5rem;
            font-weight: 800;
            color: #4CAF50;
        }
        
        .badge {
            padding: 0.5rem 1rem;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.85rem;
        }
        
        .badge-pending {
            background: rgba(255, 193, 7, 0.2);
            color: #FFC107;
            border: 2px solid #FFC107;
        }
        
        .badge-confirmed {
            background: rgba(76, 175, 80, 0.2);
            color: #4CAF50;
            border: 2px solid #4CAF50;
        }
        
        .badge-cancelled {
            background: rgba(244, 67, 54, 0.2);
            color: #f44336;
            border: 2px solid #f44336;
        }
        
        .badge-completed {
            background: rgba(33, 150, 243, 0.2);
            color: #2196F3;
            border: 2px solid #2196F3;
        }
        
        .badge-paid {
            background: rgba(76, 175, 80, 0.2);
            color: #4CAF50;
            border: 2px solid #4CAF50;
        }
        
        .badge-unpaid {
            background: rgba(244, 67, 54, 0.2);
            color: #f44336;
            border: 2px solid #f44336;
        }
        
        .empty-state {
            text-align: center;
            padding: 5rem 2rem;
            background: linear-gradient(135deg, #1a1a1a 0%, #0f0f0f 100%);
            border: 2px solid #333;
            border-radius: 20px;
        }
        
        .empty-state i {
            font-size: 5rem;
            color: #666;
            margin-bottom: 1rem;
        }
        
        .empty-state h3 {
            color: #999;
            margin-bottom: 1rem;
        }
        
        .btn-booking {
            background: linear-gradient(135deg, #D4AF37, #C5A028);
            color: #0a0a0a;
            padding: 0.8rem 2rem;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 700;
            display: inline-block;
            transition: all 0.3s ease;
        }
        
        .btn-booking:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(212, 175, 55, 0.6);
            color: #0a0a0a;
        }
        
        .filter-buttons {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }
        
        .filter-btn {
            background: transparent;
            border: 2px solid #333;
            color: #999;
            padding: 0.5rem 1.5rem;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .filter-btn.active,
        .filter-btn:hover {
            border-color: #D4AF37;
            color: #D4AF37;
            background: rgba(212, 175, 55, 0.1);
        }
        
        @media (max-width: 768px) {
            .booking-details {
                grid-template-columns: 1fr;
            }
            
            .booking-footer {
                flex-direction: column;
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="history-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1><i class="fas fa-history"></i> Riwayat Booking</h1>
            <p>Lihat semua riwayat pemesanan meja billiard Anda</p>
        </div>

        <!-- Filter Buttons -->
        <div class="filter-buttons">
            <button class="filter-btn active" onclick="filterBookings('all')">
                <i class="fas fa-list"></i> Semua
            </button>
            <button class="filter-btn" onclick="filterBookings('pending')">
                <i class="fas fa-clock"></i> Pending
            </button>
            <button class="filter-btn" onclick="filterBookings('confirmed')">
                <i class="fas fa-check-circle"></i> Confirmed
            </button>
            <button class="filter-btn" onclick="filterBookings('completed')">
                <i class="fas fa-flag-checkered"></i> Completed
            </button>
            <button class="filter-btn" onclick="filterBookings('cancelled')">
                <i class="fas fa-times-circle"></i> Cancelled
            </button>
        </div>

        <!-- Booking List -->
        <div id="booking-list">
            <?php if (empty($bookings)): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h3>Belum Ada Riwayat Booking</h3>
                    <p style="color: #666; margin-bottom: 2rem;">Mulai booking meja billiard favorit Anda sekarang!</p>
                    <a href="booking.php" class="btn-booking">
                        <i class="fas fa-plus-circle"></i> Booking Sekarang
                    </a>
                </div>
            <?php else: ?>
                <?php foreach ($bookings as $booking): ?>
                    <div class="booking-card" data-status="<?= $booking['status'] ?>">
                        <!-- Booking Header -->
                        <div class="booking-header">
                            <div>
                                <div class="booking-id">#<?= str_pad($booking['id'], 5, '0', STR_PAD_LEFT) ?></div>
                                <div class="booking-date">
                                    <i class="fas fa-calendar-alt"></i> 
                                    Dibuat: <?= date('d M Y, H:i', strtotime($booking['created_at'])) ?>
                                </div>
                            </div>
                            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                <span class="badge badge-<?= $booking['status'] ?>">
                                    <?= strtoupper($booking['status']) ?>
                                </span>
                                <span class="badge badge-<?= $booking['payment_status'] ?>">
                                    <?= strtoupper($booking['payment_status']) ?>
                                </span>
                            </div>
                        </div>

                        <!-- Booking Details -->
                        <div class="booking-details">
                            <div class="detail-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <div>
                                    <div class="detail-label">Cabang</div>
                                    <div class="detail-value"><?= htmlspecialchars($booking['nama_cabang']) ?></div>
                                </div>
                            </div>
                            
                            <div class="detail-item">
                                <i class="fas fa-dice-d6"></i>
                                <div>
                                    <div class="detail-label">Meja</div>
                                    <div class="detail-value"><?= htmlspecialchars($booking['nama_meja']) ?></div>
                                </div>
                            </div>
                            
                            <div class="detail-item">
                                <i class="fas fa-calendar"></i>
                                <div>
                                    <div class="detail-label">Tanggal</div>
                                    <div class="detail-value"><?= date('d M Y', strtotime($booking['tanggal_booking'])) ?></div>
                                </div>
                            </div>
                            
                            <div class="detail-item">
                                <i class="fas fa-clock"></i>
                                <div>
                                    <div class="detail-label">Waktu Mulai</div>
                                    <div class="detail-value"><?= date('H:i', strtotime($booking['waktu_mulai'])) ?></div>
                                </div>
                            </div>
                            
                            <div class="detail-item">
                                <i class="fas fa-hourglass-half"></i>
                                <div>
                                    <div class="detail-label">Durasi</div>
                                    <div class="detail-value"><?= $booking['durasi_jam'] ?> Jam</div>
                                </div>
                            </div>
                            
                            <div class="detail-item">
                                <i class="fas fa-tag"></i>
                                <div>
                                    <div class="detail-label">Paket</div>
                                    <div class="detail-value">
                                        <?php
                                        $paket_name = [
                                            'perjam' => 'Per Jam',
                                            'promo_siang' => 'Promo Siang',
                                            'promo_malam' => 'Promo Malam'
                                        ];
                                        echo $paket_name[$booking['paket_type']] ?? 'Unknown';
                                        ?>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if (!empty($booking['voucher_code'])): ?>
                            <div class="detail-item">
                                <i class="fas fa-ticket-alt"></i>
                                <div>
                                    <div class="detail-label">Voucher</div>
                                    <div class="detail-value" style="color: #4CAF50;"><?= htmlspecialchars($booking['voucher_code']) ?></div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Booking Footer -->
                        <div class="booking-footer">
                            <div>
                                <?php if ($booking['diskon'] > 0): ?>
                                    <div style="color: #999; font-size: 0.9rem; text-decoration: line-through;">
                                        Rp <?= number_format($booking['total_harga'] + $booking['diskon'], 0, ',', '.') ?>
                                    </div>
                                    <div style="color: #4CAF50; font-size: 0.85rem; margin-bottom: 0.3rem;">
                                        <i class="fas fa-tag"></i> Hemat Rp <?= number_format($booking['diskon'], 0, ',', '.') ?>
                                    </div>
                                <?php endif; ?>
                                <div class="total-price">
                                    Rp <?= number_format($booking['total_harga'], 0, ',', '.') ?>
                                </div>
                            </div>
                            
                            <div>
                                <div style="color: #999; font-size: 0.85rem;">Nama Pemesan</div>
                                <div style="color: #fff; font-weight: 600;"><?= htmlspecialchars($booking['nama_pemesan']) ?></div>
                                <div style="color: #999; font-size: 0.85rem;">
                                    <i class="fas fa-phone"></i> <?= htmlspecialchars($booking['telepon_pemesan']) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function filterBookings(status) {
            const cards = document.querySelectorAll('.booking-card');
            const buttons = document.querySelectorAll('.filter-btn');
            
            // Update active button
            buttons.forEach(btn => btn.classList.remove('active'));
            event.target.closest('.filter-btn').classList.add('active');
            
            // Filter cards
            cards.forEach(card => {
                if (status === 'all' || card.dataset.status === status) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>
