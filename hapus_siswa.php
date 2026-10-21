
<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    exit("Anda tidak memiliki akses");
}

require_once __DIR__ . "/config/koneksi.php";

// Pastikan permintaan menggunakan POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: siswa.php");
    exit;
}

// Ambil ID siswa
$id = (int) ($_POST['id'] ?? 0);

if ($id <= 0) {
    header("Location: siswa.php?pesan=gagal_hapus");
    exit;
}

// Periksa apakah siswa tersedia
$cek = mysqli_query(
    $koneksi,
    "SELECT id FROM t_siswa WHERE id = $id"
);

if (!$cek || mysqli_num_rows($cek) === 0) {
    header("Location: siswa.php?pesan=gagal_hapus");
    exit;
}

// Mulai transaksi
mysqli_begin_transaction($koneksi);

try {
    // Hapus data pelanggaran siswa yang terkait
    $hapusPelanggaran = mysqli_query(
        $koneksi,
        "DELETE FROM t_pelanggaran_siswa WHERE siswa_id = $id"
    );

    if (!$hapusPelanggaran) {
        throw new Exception(mysqli_error($koneksi));
    }

    // Hapus data penempatan kelas siswa yang terkait
    $hapusKelas = mysqli_query(
        $koneksi,
        "DELETE FROM t_kelas_siswa WHERE siswa_id = $id"
    );

    if (!$hapusKelas) {
        throw new Exception(mysqli_error($koneksi));
    }

    // Hapus data siswa
    $hapusSiswa = mysqli_query(
        $koneksi,
        "DELETE FROM t_siswa WHERE id = $id"
    );

    if (!$hapusSiswa) {
        throw new Exception(mysqli_error($koneksi));
    }

    // Simpan semua perubahan
    mysqli_commit($koneksi);

    header("Location: siswa.php?pesan=hapus_berhasil");
    exit;

} catch (Exception $e) {
    // Batalkan jika ada proses yang gagal
    mysqli_rollback($koneksi);

    error_log("Gagal menghapus siswa ID $id: " . $e->getMessage());

    header("Location: siswa.php?pesan=gagal_hapus");
    exit;
}

