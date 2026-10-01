<?php
require_once __DIR__ . '/config/koneksi.php';


/* =========================
   HAPUS DATA
========================= */
if (isset($_GET['hapus'])) {

    $id = (int) $_GET['hapus'];

    mysqli_query($koneksi, "DELETE FROM t_siswa WHERE id = $id");

    header("Location: siswa.php");
    exit;
}


/* =========================
   TAMBAH DATA
========================= */
if (isset($_POST['tambah'])) {

    $nip = mysqli_real_escape_string($koneksi, $_POST['nip']);
    $nisn = mysqli_real_escape_string($koneksi, $_POST['nisn']);
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $jenis_kelamin = mysqli_real_escape_string($koneksi, $_POST['jenis_kelamin']);
    $tanggal_lahir = mysqli_real_escape_string($koneksi, $_POST['tanggal_lahir']);
    $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $status_aktif = (int) $_POST['status_aktif'];

    mysqli_query($koneksi, "
        INSERT INTO t_siswa
        (nip, nisn, nama, jenis_kelamin, tanggal_lahir, alamat, status_aktif)
        VALUES
        ('$nip', '$nisn', '$nama', '$jenis_kelamin', '$tanggal_lahir', '$alamat', '$status_aktif')
    ");

    header("Location: siswa.php");
    exit;
}


/* =========================
   TAMPILAN TAMBAH
========================= */
if (isset($_GET['tambah'])) {
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Tambah Data Siswa</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>

<body class="bg-light">

<div class="container py-5">

    <div class="mb-4">

        <h1 class="fw-bold mb-1">
            Tambah Data Siswa
        </h1>

        <p class="text-secondary mb-2">
            Masukkan informasi siswa baru.
        </p>

        <a
            href="siswa.php"
            class="btn btn-secondary btn-sm"
        >
            Kembali ke Data Siswa
        </a>

    </div>


    <div class="card border-0 shadow-sm">

        <div class="card-body p-4">

            <form method="POST">

                <div class="row g-3">

                    <div class="col-md-6">

                        <label class="form-label">
                            NISN
                        </label>

                        <input
                            type="text"
                            name="nisn"
                            class="form-control"
                            required
                        >

                    </div>


                    <div class="col-md-6">

                        <label class="form-label">
                            NIP
                        </label>

                        <input
                            type="text"
                            name="nip"
                            class="form-control"
                            required
                        >

                    </div>


                    <div class="col-md-6">

                        <label class="form-label">
                            Nama Siswa
                        </label>

                        <input
                            type="text"
                            name="nama"
                            class="form-control"
                            required
                        >

                    </div>


                    <div class="col-md-6">

                        <label class="form-label">
                            Jenis Kelamin
                        </label>

                        <select
                            name="jenis_kelamin"
                            class="form-select"
                            required
                        >

                            <option value="">
                                Pilih Jenis Kelamin
                            </option>

                            <option value="L">
                                Laki-laki
                            </option>

                            <option value="P">
                                Perempuan
                            </option>

                        </select>

                    </div>


                    <div class="col-md-6">

                        <label class="form-label">
                            Tanggal Lahir
                        </label>

                        <input
                            type="date"
                            name="tanggal_lahir"
                            class="form-control"
                            required
                        >

                    </div>


                    <div class="col-md-6">

                        <label class="form-label">
                            Status Aktif
                        </label>

                        <select
                            name="status_aktif"
                            class="form-select"
                            required
                        >

                            <option value="1">
                                Aktif
                            </option>

                            <option value="0">
                                Tidak Aktif
                            </option>

                        </select>

                    </div>


                    <div class="col-12">

                        <label class="form-label">
                            Alamat
                        </label>

                        <textarea
                            name="alamat"
                            class="form-control"
                            rows="3"
                            required
                        ></textarea>

                    </div>

                </div>


                <div class="mt-4">

                    <a
                        href="siswa.php"
                        class="btn btn-secondary"
                    >
                        Batal
                    </a>

                    <button
                        type="submit"
                        name="tambah"
                        class="btn btn-primary"
                    >
                        Tambah Data
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

</body>
</html>

<?php
exit;
}


/* =========================
   DATA SISWA
========================= */

$data = mysqli_query($koneksi, "
    SELECT *
    FROM t_siswa
    ORDER BY id ASC
");

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Data Siswa</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>


<body class="bg-light">


<div class="container py-5">


    <!-- HEADER -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h1 class="fw-bold mb-1">
                Data Siswa
            </h1>

            <p class="text-secondary mb-2">
                Daftar data siswa
            </p>

            <a
                href="dashboard.php"
                class="btn btn-secondary btn-sm"
            >
                Kembali ke Dashboard
            </a>

        </div>


        <a
            href="siswa.php?tambah=1"
            class="btn btn-primary"
        >
            + Tambah Siswa
        </a>

    </div>


    <!-- TABEL -->

    <div class="card border-0 shadow-sm">

        <div class="card-body p-3">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead class="table-primary">

                        <tr>

                            <th>No</th>
                            <th>NISN</th>
                            <th>NIP</th>
                            <th>Nama Siswa</th>
                            <th>Jenis Kelamin</th>
                            <th>Tanggal Lahir</th>
                            <th>Status</th>
                            <th>Aksi</th>

                        </tr>

                    </thead>


                    <tbody>

                    <?php

                    $no = 1;

                    while ($row = mysqli_fetch_assoc($data)):

                    ?>

                        <tr>

                            <td>
                                <?= $no++; ?>
                            </td>


                            <td>
                                <?= htmlspecialchars($row['nisn']); ?>
                            </td>


                            <td>
                                <?= htmlspecialchars($row['nip']); ?>
                            </td>


                            <td>
                                <strong>
                                    <?= htmlspecialchars($row['nama']); ?>
                                </strong>
                            </td>


                            <td>

                                <?php
                                if ($row['jenis_kelamin'] == 'L') {
                                    echo 'Laki-laki';
                                } else {
                                    echo 'Perempuan';
                                }
                                ?>

                            </td>


                            <td>
                                <?= htmlspecialchars($row['tanggal_lahir']); ?>
                            </td>


                            <td>

                                <?php if ($row['status_aktif'] == 1): ?>

                                    <span class="badge bg-success">
                                        Aktif
                                    </span>

                                <?php else: ?>

                                    <span class="badge bg-secondary">
                                        Tidak Aktif
                                    </span>

                                <?php endif; ?>

                            </td>


                            <td>

                                <a
                                    href="siswa.php?hapus=<?= $row['id']; ?>"
                                    class="btn btn-danger btn-sm"
                                    onclick="return confirm('Yakin ingin menghapus data siswa ini?')"
                                >
                                    Hapus
                                </a>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>


</div>


</body>

</html>