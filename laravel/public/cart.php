<?php
require_once 'config.php';

if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang - Legacy Billiard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <main class="cart-page">
        <div class="container my-5">
            <h2 class="cart-title fade-in"><i class="fas fa-shopping-cart"></i> Keranjang Belanja</h2>
            
            <div class="row g-4 mt-4">
                <div class="col-lg-8">
                    <div class="cart-items-container" id="cart-items-container">
                        <div class="text-center">
                            <i class="fas fa-spinner fa-spin fa-3x text-gold"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="cart-summary slide-in-right">
                        <h3><i class="fas fa-receipt"></i> Ringkasan Pesanan</h3>
                        <hr class="gold-hr">
                        
                        <div id="cart-summary-items">
                            <!-- Summary items will be loaded here -->
                        </div>
                        
                        <hr class="gold-hr">
                        
                        <div class="summary-total">
                            <span>Total Pembayaran:</span>
                            <strong id="cart-total">Rp 0</strong>
                        </div>
                        
                        <button class="btn-checkout" id="btn-checkout">
                            <i class="fas fa-credit-card"></i> Lanjut ke Pembayaran
                        </button>
                        
                        <a href="booking.php" class="btn-continue-shopping">
                            <i class="fas fa-plus"></i> Tambah Booking Lagi
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function loadCartItems() {
        fetch('get_cart.php')
            .then(response => response.json())
            .then(data => {
                const container = document.getElementById('cart-items-container');
                const summaryContainer = document.getElementById('cart-summary-items');
                const totalEl = document.getElementById('cart-total');
                
                if (data.items.length === 0) {
                    container.innerHTML = `
                        <div class="empty-cart">
                            <i class="fas fa-shopping-cart fa-5x text-gold mb-4"></i>
                            <h3>Keranjang Anda Kosong</h3>
                            <p class="text-muted">Silakan pilih meja terlebih dahulu untuk memulai booking</p>
                            <a href="booking.php" class="btn-lanjutkan mt-3">
                                <i class="fas fa-arrow-right"></i> Mulai Booking
                            </a>
                        </div>
                    `;
                    summaryContainer.innerHTML = '<p class="text-muted">Tidak ada item</p>';
                    totalEl.textContent = 'Rp 0';
                    return;
                }

                container.innerHTML = '';
                summaryContainer.innerHTML = '';
                let total = 0;

                data.items.forEach((item, index) => {
                    total += parseFloat(item.harga);

                    const itemHTML = `
                        <div class="cart-item slide-in-left" style="animation-delay: ${index * 0.1}s" data-id="${item.id}">
                            <div class="cart-item-header">
                                <div class="cart-item-info">
                                    <i class="fas fa-dice-d6 text-gold"></i>
                                    <h4>${item.nama_meja}</h4>
                                </div>
                                <button class="btn-remove" onclick="removeFromCart(${item.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            <div class="cart-item-details">
                                <div class="detail-row">
                                    <i class="fas fa-calendar"></i>
                                    <span>${formatDate(item.tanggal_booking)}</span>
                                </div>
                                <div class="detail-row">
                                    <i class="fas fa-clock"></i>
                                    <span>${item.waktu_mulai} (${item.durasi_jam} Jam)</span>
                                </div>
                                <div class="detail-row">
                                    <i class="fas fa-box"></i>
                                    <span>${formatPaketType(item.paket_type)}</span>
                                </div>
                            </div>
                            <div class="cart-item-price">
                                <span class="price-label">Harga:</span>
                                <span class="price-value">Rp ${parseFloat(item.harga).toLocaleString('id-ID')}</span>
                            </div>
                        </div>
                    `;
                    container.innerHTML += itemHTML;

                    summaryContainer.innerHTML += `
                        <div class="summary-row">
                            <span>${item.nama_meja}</span>
                            <span>Rp ${parseFloat(item.harga).toLocaleString('id-ID')}</span>
                        </div>
                    `;
                });

                totalEl.textContent = 'Rp ' + total.toLocaleString('id-ID');
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('cart-items-container').innerHTML = 
                    '<div class="alert alert-danger">Gagal memuat keranjang</div>';
            });
    }

    function removeFromCart(cartId) {
        if (!confirm('Hapus item ini dari keranjang?')) return;

        fetch('remove_from_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `cart_id=${cartId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Item dihapus dari keranjang!', 'success');
                loadCartItems();
                updateCartCount();
            } else {
                showAlert(data.message || 'Terjadi kesalahan!', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Terjadi kesalahan!', 'danger');
        });
    }

    document.getElementById('btn-checkout').addEventListener('click', function() {
        window.location.href = 'checkout.php';
    });

    function formatDate(dateString) {
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        return new Date(dateString).toLocaleDateString('id-ID', options);
    }

    function formatPaketType(type) {
        const types = {
            'perjam': 'Reguler Per Jam',
            'promo_siang': 'Promo Siang (Happy Hour)',
            'promo_malam': 'Promo Malam'
        };
        return types[type] || type;
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

    loadCartItems();
    </script>
</body>
</html>