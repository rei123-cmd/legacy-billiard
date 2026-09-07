<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Get user data
$user_query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get total bookings
$booking_query = "SELECT COUNT(*) as total_bookings FROM booking WHERE user_id = ?";
$stmt = $conn->prepare($booking_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_bookings = $stmt->get_result()->fetch_assoc()['total_bookings'];
$stmt->close();

// Get total spent
$spent_query = "SELECT SUM(total_harga) as total_spent FROM booking WHERE user_id = ? AND payment_status = 'paid'";
$stmt = $conn->prepare($spent_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_spent = $stmt->get_result()->fetch_assoc()['total_spent'] ?? 0;
$stmt->close();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $nama = clean_input($_POST['nama']);
    $email = clean_input($_POST['email']);
    $telepon = clean_input($_POST['telepon']);
    
    $update_query = "UPDATE users SET nama = ?, email = ?, telepon = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("sssi", $nama, $email, $telepon, $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Profile berhasil diupdate!';
        header('Location: profile.php');
        exit;
    } else {
        $_SESSION['error'] = 'Gagal update profile!';
    }
    $stmt->close();
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (password_verify($old_password, $user['password'])) {
        if ($new_password === $confirm_password) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_pass = "UPDATE users SET password = ? WHERE id = ?";
            $stmt = $conn->prepare($update_pass);
            $stmt->bind_param("si", $hashed_password, $user_id);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = 'Password berhasil diubah!';
                header('Location: profile.php');
                exit;
            }
            $stmt->close();
        } else {
            $_SESSION['error'] = 'Password baru tidak cocok!';
        }
    } else {
        $_SESSION['error'] = 'Password lama salah!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Legacy Billiard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            background: #0a0a0a;
            color: #fff;
        }
        
        .profile-container {
            max-width: 1200px;
            margin: 120px auto 50px;
            padding: 0 20px;
        }
        
        .profile-header {
            background: linear-gradient(135deg, #1a1a1a 0%, #0f0f0f 100%);
            border: 2px solid #D4AF37;
            border-radius: 20px;
            padding: 3rem;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 2rem;
        }
        
        .profile-avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 5px solid #D4AF37;
            object-fit: cover;
            box-shadow: 0 10px 40px rgba(212, 175, 55, 0.4);
            background: #fff;
        }
        
        .profile-info h2 {
            color: #D4AF37;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }
        
        .profile-info p {
            color: #999;
            margin-bottom: 0.3rem;
        }
        
        .profile-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #1a1a1a 0%, #0f0f0f 100%);
            border: 2px solid #333;
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            border-color: #D4AF37;
            transform: translateY(-5px);
        }
        
        .stat-card i {
            font-size: 2.5rem;
            color: #D4AF37;
            margin-bottom: 1rem;
        }
        
        .stat-card h3 {
            font-size: 2rem;
            font-weight: 800;
            color: #fff;
            margin-bottom: 0.5rem;
        }
        
        .stat-card p {
            color: #999;
            font-size: 0.9rem;
        }
        
        .profile-tabs {
            background: #1a1a1a;
            border: 2px solid #333;
            border-radius: 15px;
            padding: 2rem;
        }
        
        .nav-tabs {
            border-bottom: 2px solid #333;
        }
        
        .nav-tabs .nav-link {
            color: #999;
            border: none;
            padding: 1rem 2rem;
            transition: all 0.3s ease;
        }
        
        .nav-tabs .nav-link:hover {
            color: #D4AF37;
        }
        
        .nav-tabs .nav-link.active {
            color: #D4AF37;
            background: transparent;
            border-bottom: 3px solid #D4AF37;
        }
        
        .form-control {
            background: #0a0a0a;
            border: 2px solid #333;
            color: #fff;
            padding: 0.8rem 1rem;
            border-radius: 10px;
        }
        
        .form-control:focus {
            background: #0a0a0a;
            border-color: #D4AF37;
            color: #fff;
            box-shadow: 0 0 15px rgba(212, 175, 55, 0.3);
        }
        
        .form-label {
            color: #D4AF37;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .btn-update {
            background: linear-gradient(135deg, #D4AF37, #C5A028);
            color: #0a0a0a;
            border: none;
            padding: 0.8rem 2rem;
            border-radius: 10px;
            font-weight: 700;
            transition: all 0.3s ease;
        }
        
        .btn-update:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(212, 175, 55, 0.6);
        }
        
        .alert {
            border-radius: 10px;
            border: 2px solid;
        }
        
        .alert-success {
            background: rgba(76, 175, 80, 0.1);
            border-color: #4CAF50;
            color: #4CAF50;
        }
        
        .alert-danger {
            background: rgba(244, 67, 54, 0.1);
            border-color: #f44336;
            color: #f44336;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="profile-container">
        <!-- Alert Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> <?= $_SESSION['success'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle"></i> <?= $_SESSION['error'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Profile Header -->
        <div class="profile-header">
            <img src="images/avataruser.jpg" alt="Profile Avatar" class="profile-avatar">
            <div class="profile-info">
                <h2><?= htmlspecialchars($user['nama']) ?></h2>
                <p><i class="fas fa-envelope"></i> <?= htmlspecialchars($user['email']) ?></p>
                <p><i class="fas fa-phone"></i> <?= htmlspecialchars($user['telepon'] ?? 'Belum diisi') ?></p>
                <p><i class="fas fa-calendar"></i> Member sejak <?= date('d M Y', strtotime($user['created_at'])) ?></p>
            </div>
        </div>

        <!-- Stats -->
        <div class="profile-stats">
            <div class="stat-card">
                <i class="fas fa-calendar-check"></i>
                <h3><?= $total_bookings ?></h3>
                <p>Total Booking</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-wallet"></i>
                <h3>Rp <?= number_format($total_spent, 0, ',', '.') ?></h3>
                <p>Total Pengeluaran</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-star"></i>
                <h3>Gold</h3>
                <p>Status Member</p>
            </div>
        </div>

        <!-- Tabs -->
        <div class="profile-tabs">
            <ul class="nav nav-tabs" id="profileTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button">
                        <i class="fas fa-user"></i> Edit Profile
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="password-tab" data-bs-toggle="tab" data-bs-target="#password" type="button">
                        <i class="fas fa-lock"></i> Ganti Password
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="history-tab" data-bs-toggle="tab" data-bs-target="#history" type="button">
                        <i class="fas fa-history"></i> Riwayat Booking
                    </button>
                </li>
            </ul>

            <div class="tab-content mt-4" id="profileTabContent">
                <!-- Edit Profile Tab -->
                <div class="tab-pane fade show active" id="profile" role="tabpanel">
                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" name="nama" value="<?= htmlspecialchars($user['nama']) ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nomor Telepon</label>
                            <input type="tel" class="form-control" name="telepon" value="<?= htmlspecialchars($user['telepon'] ?? '') ?>" placeholder="+62">
                        </div>
                        <button type="submit" name="update_profile" class="btn btn-update">
                            <i class="fas fa-save"></i> Update Profile
                        </button>
                    </form>
                </div>

                <!-- Change Password Tab -->
                <div class="tab-pane fade" id="password" role="tabpanel">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">Password Lama</label>
                            <input type="password" class="form-control" name="old_password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password Baru</label>
                            <input type="password" class="form-control" name="new_password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Konfirmasi Password Baru</label>
                            <input type="password" class="form-control" name="confirm_password" required>
                        </div>
                        <button type="submit" name="change_password" class="btn btn-update">
                            <i class="fas fa-key"></i> Ubah Password
                        </button>
                    </form>
                </div>

                <!-- Booking History Tab -->
                <div class="tab-pane fade" id="history" role="tabpanel">
                    <div id="booking-history-container">
                        <div class="text-center py-5">
                            <i class="fas fa-spinner fa-spin" style="font-size: 3rem; color: #D4AF37;"></i>
                            <p class="mt-3" style="color: #999;">Memuat riwayat booking...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Load booking history when tab is clicked
        document.getElementById('history-tab').addEventListener('click', function() {
            loadBookingHistory();
        });

        function loadBookingHistory() {
            fetch('get_booking_history.php')
                .then(res => res.json())
                .then(data => {
                    const container = document.getElementById('booking-history-container');
                    
                    if (data.success && data.bookings.length > 0) {
                        let html = '<div class="table-responsive"><table class="table" style="color: #fff;">';
                        html += '<thead><tr style="border-bottom: 2px solid #D4AF37;">';
                        html += '<th>Booking ID</th><th>Meja</th><th>Tanggal</th><th>Waktu</th><th>Durasi</th><th>Total</th><th>Status</th><th>Pembayaran</th></tr></thead><tbody>';
                        
                        data.bookings.forEach(booking => {
                            const statusBadge = booking.status === 'confirmed' ? 'success' : booking.status === 'pending' ? 'warning' : 'danger';
                            const paymentBadge = booking.payment_status === 'paid' ? 'success' : 'danger';
                            
                            html += `<tr style="border-bottom: 1px solid #333;">
                                <td>#${booking.id}</td>
                                <td>${booking.nama_meja}</td>
                                <td>${new Date(booking.tanggal_booking).toLocaleDateString('id-ID')}</td>
                                <td>${booking.waktu_mulai}</td>
                                <td>${booking.durasi_jam} Jam</td>
                                <td>Rp ${parseInt(booking.total_harga).toLocaleString('id-ID')}</td>
                                <td><span class="badge bg-${statusBadge}">${booking.status}</span></td>
                                <td><span class="badge bg-${paymentBadge}">${booking.payment_status}</span></td>
                            </tr>`;
                        });
                        
                        html += '</tbody></table></div>';
                        container.innerHTML = html;
                    } else {
                        container.innerHTML = '<div class="text-center py-5"><i class="fas fa-inbox" style="font-size: 4rem; color: #666;"></i><p class="mt-3" style="color: #999;">Belum ada riwayat booking</p></div>';
                    }
                })
                .catch(err => {
                    console.error('Error:', err);
                    document.getElementById('booking-history-container').innerHTML = '<div class="alert alert-danger">Gagal memuat riwayat</div>';
                });
        }
    </script>
</body>
</html>
