<?php
/**
 * ============================================
 * LEGACY BILLIARD - ADMIN SETTINGS
 * ULTIMATE EDITION - FULL FEATURED
 * ============================================
 * Features:
 * - General Website Settings
 * - Booking & Pricing Configuration
 * - Tournament Settings
 * - Social Media Links
 * - Email Configuration
 * - Maintenance Mode Toggle
 * - Beautiful UI with Tabs
 * ============================================
 */

require_once 'config.php';
require_once 'admin_check.php';

// ============================================
// HANDLE SAVE SETTINGS
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action === 'save_general') {
        $site_name = $conn->real_escape_string($_POST['site_name']);
        $site_email = $conn->real_escape_string($_POST['site_email']);
        $site_phone = $conn->real_escape_string($_POST['site_phone']);
        $site_address = $conn->real_escape_string($_POST['site_address']);
        
        $settings = [
            'site_name' => $site_name,
            'site_email' => $site_email,
            'site_phone' => $site_phone,
            'site_address' => $site_address
        ];
        
        foreach ($settings as $key => $value) {
            $check = $conn->query("SELECT id FROM settings WHERE setting_key = '$key'");
            if ($check->num_rows > 0) {
                $conn->query("UPDATE settings SET setting_value = '$value' WHERE setting_key = '$key'");
            } else {
                $conn->query("INSERT INTO settings (setting_key, setting_value) VALUES ('$key', '$value')");
            }
        }
        
        $_SESSION['success_message'] = '✅ General settings saved successfully!';
        header('Location: admin_settings.php');
        exit;
    }
    
    if ($action === 'save_booking') {
        $hourly_price = floatval($_POST['hourly_price']);
        $promo_day_price = floatval($_POST['promo_day_price']);
        $promo_night_price = floatval($_POST['promo_night_price']);
        $promo_day_start = $_POST['promo_day_start'];
        $promo_day_end = $_POST['promo_day_end'];
        $promo_night_start = $_POST['promo_night_start'];
        $promo_night_end = $_POST['promo_night_end'];
        
        $settings = [
            'hourly_price' => $hourly_price,
            'promo_day_price' => $promo_day_price,
            'promo_night_price' => $promo_night_price,
            'promo_day_start' => $promo_day_start,
            'promo_day_end' => $promo_day_end,
            'promo_night_start' => $promo_night_start,
            'promo_night_end' => $promo_night_end
        ];
        
        foreach ($settings as $key => $value) {
            $value = $conn->real_escape_string($value);
            $check = $conn->query("SELECT id FROM settings WHERE setting_key = '$key'");
            if ($check->num_rows > 0) {
                $conn->query("UPDATE settings SET setting_value = '$value' WHERE setting_key = '$key'");
            } else {
                $conn->query("INSERT INTO settings (setting_key, setting_value) VALUES ('$key', '$value')");
            }
        }
        
        $_SESSION['success_message'] = '✅ Booking settings saved successfully!';
        header('Location: admin_settings.php');
        exit;
    }
    
    if ($action === 'save_social') {
        $facebook = $conn->real_escape_string($_POST['facebook']);
        $instagram = $conn->real_escape_string($_POST['instagram']);
        $twitter = $conn->real_escape_string($_POST['twitter']);
        $youtube = $conn->real_escape_string($_POST['youtube']);
        
        $settings = [
            'facebook_url' => $facebook,
            'instagram_url' => $instagram,
            'twitter_url' => $twitter,
            'youtube_url' => $youtube
        ];
        
        foreach ($settings as $key => $value) {
            $check = $conn->query("SELECT id FROM settings WHERE setting_key = '$key'");
            if ($check->num_rows > 0) {
                $conn->query("UPDATE settings SET setting_value = '$value' WHERE setting_key = '$key'");
            } else {
                $conn->query("INSERT INTO settings (setting_key, setting_value) VALUES ('$key', '$value')");
            }
        }
        
        $_SESSION['success_message'] = '✅ Social media links saved successfully!';
        header('Location: admin_settings.php');
        exit;
    }
    
    if ($action === 'toggle_maintenance') {
        $current = $conn->query("SELECT setting_value FROM settings WHERE setting_key = 'maintenance_mode'")->fetch_assoc();
        $new_value = ($current && $current['setting_value'] === '1') ? '0' : '1';
        
        $check = $conn->query("SELECT id FROM settings WHERE setting_key = 'maintenance_mode'");
        if ($check->num_rows > 0) {
            $conn->query("UPDATE settings SET setting_value = '$new_value' WHERE setting_key = 'maintenance_mode'");
        } else {
            $conn->query("INSERT INTO settings (setting_key, setting_value) VALUES ('maintenance_mode', '$new_value')");
        }
        
        $status = $new_value === '1' ? 'enabled' : 'disabled';
        $_SESSION['success_message'] = "✅ Maintenance mode $status!";
        header('Location: admin_settings.php');
        exit;
    }
}

