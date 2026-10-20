
<?php
session_start();
require_once __DIR__ . "/config/koneksi.php";

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    exit("Anda tidak memiliki akses");
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: kelas.php");
    exit;
}

$id = (int) $_GET['id'];

mysqli_begin_transaction($koneksi);

try {
    // Hapus catatan pelanggaran yang menggunakan kelas ini
    $hapusPelanggaran = mysqli_query(
        $koneksi,
        "DELETE FROM t_pelanggaran_siswa WHERE kelas_id = $id"
    );

    if (!$hapusPelanggaran) {
        throw new Exception(mysqli_error($koneksi));
    }

    // Hapus penempatan wali kelas yang menggunakan kelas ini
    $hapusWali = mysqli_query(
        $koneksi,
        "DELETE FROM t_wali_kelas WHERE kelas_id = $id"
    );

    if (!$hapusWali) {
        throw new Exception(mysqli_error($koneksi));
    }

    // Hapus data kelas
    $hapusKelas = mysqli_query(
        $koneksi,
        "DELETE FROM t_kelas WHERE id = $id"
    );

    if (!$hapusKelas) {
        throw new Exception(mysqli_error($koneksi));
    }

    mysqli_commit($koneksi);

    header("Location: kelas.php?pesan=hapus_berhasil");
    exit;

} catch (Exception $e) {
    mysqli_rollback($koneksi);

    echo "<div style='font-family: Arial; padding: 20px;'>";
    echo "<h3>Data kelas gagal dihapus!</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<a href='kelas.php'>Kembali ke Data Kelas</a>";
    echo "</div>";
    exit;
}
?>
