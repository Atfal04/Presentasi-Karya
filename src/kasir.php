<?php
// 1. NYALAKAN SESI DAN KONEKSI DATABASE
session_start();
include './lib/koneksi.php';

// Kalau belum login, kembali ke halaman login
if (isset($_SESSION['kasir_id']) == false) {
    header("Location: index.php");
    exit;
}

// =====================================================================
// 2. LOGIKA JIKA TOMBOL BAYAR DITEKAN OLEH KASIR
// =====================================================================
if (isset($_POST['tombol_submit_bayar'])) {

    // Ambil tagihan, uang pelanggan, dan hitung kembaliannya
    $total_tagihan = $_POST['total_tagihan_rahasia'];
    $uang_dari_pelanggan = $_POST['uang_pelanggan'];
    $uang_kembalian = $uang_dari_pelanggan - $total_tagihan;

    // Ubah data keranjang (yang dikirim Javascript) jadi daftar yang bisa dibaca PHP
    $daftar_belanjaan = json_decode($_POST['data_keranjang_rahasia'], true);

    // Kalau uang pelanggan cukup, tagihan tidak 0, dan keranjang tidak kosong
    if ($uang_dari_pelanggan >= $total_tagihan && $total_tagihan > 0 && !empty($daftar_belanjaan)) {

        // Simpan ke buku riwayat transaksi
        $id_kasir_yang_jaga = $_SESSION['kasir_id'];
        mysqli_query($conn, "INSERT INTO transaksi (kasir_id, total_belanja, uang_bayar, kembalian) VALUES ('$id_kasir_yang_jaga', '$total_tagihan', '$uang_dari_pelanggan', '$uang_kembalian')");

        // Buka daftar keranjang satu per satu, kurangi stoknya di gudang
        foreach ($daftar_belanjaan as $barang) {
            $id_yang_dibeli = $barang['id_barang'];
            $jumlah_yang_dibeli = $barang['jumlah'];
            mysqli_query($conn, "UPDATE produk SET stok = stok - $jumlah_yang_dibeli WHERE id = '$id_yang_dibeli'");
        }

        // Nyalakan mode print struk
        $tampilkan_struk = true;
        $cetak_total = $total_tagihan;
        $cetak_bayar = $uang_dari_pelanggan;
        $cetak_kembali = $uang_kembalian;
    }
}

