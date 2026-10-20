
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

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: tahun_ajaran.php");
    exit;
}

$id = (int) $_GET['id'];

// Periksa tahun ajaran
$cek = mysqli_query(
    $koneksi,
    "SELECT id FROM t_tahun_ajaran WHERE id = $id"
);

if (!$cek || mysqli_num_rows($cek) == 0) {
    header("Location: tahun_ajaran.php");
    exit;
}

mysqli_begin_transaction($koneksi);

try {
    // 1. Hapus catatan pelanggaran terkait tahun ajaran
    $hapusPelanggaran = mysqli_query(
        $koneksi,
        "DELETE FROM t_pelanggaran_siswa
         WHERE tahun_ajaran_id = $id"
    );

    if (!$hapusPelanggaran) {
        throw new Exception(mysqli_error($koneksi));
    }

    // 2. Hapus penempatan siswa terkait tahun ajaran
    $hapusSiswa = mysqli_query(
        $koneksi,
        "DELETE FROM t_kelas_siswa
         WHERE tahun_ajaran_id = $id"
    );

    if (!$hapusSiswa) {
        throw new Exception(mysqli_error($koneksi));
    }

    // 3. Hapus penugasan wali kelas terkait tahun ajaran
    $hapusWali = mysqli_query(
        $koneksi,
        "DELETE FROM t_wali_kelas
         WHERE tahun_ajaran_id = $id"
    );

    if (!$hapusWali) {
        throw new Exception(mysqli_error($koneksi));
    }

    // 4. Hapus tahun ajaran
    $hapusTahun = mysqli_query(
        $koneksi,
        "DELETE FROM t_tahun_ajaran
         WHERE id = $id"
    );

    if (!$hapusTahun) {
        throw new Exception(mysqli_error($koneksi));
    }

    mysqli_commit($koneksi);

    header("Location: tahun_ajaran.php?pesan=hapus_berhasil");
    exit;

} catch (Exception $e) {
    mysqli_rollback($koneksi);

    echo "<div style='font-family:Arial;padding:20px'>";
    echo "<h3>Data tahun ajaran gagal dihapus!</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<a href='tahun_ajaran.php'>Kembali ke Data Tahun Ajaran</a>";
    echo "</div>";
    exit;
}
?>
