// Main JavaScript untuk Legacy Billiard

document.addEventListener('DOMContentLoaded', function () {
    
    // Navbar scroll effect
    const navbar = document.querySelector('.navbar');
    window.addEventListener('scroll', function() {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });

    // Smooth scroll untuk semua link
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Gallery filter
    const filterButtons = document.querySelectorAll('.filter-buttons button');
    const galleryItems = document.querySelectorAll('.gallery-item');

    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            const filter = this.getAttribute('data-filter');
            
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');

            galleryItems.forEach(item => {
                if (filter === 'all' || item.classList.contains(filter)) {
                    item.style.display = 'block';
                    item.style.animation = 'fadeIn 0.6s ease-out';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });

    // Gallery image zoom
    galleryItems.forEach(img => {
        img.addEventListener('click', function() {
            galleryItems.forEach(other => {
                if (other !== img) other.classList.remove('clicked');
            });
            this.classList.toggle('clicked');
        });
    });

    // Booking page functionality
    if (document.getElementById('wadah_meja')) {
        setupBookingPage();
    }

    // Booking detail page functionality
    if (document.getElementById('nama_meja_terpilih')) {
        setupBookingDetailPage();
    }

    // Cart functionality
    if (document.getElementById('cart-items-container')) {
        loadCartItems();
        setupCartPage();
    }

    // Profile page
    if (document.getElementById('form-profile')) {
        setupProfilePage();
    }

    // Animated counters
    const counters = document.querySelectorAll('.stat-number');
    if (counters.length > 0) {
        animateCounters();
    }

    // Form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const inputs = form.querySelectorAll('input[required], select[required]');
            let valid = true;

            inputs.forEach(input => {
                if (!input.value.trim()) {
                    valid = false;
                    input.style.borderColor = '#dc3545';
                } else {
                    input.style.borderColor = '';
                }
            });

            if (!valid) {
                e.preventDefault();
                showAlert('Mohon lengkapi semua field yang diperlukan!', 'danger');
            }
        });
    });
});

// ============================================
// BOOKING PAGE FUNCTIONS
// ============================================

function setupBookingPage() {
    const tombolBsd = document.getElementById('tombol_bsd');
    const tombolTangerang = document.getElementById('tombol_tng');
    const tombolFnb = document.getElementById('tombol_fnb');
    const pemilihLantai = document.getElementById('pemilih_lantai');
    const wadahMeja = document.getElementById('wadah_meja');
    const wadahFnb = document.getElementById('wadah_fnb');
    const subJudulCabang = document.getElementById('subjudul_cabang');

    // Load booked tables dari server
    loadBookedTables();

    tombolBsd.addEventListener('click', () => handleBranchSelection('BSD', 1));
    tombolTangerang.addEventListener('click', () => handleBranchSelection('TNG', 2));
    tombolFnb.addEventListener('click', () => handleBranchSelection('FNB', null));

    // Floor selection
    const floorButtons = document.querySelectorAll('.btn-floor');
    floorButtons.forEach((button, index) => {
        button.addEventListener('click', function() {
            floorButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            loadTables(1, index + 1); // BSD, lantai yang dipilih
        });
    });

    // Table selection
    const semuaMeja = document.querySelectorAll('.table-box');
    const areaTombolLanjut = document.getElementById('area-tombol-lanjut');
    const tombolLanjut = document.getElementById('tombol_lanjut');

    semuaMeja.forEach(meja => {
        meja.addEventListener('click', function() {
            if (this.classList.contains('booked')) {
                showAlert('Maaf, meja ini sudah dibooking!', 'warning');
                return;
            }

            semuaMeja.forEach(m => m.classList.remove('selected'));
            meja.classList.add('selected');
            
            const mejaId = meja.getAttribute('data-id');
            const namaMeja = meja.textContent;
            
            areaTombolLanjut.style.display = 'block';
            tombolLanjut.href = `booking-detail.php?meja_id=${mejaId}&nama=${encodeURIComponent(namaMeja)}`;
        });
    });

    function handleBranchSelection(branch, cabangId) {
        const allBranchButtons = document.querySelectorAll('.btn-branch, .btn-fnb');
        wadahMeja.classList.add('d-none');
        pemilihLantai.classList.add('d-none');
        wadahFnb.classList.add('d-none');

        if (branch === 'BSD') {
            wadahMeja.classList.remove('d-none');
            pemilihLantai.classList.remove('d-none');
            subJudulCabang.textContent = 'BSD';
            setActiveButton(allBranchButtons, tombolBsd);
            loadTables(cabangId, 1);
        } else if (branch === 'TNG') {
            wadahMeja.classList.remove('d-none');
            subJudulCabang.textContent = 'Tangerang Kota';
            setActiveButton(allBranchButtons, tombolTangerang);
            loadTables(cabangId, 1);
        } else if (branch === 'FNB') {
            wadahFnb.classList.remove('d-none');
            subJudulCabang.textContent = 'Food & Beverage';
            setActiveButton(allBranchButtons, tombolFnb);
        }
    }

    // Initialize with BSD
    tombolBsd.click();
}

