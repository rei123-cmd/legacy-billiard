<?php

require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
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

// Get booking details
$query = "SELECT b.*, m.nama_meja, u.nama as user_name, u.email as user_email
          FROM booking b 
          LEFT JOIN meja m ON b.meja_id = m.id 
          LEFT JOIN users u ON b.user_id = u.id
          WHERE b.id IN ($ids_string) AND b.user_id = $user_id
          ORDER BY b.created_at DESC";
$result = $conn->query($query);

$bookings = [];
$total_bayar = 0;

while ($row = $result->fetch_assoc()) {
    $bookings[] = $row;
    $total_bayar += $row['total_harga'];
}

if (empty($bookings)) {
    header('Location: index.php');
    exit;
}

$first_booking = $bookings[0];
$invoice_number = 'INV-LB-' . date('Ymd') . '-' . str_pad($first_booking['id'], 5, '0', STR_PAD_LEFT);
$invoice_date = date('d F Y, H:i', strtotime($first_booking['created_at']));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?= $invoice_number ?> - Legacy Billiard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            padding: 2rem;
        }
        
        .invoice-container {
            max-width: 900px;
            margin: 0 auto;
            background: #fff;
            box-shadow: 0 0 30px rgba(0,0,0,0.1);
            border-radius: 15px;
            overflow: hidden;
        }
        
        /* Header */
        .invoice-header {
            background: linear-gradient(135deg, #1a1a1a 0%, #2a2a2a 100%);
            color: #fff;
            padding: 3rem 3rem 2rem;
            position: relative;
            overflow: hidden;
        }
        
        .invoice-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(212,175,55,0.2) 0%, transparent 70%);
            border-radius: 50%;
        }
        
        .invoice-logo {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
            position: relative;
            z-index: 2;
        }
        
        .invoice-logo i {
            font-size: 3rem;
            color: #D4AF37;
            animation: pulse 2s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .invoice-logo h1 {
            font-size: 2rem;
            font-weight: 900;
            color: #D4AF37;
            letter-spacing: 2px;
        }
        
        .invoice-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: #D4AF37;
            margin-bottom: 0.5rem;
        }
        
        .invoice-date {
            color: #ccc;
            font-size: 0.95rem;
        }
        
        /* Info Section */
        .invoice-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            padding: 2rem 3rem;
            background: #f9f9f9;
            border-bottom: 3px solid #D4AF37;
        }
        
        .info-block h3 {
            color: #1a1a1a;
            font-size: 1.1rem;
            margin-bottom: 0.8rem;
            font-weight: 700;
        }
        
        .info-block p {
            color: #666;
            margin: 0.3rem 0;
            font-size: 0.95rem;
        }
        
        .info-block strong {
            color: #1a1a1a;
        }
        
        /* Booking Items */
        .invoice-items {
            padding: 2rem 3rem;
        }
        
        .section-title {
            color: #1a1a1a;
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .section-title i {
            color: #D4AF37;
        }
        
        .booking-item {
            background: #f9f9f9;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }
        
        .booking-item:hover {
            border-color: #D4AF37;
            box-shadow: 0 5px 15px rgba(212,175,55,0.2);
        }
        
        .booking-item-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .item-title {
            color: #D4AF37;
            font-size: 1.2rem;
            font-weight: 700;
        }
        
        .item-price {
            color: #1a1a1a;
            font-size: 1.3rem;
            font-weight: 700;
        }
        
        .booking-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 0.8rem;
        }
        
        .detail-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #666;
            font-size: 0.9rem;
        }
        
        .detail-item i {
            color: #D4AF37;
            width: 20px;
        }
        
        /* Total Section */
        .invoice-total {
            padding: 2rem 3rem;
            background: #f9f9f9;
            border-top: 2px solid #e0e0e0;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 0.8rem 0;
            font-size: 1.1rem;
        }
        
        .total-row.grand {
            border-top: 3px solid #D4AF37;
            margin-top: 1rem;
            padding-top: 1.5rem;
            font-size: 1.8rem;
            font-weight: 900;
            color: #D4AF37;
        }
        
        /* Payment Info */
        .payment-info {
            padding: 2rem 3rem;
            background: #fff;
        }
        
        .payment-method-box {
            background: #f9f9f9;
            border: 2px solid #D4AF37;
            border-radius: 12px;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .payment-method-box i {
            font-size: 2.5rem;
            color: #D4AF37;
        }
        
        .payment-details h4 {
            color: #1a1a1a;
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
        }
        
        .payment-details p {
            color: #666;
            font-size: 0.9rem;
        }
        
        .payment-status {
            margin-top: 1rem;
            padding: 1rem;
            background: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 8px;
            color: #856404;
            text-align: center;
            font-weight: 700;
        }
        
        .payment-status.paid {
            background: #d4edda;
            border-color: #28a745;
            color: #155724;
        }
        
        /* Actions */
        .invoice-actions {
            padding: 2rem 3rem 3rem;
            display: flex;
            gap: 1rem;
            justify-content: center;
        }
        
        .btn {
            padding: 1rem 2.5rem;
            border: none;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-print {
            background: linear-gradient(135deg, #D4AF37, #F5D068);
            color: #000;
        }
        
        .btn-print:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(212,175,55,0.4);
        }
        
        .btn-home {
            background: #6c757d;
            color: #fff;
        }
        
        .btn-home:hover {
            background: #5a6268;
            transform: translateY(-3px);
        }
        
        /* Footer */
        .invoice-footer {
            background: #1a1a1a;
            color: #ccc;
            padding: 2rem 3rem;
            text-align: center;
            font-size: 0.9rem;
        }
        
        .invoice-footer p {
            margin: 0.3rem 0;
        }
        
        .invoice-footer strong {
            color: #D4AF37;
        }
        
        /* Print Styles */
        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            
            .invoice-container {
                box-shadow: none;
                border-radius: 0;
            }
            
            .invoice-actions {
                display: none !important;
            }
            
            .btn {
                display: none !important;
            }
        }
        
        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }
            
            .invoice-header,
            .invoice-info,
            .invoice-items,
            .invoice-total,
            .payment-info,
            .invoice-actions,
            .invoice-footer {
                padding: 1.5rem;
            }
            
            .invoice-info {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
            
            .invoice-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Header -->
        <div class="invoice-header">
            <div class="invoice-logo">
                <i class="fas fa-dice-d6"></i>
                <h1>LEGACY BILLIARD</h1>
            </div>
            <div class="invoice-number">Invoice #<?= $invoice_number ?></div>
            <div class="invoice-date"><i class="far fa-calendar"></i> <?= $invoice_date ?></div>
        </div>
        
        <!-- Info Section -->
        <div class="invoice-info">
            <div class="info-block">
                <h3><i class="fas fa-user-circle"></i> Customer Information</h3>
                <p><strong>Nama:</strong> <?= htmlspecialchars($first_booking['nama_pemesan']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($first_booking['user_email']) ?></p>
                <p><strong>Telepon:</strong> <?= htmlspecialchars($first_booking['telepon_pemesan']) ?></p>
            </div>
            
            <div class="info-block">
                <h3><i class="fas fa-building"></i> Venue Information</h3>
                <p><strong>Legacy Billiards BSD</strong></p>
                <p>Jl. Lingkar Luar Botanika Utara</p>
                <p>BSD City, Tangerang</p>
                <p><strong>Phone:</strong> +62 811-848-988</p>
            </div>
        </div>
        
        <!-- Booking Items -->
        <div class="invoice-items">
            <h3 class="section-title">
                <i class="fas fa-list-alt"></i> Booking Details
            </h3>
            
            <?php foreach($bookings as $booking): ?>
            <div class="booking-item">
                <div class="booking-item-header">
                    <div class="item-title">
                        <i class="fas fa-dice-d6"></i> <?= htmlspecialchars($booking['nama_meja']) ?>
                    </div>
                    <div class="item-price">
                        Rp <?= number_format($booking['total_harga'], 0, ',', '.') ?>
                    </div>
                </div>
                
                <div class="booking-details">
                    <div class="detail-item">
                        <i class="fas fa-calendar"></i>
                        <span><?= date('d F Y', strtotime($booking['tanggal_booking'])) ?></span>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-clock"></i>
                        <span><?= date('H:i', strtotime($booking['waktu_mulai'])) ?> WIB (<?= $booking['durasi_jam'] ?> Jam)</span>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-tag"></i>
                        <span>
                            <?php
                            if ($booking['paket_type'] === 'perjam') {
                                echo 'Reguler Per Jam';
                            } elseif ($booking['paket_type'] === 'promo_siang') {
                                echo 'Promo Siang (3 Jam)';
                            } else {
                                echo 'Promo Malam (4 Jam)';
                            }
                            ?>
                        </span>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-check-circle"></i>
                        <span style="color: #28a745; font-weight: 700;">
                            <?= ucfirst($booking['status']) ?>
                        </span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Total -->
        <div class="invoice-total">
            <div class="total-row">
                <span>Subtotal</span>
                <span>Rp <?= number_format($total_bayar, 0, ',', '.') ?></span>
            </div>
            <div class="total-row grand">
                <span>TOTAL BAYAR</span>
                <span>Rp <?= number_format($total_bayar, 0, ',', '.') ?></span>
            </div>
        </div>
        
        <!-- Payment Info -->
        <div class="payment-info">
            <h3 class="section-title">
                <i class="fas fa-credit-card"></i> Payment Information
            </h3>
            
            <div class="payment-method-box">
                <?php
                $payment_icon = 'fa-money-bill-wave';
                $payment_name = 'Bayar di Tempat';
                $payment_desc = 'Silakan bayar cash saat tiba di lokasi';
                
                if ($first_booking['payment_method'] === 'transfer') {
                    $payment_icon = 'fa-university';
                    $payment_name = 'Transfer Bank';
                    $payment_desc = 'Transfer ke rekening: BCA 1234567890 a/n Legacy Billiard';
                } elseif ($first_booking['payment_method'] === 'ewallet') {
                    $payment_icon = 'fa-mobile-alt';
                    $payment_name = 'E-Wallet';
                    $payment_desc = 'Hubungi kami untuk detail pembayaran e-wallet';
                }
                ?>
                <i class="fas <?= $payment_icon ?>"></i>
                <div class="payment-details">
                    <h4><?= $payment_name ?></h4>
                    <p><?= $payment_desc ?></p>
                </div>
            </div>
            
            <div class="payment-status <?= $first_booking['payment_status'] === 'paid' ? 'paid' : '' ?>">
                <?php if ($first_booking['payment_status'] === 'paid'): ?>
                    <i class="fas fa-check-circle"></i> LUNAS
                <?php else: ?>
                    <i class="fas fa-clock"></i> MENUNGGU PEMBAYARAN
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Actions -->
        <div class="invoice-actions">
            <button onclick="window.print()" class="btn btn-print">
                <i class="fas fa-print"></i> Print Invoice
            </button>
            <a href="index.php" class="btn btn-home">
                <i class="fas fa-home"></i> Kembali ke Home
            </a>
        </div>
        
        <!-- Footer -->
        <div class="invoice-footer">
            <p><strong>Terima kasih telah memilih Legacy Billiard!</strong></p>
            <p>Untuk pertanyaan, hubungi kami di +62 811-848-988 atau email info@legacybilliard.com</p>
            <p style="margin-top: 1rem; color: #999; font-size: 0.85rem;">
                Invoice ini dibuat secara otomatis dan sah tanpa tanda tangan
            </p>
        </div>
    </div>
    
    <script>
        // Auto redirect after 30 seconds (optional)
        setTimeout(() => {
            if (confirm('Redirect ke halaman Home?')) {
                window.location.href = 'index.php';
            }
        }, 30000);
    </script>
</body>
</html>