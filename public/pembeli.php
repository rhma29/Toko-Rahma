<?php
require_once __DIR__ . '/../config/database.php';
include __DIR__ . '/../includes/header.php';

session_start();
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
  header("Location: /toko_rahma/public/login.php");
  exit;
}

// ======================= PENCARIAN DATA =======================
$search = $_GET['search'] ?? '';
$where = '';
if (!empty($search)) {
    $search_safe = $conn->real_escape_string($search);
    $where = "WHERE nama_pembeli LIKE '%$search_safe%'";
}

// ======================= TAMBAH PEMBELI =======================
if (isset($_POST['tambah'])) {
    $id_pembeli   = strtoupper(trim($_POST['id_pembeli']));
    $nama_pembeli = trim($_POST['nama_pembeli']);
    $alamat       = trim($_POST['alamat']);
    $no_hp        = trim($_POST['no_hp']);

    // ✅ Validasi input
    if (empty($id_pembeli) || empty($nama_pembeli)) {
        $error = "⚠️ ID Pembeli dan Nama Pembeli wajib diisi!";
    } elseif (!empty($no_hp) && !preg_match('/^[0-9]+$/', $no_hp)) {
        $error = "⚠️ Nomor HP hanya boleh berisi angka!";
    } else {
        // 🔍 Cek duplikat ID
        $cekID = $conn->query("SELECT id_pembeli FROM pembeli WHERE id_pembeli = '$id_pembeli'");
        if ($cekID && $cekID->num_rows > 0) {
            $error = "❌ ID Pembeli '$id_pembeli' sudah digunakan. Gunakan ID lain!";
        } else {
            // 🔹 Simpan data baru
            $stmt = $conn->prepare("INSERT INTO pembeli (id_pembeli, nama_pembeli, alamat, no_hp) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $id_pembeli, $nama_pembeli, $alamat, $no_hp);
            $stmt->execute();
            $stmt->close();
            $success = "✅ Data pembeli berhasil ditambahkan!";
        }
    }
}

// ======================= HAPUS PEMBELI =======================
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];

    // Pastikan data ada
    
        $conn->query("DELETE FROM pembeli WHERE id_pembeli='$id'");
        $success = "🗑️ Data pembeli berhasil dihapus!";
    }


// ======================= UPDATE PEMBELI =======================
if (isset($_POST['update'])) {
    $id_lama      = $_POST['id_lama'];
    $id_pembeli   = strtoupper(trim($_POST['id_pembeli']));
    $nama_pembeli = trim($_POST['nama_pembeli']);
    $alamat       = trim($_POST['alamat']);
    $no_hp        = trim($_POST['no_hp']);

    // ✅ Validasi input
    if (empty($id_pembeli) || empty($nama_pembeli)) {
        $error = "⚠️ ID Pembeli dan Nama Pembeli wajib diisi!";
    } elseif (!empty($no_hp) && !preg_match('/^[0-9]+$/', $no_hp)) {
        $error = "⚠️ Nomor HP hanya boleh berisi angka!";
    } else {
        // 🔍 Cek duplikat ID
        $cek = $conn->query("SELECT id_pembeli FROM pembeli WHERE id_pembeli='$id_pembeli' AND id_pembeli != '$id_lama'");
        if ($cek->num_rows > 0) {
            $error = "❌ ID Pembeli '$id_pembeli' sudah digunakan oleh data lain!";
        } else {
            // 🔹 Update data
            $stmt = $conn->prepare("UPDATE pembeli SET id_pembeli=?, nama_pembeli=?, alamat=?, no_hp=? WHERE id_pembeli=?");
            $stmt->bind_param("sssss", $id_pembeli, $nama_pembeli, $alamat, $no_hp, $id_lama);
            $stmt->execute();
            $stmt->close();
            $success = "✅ Data pembeli berhasil diperbarui!";
        }
    }
}

