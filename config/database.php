<?php
// config/database.php
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'toko_rahma';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
?>
