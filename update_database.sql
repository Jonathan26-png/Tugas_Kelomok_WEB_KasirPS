-- ============================================================
-- UPDATE DATABASE (khusus jika database lama SUDAH terlanjur diimport)
-- Jika import dari nol, cukup pakai kasir_rental_ps.sql yang baru
-- Jalankan di phpMyAdmin > database kasir_rental_ps > tab SQL
-- ============================================================

-- 1. Tanggal pesanan tidak boleh berubah otomatis saat pesanan di-update
ALTER TABLE `pesanan` MODIFY `tanggal` timestamp NOT NULL DEFAULT current_timestamp();

-- 2. (Opsional) Rapikan data contoh produk
UPDATE `produk` SET `namaproduk`='Indomie Goreng', `deskripsi`='Mi instan goreng', `harga`=8000  WHERE `idproduk`=1;
UPDATE `produk` SET `namaproduk`='Es Teh Manis',   `deskripsi`='Teh manis dingin', `harga`=5000  WHERE `idproduk`=3;
UPDATE `produk` SET `namaproduk`='Kopi Susu',      `deskripsi`='Kopi susu gelas',  `harga`=12000 WHERE `idproduk`=5;
