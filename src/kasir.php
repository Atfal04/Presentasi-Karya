<?php
session_start();
include './lib/koneksi.php';

if (isset($_SESSION['kasir_id']) == false) {
    header("Location: index.php");
    exit;
}

// JIKA TOMBOL BAYAR DITEKAN OLEH KASIR
if (isset($_POST['tombol_submit_bayar'])) {

    // Ambil uang tagihan dan uang dari pelanggan
    $total_tagihan = $_POST['total_tagihan_rahasia'];
    $uang_dari_pelanggan = $_POST['uang_pelanggan'];
    $uang_kembalian = $uang_dari_pelanggan - $total_tagihan;

    // Ubah data keranjang jadi daftar (array) agar bisa dibaca PHP
    $daftar_belanjaan = json_decode($_POST['data_keranjang_rahasia'], true);

    // Kalau uang pelanggan cukup dan ada barang yang dibeli
    if ($uang_dari_pelanggan >= $total_tagihan && $total_tagihan > 0 && !empty($daftar_belanjaan)) {

        // 1. Simpan ke database buku transaksi
        $id_kasir_yang_jaga = $_SESSION['kasir_id'];
        mysqli_query($conn, "INSERT INTO transaksi (kasir_id, total_belanja, uang_bayar, kembalian) VALUES ('$id_kasir_yang_jaga', '$total_tagihan', '$uang_dari_pelanggan', '$uang_kembalian')");

        // 2. Kurangi stok barang di gudang satu per satu
        foreach ($daftar_belanjaan as $barang) {
            $id_yang_dibeli = $barang['id_barang'];
            $jumlah_yang_dibeli = $barang['jumlah'];
            mysqli_query($conn, "UPDATE produk SET stok = stok - $jumlah_yang_dibeli WHERE id = '$id_yang_dibeli'");
        }

        // 3. Nyalakan mode print struk
        $tampilkan_struk = true;
        $cetak_total = $total_tagihan;
        $cetak_bayar = $uang_dari_pelanggan;
        $cetak_kembali = $uang_kembalian;
    }
}

// Ambil semua barang dari database untuk dipajang
$semua_barang_toko = mysqli_query($conn, "SELECT * FROM produk ORDER BY nama_produk ASC");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Kasir - TepatKasir</title>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./style/style.css">
</head>

