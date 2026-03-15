<?php
session_start();
include './lib/koneksi.php';

// Kalau belum login, kembali ke halaman awal
if (isset($_SESSION['kasir_id']) == false) {
    header("Location: index.php");
    exit;
}

// CARI PENDAPATAN HARI INI
$tanya_pendapatan = mysqli_query($conn, "SELECT SUM(total_belanja) AS total_uang FROM transaksi WHERE DATE(waktu_transaksi) = CURDATE()");
$data_pendapatan = mysqli_fetch_assoc($tanya_pendapatan);
$uang_hari_ini = $data_pendapatan['total_uang'];
if ($uang_hari_ini == null) {
    $uang_hari_ini = 0;
}

// CARI TRANSAKSI HARI INI
$tanya_transaksi = mysqli_query($conn, "SELECT COUNT(id) AS jumlah FROM transaksi WHERE DATE(waktu_transaksi) = CURDATE()");
$data_transaksi = mysqli_fetch_assoc($tanya_transaksi);
$jumlah_transaksi = $data_transaksi['jumlah'];

// CARI JUMLAH JENIS BARANG
$tanya_produk = mysqli_query($conn, "SELECT COUNT(id) AS jumlah FROM produk");
$data_produk = mysqli_fetch_assoc($tanya_produk);
$jumlah_produk = $data_produk['jumlah'];

// CARI BARANG YANG MAU HABIS (STOK DI BAWAH 10)
$barang_mau_habis = mysqli_query($conn, "SELECT * FROM produk WHERE stok <= 10");

// CARI 5 TRANSAKSI TERBARU
$transaksi_terbaru = mysqli_query($conn, "SELECT t.*, u.username FROM transaksi t JOIN users u ON t.kasir_id = u.id ORDER BY t.waktu_transaksi DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Dashboard - TepatKasir</title>
    <link rel="stylesheet" href="./style/style.css">
</head>

<body>

    <div class="navbar">
        <a href="#" class="navbar-brand">TepatKasir</a>
        <div class="navbar-menu">
            <a href="dashboard.php" class="aktif">Dashboard</a>
            <a href="kasir.php">Kasir POS</a>
            <a href="inventory.php">Inventory</a>
            <a href="riwayat.php">Riwayat</a>
            <span style="border-left:2px solid #000; padding-left:15px; margin-left:5px; font-weight:bold;">👋 Halo, <?= $_SESSION['username'] ?></span>
            <a href="logout.php" class="btn-merah">Keluar</a>
        </div>
    </div>

    <div class="container">

        <div class="row-cards">
            <div class="card-stat">
                <h3 style="color:#202124;">Pendapatan Hari Ini</h3>
                <h1 style="color:#4285F4; margin-top:10px;">Rp <?= number_format($uang_hari_ini, 0, ',', '.') ?></h1>
            </div>
            <div class="card-stat">
                <h3 style="color:#202124;">Total Transaksi</h3>
                <h1 style="color:#FBBC04; margin-top:10px;"><?= $jumlah_transaksi ?> Nota</h1>
            </div>
            <div class="card-stat">
                <h3 style="color:#202124;">Jenis Produk</h3>
                <h1 style="color:#34A853; margin-top:10px;"><?= $jumlah_produk ?> Item</h1>
            </div>
        </div>

        <div style="display:flex; gap:30px;">

            <div class="box-putih" style="flex:1;">
                <h3 style="margin-top:0; color:#EA4335; border-bottom: 2px solid #000; padding-bottom:10px;">⚠️ Peringatan Stok</h3>
                <table>
                    <tr>
                        <th>Nama Barang</th>
                        <th>Stok</th>
                        <th>Aksi</th>
                    </tr>
                    <?php while ($baris = mysqli_fetch_assoc($barang_mau_habis)): ?>
                        <tr>
                            <td style="font-weight:bold;"><?= $baris['nama_produk'] ?></td>
                            <td style="color:#EA4335; font-weight:bold; font-size:18px;"><?= $baris['stok'] ?></td>
                            <td><a href="inventory.php?edit=<?= $baris['id'] ?>" class="btn-kuning">+ Isi Stok</a></td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            </div>

            <div class="box-putih" style="flex:1;">
                <h3 style="margin-top:0; color:#202124; border-bottom: 2px solid #000; padding-bottom:10px;">🛒 Transaksi Terbaru</h3>
                <table>
                    <tr>
                        <th>Waktu</th>
                        <th>Kasir</th>
                        <th>Total</th>
                    </tr>
                    <?php while ($baris = mysqli_fetch_assoc($transaksi_terbaru)): ?>
                        <tr>
                            <td style="font-weight:bold;"><?= date('H:i', strtotime($baris['waktu_transaksi'])) ?></td>
                            <td style="font-weight:bold; color:#4285F4;"><?= $baris['username'] ?></td>
                            <td style="color:#34A853; font-weight:bold;">Rp <?= number_format($baris['total_belanja'], 0, ',', '.') ?></td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            </div>

        </div>
    </div>
</body>

</html>