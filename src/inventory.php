<?php
// 1. NYALAKAN SESI DAN KONEKSI DATABASE
session_start();
include './lib/koneksi.php';

// Kalau belum login, usir ke halaman login
if (isset($_SESSION['kasir_id']) == false) {
    header("Location: index.php");
    exit;
}

// ==========================================================
// 2. LOGIKA JIKA TOMBOL "SIMPAN" (TAMBAH BARANG BARU) DITEKAN
// ==========================================================
if (isset($_POST['tombol_simpan_barang'])) {
    $nama_baru = $_POST['isian_nama'];
    $harga_baru = $_POST['isian_harga'];
    $stok_baru = $_POST['isian_stok'];
    $satuan_baru = $_POST['isian_satuan'];

    mysqli_query($conn, "INSERT INTO produk (nama_produk, harga, stok, satuan) VALUES ('$nama_baru', '$harga_baru', '$stok_baru', '$satuan_baru')");
    header("Location: inventory.php");
    exit;
}

// ==========================================================
// 3. LOGIKA JIKA TOMBOL "UPDATE" (SIMPAN EDITAN) DITEKAN
// ==========================================================
if (isset($_POST['tombol_update_barang'])) {
    $id_yang_diedit = $_POST['id_rahasia'];
    $nama_edit = $_POST['isian_nama'];
    $harga_edit = $_POST['isian_harga'];
    $stok_edit = $_POST['isian_stok'];
    $satuan_edit = $_POST['isian_satuan'];

    mysqli_query($conn, "UPDATE produk SET nama_produk='$nama_edit', harga='$harga_edit', stok='$stok_edit', satuan='$satuan_edit' WHERE id='$id_yang_diedit'");
    header("Location: inventory.php");
    exit;
}

// ==========================================================
// 4. LOGIKA JIKA TOMBOL "HAPUS" DITEKAN
// ==========================================================
if (isset($_GET['hapus'])) {
    $id_yang_dihapus = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM produk WHERE id = '$id_yang_dihapus'");
    header("Location: inventory.php");
    exit;
}

// ==========================================================
// 5. PERSIAPAN DATA UNTUK KOTAK EDIT
// ==========================================================
$data_barang_edit = null;
if (isset($_GET['edit'])) {
    $id_mau_di_edit = $_GET['edit'];
    $perintah_cari = mysqli_query($conn, "SELECT * FROM produk WHERE id='$id_mau_di_edit'");
    $data_barang_edit = mysqli_fetch_assoc($perintah_cari);
}

