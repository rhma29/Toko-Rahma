<?php
require_once __DIR__ . '/../config/database.php';
include __DIR__ . '/../includes/header.php';

session_start();
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
  header("Location: /toko_rahma/public/login.php");
  exit;
}


// ======================= FILTER TANGGAL =======================
$tgl_awal  = $_GET['tgl_awal'] ?? '';
$tgl_akhir = $_GET['tgl_akhir'] ?? '';
$where = '';

if (!empty($tgl_awal) && !empty($tgl_akhir)) {
    $where = "WHERE t.tanggal BETWEEN '$tgl_awal' AND '$tgl_akhir'";
}

// ======================= TAMBAH TRANSAKSI =======================
if (isset($_POST['tambah'])) {
    $id_transaksi = strtoupper(trim($_POST['id_transaksi']));
    $id_pembeli   = $_POST['id_pembeli'] ?? '';
    $id_barang    = $_POST['id_barang'] ?? '';
    $jumlah       = intval($_POST['jumlah']);
    $tanggal      = $_POST['tanggal'] ?? date('Y-m-d');

    if (empty($id_transaksi) || empty($id_pembeli) || empty($id_barang)) {
        $error = "⚠️ Semua field wajib diisi!";
    } elseif ($jumlah <= 0) {
        $error = "⚠️ Jumlah harus lebih dari 0!";
    } else {
        $cekID = $conn->query("SELECT id_transaksi FROM transaksi WHERE id_transaksi = '$id_transaksi'");
        if ($cekID && $cekID->num_rows > 0) {
            $error = "❌ ID Transaksi '$id_transaksi' sudah digunakan. Gunakan ID lain!";
        } else {
            $barang = $conn->query("SELECT harga, stok FROM barang WHERE id_barang='$id_barang'")->fetch_assoc();
            $harga = $barang['harga'] ?? 0;
            $stok  = $barang['stok'] ?? 0;
            $total_harga = $harga * $jumlah;

            if ($jumlah > $stok) {
                $error = "⚠️ Jumlah melebihi stok ($stok)!";
            } else {
                $stmt = $conn->prepare("INSERT INTO transaksi (id_transaksi, id_pembeli, id_barang, jumlah, total_harga, tanggal) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssids", $id_transaksi, $id_pembeli, $id_barang, $jumlah, $total_harga, $tanggal);
                $stmt->execute();
                $stmt->close();

                $conn->query("UPDATE barang SET stok = stok - $jumlah WHERE id_barang='$id_barang'");
                $success = "✅ Transaksi berhasil ditambahkan!";
            }
        }
    }
}

// ======================= HAPUS TRANSAKSI =======================
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $get = $conn->query("SELECT id_barang, jumlah FROM transaksi WHERE id_transaksi='$id'")->fetch_assoc();
    if ($get) {
        $conn->query("UPDATE barang SET stok = stok + {$get['jumlah']} WHERE id_barang='{$get['id_barang']}'");
        $conn->query("DELETE FROM transaksi WHERE id_transaksi='$id'");
        $success = "🗑️ Transaksi berhasil dihapus!";
    } 
}