function loadTables(cabangId, lantai) {
    const wadahMeja = document.getElementById('wadah_meja');
    
    fetch(`get_tables.php?cabang_id=${cabangId}&lantai=${lantai}`)
        .then(response => response.json())
        .then(data => {
            wadahMeja.innerHTML = '';
            data.forEach(meja => {
                const mejaBox = document.createElement('div');
                mejaBox.className = 'table-box';
                mejaBox.setAttribute('data-id', meja.id);
                mejaBox.setAttribute('data-table', meja.nama_meja);
                mejaBox.textContent = meja.nama_meja;
                
                if (meja.status === 'terisi') {
                    mejaBox.classList.add('booked');
                }
                
                mejaBox.addEventListener('click', function() {
                    if (this.classList.contains('booked')) {
                        showAlert('Maaf, meja ini sudah dibooking!', 'warning');
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
        .catch(error => console.error('Error loading tables:', error));
}

function loadBookedTables() {
    const selectedDate = document.getElementById('tanggal-booking')?.value || new Date().toISOString().split('T')[0];
    
    fetch(`get_booked_tables.php?tanggal=${selectedDate}`)
        .then(response => response.json())
        .then(data => {
            // Update status meja yang sudah dibooking
            data.forEach(booking => {
                const mejaBox = document.querySelector(`.table-box[data-id="${booking.meja_id}"]`);
                if (mejaBox) {
                    mejaBox.classList.add('booked');
                }
            });
        })
        .catch(error => console.error('Error loading booked tables:', error));
}

// ============================================
// BOOKING DETAIL PAGE FUNCTIONS
// ============================================

function setupBookingDetailPage() {
    const params = new URLSearchParams(window.location.search);
    const mejaId = params.get('meja_id');
    const namaMeja = params.get('nama');

    if (!mejaId || !namaMeja) {
        showAlert('Data meja tidak valid!', 'danger');
        setTimeout(() => window.location.href = 'booking.php', 2000);
        return;
    }

    document.getElementById('nama_meja_terpilih').textContent = `Anda Memesan: ${namaMeja}`;

    const wadahWaktu = document.getElementById('wadah_waktu');
    const semuaPaketBox = document.querySelectorAll('.paket-box');
    const tanggalInput = document.getElementById('tanggal-booking');
    const durasiInput = document.getElementById('durasi-main');
    const wadahDurasi = document.getElementById('wadah-durasi');
    const ringkasanBox = document.getElementById('ringkasan-pesanan');
    const formPemesanan = document.getElementById('form-pemesanan-detail');

    let state = {
        waktuTerpilih: null,
        paketTerpilih: 'perjam',
        tanggalTerpilih: null,
        durasiTerpilih: 2,
        mejaId: mejaId,
        namaMeja: namaMeja
    };

    const HARGA = {
        perjam: { weekday: 40000, weekend: 55000 },
        promo_siang: { weekday: 60000, weekend: 60000 },
        promo_malam: { weekday: 80000, weekend: 100000 }
    };

    // Set minimum date to today
    const today = new Date().toISOString().split('T')[0];
    tanggalInput.min = today;

    // Load available time slots
    function loadTimeSlots() {
        const tanggal = tanggalInput.value;
        if (!tanggal) return;

        fetch(`get_available_slots.php?meja_id=${mejaId}&tanggal=${tanggal}`)
            .then(response => response.json())
            .then(data => {
                wadahWaktu.innerHTML = '';
                
                for (let i = 0; i < 16; i++) {
                    const jam = 11 + i;
                    const jamFormat = (jam % 24).toString().padStart(2, '0') + ':00';
                    
                    const tombolWaktu = document.createElement('button');
                    tombolWaktu.className = 'btn-waktu';
                    tombolWaktu.textContent = jamFormat;
                    tombolWaktu.type = 'button';
                    
                    if (data.bookedSlots.includes(jamFormat)) {
                        tombolWaktu.classList.add('disabled');
                        tombolWaktu.disabled = true;
                    }
                    
                    tombolWaktu.addEventListener('click', function() {
                        if (this.disabled) return;
                        
                        document.querySelectorAll('.btn-waktu.selected').forEach(btn => btn.classList.remove('selected'));
                        this.classList.add('selected');
                        state.waktuTerpilih = jamFormat;
                        perbaruiRingkasan();
                    });
                    
                    wadahWaktu.appendChild(tombolWaktu);
                }
            })
            .catch(error => console.error('Error loading time slots:', error));
    }

    // Package selection
    semuaPaketBox.forEach(box => {
        box.addEventListener('click', function() {
            if (this.classList.contains('disabled')) return;
            
            semuaPaketBox.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            state.paketTerpilih = this.getAttribute('data-paket');
            
            wadahDurasi.style.display = (state.paketTerpilih === 'perjam') ? 'block' : 'none';
            
            if (state.paketTerpilih === 'promo_siang') {
                state.durasiTerpilih = 3;
            } else if (state.paketTerpilih === 'promo_malam') {
                state.durasiTerpilih = 3;
            }
            
            perbaruiRingkasan();
        });
    });

    tanggalInput.addEventListener('change', function() {
        state.tanggalTerpilih = this.value;
        state.waktuTerpilih = null;
        document.querySelectorAll('.btn-waktu.selected').forEach(btn => btn.classList.remove('selected'));
        loadTimeSlots();
        perbaruiRingkasan();
    });

    durasiInput.addEventListener('change', function() {
        state.durasiTerpilih = parseInt(this.value);
        perbaruiRingkasan();
    });

    function perbaruiRingkasan() {
        if (!state.waktuTerpilih || !state.tanggalTerpilih) {
            ringkasanBox.style.display = 'none';
            return;
        }

        ringkasanBox.style.display = 'block';
        
        const tanggal = new Date(state.tanggalTerpilih + 'T00:00:00');
        const hari = tanggal.getDay();
        const tipeHari = (hari === 5 || hari === 6) ? 'weekend' : 'weekday';
        
        let totalHarga = 0;
        let namaPaket = '';

        switch (state.paketTerpilih) {
            case 'perjam':
                totalHarga = HARGA.perjam[tipeHari] * state.durasiTerpilih;
                namaPaket = `Reguler (${state.durasiTerpilih} Jam)`;
                break;
            case 'promo_siang':
                totalHarga = HARGA.promo_siang[tipeHari];
                namaPaket = 'Promo Siang (3 Jam)';
                break;
            case 'promo_malam':
                totalHarga = HARGA.promo_malam[tipeHari];
                namaPaket = 'Promo Malam (3 Jam)';
                break;
        }

        document.getElementById('summary-paket').textContent = namaPaket;
        document.getElementById('summary-waktu').textContent = state.waktuTerpilih;
        document.getElementById('summary-harga').textContent = 'Rp ' + totalHarga.toLocaleString('id-ID');
    }

    // Form submission - Add to cart
    formPemesanan.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!state.waktuTerpilih || !state.tanggalTerpilih) {
            showAlert('Silakan pilih tanggal dan waktu mulai terlebih dahulu!', 'warning');
            return;
        }

        const formData = new FormData();
        formData.append('meja_id', state.mejaId);
        formData.append('tanggal_booking', state.tanggalTerpilih);
        formData.append('waktu_mulai', state.waktuTerpilih);
        formData.append('durasi_jam', state.durasiTerpilih);
        formData.append('paket_type', state.paketTerpilih);

        fetch('add_to_cart.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Berhasil ditambahkan ke keranjang!', 'success');
                setTimeout(() => {
                    window.location.href = 'cart.php';
                }, 1500);
            } else {
                showAlert(data.message || 'Terjadi kesalahan!', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Terjadi kesalahan!', 'danger');
        });
    });
}

// ============================================
// CART PAGE FUNCTIONS
// ============================================

function setupCartPage() {
    const btnCheckout = document.getElementById('btn-checkout');
    
    if (btnCheckout) {
        btnCheckout.addEventListener('click', function() {
            const cartItems = document.querySelectorAll('.cart-item');
            if (cartItems.length === 0) {
                showAlert('Keranjang Anda kosong!', 'warning');
                return;
            }

            if (confirm('Lanjutkan ke pembayaran?')) {
                window.location.href = 'checkout.php';
            }
        });
    }
}

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
                        <i class="fas fa-shopping-cart"></i>
                        <h3>Keranjang Anda Kosong</h3>
                        <p>Silakan pilih meja terlebih dahulu</p>
                        <a href="booking.php" class="btn-lanjutkan mt-3">Mulai Booking</a>
                    </div>
                `;
                return;
            }

            container.innerHTML = '';
            summaryContainer.innerHTML = '';
            let total = 0;

            data.items.forEach(item => {
                total += parseFloat(item.harga);

                const itemHTML = `
                    <div class="cart-item" data-id="${item.id}">
                        <div class="cart-item-header">
                            <div class="cart-item-name">${item.nama_meja}</div>
                            <button class="btn-remove" onclick="removeFromCart(${item.id})">Hapus</button>
                        </div>
                        <div class="cart-item-details">
                            <p><strong>Tanggal:</strong> ${formatDate(item.tanggal_booking)}</p>
                            <p><strong>Waktu:</strong> ${item.waktu_mulai}</p>
                            <p><strong>Durasi:</strong> ${item.durasi_jam} Jam</p>
                            <p><strong>Paket:</strong> ${formatPaketType(item.paket_type)}</p>
                        </div>
                        <div class="cart-item-price">Rp ${parseFloat(item.harga).toLocaleString('id-ID')}</div>
                    </div>
                `;
                container.innerHTML += itemHTML;

                summaryContainer.innerHTML += `
                    <div class="summary-row">
                        <span>${item.nama_meja} (${formatPaketType(item.paket_type)})</span>
                        <span>Rp ${parseFloat(item.harga).toLocaleString('id-ID')}</span>
                    </div>
                `;
            });

            totalEl.textContent = 'Rp ' + total.toLocaleString('id-ID');
        })
        .catch(error => console.error('Error loading cart:', error));
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
        } else {
            showAlert(data.message || 'Terjadi kesalahan!', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Terjadi kesalahan!', 'danger');
    });
}

// ============================================
// PROFILE PAGE FUNCTIONS
// ============================================

function setupProfilePage() {
    const formProfile = document.getElementById('form-profile');
    const avatarInput = document.getElementById('foto-profil');
    const avatarPreview = document.getElementById('avatar-preview');

    if (avatarInput && avatarPreview) {
        avatarInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    avatarPreview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    }

    if (formProfile) {
        formProfile.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('update_profile.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('Profil berhasil diperbarui!', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert(data.message || 'Terjadi kesalahan!', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Terjadi kesalahan!', 'danger');
            });
        });
    }
}

// ============================================
// UTILITY FUNCTIONS
// ============================================

function setActiveButton(buttons, activeButton) {
    buttons.forEach(btn => btn.classList.remove('active'));
    activeButton.classList.add('active');
}

function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.textContent = message;
    alertDiv.style.position = 'fixed';
    alertDiv.style.top = '100px';
    alertDiv.style.right = '20px';
    alertDiv.style.zIndex = '9999';
    alertDiv.style.minWidth = '300px';
    
    document.body.appendChild(alertDiv);
    
    setTimeout(() => {
        alertDiv.style.animation = 'fadeOut 0.5s ease-out';
        setTimeout(() => alertDiv.remove(), 500);
    }, 3000);
}

function formatDate(dateString) {
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('id-ID', options);
}

function formatPaketType(type) {
    const types = {
        'perjam': 'Per Jam',
        'promo_siang': 'Promo Siang',
        'promo_malam': 'Promo Malam'
    };
    return types[type] || type;
}

function animateCounters() {
    const counters = document.querySelectorAll('.stat-number');
    
    counters.forEach(counter => {
        const target = parseInt(counter.getAttribute('data-target') || counter.textContent);
        const duration = 2000;
        const step = target / (duration / 16);
        let current = 0;
        
        const timer = setInterval(() => {
            current += step;
            if (current >= target) {
                counter.textContent = target;
                clearInterval(timer);
            } else {
                counter.textContent = Math.floor(current);
            }
        }, 16);
    });
}

// Add fade out animation to CSS
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeOut {
        from {
            opacity: 1;
            transform: translateX(0);
        }
        to {
            opacity: 0;
            transform: translateX(20px);
        }
    }
`;

// Promo booking functionality
document.querySelectorAll('.btn-book-promo').forEach(button => {
    button.addEventListener('click', function() {
        const promoType = this.getAttribute('data-promo-type');
        
        if (!isLoggedIn()) {
            showNotification('Silakan login terlebih dahulu', 'warning');
            setTimeout(() => {
                window.location.href = 'login.php?redirect=booking.php?promo=' + promoType;
            }, 1500);
            return;
        }
        
        window.location.href = 'booking.php?promo=' + promoType;
    });
});

// Apply promo code in checkout
function applyPromoCode() {
    const promoInput = document.getElementById('promo-code-input');
    const promoCode = promoInput ? promoInput.value.trim() : '';
    
    if (!promoCode) {
        showNotification('Masukkan kode promo', 'warning');
        return;
    }
    
    const totalHarga = parseFloat(document.getElementById('total-harga').getAttribute('data-total'));
    
    fetch('validate_promo.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `kode_promo=${encodeURIComponent(promoCode)}&total_harga=${totalHarga}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            
            // Update display
            document.getElementById('diskon-amount').textContent = 'Rp ' + formatCurrency(data.diskon);
            document.getElementById('total-akhir').textContent = 'Rp ' + formatCurrency(data.total_akhir);
            document.getElementById('promo-applied').value = promoCode;
            document.getElementById('promo-id').value = data.promo_id;
            
            // Show applied promo
            const promoAppliedDiv = document.querySelector('.promo-applied-info');
            if (promoAppliedDiv) {
                promoAppliedDiv.style.display = 'block';
                promoAppliedDiv.querySelector('.applied-code').textContent = promoCode;
            }
            
            promoInput.disabled = true;
            document.querySelector('.btn-apply-promo').style.display = 'none';
            document.querySelector('.btn-remove-promo').style.display = 'inline-block';
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Terjadi kesalahan saat memvalidasi promo', 'error');
    });
}

function removePromoCode() {
    document.getElementById('promo-code-input').value = '';
    document.getElementById('promo-code-input').disabled = false;
    document.getElementById('promo-applied').value = '';
    document.getElementById('promo-id').value = '';
    
    // Reset display
    const totalAwal = parseFloat(document.getElementById('total-harga').getAttribute('data-total'));
    document.getElementById('diskon-amount').textContent = 'Rp 0';
    document.getElementById('total-akhir').textContent = 'Rp ' + formatCurrency(totalAwal);
    
    // Hide applied promo
    const promoAppliedDiv = document.querySelector('.promo-applied-info');
    if (promoAppliedDiv) {
        promoAppliedDiv.style.display = 'none';
    }
    
    document.querySelector('.btn-apply-promo').style.display = 'inline-block';
    document.querySelector('.btn-remove-promo').style.display = 'none';
    
    showNotification('Promo code dihapus', 'info');
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('id-ID').format(amount);
}

// Check if user is logged in
function isLoggedIn() {
    return document.body.classList.contains('logged-in') || 
           document.querySelector('.btn-profile') !== null;
}

document.head.appendChild(style);