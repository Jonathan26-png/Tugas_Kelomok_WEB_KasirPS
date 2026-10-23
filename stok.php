<?php
require 'checklogin.php';
require_once 'function.php';
$result = mysqli_query($koneksi, "SELECT * FROM produk");

$count = mysqli_num_rows($result);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <?php /* LAMA: <title>Stock Barang</title> */ ?><title>Produk</title>
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
                        <a class="nav-link active" href="stok.php">
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
                        <a class="nav-link" href="index.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-receipt"></i></div>
                            Pesanan
                        </a>
                        <a class="nav-link active" href="stok.php">
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
                    <?php /* LAMA: <h1 class="mt-4">Pesanan</h1> */ ?><h1 class="mt-4">Produk</h1>
                    <ol class="breadcrumb mb-4">
                        <?php /* LAMA: <li class="breadcrumb-item active">Selamat Datang</li> */ ?><li class="breadcrumb-item active">Daftar Produk</li>
                    </ol>

                    <!-- Card Bagian Atas -->
                    <div class="row">
                        <div class="col-xl-3 col-md-6">
                            <div class="card bg-primary text-white mb-4">
                                <?php /* LAMA: <div class="card-body">Jumlah Barang : <?php echo $count ?></div> */ ?><div class="card-body">Jumlah Produk : <?php echo $count ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Button to Open the Modal -->
                    <button type="button" class="btn btn-info mb-4" data-bs-toggle="modal" data-bs-target="#myModal">
                        <?php /* LAMA: Tambah Barang Baru */ ?>Tambah Produk Baru
                    </button>


                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-table me-1"></i>
                            <?php /* LAMA: Data Barang */ ?>Data Produk
                        </div>
                        <div class="card-body">
                            <table id="datatablesSimple">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>Nama Produk</th>
                                        <th>Deskripsi</th>
                                        <th>Harga</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    <?php

                                    // Di video dia get, ganti tampil table biar jelas
                                    $tampiltabel = mysqli_query($koneksi, "SELECT * FROM produk");
                                    $i = 1;

                                    while ($p = mysqli_fetch_array($tampiltabel)) {
                                        $namaproduk = $p['namaproduk'];
                                        $deskripsi = $p['deskripsi'];
                                        $harga = $p['harga'];
                                        $idproduk = $p['idproduk'];


                                    ?>

                                        <tr>
                                            <td><?= $i++ ?></td>
                                            <td><?= $namaproduk ?></td>
                                            <td><?= $deskripsi ?></td>
                                            <?php /* LAMA: <td>Rp.<?= number_format($harga) ?></td> */ ?><td><?= rupiah($harga) ?></td>

                                            <td><button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#edit<?= $idproduk ?>">
                                                    Edit </button>
                                                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#hapus<?= $idproduk ?>">
                                                    <?php /* LAMA: Delete */ ?>Hapus </button>
                                            </td>
                                        </tr>
                                         <!-- Modal Edit -->

                                        <div class="modal fade" id="edit<?= $idproduk ?>">
                                            <div class="modal-dialog">
                                                <div class="modal-content">

                                                    <!-- Modal Header -->
                                                    <div class="modal-header">
                                                        <h4 class="modal-title">Ubah Produk</h4>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>

                                                    <form method="post">

                                                        <!-- Modal body -->
                                                        <div class="modal-body">
                                                            <?php /* LAMA: <input type="text" name="namaproduk" class="form-control" placeholder="Nama Produk" */ ?><input type="text" name="namaproduk" class="form-control" placeholder="Nama Produk" required value="<?= $namaproduk ?>">
                                                            <input type="text" name="deskripsi" class="form-control mt-3" placeholder="Deskripsi" value="<?= $deskripsi ?>">
                                                            <?php /* LAMA: <input type="num" name="harga" class="form-control mt-3" placeholder="Harga Produk" value="<?= $harga ?>"> */ ?><input type="number" min="0" required name="harga" class="form-control mt-3" placeholder="Harga Produk" value="<?= $harga ?>">
                                                            <input type="hidden" name="idp" value="<?= $idproduk ?>">
                                                        </div>

                                                        <!-- Modal footer -->
                                                        <div class="modal-footer">
                                                            <?php /* LAMA: <button type="submit" class="btn btn-success" data-bs-dismiss="modal" name="editbarang">Submit</button> */ ?><button type="submit" class="btn btn-success" name="editbarang">Simpan</button>
                                                            <?php /* LAMA: <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button> */ ?><button type="button" class="btn btn-danger" data-bs-dismiss="modal">Batal</button>
                                                        </div>

                                                    </form>

                                                </div>
                                            </div>
                                        </div>
                                        <!-- Modal Delete -->
                                         <div class="modal fade" id="hapus<?= $idproduk ?>">
                                            <div class="modal-dialog">
                                                <div class="modal-content">

                                                    <!-- Modal Header -->
                                                    <div class="modal-header">
                                                        <h4 class="modal-title">Hapus Produk</h4>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>

                                                    <form method="post">

                                                        <!-- Modal body -->
                                                        <div class="modal-body">
                                                            <?php /* LAMA: Apakah anda yakin ingin menghapus barang ini? */ ?>Apakah anda yakin ingin menghapus produk ini?
                                                            <input type="hidden" name="idp" value="<?= $idproduk ?>">
                                                        </div>

                                                        <!-- Modal footer -->
                                                        <div class="modal-footer">
                                                            <?php /* LAMA: <button type="submit" class="btn btn-success" data-bs-dismiss="modal" name="hapusbarang">Submit</button> */ ?><button type="submit" class="btn btn-success" name="hapusbarang">Hapus</button>
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

<!-- The Modal , Modal agak beda dengan video pakai versi bootsrap versi 5-->
<div class="modal fade" id="myModal">
    <div class="modal-dialog">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <?php /* LAMA: <h4 class="modal-title">Tambah Barang Baru</h4> */ ?><h4 class="modal-title">Tambah Produk Baru</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form method="post">

                <!-- Modal body -->
                <div class="modal-body">
                    <?php /* LAMA: <input type="text" name="namaproduk" class="form-control" placeholder="Nama Produk" */ ?><input type="text" name="namaproduk" class="form-control" placeholder="Nama Produk" required>
                    <input type="text" name="deskripsi" class="form-control mt-3" placeholder="Deskripsi">

                    <?php /* LAMA: <input type="num" name="harga" class="form-control mt-3" placeholder="Harga Produk"> */ ?><input type="number" min="0" required name="harga" class="form-control mt-3" placeholder="Harga Produk">
                </div>

                <!-- Modal footer -->
                <div class="modal-footer">
                    <?php /* LAMA: <button type="submit" class="btn btn-success" data-bs-dismiss="modal" name="tambahbarang">Submit</button> */ ?><button type="submit" class="btn btn-success" name="tambahbarang">Simpan</button>
                    <?php /* LAMA: <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button> */ ?><button type="button" class="btn btn-danger" data-bs-dismiss="modal">Batal</button>
                </div>

            </form>

        </div>
    </div>
</div>

</html>