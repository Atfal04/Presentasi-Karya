<?php
session_start();
include './lib/koneksi.php';

// Kalau sudah login, langsung lempar ke dashboard
if (isset($_SESSION['kasir_id'])) {
    header("Location: dashboard.php");
    exit;
}

$pesan_sukses = "";
$pesan_error = "";

// Jika tombol daftar ditekan
if (isset($_POST['tombol_daftar'])) {
    $username_baru = $_POST['username'];
    // Password kita acak dengan MD5 agar aman dan tidak bisa dibaca langsung di database
    $password_baru = md5($_POST['password']); 
    // Cek dulu ke database, apakah username ini sudah ada yang pakai?
    $tanya_database = mysqli_query($conn, "SELECT * FROM users WHERE username='$username_baru'");
    
    // Kalau jumlah barisnya lebih dari 0, berarti namanya sudah dipakai
    if (mysqli_num_rows($tanya_database) > 0) {
        $pesan_error = "Maaf, Username ini sudah dipakai. Cari nama lain!";
    } else {
        // Kalau belum ada yang pakai, masukkan data kasir baru ini ke gudang data (database)
        mysqli_query($conn, "INSERT INTO users (username, password) VALUES ('$username_baru', '$password_baru')");
        $pesan_sukses = "Pendaftaran sukses! Silakan klik tombol Masuk.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Kasir - TepatKasir</title>
    <link rel="stylesheet" href="./style/style.css">
</head>
<body class="layar-login">

    <div class="kotak-login">
        <h1 style="font-size: 32px; font-weight: 800; color: #202124; margin-bottom: 5px;">AKUN KASIR BARU</h1>
        <p style="font-weight: 600; color: #636e72; margin-bottom: 25px;">Buat akun untuk bisa masuk ke sistem</p>

        <?php if ($pesan_error != ""): ?>
            <div style="background: #EA4335; color: white; padding: 10px; border: 2px solid #000; font-weight: bold; margin-bottom: 20px; box-shadow: 0.2rem 0.2rem 0 #000;">
                <?= $pesan_error ?>
            </div>
        <?php endif; ?>

        <?php if ($pesan_sukses != ""): ?>
            <div style="background: #34A853; color: white; padding: 10px; border: 2px solid #000; font-weight: bold; margin-bottom: 20px; box-shadow: 0.2rem 0.2rem 0 #000;">
                <?= $pesan_sukses ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <input type="text" name="username" class="kolom-ketik" placeholder="Buat Username Baru" required>
            <input type="password" name="password" class="kolom-ketik" placeholder="Buat Password Baru" required>
            
            <button type="submit" name="tombol_daftar" class="btn-hijau" style="width: 100%; font-size: 18px; padding: 15px; margin-bottom: 15px;">DAFTAR SEKARANG</button>
        </form>
        <p style="font-weight: bold; margin-top: 20px;">
            Sudah punya akun? <br>
            <a href="index.php" class="btn-biru" style="width: 100%; display: block; text-align: center; padding: 15px; font-size: 16px; box-sizing: border-box;">Kembali ke halaman MASUK</a>
        </p>

    </div>

</body>
</html>