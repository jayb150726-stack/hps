<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // Menggunakan URL bersih
    header("Location: index"); // Perbaikan di sini
    exit;
}

include 'config.php';

// Pastikan ada ID barang yang dikirim dan valid
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    // Menggunakan URL bersih
    header("Location: index"); // Perbaikan di sini
    exit;
}

$id = intval($_GET['id']);

// Ambil data barang dari database
$result = $conn->query("SELECT * FROM barang WHERE id = $id");
if ($result->num_rows !== 1) {
    // Menggunakan URL bersih
    header("Location: index"); // Perbaikan di sini
    exit;
}

$data = $result->fetch_assoc(); // Data barang yang akan diedit

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kode     = $conn->real_escape_string($_POST['kode']);
    $nama     = $conn->real_escape_string($_POST['nama']);
    $satuan     = $conn->real_escape_string($_POST['satuan']);
    $kategori = $conn->real_escape_string($_POST['kategori']);
    $harga    = floatval($_POST['harga']);
    $tahun    = intval($_POST['tahun']); // Ambil nilai tahun dari formulir

    // Cek apakah kode diubah dan sudah dipakai oleh barang lain
    $cek = $conn->query("SELECT * FROM barang WHERE kode = '$kode' AND id != $id");
    if ($cek->num_rows > 0) {
        $error = "Kode barang sudah digunakan oleh barang lain!";
    } else {
        // Update query: tambahkan kolom 'tahun' ke dalam SET
        $sql = "UPDATE barang 
                SET kode = '$kode', nama = '$nama', satuan = '$satuan', kategori = '$kategori', harga = $harga, tahun = $tahun 
                WHERE id = $id";

        if ($conn->query($sql)) {
            // Setelah berhasil update, tambahkan pesan sukses ke sesi
            $_SESSION['message'] = "Data barang berhasil diperbarui!";
            $_SESSION['message_type'] = "success";
            // Menggunakan URL bersih
            header("Location: index"); // Perbaikan di sini
            exit;
        } else {
            $error = "Gagal memperbarui data: " . $conn->error; // Tampilkan error database untuk debugging
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Item</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #5A67D8;
            /* A vibrant indigo blue */
            --primary-dark: #434190;
            --secondary-color: #6B7280;
            /* Muted gray */
            --accent-color: #F6AD55;
            /* Soft orange */
            --danger-color: #EF4444;
            /* Tailwind red-500 */
            --success-color: #10B981;
            /* Tailwind green-500 */
            --info-color: #3B82F6;
            /* Tailwind blue-500 */
            --light-bg: #F9FAFB;
            /* Off-white background */
            --card-bg: #FFFFFF;
            --text-dark: #1F2937;
            /* Dark gray for main text */
            --text-light: #6B7280;
            /* Lighter gray for secondary text */
            --border-color: #E5E7EB;
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
        }

        body {
            background-color: var(--light-bg);
            font-family: 'Poppins', sans-serif;
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            /* Pusatkan konten vertikal */
            align-items: center;
            /* Pusatkan konten horizontal */
            padding: 40px 15px;
            /* Beri padding agar tidak terlalu mepet */
        }

        .container {
            background-color: var(--card-bg);
            border-radius: 1rem;
            box-shadow: var(--shadow-lg);
            padding: 2.5rem;
            max-width: 600px;
            /* Batasi lebar container */
            width: 100%;
            border: 1px solid var(--border-color);
            animation: fadeInScale 0.6s ease-out;
        }

        h2 {
            color: var(--primary-dark);
            font-weight: 700;
            margin-bottom: 2rem;
            text-align: center;
            font-size: 2.5rem;
            letter-spacing: -0.8px;
        }

        .form-label {
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        .form-control {
            border-radius: 0.6rem;
            border: 1px solid var(--border-color);
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            padding: 0.8rem 1.2rem;
            color: var(--text-dark);
            background-color: var(--card-bg);
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(90, 103, 216, 0.15);
            outline: none;
        }

        .btn {
            border-radius: 0.75rem;
            padding: 0.8rem 1.8rem;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-sm);
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }

        .btn-success {
            background-color: var(--success-color);
            border: none;
            color: white;
        }

        .btn-success:hover {
            background-color: #0E9F6E;
        }

        .btn-secondary {
            background-color: var(--secondary-color);
            border: none;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #5A606B;
        }

        .alert {
            border-radius: 0.75rem;
            font-weight: 500;
            animation: fadeInScale 0.6s ease-out;
            border: none;
            padding: 1.2rem 2rem;
            margin-bottom: 30px;
            background-color: #FEE2E2;
            /* Tailwind red-100 */
            color: #991B1B;
            /* Tailwind red-800 */
        }

        .alert .btn-close {
            box-shadow: none;
            font-size: 1rem;
            opacity: 0.7;
        }

        /* Animations */
        @keyframes fadeInScale {
            from {
                opacity: 0;
                transform: translateY(-20px) scale(0.98);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @media (max-width: 767.98px) {
            .container {
                padding: 1.5rem;
            }

            h2 {
                font-size: 2rem;
                margin-bottom: 1.5rem;
            }

            .btn {
                width: 100%;
                margin-bottom: 1rem;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h2><i class="bi bi-pencil-square me-2"></i> Edit Item</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form method="post" class="mt-3">
            <div class="mb-3">
                <label for="kode" class="form-label">Kode Barang</label>
                <input type="text" name="kode" id="kode" class="form-control" value="<?= htmlspecialchars($data['kode']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="nama" class="form-label">Nama Barang</label>
                <input type="text" name="nama" id="nama" class="form-control" value="<?= htmlspecialchars($data['nama']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="satuan" class="form-label">Satuan</label>
                <input type="text" name="satuan" id="satuan" class="form-control" value="<?= htmlspecialchars($data['satuan']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="kategori" class="form-label">Kategori</label>
                <input type="text" name="kategori" id="kategori" class="form-control" value="<?= htmlspecialchars($data['kategori']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="harga" class="form-label">Harga (Rp)</label>
                <input type="number" name="harga" id="harga" class="form-control" step="0.01" value="<?= htmlspecialchars($data['harga']) ?>" required min="0">
            </div>
            <div class="mb-4">
                <label for="tahun" class="form-label">Tahun</label>
                <input type="number" name="tahun" id="tahun" class="form-control"
                    value="<?= htmlspecialchars($data['tahun']) ?>" required min="1900" max="<?= date('Y') + 5 ?>">
            </div>
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button type="submit" class="btn btn-success"><i class="bi bi-save-fill me-2"></i> Simpan Perubahan</button>
                <a href="index" class="btn btn-secondary"><i class="bi bi-arrow-left-circle-fill me-2"></i> Batal</a>
            </div>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>