<?php
require_once 'config.php';
require_once 'admin_check.php';

// Handle Upload Image
if (isset($_POST['upload'])) {
    $title = clean_input($_POST['title']);
    $description = clean_input($_POST['description']);
    $uploaded_by = $_SESSION['user_id'];
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $upload_dir = 'images/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($file_ext, $allowed)) {
            $filename = 'gallery' . time() . '.' . $file_ext;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $filename)) {
                // Get max display order
                $query = "SELECT MAX(display_order) as max_order FROM gallery_images";
                $result = $conn->query($query);
                $max_order = $result->fetch_assoc()['max_order'] ?? 0;
                
                $query = "INSERT INTO gallery_images (filename, title, description, display_order, uploaded_by) 
                          VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($query);
                $new_order = $max_order + 1;
                $stmt->bind_param("sssii", $filename, $title, $description, $new_order, $uploaded_by);
                
                if ($stmt->execute()) {
                    $_SESSION['success'] = 'Image uploaded successfully!';
                } else {
                    $_SESSION['error'] = 'Failed to save image to database!';
                }
                $stmt->close();
            } else {
                $_SESSION['error'] = 'Failed to upload image!';
            }
        } else {
            $_SESSION['error'] = 'Invalid file format! Only JPG, PNG, GIF allowed.';
        }
    }
    header('Location: admin_gallery.php');
    exit;
}

// Handle Delete Image
if (isset($_GET['delete'])) {
    $image_id = (int)$_GET['delete'];
    
    // Get filename first
    $query = "SELECT filename FROM gallery_images WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $image_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $image = $result->fetch_assoc();
    $stmt->close();
    
    if ($image) {
        // Delete file
        $filepath = 'images/' . $image['filename'];
        if (file_exists($filepath)) {
            unlink($filepath);
        }
        
        // Delete from database
        $query = "DELETE FROM gallery_images WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $image_id);
        $stmt->execute();
        $stmt->close();
        
        $_SESSION['success'] = 'Image deleted successfully!';
    }
    header('Location: admin_gallery.php');
    exit;
}

// Handle Toggle Active
if (isset($_GET['toggle'])) {
    $image_id = (int)$_GET['toggle'];
    $query = "UPDATE gallery_images SET is_active = NOT is_active WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $image_id);
    $stmt->execute();
    $stmt->close();
    $_SESSION['success'] = 'Image status updated!';
    header('Location: admin_gallery.php');
    exit;
}

// Get all gallery images
$query = "SELECT gi.*, u.nama as uploader_name 
          FROM gallery_images gi 
          LEFT JOIN users u ON gi.uploaded_by = u.id 
          ORDER BY gi.display_order ASC";
