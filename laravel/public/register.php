<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Legacy Billiard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <main class="login-page">
        <div class="login-container">
            <form class="login-form" id="form-signup">
                <h2 class="login-title">SIGN UP</h2>
                <p class="login-subtitle">Buat akun baru Anda.</p>
                
                <div class="mb-3">
                    <label for="nama" class="form-label">Nama Lengkap</label>
                    <input type="text" class="form-control" id="nama" name="nama" required>
                </div>

                <div class="mb-3">
                    <label for="email-signup" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email-signup" name="email" required>
                </div>

                <div class="mb-3">
                    <label for="telepon" class="form-label">Nomor Telepon</label>
                    <input type="tel" class="form-control" id="telepon" name="telepon" required placeholder="+62">
                </div>
                
                <div class="mb-3">
                    <label for="password-signup" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password-signup" name="password" required>
                </div>

                <div class="mb-3">
                    <label for="confirm-password" class="form-label">Konfirmasi Password</label>
                    <input type="password" class="form-control" id="confirm-password" name="confirm_password" required>
                </div>
                
                <button type="submit" class="btn-login-submit">Daftar</button>
                
                <div class="register-link">
                    <p>Sudah punya akun? <a href="login.php">Masuk di sini</a></p>
                </div>
            </form>
        </div>
    </main>

    <?php include 'footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.getElementById('form-signup').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const password = document.getElementById('password-signup').value;
        const confirmPassword = document.getElementById('confirm-password').value;
        
        if (password !== confirmPassword) {
            showAlert('Password tidak cocok!', 'danger');
            return;
        }

        if (password.length < 6) {
            showAlert('Password minimal 6 karakter!', 'danger');
            return;
        }
        
        const formData = new FormData(this);
        
        fetch('process_signup.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Pendaftaran berhasil! Mengalihkan ke halaman login...', 'success');
                setTimeout(() => {
                    window.location.href = 'login.php';
                }, 2000);
            } else {
                showAlert(data.message || 'Pendaftaran gagal!', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Terjadi kesalahan!', 'danger');
        });
    });

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