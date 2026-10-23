<?php

require 'checklogin.php';
require_once 'function.php';
if (isset($_GET['idp'])) {
    $idp = $_GET['idp'];

    $ambilruang = mysqli_query($koneksi, "Select * from pesanan p, ruang r where p.id_ruang=r.id_ruang and p.idpesanan='$idp'");
    $nr = mysqli_fetch_array($ambilruang);
    // [BARU] kalau id pesanan tidak ditemukan, kembali ke daftar pesanan (sebelumnya muncul warning)
    if (!$nr) { header('location:index.php'); exit; }
    $ruang = $nr['namaruang'];
    $status = $nr['status'];          // [BARU] status pesanan ini
    $lunas = ($status == 'lunas');    // [BARU] pesanan lunas tidak boleh diubah itemnya
} else {
    header('location:index.php'); exit; // [BARU] exit
}
// [LAMA] $result = mysqli_query($koneksi, "SELECT * FROM detailpesanan");  (menghitung item SEMUA pesanan)
$result = mysqli_query($koneksi, "SELECT * FROM detailpesanan where idpesanan='$idp'");


$count = mysqli_num_rows($result);

// [BARU] total yang harus dibayar untuk pesanan ini
$hitungtotal = mysqli_query($koneksi, "SELECT sum(d.qty * pr.harga) as total FROM detailpesanan d, produk pr WHERE d.idproduk=pr.idproduk AND d.idpesanan='$idp'");
$total = mysqli_fetch_array($hitungtotal)['total'] ?? 0;

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Pesanan</title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
</head>

