<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery - Legacy Billiard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            background: #0a0a0a;
            color: #fff;
            padding-top: 80px;
        }

        /* GALLERY HEADER */
        .gallery-header {
            background: linear-gradient(135deg, #1a1a1a 0%, #0f0f0f 100%);
            padding: 4rem 0;
            text-align: center;
            border-bottom: 2px solid #D4AF37;
            margin-bottom: 3rem;
        }

        .gallery-header h1 {
            font-size: 3.5rem;
            font-weight: 900;
            color: #D4AF37;
            margin-bottom: 1rem;
            text-shadow: 0 0 30px rgba(212, 175, 55, 0.5);
        }

        .gallery-header p {
            font-size: 1.2rem;
            color: #999;
            margin: 0;
        }

        /* GALLERY CONTAINER */
        .gallery-container {
            max-width: 1400px;
            margin: 0 auto 4rem;
            padding: 0 2rem;
        }

        /* GALLERY GRID - FIXED LAYOUT */
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        /* GALLERY ITEM */
        .gallery-item {
            position: relative;
            height: 350px;
            border-radius: 15px;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.4s ease;
            background: #1a1a1a;
            border: 2px solid #333;
        }

        .gallery-item:hover {
            transform: translateY(-10px);
            border-color: #D4AF37;
            box-shadow: 0 15px 40px rgba(212, 175, 55, 0.4);
        }

        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: all 0.5s ease;
        }

        .gallery-item:hover img {
            transform: scale(1.15);
        }

        /* GALLERY OVERLAY */
        .gallery-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.9) 0%, transparent 60%);
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 1.5rem;
            opacity: 0;
            transition: all 0.4s ease;
        }

        .gallery-item:hover .gallery-overlay {
            opacity: 1;
        }

        .gallery-overlay h5 {
            color: #D4AF37;
            font-weight: 700;
            margin: 0 0 0.5rem 0;
            font-size: 1.2rem;
        }

        .gallery-overlay p {
            color: #fff;
            margin: 0;
            font-size: 0.9rem;
        }

        /* VIEW MORE BUTTON */
        .view-more-btn {
            background: linear-gradient(135deg, #D4AF37, #C5A028);
            color: #0a0a0a;
            padding: 1rem 3rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 0.8rem;
            transition: all 0.4s ease;
            border: none;
            cursor: pointer;
            font-size: 1.1rem;
        }

        .view-more-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(212, 175, 55, 0.5);
            color: #0a0a0a;
        }

        .text-center {
            text-align: center;
            margin-top: 3rem;
        }

        /* LIGHTBOX MODAL */
        .lightbox-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.95);
            z-index: 10000;
            justify-content: center;
            align-items: center;
            padding: 2rem;
        }

        .lightbox-modal.active {
            display: flex;
        }

        .lightbox-content {
            max-width: 90%;
            max-height: 90vh;
            position: relative;
        }

        .lightbox-content img {
            max-width: 100%;
            max-height: 90vh;
            border-radius: 10px;
            box-shadow: 0 20px 60px rgba(212, 175, 55, 0.5);
        }

        .lightbox-close {
            position: absolute;
            top: -50px;
            right: 0;
            background: #D4AF37;
            color: #0a0a0a;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 1.5rem;
            transition: all 0.3s ease;
        }

        .lightbox-close:hover {
            transform: rotate(90deg);
            background: #F5D068;
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .gallery-header h1 {
                font-size: 2.5rem;
            }

            .gallery-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 1rem;
            }

            .gallery-item {
                height: 280px;
            }
        }

        @media (max-width: 480px) {
            .gallery-grid {
                grid-template-columns: 1fr;
            }

            .gallery-item {
                height: 300px;
            }
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <!-- GALLERY HEADER -->
    <section class="gallery-header">
        <div class="container">
            <h1><i class="fas fa-images"></i> GALLERY</h1>
            <p>Explore Our Premium Billiard Venue</p>
        </div>
    </section>

    <!-- GALLERY GRID -->
    <div class="gallery-container">
        <div class="gallery-grid" id="galleryGrid">
            <!-- Gallery Item 1 -->
            <div class="gallery-item" onclick="openLightbox('images/gallery1.jpg')">
                <img src="images/gallery1.jpg" alt="Interior View">
                <div class="gallery-overlay">
                    <h5>Interior View</h5>
                    <p>Modern dan nyaman untuk pengalaman terbaik</p>
                </div>
            </div>

            <!-- Gallery Item 2 -->
            <div class="gallery-item" onclick="openLightbox('images/gallery2.jpg')">
                <img src="images/gallery2.jpg" alt="Premium Tables">
                <div class="gallery-overlay">
                    <h5>Premium Tables</h5>
                    <p>Meja billiard standar internasional</p>
                </div>
            </div>

            <!-- Gallery Item 3 -->
            <div class="gallery-item" onclick="openLightbox('images/gallery3.jpg')">
                <img src="images/gallery3.jpg" alt="Lounge Area">
                <div class="gallery-overlay">
                    <h5>Lounge Area</h5>
                    <p>Ruang tunggu yang comfortable</p>
                </div>
            </div>

            <!-- Gallery Item 4 -->
            <div class="gallery-item" onclick="openLightbox('images/gallery4.jpg')">
                <img src="images/gallery4.jpg" alt="Night View">
                <div class="gallery-overlay">
                    <h5>Night View</h5>
                    <p>Suasana malam yang elegan</p>
                </div>
            </div>

            <!-- Gallery Item 5 -->
            <div class="gallery-item" onclick="openLightbox('images/gallery1.jpg')">
                <img src="images/gallery1.jpg" alt="VIP Room">
                <div class="gallery-overlay">
                    <h5>VIP Room</h5>
                    <p>Privasi maksimal untuk pengalaman eksklusif</p>
                </div>
            </div>

            <!-- Gallery Item 6 -->
            <div class="gallery-item" onclick="openLightbox('images/gallery2.jpg')">
                <img src="images/gallery2.jpg" alt="Tournament Setup">
                <div class="gallery-overlay">
                    <h5>Tournament Setup</h5>
                    <p>Fasilitas turnamen profesional</p>
                </div>
            </div>

            <!-- Gallery Item 7 -->
            <div class="gallery-item" onclick="openLightbox('images/gallery3.jpg')">
                <img src="images/gallery3.jpg" alt="F&B Area">
                <div class="gallery-overlay">
                    <h5>F&B Area</h5>
                    <p>Makanan dan minuman berkualitas</p>
                </div>
            </div>

            <!-- Gallery Item 8 -->
            <div class="gallery-item" onclick="openLightbox('images/gallery4.jpg')">
                <img src="images/gallery4.jpg" alt="Parking Area">
                <div class="gallery-overlay">
                    <h5>Parking Area</h5>
                    <p>Parkir luas dan aman</p>
                </div>
            </div>

            <!-- Gallery Item 9 -->
            <div class="gallery-item" onclick="openLightbox('images/gallery1.jpg')">
                <img src="images/gallery1.jpg" alt="Reception">
                <div class="gallery-overlay">
                    <h5>Reception Desk</h5>
                    <p>Pelayanan profesional dan ramah</p>
                </div>
            </div>
        </div>

        <div class="text-center">
            <button class="view-more-btn" onclick="loadMore()">
                <i class="fas fa-plus-circle"></i> VIEW MORE
            </button>
        </div>
    </div>

    <!-- LIGHTBOX MODAL -->
    <div class="lightbox-modal" id="lightboxModal" onclick="closeLightbox()">
        <div class="lightbox-content" onclick="event.stopPropagation()">
            <div class="lightbox-close" onclick="closeLightbox()">
                <i class="fas fa-times"></i>
            </div>
            <img id="lightboxImage" src="" alt="Gallery Image">
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Lightbox Functions
        function openLightbox(imageSrc) {
            const modal = document.getElementById('lightboxModal');
            const image = document.getElementById('lightboxImage');
            image.src = imageSrc;
            modal.classList.add('active');
        }

        function closeLightbox() {
            const modal = document.getElementById('lightboxModal');
            modal.classList.remove('active');
        }

        // Close lightbox with ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeLightbox();
            }
        });

        // Load More Function
        function loadMore() {
            alert('Feature coming soon! More photos will be added.');
        }
    </script>
</body>
</html>
