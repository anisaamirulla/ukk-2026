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

// Periksa data tahun ajaran
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT id FROM t_tahun_ajaran WHERE id = ?"
);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$cek = mysqli_stmt_get_result($stmt);

if (!$cek || mysqli_num_rows($cek) === 0) {
    header("Location: tahun_ajaran.php?pesan=gagal_hapus");
    exit;
}

mysqli_begin_transaction($koneksi);

try {
    // Hapus catatan pelanggaran terkait tahun ajaran
    $stmt = mysqli_prepare(
        $koneksi,
        "DELETE FROM t_pelanggaran_siswa WHERE tahun_ajaran_id = ?"
    );
    mysqli_stmt_bind_param($stmt, "i", $id);

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Gagal menghapus catatan pelanggaran.");
    }

    // Hapus penempatan siswa terkait tahun ajaran
    $stmt = mysqli_prepare(
        $koneksi,
        "DELETE FROM t_kelas_siswa WHERE tahun_ajaran_id = ?"
    );
    mysqli_stmt_bind_param($stmt, "i", $id);

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Gagal menghapus penempatan siswa.");
    }

    // Hapus penugasan wali kelas terkait tahun ajaran
    $stmt = mysqli_prepare(
        $koneksi,
        "DELETE FROM t_wali_kelas WHERE tahun_ajaran_id = ?"
    );
    mysqli_stmt_bind_param($stmt, "i", $id);

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Gagal menghapus penugasan wali kelas.");
    }

    // Hapus tahun ajaran
    $stmt = mysqli_prepare(
        $koneksi,
        "DELETE FROM t_tahun_ajaran WHERE id = ?"
    );
    mysqli_stmt_bind_param($stmt, "i", $id);

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Gagal menghapus data tahun ajaran.");
    }

    mysqli_commit($koneksi);

    header("Location: tahun_ajaran.php?pesan=hapus_berhasil");
    exit;

} catch (Throwable $e) {
    mysqli_rollback($koneksi);

    error_log("Gagal menghapus tahun ajaran ID $id: " . $e->getMessage());

    header("Location: tahun_ajaran.php?pesan=gagal_hapus");
    exit;
}
?>