<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Jika sudah login, arahkan langsung ke dashboard
if (isset($_SESSION['login']) && $_SESSION['login'] === true) {
  header("Location: /toko_rahma/index.php");
  exit;
}

// ==================== PROSES LOGIN ====================
if (isset($_POST['login'])) {
  $username = trim($_POST['username']);
  $password = trim($_POST['password']);

  if (empty($username) || empty($password)) {
    $error = "⚠️ Username dan password wajib diisi!";
  } else {
    $sql = $conn->prepare("SELECT * FROM users WHERE username=?");
    $sql->bind_param("s", $username);
    $sql->execute();
    $result = $sql->get_result();

    if ($result->num_rows === 1) {
      $user = $result->fetch_assoc();

      if ($password === $user['password']) {
        $_SESSION['login'] = true;
        $_SESSION['username'] = $user['username'];
        $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
        $_SESSION['role'] = $user['role'];

        header("Location: /toko_rahma/index.php");
        exit;
      } else {
        $error = "❌ Password salah!";
      }
    } else {
      $error = "❌ Username tidak ditemukan!";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Login - Toko Rahma</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
  <!-- ✅ Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <!-- ✅ Google Font -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  
  <style>
    body {
      font-family: 'system-ui', sans-serif;
      background: linear-gradient(135deg, #e3f2fd, #bbdefb, #e3f2fd);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .login-card {
      width: 100%;
      max-width: 400px;
      padding: 40px 35px;
      border-radius: 20px;
      background: rgba(255, 255, 255, 0.85);
      backdrop-filter: blur(10px);
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
      animation: fadeIn 0.5s ease-in-out;
    }

    .login-card h3 {
      text-align: center;
      font-weight: 600;
      color: #0d47a1;
      margin-bottom: 10px;
    }

    .login-card p {
      text-align: center;
      color: #6c757d;
      font-size: 0.95rem;
      margin-bottom: 25px;
    }

    .form-control {
      border-radius: 10px;
      border: 1px solid #d0d7de;
      padding: 8px 14px;
      transition: all 0.3s;
    }

    .form-control:focus {
      border-color: #1976d2;
      box-shadow: 0 0 0 3px rgba(25, 118, 210, 0.15);
    }

    .btn-primary {
      background-color: #0D57C6;
      border: none;
      border-radius: 10px;
      font-weight: 400;
      padding: 8px;
      transition: all 0.3s;
    }

    .btn-primary:hover {
      background-color: #0d47a1;
      transform: translateY(-2px);
    }

    .alert {
      border-radius: 10px;
      font-size: 0.9rem;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body>

<div class="login-card">
  <h3>Login - Toko Rahma</h3>
  <p>Selamat datang di Toko Rahma. Silakan login untuk mulai menggunakan aplikasi.</p>

  <?php if (isset($error)): ?>
    <div class="alert alert-danger text-center py-2"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST" class="mt-3">
    <div class="mb-3">
      <label class="form-label">Username</label>
      <input type="text" name="username" class="form-control" required autofocus>
    </div>

    <div class="mb-3">
      <label class="form-label">Password</label>
      <input type="password" name="password" class="form-control" required>
    </div>

    <button type="submit" name="login" class="btn btn-primary w-100 mt-2">
      <i class="bi bi-box-arrow-in-right me-1"></i> Login
    </button>
  </form>

  <p class="text-muted mt-4">© <?= date('Y') ?> Toko Rahma. All rights reserved.</p>
</div>

</body>
</html>
