<?php 
// [LAMA] <!-- FILE BERISIKAN FUNCTION PHP -->  (ada di luar tag PHP => menghasilkan output sebelum session_start()/header())
// [BARU] komentar dipindah ke dalam PHP: FILE BERISIKAN FUNCTION PHP
$koneksi = mysqli_connect('localhost','root','','kasir_rental_ps');
session_start();

// [BARU] Zona waktu WIB, supaya tanggal/jam pesanan dan laporan harian tidak selisih 7 jam
date_default_timezone_set('Asia/Jakarta');
mysqli_query($koneksi, "SET time_zone = '+07:00'");

// [BARU] Format rupiah: 100000 => Rp 100.000
if (!function_exists('rupiah')) {
    function rupiah($angka) {
        return 'Rp ' . number_format((float)$angka, 0, ',', '.');
    }
}

// [BARU] STATUS PESANAN: baru -> diantar -> lunas
if (!function_exists('daftarStatus')) {
    // daftar status yang diizinkan (kode di database => tulisan di layar)
    function daftarStatus() {
        return array('baru' => 'Baru', 'diantar' => 'Diantar', 'lunas' => 'Lunas');
    }

    // label berwarna untuk status
    function badgeStatus($status) {
        $warna = array('baru' => 'bg-secondary', 'diantar' => 'bg-warning text-dark', 'lunas' => 'bg-success');
        $label = daftarStatus();
        $kelas = isset($warna[$status]) ? $warna[$status] : 'bg-secondary';
        $teks = isset($label[$status]) ? $label[$status] : $status;
        return '<span class="badge ' . $kelas . '">' . $teks . '</span>';
    }

    // true jika pesanan sudah lunas
    function pesananLunas($koneksi, $idp) {
        $cek = mysqli_query($koneksi, "SELECT status FROM pesanan where idpesanan='$idp'");
        $row = mysqli_fetch_array($cek);
        return ($row && $row['status'] == 'lunas');
    }

    // pesanan lunas tidak boleh diubah: tampilkan pesan lalu hentikan proses
    function tolakJikaLunas($koneksi, $idp, $kembali) {
        if (pesananLunas($koneksi, $idp)) {
            echo '<script>alert("Pesanan sudah lunas, tidak bisa diubah. Ubah status dulu jika perlu koreksi"); window.location.href="' . $kembali . '"</script>';
            exit;
        }
    }
}
// Membuat Koneksi dengan SQL
// Nama koneksi beda dengan video di video kasir, di sini kasir_rental_ps
// Koneksi di video pakek $c tapi ku ganti $koneksi biar lebih jelas



// Untuk Login

if (isset($_POST['login'])) {
    // Variabel yang dipakai
    $username = $_POST['username'];
    $password = $_POST['password'];

    $check = mysqli_query($koneksi,"SELECT * FROM user WHERE username = '$username' and password = '$password'");
    $hitung = mysqli_num_rows($check);

    if ($hitung > 0) {
        // Jika data username ada dan bisa login

        $_SESSION['login'] = 'True';
        header('location:index.php'); exit; // [BARU] exit supaya kode di bawahnya tidak ikut jalan
    } else {
        // Jika data tidak ada dan tidak boleh login
        // [LAMA] window.locattion.href="login.php"   <- salah ketik (locattion), sudah diperbaiki menjadi window.location di bawah

        echo '
        <script>
        alert("Username / Password Salah");
        window.location.href="login.php"
        </script>
        ';
    }

}

// FUNCTION YANG DIGUNAKAN UNTUK TAMBAH BARANG DI STOK

if (isset($_POST['tambahbarang'])) {
    $namaproduk = $_POST['namaproduk'];
    $deskripsi = $_POST['deskripsi'];
    $harga = $_POST['harga'];

    // [BARU] validasi sederhana: nama wajib diisi, harga harus angka >= 0
    if ($namaproduk == '' || !is_numeric($harga) || $harga < 0) {
        echo '<script>alert("Nama produk wajib diisi dan harga harus berupa angka"); window.location.href="stok.php"</script>';
        exit;
    }

    $tambahproduk = mysqli_query($koneksi, "INSERT INTO produk(namaproduk,deskripsi,harga) VALUES ('$namaproduk','$deskripsi','$harga')");

    if ($tambahproduk) {
        header('location:stok.php'); exit; // [BARU] exit supaya kode di bawahnya tidak ikut jalan
    } else {
           echo '
        <script>
        alert("Gagal Menambah Barang Baru");
        window.location.href="stok.php"
        </script>
        ';
    }
}