// ============================================
// GET CURRENT SETTINGS
// ============================================
function getSetting($key, $default = '') {
    global $conn;
    $result = $conn->query("SELECT setting_value FROM settings WHERE setting_key = '$key'");
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc()['setting_value'];
    }
    return $default;
}

$site_name = getSetting('site_name', 'Legacy Billiard');
$site_email = getSetting('site_email', 'info@legacybilliard.com');
$site_phone = getSetting('site_phone', '+62 812-3456-7890');
$site_address = getSetting('site_address', 'BSD, Tangerang Selatan');
$hourly_price = getSetting('hourly_price', '40000');
$promo_day_price = getSetting('promo_day_price', '20000');
$promo_night_price = getSetting('promo_night_price', '30000');
$promo_day_start = getSetting('promo_day_start', '09:00');
$promo_day_end = getSetting('promo_day_end', '17:00');
$promo_night_start = getSetting('promo_night_start', '21:00');
$promo_night_end = getSetting('promo_night_end', '23:00');
$facebook = getSetting('facebook_url', 'https://facebook.com/legacybilliard');
$instagram = getSetting('instagram_url', 'https://instagram.com/legacybilliard');
$twitter = getSetting('twitter_url', 'https://twitter.com/legacybilliard');
$youtube = getSetting('youtube_url', 'https://youtube.com/legacybilliard');
$maintenance_mode = getSetting('maintenance_mode', '0');

