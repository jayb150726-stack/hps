<?php
session_start();
// Pastikan file config.php berisi koneksi database $conn
include 'config.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    $res = $conn->query("SELECT * FROM users WHERE username='$username'");
    if ($res->num_rows === 1) {
        $user = $res->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            header("Location: index.php");
            exit;
        } else {
            $error = "Password salah! Silakan coba lagi.";
        }
    } else {
        $error = "Username tidak ditemukan! Pastikan username Anda benar.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informasi Daftar Harga</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #5A67D8;
            /* A vibrant indigo blue */
            --primary-dark: #434190;
            /* Darker primary for text/headings */
            --light-bg: #F0F4F8;
            /* Light gray background */
            --card-bg: #FFFFFF;
            /* White card background */
            --text-dark: #1F2937;
            /* Dark gray for main text */
            --text-light: #6B7280;
            /* Lighter gray for secondary text */
            --border-color: #E5E7EB;
            /* Light border color */
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--light-bg);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            color: var(--text-dark);
            overflow: hidden;
            /* Prevent scrollbar from background effects */
        }

        /* Background Animated Circles (Optional, for extra flair) */
        .circles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
            /* Send to background */
        }

        .circles li {
            position: absolute;
            display: block;
            list-style: none;
            width: 20px;
            height: 20px;
            background: rgba(255, 255, 255, 0.2);
            animation: animate 25s linear infinite;
            bottom: -150px;
            border-radius: 50%;
            /* Make them circles */
        }

        .circles li:nth-child(1) {
            left: 25%;
            width: 80px;
            height: 80px;
            animation-delay: 0s;
        }

        .circles li:nth-child(2) {
            left: 10%;
            width: 20px;
            height: 20px;
            animation-delay: 2s;
            animation-duration: 12s;
        }

        .circles li:nth-child(3) {
            left: 70%;
            width: 20px;
            height: 20px;
            animation-delay: 4s;
        }

        .circles li:nth-child(4) {
            left: 40%;
            width: 60px;
            height: 60px;
            animation-delay: 0s;
            animation-duration: 18s;
        }

        .circles li:nth-child(5) {
            left: 65%;
            width: 20px;
            height: 20px;
            animation-delay: 0s;
        }

        .circles li:nth-child(6) {
            left: 75%;
            width: 110px;
            height: 110px;
            animation-delay: 3s;
        }

        .circles li:nth-child(7) {
            left: 35%;
            width: 150px;
            height: 150px;
            animation-delay: 7s;
        }

        .circles li:nth-child(8) {
            left: 50%;
            width: 25px;
            height: 25px;
            animation-delay: 15s;
            animation-duration: 45s;
        }

        .circles li:nth-child(9) {
            left: 20%;
            width: 15px;
            height: 15px;
            animation-delay: 2s;
            animation-duration: 35s;
        }

        .circles li:nth-child(10) {
            left: 85%;
            width: 150px;
            height: 150px;
            animation-delay: 0s;
            animation-duration: 11s;
        }

        @keyframes animate {
            0% {
                transform: translateY(0) rotate(0deg);
                opacity: 1;
                border-radius: 0;
            }

            100% {
                transform: translateY(-1000px) rotate(720deg);
                opacity: 0;
                border-radius: 50%;
            }
        }

        .login-wrapper {
            position: relative;
            /* Needed for z-index */
            z-index: 1;
            /* Bring to front of circles */
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 30px;
            /* Space between logo and form */
        }

        .app-logo {
            font-size: 3.5rem;
            color: var(--primary-color);
            text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.1);
            animation: fadeInDown 0.8s ease-out;
        }

        .app-logo i {
            margin-right: 15px;
        }

        .login-container {
            max-width: 420px;
            width: 100%;
            background: var(--card-bg);
            padding: 40px;
            border-radius: 1.25rem;
            /* More rounded corners */
            box-shadow: var(--shadow-lg);
            /* Stronger shadow */
            animation: fadeInUp 0.8s ease-out 0.2s backwards;
            /* Animate after logo */
            border: 1px solid var(--border-color);
        }

        .login-container h3 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-dark);
            margin-bottom: 30px;
            text-align: center;
            letter-spacing: -0.5px;
        }

        .form-control {
            border-radius: 0.75rem;
            /* More rounded input fields */
            padding: 0.9rem 1.25rem;
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
            background-color: var(--light-bg);
            /* Subtle background for inputs */
            color: var(--text-dark);
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(90, 103, 216, 0.15);
            /* Primary color light shadow */
            background-color: var(--card-bg);
            /* White on focus */
        }

        .form-label {
            font-weight: 500;
            color: var(--text-light);
            margin-bottom: 8px;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border: none;
            border-radius: 0.75rem;
            padding: 0.9rem 1.5rem;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-md);
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(90, 103, 216, 0.25);
            /* Enhanced shadow on hover */
        }

        .alert {
            border-radius: 0.75rem;
            font-weight: 500;
            animation: fadeInScale 0.6s ease-out;
            border: none;
            padding: 1rem 1.5rem;
            margin-bottom: 25px;
            background-color: #FEE2E2;
            /* Tailwind red-100 */
            color: #991B1B;
            /* Tailwind red-800 */
        }

        .text-center a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .text-center a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        /* Keyframe Animations */
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInScale {
            from {
                opacity: 0;
                transform: translateY(-10px) scale(0.98);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* Responsive Adjustments */
        @media (max-width: 576px) {
            .login-container {
                padding: 30px 25px;
                margin: 40px auto;
            }

            .app-logo {
                font-size: 3rem;
            }

            .login-container h3 {
                font-size: 1.8rem;
                margin-bottom: 25px;
            }

            .form-control {
                padding: 0.8rem 1rem;
            }

            .btn-primary {
                font-size: 1rem;
                padding: 0.8rem 1rem;
            }
        }
    </style>
</head>

<body>
    <div class="circles">
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
    </div>

    <div class="login-wrapper">
        <div class="app-logo">
            <i class="bi bi-box-fill"></i>Informasi Daftar Harga
        </div>
        <div class="login-container">
            <h3>Login Akun</h3>
            <?php if ($error) : ?>
                <div class="alert alert-danger" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            <form method="post">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input name="username" type="text" id="username" class="form-control" required autofocus autocomplete="username">
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <input name="password" type="password" id="password" class="form-control" required autocomplete="current-password">
                </div>
                <button class="btn btn-primary w-100 mb-3" type="submit">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Login
                </button>
            </form>
            <!-- <div class="text-center mt-3 text-light">
                Belum punya akun? <a href="register.php">Daftar di sini</a>
            </div> -->
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>