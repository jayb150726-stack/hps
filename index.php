<?php

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'user';

include 'config.php'; // pastikan $conn tersedia

// Ambil daftar kategori unik
$kategori_result = $conn->query("SELECT DISTINCT kategori FROM barang ORDER BY kategori ASC");

// Ambil daftar tahun unik
$tahun_result = $conn->query("SELECT DISTINCT tahun FROM barang WHERE tahun IS NOT NULL ORDER BY tahun DESC");

// Ambil filter dari query string
$filter_kategori = isset($_GET['kategori']) ? $_GET['kategori'] : '';
$filter_tahun = isset($_GET['tahun']) ? $_GET['tahun'] : '';
$search_query = isset($_GET['search']) ? $_GET['search'] : '';

// Bangun klausa WHERE untuk filter dan pencarian (aman dengan real_escape_string)
$where_clauses = [];
if ($filter_kategori !== '' && $filter_kategori !== 'all') {
    $filter_kategori_esc = $conn->real_escape_string($filter_kategori);
    $where_clauses[] = "kategori='$filter_kategori_esc'";
}
if ($filter_tahun !== '' && $filter_tahun !== 'all') {
    $filter_tahun_esc = $conn->real_escape_string($filter_tahun);
    $where_clauses[] = "tahun='$filter_tahun_esc'";
}
if ($search_query !== '') {
    $search_query_esc = $conn->real_escape_string($search_query);
    $where_clauses[] = "(nama LIKE '%$search_query_esc%' OR kode LIKE '%$search_query_esc%')";
}

$where_sql = '';
if (!empty($where_clauses)) {
    $where_sql = ' WHERE ' . implode(' AND ', $where_clauses);
}

// --- Statistik (sama seperti sebelumnya, tapi pastikan query sederhana) ---

// Untuk Total Barang (menghitung semua tanpa filter untuk ringkasan)
$total_barang_query = $conn->query("SELECT COUNT(*) as total FROM barang");
$total_barang = $total_barang_query->fetch_assoc()['total'] ?? 0;

// Untuk Total Kategori Unik
$total_kategori_query = $conn->query("SELECT COUNT(DISTINCT kategori) as total FROM barang");
$total_kategori = $total_kategori_query->fetch_assoc()['total'] ?? 0;

// Untuk Harga Tertinggi
$harga_tertinggi_query = $conn->query("SELECT harga as max_harga, nama as nama_barang FROM barang ORDER BY harga DESC LIMIT 1");
$harga_tertinggi_data = $harga_tertinggi_query->fetch_assoc();
$harga_tertinggi = $harga_tertinggi_data ? $harga_tertinggi_data['max_harga'] : 0;
$nama_harga_tertinggi = $harga_tertinggi_data ? $harga_tertinggi_data['nama_barang'] : '-';

// Untuk Harga Terendah
$harga_terendah_query = $conn->query("SELECT harga as min_harga, nama as nama_barang FROM barang ORDER BY harga ASC LIMIT 1");
$harga_terendah_data = $harga_terendah_query->fetch_assoc();
$harga_terendah = $harga_terendah_data ? $harga_terendah_data['min_harga'] : 0;
$nama_harga_terendah = $harga_terendah_data ? $harga_terendah_data['nama_barang'] : '-';

// ---------------- Paginasi ----------------
$items_per_page = 20;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) $current_page = 1;

// Hitung total item sesuai filter (penting untuk paginasi ketika filter/search aktif)
$total_items_q = $conn->query("SELECT COUNT(*) AS total FROM barang" . $where_sql);
$total_items_row = $total_items_q->fetch_assoc();
$total_items = $total_items_row ? (int)$total_items_row['total'] : 0;

$total_pages = ($total_items > 0) ? ceil($total_items / $items_per_page) : 1;
if ($current_page > $total_pages) $current_page = $total_pages;

$offset = ($current_page - 1) * $items_per_page;