// Mengambil semua barang untuk tabel
$semua_daftar_barang = mysqli_query($conn, "SELECT * FROM produk ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory - TepatKasir</title>
    <link rel="stylesheet" href="./style/style.css">
</head>

<body>

    <div class="navbar">
        <a href="#" class="navbar-brand">TepatKasir</a>
        <div class="navbar-menu">
            <a href="dashboard.php">Dashboard</a>
            <a href="kasir.php">Kasir</a>
            <a href="inventory.php" class="aktif">Inventory</a>
            <a href="riwayat.php">Riwayat</a>
            <span style="border-left:2px solid #000; padding-left:15px; margin-left:5px; font-weight:900;">
                👋 Halo, <?= $_SESSION['username'] ?>
            </span>
            <a href="logout.php" class="btn-merah">Keluar</a>
        </div>
    </div>

    <div class="container">

        <div class="box-putih">
            <?php if ($data_barang_edit != null) { ?>

                <h2 style="margin-top:0; border-bottom: 2px solid #000; padding-bottom:10px; margin-bottom:20px;">
                    ✏️ Edit Data Barang
                </h2>

                <form method="POST" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; align-items: center;">
                    <input type="hidden" name="id_rahasia" value="<?= $data_barang_edit['id'] ?>">

                    <input type="text" name="isian_nama" class="kolom-ketik" value="<?= $data_barang_edit['nama_produk'] ?>" required style="margin-bottom:0;">
                    <input type="number" name="isian_harga" class="kolom-ketik" value="<?= $data_barang_edit['harga'] ?>" required min="0" oninput="if(this.value < 0) this.value = 0;" style="margin-bottom:0;">
                    <input type="number" name="isian_stok" class="kolom-ketik" value="<?= $data_barang_edit['stok'] ?>" required min="0" oninput="if(this.value < 0) this.value = 0;" style="margin-bottom:0;">
                    <input type="text" name="isian_satuan" class="kolom-ketik" value="<?= $data_barang_edit['satuan'] ?? '' ?>" required placeholder="Satuan (Pcs/Kg)" style="margin-bottom:0;">

                    <button type="submit" name="tombol_update_barang" class="btn-kuning" style="padding:15px; width:100%;">UPDATE</button>
                    <a href="inventory.php" class="btn-merah" style="padding:15px; text-align:center; display:block; width:100%; box-sizing:border-box;">BATAL</a>
                </form>

            <?php } else { ?>

                <h2 style="margin-top:0; border-bottom: 2px solid #000; padding-bottom:10px; margin-bottom:20px;">
                    📦 Tambah Barang Baru
                </h2>

                <form id="form-tambah-barang" method="POST" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; align-items: center;">
                    <input type="text" name="isian_nama" class="kolom-ketik" placeholder="Nama Barang" required style="margin-bottom:0;">
                    <input type="number" name="isian_harga" class="kolom-ketik" placeholder="Harga (Rp)" required min="0" oninput="if(this.value < 0) this.value = 0;" style="margin-bottom:0;">
                    <input type="number" name="isian_stok" class="kolom-ketik" placeholder="Jumlah Stok" required min="0" oninput="if(this.value < 0) this.value = 0;" style="margin-bottom:0;">
                    <input type="text" name="isian_satuan" class="kolom-ketik" placeholder="Satuan (Pcs/Kg)" required style="margin-bottom:0;">

                    <button type="button" onclick="tampilkanModalKonfirmasi()" class="btn-biru" style="padding:15px; width:100%;">SIMPAN</button>
                </form>

            <?php } ?>
        </div>

        <div class="box-putih">
            <h2 style="margin-top:0; border-bottom: 2px solid #000; padding-bottom:10px; margin-bottom:20px;">📋 Daftar Barang di Gudang</h2>

            <div class="wadah-tabel" style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; padding-bottom: 10px;">
                <table class="tabel-brutal" style="min-width: 600px;">
                    <tr>
                        <th style="background-color: #4285F4; color: #fff;">ID</th>
                        <th style="background-color: #4285F4; color: #fff;">Nama Barang</th>
                        <th style="background-color: #4285F4; color: #fff;">Harga</th>
                        <th style="background-color: #4285F4; color: #fff;">Stok Tersedia</th>
                        <th style="background-color: #4285F4; color: #fff;">Satuan</th>
                        <th style="background-color: #4285F4; color: #fff;">Aksi</th>
                    </tr>

                    <?php while ($barang = mysqli_fetch_assoc($semua_daftar_barang)): ?>
                        <tr>
                            <td style="font-weight:900;">#<?= $barang['id'] ?></td>
                            <td style="font-weight:700;"><?= $barang['nama_produk'] ?></td>
                            <td style="font-weight:900; color:#34A853;">Rp <?= number_format($barang['harga'], 0, ',', '.') ?></td>

                            <td style="font-weight:900; font-size:18px;">
                                <?php if ($barang['stok'] <= 10) { ?>
                                    <span style="color:#EA4335;"><?= $barang['stok'] ?></span>
                                <?php } else { ?>
                                    <?= $barang['stok'] ?>
                                <?php } ?>
                            </td>
                            <td style="font-weight:700;"><?= $barang['satuan'] ?? '-' ?></td>

                            <td style="white-space: nowrap;">
                                <a href="inventory.php?edit=<?= $barang['id'] ?>" class="btn-kuning">Edit</a>
                                <a href="inventory.php?hapus=<?= $barang['id'] ?>" class="btn-merah" onclick="return confirm('Apakah kamu yakin ingin menghapus barang ini?')">Hapus</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>

                </table>
            </div>
        </div>

    </div>

    <!-- MODAL KONFIRMASI TAMBAH BARANG -->
    <div id="modal-konfirmasi" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 9999; justify-content: center; align-items: center; backdrop-filter: blur(3px);">
        <div class="box-putih" style="width: 90%; max-width: 400px; text-align: center; padding: 30px; animation: popup 0.3s ease-out;">
            <div style="font-size: 50px; margin-bottom: 10px;">📦</div>
            <h2 style="margin-top: 0; color: #202124;">Konfirmasi Simpan</h2>
            <p style="font-weight: 600; color: #636e72; margin-bottom: 25px;">Apakah kamu yakin ingin menambah barang baru ini ke dalam gudang?</p>
            <div style="display: flex; gap: 15px; justify-content: center;">
                <button onclick="tutupModal()" class="btn-merah" style="padding: 12px 20px; flex: 1;">Batal</button>
                <button onclick="submitFormTambah()" class="btn-biru" style="padding: 12px 20px; flex: 1;">Ya, Simpan</button>
            </div>
        </div>
    </div>

    <style>
        @keyframes popup {
            from { transform: scale(0.8); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
    </style>

    <script>
        function tampilkanModalKonfirmasi() {
            var form = document.getElementById('form-tambah-barang');
            // Cek apakah semua input sudah diisi sesuai requirement HTML
            if (form.checkValidity()) {
                document.getElementById('modal-konfirmasi').style.display = 'flex';
            } else {
                form.reportValidity(); // Menampilkan pesan error bawaan browser
            }
        }

        function tutupModal() {
            document.getElementById('modal-konfirmasi').style.display = 'none';
        }

        function submitFormTambah() {
            var form = document.getElementById('form-tambah-barang');
            // Tambahkan input hidden agar PHP bisa mendeteksi bahwa form telah di-submit
            var inputHidden = document.createElement('input');
            inputHidden.type = 'hidden';
            inputHidden.name = 'tombol_simpan_barang';
            inputHidden.value = '1';
            form.appendChild(inputHidden);
            
            form.submit();
        }
    </script>
</body>

</html>