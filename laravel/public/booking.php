<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Table - Legacy Billiard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <?php include 'navbar.php'; ?>

    <main class="booking-page">
        <div class="container my-5">
            <div class="text-center mb-4 fade-in">
                <h2 class="booking-title">Arena Billiard</h2>
                <p class="booking-subtitle" id="subjudul_cabang">BSD</p>
            </div>

            <div class="d-flex justify-content-center align-items-center mb-4 branch-selection slide-in-left">
                <button class="btn btn-branch active" id="tombol_bsd" data-cabang="1">
                    <i class="fas fa-building"></i> BSD
                </button>
                <button class="btn btn-branch" id="tombol_tng" data-cabang="2">
                    <i class="fas fa-building"></i> Tangerang Kota
                </button>
                <button class="btn btn-fnb" id="tombol_fnb">
                    <i class="fas fa-utensils"></i> FNB
                </button>
            </div>

            <div class="d-flex justify-content-center align-items-center mb-5 floor-selection" id="pemilih_lantai">
                <button class="btn btn-floor active" data-lantai="1">
                    <i class="fas fa-layer-group"></i> Lantai 1
                </button>
                <button class="btn btn-floor" data-lantai="2">
                    <i class="fas fa-layer-group"></i> Lantai 2
                </button>
                <button class="btn btn-floor" data-lantai="3">
                    <i class="fas fa-layer-group"></i> Lantai 3
                </button>
            </div>

            <div class="table-grid slide-in-right" id="wadah_meja">
                <!-- Tables will be loaded here -->
            </div>

            <div class="fnb-container text-center d-none" id="wadah_fnb">
                <h3 class="fnb-title"><i class="fas fa-utensils"></i> Food & Beverage Menu</h3>
                <p class="mt-3 mb-4">Lihat daftar menu makanan dan minuman kami yang tersedia.</p>
                <a href="https://drive.google.com/drive/folders/1EbuhOuImuwlAeqLJpI1S46zctBfuEs6f" 
                   target="_blank" rel="noopener noreferrer" class="btn-fnb-menu">
                    <i class="fas fa-book-open"></i> Lihat Menu FNB
                </a>
            </div>

            <div class="text-center mt-5" id="area-tombol-lanjut" style="display: none;">
                <a href="#" id="tombol_lanjut" class="btn-lanjutkan">
                    <i class="fas fa-arrow-right"></i> Lanjutkan ke Detail Pesanan
                </a>
            </div>
        </div>
    </main>

    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    let selectedCabang = 1;
    let selectedLantai = 1;

    document.addEventListener('DOMContentLoaded', function() {
        loadTables(selectedCabang, selectedLantai);

        // Branch selection
        document.querySelectorAll('.btn-branch').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.btn-branch, .btn-fnb').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                selectedCabang = this.dataset.cabang;
                document.getElementById('subjudul_cabang').textContent = this.textContent.trim();
                
                document.getElementById('wadah_meja').classList.remove('d-none');
                document.getElementById('pemilih_lantai').classList.remove('d-none');
                document.getElementById('wadah_fnb').classList.add('d-none');
                
                // Reset floor selection
                selectedLantai = 1;
                document.querySelectorAll('.btn-floor').forEach((b, i) => {
                    b.classList.toggle('active', i === 0);
                });
                
                // Hide floor selection for Tangerang (only 1 floor)
                if (selectedCabang == 2) {
                    document.getElementById('pemilih_lantai').style.display = 'none';
                } else {
                    document.getElementById('pemilih_lantai').style.display = 'flex';
                }
                
                loadTables(selectedCabang, selectedLantai);
            });
        });

        // FNB button
        document.getElementById('tombol_fnb').addEventListener('click', function() {
            document.querySelectorAll('.btn-branch, .btn-fnb').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            document.getElementById('wadah_meja').classList.add('d-none');
            document.getElementById('pemilih_lantai').classList.add('d-none');
            document.getElementById('wadah_fnb').classList.remove('d-none');
            document.getElementById('area-tombol-lanjut').style.display = 'none';
            document.getElementById('subjudul_cabang').textContent = 'Food & Beverage';
        });

        // Floor selection
        document.querySelectorAll('.btn-floor').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.btn-floor').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                selectedLantai = this.dataset.lantai;
                loadTables(selectedCabang, selectedLantai);
            });
        });
    });

    function loadTables(cabangId, lantai) {
        const wadahMeja = document.getElementById('wadah_meja');
        wadahMeja.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin fa-3x text-gold"></i></div>';
        
        fetch(`get_tables.php?cabang_id=${cabangId}&lantai=${lantai}`)
            .then(response => response.json())
            .then(data => {
                wadahMeja.innerHTML = '';
                
                if (data.length === 0) {
                    wadahMeja.innerHTML = '<p class="text-center text-muted">Tidak ada meja tersedia</p>';
                    return;
                }
                
                data.forEach((meja, index) => {
                    const mejaBox = document.createElement('div');
                    mejaBox.className = 'table-box';
                    mejaBox.style.animationDelay = `${index * 0.05}s`;
                    mejaBox.setAttribute('data-id', meja.id);
                    mejaBox.setAttribute('data-table', meja.nama_meja);
                    
                    mejaBox.innerHTML = `
                        <i class="fas fa-dice-d6 table-icon"></i>
                        <div class="table-name">${meja.nama_meja}</div>
                        <div class="table-status">${meja.status === 'tersedia' ? 'Tersedia' : 'Terisi'}</div>
                    `;
                    
                    if (meja.status === 'terisi') {
                        mejaBox.classList.add('booked');
                    }
                    
                    mejaBox.addEventListener('click', function() {
                        if (this.classList.contains('booked')) {
                            showAlert('Maaf, meja ini sedang tidak tersedia!', 'warning');
                            return;
                        }
                        
                        document.querySelectorAll('.table-box').forEach(m => m.classList.remove('selected'));
                        this.classList.add('selected');
                        
                        const areaTombolLanjut = document.getElementById('area-tombol-lanjut');
                        const tombolLanjut = document.getElementById('tombol_lanjut');
                        
                        areaTombolLanjut.style.display = 'block';
                        tombolLanjut.href = `booking-detail.php?meja_id=${meja.id}&nama=${encodeURIComponent(meja.nama_meja)}`;
                    });
                    
                    wadahMeja.appendChild(mejaBox);
                });
            })
            .catch(error => {
                console.error('Error:', error);
                wadahMeja.innerHTML = '<p class="text-center text-danger">Gagal memuat data meja</p>';
            });
    }

    function showAlert(message, type) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type}`;
        alertDiv.textContent = message;
        alertDiv.style.position = 'fixed';
        alertDiv.style.top = '100px';
        alertDiv.style.right = '20px';
        alertDiv.style.zIndex = '9999';
        alertDiv.style.minWidth = '300px';
        alertDiv.style.animation = 'slideInLeft 0.5s ease-out';
        
        document.body.appendChild(alertDiv);
        
        setTimeout(() => {
            alertDiv.style.animation = 'fadeOut 0.5s ease-out';
            setTimeout(() => alertDiv.remove(), 500);
        }, 3000);
    }
    </script>
</body>
</html>