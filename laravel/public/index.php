<?php
require_once 'config.php';

// Get latest promos
$promo_query = "SELECT * FROM promo WHERE aktif = TRUE ORDER BY created_at DESC LIMIT 3";
$promo_result = $conn->query($promo_query);
$promos = [];
if ($promo_result) {
    while ($row = $promo_result->fetch_assoc()) {
        $promos[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Legacy Billiard - Premium Billiard Club</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* =============== FORCE DARK THEME =============== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: #0a0a0a !important;
            color: #fff;
            overflow-x: hidden;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        section {
            background: #0a0a0a !important;
        }
        
        .promo-section {
            background: #0a0a0a !important;
            padding: 5rem 5%;
        }
        
        .features-section {
            background: #1a1a1a !important;
        }
        
        .stats-section {
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 100%) !important;
        }
        
        .tables-preview {
            background: #0a0a0a !important;
        }
        
        .gallery-preview {
            background: #1a1a1a !important;
        }
        
        .testimonial-section {
            background: #0a0a0a !important;
        }
        
        .why-choose-section {
            background: #0a0a0a !important;
        }
        
        .cta-section {
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 50%, #0a0a0a 100%) !important;
        }
        /* =============================================== */

        /* ==================== HERO BACKGROUND IMAGE FIX ==================== */
.homepage-hero {
    position: relative;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), 
                url('images/bgsemua.jpg') center center/cover no-repeat fixed !important;
    overflow: hidden;
    margin-top: 0 !important;
    padding: 0 !important;
}

.homepage-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: radial-gradient(circle at center, transparent 0%, rgba(0, 0, 0, 0.5) 100%);
    z-index: 1;
}

