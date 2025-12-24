<?php
// register.php (dipatch: pendaftaran paksa role='user' — aman untuk dipublikasikan sementara)
include 'config.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $conn->real_escape_string(trim($_POST['username']));
    $password = $_POST['password'];

    // Paksa role menjadi 'user' untuk mencegah pembuatan admin lewat form publik
    $role = 'user';

    if ($username === '' || $password === '') {
        $error = "Username dan password wajib diisi.";
    } else {
        $cek = $conn->query("SELECT id FROM users WHERE username='$username'");
        if ($cek && $cek->num_rows > 0) {
            $error = "Username sudah digunakan! Silakan pilih username lain.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $insert_query = "INSERT INTO users (username, password, role) VALUES ('$username', '$hash', '$role')";
            if ($conn->query($insert_query)) {
                header("Location: login.php?registered=true");
                exit;
            } else {
                $error = "Terjadi kesalahan saat pendaftaran. Silakan coba lagi. (" . $conn->error . ")";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="d-flex align-items-center justify-content-center" style="min-height:100vh;">
    <div class="card p-4" style="width:420px;">
        <h3 class="mb-3">Daftar Akun Baru</h3>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input name="username" type="text" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input name="password" type="password" class="form-control" required>
            </div>

            <!-- NOTE: role tidak ditampilkan, diset otomatis jadi 'user' di server -->
            <button class="btn btn-success w-100" type="submit">Daftar</button>
        </form>
        <div class="mt-3 text-center">
            Sudah punya akun? <a href="login.php">Login</a>
        </div>
    </div>
</body>
</html>