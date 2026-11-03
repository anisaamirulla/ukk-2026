<?php
session_start();

require_once __DIR__ . "/config/koneksi.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// CEK LOGIN
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

// CEK ADMIN
if (($_SESSION['role'] ?? '') != 'admin') {
    exit("Anda tidak memiliki akses");
}

// CEK METHOD
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: kategori_pelanggaran.php");
    exit;
}

// CEK ID
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    header("Location: kategori_pelanggaran.php?pesan=gagal");
    exit;
}

$id = (int) $_POST['id'];

try {

    // MULAI TRANSAKSI
    mysqli_begin_transaction($koneksi);

    // CEK KATEGORI
    $cek = mysqli_prepare(
        $koneksi,
        "SELECT id FROM t_pelanggaran_kategori WHERE id = ?"
    );

    mysqli_stmt_bind_param($cek, "i", $id);
    mysqli_stmt_execute($cek);

    $hasil = mysqli_stmt_get_result($cek);
    $kategori = mysqli_fetch_assoc($hasil);

    mysqli_stmt_close($cek);

    if (!$kategori) {
        throw new Exception("Kategori tidak ditemukan.");
    }

    // 1. HAPUS RIWAYAT SISWA BERDASARKAN JENIS PELANGGARAN
    $hapusRiwayat = mysqli_prepare(
        $koneksi,
        "DELETE FROM t_pelanggaran_siswa
         WHERE pelanggaran_id IN (
             SELECT id
             FROM t_pelanggaran
             WHERE pelanggaran_kategori_id = ?
         )"
    );

    mysqli_stmt_bind_param($hapusRiwayat, "i", $id);
    mysqli_stmt_execute($hapusRiwayat);
    mysqli_stmt_close($hapusRiwayat);

    // 2. HAPUS SEMUA JENIS PELANGGARAN DALAM KATEGORI
    $hapusJenis = mysqli_prepare(
        $koneksi,
        "DELETE FROM t_pelanggaran
         WHERE pelanggaran_kategori_id = ?"
    );

    mysqli_stmt_bind_param($hapusJenis, "i", $id);
    mysqli_stmt_execute($hapusJenis);
    mysqli_stmt_close($hapusJenis);

    // 3. HAPUS KATEGORI
    $hapusKategori = mysqli_prepare(
        $koneksi,
        "DELETE FROM t_pelanggaran_kategori
         WHERE id = ?"
    );

    mysqli_stmt_bind_param($hapusKategori, "i", $id);
    mysqli_stmt_execute($hapusKategori);

    $berhasil = mysqli_stmt_affected_rows($hapusKategori) > 0;

    mysqli_stmt_close($hapusKategori);

    if (!$berhasil) {
        throw new Exception("Kategori gagal dihapus.");
    }

    // SIMPAN PERUBAHAN
    mysqli_commit($koneksi);

    header("Location: kategori_pelanggaran.php?pesan=hapus");
    exit;

} catch (Throwable $e) {

    // BATALKAN SEMUA JIKA TERJADI KESALAHAN
    mysqli_rollback($koneksi);

    echo "<h3>Penghapusan gagal!</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<a href='kategori_pelanggaran.php'>Kembali</a>";
}
?>
