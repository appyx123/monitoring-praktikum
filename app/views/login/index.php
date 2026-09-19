<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Login — Monitoring Praktikum</title>
    <link rel="stylesheet" href="<?= BASEURL;?>/public/template/plugins/fontawesome-free/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        /* Background slideshow */
        .bg-slide {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            opacity: 0;
            transition: opacity 1.5s ease-in-out;
            z-index: 0;
        }
        .bg-slide.active {
            opacity: 1;
        }

        /* Dark overlay di atas semua slide */
        .bg-overlay {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: linear-gradient(
                135deg,
                rgba(10, 10, 30, 0.75) 0%,
                rgba(15, 30, 60, 0.65) 50%,
                rgba(30, 10, 60, 0.70) 100%
            );
            z-index: 1;
        }

        /* Animated background blobs */
        body::before,
        body::after {
            content: '';
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.15;
            animation: float 8s ease-in-out infinite;
        }
        body::before {
            width: 500px;
            height: 500px;
            background: #7c3aed;
            top: -150px;
            left: -100px;
            animation-delay: 0s;
        }
        body::after {
            width: 400px;
            height: 400px;
            background: #2563eb;
            bottom: -100px;
            right: -100px;
            animation-delay: 4s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) scale(1); }
            50% { transform: translateY(-30px) scale(1.05); }
        }

        /* Main card container */
        .login-container {
            width: 100%;
            max-width: 420px;
            padding: 20px;
            position: relative;
            z-index: 10;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.07);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 24px;
            padding: 44px 40px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.4);
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Logo area */
        .logo-area {
            text-align: center;
            margin-bottom: 32px;
        }

        .logo-area img {
            width: 80px;
            height: 80px;
            object-fit: contain;
            border-radius: 0;
            box-shadow: none;
            margin-bottom: 16px;
        }

        .logo-area h1 {
            font-size: 1.4rem;
            font-weight: 700;
            color: #fff;
            letter-spacing: -0.02em;
        }

        .logo-area p {
            font-size: 0.78rem;
            color: rgba(255, 255, 255, 0.5);
            margin-top: 4px;
        }

        /* Alert */
        .alert-area {
            margin-bottom: 20px;
        }
        .alert-danger-custom {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 0.82rem;
        }
        .alert-success-custom {
            background: rgba(34, 197, 94, 0.15);
            border: 1px solid rgba(34, 197, 94, 0.3);
            color: #86efac;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 0.82rem;
        }

        /* Input fields */
        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            font-size: 0.78rem;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 7px;
            letter-spacing: 0.03em;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper .icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.35);
            font-size: 0.85rem;
            pointer-events: none;
        }

        .form-control-login {
            width: 100%;
            height: 48px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 12px;
            padding: 0 42px 0 40px;
            color: #fff;
            font-size: 0.88rem;
            outline: none;
            transition: all 0.3s ease;
        }

        .form-control-login::placeholder {
            color: rgba(255, 255, 255, 0.25);
        }

        .form-control-login:focus {
            background: rgba(255, 255, 255, 0.12);
            border-color: rgba(124, 58, 237, 0.7);
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.2);
        }

        .toggle-pw {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.35);
            cursor: pointer;
            font-size: 0.85rem;
            transition: color 0.2s;
        }

        .toggle-pw:hover {
            color: rgba(255, 255, 255, 0.7);
        }

        /* Remember me */
        .remember-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 24px;
            margin-top: 4px;
        }

        .remember-row input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: #7c3aed;
            cursor: pointer;
        }

        .remember-row label {
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.55);
            cursor: pointer;
            user-select: none;
        }

        /* Submit button */
        .btn-login {
            width: 100%;
            height: 50px;
            background: linear-gradient(135deg, #7c3aed, #2563eb);
            border: none;
            border-radius: 12px;
            color: #fff;
            font-size: 0.92rem;
            font-weight: 600;
            letter-spacing: 0.03em;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            transition: left 0.5s ease;
        }

        .btn-login:hover::before {
            left: 100%;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(124, 58, 237, 0.4);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        /* Footer text */
        .footer-text {
            text-align: center;
            margin-top: 24px;
            font-size: 0.72rem;
            color: rgba(255, 255, 255, 0.3);
        }

        /* Responsive */
        @media (max-width: 576px) {
            .login-container {
                padding: 10px;
                max-width: 100%;
            }

            .login-card {
                padding: 30px 20px;
                border-radius: 16px;
            }

            .logo-area img {
                width: 60px;
                height: 60px;
                margin-bottom: 12px;
            }

            .logo-area h1 {
                font-size: 1.2rem;
            }

            .logo-area p {
                font-size: 0.7rem;
            }

            .form-control-login {
                height: 44px;
                font-size: 0.85rem;
            }

            .btn-login {
                height: 44px;
                font-size: 0.85rem;
            }
        }
        
        @media (max-width: 360px) {
            .login-card {
                padding: 24px 16px;
            }

            .logo-area h1 {
                font-size: 1.1rem;
            }
        }
    </style>
</head>
<body>

<!-- Background Slideshow -->
<div class="bg-slide active" style="background-image: url('<?= BASEURL ?>/public/img/background/CV.webp');"></div>
<div class="bg-slide" style="background-image: url('<?= BASEURL ?>/public/img/background/DS.webp');"></div>
<div class="bg-slide" style="background-image: url('<?= BASEURL ?>/public/img/background/IoT.webp');"></div>
<div class="bg-slide" style="background-image: url('<?= BASEURL ?>/public/img/background/Micro.webp');"></div>
<div class="bg-slide" style="background-image: url('<?= BASEURL ?>/public/img/background/StartUp.webp');"></div>

<!-- Dark Overlay -->
<div class="bg-overlay"></div>

<div class="login-container" style="position: relative; z-index: 10;">
    <div class="login-card">

        <!-- Logo -->
        <div class="logo-area">
            <img src="<?= BASEURL;?>/public/img/ICLabs.webp" alt="ICLabs Logo">
            <h1>Monitoring Praktikum</h1>
            <p>Integrated Computer Laboratorium System</p>
        </div>

        <!-- Flash message -->
        <div class="alert-area">
            <?php Flasher::flashLogin(); ?>
        </div>

        <!-- Form -->
        <form action="<?= BASEURL?>/Login/login" method="post" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

            <div class="form-group">
                <label for="username">Username / Email</label>
                <div class="input-wrapper">
                    <i class="fas fa-user icon"></i>
                    <input type="text"
                           id="username"
                           name="username"
                           class="form-control-login"
                           placeholder="Masukkan username"
                           value="<?= isset($data['remember_username']) ? htmlspecialchars($data['remember_username']) : '' ?>"
                           required
                           autocomplete="off">
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrapper">
                    <i class="fas fa-lock icon"></i>
                    <input type="password"
                           id="password"
                           name="password"
                           class="form-control-login"
                           placeholder="Masukkan password"
                           required>
                    <span class="toggle-pw" id="togglePassword">
                        <i class="fas fa-eye" id="eyeIcon"></i>
                    </span>
                </div>
            </div>

            <div class="remember-row">
                <input type="checkbox"
                       id="remember"
                       name="remember"
                       <?= isset($_COOKIE['remember_username']) ? 'checked' : '' ?>>
                <label for="remember">Ingat saya di perangkat ini</label>
            </div>

            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt mr-2"></i> Masuk
            </button>

        </form>

        <div class="footer-text">
            &copy; <?= date('Y') ?> ICLabs — Hak cipta dilindungi
        </div>

    </div>
</div>

<script>
    // === Slideshow ===
    const slides = document.querySelectorAll('.bg-slide');
    let current = 0;
    setInterval(() => {
        slides[current].classList.remove('active');
        current = (current + 1) % slides.length;
        slides[current].classList.add('active');
    }, 5000);

    // === Toggle Password ===
    const togglePassword = document.getElementById('togglePassword');
    const password = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');

    togglePassword.addEventListener('click', function () {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        eyeIcon.classList.toggle('fa-eye');
        eyeIcon.classList.toggle('fa-eye-slash');
    });
</script>

</body>
</html>