// ======================= UPDATE TRANSAKSI =======================
if (isset($_POST['update'])) {
    $id_lama      = $_POST['id_lama'];
    $id_transaksi = strtoupper(trim($_POST['id_transaksi']));
    $id_pembeli   = $_POST['id_pembeli'] ?? '';
    $id_barang    = $_POST['id_barang'] ?? '';
    $jumlah_baru  = intval($_POST['jumlah']);
    $tanggal      = $_POST['tanggal'];

    if (empty($id_transaksi) || empty($id_pembeli) || empty($id_barang)) {
        $error = "⚠️ Semua field wajib diisi!";
    } elseif ($jumlah_baru <= 0) {
        $error = "⚠️ Jumlah harus lebih dari 0!";
    } else {
        $cek = $conn->query("SELECT id_transaksi FROM transaksi WHERE id_transaksi='$id_transaksi' AND id_transaksi != '$id_lama'");
        if ($cek->num_rows > 0) {
            $error = "❌ ID Transaksi '$id_transaksi' sudah digunakan!";
        } else {
            $old = $conn->query("SELECT id_barang, jumlah FROM transaksi WHERE id_transaksi='$id_lama'")->fetch_assoc();
            $barang = $conn->query("SELECT harga, stok FROM barang WHERE id_barang='$id_barang'")->fetch_assoc();

            $harga = $barang['harga'] ?? 0;
            $stok  = $barang['stok'] ?? 0;
            $total_harga = $harga * $jumlah_baru;
            $selisih = $jumlah_baru - $old['jumlah'];

            if ($selisih > $stok) {
                $error = "⚠️ Jumlah melebihi stok ($stok)!";
            } else {
                $stmt = $conn->prepare("UPDATE transaksi SET id_transaksi=?, id_pembeli=?, id_barang=?, jumlah=?, total_harga=?, tanggal=? WHERE id_transaksi=?");
                $stmt->bind_param("sssidss", $id_transaksi, $id_pembeli, $id_barang, $jumlah_baru, $total_harga, $tanggal, $id_lama);
                $stmt->execute();
                $stmt->close();

                $conn->query("UPDATE barang SET stok = stok - $selisih WHERE id_barang='$id_barang'");
                $success = "✅ Transaksi berhasil diperbarui!";
            }
        }
    }
}