.hero-content {
    position: relative;
    z-index: 5;
    text-align: center;
}

        /* Additional styles for homepage */
        .hero-particles {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }

        .particle {
            position: absolute;
            background: var(--gold);
            border-radius: 50%;
            animation: float 15s infinite;
            opacity: 0.1;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0) translateX(0);
            }
            25% {
                transform: translateY(-100px) translateX(50px);
            }
            50% {
                transform: translateY(-50px) translateX(-50px);
            }
            75% {
                transform: translateY(-150px) translateX(30px);
            }
        }

        .features-section {
            padding: 5rem 5%;
            position: relative;
            overflow: hidden;
        }

        .feature-card {
            background: var(--tertiary-dark);
            border: 2px solid transparent;
            border-radius: 20px;
            padding: 3rem 2rem;
            text-align: center;
            transition: all 0.5s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, var(--gold), transparent);
            transform: rotate(45deg);
            transition: all 0.5s ease;
            opacity: 0;
        }

        .feature-card:hover::before {
            opacity: 0.1;
            animation: shine 1.5s infinite;
        }

        @keyframes shine {
            0% {
                transform: translateX(-100%) translateY(-100%) rotate(45deg);
            }
            100% {
                transform: translateX(100%) translateY(100%) rotate(45deg);
            }
        }

        .feature-card:hover {
            border-color: var(--gold);
            transform: translateY(-20px) scale(1.05);
            box-shadow: 0 20px 60px rgba(212, 175, 55, 0.4);
        }

        .feature-icon {
            font-size: 4rem;
            color: var(--gold);
            margin-bottom: 1.5rem;
            transition: all 0.5s ease;
        }

        .feature-card:hover .feature-icon {
            transform: scale(1.2) rotate(360deg);
        }

        .stats-section {
            padding: 5rem 5%;
            position: relative;
        }

        .stat-item {
            text-align: center;
            padding: 2rem;
            position: relative;
        }

        .stat-number {
            font-size: 4rem;
            font-weight: 800;
            color: var(--gold);
            text-shadow: 0 0 30px rgba(212, 175, 55, 0.5);
            display: block;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 1.2rem;
            color: var(--text-gray);
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .tables-preview {
            padding: 5rem 5%;
        }

        .table-card {
            background: var(--secondary-dark);
            border: 2px solid var(--tertiary-dark);
            border-radius: 20px;
            padding: 2rem;
            text-align: center;
            transition: all 0.4s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .table-card::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: radial-gradient(circle, rgba(212, 175, 55, 0.1) 0%, transparent 70%);
            transform: translate(-50%, -50%);
            transition: all 0.5s ease;
        }

        .table-card:hover::after {
            width: 300%;
            height: 300%;
        }

        .table-card:hover {
            border-color: var(--gold);
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(212, 175, 55, 0.3);
        }

        .table-icon {
            font-size: 3rem;
            color: var(--gold);
            margin-bottom: 1rem;
        }

        .testimonial-section {
            padding: 5rem 5%;
            position: relative;
        }

        .testimonial-card {
            background: var(--tertiary-dark);
            border-radius: 20px;
            padding: 2.5rem;
            border: 2px solid transparent;
            transition: all 0.4s ease;
            height: 100%;
        }

        .testimonial-card:hover {
            border-color: var(--gold);
            transform: scale(1.05);
        }

        .testimonial-text {
            color: var(--text-light);
            font-style: italic;
            margin-bottom: 1.5rem;
            line-height: 1.8;
        }

        .testimonial-author {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .author-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: 3px solid var(--gold);
        }

        .author-info h5 {
            color: var(--gold);
            margin: 0;
        }

        .author-info p {
            color: var(--text-gray);
            margin: 0;
            font-size: 0.9rem;
        }

        .cta-section {
            padding: 6rem 5%;
            position: relative;
            overflow: hidden;
        }

        .cta-section::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(212, 175, 55, 0.1) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
        }

        @keyframes rotate {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }

        .cta-content {
            position: relative;
            z-index: 2;
            text-align: center;
        }

        .cta-title {
            font-size: 3.5rem;
            font-weight: 800;
            color: var(--gold);
            margin-bottom: 1.5rem;
            text-shadow: 0 0 30px rgba(212, 175, 55, 0.5);
        }

        .cta-buttons {
            display: flex;
            gap: 2rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 3rem;
        }

        .btn-cta-primary {
            background: linear-gradient(135deg, var(--gold), var(--gold-dark));
            color: var(--primary-dark);
            padding: 1.5rem 3rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 700;
            font-size: 1.2rem;
            transition: all 0.4s ease;
            box-shadow: 0 10px 30px rgba(212, 175, 55, 0.3);
            display: inline-flex;
            align-items: center;
            gap: 1rem;
        }

        .btn-cta-primary:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 50px rgba(212, 175, 55, 0.5);
            background: linear-gradient(135deg, var(--gold-light), var(--gold));
        }

        .btn-cta-secondary {
            background: transparent;
            color: var(--gold);
            padding: 1.5rem 3rem;
            border: 3px solid var(--gold);
            border-radius: 50px;
            text-decoration: none;
            font-weight: 700;
            font-size: 1.2rem;
            transition: all 0.4s ease;
            display: inline-flex;
            align-items: center;
            gap: 1rem;
        }

        .btn-cta-secondary:hover {
            background: var(--gold);
            color: var(--primary-dark);
            transform: translateY(-5px);
        }

        .promo-carousel {
            padding: 5rem 5%;
        }

        .promo-slide {
            background: var(--secondary-dark);
            border-radius: 20px;
            overflow: hidden;
            border: 2px solid var(--tertiary-dark);
            transition: all 0.4s ease;
        }

        .promo-slide:hover {
            border-color: var(--gold);
            transform: scale(1.02);
        }

        .promo-image-container {
            height: 250px;
            overflow: hidden;
            position: relative;
        }

        .promo-image-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: all 0.5s ease;
        }

        .promo-slide:hover .promo-image-container img {
            transform: scale(1.1);
        }

        .promo-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            background: var(--gold);
            color: var(--primary-dark);
            padding: 0.5rem 1.5rem;
            border-radius: 20px;
            font-weight: 700;
            font-size: 0.9rem;
        }

        .promo-content {
            padding: 2rem;
        }

        .scroll-indicator {
            position: absolute;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 10;
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateX(-50%) translateY(0);
            }
            40% {
                transform: translateX(-50%) translateY(-20px);
            }
            60% {
                transform: translateX(-50%) translateY(-10px);
            }
        }

        .scroll-indicator i {
            font-size: 2rem;
            color: var(--gold);
        }

        .gallery-preview {
            padding: 5rem 5%;
        }

        .gallery-grid-home {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-top: 3rem;
        }

        .gallery-item-home {
            position: relative;
            height: 300px;
            border-radius: 15px;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.4s ease;
        }

        .gallery-item-home img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: all 0.5s ease;
        }

        .gallery-item-home:hover img {
            transform: scale(1.2) rotate(5deg);
        }

        .gallery-overlay-home {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, transparent 100%);
            display: flex;
            align-items: flex-end;
            padding: 1.5rem;
            opacity: 0;
            transition: all 0.4s ease;
        }

        .gallery-item-home:hover .gallery-overlay-home {
            opacity: 1;
        }

        .why-choose-section {
            padding: 5rem 5%;
        }

        .why-card {
            background: var(--secondary-dark);
            border-radius: 20px;
            padding: 2.5rem;
            border: 2px solid transparent;
            transition: all 0.4s ease;
            text-align: center;
            height: 100%;
        }

        .why-card:hover {
            border-color: var(--gold);
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(212, 175, 55, 0.3);
        }

        .why-icon {
            font-size: 3.5rem;
            color: var(--gold);
            margin-bottom: 1.5rem;
            transition: all 0.4s ease;
        }

        .why-card:hover .why-icon {
            transform: scale(1.2);
        }

        .promo-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }

        .promo-card {
            background: linear-gradient(135deg, #1a1a1a 0%, #0f0f0f 100%) !important;
            border: 2px solid #D4AF37;
            border-radius: 20px;
            padding: 2rem;
            text-align: center;
            transition: all 0.4s ease;
            cursor: pointer;
        }

        .promo-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 50px rgba(212, 175, 55, 0.4);
            border-color: #F5D068;
        }

        .promo-icon {
            font-size: 3rem;
            color: #D4AF37;
            margin-bottom: 1rem;
        }

        .promo-card h3 {
            color: #D4AF37;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .promo-card p {
            color: #fff;
            margin-bottom: 1.5rem;
        }

        .promo-code-box {
            background: #0f0f0f;
            border: 2px dashed #D4AF37;
            border-radius: 10px;
            padding: 1rem;
            margin: 1rem 0;
        }

        .promo-code {
            font-size: 1.5rem;
            font-weight: 800;
            color: #D4AF37;
            letter-spacing: 2px;
        }

        .promo-price-box {
            margin: 1rem 0;
        }

        .price {
            font-size: 2rem;
            font-weight: 800;
            color: #4CAF50;
        }

        .duration {
            color: #999;
            font-size: 0.9rem;
            display: block;
            margin-top: 0.5rem;
        }

        .promo-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .btn-book-promo,
        .btn-detail {
            flex: 1;
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-book-promo {
            background: linear-gradient(135deg, #D4AF37, #C5A028);
            color: #0a0a0a;
        }

        .btn-book-promo:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(212, 175, 55, 0.6);
        }

        .btn-detail {
            background: transparent;
            border: 2px solid #D4AF37;
            color: #D4AF37;
        }

        .btn-detail:hover {
            background: #D4AF37;
            color: #0a0a0a;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <!-- Hero Section with Particles -->
    <section class="homepage-hero">
        <div class="hero-particles" id="particles"></div>
        <div class="hero-content animate__animated animate__fadeInUp">
            <h1 class="animate__animated animate__fadeInDown">THE GAME FOR <br>EVERYONE</h1>
            <p class="subtitle animate__animated animate__fadeInUp animate__delay-1s">PLAY HARD, PLAY SMART, PLAY BILLIARDS</p>
            <div class="cta-buttons animate__animated animate__fadeInUp animate__delay-2s">
                <a href="booking.php" class="btn-cta-primary">
                    <i class="fas fa-calendar-check"></i> BOOK NOW
                </a>
                <a href="#features" class="btn-cta-secondary">
                    <i class="fas fa-arrow-down"></i> EXPLORE
                </a>
            </div>
        </div>
        <div class="scroll-indicator">
            <i class="fas fa-chevron-down"></i>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row">
                <div class="col-md-3 col-6 mb-4">
                    <div class="stat-item animate__animated animate__fadeInUp" data-wow-delay="0.1s">
                        <span class="stat-number" data-target="5000">0</span>
                        <span class="stat-label">Happy Players</span>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-4">
                    <div class="stat-item animate__animated animate__fadeInUp" data-wow-delay="0.2s">
                        <span class="stat-number" data-target="40">0</span>
                        <span class="stat-label">Premium Tables</span>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-4">
                    <div class="stat-item animate__animated animate__fadeInUp" data-wow-delay="0.3s">
                        <span class="stat-number" data-target="500">0</span>
                        <span class="stat-label">Tournaments</span>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-4">
                    <div class="stat-item animate__animated animate__fadeInUp" data-wow-delay="0.4s">
                        <span class="stat-number" data-target="24">0</span>
                        <span class="stat-label">Years Experience</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section" id="features">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title animate__animated animate__fadeInDown">OUR FEATURES</h2>
                <p class="section-subtitle animate__animated animate__fadeInUp">Experience The Best Billiard Club</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4 animate__animated animate__fadeInLeft" data-wow-delay="0.1s">
                    <div class="feature-card">
                        <i class="fas fa-dice-d6 feature-icon"></i>
                        <h3 style="color: var(--gold); font-weight: 700;">International Standard</h3>
                        <p style="color: var(--text-light); line-height: 1.8;">Premium billiard tables dengan standar internasional untuk pengalaman bermain terbaik</p>
                    </div>
                </div>
                <div class="col-md-4 animate__animated animate__fadeInUp" data-wow-delay="0.2s">
                    <div class="feature-card">
                        <i class="fas fa-clock feature-icon"></i>
                        <h3 style="color: var(--gold); font-weight: 700;">24/7 Booking</h3>
                        <p style="color: var(--text-light); line-height: 1.8;">Sistem booking online 24 jam untuk kemudahan reservasi kapan saja</p>
                    </div>
                </div>
                <div class="col-md-4 animate__animated animate__fadeInRight" data-wow-delay="0.3s">
                    <div class="feature-card">
                        <i class="fas fa-utensils feature-icon"></i>
                        <h3 style="color: var(--gold); font-weight: 700;">Premium F&B</h3>
                        <p style="color: var(--text-light); line-height: 1.8;">Makanan dan minuman berkualitas untuk menemani permainan Anda</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Choose Us Section -->
    <section class="why-choose-section">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title animate__animated animate__fadeInDown">WHY CHOOSE US</h2>
                <p class="section-subtitle animate__animated animate__fadeInUp">What Makes Us Different</p>
            </div>
            <div class="row g-4">
                <div class="col-md-3 col-6">
                    <div class="why-card">
                        <i class="fas fa-award why-icon"></i>
                        <h4 style="color: var(--gold); margin-bottom: 1rem;">Professional Service</h4>
                        <p style="color: var(--text-light);">Staff terlatih dan profesional siap melayani Anda</p>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="why-card">
                        <i class="fas fa-shield-alt why-icon"></i>
                        <h4 style="color: var(--gold); margin-bottom: 1rem;">Safe & Clean</h4>
                        <p style="color: var(--text-light);">Lingkungan bersih dan aman untuk kenyamanan Anda</p>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="why-card">
                        <i class="fas fa-wifi why-icon"></i>
                        <h4 style="color: var(--gold); margin-bottom: 1rem;">Free Wi-Fi</h4>
                        <p style="color: var(--text-light);">Internet cepat gratis untuk semua pengunjung</p>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="why-card">
                        <i class="fas fa-parking why-icon"></i>
                        <h4 style="color: var(--gold); margin-bottom: 1rem;">Free Parking</h4>
                        <p style="color: var(--text-light);">Area parkir luas dan aman tersedia gratis</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Tables Preview Section -->
    <section class="tables-preview">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title animate__animated animate__fadeInDown">OUR TABLES</h2>
                <p class="section-subtitle animate__animated animate__fadeInUp">Choose Your Perfect Table</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="table-card">
                        <i class="fas fa-dice-d6 table-icon"></i>
                        <h3 style="color: var(--gold); font-weight: 700; margin-bottom: 1rem;">Standard Tables</h3>
                        <p style="color: var(--text-light); margin-bottom: 1.5rem;">Perfect untuk pemain casual dan latihan</p>
                        <div style="font-size: 2rem; color: var(--gold); font-weight: 700; margin-bottom: 1rem;">Rp 40K/jam</div>
                        <a href="booking.php" class="btn-promo">Book Now</a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="table-card">
                        <i class="fas fa-star table-icon"></i>
                        <h3 style="color: var(--gold); font-weight: 700; margin-bottom: 1rem;">VIP Tables</h3>
                        <p style="color: var(--text-light); margin-bottom: 1.5rem;">Premium experience dengan area private</p>
                        <div style="font-size: 2rem; color: var(--gold); font-weight: 700; margin-bottom: 1rem;">Rp 75K/jam</div>
                        <a href="booking.php" class="btn-promo">Book Now</a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="table-card">
                        <i class="fas fa-trophy table-icon"></i>
                        <h3 style="color: var(--gold); font-weight: 700; margin-bottom: 1rem;">Tournament Tables</h3>
                        <p style="color: var(--text-light); margin-bottom: 1.5rem;">Standar internasional untuk kompetisi</p>
                        <div style="font-size: 2rem; color: var(--gold); font-weight: 700; margin-bottom: 1rem;">Rp 100K/jam</div>
                        <a href="booking.php" class="btn-promo">Book Now</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Promo Section -->
    <?php if (count($promos) > 0): ?>
    <section class="promo-section">
        <div class="container">
            <div class="section-header">
                <h2>Don't Miss Our Amazing Deals</h2>
                <p>Penawaran terbaik untuk pengalaman billiard yang tak terlupakan</p>
            </div>
            <div class="promo-grid">
                <?php if (!empty($promos)): ?>
                    <?php foreach ($promos as $promo): ?>
                        <div class="promo-card">
                            <div class="promo-icon">
                                <?php if ($promo['jenis_promo'] == 'promo_siang'): ?>
                                    <i class="fas fa-sun"></i>
                                <?php elseif ($promo['jenis_promo'] == 'promo_malam'): ?>
                                    <i class="fas fa-moon"></i>
                                <?php else: ?>
                                    <i class="fas fa-star"></i>
                                <?php endif; ?>
                            </div>
                            <h3><?= htmlspecialchars($promo['judul']) ?></h3>
                            <p><?= htmlspecialchars($promo['deskripsi']) ?></p>
                            
                            <?php if ($promo['kode_promo']): ?>
                                <div class="promo-code-box">
                                    <span class="promo-code"><?= htmlspecialchars($promo['kode_promo']) ?></span>
                                </div>
                            <?php elseif ($promo['harga']): ?>
                                <div class="promo-price-box">
                                    <span class="price">Rp <?= number_format($promo['harga'], 0, ',', '.') ?></span>
                                    <?php if ($promo['durasi_jam']): ?>
                                        <span class="duration"><?= $promo['durasi_jam'] ?> Jam</span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="promo-actions">
                                <?php if ($promo['jenis_promo'] == 'promo_siang' || $promo['jenis_promo'] == 'promo_malam'): ?>
                                    <button class="btn-book-promo" data-promo-type="<?= $promo['jenis_promo'] ?>">
                                        <i class="fas fa-bolt"></i> Booking Langsung
                                    </button>
                                <?php endif; ?>
                                <a href="promo.php" class="btn-detail">
                                    <i class="fas fa-info-circle"></i> Detail
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Gallery Preview Section -->
    <section class="gallery-preview">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title animate__animated animate__fadeInDown">GALLERY</h2>
                <p class="section-subtitle animate__animated animate__fadeInUp">Take A Look At Our Venue</p>
            </div>
            <div class="gallery-grid-home">
                <div class="gallery-item-home animate__animated animate__fadeIn">
                    <img src="images/gallery1.jpg" alt="Gallery 1">
                    <div class="gallery-overlay-home">
                        <h5 style="color: white; margin: 0;">Interior View</h5>
                    </div>
                </div>
                <div class="gallery-item-home animate__animated animate__fadeIn" data-wow-delay="0.1s">
                    <img src="images/gallery2.jpg" alt="Gallery 2">
                    <div class="gallery-overlay-home">
                        <h5 style="color: white; margin: 0;">Premium Tables</h5>
                    </div>
                </div>
                <div class="gallery-item-home animate__animated animate__fadeIn" data-wow-delay="0.2s">
                    <img src="images/gallery3.jpg" alt="Gallery 3">
                    <div class="gallery-overlay-home">
                        <h5 style="color: white; margin: 0;">Lounge Area</h5>
                    </div>
                </div>
                <div class="gallery-item-home animate__animated animate__fadeIn" data-wow-delay="0.3s">
                    <img src="images/gallery4.jpg" alt="Gallery 4">
                    <div class="gallery-overlay-home">
                        <h5 style="color: white; margin: 0;">Night View</h5>
                    </div>
                </div>
            </div>
            <div class="text-center mt-5">
                <a href="gallery.php" class="btn-view">VIEW FULL GALLERY</a>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonial-section">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title animate__animated animate__fadeInDown">WHAT PLAYERS SAY</h2>
                <p class="section-subtitle animate__animated animate__fadeInUp">Reviews From Our Customers</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="testimonial-card">
                        <div class="testimonial-text">
                            "Tempat billiard terbaik di BSD! Mejanya berkualitas dan suasananya sangat nyaman. Highly recommended!"
                        </div>
                        <div class="testimonial-author">
                            <img src="https://ui-avatars.com/api/?name=Ahmad+Fauzi&background=d4af37&color=fff" alt="Ahmad Fauzi" class="author-avatar">
                            <div class="author-info">
                                <h5>Ahmad Fauzi</h5>
                                <p>Regular Player</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="testimonial-card">
                        <div class="testimonial-text">
                            "Sistem booking online-nya sangat memudahkan. Tidak perlu antri lagi untuk main. Pelayanannya juga ramah!"
                        </div>
                        <div class="testimonial-author">
                            <img src="https://ui-avatars.com/api/?name=Siti+Nurhaliza&background=d4af37&color=fff" alt="Siti Nurhaliza" class="author-avatar">
                            <div class="author-info">
                                <h5>Siti Nurhaliza</h5>
                                <p>Happy Customer</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="testimonial-card">
                        <div class="testimonial-text">
                            "Harga terjangkau dengan fasilitas premium. Promo happy hour-nya sangat worth it. Pasti balik lagi!"
                        </div>
                        <div class="testimonial-author">
                            <img src="https://ui-avatars.com/api/?name=Budi+Santoso&background=d4af37&color=fff" alt="Budi Santoso" class="author-avatar">
                            <div class="author-info">
                                <h5>Budi Santoso</h5>
                                <p>Tournament Player</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="cta-content">
            <h2 class="cta-title animate__animated animate__pulse animate__infinite">READY TO PLAY?</h2>
            <p style="color: var(--text-gray); font-size: 1.3rem; margin-bottom: 0;">Book your table now and experience the best billiard club in town!</p>
            <div class="cta-buttons">
                <a href="booking.php" class="btn-cta-primary">
                    <i class="fas fa-calendar-check"></i> BOOK YOUR TABLE
                </a>
                <a href="aboutus.php" class="btn-cta-secondary">
                    <i class="fas fa-info-circle"></i> LEARN MORE
                </a>
            </div>
        </div>
    </section>

    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/main.js"></script>
    <script>
        // Particle Animation
        function createParticles() {
            const particlesContainer = document.getElementById('particles');
            const particleCount = 50;
            
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.width = Math.random() * 5 + 2 + 'px';
                particle.style.height = particle.style.width;
                particle.style.left = Math.random() * 100 + '%';
                particle.style.top = Math.random() * 100 + '%';
                particle.style.animationDelay = Math.random() * 15 + 's';
                particle.style.animationDuration = Math.random() * 10 + 10 + 's';
                particlesContainer.appendChild(particle);
            }
        }

        // Counter Animation
        function animateCounters() {
            const counters = document.querySelectorAll('.stat-number');
            counters.forEach(counter => {
                const target = parseInt(counter.getAttribute('data-target'));
                const duration = 2000;
                const step = target / (duration / 16);
                let current = 0;
                
                const timer = setInterval(() => {
                    current += step;
                    if (current >= target) {
                        counter.textContent = target.toLocaleString();
                        clearInterval(timer);
                    } else {
                        counter.textContent = Math.floor(current).toLocaleString();
                    }
                }, 16);
            });
        }

        // Intersection Observer for animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -100px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate__animated');
                    if (entry.target.classList.contains('stat-item')) {
                        animateCounters();
                    }
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        // Observe all animated elements
        document.addEventListener('DOMContentLoaded', function() {
            createParticles();
            
            const animatedElements = document.querySelectorAll('.animate__animated');
            animatedElements.forEach(el => observer.observe(el));
        });

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Parallax effect on scroll
        window.addEventListener('scroll', function() {
            const scrolled = window.pageYOffset;
            const heroContent = document.querySelector('.hero-content');
            if (heroContent) {
                heroContent.style.transform = `translateY(${scrolled * 0.5}px)`;
                heroContent.style.opacity = 1 - (scrolled / 700);
            }
        });

        // Add hover sound effect (optional)
        const cards = document.querySelectorAll('.feature-card, .table-card, .why-card');
        cards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-20px) scale(1.05)';
            });
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });

        // Loading animation
        window.addEventListener('load', function() {
            document.body.style.opacity = '0';
            setTimeout(() => {
                document.body.style.transition = 'opacity 0.5s ease';
                document.body.style.opacity = '1';
            }, 100);
        });
    </script>
</body>
</html>
