<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Legacy Billiard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <?php include 'navbar.php'; ?>

    <section class="gallery-hero" style="background: url('images/bgsemua.jpg') center center / cover no-repeat;">
        <div class="gallery-title fade-in">
            <h4>SPORTS CLUB</h4>
            <h2>ABOUT US</h2>
        </div>
    </section>

    <section class="about">
        <div class="about-top">
            <div class="about-image slide-in-left">
                <img src="images/aboutus1.jpg" alt="About Legacy Billiard" />
            </div>
            <div class="about-text slide-in-right">
                <p class="label"><i class="fas fa-info-circle"></i> ABOUT CLUB</p>
                <h2>WELCOME TO<br>OUR CLUB</h2>
                <p>Legacy Billiard adalah venue billiard terkemuka yang berlokasi di BSD City, Tangerang, Indonesia. Kami berdedikasi untuk memberikan pengalaman luar biasa dengan meja billiard standar internasional, aksesori berkualitas tinggi, dan pilihan makanan dan minuman yang beragam.</p>
                <p>Baik Anda pemain berpengalaman atau pemula, venue kami menyediakan lingkungan yang ramah dan nyaman untuk semua tamu menikmati permainan billiard.</p>
                
                <div class="about-features mt-4">
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Meja Billiard Standar Internasional</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Aksesori Berkualitas Premium</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Food & Beverage Berkualitas</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Suasana Nyaman & Modern</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="about-stats">
            <div class="stat-item slide-in-left">
                <div class="stat-icon"><i class="fas fa-users"></i></div>
                <div class="stat-number" data-target="5000">0</div>
                <div class="stat-label">Happy Customers</div>
            </div>
            <div class="stat-item slide-in-left" style="animation-delay: 0.1s">
                <div class="stat-icon"><i class="fas fa-dice-d6"></i></div>
                <div class="stat-number" data-target="40">0</div>
                <div class="stat-label">Premium Tables</div>
            </div>
            <div class="stat-item slide-in-left" style="animation-delay: 0.2s">
                <div class="stat-icon"><i class="fas fa-trophy"></i></div>
                <div class="stat-number" data-target="50">0</div>
                <div class="stat-label">Tournaments Held</div>
            </div>
            <div class="stat-item slide-in-left" style="animation-delay: 0.3s">
                <div class="stat-icon"><i class="fas fa-clock"></i></div>
                <div class="stat-number" data-target="24">0</div>
                <div class="stat-label">Years Experience</div>
            </div>
        </div>

        <div class="about-bottom">
            <div class="contact">
                <h3 class="mb-4"><i class="fas fa-address-card"></i> Detail Contact</h3>

                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="contact-info-block slide-in-left">
                            <div class="contact-line">
                                <div class="contact-icon"><i class="fas fa-map-marker-alt"></i></div>
                                <div>
                                    <strong class="label">ADDRESS:</strong>
                                    <div class="value">
                                        Legacy Billiards BSD,<br>
                                        Jl. Lingkar Luar Botanika Utara,<br>
                                        Kavling Komersial Dalkamya at The Zora Blok C1 No. 20,<br>
                                        BSD City, Tangerang
                                    </div>
                                </div>
                            </div>
                            <div class="contact-line mt-3">
                                <div class="contact-icon"><i class="fas fa-envelope"></i></div>
                                <div>
                                    <strong class="label">EMAIL:</strong>
                                    <div class="value">info@legacybilliard.com</div>
                                </div>
                            </div>
                            <div class="contact-line mt-3">
                                <div class="contact-icon"><i class="fas fa-phone"></i></div>
                                <div>
                                    <strong class="label">PHONE:</strong>
                                    <div class="value">+62 811-848-988</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="opening-hours slide-in-right">
                            <h4><i class="fas fa-clock"></i> Opening Hours</h4>
                            <div class="hours-list">
                                <div class="contact-line">
                                    <div class="label day">Monday - Thursday</div>
                                    <div class="value time">11:00 AM - 12:00 PM</div>
                                </div>
                                <div class="contact-line">
                                    <div class="label day highlight">Friday - Saturday</div>
                                    <div class="value time">11:00 AM - 1:00 AM</div>
                                </div>
                                <div class="contact-line">
                                    <div class="label day">Sunday</div>
                                    <div class="value time">11:00 AM - 12:00 PM</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="map mt-5 slide-in-left">
                    <h4 class="mb-3"><i class="fas fa-map"></i> Location Map</h4>
                    <img src="images/aboutus2.jpg" alt="Location Map" />
                </div>
            </div>
        </div>
    </section>

    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Animate counters
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

    // Trigger animation when stats are in viewport
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateCounters();
                observer.disconnect();
            }
        });
    }, { threshold: 0.5 });

    const statsSection = document.querySelector('.about-stats');
    if (statsSection) {
        observer.observe(statsSection);
    }
    </script>

    <style>
    .about-features {
        display: grid;
        gap: 1rem;
    }

    .feature-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        color: var(--text-light);
    }

    .feature-item i {
        color: var(--gold);
        font-size: 1.2rem;
    }

    .about-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 2rem;
        padding: 4rem 5%;
        background: var(--secondary-dark);
        margin: 4rem 0;
    }

    .stat-item {
        text-align: center;
        padding: 2rem;
        background: var(--tertiary-dark);
        border-radius: 15px;
        border: 2px solid transparent;
        transition: all 0.3s ease;
        animation: fadeIn 0.8s ease-out;
    }

    .stat-item:hover {
        border-color: var(--gold);
        transform: translateY(-10px);
        box-shadow: var(--shadow-gold);
    }

    .stat-icon {
        font-size: 3rem;
        color: var(--gold);
        margin-bottom: 1rem;
    }

    .stat-number {
        font-size: 3rem;
        font-weight: 700;
        color: var(--gold);
        margin: 0.5rem 0;
    }

    .stat-label {
        color: var(--text-gray);
        font-size: 1.1rem;
    }

    .contact-icon {
        color: var(--gold);
        font-size: 1.5rem;
        margin-right: 1rem;
    }

    .contact-line {
        display: flex;
        align-items: flex-start;
        padding: 1rem 0;
    }

    .hours-list {
        background: var(--tertiary-dark);
        padding: 2rem;
        border-radius: 15px;
        border: 2px solid var(--tertiary-dark);
        transition: all 0.3s ease;
    }

    .hours-list:hover {
        border-color: var(--gold);
    }
    </style>
</body>
</html>