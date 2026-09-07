<?php
// Get current page filename
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="admin-sidebar">
    <!-- LOGO SECTION -->
    <div class="sidebar-logo">
        <i class="fas fa-dice-d6"></i>
        <h3>LEGACY BILLIARD</h3>
        <p>Admin Control Panel</p>
    </div>
    
    <!-- ADMIN PROFILE SECTION -->
    <div class="admin-user-profile">
        <img src="images/<?= htmlspecialchars($_SESSION['user_photo'] ?? 'avataruser.jpg') ?>" alt="Admin Avatar" class="admin-avatar">
        <div class="admin-user-details">
            <h5><?= htmlspecialchars($_SESSION['username'] ?? 'Administrator') ?></h5>
            <span class="admin-badge">Administrator</span>
        </div>
    </div>

    <!-- NAVIGATION MENU -->
    <nav class="admin-menu">
        <!-- DASHBOARD -->
        <a href="admin_index.php" class="<?= $current_page === 'admin_index.php' ? 'active' : '' ?>">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
            <?php if ($current_page === 'admin_index.php'): ?>
                <span class="active-indicator"></span>
            <?php endif; ?>
        </a>
        
        <!-- SECTION DIVIDER -->
        <div class="menu-divider">
            <i class="fas fa-layer-group"></i>
            <span>MANAGEMENT</span>
        </div>
        
        <!-- BOOKINGS MANAGEMENT -->
        <a href="admin_bookings.php" class="<?= $current_page === 'admin_bookings.php' ? 'active' : '' ?>">
            <i class="fas fa-calendar-check"></i>
            <span>Kelola Booking</span>
            <?php if ($current_page === 'admin_bookings.php'): ?>
                <span class="active-indicator"></span>
            <?php endif; ?>
        </a>
        
        <!-- TABLES MANAGEMENT -->
        <a href="admin_tables.php" class="<?= $current_page === 'admin_tables.php' ? 'active' : '' ?>">
            <i class="fas fa-table"></i>
            <span>Kelola Meja</span>
            <?php if ($current_page === 'admin_tables.php'): ?>
                <span class="active-indicator"></span>
            <?php endif; ?>
        </a>
        
        <!-- TOURNAMENTS MANAGEMENT -->
        <a href="admin_tournaments.php" class="<?= $current_page === 'admin_tournaments.php' || $current_page === 'admin_create_tournament.php' || $current_page === 'admin_tournament_bracket.php' ? 'active' : '' ?>">
            <i class="fas fa-trophy"></i>
            <span>Kelola Tournament</span>
            <?php if ($current_page === 'admin_tournaments.php' || $current_page === 'admin_create_tournament.php' || $current_page === 'admin_tournament_bracket.php'): ?>
                <span class="active-indicator"></span>
            <?php endif; ?>
        </a>
        
        <!-- PROMO MANAGEMENT -->
        <a href="admin_promo.php" class="<?= $current_page === 'admin_promo.php' ? 'active' : '' ?>">
            <i class="fas fa-tags"></i>
            <span>Kelola Promo</span>
            <?php if ($current_page === 'admin_promo.php'): ?>
                <span class="active-indicator"></span>
            <?php endif; ?>
        </a>
        
        <!-- GALLERY MANAGEMENT -->
        <a href="admin_gallery.php" class="<?= $current_page === 'admin_gallery.php' ? 'active' : '' ?>">
            <i class="fas fa-images"></i>
            <span>Kelola Gallery</span>
            <?php if ($current_page === 'admin_gallery.php'): ?>
                <span class="active-indicator"></span>
            <?php endif; ?>
        </a>
        
        <!-- SECTION DIVIDER -->
        <div class="menu-divider">
            <i class="fas fa-cog"></i>
            <span>SYSTEM</span>
        </div>
        
        <!-- VIEW WEBSITE -->
        <a href="index.php" target="_blank" rel="noopener noreferrer">
            <i class="fas fa-external-link-alt"></i>
            <span>Lihat Website</span>
        </a>
        
        <!-- SETTINGS -->
        <a href="admin_settings.php" class="<?= $current_page === 'admin_settings.php' ? 'active' : '' ?>">
            <i class="fas fa-sliders-h"></i>
            <span>Pengaturan</span>
            <?php if ($current_page === 'admin_settings.php'): ?>
                <span class="active-indicator"></span>
            <?php endif; ?>
        </a>
        
        <!-- LOGOUT -->
        <a href="logout.php" class="logout-link" onclick="return confirm('Apakah Anda yakin ingin logout?')">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </nav>
    
    <!-- FOOTER INFO -->
    <div class="sidebar-footer">
        <p>&copy; <?= date('Y') ?> Legacy Billiard</p>
        <p>Version 1.0.0</p>
    </div>
</div>

<style>
/* ============================================
   ADMIN SIDEBAR STYLES - COMPLETE
   ============================================ */

