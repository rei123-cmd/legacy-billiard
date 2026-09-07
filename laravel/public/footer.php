<!-- ============================================
     FOOTER - ULTRA PREMIUM DESIGN
     Legacy Billiard Footer Section
     ============================================ -->

<footer class="footer-section">
    <!-- Main Footer -->
    <div class="footer-main">
        <div class="container">
            <div class="footer-grid">
                <!-- Company Info -->
                <div class="footer-column footer-about">
                    <div class="footer-logo">
                        <i class="fas fa-dice-d6"></i>
                        <h3>LEGACY BILLIARD</h3>
                    </div>
                    <p class="footer-description">
                        Experience the ultimate billiard gaming destination. We offer premium tables, 
                        professional tournaments, and an unmatched gaming atmosphere.
                    </p>
                    <div class="footer-social">
                        <a href="https://facebook.com/legacybilliard" target="_blank" class="social-link" aria-label="Facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://instagram.com/legacybilliard" target="_blank" class="social-link" aria-label="Instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="https://twitter.com/legacybilliard" target="_blank" class="social-link" aria-label="Twitter">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="https://wa.me/628118488988" target="_blank" class="social-link" aria-label="WhatsApp">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                        <a href="https://youtube.com/@legacybilliard" target="_blank" class="social-link" aria-label="YouTube">
                            <i class="fab fa-youtube"></i>
                        </a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="footer-column">
                    <h4 class="footer-title">Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="index.php"><i class="fas fa-chevron-right"></i> Home</a></li>
                        <li><a href="aboutus.php"><i class="fas fa-chevron-right"></i> About Us</a></li>
                        <li><a href="gallery.php"><i class="fas fa-chevron-right"></i> Gallery</a></li>
                        <li><a href="tournament.php"><i class="fas fa-chevron-right"></i> Tournaments</a></li>
                        <li><a href="promo.php"><i class="fas fa-chevron-right"></i> Promotions</a></li>
                        <li><a href="booking.php"><i class="fas fa-chevron-right"></i> Book Now</a></li>
                    </ul>
                </div>

                <!-- Services -->
                <div class="footer-column">
                    <h4 class="footer-title">Our Services</h4>
                    <ul class="footer-links">
                        <li><a href="booking.php"><i class="fas fa-chevron-right"></i> Table Booking</a></li>
                        <li><a href="tournament.php"><i class="fas fa-chevron-right"></i> Tournament Registration</a></li>
                        <li><a href="promo.php"><i class="fas fa-chevron-right"></i> Special Deals</a></li>
                        <li><a href="profile.php"><i class="fas fa-chevron-right"></i> Member Area</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Private Events</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Coaching Classes</a></li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div class="footer-column footer-contact">
                    <h4 class="footer-title">Contact Us</h4>
                    <ul class="footer-contact-list">
                        <li>
                            <div class="contact-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="contact-info">
                                <strong>Address</strong>
                                <p>Legacy Billiards BSD, Jl. Lingkar Luar Botanika Utara, Kavling Komersial Dalkamya at The Zora Blok C1 No. 20, BSD</p>
                            </div>
                        </li>
                        <li>
                            <div class="contact-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="contact-info">
                                <strong>Email</strong>
                                <p><a href="mailto:info@legacybilliard.com">info@legacybilliard.com</a></p>
                            </div>
                        </li>
                        <li>
                            <div class="contact-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div class="contact-info">
                                <strong>Phone</strong>
                                <p><a href="tel:+628118488988">+62 811-848-8988</a></p>
                            </div>
                        </li>
                        <li>
                            <div class="contact-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="contact-info">
                                <strong>Opening Hours</strong>
                                <p>Mon - Sun: 10:00 AM - 2:00 AM</p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Newsletter Section -->
    <div class="footer-newsletter">
        <div class="container">
            <div class="newsletter-content">
                <div class="newsletter-text">
                    <h3><i class="fas fa-envelope-open-text"></i> Subscribe to Our Newsletter</h3>
                    <p>Join and get the latest info on Club, Events, and Special Offers</p>
                </div>
                <form class="newsletter-form" id="newsletterForm">
                    <div class="newsletter-input-group">
                        <i class="fas fa-envelope"></i>
                        <input 
                            type="email" 
                            name="email" 
                            placeholder="Enter your email address..." 
                            required
                            pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"
                        >
                        <button type="submit" class="btn-newsletter">
                            <span>Subscribe</span>
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bottom Footer -->
    <div class="footer-bottom">
        <div class="container">
            <div class="footer-bottom-content">
                <div class="footer-copyright">
                    <p>© <?= date('Y') ?> <strong>Legacy Billiard</strong>. All Rights Reserved.</p>
                </div>
                <div class="footer-bottom-links">
                    <a href="#">Privacy Policy</a>
                    <span class="separator">|</span>
                    <a href="#">Terms of Service</a>
                    <span class="separator">|</span>
                    <a href="#">Refund Policy</a>
                </div>
                <div class="footer-payment">
                    <span>We Accept:</span>
                    <i class="fab fa-cc-visa"></i>
                    <i class="fab fa-cc-mastercard"></i>
                    <i class="fab fa-cc-paypal"></i>
                    <i class="fas fa-money-bill-wave"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Back to Top Button -->
    <button class="back-to-top" id="backToTop" aria-label="Back to top">
        <i class="fas fa-chevron-up"></i>
    </button>