// FUNCTION YANG DIGUNAKAN UNTUK TAMBAH PESANAN

if (isset($_POST['tambahpesanan'])) {
    $idruang = $_POST['idruang'];
    $tambahpesanan = mysqli_query($koneksi, "INSERT INTO pesanan(id_ruang) VALUES ('$idruang')");

    if ($tambahpesanan) {
        header('location:index.php'); exit; // [BARU] exit supaya kode di bawahnya tidak ikut jalan
    } else {
           echo '
        <script>
        alert("Gagal Menambah Pesanan Baru");
        window.location.href="index.php"
        </script>
        ';
    }
}

// FUNCTION YANG DIGUNAKAN UNTUK TAMBAH PRODUK

if (isset($_POST['addproduk'])) {
    // [LAMA] $idproduk = $_POST['idproduk'];
    $idproduk = isset($_POST['idproduk']) ? $_POST['idproduk'] : ''; // [BARU] cegah error jika dropdown kosong
     $idp = $_POST['idp'];
     $qty = $_POST['qty']; // jumlah

    // [BARU] pesanan yang sudah lunas tidak boleh ditambah item
    tolakJikaLunas($koneksi, $idp, 'view.php?idp=' . $idp);

    // [BARU] produk wajib dipilih dan jumlah minimal 1
    if ($idproduk == '' || (int)$qty < 1) {
        echo '<script>alert("Pilih produk dan isi jumlah minimal 1"); window.location.href="view.php?idp=' . $idp . '"</script>';
        exit;
    }
    
    $addproduk = mysqli_query($koneksi, "INSERT INTO detailpesanan(idpesanan,idproduk,qty) VALUES ('$idp','$idproduk','$qty')");

    if ($addproduk) {
        header('location:view.php?idp='.$idp); exit; // [BARU] exit supaya kode di bawahnya tidak ikut jalan
    } else {
           echo '
        <script>
        alert("Gagal Menambah Item");
        // [LAMA] window.location.href="view.php?idp="'.$idp.'   (tanda kutip salah => error JavaScript)
        window.location.href="view.php?idp='.$idp.'"
        </script>
        ';
    }
}

// FUNCTION YANG DIGUNAKAN UNTUK EDIT BARANG

if(isset($_POST['editbarang'])) {
    $namaproduk = $_POST['namaproduk'];
    $deskripsi = $_POST['deskripsi'];
    $harga = $_POST['harga'];
    $idp = $_POST['idp'];

    // [BARU] validasi sederhana: nama wajib diisi, harga harus angka >= 0
    if ($namaproduk == '' || !is_numeric($harga) || $harga < 0) {
        echo '<script>alert("Nama produk wajib diisi dan harga harus berupa angka"); window.location.href="stok.php"</script>';
        exit;
    }

    $query = mysqli_query($koneksi, "UPDATE produk set namaproduk='$namaproduk', deskripsi='$deskripsi',harga='$harga' where idproduk='$idp'");

    if($query){
 header('location:stok.php'); exit; // [BARU] exit supaya kode di bawahnya tidak ikut jalan
    } else {
           echo '
        <script>
        alert("Gagal Mengubah Produk");
        window.location.href="stok.php"
        </script>
        ';
    }
    
}

// FUNCTION YANG DIGUNAKAN UNTUK HAPUS BARANG

if(isset($_POST['hapusbarang'])) {
    $idp = $_POST['idp'];

    // [BARU] produk yang sudah pernah dipesan tidak boleh dihapus (laporan penjualan akan kehilangan datanya)
    $dipakai = mysqli_num_rows(mysqli_query($koneksi, "SELECT 1 FROM detailpesanan where idproduk='$idp'"));
    if ($dipakai > 0) {
        echo '<script>alert("Produk tidak bisa dihapus karena sudah dipakai di pesanan"); window.location.href="stok.php"</script>';
        exit;
    }

    // [LAMA] $query = mysqli_query($koneksi, "DELETE FROM PRODUK where idproduk='$idp'");  (nama tabel huruf besar gagal di server Linux)
    $query = mysqli_query($koneksi, "DELETE FROM produk where idproduk='$idp'");
    if($query){
 header('location:stok.php'); exit; // [BARU] exit supaya kode di bawahnya tidak ikut jalan
    } else {
           echo '
        <script>
        alert("Gagal Hapus Produk");
        window.location.href="stok.php"
        </script>
        ';
    }
}