// 3. AMBIL SEMUA BARANG DARI GUDANG UNTUK DIPAJANG DI KIRI
$semua_barang_toko = mysqli_query($conn, "SELECT * FROM produk ORDER BY nama_produk ASC");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kasir - TepatKasir</title>
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
            <span style="border-left:2px solid #000; padding-left:15px; margin-left:5px; font-weight:900;">👋 Halo, <?= $_SESSION['username'] ?></span>
            <a href="logout.php" class="btn-merah">Keluar</a>
        </div>
    </div>

    <div class="wadah-kasir-scroll">
        <div class="kasir-wrapper">

            <div class="area-kiri box-putih" style="padding-top: 20px;">

                <input type="text" id="kotak_pencarian" onkeyup="cariBarangPintar()" placeholder="🔍 Cari nama barang..." style="width: 100%; padding: 15px; margin-bottom: 20px; border: 2px solid #000; border-radius: 4px; font-weight: 800; font-size: 15px; outline: none; box-sizing: border-box;">

                <div class="product-grid">
                    <?php while ($barang = mysqli_fetch_assoc($semua_barang_toko)): ?>
                        <?php
                        $status = ($barang['stok'] <= 0) ? "habis" : "";
                        $nama_aman = htmlspecialchars($barang['nama_produk'], ENT_QUOTES);
                        ?>

                        <div class="card <?= $status ?> kotak-barang" onclick="tambahKeKeranjang(<?= $barang['id'] ?>, '<?= $nama_aman ?>', <?= $barang['harga'] ?>, <?= $barang['stok'] ?>)">
                            <h3 class="judul-barang" style="font-weight:900; font-size:16px; margin-bottom:10px;"><?= $barang['nama_produk'] ?></h3>
                            <p style="color:#34A853; font-weight:900; font-size:18px; margin-bottom:12px;">Rp <?= number_format($barang['harga'], 0, ',', '.') ?></p>
                            <small style="background:#202124; color:#fff; padding:4px 10px; border-radius:4px; font-weight:bold;">Stok: <?= $barang['stok'] ?></small>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <div class="area-kanan">
                <h2 style="color: #fff; margin-bottom: 20px; font-weight:900; font-size:22px;">Daftar Belanja</h2>

                <div id="layar_keranjang" class="list-keranjang">
                    <p style="text-align:center; color:#fff; font-weight:bold; margin-top:30px;">Keranjang kosong</p>
                </div>

                <h2 id="layar_total_harga" style="color: #39FF14; font-size: 36px; font-weight: 900; margin-top: 10px; margin-bottom: 15px;">Total: Rp 0</h2>

                <form method="POST" onsubmit="return pastikanBisaBayar()">
                    <input type="hidden" name="total_tagihan_rahasia" id="input_total">
                    <input type="hidden" name="data_keranjang_rahasia" id="input_data_keranjang">

                    <input type="number" name="uang_pelanggan" required placeholder="Uang Pelanggan (Rp)" style="width: 100%; padding: 15px; margin-bottom: 15px; border: 2px solid #000; border-radius: 4px; font-size: 16px; font-weight: bold; outline: none; box-sizing: border-box;">

                    <button type="submit" name="tombol_submit_bayar" style="width: 100%; background-color: #39FF14; color: #000; font-size: 18px; font-weight: 900; padding: 15px; border: 2px solid #000; border-radius: 4px; cursor: pointer; box-shadow: 2px 2px 0px #000; text-transform: uppercase;">BAYAR & CETAK STRUK</button>
                </form>
            </div>

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
            <div style="display:flex; justify-content:space-between; font-weight:bold;"><span>TOTAL:</span> <span>Rp <?= number_format($cetak_total, 0, ',', '.') ?></span></div>
            <div style="display:flex; justify-content:space-between;"><span>TUNAI:</span> <span>Rp <?= number_format($cetak_bayar, 0, ',', '.') ?></span></div>
            <div style="display:flex; justify-content:space-between;"><span>KEMBALI:</span> <span>Rp <?= number_format($cetak_kembali, 0, ',', '.') ?></span></div>
            <div style="border-top: 1px dashed #000; margin: 10px 0;"></div>
        </div>

        <script>
            window.onload = function() {
                window.print();
            }
        </script>
    <?php endif; ?>


    <script>
        // 1. FUNGSI PENCARIAN BARANG
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

        // Keranjang disiapkan kosong
        let keranjang_belanja = [];

        // 2. FUNGSI TAMBAH BARANG KE KERANJANG
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

        // 3. FUNGSI KURANGI BARANG DARI KERANJANG
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

        // 4. FUNGSI MENGGAMBAR KOTAK KERANJANG PERSIS SEPERTI DI FOTO
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

                // --- KODE HTML DESAIN KOTAK PUTIH KERANJANG ---
                let desain_html = '<div style="background: white; padding: 15px; margin-bottom: 12px; border: 2px solid #000; border-radius: 8px;">';

                // Baris Atas (Nama Barang & Harga Hijau Tosca)
                desain_html += '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">';
                desain_html += '<span style="color: #202124; font-weight: 900; font-size:16px;">' + barang.nama_barang + '</span>';
                desain_html += '<span style="color: #1abc9c; font-weight: 900; font-size:16px;">Rp ' + sub_total.toLocaleString('id-ID') + '</span>';
                desain_html += '</div>';

                // Baris Bawah (Tombol Minus, Angka, Tombol Plus)
                desain_html += '<div style="display: flex; align-items: center; gap: 15px;">';
                desain_html += '<button type="button" onclick="kurangiDariKeranjang(' + barang.id_barang + ')" style="background: #EA4335; color: white; border: 2px solid #000; width: 35px; height: 35px; font-weight: 900; border-radius: 4px; cursor: pointer; box-shadow: 2px 2px 0 #000; font-size: 16px;">-</button>';
                desain_html += '<span style="font-weight: 900; color: #202124; font-size:16px; width: 20px; text-align: center;">' + barang.jumlah + '</span>';
                desain_html += '<button type="button" onclick="tambahKeKeranjang(' + barang.id_barang + ', \'' + barang.nama_barang + '\', ' + barang.harga_satuan + ', ' + barang.maksimal_stok + ')" style="background: #4285F4; color: white; border: 2px solid #000; width: 35px; height: 35px; font-weight: 900; border-radius: 4px; cursor: pointer; box-shadow: 2px 2px 0 #000; font-size: 16px;">+</button>';
                desain_html += '</div></div>';
                // ----------------------------------------------

                tempat_gambar.innerHTML += desain_html;
            }

            // Perbarui tulisan layar dan isi data tersembunyi
            document.getElementById('layar_total_harga').innerHTML = "Total: Rp " + total_bayar.toLocaleString('id-ID');
            document.getElementById('input_total').value = total_bayar;
            document.getElementById('input_data_keranjang').value = JSON.stringify(keranjang_belanja);
        }

        // 5. FUNGSI CEK SEBELUM BAYAR
        function pastikanBisaBayar() {
            if (keranjang_belanja.length == 0) {
                alert("Pilih barang terlebih dahulu!");
                return false;
            }
            return true;
        }
    </script>
</body>

</html>