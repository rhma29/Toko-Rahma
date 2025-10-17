<?php
// public/index.php
require_once __DIR__ . '../config/database.php';
include __DIR__ . '../includes/header.php';

session_start();

// Jika belum login, arahkan ke halaman login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
  header("Location: /toko_rahma/public/login.php");
  exit;
}

// Total transaksi
$res = $conn->query("SELECT COUNT(*) AS total FROM transaksi");
$total_transaksi = $res->fetch_assoc()['total'] ?? 0;

// Total pendapatan
$res = $conn->query("SELECT COALESCE(SUM(total_harga),0) AS pendapatan FROM transaksi");
$total_pendapatan = $res->fetch_assoc()['pendapatan'] ?? 0;

// Barang terlaris
$res = $conn->query("
  SELECT b.nama_barang, SUM(t.jumlah) AS total_jual
  FROM transaksi t
  JOIN barang b ON t.id_barang = b.id_barang
  GROUP BY b.id_barang
  ORDER BY total_jual DESC
  LIMIT 1
");
$barang_terlaris = $res->fetch_assoc();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h2 class="mb-0 text-dark">Dashboard</h2>
  <!--span class="text-muted">Terakhir diperbarui: <?= date('d M Y, H:i') ?></span-->
</div>

<!-- 🔹 Statistik utama modern -->
<div class="row g-4 mb-5">
  
  <!-- Total Transaksi -->
  <div class="col-md-4">
    <div class="card border-0 shadow-sm h-100 p-4 d-flex align-items-center flex-row">
      <div class="icon-circle me-3 bg-primary-light text-primary">
        <i class="bi bi-receipt fs-4"></i>
      </div>
      <div>
        <small class="text-muted">Total Transaksi</small>
        <h5 class="fw-bold mb-1"><?= $total_transaksi ?></h5>
      </div>
    </div>
  </div>

  <!-- Total Pendapatan -->
  <div class="col-md-4">
    <div class="card border-0 shadow-sm h-100 p-4 d-flex align-items-center flex-row">
      <div class="icon-circle me-3 bg-success-light text-success">
        <i class="bi bi-cash-stack fs-4"></i>
      </div>
      <div>
        <small class="text-muted">Total Pendapatan</small>
        <h5 class="fw-bold text-success mb-1">Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></h5>
      </div>
    </div>
  </div>

  <!-- Barang Terlaris -->
  <div class="col-md-4">
    <div class="card border-0 shadow-sm h-100 p-4 d-flex align-items-center flex-row">
      <div class="icon-circle me-3 bg-warning-light text-warning">
        <i class="bi bi-star-fill fs-4"></i>
      </div>
      <div>
        <h6 class="fw-bold mb-1"><?= $barang_terlaris['nama_barang'] ?? '-' ?></h6>
        <small class="text-muted">Terjual <?= $barang_terlaris['total_jual'] ?? 0 ?> unit</small>
      </div>
    </div>
  </div>
</div>

<!-- 🔹 Tabel Transaksi Terbaru -->
<div class="card border-0 shadow-sm">
  <div class="card-header bg-white border-0 py-3 px-4 d-flex align-items-center justify-content-between">
    <h5 class="fw-bold text-dark mb-0"><i class="bi bi-clock-history me-2 text-primary"></i>Transaksi Terbaru</h5>
  </div>

  <div class="card-body px-4 pb-4">
    <?php
    $query = "
      SELECT 
        t.id_transaksi,
        p.nama_pembeli,
        b.nama_barang,
        t.jumlah,
        t.total_harga,
        DATE_FORMAT(t.tanggal, '%d-%m-%Y') AS tanggal
      FROM transaksi t
      JOIN pembeli p ON t.id_pembeli = p.id_pembeli
      JOIN barang b ON t.id_barang = b.id_barang
      ORDER BY t.id_transaksi DESC
      LIMIT 10
    ";
    $result = $conn->query($query);
    ?>

    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-primary">
          <tr>
            <th>ID Transaksi</th>
            <th>Nama Pembeli</th>
            <th>Nama Barang</th>
            <th>Jumlah</th>
            <th>Total Harga</th>
            <th>Tanggal</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
              <tr>
                <td class="text-dark text-uppercase"><?= htmlspecialchars($row['id_transaksi']) ?></td>
                <td><?= htmlspecialchars($row['nama_pembeli']) ?></td>
                <td class="text-uppercase"><?= htmlspecialchars($row['nama_barang']) ?></td>
                <td><?= htmlspecialchars($row['jumlah']) ?></td>
                <td class="text-success fw-semibold">Rp <?= number_format($row['total_harga'], 0, ',', '.') ?></td>
                <td class="text-dark"><?= htmlspecialchars($row['tanggal']) ?></td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="6" class="text-center text-muted py-4">
                Belum ada transaksi yang tercatat.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>





<?php include __DIR__ . '../includes/footer.php'; ?>