// ======================= AMBIL DATA PEMBELI =======================
$result = $conn->query("SELECT * FROM pembeli $where ORDER BY id_pembeli ASC");
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
  <h2 class="text-dark mb-2 mb-md-0">Data Pembeli</h2>
  <div class="d-flex align-items-center gap-2 flex-wrap">

    <!-- 🔍 Form Pencarian -->
    <form class="d-flex" method="get" action="pembeli.php">
        <input 
            type="text" 
            name="search" 
            class="form-control form-control-sm" 
            placeholder="Cari Nama Pembeli" 
            value="<?= htmlspecialchars($search) ?>" 
            style="width: 220px;"
        >
        <button class="btn btn-sm btn-secondary ms-1" type="submit"><i class="bi bi-search"></i></button>
        <a href="pembeli.php" class="btn btn-sm btn-outline-secondary ms-1"><i class="bi bi-arrow-clockwise"></i></a>
    </form>

    <!-- ➕ Tombol Tambah Data -->
    <button class="btn btn-primary btn-sm ms-2" data-bs-toggle="modal" data-bs-target="#modalTambah">
      <i class="bi bi-plus-lg me-1"></i> Tambah Data
    </button>
  </div>
</div>

<!-- 🔔 ALERT -->
<?php if (isset($error)): ?>
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?= htmlspecialchars($error) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php elseif (isset($success)): ?>
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    <?= htmlspecialchars($success) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead class="table-primary">
          <tr>
            <th>ID Pembeli</th>
            <th>Nama Pembeli</th>
            <th>Alamat</th>
            <th>No. HP</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
              <tr>
                <td class="text-uppercase"><?= htmlspecialchars($row['id_pembeli']) ?></td>
                <td><?= htmlspecialchars($row['nama_pembeli']) ?></td>
                <td><?= htmlspecialchars($row['alamat']) ?></td>
                <td><?= htmlspecialchars($row['no_hp']) ?></td>
                <td>
                  <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id_pembeli'] ?>">
                    <i class="bi bi-pencil-square"></i>
                  </button>
                  <a href="?hapus=<?= $row['id_pembeli'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus data ini?')">
                    <i class="bi bi-trash"></i>
                  </a>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="5" class="text-center text-muted py-3">Belum ada data pembeli.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- ======================= Modal Tambah Pembeli ======================= -->
<div class="modal fade" id="modalTambah" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header bg-light text-dark">
          <h5 class="modal-title">Tambah Data Pembeli</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label>ID Pembeli</label>
            <input type="text" name="id_pembeli" class="form-control text-uppercase" placeholder="Contoh: P001" required>
          </div>
          <div class="mb-3">
            <label>Nama Pembeli</label>
            <input type="text" name="nama_pembeli" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Alamat</label>
            <textarea name="alamat" class="form-control" rows="2" required></textarea>
          </div>
          <div class="mb-3">
            <label>No. HP</label>
            <input type="text" name="no_hp" class="form-control" pattern="[0-9]*">
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

<!-- ======================= Modal Edit Pembeli ======================= -->
<?php
$result->data_seek(0);
while ($row = $result->fetch_assoc()):
?>
<div class="modal fade" id="editModal<?= $row['id_pembeli'] ?>" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header bg-light text-dark">
          <h5 class="modal-title">Edit Data Pembeli</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id_lama" value="<?= $row['id_pembeli'] ?>">
          <div class="mb-3">
            <label>ID Pembeli</label>
            <input type="text" name="id_pembeli" class="form-control text-uppercase" value="<?= htmlspecialchars($row['id_pembeli']) ?>" required>
          </div>
          <div class="mb-3">
            <label>Nama Pembeli</label>
            <input type="text" name="nama_pembeli" class="form-control" value="<?= htmlspecialchars($row['nama_pembeli']) ?>" required>
          </div>
          <div class="mb-3">
            <label>Alamat</label>
            <textarea name="alamat" class="form-control" rows="2" required><?= htmlspecialchars($row['alamat']) ?></textarea>
          </div>
          <div class="mb-3">
            <label>No. HP</label>
            <input type="text" name="no_hp" class="form-control" value="<?= htmlspecialchars($row['no_hp']) ?>" pattern="[0-9]*">
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