.admin-sidebar {
    position: fixed;
    left: 0;
    top: 0;
    width: 280px;
    height: 100vh;
    background: linear-gradient(180deg, #1a1a2e 0%, #0f0f1e 100%);
    border-right: 3px solid #D4AF37;
    overflow-y: auto;
    z-index: 1000;
    box-shadow: 5px 0 30px rgba(0, 0, 0, 0.5);
    transition: all 0.3s ease;
}

/* LOGO SECTION */
.sidebar-logo {
    padding: 2rem 1.5rem;
    text-align: center;
    border-bottom: 2px solid rgba(212, 175, 55, 0.2);
    background: linear-gradient(135deg, rgba(212, 175, 55, 0.1) 0%, transparent 100%);
}

.sidebar-logo i {
    font-size: 3.5rem;
    color: #D4AF37;
    margin-bottom: 1rem;
    display: block;
    text-shadow: 0 0 20px rgba(212, 175, 55, 0.6);
    animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% {
        transform: scale(1);
        text-shadow: 0 0 20px rgba(212, 175, 55, 0.6);
    }
    50% {
        transform: scale(1.05);
        text-shadow: 0 0 30px rgba(212, 175, 55, 0.8);
    }
}

.sidebar-logo h3 {
    color: #D4AF37;
    font-weight: 900;
    font-size: 1.5rem;
    margin: 0;
    letter-spacing: 2px;
    text-transform: uppercase;
}

.sidebar-logo p {
    color: #888;
    font-size: 0.75rem;
    margin: 0.5rem 0 0 0;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    font-weight: 600;
}

/* ADMIN PROFILE SECTION */
.admin-user-profile {
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    border-bottom: 1px solid rgba(212, 175, 55, 0.1);
    background: rgba(212, 175, 55, 0.03);
    transition: all 0.3s ease;
}

.admin-user-profile:hover {
    background: rgba(212, 175, 55, 0.08);
}

.admin-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    border: 3px solid #D4AF37;
    object-fit: cover;
    box-shadow: 0 0 20px rgba(212, 175, 55, 0.4);
    transition: all 0.3s ease;
}

.admin-avatar:hover {
    transform: scale(1.1);
    box-shadow: 0 0 30px rgba(212, 175, 55, 0.6);
}

.admin-user-details {
    flex: 1;
}

.admin-user-details h5 {
    margin: 0;
    color: #D4AF37;
    font-weight: 700;
    font-size: 1.1rem;
    margin-bottom: 0.3rem;
}

.admin-badge {
    display: inline-block;
    background: linear-gradient(135deg, #D4AF37, #F5D068);
    color: #000;
    padding: 0.25rem 0.8rem;
    border-radius: 50px;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 10px rgba(212, 175, 55, 0.3);
}

/* NAVIGATION MENU */
.admin-menu {
    padding: 1rem 0 2rem;
}

.menu-divider {
    padding: 1.5rem 2rem 0.8rem;
    color: #666;
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 2px;
    text-transform: uppercase;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.menu-divider i {
    font-size: 0.9rem;
    color: #D4AF37;
}

.admin-menu a {
    display: flex;
    align-items: center;
    padding: 1rem 2rem;
    color: #b0b0b0;
    text-decoration: none;
    transition: all 0.3s ease;
    border-left: 4px solid transparent;
    position: relative;
    font-weight: 500;
}

.admin-menu a::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    height: 100%;
    width: 0;
    background: rgba(212, 175, 55, 0.1);
    transition: width 0.3s ease;
    z-index: -1;
}

.admin-menu a:hover {
    color: #D4AF37;
    background: rgba(212, 175, 55, 0.05);
    border-left-color: #D4AF37;
    padding-left: 2.5rem;
}

.admin-menu a:hover::before {
    width: 100%;
}

.admin-menu a.active {
    background: linear-gradient(90deg, rgba(212, 175, 55, 0.2) 0%, transparent 100%);
    color: #D4AF37;
    border-left-color: #D4AF37;
    font-weight: 700;
    box-shadow: inset 0 0 20px rgba(212, 175, 55, 0.1);
}

.admin-menu a i {
    width: 30px;
    margin-right: 1rem;
    font-size: 1.2rem;
    text-align: center;
}

.admin-menu a span:not(.active-indicator) {
    flex: 1;
}

.active-indicator {
    width: 8px;
    height: 8px;
    background: #D4AF37;
    border-radius: 50%;
    box-shadow: 0 0 10px rgba(212, 175, 55, 0.6);
    animation: blink 1.5s ease-in-out infinite;
}

@keyframes blink {
    0%, 100% {
        opacity: 1;
        box-shadow: 0 0 10px rgba(212, 175, 55, 0.6);
    }
    50% {
        opacity: 0.5;
        box-shadow: 0 0 5px rgba(212, 175, 55, 0.3);
    }
}

.logout-link {
    margin-top: 1rem;
    border-top: 1px solid rgba(212, 175, 55, 0.1);
}

.logout-link:hover {
    background: rgba(220, 53, 69, 0.1) !important;
    color: #dc3545 !important;
    border-left-color: #dc3545 !important;
}

.logout-link:hover i {
    animation: shake 0.5s ease-in-out;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}

/* SIDEBAR FOOTER */
.sidebar-footer {
    padding: 1.5rem 2rem;
    text-align: center;
    border-top: 1px solid rgba(212, 175, 55, 0.1);
    background: rgba(0, 0, 0, 0.2);
}

.sidebar-footer p {
    margin: 0;
    color: #666;
    font-size: 0.7rem;
    line-height: 1.8;
}

/* SCROLLBAR */
.admin-sidebar::-webkit-scrollbar {
    width: 8px;
}

.admin-sidebar::-webkit-scrollbar-track {
    background: #0f0f1e;
}

.admin-sidebar::-webkit-scrollbar-thumb {
    background: linear-gradient(180deg, #D4AF37, #F5D068);
    border-radius: 10px;
}

.admin-sidebar::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(180deg, #F5D068, #D4AF37);
}

/* RESPONSIVE */
@media (max-width: 768px) {
    .admin-sidebar {
        transform: translateX(-100%);
    }
    
    .admin-sidebar.show {
        transform: translateX(0);
    }
}
</style>
