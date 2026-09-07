<?php
// Get user info if logged in
$user_avatar = 'images/avataruser.jpg';
$user_name = 'Profile';

if (isset($_SESSION['user_id'])) {
    $user_query = "SELECT nama, foto_profil FROM users WHERE id = ?";
    $stmt = $conn->prepare($user_query);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $user_result = $stmt->get_result();
    if ($user_data = $user_result->fetch_assoc()) {
        $user_name = $user_data['nama'];
        if (!empty($user_data['foto_profil']) && file_exists('images/' . $user_data['foto_profil'])) {
            $user_avatar = 'images/' . $user_data['foto_profil'];
        }
    }
    $stmt->close();
}
?>
<style>
    /* GLOBAL BODY PADDING */
    body {
        padding-top: 80px !important;
        background: #0a0a0a;
        color: #fff;
        margin: 0;
    }
    
    /* NAVBAR CONTAINER */
    .main-navbar {
        background: #1a1a1a;
        padding: 1rem 5%;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 9999;
        border-bottom: 1px solid #333;
        box-shadow: 0 2px 20px rgba(0, 0, 0, 0.8);
    }
    
    .navbar-wrapper {
        display: flex;
        justify-content: space-between;
        align-items: center;
        max-width: 1600px;
        margin: 0 auto;
    }
    
    /* LOGO */
    .nav-logo {
        font-size: 1.6rem;
        font-weight: 900;
        color: #D4AF37;
        text-decoration: none;
        letter-spacing: 1px;
        transition: all 0.3s ease;
    }
    
    .nav-logo:hover {
        color: #F5D068;
    }
    
    /* NAV MENU */
    .nav-menu {
        display: flex;
        gap: 2.5rem;
        align-items: center;
        list-style: none;
        margin: 0;
        padding: 0;
    }
    
    .nav-menu a {
        color: #fff;
        text-decoration: none;
        font-weight: 500;
        font-size: 0.95rem;
        transition: color 0.3s ease;
        position: relative;
    }
    
    .nav-menu a:hover,
    .nav-menu a.active {
        color: #D4AF37;
    }
    
    .nav-menu a.active {
        border-bottom: 2px solid #D4AF37;
        padding-bottom: 3px;
    }
    
    /* RIGHT ACTIONS */
    .nav-actions {
        display: flex;
        gap: 1.2rem;
        align-items: center;
    }
    
    /* CART ICON */
    .nav-cart {
        position: relative;
        color: #D4AF37;
        font-size: 1.4rem;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: flex;
        align-items: center;
    }
    
    .nav-cart:hover {
        transform: scale(1.1);
        color: #F5D068;
    }
    
    .cart-count {
        position: absolute;
        top: -8px;
        right: -8px;
        background: #FF3B3B;
        color: #fff;
        border-radius: 50%;
        width: 18px;
        height: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.65rem;
        font-weight: 700;
    }
    
    /* PROFILE BUTTON */
    .nav-profile {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        background: #2a2a2a;
        border: none;
        border-radius: 30px;
        padding: 0.5rem 1.2rem;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        color: #fff;
    }
    
    .nav-profile:hover {
        background: #333;
        color: #D4AF37;
    }
    
    .nav-profile i {
        font-size: 1.2rem;
    }
    
    .nav-profile span {
        font-weight: 600;
        font-size: 0.9rem;
    }
    
    /* LOGOUT BUTTON */
    .nav-logout {
        background: #D4AF37;
        color: #000;
        padding: 0.6rem 1.5rem;
        border-radius: 30px;
        text-decoration: none;
        font-weight: 700;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
    }
    
    .nav-logout:hover {
        background: #F5D068;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(212, 175, 55, 0.4);
        color: #000;
    }
    
    /* LOGIN BUTTON */
    .nav-login {
        background: transparent;
        border: 2px solid #D4AF37;
        color: #D4AF37;
        padding: 0.6rem 1.5rem;
        border-radius: 30px;
        text-decoration: none;
        font-weight: 700;
        font-size: 0.9rem;
        transition: all 0.3s ease;
    }
    
    .nav-login:hover {
        background: #D4AF37;
        color: #000;
    }
    
    /* RESPONSIVE */
    @media (max-width: 992px) {
        body {
            padding-top: 75px !important;
        }
        
        .nav-menu {
            gap: 1.5rem;
        }
        
        .nav-menu a {
            font-size: 0.85rem;
        }
    }
    
    @media (max-width: 768px) {
        body {
            padding-top: 70px !important;
        }
        
        .main-navbar {
            padding: 0.8rem 3%;
        }
        
        .nav-logo {
            font-size: 1.3rem;
        }
        
        .nav-menu {
            display: none;
        }
        
        .nav-actions {
            gap: 0.8rem;
        }
        
        .nav-profile span {
            display: none;
        }
        
        .nav-logout {
            padding: 0.5rem 1rem;
            font-size: 0.8rem;
        }
    }
</style>

<nav class="main-navbar">
    <div class="navbar-wrapper">
        <!-- LOGO -->
        <a href="index.php" class="nav-logo">LEGACY BILLIARD</a>
        
        <!-- MENU -->
        <ul class="nav-menu">
            <li><a href="index.php" class="<?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">Home</a></li>
            <li><a href="gallery.php" class="<?= basename($_SERVER['PHP_SELF']) == 'gallery.php' ? 'active' : '' ?>">Gallery</a></li>
            <li><a href="booking.php" class="<?= basename($_SERVER['PHP_SELF']) == 'booking.php' ? 'active' : '' ?>">Pricing & Booking</a></li>
            <li><a href="tournament.php" class="<?= basename($_SERVER['PHP_SELF']) == 'tournament.php' ? 'active' : '' ?>">Tournament</a></li>
            <li><a href="aboutus.php" class="<?= basename($_SERVER['PHP_SELF']) == 'aboutus.php' ? 'active' : '' ?>">About Us</a></li>
        </ul>
        
        <!-- RIGHT ACTIONS -->
        <div class="nav-actions">
            <?php if (isset($_SESSION['user_id'])): ?>
                <!-- CART -->
                <a href="cart.php" class="nav-cart">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-count" id="cart-count">0</span>
                </a>
                
                <!-- PROFILE -->
                <a href="profile.php" class="nav-profile">
                    <i class="fas fa-user"></i>
                    <span><?= htmlspecialchars(explode(' ', $user_name)[0]) ?></span>
                </a>
                
                <!-- LOGOUT -->
                <a href="logout.php" class="nav-logout">Logout</a>
            <?php else: ?>
                <!-- LOGIN -->
                <a href="login.php" class="nav-login">LOGIN</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<?php if (isset($_SESSION['user_id'])): ?>
<script>
    // Update cart count
    function updateCartCount() {
        fetch('get_cart_count.php')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const badge = document.getElementById('cart-count');
                    if (badge) {
                        badge.textContent = data.count;
                        badge.style.display = data.count > 0 ? 'flex' : 'none';
                    }
                }
            })
            .catch(err => console.error('Error:', err));
    }
    
    updateCartCount();
    setInterval(updateCartCount, 30000);
</script>
<?php endif; ?>
