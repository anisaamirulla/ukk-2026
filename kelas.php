<?php
require_once __DIR__ . '/config/koneksi.php';

/* =========================
   HAPUS DATA
========================= */
if (isset($_GET['hapus'])) {
    $id = (int) $_GET['hapus'];

    mysqli_query($koneksi, "DELETE FROM t_kelas WHERE id = $id");

    header("Location: kelas.php");
    exit;
}


/* =========================
   TAMBAH DATA
========================= */
if (isset($_POST['tambah'])) {

    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $tingkat = mysqli_real_escape_string($koneksi, $_POST['tingkat']);
    $jurusan = mysqli_real_escape_string($koneksi, $_POST['jurusan']);
    $status_aktif = (int) $_POST['status_aktif'];

    mysqli_query($koneksi, "
        INSERT INTO t_kelas
        (nama, tingkat, jurusan, status_aktif)
        VALUES
        ('$nama', '$tingkat', '$jurusan', '$status_aktif')
    ");

    header("Location: kelas.php");
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Tambah Data Kelas</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>

<body class="bg-light">

<div class="container py-5">

    <div class="mb-4">

        <h1 class="fw-bold mb-1">
            Tambah Data Kelas
        </h1>

        <p class="text-secondary mb-3">
            Masukkan informasi kelas baru.
        </p>

        <a href="kelas.php" class="btn btn-secondary btn-sm">
            Kembali ke Data Kelas
        </a>

    </div>


    <div class="card border-0 shadow-sm">

        <div class="card-body p-4">

            <form method="POST">

                <div class="row g-3">

                    <div class="col-md-6">

                        <label class="form-label">
                            Nama Kelas
                        </label>

                        <input
                            type="text"
                            name="nama"
                            class="form-control"
                            placeholder="Contoh: X RPL 1"
                            required
                        >

                    </div>


                    <div class="col-md-6">

                        <label class="form-label">
                            Tingkat
                        </label>

                        <select
                            name="tingkat"
                            class="form-select"
                            required
                        >

                            <option value="">
                                Pilih Tingkat
                            </option>

                            <option value="X">
                                X
                            </option>

                            <option value="XI">
                                XI
                            </option>

                            <option value="XII">
                                XII
                            </option>

                        </select>

                    </div>


                    <div class="col-md-6">

                        <label class="form-label">
                            Jurusan
                        </label>

                        <input
                            type="text"
                            name="jurusan"
                            class="form-control"
                            placeholder="Contoh: Rekayasa Perangkat Lunak"
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

                </div>


                <div class="mt-4">

                    <a
                        href="kelas.php"
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
   DATA KELAS
========================= */

$data = mysqli_query($koneksi, "
    SELECT *
    FROM t_kelas
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

    <title>Data Kelas</title>

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
                Data Kelas
            </h1>

            <p class="text-secondary mb-2">
                Daftar data kelas
            </p>

            <a
                href="dashboard.php"
                class="btn btn-secondary btn-sm"
            >
                Kembali ke Dashboard
            </a>

        </div>


        <a
            href="kelas.php?tambah=1"
            class="btn btn-primary"
        >
            + Tambah Kelas
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

                            <th>Nama Kelas</th>

                            <th>Tingkat</th>

                            <th>Jurusan</th>

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
                                <strong>
                                    <?= htmlspecialchars($row['nama']); ?>
                                </strong>
                            </td>


                            <td>
                                <?= htmlspecialchars($row['tingkat']); ?>
                            </td>


                            <td>
                                <?= htmlspecialchars($row['jurusan']); ?>
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
                                    href="kelas.php?hapus=<?= $row['id']; ?>"
                                    class="btn btn-danger btn-sm"
                                    onclick="return confirm('Yakin ingin menghapus data kelas ini?')"
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