// Ambil data untuk halaman saat ini
$sql_paginated = "SELECT * FROM barang" . $where_sql . " ORDER BY id DESC LIMIT $items_per_page OFFSET $offset";
$result_paginated = $conn->query($sql_paginated);

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Informasi Daftar Harga</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* (CSS sama seperti file Anda; dipertahankan) */
        :root {
            --primary-color: #5A67D8;
            --primary-dark: #434190;
            --secondary-color: #6B7280;
            --accent-color: #F6AD55;
            --danger-color: #EF4444;
            --success-color: #10B981;
            --info-color: #3B82F6;
            --light-bg: #F9FAFB;
            --card-bg: #FFFFFF;
            --text-dark: #1F2937;
            --text-light: #6B7280;
            --border-color: #E5E7EB;
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
        }

        /* Tema gelap: variasi variabel ketika .dark-mode ada pada <html> */
        .dark-mode {
            --primary-color: #2b3467;
            --primary-dark: #1f2546;
            --secondary-color: #9CA3AF;
            --accent-color: #F6AD55;
            --danger-color: #F87171;
            --success-color: #34D399;
            --info-color: #60A5FA;
            --light-bg: #0b1220;
            --card-bg: #000000ff;
            --text-dark: #E6EDF3;
            --text-light: #94A3B8;
            --border-color: #1f2937;
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.35);
            --shadow-md: 0 6px 12px rgba(0, 0, 0, 0.45);
            --shadow-lg: 0 14px 28px rgba(0, 0, 0, 0.55);
        }

        body {
            background-color: var(--light-bg);
            font-family: 'Poppins', sans-serif;
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            padding-top: 70px;
            overflow-x: hidden;
            line-height: 1.6;
        }

        .navbar {
            background-color: var(--primary-color) !important;
            box-shadow: var(--shadow-lg);
            z-index: 1030;
            padding: 1rem 1.5rem;
            animation: slideInDown 0.6s ease-out;
        }

        .navbar-brand,
        .nav-link,
        .navbar-toggler-icon {
            color: white !important;
            font-weight: 600;
        }

        .navbar-brand {
            font-size: 1.8rem;
            display: flex;
            align-items: center;
            letter-spacing: -0.5px;
        }

        .navbar-brand i {
            margin-right: 10px;
            font-size: 1.7rem;
            transition: transform 0.3s ease;
        }

        .navbar-brand:hover i {
            transform: rotate(5deg) scale(1.05);
        }

        .navbar .btn-logout {
            background-color: #e22020ff;
            border: none;
            transition: all 0.3s ease;
            font-weight: 500;
            padding: 0.7rem 1.5rem;
            border-radius: 0.75rem;
            box-shadow: var(--shadow-sm);
            color: black;
        }

        .navbar .btn-logout:hover {
            background-color: #e22020ff;
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }

        /* Tombol Dark Mode di navbar */
        .btn-darkmode {
            border-radius: 0.75rem;
            background-color: rgba(255, 255, 255, 1);
            padding: 0.55rem 0.9rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: var(--shadow-sm);
            border: none;
        }

        .btn-darkmode .bi {
            font-size: 1.05rem;
        }

        .main-content {
            flex-grow: 1;
            padding: 40px 0;
        }

        .section-card {
            background-color: var(--card-bg);
            border-radius: 1rem;
            box-shadow: var(--shadow-md);
            padding: 35px;
            margin-bottom: 35px;
            transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
            border: 1px solid var(--border-color);
        }

        .section-card:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-10px);
        }

        h1 {
            color: var(--primary-color);
            font-weight: 700;
            text-align: center;
            margin-bottom: 100px;
            font-size: 3rem;
            letter-spacing: -1.5px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.08);
            animation: fadeIn 0.8s ease-out;
        }

        h2 {
            color: var(--text-dark);
            font-weight: 600;
            font-size: 2.2rem;
            margin-bottom: 30px;
            text-align: left;
            border-bottom: 2px solid var(--border-color);
            padding-bottom: 20px;
        }

        .metric-card {
            background: var(--card-bg) 0%, #ffffffff 100%;
            border-radius: 1rem;
            padding: 30px;
            box-shadow: var(--shadow-sm);
            transition: all 0.4s ease;
            display: center;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            text-align: center;
            height: 100%;
            animation: fadeInUp 0.7s ease-out backwards;
        }

        .metric-card .icon-wrapper {
            padding: 18px;
            display: inline-flex;
            margin-bottom: 20px;
            animation: bounceIn 0.8s ease-out;
            border-radius: 50%;
        }

        .metric-card.total-items .icon-wrapper {
            background-color: rgba(90, 103, 216, 0.1);
        }

        .metric-card.total-items .icon-wrapper i {
            color: var(--primary-color);
        }

        .metric-card.total-categories .icon-wrapper {
            background-color: rgba(16, 185, 129, 0.1);
        }

        .metric-card.max-price .icon-wrapper {
            background-color: rgba(246, 173, 85, 0.1);
        }

        .metric-card.min-price .icon-wrapper {
            background-color: rgba(239, 68, 68, 0.1);
        }

        .metric-card .icon-wrapper i {
            font-size: 3rem;
            color: var(--primary-color);
        }

        .metric-card .metric-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-dark);
            line-height: 1.2;
            margin-bottom: 8px;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        .metric-card .metric-label {
            font-size: 1.05rem;
            color: var(--text-light);
            text-align: center;
            font-weight: 500;
        }
        /* tombos  tambah item */
        .btn-main-action {
            font-weight: 600;
            transition: all 0.3s ease;
            border-radius: 0.75rem;
            padding: 0.8rem 2rem;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow-sm);
        }
        /* tombos  tambah item */
        .btn-add-item {
            background-color: #1fe618e5;
            border: none;
            color: white;
        }

        .btn-icon-action {
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.95rem;
            font-weight: 500;
            transition: all 0.2s ease;
            box-shadow: var(--shadow-sm);
        }

        .btn-edit {
            background-color: #187ff5ea;
            border: none;
            color: black;
        }

        .btn-delete {
            background-color: #e22020ff;
            border: none;
            color: black;
        }

        .alert {
            border-radius: 0.75rem;
            font-weight: 500;
            animation: fadeInScale 0.6s ease-out;
            border: none;
            padding: 1.2rem 2rem;
            margin-bottom: 30px;
        }

        .alert-success {
            background-color: #fcfcfcff;
            color: #065F46;
        }

        .alert-danger {
            background-color: #fcfcfcff;
            color: #e22020ff;
        }

        .form-control,
        .form-select {
            border-radius: 0.6rem;
            border: 1px solid var(--border-color);
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            padding: 0.8rem 1.2rem;
            color: var(--text-dark);
            background-color: var(--card-bg);
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(90, 103, 216, 0.15);
            outline: none;
        }

        .form-select option {
            padding-right: 2.5rem;
            position: relative;
            line-height: 1.5;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .form-select option[selected]::after,
        .form-select option:checked::after {
            content: none !important;
            display: none !important;
        }

        .form-select {
            padding-right: 3rem;
            background-position: right 0.75rem center;
        }

        .table {
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: var(--shadow-md);
            margin-top: 25px;
            border: 1px solid var(--border-color);
            background-color: var(--card-bg);
        }

        .table thead {
            background-color: var(--primary-dark);
            color: white;
            font-weight: 600;
        }

        .table th {
            padding: 18px 25px;
            vertical-align: middle;
            border-top: 1px solid var(--border-color);
        }
        .table td {
            padding: 18px 25px;
            vertical-align: middle;
            border-top: 1px solid var(--border-color);
        }

        .table tbody tr:nth-child(even) {
            background-color: rgba(90, 103, 216, 0.15);
        }

        .table tbody tr:hover {
            background: var(--card-bg) 0%, #000000ff 100%;
            border-radius: 1rem;
            padding: 30px;
            box-shadow: var(--card-bg);
            transition: all 0.4s ease;
            display: center;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            text-align: center;
            height: 100%;
            animation: fadeInUp 0.7s ease-out backwards;
        }

        .text-center.no-wrap {
            white-space: nowrap;
        }

        .badge-category {
            background-color: #187ff5ea;
            color: black;
            padding: 0.6em 1.2em;
            border-radius: 20px;
            font-weight: 1000;
            font-size: 0.9em;
            letter-spacing: 0.5px;
            transition: 0.2s ease;
        }

        .footer {
            background-color: var(--text-dark);
            color: rgba(90, 103, 216, 0.15);
            padding: 25px 0;
            text-align: center;
            margin-top: auto;
            box-shadow: 0 -4px 10px rgba(0, 0, 0, 0.05);
            font-size: 0.9em;
            font-weight: 400;
            letter-spacing: 0.2px;
        }

        .footer p {
            margin-bottom: 0;
            opacity: 0.9;
        }

        /* Keyframes etc. dikurangi untuk ringkasan */
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container-fluid px-4">
            <a class="navbar-brand" href="#">
                <i class="bi bi-box-fill"></i> Informasi Daftar Harga
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item me-2">
                        <!-- Tombol Dark Mode: berada di sebelah kiri tombol Keluar -->
                        <button id="darkModeToggle" class="btn btn-darkmode" aria-pressed="false" title="Toggle Dark Mode">
                            <i id="darkModeIcon" class="bi bi-moon-stars-fill"></i>
                            <span id="darkModeLabel" class="d-none d-sm-inline">MODE GELAP</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-logout" href="logout.php">
                            <i class="bi bi-box-arrow-right me-2"></i> Keluar
                        </a>
                    </li>
                </ul>
            </div>

    </nav>

    <div class="main-content">
        <div class="container">
            <h1>Dashboard</h1>

            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-<?= $_SESSION['message_type'] ?> alert-dismissible fade show" role="alert">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    <?= htmlspecialchars($_SESSION['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php
                unset($_SESSION['message']);
                unset($_SESSION['message_type']);
                ?>
            <?php endif; ?>

            <div class="section-card mb-5">
                <h2>Ringkasan</h2>
                <div class="row justify-content-center g-4">
                    <div class="col-lg-3 col-md-6 col-sm-6">
                        <div class="metric-card total-items">
                            <div class="icon-wrapper">
                                <i class="bi bi-boxes"></i>
                            </div>
                            <div>
                                <div class="metric-value"><?= number_format($total_barang, 0, ',', '.') ?></div>
                                <div class="metric-label">Total Item</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6">
                        <div class="metric-card total-categories">
                            <div class="icon-wrapper">
                                <i class="bi bi-tags"></i>
                            </div>
                            <div>
                                <div class="metric-value"><?= number_format($total_kategori, 0, ',', '.') ?></div>
                                <div class="metric-label">Kategori</div>
                                <div class="metric-label">( INSTALASI, TEKNIK SIPIL, DAN UMUM )</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6">
                        <div class="metric-card max-price">
                            <div class="icon-wrapper">
                                <i class="bi bi-graph-up"></i>
                            </div>
                            <div>
                                <div class="metric-value">Rp <?= number_format($harga_tertinggi, 0, ',', '.') ?></div>
                                <div class="metric-label">Nilai (<?= htmlspecialchars($nama_harga_tertinggi) ?>)</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6">
                        <div class="metric-card min-price">
                            <div class="icon-wrapper">
                                <i class="bi bi-graph-down"></i>
                            </div>
                            <div>
                                <div class="metric-value">Rp <?= number_format($harga_terendah, 0, ',', '.') ?></div>
                                <div class="metric-label">Nilai <?= htmlspecialchars($nama_harga_terendah) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="section-card">
                <h2>Manajemen Item</h2>
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
                    <?php if ($role === 'admin'): ?>
                        <a href="tambah.php" class="btn btn-main-action btn-add-item shadow-sm flex-grow-1 flex-md-grow-0">
                            <i class="bi bi-plus-circle-fill me-2"></i> Tambah Item
                        </a>
                    <?php endif; ?>

                    <div class="d-flex flex-column flex-md-row align-items-md-center gap-3 w-100 w-md-auto">
                        <form method="get" class="d-flex w-100 flex-grow-1">
                            <div class="input-group">
                                <input type="text" name="search" id="search" class="form-control" placeholder="Cari nama atau kode..." value="<?= htmlspecialchars($search_query) ?>" aria-label="Cari barang">
                                <button class="btn btn-outline-secondary" type="submit" title="Cari Barang">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </form>

                        <form method="get" class="d-flex align-items-center gap-2 flex-shrink-0">
                            <label for="kategori" class="form-label mb-0 fw-semibold text-nowrap">Kategori:</label>
                            <select name="kategori" id="kategori" class="form-select" onchange="this.form.submit()" aria-label="Filter berdasarkan kategori">
                                <option value="all" <?= ($filter_kategori == '' || $filter_kategori == 'all') ? 'selected' : '' ?>>Semua</option>
                                <?php $kategori_result->data_seek(0); ?>
                                <?php while ($row = $kategori_result->fetch_assoc()): ?>
                                    <option value="<?= htmlspecialchars($row['kategori']) ?>" <?= ($filter_kategori == $row['kategori']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($row['kategori']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </form>

                        <form method="get" class="d-flex align-items-center gap-2 flex-shrink-0">
                            <label for="tahun" class="form-label mb-0 fw-semibold text-nowrap">Tahun:</label>
                            <select name="tahun" id="tahun" class="form-select" onchange="this.form.submit()" aria-label="Filter berdasarkan tahun">
                                <option value="all" <?= ($filter_tahun == '' || $filter_tahun == 'all') ? 'selected' : '' ?>>Semua</option>
                                <?php $tahun_result->data_seek(0); ?>
                                <?php while ($row_tahun = $tahun_result->fetch_assoc()): ?>
                                    <option value="<?= htmlspecialchars($row_tahun['tahun']) ?>" <?= ($filter_tahun == $row_tahun['tahun']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($row_tahun['tahun']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </form>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-center">
                        <thead class="table-primary">
                            <tr>
                                <th scope="col">No</th>
                                <th scope="col">Kode</th>
                                <th scope="col">Nama Barang</th>
                                <th scope="col">Satuan</th>
                                <th scope="col">Kategori</th>
                                <th scope="col">Tahun</th>
                                <th scope="col">Harga (Rp)</th>
                                <?php if ($role === 'admin'): ?>
                                    <th scope="col" class="text-center" style="min-width: 170px;">Aksi</th>
                                <?php endif; ?>
                            </tr>
                        </thead>

                        <!-- tabel data barang -->
                        <tbody class="table-responsive">
                            <thead class = "table-dark"
                            <?php
                            $no = $offset + 1;
                            if ($result_paginated && $result_paginated->num_rows > 0):
                                while ($row = $result_paginated->fetch_assoc()):
                            ?>
                                    <tr>
                                        <td class="no-wrap"><?= $no++ ?></td>
                                        <td><?= htmlspecialchars($row['kode']) ?></td>
                                        <td><?= htmlspecialchars($row['nama']) ?></td>
                                        <td><?= htmlspecialchars($row['satuan']) ?></td>
                                        <td><span class="badge-category"><?= htmlspecialchars($row['kategori']) ?></span></td>
                                        <td><?= htmlspecialchars($row['tahun']) ?></td>
                                        <td>Rp <?= number_format((float)$row['harga'], 0, ',', '.') ?></td>
                                        <?php if ($role === 'admin'): ?>
                                            <td class="text-middle">
                                                <a href="edit.php?id=<?= urlencode($row['id']) ?>" class="btn btn-edit btn-icon-action me-2" title="Edit Data">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </a>
                                                <button type="button" class="btn btn-delete btn-icon-action"
                                                    data-bs-toggle="modal" data-bs-target="#deleteConfirmationModal"
                                                    data-id="<?= htmlspecialchars($row['id']) ?>"
                                                    data-nama="<?= htmlspecialchars($row['nama']) ?>"
                                                    title="Hapus Data">
                                                    <i class="bi bi-trash"></i> Hapus
                                                </button>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php
                                endwhile;
                            else:
                                ?>
                                <tr>
                                    <td colspan="<?= ($role === 'admin') ? '8' : '7' ?>" class="text-center py-4">
                                        <i class="bi bi-inbox"></i> Tidak ada Item ditemukan.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- Paginasi -->
        <nav aria-label="Navigasi halaman" class="d-flex justify-content-center mt-4">
            <ul class="pagination">
                <?php
                // Bangun string query untuk tautan paginasi (kecuali page)
                $query_params = [];
                if ($filter_kategori !== '') $query_params['kategori'] = $filter_kategori;
                if ($filter_tahun !== '') $query_params['tahun'] = $filter_tahun;
                if ($search_query !== '') $query_params['search'] = $search_query;

                // Helper untuk membuat link dengan query params
                function page_link($p, $query_params)
                {
                    $params = $query_params;
                    $params['page'] = $p;
                    return '?' . http_build_query($params);
                }

                // Tombol Sebelumnya
                if ($current_page > 1) {
                    echo '<li class="page-item"><a class="page-link" href="' . page_link($current_page - 1, $query_params) . '"><i class="bi bi-chevron-left"></i> Sebelumnya</a></li>';
                } else {
                    echo '<li class="page-item disabled"><span class="page-link"><i class="bi bi-chevron-left"></i> Sebelumnya</span></li>';
                }

                // Nomor halaman (tampilkan window kecil)
                $max_visible = 5;
                $start_page = max(1, $current_page - floor($max_visible / 2));
                $end_page = min($total_pages, $start_page + $max_visible - 1);
                if ($start_page > 1) {
                    echo '<li class="page-item"><a class="page-link" href="' . page_link(1, $query_params) . '">1</a></li>';
                    if ($start_page > 2) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
                for ($i = $start_page; $i <= $end_page; $i++) {
                    if ($i == $current_page) {
                        echo '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
                    } else {
                        echo '<li class="page-item"><a class="page-link" href="' . page_link($i, $query_params) . '">' . $i . '</a></li>';
                    }
                }
                if ($end_page < $total_pages) {
                    if ($end_page < $total_pages - 1) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    echo '<li class="page-item"><a class="page-link" href="' . page_link($total_pages, $query_params) . '">' . $total_pages . '</a></li>';
                }

                // Tombol Selanjutnya
                if ($current_page < $total_pages) {
                    echo '<li class="page-item"><a class="page-link" href="' . page_link($current_page + 1, $query_params) . '">Selanjutnya <i class="bi bi-chevron-right"></i></a></li>';
                } else {
                    echo '<li class="page-item disabled"><span class="page-link">Selanjutnya <i class="bi bi-chevron-right"></i></span></li>';
                }
                ?>
            </ul>
        </nav>

    </div>
    </div>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 PT Sinergi Perkebunan Nusantara.</p>
        </div>
    </footer>

    <!-- Modal Hapus -->
    <div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteConfirmationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteConfirmationModalLabel"><i class="bi bi-exclamation-octagon-fill me-2"></i> Konfirmasi Penghapusan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Apakah Anda yakin ingin menghapus barang "<strong id="itemNameToDelete"></strong>"?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="bi bi-x-circle me-2"></i> Batal</button>
                    <a href="#" id="confirmDeleteButton" class="btn btn-danger"><i class="bi bi-trash-fill me-2"></i> Hapus</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Modal delete setup
        document.addEventListener('DOMContentLoaded', function() {
            const deleteConfirmationModal = document.getElementById('deleteConfirmationModal');
            if (deleteConfirmationModal) {
                deleteConfirmationModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const itemId = button.getAttribute('data-id');
                    const itemName = button.getAttribute('data-nama');
                    const itemNameToDelete = deleteConfirmationModal.querySelector('#itemNameToDelete');
                    const confirmDeleteButton = deleteConfirmationModal.querySelector('#confirmDeleteButton');

                    itemNameToDelete.textContent = itemName;
                    confirmDeleteButton.href = `hapus.php?id=${encodeURIComponent(itemId)}`;
                });
            }
        });

        // Dark mode toggle
        (function() {
            const root = document.documentElement;
            const toggleBtn = document.getElementById('darkModeToggle');
            const toggleIcon = document.getElementById('darkModeIcon');
            const toggleLabel = document.getElementById('darkModeLabel');
            const storageKey = 'darkModeEnabled';

            function applyDarkMode(enabled, save = false) {
                if (enabled) {
                    root.classList.add('dark-mode');
                    if (toggleBtn) {
                        toggleBtn.setAttribute('aria-pressed', 'true');
                        toggleIcon.className = 'bi bi-sun-fill';
                        toggleLabel.textContent = 'MODE TERANG';
                        toggleBtn.classList.remove('btn-outline-light');
                    }
                } else {
                    root.classList.remove('dark-mode');
                    if (toggleBtn) {
                        toggleBtn.setAttribute('aria-pressed', 'false');
                        toggleIcon.className = 'bi bi-moon-stars-fill';
                        toggleLabel.textContent = 'MODE GELAP';
                        toggleBtn.classList.remove('btn-outline-light');
                    }
                }
                if (save) {
                    try {
                        localStorage.setItem(storageKey, enabled ? '1' : '0');
                    } catch (e) {
                        // ignore storage errors
                    }
                }
            }

            // Inisialisasi dari localStorage (jika tersedia)
            try {
                const saved = localStorage.getItem(storageKey);
                if (saved === '1') {
                    applyDarkMode(true, false);
                } else if (saved === '0') {
                    applyDarkMode(false, false);
                } else {
                    // Jika tidak ada preferensi, kita biarkan default (light)
                }
            } catch (e) {
                // ignore
            }

            if (toggleBtn) {
                toggleBtn.addEventListener('click', function() {
                    const isPressed = toggleBtn.getAttribute('aria-pressed') === 'true';
                    applyDarkMode(!isPressed, true);
                });
            }
        })();
    </script>
</body>

</html>

<?php
$conn->close();
?>