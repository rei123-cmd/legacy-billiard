<?php
require_once 'config.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        header("Location: admin_index.php");
    } else {
        header("Location: index.php");
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Login - Legacy Billiard</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a2e 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .login-container {
            width: 100%;
            max-width: 450px;
        }

        .login-card {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            border: 3px solid #D4AF37;
            border-radius: 25px;
            padding: 3rem;
            box-shadow: 0 20px 60px rgba(212, 175, 55, 0.4);
            position: relative;
            overflow: hidden;
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(212, 175, 55, 0.1) 0%, transparent 70%);
            pointer-events: none;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2.5rem;
            position: relative;
            z-index: 2;
        }

        .login-logo {
            font-size: 4rem;
            color: #D4AF37;
            margin-bottom: 1rem;
            text-shadow: 0 0 30px rgba(212, 175, 55, 0.6);
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
                text-shadow: 0 0 30px rgba(212, 175, 55, 0.6);
            }
            50% {
                transform: scale(1.05);
                text-shadow: 0 0 40px rgba(212, 175, 55, 0.8);
            }
        }

        .login-header h2 {
            color: #D4AF37;
            font-weight: 900;
            font-size: 2rem;
            margin-bottom: 0.5rem;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .login-header p {
            color: #888;
            font-size: 0.95rem;
            font-weight: 500;
        }

        .login-form {
            position: relative;
            z-index: 2;
        }

        .form-group {
            margin-bottom: 1.8rem;
        }

        .form-label {
            color: #D4AF37;
            font-weight: 700;
            margin-bottom: 0.8rem;
            display: block;
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .form-control {
            width: 100%;
            padding: 1rem 1.2rem;
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(212, 175, 55, 0.3);
            border-radius: 12px;
            color: #fff;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #D4AF37;
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 20px rgba(212, 175, 55, 0.3);
        }

        .form-control::placeholder {
            color: #666;
        }

        .password-toggle {
            position: absolute;
            right: 1.2rem;
            top: 50%;
            transform: translateY(-50%);
            color: #D4AF37;
            cursor: pointer;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }

        .password-toggle:hover {
            color: #F5D068;
        }

        .input-group {
            position: relative;
        }

        .btn-login {
            width: 100%;
            padding: 1.2rem;
            background: linear-gradient(135deg, #D4AF37, #F5D068);
            border: none;
            border-radius: 12px;
            color: #000;
            font-weight: 700;
            font-size: 1.1rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 8px 20px rgba(212, 175, 55, 0.4);
        }

        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(212, 175, 55, 0.6);
            background: linear-gradient(135deg, #F5D068, #D4AF37);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .btn-login:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .login-footer {
            text-align: center;
            margin-top: 2rem;
            position: relative;
            z-index: 2;
        }

        .login-footer a {
            color: #D4AF37;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .login-footer a:hover {
            color: #F5D068;
            text-decoration: underline;
        }

        .login-footer p {
            color: #888;
            margin-top: 1.5rem;
            font-size: 0.9rem;
        }

        .alert {
            padding: 1rem 1.2rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-weight: 600;
            border: none;
            position: relative;
            z-index: 2;
        }

        .alert-success {
            background: linear-gradient(135deg, rgba(40, 167, 69, 0.2), rgba(40, 167, 69, 0.1));
            color: #28a745;
            border: 2px solid rgba(40, 167, 69, 0.5);
        }

        .alert-danger {
            background: linear-gradient(135deg, rgba(220, 53, 69, 0.2), rgba(220, 53, 69, 0.1));
            color: #dc3545;
            border: 2px solid rgba(220, 53, 69, 0.5);
        }

        .loading {
            display: none;
            text-align: center;
            color: #D4AF37;
            margin-top: 1rem;
        }

        .loading i {
            font-size: 2rem;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        @media (max-width: 576px) {
            .login-card {
                padding: 2rem;
            }

            .login-header h2 {
                font-size: 1.5rem;
            }

            .login-logo {
                font-size: 3rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">
                    <i class="fas fa-dice-d6"></i>
                </div>
                <h2>Legacy Billiard</h2>
                <p>Sign in to your account</p>
            </div>

            <!-- Alert Messages -->
            <div id="alertContainer"></div>

            <!-- Login Form -->
            <form id="loginForm" class="login-form">
                <div class="form-group">
                    <label class="form-label" for="email">
                        <i class="fas fa-envelope"></i> Email Address
                    </label>
                    <input 
                        type="email" 
                        class="form-control" 
                        id="email" 
                        name="email" 
                        placeholder="Enter your email"
                        required
                    >
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">
                        <i class="fas fa-lock"></i> Password
                    </label>
                    <div class="input-group">
                        <input 
                            type="password" 
                            class="form-control" 
                            id="password" 
                            name="password" 
                            placeholder="Enter your password"
                            required
                        >
                        <span class="password-toggle" onclick="togglePassword()">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </span>
                    </div>
                </div>

                <button type="submit" class="btn-login" id="loginBtn">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>

                <div class="loading" id="loading">
                    <i class="fas fa-spinner"></i>
                    <p>Logging in...</p>
                </div>
            </form>

            <div class="login-footer">
                <p>Don't have an account? <a href="register.php">Sign up here</a></p>
                <p style="margin-top: 1rem;">
                    <a href="index.php">
                        <i class="fas fa-arrow-left"></i> Back to Home
                    </a>
                </p>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS -->
    <script>
        // Toggle Password Visibility
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        // Show Alert Function
        function showAlert(message, type) {
            const alertContainer = document.getElementById('alertContainer');
            alertContainer.innerHTML = `
                <div class="alert alert-${type}">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                    ${message}
                </div>
            `;
            
            // Auto hide after 5 seconds
            setTimeout(() => {
                alertContainer.innerHTML = '';
            }, 5000);
        }

        // Login Form Submission
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const loginBtn = document.getElementById('loginBtn');
            const loading = document.getElementById('loading');
            
            // Validation
            if (!email || !password) {
                showAlert('Please fill in all fields!', 'danger');
                return;
            }

            // Email format validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                showAlert('Please enter a valid email address!', 'danger');
                return;
            }

            // Disable button and show loading
            loginBtn.disabled = true;
            loading.style.display = 'block';
            
            try {
                const formData = new FormData();
                formData.append('email', email);
                formData.append('password', password);
                
                const response = await fetch('process_login.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showAlert(data.message, 'success');
                    
                    // Redirect based on role
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1000);
                } else {
                    showAlert(data.message, 'danger');
                    loginBtn.disabled = false;
                    loading.style.display = 'none';
                }
            } catch (error) {
                console.error('Login error:', error);
                showAlert('An error occurred. Please try again!', 'danger');
                loginBtn.disabled = false;
                loading.style.display = 'none';
            }
        });

        // Auto-fill for testing (REMOVE IN PRODUCTION)
        console.log('%c LOGIN CREDENTIALS FOR TESTING ', 'background: #D4AF37; color: #000; font-size: 14px; font-weight: bold; padding: 10px;');
        console.log('%c Admin: admin@legacybilliard.com / password ', 'background: #28a745; color: #fff; font-size: 12px; padding: 5px;');
        console.log('%c User: filbert@gmail.com / (user password) ', 'background: #17a2b8; color: #fff; font-size: 12px; padding: 5px;');
    </script>
</body>
</html>