// ======================= AMBIL DATA TRANSAKSI =======================
$result = $conn->query("
    SELECT t.id_transaksi, p.nama_pembeli, b.nama_barang, t.jumlah, t.total_harga, t.tanggal
    FROM transaksi t
    JOIN pembeli p ON t.id_pembeli = p.id_pembeli
    JOIN barang b ON t.id_barang = b.id_barang
    $where
    ORDER BY t.id_transaksi ASC
");
?>

<!-- ======================= HEADER + FILTER ======================= -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
  <h2 class="text-dark mb-2 mb-md-0">Data Transaksi</h2>
  <div class="d-flex align-items-center gap-2 flex-wrap">
    <form class="d-flex align-items-center" method="get" action="transaksi.php">
      <input type="date" name="tgl_awal" value="<?= htmlspecialchars($tgl_awal) ?>" class="form-control form-control-sm me-1" style="width:160px;">
      <span class="mx-2 text-muted">s.d</span>
      <input type="date" name="tgl_akhir" value="<?= htmlspecialchars($tgl_akhir) ?>" class="form-control form-control-sm me-1" style="width:160px;">
      <button type="submit" class="btn btn-sm btn-secondary me-1"><i class="bi bi-search"></i></button>
      <a href="transaksi.php" class="btn btn-sm btn-outline-secondary me-1"><i class="bi bi-arrow-clockwise"></i></a>
    </form>

    <button class="btn btn-primary btn-sm ms-2" data-bs-toggle="modal" data-bs-target="#modalTambah">
      <i class="bi bi-plus-lg me-1"></i> Tambah Transaksi
    </button>

    <button type="button" class="btn btn-sm btn-success" onclick="window.print()">
      <i class="bi bi-printer"></i> Cetak
    </button>
  </div>
</div>

<!-- ======================= ALERT ======================= -->
<?php if (isset($error)): ?>
  <div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($error) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php elseif (isset($success)): ?>
  <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($success) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

<!-- ======================= TABEL TRANSAKSI ======================= -->
<div class="card border-0 shadow-sm">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead class="table-primary">
          <tr>
            <th>ID Transaksi</th>
            <th>Nama Pembeli</th>
            <th>Nama Barang</th>
            <th>Jumlah</th>
            <th>Total Harga</th>
            <th>Tanggal</th>
            <th class="no-print">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $total_semua = 0;
          if ($result && $result->num_rows > 0):
            while ($row = $result->fetch_assoc()):
              $total_semua += $row['total_harga'];
          ?>
          <tr>
            <td class="text-uppercase"><?= $row['id_transaksi'] ?></td>
            <td><?= $row['nama_pembeli'] ?></td>
            <td class="text-uppercase"><?= $row['nama_barang'] ?></td>
            <td><?= $row['jumlah'] ?></td>
            <td>Rp <?= number_format($row['total_harga'], 0, ',', '.') ?></td>
            <td><?= date('d-m-Y', strtotime($row['tanggal'])) ?></td> <!-- ✅ Format tanggal -->
            <td class="no-print">
              <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id_transaksi'] ?>">
                <i class="bi bi-pencil-square"></i>
              </button>
              <a href="?hapus=<?= $row['id_transaksi'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus transaksi ini?')">
                <i class="bi bi-trash"></i>
              </a>
            </td>
          </tr>
          <?php endwhile; ?>
          <tr class="table-light fw-bold">
            <td colspan="4" class="text-end">Total Keseluruhan:</td>
            <td colspan="3">Rp <?= number_format($total_semua, 0, ',', '.') ?></td>
          </tr>
          <?php else: ?>
          <tr><td colspan="7" class="text-center text-muted py-3">Belum ada transaksi tercatat.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- ======================= MODAL TAMBAH ======================= -->
<div class="modal fade" id="modalTambah" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header bg-light text-dark">
          <h5 class="modal-title">Tambah Transaksi</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label>ID Transaksi</label>
            <input type="text" name="id_transaksi" class="form-control text-uppercase" placeholder="Contoh: T001" required>
          </div>
          <div class="mb-3">
            <label>Pembeli</label>
            <select name="id_pembeli" class="form-select" required>
              <option value="">-- Pilih Pembeli --</option>
              <?php
              $pembeli = $conn->query("SELECT * FROM pembeli ORDER BY nama_pembeli ASC");
              while ($p = $pembeli->fetch_assoc()):
              ?>
                <option value="<?= $p['id_pembeli'] ?>"><?= $p['nama_pembeli'] ?></option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="mb-3">
            <label>Barang</label>
            <select name="id_barang" class="form-select" required>
              <option value="">-- Pilih Barang --</option>
              <?php
              $barang = $conn->query("SELECT * FROM barang ORDER BY nama_barang ASC");
              while ($b = $barang->fetch_assoc()):
              ?>
                <option value="<?= $b['id_barang'] ?>"><?= $b['nama_barang'] ?> (Stok: <?= $b['stok'] ?>)</option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="mb-3">
            <label>Jumlah</label>
            <input type="number" name="jumlah" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Tanggal</label>
            <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>">
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="tambah" class="btn btn-primary">Simpan</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ======================= MODAL EDIT ======================= -->
<?php
$result->data_seek(0);
while ($row = $result->fetch_assoc()):
  $pembeli = $conn->query("SELECT * FROM pembeli ORDER BY nama_pembeli ASC");
  $barang  = $conn->query("SELECT * FROM barang ORDER BY nama_barang ASC");
?>
<div class="modal fade" id="editModal<?= $row['id_transaksi'] ?>" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header bg-light text-dark">
          <h5 class="modal-title">Edit Transaksi</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id_lama" value="<?= $row['id_transaksi'] ?>">
          <div class="mb-3">
            <label>ID Transaksi</label>
            <input type="text" name="id_transaksi" class="form-control text-uppercase" value="<?= $row['id_transaksi'] ?>" required>
          </div>
          <div class="mb-3">
            <label>Pembeli</label>
            <select name="id_pembeli" class="form-select" required>
              <?php while ($p = $pembeli->fetch_assoc()):
                $selected = ($p['nama_pembeli'] == $row['nama_pembeli']) ? 'selected' : ''; ?>
                <option value="<?= $p['id_pembeli'] ?>" <?= $selected ?>><?= $p['nama_pembeli'] ?></option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="mb-3">
            <label>Barang</label>
            <select name="id_barang" class="form-select" required>
              <?php while ($b = $barang->fetch_assoc()):
                $selected = ($b['nama_barang'] == $row['nama_barang']) ? 'selected' : ''; ?>
                <option value="<?= $b['id_barang'] ?>" <?= $selected ?>><?= $b['nama_barang'] ?> (Stok: <?= $b['stok'] ?>)</option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="mb-3">
            <label>Jumlah</label>
            <input type="number" name="jumlah" class="form-control" value="<?= $row['jumlah'] ?>" required>
          </div>
          <div class="mb-3">
            <label>Tanggal</label>
            <input type="date" name="tanggal" class="form-control" value="<?= $row['tanggal'] ?>" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="update" class="btn btn-warning">Simpan</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endwhile; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
