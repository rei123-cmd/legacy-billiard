<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['meja_id']) || !isset($_GET['nama'])) {
    header('Location: booking.php');
    exit;
}

$meja_id = (int)$_GET['meja_id'];
$nama_meja = clean_input($_GET['nama']);

// Get table info
$query = "SELECT m.*, c.nama_cabang FROM meja m JOIN cabang c ON m.cabang_id = c.id WHERE m.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $meja_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: booking.php');
    exit;
}

$meja = $result->fetch_assoc();
$stmt->close();

// Get active vouchers
$voucher_query = "SELECT * FROM voucher WHERE aktif = 1 AND tanggal_mulai <= CURDATE() AND tanggal_selesai >= CURDATE() ORDER BY nilai_diskon DESC LIMIT 5";
$vouchers = $conn->query($voucher_query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Booking - Legacy Billiard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* NAVBAR FIX */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem 5%;
            background: linear-gradient(to bottom, var(--secondary-dark), transparent);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(212, 175, 55, 0.1);
        }
        
        .navbar .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .logo {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--gold);
            text-decoration: none;
            letter-spacing: 2px;
        }
        
        .nav-links {
            display: flex;
            list-style: none;
            gap: 2.5rem;
            margin: 0;
            padding: 0;
        }
        
        .nav-links a {
            color: var(--text-light);
            text-decoration: none;
            font-size: 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
            padding: 0.5rem 0;
        }
        
        .nav-links a:hover,
        .nav-links a.active {
            color: var(--gold);
        }
        
        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 2px;
            background: var(--gold);
            transition: width 0.3s ease;
        }
        
        .nav-links a:hover::after,
        .nav-links a.active::after {
            width: 100%;
        }
        
        .nav-buttons {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }
        
        .btn-cart-nav {
            position: relative;
            color: var(--gold);
            font-size: 1.5rem;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .btn-cart-nav:hover {
            transform: scale(1.1);
        }
        
        .cart-count {
            position: absolute;
            top: -8px;
            right: -10px;
            background: #dc3545;
            color: #fff;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.7rem;
            font-weight: 700;
        }
        
        .btn-profile,
        .btn-logout {
            background: linear-gradient(135deg, var(--gold), var(--gold-dark));
            color: var(--primary-dark);
            padding: 0.7rem 1.8rem;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        
        .btn-profile:hover,
        .btn-logout:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(212, 175, 55, 0.5);
        }
        
        /* Additional Styles */
        .booking-detail-page {
            min-height: 100vh;
            padding: 120px 5% 50px;
            background: var(--primary-dark);
        }
        
        .booking-detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .detail-card {
            background: var(--secondary-dark);
            border: 2px solid var(--gold);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
        }
        
        .card-title {
            color: var(--gold);
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .meja-title {
            color: #fff;
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }
        
        .meja-location {
            color: #999;
            margin-bottom: 1.5rem;
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
            background: var(--tertiary-dark);
            border: 2px solid var(--gold);
            border-radius: 10px;
            color: #fff;
            font-size: 1rem;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--gold-light);
            box-shadow: 0 0 10px rgba(212, 175, 55, 0.3);
        }
        
        .section-title {
            color: var(--gold);
            font-size: 1.3rem;
            font-weight: 700;
            margin: 1.5rem 0 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .paket-grid {
            display: grid;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .paket-card {
            background: var(--tertiary-dark);
            border: 2px solid var(--gold);
            border-radius: 15px;
            padding: 1.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .paket-card:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 20px rgba(212, 175, 55, 0.3);
        }
        
        .paket-card.active {
            background: linear-gradient(135deg, var(--gold-dark), var(--gold));
            color: var(--primary-dark);
        }
        
        .paket-card i {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            display: block;
        }
        
        .paket-name {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 0.3rem;
            color: #fff;
        }
        
        .paket-card.active .paket-name,
        .paket-card.active .paket-price,
        .paket-card.active .paket-desc {
            color: var(--primary-dark);
        }
        
        .paket-price {
            font-size: 1rem;
            font-weight: 600;
            color: var(--gold);
            margin-bottom: 0.3rem;
        }
        
        .paket-desc {
            font-size: 0.9rem;
            color: #999;
        }
        
        .time-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 0.8rem;
            margin: 1rem 0;
        }
        
        .btn-waktu {
            padding: 1rem;
            background: var(--tertiary-dark);
            border: 2px solid var(--gold);
            border-radius: 10px;
            color: #fff;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-waktu:hover:not(.disabled) {
            background: var(--gold);
            color: var(--primary-dark);
            transform: scale(1.05);
        }
        
        .btn-waktu.selected {
            background: var(--gold);
            color: var(--primary-dark);
            box-shadow: 0 5px 20px rgba(212, 175, 55, 0.5);
        }
        
        .btn-waktu.disabled {
            background: #1a1a1a;
            border-color: #555;
            color: #666;
            cursor: not-allowed;
            opacity: 0.5;
        }
        
        .voucher-grid {
            display: grid;
            gap: 1rem;
            margin: 1rem 0;
        }
        
        .voucher-card {
            background: linear-gradient(135deg, var(--tertiary-dark) 0%, var(--secondary-dark) 100%);
            border: 2px solid var(--gold);
            border-radius: 15px;
            padding: 1.2rem 1.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 1rem;
            position: relative;
            overflow: hidden;
        }
        
        .voucher-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(212, 175, 55, 0.1) 0%, transparent 70%);
            transform: scale(0);
            transition: transform 0.5s ease;
        }
        
        .voucher-card:hover::before {
            transform: scale(1);
        }
        
        .voucher-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(212, 175, 55, 0.5);
            border-color: var(--gold-light);
        }
        
        .voucher-card.active {
            background: linear-gradient(135deg, rgba(76, 175, 80, 0.3), rgba(76, 175, 80, 0.1));
            border-color: #4CAF50;
            box-shadow: 0 8px 25px rgba(76, 175, 80, 0.4);
        }
        
        .voucher-icon {
            font-size: 2.5rem;
            color: var(--gold);
            min-width: 60px;
            text-align: center;
            position: relative;
            z-index: 1;
        }
        
        .voucher-info {
            flex: 1;
            position: relative;
            z-index: 1;
        }
        
        .voucher-code {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--gold);
            margin-bottom: 0.3rem;
            text-shadow: 0 0 10px rgba(212, 175, 55, 0.3);
        }
        
        .voucher-name {
            font-size: 0.95rem;
            color: #fff;
            margin-bottom: 0.2rem;
            font-weight: 600;
        }
        
        .voucher-desc {
            font-size: 0.85rem;
            color: #aaa;
        }
        
        .voucher-badge {
            background: linear-gradient(135deg, var(--gold), var(--gold-light));
            color: var(--primary-dark);
            padding: 0.6rem 1.2rem;
            border-radius: 25px;
            font-weight: 700;
            font-size: 0.85rem;
            box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3);
            position: relative;
            z-index: 1;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .alert-success {
            background: linear-gradient(135deg, rgba(76, 175, 80, 0.2), rgba(76, 175, 80, 0.1));
            border: 2px solid #4CAF50;
            border-radius: 15px;
            padding: 1rem 1.5rem;
            margin: 1rem 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            animation: slideIn 0.5s ease;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        .alert-success strong {
            color: #4CAF50;
        }
        
        .btn-remove {
            background: transparent;
            border: none;
            color: #dc3545;
            font-size: 1.3rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-remove:hover {
            transform: scale(1.2);
        }
        
        .summary-card {
            background: linear-gradient(135deg, var(--tertiary-dark), var(--secondary-dark));
            border: 2px solid var(--gold);
            border-radius: 15px;
            padding: 1.5rem;
            margin-top: 1.5rem;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin: 0.8rem 0;
            color: #fff;
        }
        
        .summary-row.discount {
            color: #4CAF50;
            font-weight: 600;
        }
        
        .summary-divider {
            border: none;
            height: 2px;
            background: linear-gradient(to right, transparent, var(--gold), transparent);
            margin: 1rem 0;
        }
        
        .summary-total {
            display: flex;
            justify-content: space-between;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--gold);
            text-shadow: 0 0 15px rgba(212, 175, 55, 0.5);
        }
        
        .btn-action {
            width: 100%;
            padding: 1.2rem;
            border: none;
            border-radius: 15px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .btn-cart {
            background: transparent;
            border: 2px solid var(--gold);
            color: var(--gold);
        }
        
        .btn-cart:hover {
            background: linear-gradient(135deg, rgba(212, 175, 55, 0.2), rgba(212, 175, 55, 0.1));
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(212, 175, 55, 0.4);
        }
        
        .btn-checkout {
            background: linear-gradient(135deg, var(--gold-dark), var(--gold));
            color: var(--primary-dark);
            box-shadow: 0 5px 20px rgba(212, 175, 55, 0.3);
        }
        
        .btn-checkout:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 35px rgba(212, 175, 55, 0.6);
            background: linear-gradient(135deg, var(--gold), var(--gold-light));
        }
        
        .price-info-card {
            background: linear-gradient(135deg, var(--tertiary-dark), var(--secondary-dark));
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }
        
        .price-info-card:hover {
            border-color: var(--gold);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(212, 175, 55, 0.3);
        }
        
        .price-info-card h4 {
            color: var(--gold);
            font-size: 1.1rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .price-info-card p {
            color: #fff;
            margin: 0.5rem 0;
            line-height: 1.6;
        }
        
        .price-info-card strong {
            color: var(--gold);
        }
        
        .info-box {
            background: linear-gradient(135deg, rgba(212, 175, 55, 0.1), rgba(212, 175, 55, 0.05));
            border-left: 4px solid var(--gold);
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }
        
        .info-box p {
            color: #fff;
            margin: 0;
            line-height: 1.6;
        }
        
        @media (max-width: 992px) {
            .booking-detail-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- NAVBAR RAPI -->
    <nav class="navbar">
        <a href="index.php" class="logo">LEGACY BILLIARD</a>
        <ul class="nav-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="gallery.php">Gallery</a></li>
            <li><a href="booking.php" class="active">Pricing & Booking</a></li>
            <li><a href="promo.php">Promo</a></li>
            <li><a href="aboutus.php">About Us</a></li>
        </ul>
        <div class="nav-buttons">
            <a href="cart.php" class="btn-cart-nav">
                <i class="fas fa-shopping-cart"></i>
                <span class="cart-count" id="cart-count">0</span>
            </a>
            <a href="profile.php" class="btn-profile">
                <i class="fas fa-user"></i> Profile
            </a>
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="booking-detail-page">
        <div class="booking-detail-grid">
            <!-- LEFT CARD -->
            <div class="detail-card">
                <h2 class="card-title">
                    <i class="fas fa-clipboard-list"></i>
                    Detail Pesanan Anda
                </h2>
                
                <h3 class="meja-title"><?= htmlspecialchars($nama_meja) ?></h3>
                <p class="meja-location">
                    <i class="fas fa-map-marker-alt"></i>
                    <?= htmlspecialchars($meja['nama_cabang']) ?> - Lantai <?= $meja['lantai'] ?>
                </p>
                
                <div style="border-bottom: 2px solid var(--gold); margin: 1rem 0;"></div>
                
                <!-- Tanggal -->
                <div class="form-group">
                    <label for="tanggal-booking" class="form-label">
                        <i class="fas fa-calendar-alt"></i> Tanggal Booking
                    </label>
                    <input type="date" class="form-control" id="tanggal-booking" min="<?= date('Y-m-d') ?>">
                </div>

                <!-- Paket -->
                <h3 class="section-title">
                    <i class="fas fa-box"></i> Pilih Paket Bermain
                </h3>
                <div class="paket-grid">
                    <div class="paket-card active" data-paket="perjam">
                        <i class="fas fa-clock"></i>
                        <div class="paket-name">Reguler Per Jam</div>
                        <div class="paket-price">Rp 40.000 - Rp 55.000</div>
                        <div class="paket-desc">Weekday: Rp 40.000 | Weekend: Rp 55.000</div>
                    </div>
                    <div class="paket-card" data-paket="promo_siang">
                        <i class="fas fa-sun"></i>
                        <div class="paket-name">Promo Siang (Happy Hour)</div>
                        <div class="paket-price">Rp 60.000</div>
                        <div class="paket-desc">3 Jam | Weekday: 11:00-15:00</div>
                    </div>
                    <div class="paket-card" data-paket="promo_malam">
                        <i class="fas fa-moon"></i>
                        <div class="paket-name">Promo Malam</div>
                        <div class="paket-price">Rp 80.000 - Rp 100.000</div>
                        <div class="paket-desc">4 Jam | 19:00-23:00</div>
                    </div>
                </div>

                <!-- Durasi -->
                <div id="wadah-durasi" class="form-group">
                    <label for="durasi-main" class="form-label">
                        <i class="fas fa-hourglass-half"></i> Durasi Bermain
                    </label>
                    <select id="durasi-main" class="form-control">
                        <option value="1">1 Jam</option>
                        <option value="2" selected>2 Jam</option>
                        <option value="3">3 Jam</option>
                        <option value="4">4 Jam</option>
                        <option value="5">5 Jam</option>
                    </select>
                </div>

                <!-- Waktu -->
                <h3 class="section-title">
                    <i class="fas fa-clock"></i> Pilih Waktu Mulai
                </h3>
                <div id="wadah_waktu" class="time-grid">
                    <p style="text-align: center; color: #999; grid-column: 1/-1;">
                        Pilih tanggal terlebih dahulu
                    </p>
                </div>

                <!-- Voucher -->
                <h3 class="section-title">
                    <i class="fas fa-ticket-alt"></i> Voucher Tersedia (Klik untuk Pakai)
                </h3>
                <div class="voucher-grid">
                    <?php if ($vouchers->num_rows > 0): ?>
                        <?php while ($v = $vouchers->fetch_assoc()): ?>
                            <div class="voucher-card" onclick="applyVoucher('<?= $v['kode'] ?>', '<?= $v['nama'] ?>', '<?= $v['tipe_diskon'] ?>', <?= $v['nilai_diskon'] ?>, <?= $v['min_transaksi'] ?>, <?= $v['max_diskon'] ?? 'null' ?>)">
                                <div class="voucher-icon">
                                    <i class="fas fa-gift"></i>
                                </div>
                                <div class="voucher-info">
                                    <div class="voucher-code"><?= $v['kode'] ?></div>
                                    <div class="voucher-name"><?= $v['nama'] ?></div>
                                    <div class="voucher-desc">
                                        <?php if ($v['tipe_diskon'] === 'persen'): ?>
                                            Diskon <?= $v['nilai_diskon'] ?>%
                                            <?php if ($v['max_diskon']): ?>
                                                (Maks. Rp <?= number_format($v['max_diskon'], 0, ',', '.') ?>)
                                            <?php endif; ?>
                                        <?php else: ?>
                                            Potongan Rp <?= number_format($v['nilai_diskon'], 0, ',', '.') ?>
                                        <?php endif; ?>
                                        <?php if ($v['min_transaksi'] > 0): ?>
                                            | Min. Rp <?= number_format($v['min_transaksi'], 0, ',', '.') ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="voucher-badge">KLIK</div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p style="text-align: center; color: #999;">Tidak ada voucher tersedia</p>
                    <?php endif; ?>
                </div>

                <!-- Voucher Applied -->
                <div id="voucher-applied" class="alert-success" style="display: none;">
                    <div>
                        <i class="fas fa-check-circle"></i>
                        <strong id="voucher-applied-text"></strong>
                    </div>
                    <button class="btn-remove" onclick="removeVoucher()">
                        <i class="fas fa-times-circle"></i>
                    </button>
                </div>

                <!-- Summary -->
                <div id="ringkasan-pesanan" class="summary-card" style="display: none;">
                    <h3 style="color: var(--gold); margin-bottom: 1rem;">
                        <i class="fas fa-receipt"></i> Ringkasan Pesanan
                    </h3>
                    <div class="summary-row">
                        <span>Paket:</span>
                        <span id="summary-paket">-</span>
                    </div>
                    <div class="summary-row">
                        <span>Waktu:</span>
                        <span id="summary-waktu">-</span>
                    </div>
                    <div class="summary-row">
                        <span>Harga:</span>
                        <span id="summary-harga-awal">Rp 0</span>
                    </div>
                    <div id="diskon-row" class="summary-row discount" style="display: none;">
                        <span>Diskon:</span>
                        <span id="summary-diskon">Rp 0</span>
                    </div>
                    <div class="summary-divider"></div>
                    <div class="summary-total">
                        <span>Total:</span>
                        <span id="summary-harga">Rp 0</span>
                    </div>
                </div>

                <!-- Buttons -->
                <form id="form-pemesanan-detail">
                    <input type="hidden" name="meja_id" value="<?= $meja_id ?>">
                    <input type="hidden" name="nama_meja" value="<?= htmlspecialchars($nama_meja) ?>">
                    <input type="hidden" id="hidden-tanggal" name="tanggal_booking">
                    <input type="hidden" id="hidden-waktu" name="waktu_mulai">
                    <input type="hidden" id="hidden-durasi" name="durasi_jam">
                    <input type="hidden" id="hidden-paket" name="paket_type">
                    <input type="hidden" id="hidden-voucher" name="voucher_code">
                    
                    <button type="button" class="btn-action btn-cart" onclick="tambahKeKeranjang()">
                        <i class="fas fa-shopping-cart"></i> Tambah ke Keranjang
                    </button>
                    <button type="button" class="btn-action btn-checkout" onclick="bookingLangsung()">
                        <i class="fas fa-check-circle"></i> Booking Langsung
                    </button>
                </form>
            </div>

            <!-- RIGHT CARD -->
            <div class="detail-card">
                <h2 class="card-title">
                    <i class="fas fa-tag"></i>
                    Detail Harga
                </h2>

                <div class="price-info-card">
                    <h4><i class="fas fa-clock"></i> Reguler Per Jam</h4>
                    <p><strong>Weekday</strong> (Minggu-Kamis): Rp 40.000</p>
                    <p><strong>Weekend</strong> (Jum'at-Sabtu): Rp 55.000</p>
                </div>

                <div class="price-info-card">
                    <h4><i class="fas fa-sun"></i> Promo Siang (Happy Hour)</h4>
                    <p><strong>Harga:</strong> Rp 60.000</p>
                    <p><strong>Durasi:</strong> 3 Jam</p>
                    <p><strong>Waktu:</strong> 11:00 - 15:00 (Weekday)</p>
                </div>

                <div class="price-info-card">
                    <h4><i class="fas fa-moon"></i> Promo Malam (19:00-23:00)</h4>
                    <p><strong>Weekday:</strong> Rp 80.000 / 4 jam</p>
                    <p><strong>Weekend:</strong> Rp 100.000 / 4 jam</p>
                </div>

                <div class="info-box">
                    <i class="fas fa-info-circle" style="color: var(--gold); font-size: 1.5rem; margin-bottom: 0.5rem;"></i>
                    <p><strong>Catatan:</strong> Pilih tanggal, paket, dan waktu untuk melihat total harga. Gunakan voucher untuk mendapatkan diskon!</p>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script src="main.js"></script>
    <script>
        let bookingState = {
            mejaId: <?= $meja_id ?>,
            namaMeja: '<?= htmlspecialchars($nama_meja) ?>',
            tanggal: null,
            waktu: null,
            paket: 'perjam',
            durasi: 2,
            hargaAwal: 0,
            voucher: null,
            diskon: 0,
            hargaAkhir: 0
        };

        fetch('get_cart_count.php').then(res => res.json()).then(data => {
            document.getElementById('cart-count').textContent = data.count || 0;
        });

        document.querySelectorAll('.paket-card').forEach(card => {
            card.addEventListener('click', function() {
                document.querySelectorAll('.paket-card').forEach(c => c.classList.remove('active'));
                this.classList.add('active');
                bookingState.paket = this.dataset.paket;
                document.getElementById('hidden-paket').value = bookingState.paket;
                
                if (bookingState.paket === 'perjam') {
                    document.getElementById('wadah-durasi').style.display = 'block';
                    bookingState.durasi = parseInt(document.getElementById('durasi-main').value);
                } else {
                    document.getElementById('wadah-durasi').style.display = 'none';
                    bookingState.durasi = (bookingState.paket === 'promo_siang') ? 3 : 4;
                }
                
                document.getElementById('hidden-durasi').value = bookingState.durasi;
                if (bookingState.tanggal) loadTimeSlots();
                updateSummary();
            });
        });

        document.getElementById('durasi-main').addEventListener('change', function() {
            bookingState.durasi = parseInt(this.value);
            document.getElementById('hidden-durasi').value = bookingState.durasi;
            updateSummary();
        });

        document.getElementById('tanggal-booking').addEventListener('change', function() {
            bookingState.tanggal = this.value;
            document.getElementById('hidden-tanggal').value = this.value;
            bookingState.waktu = null;
            document.querySelectorAll('.btn-waktu').forEach(btn => btn.classList.remove('selected'));
            loadTimeSlots();
        });

        function loadTimeSlots() {
            const container = document.getElementById('wadah_waktu');
            container.innerHTML = '<p style="text-align: center; color: #fff; grid-column: 1/-1;"><i class="fas fa-spinner fa-spin"></i> Memuat...</p>';
            
            fetch(`get_available_slots.php?meja_id=${bookingState.mejaId}&tanggal=${bookingState.tanggal}&paket=${bookingState.paket}`)
                .then(res => res.json())
                .then(data => renderTimeSlots(data.bookedSlots || []))
                .catch(err => {
                    console.error(err);
                    container.innerHTML = '<p style="text-align: center; color: #dc3545; grid-column: 1/-1;">Gagal memuat</p>';
                });
        }

        function renderTimeSlots(bookedSlots) {
            const container = document.getElementById('wadah_waktu');
            container.innerHTML = '';
            
            let slots = [];
            if (bookingState.paket === 'promo_siang') {
                for (let i = 11; i <= 15; i++) slots.push(i.toString().padStart(2, '0') + ':00');
            } else if (bookingState.paket === 'promo_malam') {
                for (let i = 19; i <= 23; i++) slots.push(i.toString().padStart(2, '0') + ':00');
            } else {
                for (let i = 9; i <= 23; i++) slots.push(i.toString().padStart(2, '0') + ':00');
            }
            
            slots.forEach(time => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'btn-waktu';
                btn.textContent = time;
                
                if (bookedSlots.includes(time + ':00')) {
                    btn.classList.add('disabled');
                    btn.disabled = true;
                } else {
                    btn.addEventListener('click', function() {
                        document.querySelectorAll('.btn-waktu').forEach(b => b.classList.remove('selected'));
                        this.classList.add('selected');
                        bookingState.waktu = time;
                        document.getElementById('hidden-waktu').value = time + ':00';
                        updateSummary();
                    });
                }
                
                container.appendChild(btn);
            });
        }

        function updateSummary() {
            if (!bookingState.waktu || !bookingState.tanggal) {
                document.getElementById('ringkasan-pesanan').style.display = 'none';
                return;
            }
            
            const dateObj = new Date(bookingState.tanggal);
            const dayOfWeek = dateObj.getDay();
            const isWeekend = (dayOfWeek === 5 || dayOfWeek === 6);
            
            let harga = 0, paketNama = '';
            
            if (bookingState.paket === 'perjam') {
                harga = (isWeekend ? 55000 : 40000) * bookingState.durasi;
                paketNama = `Reguler ${bookingState.durasi} Jam`;
            } else if (bookingState.paket === 'promo_siang') {
                harga = 60000;
                paketNama = 'Promo Siang (3 Jam)';
            } else {
                harga = isWeekend ? 100000 : 80000;
                paketNama = 'Promo Malam (4 Jam)';
            }
            
            bookingState.hargaAwal = harga;
            
            if (bookingState.voucher) {
                bookingState.diskon = calculateDiscount(harga, bookingState.voucher);
                bookingState.hargaAkhir = harga - bookingState.diskon;
            } else {
                bookingState.diskon = 0;
                bookingState.hargaAkhir = harga;
            }
            
            document.getElementById('summary-paket').textContent = paketNama;
            document.getElementById('summary-waktu').textContent = bookingState.waktu;
            document.getElementById('summary-harga-awal').textContent = 'Rp ' + Math.round(harga).toLocaleString('id-ID');
            
            if (bookingState.diskon > 0) {
                document.getElementById('diskon-row').style.display = 'flex';
                document.getElementById('summary-diskon').textContent = '- Rp ' + Math.round(bookingState.diskon).toLocaleString('id-ID');
            } else {
                document.getElementById('diskon-row').style.display = 'none';
            }
            
            document.getElementById('summary-harga').textContent = 'Rp ' + Math.round(bookingState.hargaAkhir).toLocaleString('id-ID');
            document.getElementById('ringkasan-pesanan').style.display = 'block';
        }

        function applyVoucher(kode, nama, tipe, nilai, minTransaksi, maxDiskon) {
            if (!bookingState.waktu || !bookingState.tanggal) {
                alert('⚠️ Pilih tanggal, paket, dan waktu terlebih dahulu!');
                return;
            }
            
            if (bookingState.hargaAwal < minTransaksi) {
                alert(`⚠️ Minimal transaksi Rp ${minTransaksi.toLocaleString('id-ID')}`);
                return;
            }
            
            bookingState.voucher = {kode, nama, tipe, nilai, maxDiskon};
            document.getElementById('hidden-voucher').value = kode;
            document.getElementById('voucher-applied').style.display = 'flex';
            document.getElementById('voucher-applied-text').textContent = `Voucher ${kode} diterapkan!`;
            
            document.querySelectorAll('.voucher-card').forEach(card => {
                card.style.opacity = '0.5';
                card.style.pointerEvents = 'none';
            });
            event.target.closest('.voucher-card').style.opacity = '1';
            event.target.closest('.voucher-card').classList.add('active');
            
            updateSummary();
        }

        function removeVoucher() {
            bookingState.voucher = null;
            bookingState.diskon = 0;
            document.getElementById('hidden-voucher').value = '';
            document.getElementById('voucher-applied').style.display = 'none';
            
            document.querySelectorAll('.voucher-card').forEach(card => {
                card.style.opacity = '1';
                card.style.pointerEvents = 'auto';
                card.classList.remove('active');
            });
            
            updateSummary();
        }

        function calculateDiscount(harga, voucher) {
            let diskon = 0;
            if (voucher.tipe === 'persen') {
                diskon = (harga * voucher.nilai) / 100;
                if (voucher.maxDiskon && diskon > voucher.maxDiskon) diskon = voucher.maxDiskon;
            } else {
                diskon = voucher.nilai;
            }
            return diskon;
        }

        function tambahKeKeranjang() {
            if (!bookingState.tanggal || !bookingState.waktu) {
                alert('⚠️ Lengkapi semua data booking!');
                return;
            }
            
            const formData = new FormData(document.getElementById('form-pemesanan-detail'));
            
            fetch('add_to_cart.php', {method: 'POST', body: formData})
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('✅ ' + data.message);
                        setTimeout(() => window.location.href = 'cart.php', 1000);
                    } else {
                        alert('❌ ' + data.message);
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('❌ Terjadi kesalahan');
                });
        }

        function bookingLangsung() {
    if (!bookingState.tanggal || !bookingState.waktu) {
        alert('⚠️ Lengkapi semua data booking!\n\nPastikan Anda sudah memilih:\n- Tanggal booking\n- Paket\n- Waktu mulai');
        return;
    }
    
    const formData = new FormData(document.getElementById('form-pemesanan-detail'));
    
    // Show loading
    const btnCheckout = document.querySelector('.btn-checkout');
    const originalText = btnCheckout.innerHTML;
    btnCheckout.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
    btnCheckout.disabled = true;
    
    fetch('add_to_cart.php', {
        method: 'POST', 
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const totalFormatted = Math.round(data.total || bookingState.hargaAkhir).toLocaleString('id-ID');
            alert('✅ Booking berhasil ditambahkan!\n\n' + 
                  '📋 Total: Rp ' + totalFormatted + '\n' +
                  (data.diskon > 0 ? '🎉 Hemat: Rp ' + Math.round(data.diskon).toLocaleString('id-ID') + '\n' : '') +
                  '\n⏳ Mengarahkan ke halaman pembayaran...');
            
            // Redirect to checkout
            setTimeout(() => {
                window.location.href = 'checkout.php';
            }, 1000);
        } else {
            alert('❌ ' + data.message);
            btnCheckout.innerHTML = originalText;
            btnCheckout.disabled = false;
        }
    })
    .catch(err => {
        console.error('Error:', err);
        alert('❌ Terjadi kesalahan koneksi!\n\nSilakan coba lagi atau hubungi admin.');
        btnCheckout.innerHTML = originalText;
        btnCheckout.disabled = false;
    });
}

        document.getElementById('hidden-paket').value = 'perjam';
        document.getElementById('hidden-durasi').value = '2';
    </script>
</body>
</html>
