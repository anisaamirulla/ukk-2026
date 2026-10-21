
<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    exit("Anda tidak memiliki akses");
}

require_once __DIR__ . "/config/koneksi.php";

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: guru.php?pesan=gagal_hapus");
    exit;
}

$id = (int) $_GET['id'];

if ($id <= 0) {
    header("Location: guru.php?pesan=gagal_hapus");
    exit;
}

mysqli_begin_transaction($koneksi);

try {
    // Hapus penugasan wali kelas yang terkait dengan guru
    $stmtWali = mysqli_prepare(
        $koneksi,
        "DELETE FROM t_wali_kelas WHERE guru_id = ?"
    );

    if (!$stmtWali) {
        throw new Exception(mysqli_error($koneksi));
    }

    mysqli_stmt_bind_param($stmtWali, "i", $id);

    if (!mysqli_stmt_execute($stmtWali)) {
        throw new Exception(mysqli_stmt_error($stmtWali));
    }

    mysqli_stmt_close($stmtWali);

    // Hapus data guru
    $stmtGuru = mysqli_prepare(
        $koneksi,
        "DELETE FROM t_guru WHERE id = ?"
    );

    if (!$stmtGuru) {
        throw new Exception(mysqli_error($koneksi));
    }

    mysqli_stmt_bind_param($stmtGuru, "i", $id);

    if (!mysqli_stmt_execute($stmtGuru)) {
        throw new Exception(mysqli_stmt_error($stmtGuru));
    }

    $jumlahTerhapus = mysqli_stmt_affected_rows($stmtGuru);
    mysqli_stmt_close($stmtGuru);

    if ($jumlahTerhapus <= 0) {
        throw new Exception("Data guru tidak ditemukan.");
    }

    mysqli_commit($koneksi);

    header("Location: guru.php?pesan=hapus");
    exit;

} catch (Throwable $e) {
    mysqli_rollback($koneksi);

    die(
        "Data guru gagal dihapus. Penyebab: " .
        htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') .
        "<br><br><a href='guru.php'>Kembali ke Data Guru</a>"
    );
}
?>