$page_title = "System Settings";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - Legacy Billiard Admin</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-gold: #D4AF37;
            --secondary-gold: #F5D068;
            --dark-bg: #0a0a0a;
            --card-bg: #1a1a2e;
            --sidebar-bg: #16213e;
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
            --info: #17a2b8;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            background: var(--dark-bg);
            color: #fff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
        }

        .settings-page {
            margin-left: 260px;
            padding: 2rem;
            min-height: 100vh;
        }

        /* ========================================== */
        /* HEADER */
        /* ========================================== */
        .page-header {
            background: linear-gradient(135deg, var(--card-bg), var(--sidebar-bg));
            border: 3px solid var(--primary-gold);
            border-radius: 20px;
            padding: 2.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 15px 50px rgba(212, 175, 55, 0.3);
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(212, 175, 55, 0.1) 0%, transparent 70%);
            animation: rotate 30s linear infinite;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .page-header h1 {
            color: var(--primary-gold);
            font-size: 3rem;
            font-weight: 900;
            margin-bottom: 0.5rem;
            text-shadow: 0 0 30px rgba(212, 175, 55, 0.5);
            position: relative;
            z-index: 1;
        }

        .page-header p {
            color: #aaa;
            font-size: 1.2rem;
            position: relative;
            z-index: 1;
        }

        /* ========================================== */
        /* ALERTS */
        /* ========================================== */
        .alert {
            border-radius: 15px;
            padding: 1.3rem 1.8rem;
            margin-bottom: 2rem;
            font-weight: 600;
            font-size: 1.1rem;
            border: 2px solid;
            animation: slideInDown 0.5s ease;
        }

        @keyframes slideInDown {
            from { opacity: 0; transform: translateY(-30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .alert-success {
            background: rgba(40, 167, 69, 0.2);
            border-color: var(--success);
            color: var(--success);
        }

        .alert-danger {
            background: rgba(220, 53, 69, 0.2);
            border-color: var(--danger);
            color: var(--danger);
        }

        /* ========================================== */
        /* TABS */
        /* ========================================== */
        .tabs-container {
            background: linear-gradient(135deg, var(--card-bg), var(--sidebar-bg));
            border: 3px solid var(--primary-gold);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.5);
        }

        .nav-tabs {
            border: none;
            margin-bottom: 2rem;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .nav-tabs .nav-link {
            background: rgba(212, 175, 55, 0.1);
            border: 2px solid rgba(212, 175, 55, 0.3);
            color: #fff;
            padding: 1rem 2rem;
            border-radius: 15px;
            font-weight: 700;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .nav-tabs .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(212, 175, 55, 0.3), transparent);
            transition: left 0.5s ease;
        }

        .nav-tabs .nav-link:hover::before {
            left: 100%;
        }

        .nav-tabs .nav-link:hover {
            border-color: var(--primary-gold);
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(212, 175, 55, 0.4);
        }

        .nav-tabs .nav-link.active {
            background: linear-gradient(135deg, var(--primary-gold), var(--secondary-gold));
            color: #000;
            border-color: var(--primary-gold);
            box-shadow: 0 10px 40px rgba(212, 175, 55, 0.6);
        }

        .nav-tabs .nav-link i {
            margin-right: 0.8rem;
            font-size: 1.3rem;
        }

        /* ========================================== */
        /* FORM STYLING */
        /* ========================================== */
        .settings-form {
            background: rgba(255, 255, 255, 0.02);
            padding: 2rem;
            border-radius: 15px;
            border: 2px solid rgba(212, 175, 55, 0.2);
        }

        .form-section {
            margin-bottom: 2.5rem;
        }

        .form-section h3 {
            color: var(--primary-gold);
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid rgba(212, 175, 55, 0.3);
        }

        .form-section h3 i {
            margin-right: 1rem;
            font-size: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.8rem;
        }

        .form-label {
            color: var(--primary-gold);
            font-weight: 700;
            font-size: 1.1rem;
            margin-bottom: 0.8rem;
            display: block;
        }

        .form-label i {
            margin-right: 0.5rem;
            color: var(--secondary-gold);
        }

        .form-control,
        .form-select {
            background: rgba(0, 0, 0, 0.5);
            border: 2px solid rgba(212, 175, 55, 0.3);
            color: #fff;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus,
        .form-select:focus {
            background: rgba(0, 0, 0, 0.7);
            border-color: var(--primary-gold);
            box-shadow: 0 0 30px rgba(212, 175, 55, 0.4);
            color: #fff;
        }

        .form-control::placeholder {
            color: #888;
        }

        .input-group {
            position: relative;
        }

        .input-group-text {
            background: rgba(212, 175, 55, 0.2);
            border: 2px solid rgba(212, 175, 55, 0.3);
            color: var(--primary-gold);
            font-weight: 700;
            padding: 1rem 1.5rem;
            border-radius: 12px 0 0 12px;
        }

        .input-group .form-control {
            border-left: none;
            border-radius: 0 12px 12px 0;
        }

        /* ========================================== */
        /* BUTTONS */
        /* ========================================== */
        .btn-save {
            background: linear-gradient(135deg, var(--success), #20c997);
            border: none;
            color: #fff;
            padding: 1.2rem 3rem;
            border-radius: 50px;
            font-weight: 900;
            font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(40, 167, 69, 0.4);
            position: relative;
            overflow: hidden;
        }

        .btn-save::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .btn-save:hover::before {
            width: 300px;
            height: 300px;
        }

        .btn-save:hover {
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 15px 50px rgba(40, 167, 69, 0.6);
        }

        .btn-save i {
            margin-right: 0.8rem;
        }

        .btn-maintenance {
            background: linear-gradient(135deg, var(--danger), #e74c3c);
            border: none;
            color: #fff;
            padding: 1.5rem 2.5rem;
            border-radius: 50px;
            font-weight: 900;
            font-size: 1.3rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 10px 40px rgba(220, 53, 69, 0.5);
            width: 100%;
            margin-top: 1rem;
        }

        .btn-maintenance.active {
            background: linear-gradient(135deg, var(--success), #20c997);
            box-shadow: 0 10px 40px rgba(40, 167, 69, 0.5);
        }

        .btn-maintenance:hover {
            transform: translateY(-5px) scale(1.02);
        }

        /* ========================================== */
        /* MAINTENANCE CARD */
        /* ========================================== */
        .maintenance-card {
            background: linear-gradient(135deg, rgba(220, 53, 69, 0.2), rgba(231, 76, 60, 0.1));
            border: 3px solid var(--danger);
            border-radius: 20px;
            padding: 2.5rem;
            text-align: center;
            box-shadow: 0 15px 50px rgba(220, 53, 69, 0.3);
        }

        .maintenance-card.active {
            background: linear-gradient(135deg, rgba(40, 167, 69, 0.2), rgba(32, 201, 151, 0.1));
            border-color: var(--success);
            box-shadow: 0 15px 50px rgba(40, 167, 69, 0.3);
        }

        .maintenance-icon {
            font-size: 6rem;
            margin-bottom: 1.5rem;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.1); opacity: 0.8; }
        }

        .maintenance-status {
            font-size: 2rem;
            font-weight: 900;
            margin-bottom: 1rem;
        }

        .maintenance-description {
            font-size: 1.2rem;
            color: #aaa;
            margin-bottom: 2rem;
        }

        /* ========================================== */
        /* INFO BOXES */
        /* ========================================== */
        .info-box {
            background: rgba(23, 162, 184, 0.2);
            border: 2px solid var(--info);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .info-box i {
            color: var(--info);
            font-size: 1.5rem;
            margin-right: 1rem;
        }

        .info-box p {
            margin: 0;
            font-size: 1.1rem;
            color: #fff;
        }

        /* ========================================== */
        /* RESPONSIVE */
        /* ========================================== */
        @media (max-width: 1400px) {
            .settings-page {
                margin-left: 0;
                padding: 1rem;
            }
        }

        @media (max-width: 768px) {
            .page-header h1 {
                font-size: 2rem;
            }

            .nav-tabs .nav-link {
                padding: 0.8rem 1.5rem;
                font-size: 1rem;
            }

            .form-control,
            .form-select {
                padding: 0.8rem 1.2rem;
                font-size: 1rem;
            }

            .btn-save {
                padding: 1rem 2rem;
                font-size: 1.1rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'admin_sidebar.php'; ?>
    
    <div class="settings-page">
        <!-- Page Header -->
        <div class="page-header">
            <h1>
                <i class="fas fa-cogs"></i>
                System Settings
            </h1>
            <p>Configure your billiard system settings and preferences</p>
        </div>
        
        <!-- Alerts -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?= htmlspecialchars($_SESSION['success_message']) ?>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($_SESSION['error_message']) ?>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
        
        <!-- Tabs Container -->
        <div class="tabs-container">
            <ul class="nav nav-tabs" id="settingsTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab">
                        <i class="fas fa-building"></i>
                        General
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="booking-tab" data-bs-toggle="tab" data-bs-target="#booking" type="button" role="tab">
                        <i class="fas fa-calendar-check"></i>
                        Booking & Pricing
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="social-tab" data-bs-toggle="tab" data-bs-target="#social" type="button" role="tab">
                        <i class="fas fa-share-alt"></i>
                        Social Media
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="maintenance-tab" data-bs-toggle="tab" data-bs-target="#maintenance" type="button" role="tab">
                        <i class="fas fa-tools"></i>
                        Maintenance
                    </button>
                </li>
            </ul>
            
            <div class="tab-content" id="settingsTabContent">
                <!-- GENERAL SETTINGS TAB -->
                <div class="tab-pane fade show active" id="general" role="tabpanel">
                    <form method="POST" class="settings-form">
                        <input type="hidden" name="action" value="save_general">
                        
                        <div class="form-section">
                            <h3>
                                <i class="fas fa-info-circle"></i>
                                General Information
                            </h3>
                            
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-building"></i>
                                    Site Name
                                </label>
                                <input type="text" name="site_name" class="form-control" value="<?= htmlspecialchars($site_name) ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-envelope"></i>
                                    Contact Email
                                </label>
                                <input type="email" name="site_email" class="form-control" value="<?= htmlspecialchars($site_email) ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-phone"></i>
                                    Contact Phone
                                </label>
                                <input type="text" name="site_phone" class="form-control" value="<?= htmlspecialchars($site_phone) ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-map-marker-alt"></i>
                                    Address
                                </label>
                                <textarea name="site_address" class="form-control" rows="3" required><?= htmlspecialchars($site_address) ?></textarea>
                            </div>
                        </div>
                        
                        <div class="text-end">
                            <button type="submit" class="btn-save">
                                <i class="fas fa-save"></i>
                                Save General Settings
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- BOOKING & PRICING TAB -->
                <div class="tab-pane fade" id="booking" role="tabpanel">
                    <form method="POST" class="settings-form">
                        <input type="hidden" name="action" value="save_booking">
                        
                        <div class="form-section">
                            <h3>
                                <i class="fas fa-dollar-sign"></i>
                                Pricing Configuration
                            </h3>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label">
                                            <i class="fas fa-coins"></i>
                                            Hourly Rate (Normal)
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="number" name="hourly_price" class="form-control" value="<?= htmlspecialchars($hourly_price) ?>" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label">
                                            <i class="fas fa-sun"></i>
                                            Day Promo Rate
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="number" name="promo_day_price" class="form-control" value="<?= htmlspecialchars($promo_day_price) ?>" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label">
                                            <i class="fas fa-moon"></i>
                                            Night Promo Rate
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="number" name="promo_night_price" class="form-control" value="<?= htmlspecialchars($promo_night_price) ?>" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-section">
                            <h3>
                                <i class="fas fa-clock"></i>
                                Promo Time Periods
                            </h3>
                            
                            <div class="info-box">
                                <i class="fas fa-info-circle"></i>
                                <p>Configure the time periods when promotional rates are applied. Make sure times don't overlap.</p>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">
                                            <i class="fas fa-sun"></i>
                                            Day Promo Start Time
                                        </label>
                                        <input type="time" name="promo_day_start" class="form-control" value="<?= htmlspecialchars($promo_day_start) ?>" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">
                                            <i class="fas fa-sun"></i>
                                            Day Promo End Time
                                        </label>
                                        <input type="time" name="promo_day_end" class="form-control" value="<?= htmlspecialchars($promo_day_end) ?>" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">
                                            <i class="fas fa-moon"></i>
                                            Night Promo Start Time
                                        </label>
                                        <input type="time" name="promo_night_start" class="form-control" value="<?= htmlspecialchars($promo_night_start) ?>" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">
                                            <i class="fas fa-moon"></i>
                                            Night Promo End Time
                                        </label>
                                        <input type="time" name="promo_night_end" class="form-control" value="<?= htmlspecialchars($promo_night_end) ?>" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-end">
                            <button type="submit" class="btn-save">
                                <i class="fas fa-save"></i>
                                Save Booking Settings
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- SOCIAL MEDIA TAB -->
                <div class="tab-pane fade" id="social" role="tabpanel">
                    <form method="POST" class="settings-form">
                        <input type="hidden" name="action" value="save_social">
                        
                        <div class="form-section">
                            <h3>
                                <i class="fas fa-share-alt"></i>
                                Social Media Links
                            </h3>
                            
                            <div class="info-box">
                                <i class="fas fa-info-circle"></i>
                                <p>Add your social media profile URLs. These will be displayed in your website footer and contact page.</p>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fab fa-facebook"></i>
                                    Facebook Page URL
                                </label>
                                <input type="url" name="facebook" class="form-control" value="<?= htmlspecialchars($facebook) ?>" placeholder="https://facebook.com/your-page">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fab fa-instagram"></i>
                                    Instagram Profile URL
                                </label>
                                <input type="url" name="instagram" class="form-control" value="<?= htmlspecialchars($instagram) ?>" placeholder="https://instagram.com/your-profile">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fab fa-twitter"></i>
                                    Twitter Profile URL
                                </label>
                                <input type="url" name="twitter" class="form-control" value="<?= htmlspecialchars($twitter) ?>" placeholder="https://twitter.com/your-profile">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fab fa-youtube"></i>
                                    YouTube Channel URL
                                </label>
                                <input type="url" name="youtube" class="form-control" value="<?= htmlspecialchars($youtube) ?>" placeholder="https://youtube.com/your-channel">
                            </div>
                        </div>
                        
                        <div class="text-end">
                            <button type="submit" class="btn-save">
                                <i class="fas fa-save"></i>
                                Save Social Media Links
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- MAINTENANCE MODE TAB -->
                <div class="tab-pane fade" id="maintenance" role="tabpanel">
                    <div class="settings-form">
                        <div class="form-section">
                            <h3>
                                <i class="fas fa-tools"></i>
                                Maintenance Mode
                            </h3>
                            
                            <div class="maintenance-card <?= $maintenance_mode === '1' ? 'active' : '' ?>">
                                <div class="maintenance-icon">
                                    <?php if ($maintenance_mode === '1'): ?>
                                        <i class="fas fa-check-circle" style="color: var(--success);"></i>
                                    <?php else: ?>
                                        <i class="fas fa-times-circle" style="color: var(--danger);"></i>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="maintenance-status">
                                    <?php if ($maintenance_mode === '1'): ?>
                                        <span style="color: var(--success);">MAINTENANCE MODE ACTIVE</span>
                                    <?php else: ?>
                                        <span style="color: var(--danger);">MAINTENANCE MODE DISABLED</span>
                                    <?php endif; ?>
                                </div>
                                
                                <p class="maintenance-description">
                                    <?php if ($maintenance_mode === '1'): ?>
                                        Your website is currently in maintenance mode. Visitors will see a maintenance page. Click button below to disable.
                                    <?php else: ?>
                                        Your website is currently active and accessible to all visitors. Click button below to enable maintenance mode.
                                    <?php endif; ?>
                                </p>
                                
                                <form method="POST">
                                    <input type="hidden" name="action" value="toggle_maintenance">
                                    <button type="submit" class="btn-maintenance <?= $maintenance_mode === '1' ? 'active' : '' ?>" onclick="return confirm('<?= $maintenance_mode === '1' ? 'Disable maintenance mode and make site accessible?' : 'Enable maintenance mode? Users will not be able to access the site.' ?>')">
                                        <?php if ($maintenance_mode === '1'): ?>
                                            <i class="fas fa-play"></i>
                                            DISABLE MAINTENANCE MODE
                                        <?php else: ?>
                                            <i class="fas fa-pause"></i>
                                            ENABLE MAINTENANCE MODE
                                        <?php endif; ?>
                                    </button>
                                </form>
                            </div>
                            
                            <div class="info-box" style="margin-top: 2rem;">
                                <i class="fas fa-exclamation-triangle"></i>
                                <p><strong>Important:</strong> When maintenance mode is enabled, regular users cannot access the site. Only administrators can access the admin panel. Use this feature when performing updates or maintenance.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Console styling
        console.log('%c⚙️ LEGACY BILLIARD - ADMIN SETTINGS ⚙️', 'background: linear-gradient(135deg, #D4AF37, #F5D068); color: #000; font-size: 20px; font-weight: bold; padding: 15px; border-radius: 10px;');
        console.log('%cULTIMATE EDITION - FULL FEATURED', 'background: #28a745; color: #fff; font-size: 14px; font-weight: bold; padding: 8px; border-radius: 5px;');
        
        // Auto-dismiss alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s ease';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);
    </script>
</body>
</html>
