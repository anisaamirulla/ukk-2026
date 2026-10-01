<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/koneksi.php';

if (isset($_POST['simpan'])) {

    $nama = mysqli_real_escape_string(
        $koneksi,
        $_POST['nama']
    );

    $tingkat = mysqli_real_escape_string(
        $koneksi,
        $_POST['tingkat']
    );

    $jurusan = mysqli_real_escape_string(
        $koneksi,
        $_POST['jurusan']
    );

    $query = mysqli_query(
        $koneksi,
        "INSERT INTO t_kelas
        (nama, tingkat, jurusan, status_aktif)
        VALUES
        ('$nama', '$tingkat', '$jurusan', 1)"
    );

    if ($query) {

        header("Location: kelas.php");
        exit;

    } else {

        $error = mysqli_error($koneksi);

    }

}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Tambah Kelas</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>

<body class="bg-light">

<div class="container py-5">

    <div class="card shadow-sm border-0">

        <div class="card-body p-4">

            <h2 class="fw-bold mb-1">
                Tambah Kelas
            </h2>

            <p class="text-muted mb-4">
                Tambahkan data kelas baru
            </p>


            <?php if (isset($error)): ?>

                <div class="alert alert-danger">
                    <?= htmlspecialchars($error) ?>
                </div>

            <?php endif; ?>


            <form method="POST">


                <!-- NAMA KELAS -->

                <div class="mb-3">

                    <label class="form-label">
                        Nama Kelas
                    </label>

                    <input
                        type="text"
                        name="nama"
                        class="form-control"
                        placeholder="Contoh: X RPL 4"
                        required
                    >

                </div>


                <!-- TINGKAT -->

                <div class="mb-3">

                    <label class="form-label">
                        Tingkat
                    </label>

                    <select
                        name="tingkat"
                        class="form-select"
                        required
                    >

                        <option value="">
                            -- Pilih Tingkat --
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


                <!-- JURUSAN -->

                <div class="mb-4">

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


                <!-- TOMBOL -->

                <div class="d-flex gap-2">

                    <a
                        href="kelas.php"
                        class="btn btn-secondary"
                    >
                        Kembali
                    </a>

                    <button
                        type="submit"
                        name="simpan"
                        class="btn btn-primary"
                    >
                        Simpan Kelas
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

</body>
</html>