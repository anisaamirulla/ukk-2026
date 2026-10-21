
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
    $stmt = mysqli_prepare(
        $koneksi,
        "DELETE FROM t_pelanggaran_siswa WHERE kelas_id = ?"
    );
    if (!$stmt) {
        throw new Exception(mysqli_error($koneksi));
    }
    mysqli_stmt_bind_param($stmt, "i", $id);
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception(mysqli_stmt_error($stmt));
    }

    // Hapus penempatan wali kelas yang menggunakan kelas ini
    $stmt = mysqli_prepare(
        $koneksi,
        "DELETE FROM t_wali_kelas WHERE kelas_id = ?"
    );
    if (!$stmt) {
        throw new Exception(mysqli_error($koneksi));
    }
    mysqli_stmt_bind_param($stmt, "i", $id);
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception(mysqli_stmt_error($stmt));
    }

    // Hapus data kelas
    $stmt = mysqli_prepare(
        $koneksi,
        "DELETE FROM t_kelas WHERE id = ?"
    );
    if (!$stmt) {
        throw new Exception(mysqli_error($koneksi));
    }
    mysqli_stmt_bind_param($stmt, "i", $id);
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception(mysqli_stmt_error($stmt));
    }

    if (mysqli_stmt_affected_rows($stmt) < 1) {
        throw new Exception("Data kelas tidak ditemukan.");
    }

    mysqli_commit($koneksi);

    header("Location: kelas.php?pesan=hapus_berhasil");
    exit;

} catch (Throwable $e) {
    mysqli_rollback($koneksi);
    error_log("Gagal menghapus kelas ID {$id}: " . $e->getMessage());

    header("Location: kelas.php?pesan=gagal_hapus");
    exit;
}
?>
