-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 12, 2026 at 10:21 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `chat_sistem`
--

-- --------------------------------------------------------

--
-- Table structure for table `chatbot`
--

CREATE TABLE `chatbot` (
  `id` int(11) NOT NULL,
  `pertanyaan` varchar(300) NOT NULL,
  `jawaban` mediumtext NOT NULL,
  `kategori` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chatbot`
--

INSERT INTO `chatbot` (`id`, `pertanyaan`, `jawaban`, `kategori`) VALUES
(1, 'Kapan pengisian KRS semester ganjil?|Pengisian KRS semester ganjil itu kapan?|Semester ganjil pengisian KRS kapan?|Saya mau tahu pengisian KRS semester ganjil|Kapan mulai pengisian KRS semester ganjil?', 'Pengisian KRS Semester Ganjil 2024/2025: 13 - 16 September 2024.', 'krs'),
(2, 'Batas akhir KRS semester ganjil?|Kapan batas akhir KRS semester ganjil?|Kapan pengisian KRS ganjil berakhir?|Batas KRS semester ganjil sampai kapan?|Akhir pengisian KRS semester ganjil?', 'Batas akhir pengisian KRS Semester Ganjil 2024/2025: 16 September 2024.', 'krs'),
(3, 'Batas akhir pengajuan cuti semester ganjil?|Kapan terakhir ajukan cuti semester ganjil?|Batas pengajuan cuti ganjil sampai kapan?|Pengajuan cuti semester ganjil terakhir kapan?|Kapan batas cuti semester ganjil?', 'Batas akhir pengajuan cuti Semester Ganjil 2024/2025: 16 September 2024.', 'cuti'),
(4, 'Kapan pengisian KRS semester genap?|Pengisian KRS semester genap itu kapan?|Semester genap pengisian KRS kapan?|Saya mau tahu pengisian KRS semester genap|Kapan mulai pengisian KRS semester genap?', 'Pengisian KRS Semester Genap 2024/2025: 3 - 22 Februari 2025.', 'krs'),
(5, 'Batas akhir pengajuan cuti semester genap?|Kapan terakhir ajukan cuti semester genap?|Batas pengajuan cuti genap sampai kapan?|Pengajuan cuti semester genap terakhir kapan?|Kapan batas cuti semester genap?', 'Batas akhir pengajuan cuti Semester Genap 2024/2025: 22 Februari 2025.', 'cuti'),
(6, 'Kapan pembayaran BPP mahasiswa lama semester ganjil?|Pembayaran BPP mahasiswa lama semester ganjil kapan?|Semester ganjil pembayaran BPP mahasiswa lama kapan?|BPP semester ganjil untuk mahasiswa lama kapan dibayar?|Saya mau tahu pembayaran BPP semester ganjil mahasiswa lama', 'Pembayaran BPP Mahasiswa Lama Semester Ganjil 2024/2025: 5 Agustus - 16 September 2024.', 'pembayaran'),
(7, 'Kapan pembayaran BPP mahasiswa lama semester genap?|Pembayaran BPP mahasiswa lama semester genap kapan?|Semester genap pembayaran BPP mahasiswa lama kapan?|BPP semester genap untuk mahasiswa lama kapan dibayar?|Saya mau tahu pembayaran BPP semester genap mahasiswa lama', 'Pembayaran BPP Mahasiswa Lama Semester Genap 2024/2025: 2 - 22 Februari 2025.', 'pembayaran'),
(8, 'Batas akhir pembayaran SKS semester ganjil?|Kapan terakhir bayar SKS semester ganjil?|Pembayaran SKS semester ganjil sampai kapan?|Batas bayar SKS ganjil kapan?|Kapan deadline pembayaran SKS semester ganjil?', 'Batas akhir pembayaran SKS Semester Ganjil 2024/2025: 7 Januari 2025.', 'pembayaran'),
(9, 'Batas akhir pembayaran SKS semester genap?|Kapan terakhir bayar SKS semester genap?|Pembayaran SKS semester genap sampai kapan?|Batas bayar SKS genap kapan?|Kapan deadline pembayaran SKS semester genap?', 'Batas akhir pembayaran SKS Semester Genap 2024/2025: 17 Juni 2025.', 'pembayaran'),
(10, 'Kapan UTS semester ganjil?|UTS semester ganjil kapan dilaksanakan?|Jadwal UTS semester ganjil kapan?|Saya mau tahu jadwal UTS semester ganjil|Kapan mulai UTS semester ganjil?', 'UTS Semester Ganjil 2024/2025: 11 - 16 November 2024.', 'ujian'),
(11, 'Kapan UAS semester ganjil?|UAS semester ganjil kapan dilaksanakan?|Jadwal UAS semester ganjil kapan?|Saya mau tahu jadwal UAS semester ganjil|Kapan mulai UAS semester ganjil?', 'UAS Semester Ganjil 2024/2025: 13 - 25 Januari 2025.', 'ujian'),
(12, 'Kapan UTS semester genap?|UTS semester genap kapan dilaksanakan?|Jadwal UTS semester genap kapan?|Saya mau tahu jadwal UTS semester genap|Kapan mulai UTS semester genap?', 'UTS Semester Genap 2024/2025: 21 - 30 April 2025.', 'ujian'),
(13, 'Kapan UAS semester genap?|UAS semester genap kapan dilaksanakan?|Jadwal UAS semester genap kapan?|Saya mau tahu jadwal UAS semester genap|Kapan mulai UAS semester genap?', 'UAS Semester Genap 2024/2025: 30 Juni - 5 Juli 2025.', 'ujian'),
(14, 'Masa pengajuan proposal skripsi semester ganjil?|Kapan pengajuan proposal skripsi semester ganjil?|Pengajuan proposal skripsi semester ganjil kapan dibuka?|Saya mau tahu jadwal pengajuan proposal skripsi ganjil|Kapan mulai pengajuan proposal skripsi semester ganjil?', 'Masa pengajuan proposal skripsi Semester Ganjil 2024/2025: 9 September - 5 Oktober 2024.', 'skripsi'),
(15, 'Masa sidang skripsi semester ganjil?|Kapan sidang skripsi semester ganjil?|Sidang skripsi ganjil dilaksanakan kapan?|Saya mau tahu jadwal sidang skripsi semester ganjil|Kapan mulai sidang skripsi semester ganjil?', 'Masa sidang skripsi Semester Ganjil 2024/2025: 20 - 31 Januari 2025.', 'skripsi'),
(16, 'Masa sidang skripsi semester genap?|Kapan sidang skripsi semester genap?|Sidang skripsi genap dilaksanakan kapan?|Saya mau tahu jadwal sidang skripsi semester genap|Kapan mulai sidang skripsi semester genap?', 'Masa sidang skripsi Semester Genap 2024/2025: 30 Juni - 12 Juli 2025.', 'skripsi'),
(17, 'Kapan pelaksanaan PKPM periode Februari?|Pelaksanaan PKPM periode Februari kapan?|PKPM Februari dilaksanakan kapan?|Saya mau tahu jadwal PKPM periode Februari|Kapan mulai PKPM periode Februari?', 'Pelaksanaan PKPM/KP Periode Februari: 30 Januari - 1 Maret 2025.', 'pkpm'),
(18, 'Kapan pelaksanaan PKPM periode Agustus?|Pelaksanaan PKPM periode Agustus kapan?|PKPM Agustus dilaksanakan kapan?|Saya mau tahu jadwal PKPM periode Agustus|Kapan mulai PKPM periode Agustus?', 'Pelaksanaan PKPM/KP Periode Agustus: 28 Juli - 30 Agustus 2025.', 'pkpm'),
(19, 'Kapan yudisium semester ganjil tahap 1?|Yudisium semester ganjil tahap 1 kapan?|Tahap 1 yudisium semester ganjil dilaksanakan kapan?|Saya mau tahu jadwal yudisium ganjil tahap 1|Kapan mulai yudisium semester ganjil tahap 1?', 'Yudisium Semester Ganjil 2024/2025 Tahap 1: 23 Desember 2024.', 'yudisium'),
(20, 'Kapan yudisium semester genap tahap 1?|Yudisium semester genap tahap 1 kapan?|Tahap 1 yudisium semester genap dilaksanakan kapan?|Saya mau tahu jadwal yudisium genap tahap 1|Kapan mulai yudisium semester genap tahap 1?', 'Yudisium Semester Genap 2024/2025 Tahap 1: 30 Juni 2025.', 'yudisium'),
(21, 'Kapan wisuda semester genap?|Wisuda semester genap kapan dilaksanakan?|Jadwal wisuda semester genap kapan?|Saya mau tahu jadwal wisuda semester genap|Kapan mulai wisuda semester genap?', 'Wisuda Semester Genap 2024/2025: 10 September 2025.', 'yudisium'),
(22, 'Kapan pendaftaran semester pendek?|Pendaftaran semester pendek kapan dibuka?|Semester pendek pendaftaran kapan?|Saya mau tahu jadwal pendaftaran semester pendek|Kapan mulai daftar semester pendek?', 'Pendaftaran Semester Pendek 2024/2025: 21 Juli - 2 Agustus 2025.', 'semester_pendek'),
(23, 'Kapan perkuliahan semester pendek?|Perkuliahan semester pendek kapan dimulai?|Semester pendek perkuliahan kapan?|Saya mau tahu jadwal perkuliahan semester pendek|Kapan mulai semester pendek?', 'Perkuliahan Semester Pendek 2024/2025: 4 - 30 Agustus 2025.', 'semester_pendek'),
(24, 'Kapan UAS semester pendek?|UAS semester pendek kapan dilaksanakan?|Jadwal UAS semester pendek kapan?|Saya mau tahu jadwal UAS semester pendek|Kapan mulai UAS semester pendek?', 'UAS Semester Pendek 2024/2025: 1 - 6 September 2025.', 'semester_pendek'),
(25, 'Beasiswa apa saja untuk mahasiswa S1 Darmajaya?|Jenis beasiswa apa saja di S1 Darmajaya?|Saya mau tahu beasiswa S1 Darmajaya|Beasiswa S1 Darmajaya ada apa saja?|Apa saja pilihan beasiswa di S1 Darmajaya?', 'IIB Darmajaya menyediakan Beasiswa KIP Kuliah, Beasiswa Prestasi, Hafizh Quran, Yatim/Piatu, dan Beasiswa Tidak Mampu khusus untuk S1.', 'beasiswa'),
(26, 'Berapa jumlah beasiswa KIP Kuliah untuk S1 Darmajaya?|Kuota beasiswa KIP Kuliah S1 Darmajaya berapa?|Saya mau tahu jumlah beasiswa KIP Kuliah S1 Darmajaya|Banyaknya beasiswa KIP Kuliah S1 Darmajaya berapa?|KIP Kuliah S1 Darmajaya jumlahnya berapa?', 'IIB Darmajaya memberikan sekitar 52 beasiswa KIP Kuliah untuk calon mahasiswa S1.', 'beasiswa'),
(27, 'Berapa kuota beasiswa Yayasan Alfian Husin untuk S1?|Kuota beasiswa Yayasan Alfian Husin S1 berapa?|Saya mau tahu jumlah beasiswa Yayasan Alfian Husin S1|Banyaknya beasiswa Yayasan Alfian Husin S1 berapa?|Beasiswa Yayasan Alfian Husin S1 jumlahnya berapa?', 'beasiswa Yayasan Alfian Husin terdiri dari sekitar 56 kuota untuk S1.', 'beasiswa'),
(28, 'Bagaimana cara daftar beasiswa KIP Kuliah Darmajaya S1?|Cara mendaftar beasiswa KIP Kuliah S1 Darmajaya bagaimana?|Langkah daftar beasiswa KIP Kuliah S1 Darmajaya?|Saya mau tahu cara daftar beasiswa KIP Kuliah S1 Darmajaya|Gimana daftar beasiswa KIP Kuliah S1 Darmajaya?', 'Pendaftaran beasiswa KIP Kuliah S1 dilakukan melalui portal PMB Darmajaya, perlu verifikasi data ekonomi dan seleksi administratif.', 'beasiswa'),
(29, 'Apakah ada beasiswa Hafizh Quran S1 Darmajaya?|Beasiswa Hafizh Quran S1 Darmajaya ada?|Saya mau tahu tentang beasiswa Hafizh Quran S1 Darmajaya|Kampus Darmajaya ada beasiswa Hafizh Quran?|Apakah tersedia beasiswa Hafizh Quran di S1 Darmajaya?', 'Ada, kampus menyediakan Beasiswa Hafizh Quran bagi pendaftar S1 yang merupakan penghafal Al-Qur’an.', 'beasiswa'),
(30, 'Apa akreditasi prodi Teknik Informatika S1 Darmajaya?|Akreditasi S1 Teknik Informatika Darmajaya apa?|Saya mau tahu akreditasi Teknik Informatika S1 Darmajaya|Prodi S1 Teknik Informatika Darmajaya terakreditasi apa?|Status akreditasi S1 Teknik Informatika Darmajaya?', 'Prodi S1 Teknik Informatika meraih akreditasi Unggul (LAM-INFOKOM) terbaru.', 'prodi'),
(31, 'Apa akreditasi prodi Sistem Informasi S1 Darmajaya?|Akreditasi S1 Sistem Informasi Darmajaya apa?|Saya mau tahu akreditasi Sistem Informasi S1 Darmajaya|Prodi S1 Sistem Informasi Darmajaya terakreditasi apa?|Status akreditasi S1 Sistem Informasi Darmajaya?', 'Prodi S1 Sistem Informasi (atau Inovasi Digital) berakreditasi Unggul.', 'prodi'),
(32, 'Apa akreditasi prodi Sistem Komputer S1 Darmajaya?|Akreditasi S1 Sistem Komputer Darmajaya apa?|Saya mau tahu akreditasi Sistem Komputer S1 Darmajaya|Prodi S1 Sistem Komputer Darmajaya terakreditasi apa?|Status akreditasi S1 Sistem Komputer Darmajaya?', 'Prodi S1 Sistem Komputer memiliki akreditasi Baik Sekali (A).', 'prodi'),
(33, 'Apa akreditasi prodi Sains Data S1 Darmajaya?|Akreditasi S1 Sains Data Darmajaya apa?|Saya mau tahu akreditasi Sains Data S1 Darmajaya|Prodi S1 Sains Data Darmajaya terakreditasi apa?|Status akreditasi S1 Sains Data Darmajaya?', 'Prodi S1 Sains Data memiliki akreditasi Baik.', 'prodi'),
(34, 'Apa akreditasi prodi Manajemen S1 Darmajaya?|Akreditasi S1 Manajemen Darmajaya apa?|Saya mau tahu akreditasi Manajemen S1 Darmajaya|Prodi S1 Manajemen Darmajaya terakreditasi apa?|Status akreditasi S1 Manajemen Darmajaya?', 'Prodi S1 Manajemen memiliki akreditasi A (Baik Sekali) berdasarkan SK BAN-PT.', 'prodi'),
(35, 'Apa akreditasi prodi Akuntansi S1 Darmajaya?|Akreditasi S1 Akuntansi Darmajaya apa?|Saya mau tahu akreditasi Akuntansi S1 Darmajaya|Prodi S1 Akuntansi Darmajaya terakreditasi apa?|Status akreditasi S1 Akuntansi Darmajaya?', 'Prodi S1 Akuntansi memiliki akreditasi A (Baik Sekali).', 'prodi'),
(36, 'Apa akreditasi prodi Desain Komunikasi Visual S1 Darmajaya?|Akreditasi S1 Desain Komunikasi Visual Darmajaya apa?|Saya mau tahu akreditasi Desain Komunikasi Visual S1 Darmajaya|Prodi S1 Desain Komunikasi Visual Darmajaya terakreditasi apa?|Status akreditasi S1 Desain Komunikasi Visual Darmajaya?', 'Prodi S1 Desain Komunikasi Visual memiliki akreditasi Baik.', 'prodi'),
(37, 'Apa akreditasi prodi Desain Interior S1 Darmajaya?|Akreditasi S1 Desain Interior Darmajaya apa?|Saya mau tahu akreditasi Desain Interior S1 Darmajaya|Prodi S1 Desain Interior Darmajaya terakreditasi apa?|Status akreditasi S1 Desain Interior Darmajaya?', 'Prodi S1 Desain Interior memiliki akreditasi Baik.', 'prodi'),
(38, 'Apa akreditasi prodi Hukum Bisnis S1 Darmajaya?|Akreditasi S1 Hukum Bisnis Darmajaya apa?|Saya mau tahu akreditasi Hukum Bisnis S1 Darmajaya|Prodi S1 Hukum Bisnis Darmajaya terakreditasi apa?|Status akreditasi S1 Hukum Bisnis Darmajaya?', 'Prodi S1 Hukum Bisnis memiliki akreditasi Baik.', 'prodi'),
(39, 'Apa akreditasi prodi Pariwisata S1 Darmajaya?|Akreditasi S1 Pariwisata Darmajaya apa?|Saya mau tahu akreditasi Pariwisata S1 Darmajaya|Prodi S1 Pariwisata Darmajaya terakreditasi apa?|Status akreditasi S1 Pariwisata Darmajaya?', 'Prodi S1 Pariwisata memiliki akreditasi Baik.', 'prodi'),
(40, 'Apa akreditasi prodi Pendidikan Teknologi Informasi S1 Darmajaya?|Akreditasi S1 Pendidikan Teknologi Informasi Darmajaya apa?|Saya mau tahu akreditasi Pendidikan Teknologi Informasi S1 Darmajaya|Prodi S1 Pendidikan Teknologi Informasi Darmajaya terakreditasi apa?|Status akreditasi S1 Pendidikan Tekn', 'Prodi S1 Pendidikan Teknologi Informasi memiliki akreditasi Baik.', 'prodi'),
(41, 'Apakah Darmajaya punya program exchange untuk mahasiswa S1?|Apakah ada program pertukaran mahasiswa S1 di Darmajaya?|Saya mau tahu program exchange mahasiswa S1 Darmajaya|Darmajaya menyediakan program student exchange?|Apakah tersedia program pertukaran mahasiswa S1 Darmajaya?', 'Ya, Darmajaya memiliki program Student Mobility dan Academic Visit bagi mahasiswa S1 ke luar negeri.', 'umum'),
(42, 'Apakah ada beasiswa penuh kuliah di luar negeri untuk mahasiswa S1 Darmajaya?|Beasiswa penuh kuliah di luar negeri S1 Darmajaya ada?|Saya mau tahu beasiswa full luar negeri S1 Darmajaya|Apakah tersedia beasiswa luar negeri penuh untuk mahasiswa S1 Darmajaya?|Darmajaya memberikan beasiswa full luar n', 'Iya, Darmajaya menawarkan beasiswa penuh kuliah di luar negeri bagi mahasiswa S1 dan alumni.', 'beasiswa'),
(43, 'Berapa banyak peluang beasiswa kuliah luar negeri untuk mahasiswa S1 Darmajaya?|Peluang beasiswa kuliah luar negeri S1 Darmajaya berapa?|Saya mau tahu jumlah beasiswa luar negeri S1 Darmajaya|Banyaknya beasiswa kuliah luar negeri S1 Darmajaya berapa?|Kesempatan beasiswa luar negeri S1 Darmajaya ada ', 'Beasiswa full di luar negeri tersedia untuk mahasiswa dan alumni S1 melalui program kemitraan kampus.', 'beasiswa'),
(44, 'Bagaimana jalur pendaftaran S1 Darmajaya?|Jalur pendaftaran S1 Darmajaya apa saja?|Saya mau tahu jalur pendaftaran S1 Darmajaya|Pendaftaran S1 Darmajaya lewat jalur apa?|Apa saja jalur masuk S1 Darmajaya?', 'Pendaftaran S1 melalui portal PMB Darmajaya: pilih jalur Reguler, Karyawan, Prestasi/KIP, bayar biaya pendaftaran, unggah dokumen.', 'pendaftaran'),
(45, 'Apakah Darmajaya menerima KIP Kuliah untuk S1?|Penerimaan KIP Kuliah di S1 Darmajaya ada?|Saya mau tahu apakah S1 Darmajaya menerima KIP Kuliah|Darmajaya menerima mahasiswa S1 lewat KIP Kuliah?|Apakah tersedia jalur KIP Kuliah S1 Darmajaya?', 'Ya, Darmajaya menerima mahasiswa baru S1 melalui jalur Beasiswa KIP Kuliah.', 'pendaftaran'),
(46, 'Berapa biaya pendaftaran S1 Darmajaya?|Biaya pendaftaran S1 Darmajaya berapa?|Saya mau tahu biaya daftar S1 Darmajaya|Pendaftaran S1 Darmajaya biayanya berapa?|Berapa harga pendaftaran S1 Darmajaya?', 'Biaya pendaftaran tergantung jalur masuk (Reguler, Karyawan, Prestasi, KIP). Info lengkap tersedia di portal PMB Darmajaya.', 'pendaftaran'),
(47, 'Bagaimana cara mendaftar mahasiswa baru S1 Darmajaya?|Cara daftar mahasiswa baru S1 Darmajaya bagaimana?|Langkah pendaftaran mahasiswa baru S1 Darmajaya?|Saya mau tahu cara daftar mahasiswa baru S1 Darmajaya|Gimana daftar mahasiswa baru S1 Darmajaya?', 'Pendaftaran dilakukan melalui portal PMB Darmajaya di pmb.darmajaya.ac.id. Pilih jalur (Reguler, Prestasi, Karyawan, KIP), isi data, bayar biaya pendaftaran, dan unggah dokumen.', 'pendaftaran'),
(48, 'Apa saja jalur pendaftaran yang tersedia di S1 Darmajaya?|Jalur pendaftaran yang tersedia di S1 Darmajaya apa saja?|Saya mau tahu jalur masuk S1 Darmajaya|Pendaftaran S1 Darmajaya ada jalur apa saja?|Apa saja pilihan jalur pendaftaran S1 Darmajaya?', 'Jalur Reguler, Prestasi, Karyawan, dan Beasiswa KIP Kuliah tersedia untuk mahasiswa baru S1 Darmajaya.', 'pendaftaran'),
(49, 'Berapa biaya pendaftaran S1 Darmajaya?|Biaya pendaftaran S1 Darmajaya berapa?|Saya mau tahu biaya daftar S1 Darmajaya|Pendaftaran S1 Darmajaya biayanya berapa?|Berapa harga pendaftaran S1 Darmajaya?', 'Biaya pendaftaran bervariasi tergantung jalur masuk, sekitar Rp200.000–Rp300.000. Informasi lengkap ada di portal PMB Darmajaya.', 'pendaftaran'),
(50, 'Bagaimana cara cek status pendaftaran S1 Darmajaya?|Cara mengecek status pendaftaran S1 Darmajaya bagaimana?|Langkah cek status pendaftaran S1 Darmajaya?|Saya mau tahu status pendaftaran S1 Darmajaya|Gimana cek status pendaftaran S1 Darmajaya?', 'Login kembali ke portal PMB Darmajaya dan cek menu status pendaftaran atau hubungi bagian admisi.', 'pendaftaran'),
(51, 'Beasiswa apa saja yang tersedia di Darmajaya untuk S1?|Jenis beasiswa apa saja di S1 Darmajaya?|Saya mau tahu beasiswa S1 Darmajaya|Beasiswa S1 Darmajaya ada apa saja?|Apa saja pilihan beasiswa di S1 Darmajaya?', 'Ada Beasiswa KIP Kuliah, Prestasi, Hafizh Quran, Yatim/Piatu, dan Beasiswa Yayasan Alfian Husin.', 'beasiswa'),
(52, 'Bagaimana cara daftar beasiswa KIP Kuliah S1 Darmajaya?|Cara mendaftar beasiswa KIP Kuliah S1 Darmajaya bagaimana?|Langkah daftar beasiswa KIP Kuliah S1 Darmajaya?|Saya mau tahu cara daftar beasiswa KIP Kuliah S1 Darmajaya|Gimana daftar beasiswa KIP Kuliah S1 Darmajaya?', 'Daftar di portal PMB Darmajaya dengan melengkapi data ekonomi, unggah KIP, dan mengikuti seleksi administratif.', 'beasiswa'),
(53, 'Berapa kuota beasiswa Yayasan Alfian Husin S1 Darmajaya?|Kuota beasiswa Yayasan Alfian Husin S1 berapa?|Saya mau tahu jumlah beasiswa Yayasan Alfian Husin S1|Banyaknya beasiswa Yayasan Alfian Husin S1 berapa?|Beasiswa Yayasan Alfian Husin S1 jumlahnya berapa?', 'Tersedia sekitar 56 kuota beasiswa Yayasan Alfian Husin untuk S1 Darmajaya setiap tahun.', 'beasiswa'),
(54, 'Bagaimana cara bayar BPP lewat Siakad?|Cara membayar BPP lewat Siakad bagaimana?|Langkah bayar BPP lewat Siakad?|Saya mau tahu cara bayar BPP lewat Siakad|Gimana bayar BPP lewat Siakad?', 'Login ke Siakad, pilih menu Pembayaran, pilih tagihan BPP, dan lakukan pembayaran melalui bank yang ditunjuk atau virtual account.', 'pembayaran'),
(55, 'Apakah bisa membayar SKS tambahan?|Bisa bayar SKS tambahan atau tidak?|Saya mau tahu pembayaran SKS tambahan|Bolehkah membayar SKS tambahan?|SKS tambahan bisa dibayar?', 'Ya, pembayaran SKS tambahan dilakukan melalui menu Pembayaran di Siakad sesuai jumlah SKS yang diambil.', 'pembayaran'),
(56, 'Apakah bisa mencicil pembayaran kuliah?|Bisa mencicil pembayaran kuliah atau tidak?|Saya mau tahu cara mencicil pembayaran kuliah|Kuliah bisa dibayar cicilan?|Pembayaran kuliah bisa dicicil?', 'Bisa, ajukan permohonan cicilan ke biro keuangan kampus sebelum jatuh tempo pembayaran.', 'pembayaran'),
(57, 'Bagaimana cara mengisi KRS di Siakad?|Cara isi KRS di Siakad bagaimana?|Langkah mengisi KRS di Siakad?|Saya mau tahu cara isi KRS di Siakad|Gimana mengisi KRS di Siakad?', 'Login ke Siakad, buka menu Pengisian KRS, pilih mata kuliah sesuai SKS yang diperbolehkan, lalu klik Simpan/Submit.', 'krs'),
(58, 'Apa yang dilakukan jika KRS error di Siakad?|KRS error di Siakad harus bagaimana?|Saya mau tahu solusi KRS error di Siakad|Bagaimana mengatasi KRS error di Siakad?|KRS error di Siakad bisa diatasi bagaimana?', 'Coba login di waktu berbeda (tidak padat). Jika masih error, hubungi admin akademik.', 'krs'),
(59, 'Bagaimana cara revisi KRS?|Cara revisi KRS bagaimana?|Langkah revisi KRS?|Saya mau tahu cara revisi KRS|Gimana revisi KRS?', 'Buka menu Pengisian KRS di Siakad, lakukan perubahan, dan simpan ulang sebelum batas waktu yang ditentukan.', 'krs'),
(60, 'Bagaimana cara mengajukan cuti kuliah?|Cara mengajukan cuti kuliah bagaimana?|Langkah mengajukan cuti kuliah?|Saya mau tahu cara ajukan cuti kuliah|Gimana cara ajukan cuti kuliah?', 'Isi formulir cuti di bagian akademik atau online jika tersedia. Bayar biaya administrasi dan dapatkan persetujuan fakultas.', 'cuti'),
(61, 'Berapa lama maksimal cuti kuliah?|Maksimal cuti kuliah berapa lama?|Saya mau tahu batas waktu cuti kuliah|Cuti kuliah bisa diambil berapa lama?|Berapa lama cuti kuliah diperbolehkan?', 'Maksimal 4 semester (2 tahun) selama masa studi dengan ketentuan tidak berturut-turut lebih dari 2 semester.', 'cuti'),
(62, 'Bagaimana cara cetak kartu ujian?|Cara cetak kartu ujian bagaimana?|Langkah mencetak kartu ujian?|Saya mau tahu cara cetak kartu ujian|Gimana mencetak kartu ujian?', 'Login ke Siakad, pastikan semua tagihan sudah lunas, lalu cetak kartu ujian dari menu Cetak Kartu Ujian.', 'ujian'),
(63, 'Apa yang dilakukan jika lupa membawa kartu ujian?|Lupa bawa kartu ujian harus bagaimana?|Saya mau tahu solusi lupa bawa kartu ujian|Bagaimana mengatasi lupa membawa kartu ujian?|Kartu ujian tertinggal, apa yang harus dilakukan?', 'Segera lapor ke pengawas atau biro akademik untuk dibuatkan kartu pengganti sementara.', 'ujian'),
(64, 'Apa saja syarat ikut PKPM?|Syarat mengikuti PKPM apa saja?|Saya mau tahu syarat PKPM|PKPM persyaratannya apa saja?|Apa saja ketentuan ikut PKPM?', 'Minimal telah menyelesaikan 100 SKS, lulus mata kuliah Metodologi Penelitian, dan tidak memiliki tunggakan pembayaran.', 'pkpm'),
(65, 'Bagaimana cara daftar PKPM?|Cara daftar PKPM bagaimana?|Langkah mendaftar PKPM?|Saya mau tahu cara daftar PKPM|Gimana mendaftar PKPM?', 'Daftar di portal PKPM atau akademik kampus, ikuti pembekalan, dan tentukan lokasi penempatan kelompok.', 'pkpm'),
(66, 'Apa syarat mengajukan skripsi?|Syarat ajukan skripsi apa saja?|Saya mau tahu syarat mengajukan skripsi|Ketentuan mengajukan skripsi apa saja?|Apa saja persyaratan mengajukan skripsi?', 'Minimal lulus 144 SKS, IPK ≥2.75, lulus PKPM, dan mata kuliah Metodologi Penelitian.', 'skripsi'),
(67, 'Bagaimana cara daftar sidang skripsi?|Cara daftar sidang skripsi bagaimana?|Langkah mendaftar sidang skripsi?|Saya mau tahu cara daftar sidang skripsi|Gimana mendaftar sidang skripsi?', 'siapkan berkas syarat sidang yang sudah lengkap, lalu ke BAAK serahkan berkas ke staf, jika sudah jangan lupa untuk melalukan pendaftaran secara online melalui link https://sidang.hariwib.com/registrasi.php', 'skripsi'),
(68, 'Apa saja syarat ikut yudisium?|Syarat mengikuti yudisium apa saja?|Saya mau tahu syarat yudisium|Ketentuan ikut yudisium apa saja?|Apa saja persyaratan ikut yudisium?', 'Semua nilai sudah keluar, tidak ada tunggakan pembayaran, dan menyerahkan berkas administrasi kelulusan.', 'yudisium'),
(69, 'Bagaimana cara daftar wisuda?|Cara daftar wisuda bagaimana?|Langkah mendaftar wisuda?|Saya mau tahu cara daftar wisuda|Gimana mendaftar wisuda?', 'Isi formulir pendaftaran wisuda di akademik, unggah pas foto terbaru, dan bayar biaya wisuda.', 'wisuda'),
(70, 'Bagaimana cara mengurus surat aktif kuliah?|Cara mengurus surat aktif kuliah bagaimana?|Langkah mengurus surat aktif kuliah?|Saya mau tahu cara urus surat aktif kuliah|Gimana urus surat aktif kuliah?', 'Ajukan permohonan surat aktif kuliah ke bagian akademik melalui Siakad atau loket pelayanan.', 'umum'),
(71, 'Bagaimana cara mengganti KTM yang hilang?|Cara mengganti KTM hilang bagaimana?|Langkah ganti KTM hilang?|Saya mau tahu cara ganti KTM hilang|Gimana ganti KTM hilang?', 'Lapor ke bagian akademik, isi formulir, dan bayar biaya penggantian kartu.', 'umum'),
(72, 'Bagaimana cara cek nilai di Siakad?|Cara melihat nilai di Siakad bagaimana?|Langkah cek nilai di Siakad?|Saya mau tahu cara melihat nilai di Siakad|Gimana melihat nilai di Siakad?', 'Login ke Siakad, buka menu KHS atau Transkrip Nilai untuk melihat hasil per semester.', 'umum'),
(75, '1. \"Apa saja persyaratan yang harus saya siapkan untuk sidang skripsi?\"|\"Bisa sebutkan dokumen dan berkas yang wajib dibawa saat sidang?\"|\"Syarat administrasi sidang skripsi itu apa saja ya?\"|\"Kalau mau ikut sidang skripsi, apa saja yang harus dilengkapi?\"|\"Tolong jelaskan daftar berkas dan ketentua', 'Persyaratan Sidang\r\n\r\n1. Surat Persetujuan Sidang.\r\n\r\n\r\n2. Rangkuman Nilai Asli (yang tidak bermasalah).\r\n\r\n\r\n3. Fotokopi Form Bimbingan yang telah disetujui Pembimbing dan ditandatangani Ketua Prodi.\r\n\r\n\r\n4. Kartu Seminar.\r\n\r\n\r\n5. Fotokopi KRS Semester Terakhir.\r\n\r\n\r\n6. Fotokopi Ijazah SLTA atau fotokopi Ijazah D3 (bagi lulusan Diploma).\r\n\r\n\r\n7. Fotokopi slip pembayaran skripsi dan fotokopi slip perpanjangan SK (bagi yang memperpanjang SK skripsi).\r\n\r\n\r\n8. Fotokopi slip pembayaran sidang ulang (bagi yang mengulang).\r\n\r\n\r\n9. Fotokopi Surat Pindah dari PTS sebelumnya dan Transkrip Nilai dari PTS sebelumnya.\r\n\r\n\r\n10. Hasil konversi nilai dari PTS baru.\r\n\r\n\r\n11. Fotokopi SK Pembimbing Skripsi dan SK Perpanjangan (untuk yang memperpanjang SK).\r\n\r\n\r\n12. Fotokopi Kartu Keluarga (KK) dan fotokopi KTP (untuk FDPT).\r\n\r\n\r\n13. Fotokopi legalisir sertifikat Soft Skill, Sertifikat Ories, Minat Bakat, dan Kegiatan Keagamaan\r\n(BBQ / Retreat / Simakrama / Budha Camp).\r\n\r\n\r\n14. Penulisan Skripsi (hardcover, 3 eksemplar), diberikan kepada dosen penguji maksimal H-1 sidang.\r\n\r\n\r\n15. Fotokopi sertifikat Internasional (HTML5 / MOS / FORESEC / DBFA / ACA) dan Nasional (BNSP).\r\n\r\n\r\n16. Fotokopi Sertifikat TOEFL atau Surat Keterangan Lulus Bahasa Inggris.\r\n\r\n\r\n17. Foto hitam putih ukuran 3×4 sebanyak 4 lembar, diberi nama dan NPM di belakang foto.\r\n\r\nPakaian: kebaya (perempuan) atau jas (laki-laki).\r\n\r\nUntuk ijazah & transkrip nilai menggunakan kertas dop, bukan hasil printing.\r\n\r\n\r\n\r\n18. Semua berkas dimasukkan ke dalam map sesuai warna:\r\n\r\nBiru → Ilmu Komputer\r\n\r\nKuning → Ekonomi & Bisnis\r\n\r\nHijau → DHP\r\n\r\n\r\n\r\n19. Map diberi identitas: NPM, Nama, Nomor Telepon, dan Nama Pembimbing.\r\n\r\n\r\n20. Hasil cek plagiat yang sudah diperiksa & diparaf oleh pembimbing.\r\n\r\n\r\n21. Cetak/print out profil mahasiswa/i dari PDDIKTI melalui link:\r\nhttp://pddikti.kemdikbud.go.id.', 'skripsi');

-- --------------------------------------------------------

--
-- Table structure for table `history_chat`
--

CREATE TABLE `history_chat` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `response` text NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `message` text NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `is_read`, `message`, `timestamp`) VALUES
(143, 9, 4, 1, 'ada apa', '2026-01-11 07:06:59'),
(144, 4, 9, 1, 'berisik', '2026-01-11 07:49:35'),
(145, 4, 9, 1, '😍', '2026-01-11 07:58:02'),
(146, 9, 4, 1, 'hay', '2026-01-11 08:05:06'),
(147, 4, 9, 1, 'y', '2026-01-11 08:05:13'),
(148, 9, 4, 1, 'ada apa', '2026-01-11 08:05:19'),
(149, 4, 9, 1, 'g', '2026-01-11 08:05:33'),
(150, 9, 4, 1, '😚', '2026-01-11 08:05:46'),
(151, 9, 4, 1, 'ada apa', '2026-01-11 08:06:17'),
(152, 9, 4, 0, '😎', '2026-01-11 08:07:08');

-- --------------------------------------------------------

--
-- Table structure for table `riwayat_chatbot`
--

CREATE TABLE `riwayat_chatbot` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `pertanyaan_user` text NOT NULL,
  `jawaban_bot` text NOT NULL,
  `kategori` varchar(100) DEFAULT NULL,
  `waktu` timestamp NOT NULL DEFAULT current_timestamp(),
  `feedback` enum('bantu','tidak') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `riwayat_chatbot`
--

INSERT INTO `riwayat_chatbot` (`id`, `user_id`, `pertanyaan_user`, `jawaban_bot`, `kategori`, `waktu`, `feedback`) VALUES
(34, 10, 'Bagaimana cara daftar wisuda? ya', 'Isi formulir pendaftaran wisuda di akademik, unggah pas foto terbaru, dan bayar biaya wisuda.', 'wisuda', '2025-08-03 15:41:28', NULL),
(35, 10, 'pendaftaran s1 Darmajaya? bagaimana jalur', 'Pendaftaran S1 melalui portal PMB Darmajaya: pilih jalur Reguler, Karyawan, Prestasi/KIP, bayar biaya pendaftaran, unggah dokumen.', 'pendaftaran', '2025-08-03 15:41:41', NULL),
(36, 10, 'pendaftaran s1 Darmajaya? bagaimana jalur', 'Pendaftaran S1 melalui portal PMB Darmajaya: pilih jalur Reguler, Karyawan, Prestasi/KIP, bayar biaya pendaftaran, unggah dokumen.', 'pendaftaran', '2025-08-03 15:41:53', NULL),
(37, 10, 'Bagaimana cara daftar PKPM? bro', 'Daftar di portal PKPM atau akademik kampus, ikuti pembekalan, dan tentukan lokasi penempatan kelompok.', 'pkpm', '2025-08-03 15:43:01', NULL),
(38, 10, 'Berapa lama maksimal cuti kuliah?', 'Maksimal 4 semester (2 tahun) selama masa studi dengan ketentuan tidak berturut-turut lebih dari 2 semester.', 'cuti', '2025-08-03 15:43:14', NULL),
(39, 10, 'kapan pembayaran bpp mahasiswa lama semester ganjil?', 'Pembayaran BPP Mahasiswa Lama Semester Ganjil 2024/2025: 5 Agustus - 16 September 2024.', 'pembayaran', '2025-08-03 15:43:33', NULL),
(40, 10, 'bpp', 'Pembayaran BPP Mahasiswa Lama Semester Ganjil 2024/2025: 5 Agustus - 16 September 2024.', 'pembayaran', '2025-08-03 15:43:43', NULL),
(41, 10, 'cuti', '⚠️ Saya belum punya jawaban untuk pertanyaan ini. Silakan hubungi bagian akademik atau coba dengan kata kunci lain.', 'pembayaran', '2025-08-03 15:43:55', NULL),
(42, 10, 'pengajuan cuti', 'Batas akhir pengajuan cuti Semester Ganjil 2024/2025: 16 September 2024.', 'cuti', '2025-08-03 15:44:16', NULL),
(43, 10, 'kapan pengisian krs semester ganjil? ya', 'Pengisian KRS Semester Ganjil 2024/2025: 13 - 16 September 2024.', 'krs', '2025-08-03 15:58:00', NULL),
(44, 10, 'pendaftaran s1 Darmajaya? bagaimana jalur', 'Pendaftaran S1 melalui portal PMB Darmajaya: pilih jalur Reguler, Karyawan, Prestasi/KIP, bayar biaya pendaftaran, unggah dokumen.', 'pendaftaran', '2025-08-03 15:58:03', NULL),
(45, 10, 'bagaimana jalur pendaftaran s1 Darmajaya?', 'Pendaftaran S1 melalui portal PMB Darmajaya: pilih jalur Reguler, Karyawan, Prestasi/KIP, bayar biaya pendaftaran, unggah dokumen.', 'pendaftaran', '2025-08-03 15:58:11', NULL),
(57, 10, '1. \\\"Apa saja persyaratan yang harus saya siapkan untuk sidang skripsi?\\\"', 'Persyaratan Sidang\r\n\r\n1. Surat Persetujuan Sidang.\r\n\r\n\r\n2. Rangkuman Nilai Asli (yang tidak bermasalah).\r\n\r\n\r\n3. Fotokopi Form Bimbingan yang telah disetujui Pembimbing dan ditandatangani Ketua Prodi.\r\n\r\n\r\n4. Kartu Seminar.\r\n\r\n\r\n5. Fotokopi KRS Semester Terakhir.\r\n\r\n\r\n6. Fotokopi Ijazah SLTA atau fo', 'skripsi', '2025-08-13 12:02:28', NULL),
(79, 9, 'ok', 'Maaf, pertanyaan terlalu pendek. Coba lebih spesifik ya!', '', '2026-01-11 08:35:25', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `STATUS` enum('online','offline') DEFAULT 'offline',
  `is_online` tinyint(1) DEFAULT 0,
  `email` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `created_at`, `role`, `STATUS`, `is_online`, `email`) VALUES
(4, 'admin', '$2y$10$buKksupePSkrhQsvGNoTyes7EEmo6U3mPttpwFbP0hR3p68FpriKi', '2025-03-16 14:22:42', 'admin', 'online', 1, 'user1@gmail.com'),
(9, 'ekosaputra', '$2y$10$shptPL9a83N59CXcTAMmTumNRWTT0npTNrE5Dj80EVXT1YnOC9u1m', '2025-05-25 03:13:59', 'user', 'online', 1, 'eko@gmail.com'),
(10, 'eko', '$2y$10$PLf7d8Z0QBUZJRBwWIOgkuA8wgQIC1zaFQCSDa4mBuRz17IQ7dKlS', '2025-05-25 03:15:15', 'admin', 'online', 1, 'ekosaputra@gmail.com'),
(11, 'tes', '$2y$10$a44lz7D0.JItczU/zONlYOvqwUDcdn9ktBT8h/hHe7lhW3b3qZfDa', '2025-06-10 13:47:29', 'user', 'offline', 0, 'putra@gmail.com');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `chatbot`
--
ALTER TABLE `chatbot`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `riwayat_chatbot`
--
ALTER TABLE `riwayat_chatbot`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `chatbot`
--
ALTER TABLE `chatbot`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=153;

--
-- AUTO_INCREMENT for table `riwayat_chatbot`
--
ALTER TABLE `riwayat_chatbot`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=80;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `riwayat_chatbot`
--
ALTER TABLE `riwayat_chatbot`
  ADD CONSTRAINT `riwayat_chatbot_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
