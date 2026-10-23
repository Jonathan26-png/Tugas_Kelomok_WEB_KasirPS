<?php
// [BARU] masuk.php sekarang menjadi halaman LAPORAN PENJUALAN
// (sebelumnya masih template kosong "Barang Masuk" berisi data contoh Tiger Nixon, dan tanpa cek login)
// [LAMA] versi sebelumnya: laporan HARIAN (hanya 1 tanggal). [BARU] sekarang merekap semua pesanan, bisa difilter per periode.
require 'checklogin.php';
require_once 'function.php';

// [LAMA] Tanggal laporan (default: hari ini). strtotime() juga memastikan formatnya valid.
// [LAMA] $ts = isset($_GET['tanggal']) ? strtotime($_GET['tanggal']) : false;
// [LAMA] $tanggal = date('Y-m-d', $ts ? $ts : time());

// [BARU] Laporan sekarang merekap SEMUA pesanan di database.
// Periode bisa dibatasi lewat ?dari=YYYY-MM-DD&sampai=YYYY-MM-DD (boleh salah satu saja).
// Tanpa filter = semua pesanan sejak awal. Link lama ?tanggal=... tetap jalan (dianggap 1 hari).
// Tanggal diubah lewat strtotime() + date() sehingga hanya format YYYY-MM-DD yang lolos ke query.
function tanggalAman($nilai) {
    $t = strtotime($nilai);
    return $t ? date('Y-m-d', $t) : '';
}
$dari   = isset($_GET['dari'])   ? tanggalAman($_GET['dari'])   : '';
$sampai = isset($_GET['sampai']) ? tanggalAman($_GET['sampai']) : '';
if ($dari === '' && $sampai === '' && isset($_GET['tanggal'])) {
    $dari = $sampai = tanggalAman($_GET['tanggal']);
}
// Jika terbalik (dari > sampai), tukar saja
if ($dari !== '' && $sampai !== '' && $dari > $sampai) {
    $tmp = $dari; $dari = $sampai; $sampai = $tmp;
}

// Syarat WHERE yang dipakai semua query di halaman ini
$where = "1=1";
if ($dari !== '')   { $where .= " AND p.tanggal >= '$dari 00:00:00'"; }
if ($sampai !== '') { $where .= " AND p.tanggal <= '$sampai 23:59:59'"; }

// Keterangan periode untuk judul tabel
if ($dari === '' && $sampai === '') {
    $periode = 'Semua Waktu';
} elseif ($dari === $sampai) {
    $periode = 'Tanggal ' . date('d-m-Y', strtotime($dari));
} elseif ($sampai === '') {
    $periode = 'Sejak ' . date('d-m-Y', strtotime($dari));
} elseif ($dari === '') {
    $periode = 'Sampai ' . date('d-m-Y', strtotime($sampai));
} else {
    $periode = date('d-m-Y', strtotime($dari)) . ' s/d ' . date('d-m-Y', strtotime($sampai));
}

// Untuk tombol pintas filter
$hariIni   = date('Y-m-d');
$awalBulan = date('Y-m-01');
$akhirBulan = date('Y-m-t');

