<?php
session_start();
include './lib/koneksi.php';

// Cek apakah sudah login
if (isset($_SESSION['kasir_id']) == false) {
    header("Location: index.php");
    exit;
}

// LOGIKA MENAMBAH BARANG BARU
if (isset($_POST['tombol_tambah'])) {
    $nama = $_POST['nama_produk'];
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];

    mysqli_query($conn, "INSERT INTO produk (nama_produk, harga, stok) VALUES ('$nama', '$harga', '$stok')");
    header("Location: inventory.php");
    exit;
}

// LOGIKA MENGEDIT BARANG LAMA
if (isset($_POST['tombol_update'])) {
    $id_yang_diedit = $_POST['id_produk'];
    $nama_baru = $_POST['nama_produk'];
    $harga_baru = $_POST['harga'];
    $stok_baru = $_POST['stok'];

    mysqli_query($conn, "UPDATE produk SET nama_produk='$nama_baru', harga='$harga_baru', stok='$stok_baru' WHERE id='$id_yang_diedit'");
    header("Location: inventory.php");
    exit;
}

// LOGIKA MENGHAPUS BARANG
if (isset($_GET['hapus'])) {
    $id_hapus = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM produk WHERE id = '$id_hapus'");
    header("Location: inventory.php");
    exit;
}

// MENGAMBIL DATA UNTUK DITAMPILKAN DI FORM EDIT
$data_edit = null; // Default kosong
if (isset($_GET['edit'])) {
    $id_get = $_GET['edit'];
    $query_edit = mysqli_query($conn, "SELECT * FROM produk WHERE id='$id_get'");
    $data_edit = mysqli_fetch_assoc($query_edit);
}

// Ambil semua data barang untuk ditampilkan di tabel bawah
$semua_barang = mysqli_query($conn, "SELECT * FROM produk ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Inventory - TepatKasir</title>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./style/style.css">
</head>

<body>

    <div class="navbar">
        <a href="#" class="navbar-brand">TepatKasir</a>
        <div class="navbar-menu">
            <a href="dashboard.php">Dashboard</a>
            <a href="kasir.php">Kasir POS</a>
            <a href="inventory.php" class="aktif">Inventory</a>
            <a href="riwayat.php">Riwayat</a>
            <span style="border-left:2px solid #000; padding-left:15px; margin-left:5px; font-weight:800;">
                👋 Halo, <?= $_SESSION['username'] ?>
            </span>
            <a href="logout.php" style="background-color:#EA4335; color:white; border:2px solid #000;">Keluar</a>
        </div>
    </div>

    <div class="container">

        <div class="box-putih">

            <?php if ($data_edit != null): ?>
                <h2 style="margin-top:0; border-bottom: 2px solid #000; padding-bottom:10px; margin-bottom:20px; color:#FBBC04; text-shadow: 1px 1px 0px #000;">
                    ✏️ Edit Data Barang
                </h2>

                <form method="POST" style="display:flex; gap:15px; align-items:center;">
                    <input type="hidden" name="id_produk" value="<?= $data_edit['id'] ?>">

                    <input type="text" name="nama_produk" class="kolom-ketik" value="<?= $data_edit['nama_produk'] ?>" required style="margin-bottom:0;">
                    <input type="number" name="harga" class="kolom-ketik" value="<?= $data_edit['harga'] ?>" required style="margin-bottom:0;">
                    <input type="number" name="stok" class="kolom-ketik" value="<?= $data_edit['stok'] ?>" required style="margin-bottom:0; width:150px;">

                    <button type="submit" name="tombol_update" class="btn-kuning" style="padding:15px 30px; font-size:16px;">UPDATE</button>
                    <a href="inventory.php" class="btn-merah" style="padding:15px 20px; font-size:16px;">BATAL</a>
                </form>

            <?php else: ?>
                <h2 style="margin-top:0; border-bottom: 2px solid #000; padding-bottom:10px; margin-bottom:20px;">
                    📦 Tambah Barang Baru
                </h2>

                <form method="POST" style="display:flex; gap:15px; align-items:center;">
                    <input type="text" name="nama_produk" class="kolom-ketik" placeholder="Nama Barang" required style="margin-bottom:0;">
                    <input type="number" name="harga" class="kolom-ketik" placeholder="Harga (Rp)" required style="margin-bottom:0;">
                    <input type="number" name="stok" class="kolom-ketik" placeholder="Jumlah Stok" required style="margin-bottom:0; width:150px;">

                    <button type="submit" name="tombol_tambah" class="btn-biru" style="padding:15px 30px; font-size:16px;">SIMPAN</button>
                </form>
            <?php endif; ?>

        </div>

        <div class="box-putih">
            <h2 style="margin-top:0; border-bottom: 2px solid #000; padding-bottom:10px; margin-bottom:20px;">📋 Daftar Barang di Gudang</h2>

            <table class="tabel-brutal">
                <tr>
                    <th>ID</th>
                    <th>Nama Barang</th>
                    <th>Harga</th>
                    <th>Stok Tersedia</th>
                    <th>Aksi</th>
                </tr>
                <?php while ($barang = mysqli_fetch_assoc($semua_barang)): ?>
                    <tr>
                        <td style="font-weight:bold;">#<?= $barang['id'] ?></td>
                        <td style="font-weight:800;"><?= $barang['nama_produk'] ?></td>
                        <td style="color:#34A853; font-weight:800;">Rp <?= number_format($barang['harga'], 0, ',', '.') ?></td>
                        <td style="font-weight:800; font-size:18px; <?= ($barang['stok'] <= 10) ? 'color:#EA4335;' : '' ?>">
                            <?= $barang['stok'] ?>
                        </td>
                        <td>
                            <a href="inventory.php?edit=<?= $barang['id'] ?>" class="btn-kuning" style="margin-right: 5px;">Edit</a>

                            <a href="inventory.php?hapus=<?= $barang['id'] ?>" class="btn-merah" onclick="return confirm('Yakin ingin menghapus <?= $barang['nama_produk'] ?> dari sistem?')">Hapus</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>

    </div>
</body>

</html>