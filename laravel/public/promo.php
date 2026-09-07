<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get active vouchers from database
$promo_query = "SELECT * FROM voucher WHERE aktif = 1 AND tanggal_mulai <= CURDATE() AND tanggal_selesai >= CURDATE() ORDER BY nilai_diskon DESC";
$promo_result = $conn->query($promo_query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Promo - Legacy Billiard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: #0a0a0a !important;
            color: #fff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
        }
        
        /* NAVBAR FIX */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem 5%;
            background: linear-gradient(to bottom, #1a1a1a, transparent);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(212, 175, 55, 0.1);
        }
        
        .logo {
            font-size: 1.8rem;
            font-weight: bold;
            color: #D4AF37;
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
            color: #e0e0e0;
            text-decoration: none;
            font-size: 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
            padding: 0.5rem 0;
        }
        
        .nav-links a:hover,
        .nav-links a.active {
            color: #D4AF37;
        }
        
        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 2px;
            background: #D4AF37;
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
            color: #D4AF37;
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
            background: linear-gradient(135deg, #D4AF37, #C5A028);
            color: #0a0a0a;
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
        
        /* PROMO PAGE */
        .promo-page {
            min-height: 100vh;
            padding: 120px 5% 50px;
            background: #0a0a0a;
        }
        
        .promo-title {
            color: #D4AF37;
            font-size: 3rem;
            font-weight: 800;
            text-align: center;
            margin-bottom: 1rem;
            text-shadow: 0 0 30px rgba(212, 175, 55, 0.5);
            animation: glow 2s ease-in-out infinite;
        }
        
        @keyframes glow {
            0%, 100% { text-shadow: 0 0 20px rgba(212, 175, 55, 0.5); }
            50% { text-shadow: 0 0 40px rgba(212, 175, 55, 0.8); }
        }
        
        .promo-subtitle {
            color: #999;
            font-size: 1.2rem;
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .promo-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .promo-card {
            background: linear-gradient(135deg, #1a1a1a 0%, #0f0f0f 100%);
            border: 2px solid #D4AF37;
            border-radius: 20px;
            padding: 0;
            overflow: hidden;
            transition: all 0.4s ease;
            cursor: pointer;
            position: relative;
        }
        
        .promo-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(212, 175, 55, 0.1) 0%, transparent 70%);
            transform: scale(0);
            transition: transform 0.5s ease;
            z-index: 0;
        }
        
        .promo-card:hover::before {
            transform: scale(1);
        }
        
        .promo-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 50px rgba(212, 175, 55, 0.4);
            border-color: #F5D068;
        }
        
        .promo-header {
            background: linear-gradient(135deg, #C5A028, #D4AF37);
            padding: 2rem;
            text-align: center;
            position: relative;
            z-index: 1;
        }
        
        .promo-icon {
            font-size: 3rem;
            color: #0a0a0a;
            margin-bottom: 1rem;
            animation: bounce 2s infinite;
        }
        
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        
        .promo-code {
            font-size: 1.8rem;
            font-weight: 800;
            color: #0a0a0a;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            letter-spacing: 2px;
        }
        
        .promo-body {
            padding: 2rem;
            position: relative;
            z-index: 1;
            background: #1a1a1a;
        }
        
        .promo-name {
            color: #D4AF37;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .promo-discount {
            font-size: 2.5rem;
            font-weight: 800;
            color: #4CAF50;
            margin: 1rem 0;
            text-shadow: 0 0 20px rgba(76, 175, 80, 0.3);
        }
        
        .promo-details {
            color: #fff;
            margin: 0.5rem 0;
            font-size: 0.95rem;
        }
        
        .promo-details i {
            color: #D4AF37;
            margin-right: 0.5rem;
            width: 20px;
        }
        
        .promo-validity {
            background: #0f0f0f;
            border-radius: 10px;
            padding: 0.8rem;
            margin-top: 1rem;
            text-align: center;
            color: #999;
            font-size: 0.9rem;
            border: 1px solid #333;
        }
        
        .promo-validity i {
            color: #D4AF37;
        }
        
        .btn-use-promo {
            width: 100%;
            background: linear-gradient(135deg, #C5A028, #D4AF37);
            color: #0a0a0a;
            padding: 1rem;
            border: none;
            border-radius: 10px;
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
        
        .btn-use-promo:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(212, 175, 55, 0.6);
            background: linear-gradient(135deg, #D4AF37, #F5D068);
        }
        
        .empty-promo {
            text-align: center;
            padding: 4rem 2rem;
            color: #999;
            grid-column: 1 / -1;
            background: #1a1a1a;
            border-radius: 20px;
            border: 2px solid #333;
        }
        
        .empty-promo i {
            font-size: 5rem;
            color: #D4AF37;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        .empty-promo h3 {
            font-size: 2rem;
            color: #D4AF37;
            margin-bottom: 1rem;
        }
        
        @media (max-width: 768px) {
            .promo-grid {
                grid-template-columns: 1fr;
            }
            
            .promo-title {
                font-size: 2rem;
            }
            
            .nav-links {
                display: none;
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
            <li><a href="booking.php">Pricing & Booking</a></li>
            <li><a href="promo.php" class="active">Promo</a></li>
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

    <!-- MAIN CONTENT -->
    <div class="promo-page">
        <h1 class="promo-title">
            <i class="fas fa-gift"></i> PROMO SPESIAL
        </h1>
        <p class="promo-subtitle">
            Nikmati berbagai promo menarik dari Legacy Billiard
        </p>

        <div class="promo-grid">
            <?php if ($promo_result->num_rows > 0): ?>
                <?php while ($promo = $promo_result->fetch_assoc()): ?>
                    <div class="promo-card" onclick="copyPromoCode('<?= $promo['kode'] ?>')">
                        <div class="promo-header">
                            <div class="promo-icon">
                                <i class="fas fa-ticket-alt"></i>
                            </div>
                            <div class="promo-code"><?= htmlspecialchars($promo['kode']) ?></div>
                        </div>

                        <div class="promo-body">
                            <div class="promo-name"><?= htmlspecialchars($promo['nama']) ?></div>
                            
                            <div class="promo-discount">
                                <?php if ($promo['tipe_diskon'] === 'persen'): ?>
                                    <i class="fas fa-percentage"></i> <?= $promo['nilai_diskon'] ?>% OFF
                                <?php else: ?>
                                    Rp <?= number_format($promo['nilai_diskon'], 0, ',', '.') ?> OFF
                                <?php endif; ?>
                            </div>

                            <?php if ($promo['min_transaksi'] > 0): ?>
                                <div class="promo-details">
                                    <i class="fas fa-shopping-bag"></i>
                                    Min. pembelian: <strong>Rp <?= number_format($promo['min_transaksi'], 0, ',', '.') ?></strong>
                                </div>
                            <?php endif; ?>

                            <?php if ($promo['max_diskon'] && $promo['tipe_diskon'] === 'persen'): ?>
                                <div class="promo-details">
                                    <i class="fas fa-tag"></i>
                                    Maks. diskon: <strong>Rp <?= number_format($promo['max_diskon'], 0, ',', '.') ?></strong>
                                </div>
                            <?php endif; ?>

                            <?php if ($promo['kuota']): ?>
                                <div class="promo-details">
                                    <i class="fas fa-users"></i>
                                    Kuota: <strong><?= $promo['kuota'] - $promo['terpakai'] ?> tersisa</strong>
                                </div>
                            <?php endif; ?>

                            <div class="promo-validity">
                                <i class="fas fa-calendar-alt"></i>
                                Berlaku: <?= date('d M Y', strtotime($promo['tanggal_mulai'])) ?> - <?= date('d M Y', strtotime($promo['tanggal_selesai'])) ?>
                            </div>

                            <button class="btn-use-promo" onclick="event.stopPropagation(); usePromo('<?= $promo['kode'] ?>')">
                                <i class="fas fa-copy"></i> Salin Kode & Gunakan
                            </button>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-promo">
                    <i class="fas fa-ticket-alt"></i>
                    <h3>Belum Ada Promo</h3>
                    <p>Pantau terus untuk penawaran terbaru!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
        // Load cart count
        fetch('get_cart_count.php')
            .then(res => res.json())
            .then(data => {
                document.getElementById('cart-count').textContent = data.count || 0;
            })
            .catch(err => console.error('Error:', err));

        // Copy promo code
        function copyPromoCode(code) {
            navigator.clipboard.writeText(code).then(() => {
                alert('✅ Kode promo "' + code + '" berhasil disalin!\n\nSilakan gunakan saat checkout.');
            }).catch(err => {
                console.error('Gagal menyalin:', err);
            });
        }

        // Use promo
        function usePromo(code) {
            navigator.clipboard.writeText(code).then(() => {
                alert('🎉 Kode promo "' + code + '" berhasil disalin!\n\n' +
                      '📋 Kode sudah ada di clipboard Anda.\n' +
                      '🛒 Silakan lanjut ke halaman booking atau checkout untuk menggunakan kode ini.');
                
                setTimeout(() => {
                    window.location.href = 'booking.php';
                }, 1500);
            }).catch(err => {
                console.error('Gagal menyalin:', err);
                alert('⚠️ Gagal menyalin kode. Silakan copy manual: ' + code);
            });
        }
    </script>
</body>
</html>
