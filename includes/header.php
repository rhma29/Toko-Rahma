<?php

// 🔍 Deteksi halaman aktif
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Toko Rahma - Aplikasi Penjualan</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- ✅ Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- ✅ Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <!-- ✅ Custom CSS -->
  <link rel="stylesheet" href="/toko_rahma/style.css">
</head>

<body class="bg-light">

<!-- 🔹 Navbar -->
<nav class="navbar navbar-expand-lg navbar-light shadow-sm mb-4 sticky-top bg-light">
  <div class="container">
    <a class="navbar-brand fw-medium" href="/toko_rahma/index.php">Toko Rahma</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto align-items-center">

        <li class="nav-item">
          <a class="nav-link <?= ($current_page == 'index.php') ? 'active' : '' ?>" href="/toko_rahma/index.php">Dashboard</a>
        </li>

        <li class="nav-item">
          <a class="nav-link <?= ($current_page == 'barang.php') ? 'active' : '' ?>" href="/toko_rahma/public/barang.php">Barang</a>
        </li>

        <li class="nav-item">
          <a class="nav-link <?= ($current_page == 'pembeli.php') ? 'active' : '' ?>" href="/toko_rahma/public/pembeli.php">Pembeli</a>
        </li>

        <li class="nav-item">
          <a class="nav-link <?= ($current_page == 'transaksi.php') ? 'active' : '' ?>" href="/toko_rahma/public/transaksi.php">Transaksi</a>
        </li>

        <!-- 🔒 Tombol Logout -->
        <li class="nav-item ms-3">
          <a href="/toko_rahma/public/logout.php" class="btn btn-outline-secondary btn-sm">
            <i class=""></i> Logout
          </a>
        </li>

      </ul>
    </div>
  </div>
</nav>

<div class="container">
