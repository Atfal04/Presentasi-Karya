<?php
session_start();
include './lib/koneksi.php';

if (isset($_SESSION['kasir_id']) == false) {
    header("Location: index.php");
    exit;
}

// Ambil riwayat dari yang terbaru
$riwayat_penjualan = mysqli_query($conn, "SELECT t.*, u.username FROM transaksi t JOIN users u ON t.kasir_id = u.id ORDER BY t.waktu_transaksi DESC");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Riwayat - TepatKasir</title>
    <link rel="stylesheet" href="./style/style.css">
</head>

<body>

    <div class="navbar">
        <a href="#" class="navbar-brand">TepatKasir</a>
        <div class="navbar-menu">
            <a href="dashboard.php">Dashboard</a>
            <a href="kasir.php">Kasir POS</a>
            <a href="inventory.php">Inventory</a>
            <a href="riwayat.php" class="aktif">Riwayat</a>
            <span style="border-left:2px solid #000; padding-left:15px; margin-left:5px; font-weight:800;">
                👋 Halo, <?= $_SESSION['username'] ?>
            </span>
            <a href="logout.php" style="background-color:#EA4335; color:white; border:2px solid #000;">Keluar</a>
        </div>
    </div>

    <div class="container">

        <div class="box-putih">
            <h2 style="margin-top:0; border-bottom: 2px solid #000; padding-bottom:10px; margin-bottom:20px;">📜 Riwayat Semua Transaksi</h2>

            <table class="tabel-brutal">
                <tr style="background-color: #FBBC04;">
                    <th style="background-color: #FBBC04; color: #000;">Nomor Nota</th>
                    <th style="background-color: #FBBC04; color: #000;">Waktu</th>
                    <th style="background-color: #FBBC04; color: #000;">Kasir</th>
                    <th style="background-color: #FBBC04; color: #000;">Total Belanja</th>
                    <th style="background-color: #FBBC04; color: #000;">Uang Masuk</th>
                    <th style="background-color: #FBBC04; color: #000;">Kembalian</th>
                </tr>

                <?php while ($riwayat = mysqli_fetch_assoc($riwayat_penjualan)): ?>
                    <tr>
                        <td style="font-weight:bold;">TRX-<?= $riwayat['id'] ?></td>
                        <td style="font-weight:600;"><?= date('d M Y - H:i', strtotime($riwayat['waktu_transaksi'])) ?></td>
                        <td style="font-weight:800; color:#4285F4;"><?= $riwayat['username'] ?></td>
                        <td style="font-weight:800; color:#34A853; font-size:18px;">Rp <?= number_format($riwayat['total_belanja'], 0, ',', '.') ?></td>
                        <td style="font-weight:600;">Rp <?= number_format($riwayat['uang_bayar'], 0, ',', '.') ?></td>
                        <td style="font-weight:600; color:#EA4335;">Rp <?= number_format($riwayat['kembalian'], 0, ',', '.') ?></td>
                    </tr>
                <?php endwhile; ?>

            </table>
        </div>

    </div>
</body>

</html>