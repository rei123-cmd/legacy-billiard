<?php
require_once 'config.php';
require_once 'admin_check.php';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = clean_input($_POST['nama_tournament']);
    $deskripsi = clean_input($_POST['deskripsi']);
    $tanggal = $_POST['tanggal_mulai'];
    $waktu = $_POST['waktu_mulai'];
    $hadiah = clean_input($_POST['hadiah']);
    $max_peserta = (int)$_POST['max_peserta']; // Now dynamic: 4, 8, 16, or 32
    $created_by = $_SESSION['user_id'];
    
    // Validate max_peserta
    if (!in_array($max_peserta, [4, 8, 16, 32])) {
        $_SESSION['error'] = 'Jumlah peserta harus 4, 8, 16, atau 32!';
        header('Location: admin_create_tournament.php');
        exit;
    }
    
    // Handle banner upload
    $banner = null;
    if (isset($_FILES['banner']) && $_FILES['banner']['error'] == 0) {
        $upload_dir = 'images/tournaments/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_ext = strtolower(pathinfo($_FILES['banner']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($file_ext, $allowed)) {
            $banner = 'tournament_' . time() . '.' . $file_ext;
            move_uploaded_file($_FILES['banner']['tmp_name'], $upload_dir . $banner);
        }
    }
    
    $query = "INSERT INTO tournaments (nama_tournament, deskripsi, tanggal_mulai, waktu_mulai, hadiah, banner_image, max_peserta, created_by) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssssii", $nama, $deskripsi, $tanggal, $waktu, $hadiah, $banner, $max_peserta, $created_by);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Tournament berhasil dibuat! Max peserta: ' . $max_peserta;
        header('Location: admin_tournaments.php');
        exit;
    } else {
        $_SESSION['error'] = 'Gagal membuat tournament!';
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Tournament - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --admin-primary: #1a1a2e;
            --admin-secondary: #16213e;
            --admin-accent: #0f3460;
            --admin-gold: #D4AF37;
            --admin-text: #ffffff;
            --admin-text-gray: #b0b0b0;
        }

        body {
            background: #0a0a0a;
            color: var(--admin-text);
            font-family: 'Inter', sans-serif;
        }

        .admin-content {
            margin-left: 280px;
            padding: 2rem;
            min-height: 100vh;
        }

        .admin-header {
            background: linear-gradient(135deg, var(--admin-primary) 0%, var(--admin-secondary) 100%);
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            border: 2px solid var(--admin-gold);
            box-shadow: 0 10px 30px rgba(212, 175, 55, 0.2);
        }

        .admin-header h1 {
            color: var(--admin-gold);
            font-weight: 900;
            margin: 0;
            font-size: 2.5rem;
        }

        .form-container {
            max-width: 800px;
            margin: 0 auto;
            background: linear-gradient(135deg, var(--admin-primary) 0%, var(--admin-secondary) 100%);
            padding: 2.5rem;
            border-radius: 15px;
            border: 2px solid var(--admin-accent);
        }

        .form-label {
            color: var(--admin-gold);
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .form-control, .form-select {
            background: var(--admin-accent);
            border: 2px solid var(--admin-accent);
            color: var(--admin-text);
            padding: 0.8rem;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            background: var(--admin-accent);
            border-color: var(--admin-gold);
            color: var(--admin-text);
            box-shadow: 0 0 15px rgba(212, 175, 55, 0.3);
        }

        .form-control::placeholder {
            color: var(--admin-text-gray);
        }

        .btn-submit {
            background: linear-gradient(135deg, var(--admin-gold), #C5A028);
            color: #000;
            padding: 1rem 3rem;
            border-radius: 50px;
            border: none;
            font-weight: 700;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            width: 100%;
        }

        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(212, 175, 55, 0.5);
        }

        .btn-back {
            background: #6c757d;
            color: #fff;
            padding: 0.8rem 2rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 700;
            transition: all 0.3s ease;
            display: inline-block;
            margin-bottom: 1.5rem;
        }

        .btn-back:hover {
            background: #545b62;
            color: #fff;
            transform: translateY(-2px);
        }

        /* Participant Options */
        .participant-options {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .participant-option {
            position: relative;
        }

        .participant-option input[type="radio"] {
            position: absolute;
            opacity: 0;
        }

        .participant-label {
            display: block;
            background: var(--admin-accent);
            border: 2px solid var(--admin-accent);
            border-radius: 15px;
            padding: 1.5rem 1rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .participant-option input[type="radio"]:checked + .participant-label {
            background: rgba(212, 175, 55, 0.2);
            border-color: var(--admin-gold);
            box-shadow: 0 0 20px rgba(212, 175, 55, 0.4);
        }

        .participant-number {
            font-size: 2.5rem;
            font-weight: 900;
            color: var(--admin-gold);
            display: block;
            margin-bottom: 0.5rem;
        }

        .participant-pools {
            font-size: 0.85rem;
            color: var(--admin-text-gray);
        }

        @media (max-width: 768px) {
            .admin-content {
                margin-left: 70px;
                padding: 1rem;
            }

            .participant-options {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <?php include 'admin_sidebar.php'; ?>

    <div class="admin-content">
        <div class="admin-header">
            <h1><i class="fas fa-plus-circle"></i> Create New Tournament</h1>
        </div>

        <a href="admin_tournaments.php" class="btn-back">
            <i class="fas fa-arrow-left"></i> Back to Tournaments
        </a>

        <div class="form-container">
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label"><i class="fas fa-trophy"></i> Tournament Name</label>
                    <input type="text" class="form-control" name="nama_tournament" placeholder="e.g., Championship December 2025" required>
                </div>

                <div class="mb-3">
                    <label class="form-label"><i class="fas fa-align-left"></i> Description</label>
                    <textarea class="form-control" name="deskripsi" rows="4" placeholder="Describe the tournament..." required></textarea>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label"><i class="fas fa-calendar"></i> Start Date</label>
                        <input type="date" class="form-control" name="tanggal_mulai" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><i class="fas fa-clock"></i> Start Time</label>
                        <input type="time" class="form-control" name="waktu_mulai" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label"><i class="fas fa-gift"></i> Prize/Hadiah</label>
                    <input type="text" class="form-control" name="hadiah" placeholder="e.g., Rp 5.000.000 + Trophy" required>
                </div>

                <div class="mb-3">
                    <label class="form-label"><i class="fas fa-image"></i> Banner Image (Optional)</label>
                    <input type="file" class="form-control" name="banner" accept="image/*">
                </div>

                <div class="mb-4">
                    <label class="form-label"><i class="fas fa-users"></i> Max Participants</label>
                    <div class="participant-options">
                        <div class="participant-option">
                            <input type="radio" name="max_peserta" value="4" id="opt4">
                            <label for="opt4" class="participant-label">
                                <span class="participant-number">4</span>
                                <span class="participant-pools">Pool A: 2<br>Pool B: 2</span>
                            </label>
                        </div>
                        <div class="participant-option">
                            <input type="radio" name="max_peserta" value="8" id="opt8" checked>
                            <label for="opt8" class="participant-label">
                                <span class="participant-number">8</span>
                                <span class="participant-pools">Pool A: 4<br>Pool B: 4</span>
                            </label>
                        </div>
                        <div class="participant-option">
                            <input type="radio" name="max_peserta" value="16" id="opt16">
                            <label for="opt16" class="participant-label">
                                <span class="participant-number">16</span>
                                <span class="participant-pools">Pool A: 8<br>Pool B: 8</span>
                            </label>
                        </div>
                        <div class="participant-option">
                            <input type="radio" name="max_peserta" value="32" id="opt32">
                            <label for="opt32" class="participant-label">
                                <span class="participant-number">32</span>
                                <span class="participant-pools">Pool A: 16<br>Pool B: 16</span>
                            </label>
                        </div>
                    </div>
                    <small style="color: var(--admin-text-gray);">
                        <i class="fas fa-info-circle"></i> Tournament akan dibagi menjadi 2 pool (A & B) dengan jumlah peserta yang sama
                    </small>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-check-circle"></i> Create Tournament
                </button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>