<body class="sb-nav-fixed">
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <!-- Navbar Brand-->
        <a class="navbar-brand ps-3" href="index.php">Aplikasi Kasir RentalPS</a>
        <!-- Sidebar Toggle-->
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars"></i></button>
    </nav>
    <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
            <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                <div class="sb-sidenav-menu">
                    <div class="nav">
                        <?php /* LAMA: <div class="sb-sidenav-menu-heading">MENU</div>

                        <!-- class="fas fa-tachometer-alt" bisa diubah biar ikon yang di navbar kiri berubah juga -->

                        <a class="nav-link" href="index.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                            Pesanan
                        </a>
                        <a class="nav-link" href="stok.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                            Produk
                        </a>
                        <a class="nav-link" href="masuk.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                            Dashboard
                        </a>
                        <a class="nav-link" href="logout.php">
                            Logout
                        </a> */ ?>
                        <div class="sb-sidenav-menu-heading">MENU</div>
                        <a class="nav-link active" href="index.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-receipt"></i></div>
                            Pesanan
                        </a>
                        <a class="nav-link" href="stok.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-utensils"></i></div>
                            Produk
                        </a>
                        <a class="nav-link" href="masuk.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-chart-line"></i></div>
                            Laporan
                        </a>
                        <a class="nav-link" href="logout.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-right-from-bracket"></i></div>
                            Logout
                        </a>
                    </div>
                </div>
                <div class="sb-sidenav-footer">
                    <div class="small">Logged in as:</div>
                    <?php /* LAMA: Start Bootstrap */ ?>Admin Kasir
                </div>
            </nav>
        </div>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <?php /* LAMA: <h1 class="mt-4">Data Pesanan: <?= $idp ?> </h1> */ ?><h1 class="mt-4">Detail Pesanan #<?= $idp ?></h1>
                    <?php /* LAMA: <h4 class="mt-4">Detail Ruang: <?= $ruang; ?></h4> */ ?><?php /* LAMA: <h4 class="mt-4">Ruang: <?= $ruang; ?></h4> */ ?><h4 class="mt-4">Ruang: <?= $ruang; ?> &nbsp; <?= badgeStatus($status) ?></h4>
                    <ol class="breadcrumb mb-4">
                        <?php /* LAMA: <li class="breadcrumb-item active">Dashboard</li> */ ?><li class="breadcrumb-item active">Detail Pesanan</li>
                    </ol>

                    <!-- Card Bagian Atas -->
                    <div class="row">
                        <div class="col-xl-3 col-md-6">
                            <div class="card bg-primary text-white mb-4">
                                <?php /* LAMA: <div class="card-body">Jumlah produk : <?= $count ?> </div> */ ?><div class="card-body">Jumlah item : <?= $count ?> </div>

                            </div>
                        </div>



                    </div>
                    <?php if ($lunas) { ?>
                    <div class="alert alert-success">Pesanan ini sudah lunas, item tidak dapat diubah. Gunakan tombol Ubah Status jika perlu koreksi.</div>
                    <?php } ?>
                    <!-- [BARU] Total pembayaran -->
                    <div class="row">
                        <div class="col-xl-3 col-md-6">
                            <div class="card bg-success text-white mb-4">
                                <div class="card-body">Total : <?= rupiah($total) ?></div>
                            </div>
                        </div>
                    </div>
                    <!-- Button to Open the Modal -->
                    <?php /* LAMA: <button type="button" class="btn btn-info mb-4" data-bs-toggle="modal" data-bs-target="#myModal"> */ ?><button type="button" class="btn btn-info mb-4" data-bs-toggle="modal" data-bs-target="#myModal" <?= $lunas ? 'disabled' : '' ?>>
                        <?php /* LAMA: Tambah Barang */ ?>Tambah Item
                    </button>
                    <button type="button" class="btn btn-secondary mb-4" data-bs-toggle="modal" data-bs-target="#statusModal">Ubah Status</button>
                    <a class="btn btn-primary mb-4" href="index.php">Kembali</a>
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-table me-1"></i>
                            <?php /* LAMA: Data Pesanan */ ?>Daftar Item
                        </div>
                        <div class="card-body">
                            <table id="datatablesSimple">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Produk</th>
                                        <th>Harga</th>
                                        <th>Jumlah</th>
                                        <th>Sub-total</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    <?php

                                    // Di video dia get, ganti tampil table biar jelas
                                    $tampiltabel = mysqli_query($koneksi, "SELECT * FROM detailpesanan p, produk pr where p.idproduk=pr.idproduk and p.idpesanan = '$idp'");
                                    $i = 1;

                                    while ($p = mysqli_fetch_array($tampiltabel)) {
                                        $qty = $p['qty'];
                                        $iddetail = $p['iddetailpesanan'];
                                        $namaproduk = $p['namaproduk'];
                                        $harga = $p['harga'];
                                        $subtotal = $qty * $harga;
                                        $idproduk = $p['idproduk'];
                                        $idprodukdetail = $p['idproduk']; // [BARU] produk milik baris ini (untuk dropdown Edit)

                                    ?>

                                        <tr>
                                            <td><?= $i++ ?></td>
                                            <td><?= $namaproduk ?></td>
                                            <?php /* LAMA: <td>Rp.<?= number_format($harga) ?></td> */ ?><td><?= rupiah($harga) ?></td>
                                            <td><?= number_format($qty) ?> </td>
                                            <?php /* LAMA: <td><?= number_format($subtotal) ?> </td> */ ?><td><?= rupiah($subtotal) ?></td>
                                            <?php /* LAMA: <td><button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#edit<?= $iddetail ?>"> */ ?><td><button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#edit<?= $iddetail ?>" <?= $lunas ? 'disabled' : '' ?>>
                                                    Edit </button>
                                                <?php /* LAMA: <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#hapus<?= $iddetail ?>"> */ ?><button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#hapus<?= $iddetail ?>" <?= $lunas ? 'disabled' : '' ?>>
                                                    <?php /* LAMA: Delete */ ?>Hapus </button>
                                            </td>
                                        </tr>

                                        <div class="modal fade" id="edit<?= $iddetail ?>">
                                            <div class="modal-dialog">
                                                <div class="modal-content">

                                                    <!-- Modal Header -->
                                                    <div class="modal-header">
                                                        <?php /* LAMA: <h4 class="modal-title">Edit Barang</h4> */ ?><h4 class="modal-title">Edit Item</h4>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>

                                                    <form method="post">

                                                        <!-- Modal body -->
                                                        <div class="modal-body">
                                                            <?php /* LAMA: Pilih Barang */ ?>Pilih Produk
                                                            <?php /* LAMA: <select name="idproduk" class="form-control">

                                                                <?php
                                                                $getproduk = mysqli_query($koneksi, "select * from produk where idproduk not in (select idproduk from detailpesanan where idpesanan='$idp')");
                                                                $getstok = mysqli_query($koneksi, "SELECT qty,iddetailpesanan from detailpesanan");

                                                                while ($pl = mysqli_fetch_array($getproduk)) {
                                                                    $namaproduk = $pl['namaproduk'];
                                                                    $idproduk = $pl['idproduk'];


                                                                ?>

                                                                    <option value="<?= $idproduk;  ?>"><?= $namaproduk;  ?></option>

                                                                <?php
                                                                }
                                                                ?>
                                                            </select>
                                                            <?php
                                                            while ($ps = mysqli_fetch_array($getstok)) {
                                                            $jumlah = $ps['qty'];

                                                            }
                                                            ?>
                                                            <input type="number" name="qty" class="form-control mt-4" value="<?= $jumlah ?>" placeholder="jumlah" min="1" required> */ ?>
                                                            <select name="idproduk" class="form-control">
                                                                <?php
                                                                // [BARU] produk baris ini ikut ditampilkan & otomatis terpilih (filter mengecualikan baris ini saja)
                                                                $getproduk = mysqli_query($koneksi, "select * from produk where idproduk not in (select idproduk from detailpesanan where idpesanan='$idp' and iddetailpesanan!='$iddetail')");

                                                                while ($pl = mysqli_fetch_array($getproduk)) {
                                                                    $pilih = ($pl['idproduk'] == $idprodukdetail) ? 'selected' : '';
                                                                ?>
                                                                    <option value="<?= $pl['idproduk'] ?>" <?= $pilih ?>><?= $pl['namaproduk'] ?></option>
                                                                <?php
                                                                }
                                                                ?>
                                                            </select>
                                                            <input type="number" name="qty" class="form-control mt-4" value="<?= $qty ?>" placeholder="jumlah" min="1" required>
                                                             <input type="hidden" name="idp" value="<?= $idp; ?>">
                                                              <input type="hidden" name="idpr" value="<?= $iddetail ?>">
                                                           
                                                        </div>
                                                        <div class="modal-footer">
                                                            <?php /* LAMA: <button type="submit" class="btn btn-success" name="editdetail">Submit</button> */ ?><button type="submit" class="btn btn-success" name="editdetail">Simpan</button>
                                                            <?php /* LAMA: <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button> */ ?><button type="button" class="btn btn-danger" data-bs-dismiss="modal">Batal</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Modal Delete -->
                                        <div class="modal fade" id="hapus<?= $iddetail ?>">
                                            <div class="modal-dialog">
                                                <div class="modal-content">

                                                    <!-- Modal Header -->
                                                    <div class="modal-header">
                                                        <?php /* LAMA: <h4 class="modal-title">Hapus Pesanan</h4> */ ?><h4 class="modal-title">Hapus Item</h4>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>

                                                    <form method="post">

                                                        <!-- Modal body -->
                                                        <div class="modal-body">
                                                            <?php /* LAMA: Apakah anda yakin ingin menghapus pesanan ini? */ ?>Apakah anda yakin ingin menghapus item ini?
                                                            <input type="hidden" name="idp" value="<?= $idp ?>">
                                                            <input type="hidden" name="idpr" value="<?= $iddetail ?>">
                                                        </div>

                                                        <!-- Modal footer -->
                                                        <div class="modal-footer">
                                                            <?php /* LAMA: <button type="submit" class="btn btn-success" data-bs-dismiss="modal" name="hapusdetail">Submit</button> */ ?><button type="submit" class="btn btn-success" name="hapusdetail">Hapus</button>
                                                            <?php /* LAMA: <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button> */ ?><button type="button" class="btn btn-danger" data-bs-dismiss="modal">Batal</button>
                                                        </div>

                                                    </form>

                                                </div>
                                            </div>
                                        </div>

                                    <?php
                                    }; //Akhir dari while
                                    ?>

                                </tbody>
                            </table>
                            <h4 class="text-end mt-3">Total: <?= rupiah($total) ?></h4> <!-- [BARU] -->
                        </div>
                    </div>
                </div>
            </main>
            <footer class="py-4 bg-light mt-auto">
                <div class="container-fluid px-4">
                    <div class="d-flex align-items-center justify-content-between small">
                        <?php /* LAMA: <div class="text-muted">Copyright &copy; Your Website 2023</div>
                        <div>
                            <a href="#">Privacy Policy</a>
                            &middot;
                            <a href="#">Terms &amp; Conditions</a>
                        </div> */ ?>
                        <div class="text-muted">Kasir Rental PS &copy; 2026</div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
    <?php /* LAMA: <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script> */ ?>
    <?php /* LAMA: <script src="assets/demo/chart-area-demo.js"></script> */ ?>
    <?php /* LAMA: <script src="assets/demo/chart-bar-demo.js"></script> */ ?>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
    <script src="js/datatables-simple-demo.js"></script>