$images = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Gallery - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --admin-primary: #1a1a2e;
            --admin-secondary: #16213e;
            --admin-accent: #0f3460;
            --admin-gold: #D4AF37;
        }

        body {
            background: #0a0a0a;
            color: #ffffff;
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
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .admin-header h1 {
            color: var(--admin-gold);
            font-weight: 900;
            margin: 0;
        }

        .btn-upload {
            background: var(--admin-gold);
            color: #000;
            padding: 0.8rem 2rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 700;
            border: none;
            cursor: pointer;
        }

        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
        }

        .gallery-card {
            background: linear-gradient(135deg, var(--admin-primary) 0%, var(--admin-secondary) 100%);
            border: 2px solid var(--admin-accent);
            border-radius: 15px;
            overflow: hidden;
            transition: all 0.3s ease;
            position: relative;
        }

        .gallery-card:hover {
            transform: translateY(-10px);
            border-color: var(--admin-gold);
            box-shadow: 0 15px 40px rgba(212, 175, 55, 0.3);
        }

        .gallery-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
            background: var(--admin-accent);
        }

        .gallery-info {
            padding: 1.5rem;
        }

        .gallery-title {
            color: var(--admin-gold);
            font-weight: 700;
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
        }

        .gallery-description {
            color: #b0b0b0;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .gallery-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.85rem;
            color: #888;
            margin-bottom: 1rem;
        }

        .gallery-actions {
            display: flex;
            gap: 0.5rem;
        }

        .btn-action {
            flex: 1;
            padding: 0.6rem;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 700;
            transition: all 0.3s ease;
            text-align: center;
            text-decoration: none;
            display: inline-block;
        }

        .btn-toggle {
            background: #ffc107;
            color: #000;
        }

        .btn-delete {
            background: #dc3545;
            color: #fff;
        }

        .status-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-weight: 700;
            font-size: 0.85rem;
            z-index: 2;
        }

        .badge-active {
            background: #28a745;
            color: #fff;
        }

        .badge-inactive {
            background: #6c757d;
            color: #fff;
        }

        .modal-content {
            background: var(--admin-secondary);
            border: 2px solid var(--admin-gold);
            color: #ffffff;
        }

        .form-label {
            color: var(--admin-gold);
            font-weight: 700;
        }

        .form-control {
            background: var(--admin-accent);
            border: 2px solid var(--admin-accent);
            color: #ffffff;
        }

        .form-control:focus {
            background: var(--admin-accent);
            border-color: var(--admin-gold);
            color: #ffffff;
            box-shadow: 0 0 10px rgba(212, 175, 55, 0.3);
        }

        @media (max-width: 768px) {
            .admin-content {
                margin-left: 70px;
                padding: 1rem;
            }

            .gallery-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include 'admin_sidebar.php'; ?>

    <div class="admin-content">
        <div class="admin-header">
            <h1><i class="fas fa-images"></i> Manage Gallery</h1>
            <button class="btn-upload" data-bs-toggle="modal" data-bs-target="#uploadModal">
                <i class="fas fa-upload"></i> Upload Image
            </button>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="gallery-grid">
            <?php while ($image = $images->fetch_assoc()): ?>
                <div class="gallery-card">
                    <span class="status-badge <?= $image['is_active'] ? 'badge-active' : 'badge-inactive' ?>">
                        <?= $image['is_active'] ? 'ACTIVE' : 'HIDDEN' ?>
                    </span>
                    
                    <img src="images/<?= htmlspecialchars($image['filename']) ?>" 
                         alt="<?= htmlspecialchars($image['title']) ?>" 
                         class="gallery-image">
                    
                    <div class="gallery-info">
                        <h4 class="gallery-title"><?= htmlspecialchars($image['title']) ?></h4>
                        <p class="gallery-description"><?= htmlspecialchars($image['description']) ?></p>
                        
                        <div class="gallery-meta">
                            <span><i class="fas fa-user"></i> <?= htmlspecialchars($image['uploader_name'] ?? 'Admin') ?></span>
                            <span><i class="fas fa-calendar"></i> <?= date('d M Y', strtotime($image['created_at'])) ?></span>
                        </div>

                        <div class="gallery-actions">
                            <a href="?toggle=<?= $image['id'] ?>" class="btn-action btn-toggle">
                                <i class="fas fa-eye<?= $image['is_active'] ? '-slash' : '' ?>"></i> 
                                <?= $image['is_active'] ? 'Hide' : 'Show' ?>
                            </a>
                            <a href="?delete=<?= $image['id'] ?>" class="btn-action btn-delete" 
                               onclick="return confirm('Delete this image?')">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- Upload Modal -->
    <div class="modal fade" id="uploadModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" style="color: var(--admin-gold);">
                        <i class="fas fa-upload"></i> Upload New Image
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-image"></i> Select Image</label>
                            <input type="file" class="form-control" name="image" accept="image/*" required>
                            <small style="color: #888;">Max size: 5MB. Formats: JPG, PNG, GIF</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-heading"></i> Title</label>
                            <input type="text" class="form-control" name="title" placeholder="e.g., VIP Room" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-align-left"></i> Description</label>
                            <textarea class="form-control" name="description" rows="3" 
                                      placeholder="Describe the image..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="upload" class="btn-upload">
                            <i class="fas fa-check-circle"></i> Upload
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
