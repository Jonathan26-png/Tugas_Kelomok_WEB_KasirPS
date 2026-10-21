<!-- FILE BERISIKAN FUNCTION PHP -->
<?php 

session_start();

// Membuat Koneksi dengan SQL
// Nama koneksi beda dengan video di video kasir, di sini kasir_rental_ps
// Koneksi di video pakek $c tapi ku ganti $koneksi biar lebih jelas

$koneksi = mysqli_connect('localhost','root','','kasir_rental_ps');

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
        header('location:index.php');
    } else {
        // Jika data tidak ada dan tidak boleh login

        echo '
        <script>
        alert("Username / Password Salah");
        window.ocaiton.href="login.php"
        </script>
        ';
    }

}

// FUNCTION YANG DIGUNAKAN UNTUK TAMBAH BARANG DI STOK

if (isset($_POST['tambahbarang'])) {
    $namaproduk = $_POST['namaproduk'];
    $deskripsi = $_POST['deskripsi'];
    $stok = $_POST['stok'];
    $harga = $_POST['harga'];

    $tambahproduk = mysqli_query($koneksi, "INSERT INTO produk(namaproduk,deskripsi,harga,stok) VALUES ('$namaproduk','$deskripsi','$harga','$stok')");

    if ($tambahproduk) {
        header('location:stok.php');
    } else {
           echo '
        <script>
        alert("Gagal Menambah Barang Baru");
        window.ocaiton.href="stok.php"
        </script>
        ';
    }
}

?>