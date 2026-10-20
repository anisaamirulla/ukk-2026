
 // PROSES HAPUS DATA SISWA
if (isset($_POST['hapus'])) {
    $id = (int) ($_POST['id'] ?? 0);

    if ($id <= 0) {
        header("Location: siswa.php?pesan=gagal_hapus");
        exit;
    }

    $cek = mysqli_query(
        $koneksi,
        "SELECT id FROM t_siswa WHERE id = $id"
    );

    if (!$cek || mysqli_num_rows($cek) == 0) {
        header("Location: siswa.php?pesan=gagal_hapus");
        exit;
    }

    // Hapus data pelanggaran siswa
    $hapus = mysqli_query(
        $koneksi,
        "DELETE FROM t_pelanggaran_siswa WHERE siswa_id = $id"
    );

    if (!$hapus) {
        die("Gagal menghapus pelanggaran: " . mysqli_error($koneksi));
    }

    // Hapus penempatan kelas
    $hapus = mysqli_query(
        $koneksi,
        "DELETE FROM t_kelas_siswa WHERE siswa_id = $id"
    );

    if (!$hapus) {
        die("Gagal menghapus penempatan kelas: " . mysqli_error($koneksi));
    }

    // Hapus siswa
    $hapus = mysqli_query(
        $koneksi,
        "DELETE FROM t_siswa WHERE id = $id"
    );

    if ($hapus) {
        header("Location: siswa.php?pesan=hapus_berhasil");
        exit;
    } else {
        die("Gagal menghapus siswa: " . mysqli_error($koneksi));
    }
}
