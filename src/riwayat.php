<?php
session_start();
include './lib/koneksi.php';

if (isset($_SESSION['kasir_id']) == false) {
    header("Location: index.php");
    exit;
}

$perintah_ambil_riwayat = mysqli_query($conn, "SELECT transaksi.*, users.username FROM transaksi JOIN users ON transaksi.kasir_id = users.id ORDER BY transaksi.waktu_transaksi DESC");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            <span style="border-left:2px solid #000; padding-left:15px; margin-left:5px; font-weight:900;">👋 Halo, <?= $_SESSION['username'] ?></span>
            <a href="logout.php" class="btn-merah">Keluar</a>
        </div>
    </div>

    <div class="container">
        <div class="box-putih">
            <h2 style="margin-top:0; border-bottom: 2px solid #000; padding-bottom:10px; margin-bottom:20px;">📜 Laporan Transaksi</h2>

            <div class="wadah-tabel">
                <table class="tabel-brutal">
                    <tr>
                        <th style="background-color: #FBBC04; color: #000;">No. Nota</th>
                        <th style="background-color: #FBBC04; color: #000;">Waktu</th>
                        <th style="background-color: #FBBC04; color: #000;">Nama Kasir</th>
                        <th style="background-color: #FBBC04; color: #000;">Total Belanja</th>
                        <th style="background-color: #FBBC04; color: #000;">Tunai</th>
                        <th style="background-color: #FBBC04; color: #000;">Kembalian</th>
                    </tr>
                    <?php while ($data_riwayat = mysqli_fetch_assoc($perintah_ambil_riwayat)): ?>
                        <tr>
                            <td style="font-weight:900;">TRX-<?= $data_riwayat['id'] ?></td>
                            <td style="font-weight:700;"><?= date('d M Y - H:i', strtotime($data_riwayat['waktu_transaksi'])) ?></td>
                            <td style="font-weight:900; color:#4285F4;"><?= $data_riwayat['username'] ?></td>
                            <td style="font-weight:900; color:#34A853; font-size:18px;">Rp <?= number_format($data_riwayat['total_belanja'], 0, ',', '.') ?></td>
                            <td style="font-weight:700;">Rp <?= number_format($data_riwayat['uang_bayar'], 0, ',', '.') ?></td>
                            <td style="font-weight:700; color:#EA4335;">Rp <?= number_format($data_riwayat['kembalian'], 0, ',', '.') ?></td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            </div>

        </div>
    </div>
</body>

</html>