// [BARU] FUNCTION YANG DIGUNAKAN UNTUK UBAH STATUS PESANAN

if (isset($_POST['ubahstatus'])) {
    $idp = $_POST['idp'];
    $status = $_POST['status'];
    // setelah simpan, kembali ke halaman asal (daftar pesanan atau detail pesanan)
    $kembali = (isset($_POST['kembali']) && $_POST['kembali'] == 'view') ? 'view.php?idp=' . $idp : 'index.php';

    // hanya status yang ada di daftarStatus() yang boleh disimpan
    if (!array_key_exists($status, daftarStatus())) {
        echo '<script>alert("Status tidak valid"); window.location.href="' . $kembali . '"</script>';
        exit;
    }

    $query = mysqli_query($koneksi, "UPDATE pesanan set status='$status' where idpesanan='$idp'");
    if ($query) {
        header('location:' . $kembali); exit;
    } else {
        echo '<script>alert("Gagal Mengubah Status Pesanan"); window.location.href="' . $kembali . '"</script>';
    }
}

// FUNCTION YANG DIGUNAKAN UNTUK HAPUS PESANAN

if(isset($_POST['hapuspesanan'])) {
    $idp = $_POST['idp'];

    // [BARU] pesanan yang sudah lunas tidak boleh dihapus (laporan penjualan ikut hilang)
    tolakJikaLunas($koneksi, $idp, 'index.php');

    // [BARU] hapus dulu item-itemnya supaya tidak ada data yatim di detailpesanan
    mysqli_query($koneksi, "DELETE FROM detailpesanan where idpesanan='$idp'");

    $query = mysqli_query($koneksi, "DELETE FROM pesanan where idpesanan='$idp'");
    if($query){
 header('location:index.php'); exit; // [BARU] exit supaya kode di bawahnya tidak ikut jalan
    } else {
           echo '
        <script>
        alert("Gagal Hapus Pesanan");
        window.location.href="index.php"
        </script>
        ';
    }
}

// FUNCTION YANG DIGUNAKAN UNTUK HAPUS DETAIL PESANAN

if(isset($_POST['hapusdetail'])) {
    $idp = $_POST['idp'];
    $idpr = $_POST['idpr'];

    // [BARU] pesanan yang sudah lunas tidak boleh diubah
    tolakJikaLunas($koneksi, $idp, 'view.php?idp=' . $idp);

    $query = mysqli_query($koneksi, "DELETE FROM detailpesanan where iddetailpesanan='$idpr'");
    if($query){
 header('location:view.php?idp='.$idp); exit; // [BARU] exit supaya kode di bawahnya tidak ikut jalan
    } else {
           echo '
        <script>
        alert("Gagal Hapus Item");
        // [LAMA] window.location.href="view.php='.$idp.'"   (kurang tanda tanya)
        window.location.href="view.php?idp='.$idp.'"
        </script>
        ';
    }
}

// FUNCTION YANG DIGUNAKAN UNTUK EDIT BARANG DI DETAIL PESANAN

if(isset($_POST['editdetail'])) {
    $idproduk = $_POST['idproduk'];
    $qty = $_POST['qty'];
    $idp = $_POST['idp'];
     $idpr = $_POST['idpr'];
   

    // [BARU] pesanan yang sudah lunas tidak boleh diubah
    tolakJikaLunas($koneksi, $idp, 'view.php?idp=' . $idp);

    $query = mysqli_query($koneksi, "UPDATE detailpesanan set idproduk='$idproduk', qty='$qty' where iddetailpesanan ='$idpr'");

    if($query){
 header('location:view.php?idp='.$idp); exit; // [BARU] exit supaya kode di bawahnya tidak ikut jalan
    } else {
           echo '
        <script>
        alert("Gagal Mengubah Item");
        window.location.href="view.php?idp='.$idp.'"
        </script>
        ';
    }
    
}

?>