</body>


<!-- [BARU] Modal Ubah Status Pesanan -->
<div class="modal fade" id="statusModal">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title">Ubah Status Pesanan #<?= $idp ?></h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form method="post">
                <div class="modal-body">
                    Status
                    <select name="status" class="form-control">
                        <?php foreach (daftarStatus() as $kode => $label) { ?>
                            <option value="<?= $kode ?>" <?= ($kode == $status) ? 'selected' : '' ?>><?= $label ?></option>
                        <?php } ?>
                    </select>
                    <input type="hidden" name="idp" value="<?= $idp ?>">
                    <input type="hidden" name="kembali" value="view">
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success" name="ubahstatus">Simpan</button>
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Batal</button>
                </div>
            </form>

        </div>
    </div>
</div>

<!-- The Modal , Modal agak beda dengan video pakai versi bootsrap versi 5-->
<div class="modal fade" id="myModal">
    <div class="modal-dialog">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <?php /* LAMA: <h4 class="modal-title">Tambah Barang</h4> */ ?><h4 class="modal-title">Tambah Item</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form method="post">

                <!-- Modal body -->
                <div class="modal-body">
                    <?php /* LAMA: Pilih Barang */ ?>Pilih Produk
                    <select name="idproduk" class="form-control">

                        <?php
                        $getproduk = mysqli_query($koneksi, "select * from produk where idproduk not in (select idproduk from detailpesanan where idpesanan='$idp')");

                        while ($pl = mysqli_fetch_array($getproduk)) {
                            $namaproduk = $pl['namaproduk'];
                            $idproduk = $pl['idproduk'];
                        ?>

                            <option value="<?= $idproduk;  ?>"><?= $namaproduk;  ?></option>

                        <?php
                        }
                        ?>
                    </select>

                    <input type="number" name="qty" class="form-control mt-4" placeholder="jumlah" min="1" required>
                    <input type="hidden" name="idp" value="<?= $idp; ?>">
                </div>
                <div class="modal-footer">
                    <?php /* LAMA: <?php /* LAMA: <button type="submit" class="btn btn-success" name="addproduk">Submit</button> */ ?><?php if (mysqli_num_rows($getproduk) == 0) { echo '<span class="text-muted me-2">Semua produk sudah ada di pesanan ini</span>'; } ?><button type="submit" class="btn btn-success" name="addproduk" <?= (mysqli_num_rows($getproduk) == 0) ? 'disabled' : '' ?>>Simpan</button>
                    <?php /* LAMA: <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button> */ ?><button type="button" class="btn btn-danger" data-bs-dismiss="modal">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

</html>