// Ringkasan: jumlah pesanan, jumlah item terjual, total pendapatan
$ringkas = mysqli_query($koneksi, "
    SELECT COUNT(DISTINCT p.idpesanan) AS jml_pesanan,
           COALESCE(SUM(d.qty), 0) AS jml_item,
           COALESCE(SUM(d.qty * pr.harga), 0) AS pendapatan
    FROM pesanan p
    LEFT JOIN detailpesanan d ON d.idpesanan = p.idpesanan
    LEFT JOIN produk pr ON pr.idproduk = d.idproduk
    WHERE $where AND p.status = 'lunas'"); // [LAMA] tanpa filter status; [BARU] hanya pesanan LUNAS yang dihitung sebagai penjualan
    // [LAMA] WHERE DATE(p.tanggal) = '$tanggal' AND p.status = 'lunas'");
$r = mysqli_fetch_array($ringkas);

// [BARU] pesanan yang belum lunas (baru/diantar) pada periode terpilih: belum dihitung sebagai pendapatan
$belum = mysqli_query($koneksi, "
    SELECT COUNT(DISTINCT p.idpesanan) AS jml_pesanan,
           COALESCE(SUM(d.qty * pr.harga), 0) AS nilai
    FROM pesanan p
    LEFT JOIN detailpesanan d ON d.idpesanan = p.idpesanan
    LEFT JOIN produk pr ON pr.idproduk = d.idproduk
    WHERE $where AND p.status != 'lunas'");
    // [LAMA] WHERE DATE(p.tanggal) = '$tanggal' AND p.status != 'lunas'");
$b = mysqli_fetch_array($belum);

// [BARU] Rekap per tanggal: satu baris untuk setiap hari yang punya pesanan
$rekap = mysqli_query($koneksi, "
    SELECT DATE(p.tanggal) AS tgl,
           COUNT(DISTINCT p.idpesanan) AS jml_pesanan,
           COUNT(DISTINCT CASE WHEN p.status = 'lunas' THEN p.idpesanan END) AS jml_lunas,
           COUNT(DISTINCT CASE WHEN p.status != 'lunas' THEN p.idpesanan END) AS jml_belum,
           COALESCE(SUM(CASE WHEN p.status = 'lunas' THEN d.qty END), 0) AS jml_item,
           COALESCE(SUM(CASE WHEN p.status = 'lunas' THEN d.qty * pr.harga END), 0) AS pendapatan,
           COALESCE(SUM(CASE WHEN p.status != 'lunas' THEN d.qty * pr.harga END), 0) AS nilai_belum
    FROM pesanan p
    LEFT JOIN detailpesanan d ON d.idpesanan = p.idpesanan
    LEFT JOIN produk pr ON pr.idproduk = d.idproduk
    WHERE $where
    GROUP BY DATE(p.tanggal)
    ORDER BY tgl DESC");
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <?php /* LAMA: <title>Pesanan</title> */ ?><title>Laporan Penjualan</title>
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
                                Stok Barang
                            </a>
                             <a class="nav-link" href="masuk.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                Barang Masuk
                            </a>
                             <a class="nav-link" href="logout.php">
                                Logout
                            </a> */ ?>
                        <div class="sb-sidenav-menu-heading">MENU</div>
                        <a class="nav-link" href="index.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-receipt"></i></div>
                            Pesanan
                        </a>
                        <a class="nav-link" href="stok.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-utensils"></i></div>
                            Produk
                        </a>
                        <a class="nav-link active" href="masuk.php">
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
                        <?php /* LAMA: <h1 class="mt-4">Pesanan</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item active">Dashboard</li>
                        </ol>

                        <!-- Card Bagian Atas -->
                        <div class="row">
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-primary text-white mb-4">
                                    <div class="card-body">Primary Card</div>
                                    <div class="card-footer d-flex align-items-center justify-content-between">
                                        <a class="small text-white stretched-link" href="#">View Details</a>
                                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                                    </div>
                                </div>
                            </div>
                            
                        </div>
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                Data Pesanan
                            </div>
                            <div class="card-body">
                                <table id="datatablesSimple">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Position</th>
                                            <th>Office</th>
                                            <th>Age</th>
                                            <th>Start date</th>
                                            <th>Salary</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Tiger Nixon</td>
                                            <td>System Architect</td>
                                            <td>Edinburgh</td>
                                            <td>61</td>
                                            <td>2011/04/25</td>
                                            <td>$320,800</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                 */ ?>
                        <h1 class="mt-4">Laporan Penjualan</h1>
                        <?php /* LAMA: <li class="breadcrumb-item active">Harian</li> */ ?>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item active">Rekap Semua Pesanan</li>
                        </ol>

                        <?php /* LAMA: <!-- Filter tanggal (1 hari) -->
                        <form method="get" class="row g-2 mb-4">
                            <div class="col-auto">
                                <input type="date" name="tanggal" class="form-control" value="<?= $tanggal ?>">
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-primary">Tampilkan</button>
                                <a href="masuk.php" class="btn btn-secondary">Hari Ini</a>
                            </div>
                        </form> */ ?>

                        <!-- [BARU] Filter periode (kosongkan keduanya = semua pesanan) -->
                        <form method="get" class="row g-2 mb-4 align-items-end">
                            <div class="col-auto">
                                <label class="form-label mb-0 small">Dari tanggal</label>
                                <input type="date" name="dari" class="form-control" value="<?= $dari ?>">
                            </div>
                            <div class="col-auto">
                                <label class="form-label mb-0 small">Sampai tanggal</label>
                                <input type="date" name="sampai" class="form-control" value="<?= $sampai ?>">
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-primary">Tampilkan</button>
                                <a href="masuk.php?dari=<?= $hariIni ?>&amp;sampai=<?= $hariIni ?>" class="btn btn-secondary">Hari Ini</a>
                                <a href="masuk.php?dari=<?= $awalBulan ?>&amp;sampai=<?= $akhirBulan ?>" class="btn btn-secondary">Bulan Ini</a>
                                <a href="masuk.php" class="btn btn-secondary">Semua</a>
                            </div>
                        </form>

                        <p class="text-muted">Periode: <b><?= $periode ?></b></p>

                        <!-- Kartu ringkasan -->
                        <?php /* LAMA: ketiga kartu memakai class col-xl-4 (3 kartu sebaris) */ ?>
                        <div class="row">
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-success text-white mb-4">
                                    <div class="card-body">Pendapatan (Lunas) : <?= rupiah($r['pendapatan']) ?></div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-primary text-white mb-4">
                                    <div class="card-body">Pesanan Lunas : <?= $r['jml_pesanan'] ?></div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-warning text-white mb-4">
                                    <div class="card-body">Item Terjual : <?= number_format($r['jml_item'], 0, ',', '.') ?></div>
                                </div>
                            </div>

                            <!-- [BARU] pesanan yang belum lunas -->
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-danger text-white mb-4">
                                    <div class="card-body">Belum Lunas : <?= $b['jml_pesanan'] ?> pesanan (<?= rupiah($b['nilai']) ?>)</div>
                                </div>
                            </div>
                        </div>

                        <!-- [BARU] Rekap per tanggal -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-calendar-days me-1"></i>
                                Rekap Per Tanggal (<?= $periode ?>)
                            </div>
                            <div class="card-body">
                                <table id="tabelRekapHarian">
                                    <thead>
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Jumlah Pesanan</th>
                                            <th>Lunas</th>
                                            <th>Belum Lunas</th>
                                            <th>Item Terjual</th>
                                            <th>Pendapatan (Lunas)</th>
                                            <th>Nilai Belum Lunas</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($h = mysqli_fetch_array($rekap)) { ?>
                                            <tr>
                                                <td data-order="<?= $h['tgl'] ?>"><?= date('d-m-Y', strtotime($h['tgl'])) ?></td>
                                                <td><?= $h['jml_pesanan'] ?></td>
                                                <td><?= $h['jml_lunas'] ?></td>
                                                <td><?= $h['jml_belum'] ?></td>
                                                <td><?= number_format($h['jml_item'], 0, ',', '.') ?></td>
                                                <td data-order="<?= $h['pendapatan'] ?>"><?= rupiah($h['pendapatan']) ?></td>
                                                <td data-order="<?= $h['nilai_belum'] ?>"><?= rupiah($h['nilai_belum']) ?></td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <?php /* LAMA: <!-- Daftar pesanan pada tanggal tersebut --> (judul: "Pesanan Tanggal ...", kolom: Id Pesanan, Jam, Ruang, Total, Status, Aksi) */ ?>
                        <!-- [BARU] Daftar semua pesanan pada periode terpilih -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                Daftar Pesanan (<?= $periode ?>)
                            </div>
                            <div class="card-body">
                                <table id="datatablesSimple">
                                    <thead>
                                        <tr>
                                            <th>Id Pesanan</th>
                                            <th>Tanggal</th>
                                            <th>Jam</th>
                                            <th>Ruang</th>
                                            <th>Jumlah Item</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // [LAMA] JOIN ruang (pesanan tanpa ruang tidak ikut tampil), tanpa kolom jumlah item, hanya 1 tanggal:
                                        // [LAMA] SELECT p.idpesanan, p.tanggal, r.namaruang, p.status, COALESCE(SUM(d.qty * pr.harga), 0) AS total
                                        // [LAMA] FROM pesanan p JOIN ruang r ON r.id_ruang = p.id_ruang ... WHERE DATE(p.tanggal) = '$tanggal'
                                        // [BARU] LEFT JOIN ruang supaya SEMUA pesanan tetap masuk rekap; ditambah jumlah item
                                        $pesanan = mysqli_query($koneksi, "
                                            SELECT p.idpesanan, p.tanggal, COALESCE(r.namaruang, '-') AS namaruang, p.status,
                                                   COALESCE(SUM(d.qty), 0) AS jml_item,
                                                   COALESCE(SUM(d.qty * pr.harga), 0) AS total
                                            FROM pesanan p
                                            LEFT JOIN ruang r ON r.id_ruang = p.id_ruang
                                            LEFT JOIN detailpesanan d ON d.idpesanan = p.idpesanan
                                            LEFT JOIN produk pr ON pr.idproduk = d.idproduk
                                            WHERE $where
                                            GROUP BY p.idpesanan, p.tanggal, r.namaruang, p.status
                                            ORDER BY p.tanggal DESC");
                                        while ($p = mysqli_fetch_array($pesanan)) {
                                        ?>
                                            <tr>
                                                <td><?= $p['idpesanan'] ?></td>
                                                <td data-order="<?= $p['tanggal'] ?>"><?= date('d-m-Y', strtotime($p['tanggal'])) ?></td>
                                                <td><?= date('H:i', strtotime($p['tanggal'])) ?></td>
                                                <td><?= $p['namaruang'] ?></td>
                                                <td><?= number_format($p['jml_item'], 0, ',', '.') ?></td>
                                                <td data-order="<?= $p['total'] ?>"><?= rupiah($p['total']) ?></td>
                                                <td><?= badgeStatus($p['status']) ?></td>
                                                <td><a href="view.php?idp=<?= $p['idpesanan'] ?>" class="btn btn-primary btn-sm" target="_blank">Tampilkan</a></td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Produk terjual pada tanggal tersebut -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-chart-bar me-1"></i>
                                Produk Terjual (Pesanan Lunas, <?= $periode ?>)
                            </div>
                            <div class="card-body">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Produk</th>
                                            <th>Jumlah Terjual</th>
                                            <th>Pendapatan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $terjual = mysqli_query($koneksi, "
                                            SELECT pr.namaproduk, SUM(d.qty) AS qty, SUM(d.qty * pr.harga) AS pendapatan
                                            FROM detailpesanan d
                                            JOIN pesanan p ON p.idpesanan = d.idpesanan
                                            JOIN produk pr ON pr.idproduk = d.idproduk
                                            WHERE $where AND p.status = 'lunas'
                                            GROUP BY pr.idproduk, pr.namaproduk
                                            ORDER BY qty DESC");
                                        if (mysqli_num_rows($terjual) == 0) {
                                            // [LAMA] Belum ada penjualan pada tanggal ini
                                            echo '<tr><td colspan="3" class="text-muted">Belum ada penjualan pada periode ini</td></tr>';
                                        }
                                        while ($t = mysqli_fetch_array($terjual)) {
                                        ?>
                                            <tr>
                                                <td><?= $t['namaproduk'] ?></td>
                                                <td><?= number_format($t['qty'], 0, ',', '.') ?></td>
                                                <td><?= rupiah($t['pendapatan']) ?></td>
                                            </tr>
                                        <?php } ?>
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
        <!-- [BARU] js/datatables-simple-demo.js hanya mengaktifkan #datatablesSimple, jadi tabel rekap diaktifkan di sini -->
        <script>
            window.addEventListener('DOMContentLoaded', function () {
                var tabelRekap = document.getElementById('tabelRekapHarian');
                if (tabelRekap) {
                    new simpleDatatables.DataTable(tabelRekap);
                }
            });
        </script>
    </body>
</html>