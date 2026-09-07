<?php

require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$user_id = $_SESSION['user_id'];

// Get user info
$user_query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();
$stmt->close();

// Get cart items
$query = "SELECT * FROM cart WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows === 0) {
    header('Location: cart.php');
    exit;
}

$cart_items = [];
$total_harga = 0;
$total_diskon = 0;

while ($row = $result->fetch_assoc()) {
    $cart_items[] = $row;
    $total_harga += $row['harga'];
    if (isset($row['diskon'])) {
        $total_diskon += $row['diskon'];
    }
}
$stmt->close();

$grand_total = $total_harga - $total_diskon;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Legacy Billiard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            background: #0a0a0a;
            color: #fff;
            padding-top: 80px;
        }
        
        .checkout-page {
            min-height: 100vh;
            padding: 40px 5%;
        }
        
        .checkout-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 2rem;
        }
        
        .checkout-title {
            color: #D4AF37;
            font-size: 2.5rem;
            font-weight: 800;
            text-align: center;
            margin-bottom: 2rem;
            text-shadow: 0 0 20px rgba(212, 175, 55, 0.3);
            grid-column: 1 / -1;
        }
        
        .checkout-card {
            background: #1a1a1a;
            border: 2px solid #D4AF37;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
        }
        
        .card-title {
            color: #D4AF37;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            color: #fff;
            font-weight: 600;
            margin-bottom: 0.5rem;
            display: block;
        }
        
        .form-control {
            width: 100%;
            padding: 0.8rem;
            background: #2a2a2a;
            border: 2px solid #D4AF37;
            border-radius: 10px;
            color: #fff;
            font-size: 1rem;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #F5D068;
            box-shadow: 0 0 10px rgba(212, 175, 55, 0.3);
        }
        
        .form-control:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        /* Payment Methods */
        .payment-methods {
            display: grid;
            gap: 1rem;
            margin: 1.5rem 0;
        }
        
        .payment-option {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1.2rem;
            background: #2a2a2a;
            border: 2px solid #444;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .payment-option:hover {
            border-color: #D4AF37;
            background: #333;
        }
        
        .payment-option input[type="radio"] {
            width: 20px;
            height: 20px;
            accent-color: #D4AF37;
        }
        
        .payment-option input[type="radio"]:checked ~ .payment-icon,
        .payment-option input[type="radio"]:checked ~ .payment-info {
            color: #D4AF37;
        }
        
        .payment-option:has(input[type="radio"]:checked) {
            border-color: #D4AF37;
            background: rgba(212, 175, 55, 0.1);
            box-shadow: 0 0 20px rgba(212, 175, 55, 0.3);
        }
        
        .payment-icon {
            font-size: 2rem;
            color: #999;
            transition: color 0.3s ease;
        }
        
        .payment-info {
            flex: 1;
            color: #ccc;
        }
        
        .payment-name {
            font-weight: 700;
            font-size: 1.1rem;
            margin-bottom: 0.3rem;
        }
        
        .payment-desc {
            font-size: 0.85rem;
            color: #999;
        }
        
        /* Summary Card */
        .summary-item {
            background: #2a2a2a;
            padding: 1.2rem;
            border-radius: 12px;
            margin-bottom: 1rem;
            border: 2px solid #444;
        }
        
        .summary-item-header {
            color: #D4AF37;
            font-weight: 700;
            font-size: 1.2rem;
            margin-bottom: 0.8rem;
        }
        
        .summary-item-detail {
            color: #ccc;
            font-size: 0.9rem;
            margin: 0.4rem 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .summary-item-detail i {
            color: #D4AF37;
            width: 20px;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 0.8rem 0;
            color: #ccc;
            font-size: 1rem;
        }
        
        .summary-row.discount {
            color: #28a745;
        }
        
        .summary-divider {
            height: 2px;
            background: linear-gradient(to right, transparent, #D4AF37, transparent);
            margin: 1rem 0;
        }
        
        .summary-total {
            display: flex;
            justify-content: space-between;
            font-size: 1.8rem;
            font-weight: 700;
            color: #D4AF37;
            text-shadow: 0 0 15px rgba(212, 175, 55, 0.5);
            padding-top: 1rem;
        }
        
        .btn-checkout {
            width: 100%;
            background: linear-gradient(135deg, #D4AF37, #F5D068);
            color: #000;
            padding: 1.2rem;
            border: none;
            border-radius: 15px;
            font-size: 1.2rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1rem;
            box-shadow: 0 5px 20px rgba(212, 175, 55, 0.3);
        }
        
        .btn-checkout:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 35px rgba(212, 175, 55, 0.6);
        }
        
        .btn-checkout:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        @media (max-width: 992px) {
            .checkout-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="checkout-page">
        <h1 class="checkout-title">
            <i class="fas fa-shopping-bag"></i> Checkout
        </h1>

        <div class="checkout-container">
            <!-- LEFT: Form -->
            <div class="checkout-card">
                <h3 class="card-title">
                    <i class="fas fa-user-circle"></i> Informasi Pemesan
                </h3>

                <form id="checkout-form" method="POST" action="payment_process.php">
                    <div class="form-group">
                        <label for="nama" class="form-label">
                            <i class="fas fa-user"></i> Nama Lengkap
                        </label>
                        <input type="text" class="form-control" id="nama" name="nama" 
                               value="<?= htmlspecialchars($user['nama']) ?>" disabled>
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope"></i> Email
                        </label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?= htmlspecialchars($user['email']) ?>" disabled>
                    </div>

                    <div class="form-group">
                        <label for="no_telepon" class="form-label">
                            <i class="fas fa-phone"></i> Nomor Telepon
                        </label>
                        <input type="tel" class="form-control" id="no_telepon" name="no_telepon" 
                               placeholder="Contoh: +6281234567890" required>
                        <small style="color: #999; font-size: 0.85rem; display: block; margin-top: 0.3rem;">
                            <i class="fas fa-info-circle"></i> Nomor telepon untuk konfirmasi booking
                        </small>
                    </div>

                    <!-- Payment Method -->
                    <h3 class="card-title" style="margin-top: 2rem;">
                        <i class="fas fa-credit-card"></i> Metode Pembayaran
                    </h3>

                    <div class="payment-methods">
                        <label class="payment-option">
                            <input type="radio" name="payment_method" value="cash" required>
                            <i class="fas fa-money-bill-wave payment-icon"></i>
                            <div class="payment-info">
                                <div class="payment-name">Bayar di Tempat</div>
                                <div class="payment-desc">Bayar cash saat tiba di lokasi</div>
                            </div>
                        </label>

                        <label class="payment-option">
                            <input type="radio" name="payment_method" value="transfer" required>
                            <i class="fas fa-university payment-icon"></i>
                            <div class="payment-info">
                                <div class="payment-name">Transfer Bank</div>
                                <div class="payment-desc">Transfer ke rekening kami</div>
                            </div>
                        </label>

                        <label class="payment-option">
                            <input type="radio" name="payment_method" value="ewallet" required>
                            <i class="fas fa-mobile-alt payment-icon"></i>
                            <div class="payment-info">
                                <div class="payment-name">E-Wallet</div>
                                <div class="payment-desc">GoPay, OVO, Dana, ShopeePay</div>
                            </div>
                        </label>
                    </div>

                    <button type="submit" class="btn-checkout" id="btn-submit">
                        <i class="fas fa-arrow-right"></i> Lanjut ke Pembayaran
                    </button>
                </form>
            </div>

            <!-- RIGHT: Summary -->
            <div class="checkout-card summary-card">
                <h3 class="card-title">
                    <i class="fas fa-receipt"></i> Ringkasan Pesanan
                </h3>

                <?php foreach ($cart_items as $item): ?>
                    <div class="summary-item">
                        <div class="summary-item-header">
                            <i class="fas fa-dice-d6"></i> <?= htmlspecialchars($item['nama_meja']) ?>
                        </div>
                        <div class="summary-item-detail">
                            <i class="fas fa-calendar"></i> 
                            <?= date('d M Y', strtotime($item['tanggal_booking'])) ?>
                        </div>
                        <div class="summary-item-detail">
                            <i class="fas fa-clock"></i> 
                            <?= date('H:i', strtotime($item['waktu_mulai'])) ?> (<?= $item['durasi_jam'] ?> Jam)
                        </div>
                        <div class="summary-item-detail">
                            <i class="fas fa-tag"></i> 
                            <?php
                            if ($item['paket_type'] === 'perjam') {
                                echo 'Reguler Per Jam';
                            } elseif ($item['paket_type'] === 'promo_siang') {
                                echo 'Promo Siang (Happy Hour)';
                            } else {
                                echo 'Promo Malam';
                            }
                            ?>
                        </div>
                        <div class="summary-item-detail" style="margin-top: 0.8rem; padding-top: 0.8rem; border-top: 1px solid #444;">
                            <i class="fas fa-money-bill-wave"></i>
                            <strong>Rp <?= number_format($item['harga'], 0, ',', '.') ?></strong>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span>Rp <?= number_format($total_harga, 0, ',', '.') ?></span>
                </div>

                <?php if ($total_diskon > 0): ?>
                    <div class="summary-row discount">
                        <span>Diskon Voucher:</span>
                        <span>- Rp <?= number_format($total_diskon, 0, ',', '.') ?></span>
                    </div>
                <?php endif; ?>

                <div class="summary-divider"></div>

                <div class="summary-total">
                    <span>Total Bayar:</span>
                    <span>Rp <?= number_format($grand_total, 0, ',', '.') ?></span>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
        document.getElementById('checkout-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const phone = document.getElementById('no_telepon').value.trim();
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
            
            if (!phone) {
                alert('⚠️ Mohon isi nomor telepon!');
                return;
            }
            
            if (!paymentMethod) {
                alert('⚠️ Pilih metode pembayaran!');
                return;
            }
            
            // Show loading
            const btn = document.getElementById('btn-submit');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
            
            // Submit form
            this.submit();
        });
    </script>
</body>
</html>