</footer>

<style>
/* ============================================
   FOOTER STYLES - ULTRA PREMIUM
   ============================================ */

.footer-section {
    background: linear-gradient(180deg, #0a0a0a 0%, #1a1a2e 100%);
    color: #fff;
    position: relative;
    overflow: hidden;
}

.footer-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: 
        radial-gradient(circle at 20% 50%, rgba(212, 175, 55, 0.05), transparent 40%),
        radial-gradient(circle at 80% 50%, rgba(212, 175, 55, 0.05), transparent 40%);
    pointer-events: none;
}

/* Main Footer */
.footer-main {
    padding: 5rem 0 3rem;
    position: relative;
    z-index: 2;
}

.footer-grid {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1.5fr;
    gap: 3rem;
}

.footer-column {
    animation: fadeInUp 0.6s ease-out backwards;
}

.footer-column:nth-child(1) { animation-delay: 0.1s; }
.footer-column:nth-child(2) { animation-delay: 0.2s; }
.footer-column:nth-child(3) { animation-delay: 0.3s; }
.footer-column:nth-child(4) { animation-delay: 0.4s; }

/* Company Info */
.footer-about {
    padding-right: 2rem;
}

.footer-logo {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.footer-logo i {
    font-size: 3rem;
    color: #D4AF37;
    animation: rotate-slow 20s linear infinite;
}

@keyframes rotate-slow {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.footer-logo h3 {
    font-size: 1.8rem;
    font-weight: 900;
    color: #D4AF37;
    letter-spacing: 2px;
    margin: 0;
    text-shadow: 0 0 20px rgba(212, 175, 55, 0.5);
}

.footer-description {
    color: #b0b0b0;
    line-height: 1.8;
    margin-bottom: 2rem;
    font-size: 0.95rem;
}

/* Social Links */
.footer-social {
    display: flex;
    gap: 1rem;
}

.social-link {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: rgba(212, 175, 55, 0.1);
    border: 2px solid rgba(212, 175, 55, 0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #D4AF37;
    transition: all 0.3s ease;
    font-size: 1.1rem;
}

.social-link:hover {
    background: linear-gradient(135deg, #D4AF37, #F5D068);
    border-color: #D4AF37;
    color: #000;
    transform: translateY(-5px) scale(1.1);
    box-shadow: 0 10px 25px rgba(212, 175, 55, 0.4);
}

/* Footer Titles */
.footer-title {
    font-size: 1.3rem;
    font-weight: 900;
    color: #D4AF37;
    margin-bottom: 1.5rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    position: relative;
    padding-bottom: 1rem;
}

.footer-title::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 50px;
    height: 3px;
    background: linear-gradient(90deg, #D4AF37, transparent);
    border-radius: 50px;
}

/* Footer Links */
.footer-links {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-links li {
    margin-bottom: 1rem;
}

.footer-links a {
    color: #b0b0b0;
    text-decoration: none;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.8rem;
    font-size: 0.95rem;
}

.footer-links a i {
    font-size: 0.7rem;
    color: #D4AF37;
    transition: transform 0.3s ease;
}

.footer-links a:hover {
    color: #D4AF37;
    padding-left: 0.5rem;
}

.footer-links a:hover i {
    transform: translateX(5px);
}

/* Contact Info */
.footer-contact-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-contact-list li {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.contact-icon {
    width: 40px;
    height: 40px;
    min-width: 40px;
    border-radius: 10px;
    background: rgba(212, 175, 55, 0.1);
    border: 2px solid rgba(212, 175, 55, 0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #D4AF37;
}

.contact-info strong {
    color: #D4AF37;
    display: block;
    margin-bottom: 0.3rem;
    font-size: 0.9rem;
}

.contact-info p {
    color: #b0b0b0;
    margin: 0;
    line-height: 1.6;
    font-size: 0.9rem;
}

.contact-info a {
    color: #b0b0b0;
    text-decoration: none;
    transition: color 0.3s ease;
}

.contact-info a:hover {
    color: #D4AF37;
}

/* Newsletter Section */
.footer-newsletter {
    background: linear-gradient(135deg, rgba(212, 175, 55, 0.1), rgba(212, 175, 55, 0.05));
    padding: 3rem 0;
    border-top: 2px solid rgba(212, 175, 55, 0.2);
    border-bottom: 2px solid rgba(212, 175, 55, 0.2);
    position: relative;
    z-index: 2;
}

.newsletter-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 3rem;
}

.newsletter-text h3 {
    font-size: 1.8rem;
    font-weight: 900;
    color: #D4AF37;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.newsletter-text h3 i {
    font-size: 2.5rem;
}

.newsletter-text p {
    color: #b0b0b0;
    margin: 0;
    font-size: 1rem;
}

.newsletter-form {
    flex: 1;
    max-width: 600px;
}

.newsletter-input-group {
    display: flex;
    align-items: center;
    background: rgba(255, 255, 255, 0.05);
    border: 2px solid rgba(212, 175, 55, 0.3);
    border-radius: 50px;
    padding: 0.5rem 0.5rem 0.5rem 1.5rem;
    transition: all 0.3s ease;
    position: relative;
}

.newsletter-input-group:focus-within {
    border-color: #D4AF37;
    box-shadow: 0 0 30px rgba(212, 175, 55, 0.3);
}

.newsletter-input-group > i {
    color: #D4AF37;
    margin-right: 1rem;
    font-size: 1.2rem;
}

.newsletter-input-group input {
    flex: 1;
    background: transparent;
    border: none;
    outline: none;
    color: #fff;
    font-size: 1rem;
    padding: 0.8rem;
}

.newsletter-input-group input::placeholder {
    color: #666;
}

.btn-newsletter {
    background: linear-gradient(135deg, #D4AF37, #F5D068);
    border: none;
    border-radius: 50px;
    padding: 1rem 2rem;
    color: #000;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.8rem;
    white-space: nowrap;
}

.btn-newsletter:hover {
    transform: scale(1.05);
    box-shadow: 0 5px 20px rgba(212, 175, 55, 0.5);
}

/* Bottom Footer */
.footer-bottom {
    padding: 2rem 0;
    position: relative;
    z-index: 2;
}

.footer-bottom-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1.5rem;
}

.footer-copyright p {
    color: #888;
    margin: 0;
    font-size: 0.9rem;
}

.footer-copyright strong {
    color: #D4AF37;
}

.footer-bottom-links {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.footer-bottom-links a {
    color: #888;
    text-decoration: none;
    transition: color 0.3s ease;
    font-size: 0.9rem;
}

.footer-bottom-links a:hover {
    color: #D4AF37;
}

.footer-bottom-links .separator {
    color: #444;
}

.footer-payment {
    display: flex;
    align-items: center;
    gap: 1rem;
    color: #888;
    font-size: 0.9rem;
}

.footer-payment i {
    font-size: 1.8rem;
    color: #D4AF37;
}

/* Back to Top Button */
.back-to-top {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(135deg, #D4AF37, #F5D068);
    border: none;
    color: #000;
    font-size: 1.2rem;
    cursor: pointer;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
    z-index: 1000;
    box-shadow: 0 5px 20px rgba(212, 175, 55, 0.4);
}

.back-to-top.show {
    opacity: 1;
    visibility: visible;
}

.back-to-top:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(212, 175, 55, 0.6);
}

/* Animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive Design */
@media (max-width: 1200px) {
    .footer-grid {
        grid-template-columns: 2fr 1fr 1fr;
    }

    .footer-contact {
        grid-column: 1 / -1;
    }
}

@media (max-width: 768px) {
    .footer-grid {
        grid-template-columns: 1fr;
        gap: 2rem;
    }

    .footer-about {
        padding-right: 0;
    }

    .newsletter-content {
        flex-direction: column;
        text-align: center;
    }

    .newsletter-text h3 {
        justify-content: center;
        font-size: 1.5rem;
    }

    .newsletter-form {
        width: 100%;
    }

    .footer-bottom-content {
        flex-direction: column;
        text-align: center;
    }

    .footer-payment {
        justify-content: center;
    }

    .back-to-top {
        bottom: 20px;
        right: 20px;
        width: 45px;
        height: 45px;
    }
}

@media (max-width: 480px) {
    .newsletter-input-group {
        flex-direction: column;
        border-radius: 15px;
        padding: 1rem;
    }

    .newsletter-input-group > i {
        display: none;
    }

    .btn-newsletter {
        width: 100%;
        justify-content: center;
        margin-top: 0.5rem;
    }
}
</style>

<script>
// Newsletter Form Submission
document.getElementById('newsletterForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const email = this.querySelector('input[name="email"]').value;
    
    // Here you can add AJAX call to save email to database
    // For now, just show success message
    
    alert(`Thank you for subscribing! We'll send updates to ${email}`);
    this.reset();
});

// Back to Top Button
const backToTopBtn = document.getElementById('backToTop');

window.addEventListener('scroll', function() {
    if (window.pageYOffset > 300) {
        backToTopBtn.classList.add('show');
    } else {
        backToTopBtn.classList.remove('show');
    }
});

backToTopBtn.addEventListener('click', function() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
});

// Smooth scroll for all anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            e.preventDefault();
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});
</script>
