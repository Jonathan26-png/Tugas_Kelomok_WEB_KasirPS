<!-- FILE BERISIKAN FUNCTION PHP -->
<?php 
$koneksi = mysqli_connect('localhost','root','','kasir_rental_ps');
session_start();
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
        header('location:index.php');
    } else {
        // Jika data tidak ada dan tidak boleh login

        echo '
        <script>
        alert("Username / Password Salah");
        window.locattion.href="login.php"
        </script>
        ';
    }

}

// FUNCTION YANG DIGUNAKAN UNTUK TAMBAH BARANG DI STOK

if (isset($_POST['tambahbarang'])) {
    $namaproduk = $_POST['namaproduk'];
    $deskripsi = $_POST['deskripsi'];
    $harga = $_POST['harga'];

    $tambahproduk = mysqli_query($koneksi, "INSERT INTO produk(namaproduk,deskripsi,harga) VALUES ('$namaproduk','$deskripsi','$harga')");

    if ($tambahproduk) {
        header('location:stok.php');
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
        header('location:index.php');
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
    $idproduk = $_POST['idproduk'];
     $idp = $_POST['idp'];
     $qty = $_POST['qty']; // jumlah
    
    $addproduk = mysqli_query($koneksi, "INSERT INTO detailpesanan(idpesanan,idproduk,qty) VALUES ('$idp','$idproduk','$qty')");

    if ($addproduk) {
        header('location:view.php?idp='.$idp);
    } else {
           echo '
        <script>
        alert("Gagal Menambah Pesanan Baru");
        window.location.href="view.php?idp="'.$idp.'
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

    $query = mysqli_query($koneksi, "UPDATE produk set namaproduk='$namaproduk', deskripsi='$deskripsi',harga='$harga' where idproduk='$idp'");

    if($query){
 header('location:stok.php');
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

    $query = mysqli_query($koneksi, "DELETE FROM PRODUK where idproduk='$idp'");
    if($query){
 header('location:stok.php');
    } else {
           echo '
        <script>
        alert("Gagal Hapus Produk");
        window.location.href="stok.php"
        </script>
        ';
    }
}

// FUNCTION YANG DIGUNAKAN UNTUK HAPUS PESANAN

if(isset($_POST['hapuspesanan'])) {
    $idp = $_POST['idp'];

    $query = mysqli_query($koneksi, "DELETE FROM pesanan where idpesanan='$idp'");
    if($query){
 header('location:index.php');
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

    $query = mysqli_query($koneksi, "DELETE FROM detailpesanan where iddetailpesanan='$idpr'");
    if($query){
 header('location:view.php?idp='.$idp);
    } else {
           echo '
        <script>
        alert("Gagal Hapus Pesanan");
        window.location.href="view.php='.$idp.'"
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
   

    $query = mysqli_query($koneksi, "UPDATE detailpesanan set idproduk='$idproduk', qty='$qty' where iddetailpesanan ='$idpr'");

    if($query){
 header('location:view.php?idp='.$idp);
    } else {
           echo '
        <script>
        alert("Gagal Mengubah Produk");
        window.location.href="view.php?idp='.$idp.'"
        </script>
        ';
    }
    
}

?>