-- ============================================================
-- UPDATE DATABASE TAHAP 2 (khusus jika database sudah terlanjur diimport)
-- Jika import dari nol, cukup pakai kasir_rental_ps.sql yang baru
-- Jalankan di phpMyAdmin > database kasir_rental_ps > tab SQL, SATU KALI saja
-- ============================================================

-- 1. Tambah kolom status pada pesanan (pesanan baru otomatis berstatus 'baru')
ALTER TABLE `pesanan` ADD `status` ENUM('baru','diantar','lunas') NOT NULL DEFAULT 'baru' AFTER `id_ruang`;

-- 2. Pesanan yang sudah ada sebelumnya dianggap sudah dibayar (lunas),
--    supaya angka di laporan tetap sama seperti sebelumnya
UPDATE `pesanan` SET `status` = 'lunas';

-- 3. Hapus tabel masuk yang tidak dipakai
DROP TABLE IF EXISTS `masuk`;
