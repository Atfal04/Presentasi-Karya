<?php
session_start();
include './lib/koneksi.php';

// Kalau belum login, kembali ke index.php
if (isset($_SESSION['kasir_id']) == false) {
    header("Location: index.php");
    exit;
}

// 1. CARI PENDAPATAN HARI INI
$tanya_pendapatan = mysqli_query($conn, "SELECT SUM(total_belanja) AS total_uang FROM transaksi WHERE DATE(waktu_transaksi) = CURDATE()");
$data_pendapatan = mysqli_fetch_assoc($tanya_pendapatan);
$uang_hari_ini = $data_pendapatan['total_uang'];
if ($uang_hari_ini == null) {
    $uang_hari_ini = 0;
}

// 2. CARI TRANSAKSI HARI INI
$tanya_transaksi = mysqli_query($conn, "SELECT COUNT(id) AS jumlah FROM transaksi WHERE DATE(waktu_transaksi) = CURDATE()");
$jumlah_transaksi = mysqli_fetch_assoc($tanya_transaksi)['jumlah'];

// 3. CARI JUMLAH JENIS BARANG
$tanya_produk = mysqli_query($conn, "SELECT COUNT(id) AS jumlah FROM produk");
$jumlah_produk = mysqli_fetch_assoc($tanya_produk)['jumlah'];

// 4. CARI BARANG MAU HABIS
$barang_mau_habis = mysqli_query($conn, "SELECT * FROM produk WHERE stok <= 10");

// 5. CARI 5 TRANSAKSI TERBARU
$transaksi_terbaru = mysqli_query($conn, "SELECT t.*, u.username FROM transaksi t JOIN users u ON t.kasir_id = u.id ORDER BY t.waktu_transaksi DESC LIMIT 5");

// 6. GRAFIK PENJUALAN MINGGUAN
$keranjang_hari = [];
$keranjang_uang = [];
for ($angka = 6; $angka >= 0; $angka--) {
    $tanggal_dicari = date('Y-m-d', strtotime("-$angka days"));
    $tanya_grafik = mysqli_query($conn, "SELECT SUM(total_belanja) AS uang_masuk FROM transaksi WHERE DATE(waktu_transaksi) = '$tanggal_dicari'");
    $buka_grafik = mysqli_fetch_assoc($tanya_grafik);

    $keranjang_hari[] = date('d M', strtotime($tanggal_dicari));
    if ($buka_grafik['uang_masuk'] == null) {
        $keranjang_uang[] = 0;
    } else {
        $keranjang_uang[] = $buka_grafik['uang_masuk'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - TepatKasir</title>
    <link rel="stylesheet" href="./style/style.css?v=<?= time(); ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>

    <div class="navbar">
        <a href="#" class="navbar-brand">TepatKasir</a>
        <div class="navbar-menu">
            <a href="dashboard.php" class="aktif">Dashboard</a>
            <a href="kasir.php">Kasir POS</a>
            <a href="inventory.php">Inventory</a>
            <a href="riwayat.php">Riwayat</a>
            <span style="border-left:2px solid #000; padding-left:15px; margin-left:5px; font-weight:900;">👋 Halo, <?= $_SESSION['username'] ?></span>
            <a href="logout.php" class="btn-merah">Keluar</a>
        </div>
    </div>

    <div class="container">

        <div class="row-cards" style="display: flex; flex-wrap: wrap; gap: 20px; margin-bottom: 30px;">
            <div class="card-stat" style="min-width: 200px;">
                <h3 style="color:#202124;">Pendapatan Hari Ini</h3>
                <h1 style="color:#4285F4; margin-top:10px;">Rp <?= number_format($uang_hari_ini, 0, ',', '.') ?></h1>
            </div>
            <div class="card-stat" style="min-width: 200px;">
                <h3 style="color:#202124;">Total Transaksi</h3>
                <h1 style="color:#FBBC04; margin-top:10px;"><?= $jumlah_transaksi ?> Nota</h1>
            </div>
            <div class="card-stat" style="min-width: 200px;">
                <h3 style="color:#202124;">Jenis Produk</h3>
                <h1 style="color:#34A853; margin-top:10px;"><?= $jumlah_produk ?> Item</h1>
            </div>
        </div>

        <div class="box-putih">
            <h3 style="margin-top:0; border-bottom: 2px solid #000; padding-bottom:10px;">📈 Grafik Penjualan (7 Hari)</h3>
            <div style="height: 250px; margin-top:20px; width: 100%; position: relative;">
                <canvas id="tempat_gambar_grafik"></canvas>
            </div>
        </div>

        <div style="display: flex; flex-wrap: wrap; gap: 30px; width: 100%;">

            <div class="box-putih" style="flex: 1; min-width: 300px; margin-bottom: 0;">
                <h3 style="margin-top:0; color:#EA4335; border-bottom: 2px solid #000; padding-bottom:10px;">⚠️ Peringatan Stok</h3>

                <div class="wadah-tabel" style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; padding-bottom: 10px;">
                    <table class="tabel-brutal" style="min-width: 450px;">
                        <tr>
                            <th style="background-color: #EA4335; color: #fff;">Nama Barang</th>
                            <th style="background-color: #EA4335; color: #fff;">Stok</th>
                            <th style="background-color: #EA4335; color: #fff;">Aksi</th>
                        </tr>
                        <?php while ($baris = mysqli_fetch_assoc($barang_mau_habis)): ?>
                            <tr>
                                <td style="font-weight:700;"><?= $baris['nama_produk'] ?></td>
                                <td style="color:#EA4335; font-weight:900; font-size:18px;"><?= $baris['stok'] ?></td>
                                <td style="white-space: nowrap;"><a href="inventory.php?edit=<?= $baris['id'] ?>" class="btn-kuning">+ Isi Stok</a></td>
                            </tr>
                        <?php endwhile; ?>
                    </table>
                </div>
            </div>

            <div class="box-putih" style="flex: 1; min-width: 300px; margin-bottom: 0;">
                <h3 style="margin-top:0; color:#202124; border-bottom: 2px solid #000; padding-bottom:10px;">🛒 Transaksi Terbaru</h3>

                <div class="wadah-tabel" style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; padding-bottom: 10px;">
                    <table class="tabel-brutal" style="min-width: 450px;">
                        <tr>
                            <th>Waktu</th>
                            <th>Kasir</th>
                            <th>Total</th>
                        </tr>
                        <?php while ($baris = mysqli_fetch_assoc($transaksi_terbaru)): ?>
                            <tr>
                                <td style="font-weight:700; white-space: nowrap;"><?= date('H:i', strtotime($baris['waktu_transaksi'])) ?></td>
                                <td style="font-weight:900; color:#4285F4;"><?= $baris['username'] ?></td>
                                <td style="color:#34A853; font-weight:900; white-space: nowrap;">Rp <?= number_format($baris['total_belanja'], 0, ',', '.') ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <script>
        const kanvas = document.getElementById('tempat_gambar_grafik').getContext('2d');
        new Chart(kanvas, {
            type: 'line',
            data: {
                labels: <?= json_encode($keranjang_hari) ?>,
                datasets: [{
                    label: 'Pendapatan (Rp)',
                    data: <?= json_encode($keranjang_uang) ?>,
                    borderColor: '#202124',
                    backgroundColor: '#4285F4',
                    borderWidth: 4,
                    fill: false,
                    tension: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    </script>
</body>

</html>