<body>

    <div class="navbar">
        <a href="#" class="navbar-brand">TepatKasir</a>
        <div class="navbar-menu">
            <a href="dashboard.php">Dashboard</a>
            <a href="kasir.php" class="aktif">Kasir POS</a>
            <a href="inventory.php">Inventory</a>
            <a href="riwayat.php">Riwayat</a>
            <span style="border-left:2px solid #000; padding-left:15px; margin-left:5px; font-weight:bold;">👋 Halo, <?= $_SESSION['username'] ?></span>
            <a href="logout.php" class="btn-merah">Keluar</a>
        </div>
    </div>

    <div class="kasir-wrapper">

        <div class="area-kiri box-putih">
            <input type="text" id="kotak_pencarian" class="kolom-ketik" onkeyup="cariBarangPintar()" placeholder="🔍 Cari nama barang..." style="margin-bottom: 20px;">

            <div class="product-grid">
                <?php while ($barang = mysqli_fetch_assoc($semua_barang_toko)): ?>

                    <?php
                    // Kalau stok habis, beri tanda "habis" agar jadi abu-abu
                    $status = "";
                    if ($barang['stok'] <= 0) {
                        $status = "habis";
                    }

                    // Bersihkan nama agar aman untuk Javascript
                    $nama_aman = htmlspecialchars($barang['nama_produk'], ENT_QUOTES);
                    ?>

                    <div class="card <?= $status ?> kotak-barang" onclick="tambahKeKeranjang(<?= $barang['id'] ?>, '<?= $nama_aman ?>', <?= $barang['harga'] ?>, <?= $barang['stok'] ?>)">
                        <h3 class="judul-barang"><?= $barang['nama_produk'] ?></h3>
                        <p style="color:#34A853; font-weight:800; font-size:18px;">Rp <?= number_format($barang['harga'], 0, ',', '.') ?></p>
                        <small style="background:#202124; color:#fff; padding:4px 10px; border-radius:3px; font-weight:bold;">Stok: <?= $barang['stok'] ?></small>
                    </div>

                <?php endwhile; ?>
            </div>
        </div>

        <div class="area-kanan">
            <h2 style="color: #fff; margin-bottom: 15px;">Daftar Belanja</h2>

            <div id="layar_keranjang" class="list-keranjang">
                <p style="text-align:center; color:#fff; font-weight:bold; margin-top:30px;">Keranjang kosong</p>
            </div>

            <h2 id="layar_total_harga" style="color: #39FF14; font-size: 32px; font-weight: 900; margin-bottom: 10px;">Total: Rp 0</h2>

            <form method="POST" onsubmit="return pastikanBisaBayar()">
                <input type="hidden" name="total_tagihan_rahasia" id="input_total">
                <input type="hidden" name="data_keranjang_rahasia" id="input_data_keranjang">

                <input type="number" name="uang_pelanggan" class="kolom-ketik" required placeholder="Uang Pelanggan (Rp)" style="margin-bottom: 15px;">
                <button type="submit" name="tombol_submit_bayar" class="btn-hijau" style="font-size: 18px;">Bayar & Cetak Struk</button>
            </form>
        </div>
    </div>

    <?php if (isset($tampilkan_struk)): ?>
        <div id="area-struk">
            <h2 style="text-align:center; margin-bottom:0; font-size:16px; font-weight:bold;">TEPATKASIR</h2>
            <p style="text-align:center; margin-top:2px;">Terima Kasih</p>
            <div style="border-top: 1px dashed #000; margin: 10px 0;"></div>

            <p>Kasir: <?= $_SESSION['username'] ?><br>Waktu: <?= date('d/m/Y H:i') ?></p>
            <div style="border-top: 1px dashed #000; margin: 10px 0;"></div>

            <?php foreach ($daftar_belanjaan as $barang_dibeli): ?>
                <p style="margin: 5px 0; font-weight:bold;"><?= $barang_dibeli['nama_barang'] ?></p>
                <div style="display:flex; justify-content:space-between; margin-bottom:5px;">
                    <span><?= $barang_dibeli['jumlah'] ?> x <?= number_format($barang_dibeli['harga_satuan'], 0, ',', '.') ?></span>
                    <span><?= number_format($barang_dibeli['jumlah'] * $barang_dibeli['harga_satuan'], 0, ',', '.') ?></span>
                </div>
            <?php endforeach; ?>

            <div style="border-top: 1px dashed #000; margin: 10px 0;"></div>
            <div style="display:flex; justify-content:space-between; font-weight:bold;">
                <span>TOTAL:</span> <span>Rp <?= number_format($cetak_total, 0, ',', '.') ?></span>
            </div>
            <div style="display:flex; justify-content:space-between;">
                <span>TUNAI:</span> <span>Rp <?= number_format($cetak_bayar, 0, ',', '.') ?></span>
            </div>
            <div style="display:flex; justify-content:space-between;">
                <span>KEMBALI:</span> <span>Rp <?= number_format($cetak_kembali, 0, ',', '.') ?></span>
            </div>
            <div style="border-top: 1px dashed #000; margin: 10px 0;"></div>
        </div>
        <script>
            window.onload = function() {
                window.print();
            }
        </script>
    <?php endif; ?>


    <script>
        function cariBarangPintar() {
            let tulisan_dicari = document.getElementById("kotak_pencarian").value.toLowerCase();
            let semua_kotak_barang = document.querySelectorAll(".kotak-barang");

            for (let urutan = 0; urutan < semua_kotak_barang.length; urutan++) {
                let kotak = semua_kotak_barang[urutan];
                let nama_barang = kotak.querySelector(".judul-barang").innerText.toLowerCase();

                if (nama_barang.includes(tulisan_dicari)) {
                    kotak.style.display = "block";
                } else {
                    kotak.style.display = "none";
                }
            }
        }

        let keranjang_belanja = [];

        function tambahKeKeranjang(id_dipilih, nama_dipilih, harga_dipilih, stok_di_toko) {
            let barang_sudah_ada = false;

            for (let urutan = 0; urutan < keranjang_belanja.length; urutan++) {
                if (keranjang_belanja[urutan].id_barang == id_dipilih) {
                    barang_sudah_ada = true;

                    if (keranjang_belanja[urutan].jumlah + 1 > stok_di_toko) {
                        alert("Gagal! Stok di toko habis.");
                        return;
                    }

                    keranjang_belanja[urutan].jumlah += 1;
                }
            }

            if (barang_sudah_ada == false) {
                keranjang_belanja.push({
                    id_barang: id_dipilih,
                    nama_barang: nama_dipilih,
                    harga_satuan: harga_dipilih,
                    jumlah: 1,
                    maksimal_stok: stok_di_toko
                });
            }

            gambarUlangLayarKeranjang();
        }

        function kurangiDariKeranjang(id_dipilih) {
            for (let urutan = 0; urutan < keranjang_belanja.length; urutan++) {
                if (keranjang_belanja[urutan].id_barang == id_dipilih) {

                    keranjang_belanja[urutan].jumlah -= 1;

                    if (keranjang_belanja[urutan].jumlah == 0) {
                        keranjang_belanja.splice(urutan, 1);
                    }
                    break;
                }
            }
            gambarUlangLayarKeranjang();
        }

        function gambarUlangLayarKeranjang() {
            let tempat_gambar = document.getElementById('layar_keranjang');
            tempat_gambar.innerHTML = "";

            let total_bayar = 0;

            if (keranjang_belanja.length == 0) {
                tempat_gambar.innerHTML = '<p style="text-align:center; color:#fff; font-weight:bold; margin-top:30px;">Keranjang kosong</p>';
                document.getElementById('layar_total_harga').innerHTML = "Total: Rp 0";
                document.getElementById('input_total').value = 0;
                document.getElementById('input_data_keranjang').value = "";
                return;
            }

            for (let urutan = 0; urutan < keranjang_belanja.length; urutan++) {
                let barang = keranjang_belanja[urutan];
                let sub_total = barang.harga_satuan * barang.jumlah;
                total_bayar += sub_total;

                let desain_html = '<div style="background: white; padding: 15px; margin-bottom: 10px; border: 2px solid #000; border-radius: 5px;">';

                desain_html += '<div style="display: flex; justify-content: space-between; font-weight: bold; margin-bottom: 10px;">';
                desain_html += '<span style="color: #202124;">' + barang.nama_barang + '</span>';
                desain_html += '<span style="color: #1abc9c;">Rp ' + sub_total.toLocaleString('id-ID') + '</span>';
                desain_html += '</div>';

                desain_html += '<div style="display: flex; align-items: center; gap: 10px;">';
                desain_html += '<button type="button" onclick="kurangiDariKeranjang(' + barang.id_barang + ')" style="background: #e74c3c; color: white; border: 2px solid #000; width: 30px; height: 30px; font-weight: bold; border-radius: 3px; cursor: pointer; box-shadow: 2px 2px 0 #000;">-</button>';
                desain_html += '<span style="font-weight: bold; color: #202124;">' + barang.jumlah + '</span>';
                desain_html += '<button type="button" onclick="tambahKeKeranjang(' + barang.id_barang + ', \'' + barang.nama_barang + '\', ' + barang.harga_satuan + ', ' + barang.maksimal_stok + ')" style="background: #3498db; color: white; border: 2px solid #000; width: 30px; height: 30px; font-weight: bold; border-radius: 3px; cursor: pointer; box-shadow: 2px 2px 0 #000;">+</button>';
                desain_html += '</div>';

                desain_html += '</div>';

                tempat_gambar.innerHTML += desain_html;
            }

            document.getElementById('layar_total_harga').innerHTML = "Total: Rp " + total_bayar.toLocaleString('id-ID');
            document.getElementById('input_total').value = total_bayar;
            document.getElementById('input_data_keranjang').value = JSON.stringify(keranjang_belanja);
        }

        function pastikanBisaBayar() {
            if (keranjang_belanja.length == 0) {
                alert("Keranjang masih kosong!");
                return false;
            }
            return true;
        }
    </script>
</body>

</html>