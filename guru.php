<?php
require_once __DIR__ . '/config/koneksi.php';


/* =========================
   HAPUS DATA
========================= */
if (isset($_GET['hapus'])) {

    $id = (int) $_GET['hapus'];

    $cek = mysqli_query(
        $koneksi,
        "SELECT user_id FROM t_guru WHERE id = $id"
    );

    $guru = mysqli_fetch_assoc($cek);

    if ($guru) {

        $user_id = (int) $guru['user_id'];

        mysqli_query(
            $koneksi,
            "DELETE FROM t_guru WHERE id = $id"
        );

        mysqli_query(
            $koneksi,
            "DELETE FROM t_user WHERE id = $user_id"
        );
    }

    header("Location: guru.php");
    exit;
}


/* =========================
   TAMBAH DATA
========================= */
if (isset($_POST['tambah'])) {

    $nip = mysqli_real_escape_string(
        $koneksi,
        $_POST['nip']
    );

    $nama = mysqli_real_escape_string(
        $koneksi,
        $_POST['nama']
    );

    $email = mysqli_real_escape_string(
        $koneksi,
        $_POST['email']
    );

    $status_aktif = (int) $_POST['status_aktif'];


    $password = password_hash(
        '12345678',
        PASSWORD_DEFAULT
    );


    mysqli_query($koneksi, "
        INSERT INTO t_user
        (name, email, password, remember_token, role)
        VALUES
        ('$nama', '$email', '$password', '', 'guru')
    ");


    $user_id = mysqli_insert_id($koneksi);


    mysqli_query($koneksi, "
        INSERT INTO t_guru
        (nip, nama, email, status_aktif, user_id)
        VALUES
        ('$nip', '$nama', '$email', '$status_aktif', '$user_id')
    ");


    header("Location: guru.php");
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

    <title>Tambah Data Guru</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>


<body class="bg-light">


<div class="container py-5">


    <div class="mb-4">

        <h1 class="fw-bold mb-1">
            Tambah Data Guru
        </h1>

        <p class="text-secondary mb-2">
            Masukkan informasi guru baru.
        </p>

        <a
            href="guru.php"
            class="btn btn-secondary btn-sm"
        >
            Kembali ke Data Guru
        </a>

    </div>


    <div class="card border-0 shadow-sm">

        <div class="card-body p-4">

            <form method="POST">

                <div class="row g-3">


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
                            Nama Guru
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
                            Email
                        </label>

                        <input
                            type="email"
                            name="email"
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

                </div>


                <div class="mt-4">

                    <a
                        href="guru.php"
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
   DATA GURU
========================= */

$data = mysqli_query($koneksi, "
    SELECT *
    FROM t_guru
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

    <title>Data Guru</title>

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
                Data Guru
            </h1>

            <p class="text-secondary mb-2">
                Daftar data guru
            </p>

            <a
                href="dashboard.php"
                class="btn btn-secondary btn-sm"
            >
                Kembali ke Dashboard
            </a>

        </div>


        <a
            href="guru.php?tambah=1"
            class="btn btn-primary"
        >
            + Tambah Guru
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
                            <th>NIP</th>
                            <th>Nama Guru</th>
                            <th>Email</th>
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
                                <?= htmlspecialchars($row['nip']); ?>
                            </td>


                            <td>
                                <strong>
                                    <?= htmlspecialchars($row['nama']); ?>
                                </strong>
                            </td>


                            <td>
                                <?= htmlspecialchars($row['email']); ?>
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
                                    href="guru.php?hapus=<?= $row['id']; ?>"
                                    class="btn btn-danger btn-sm"
                                    onclick="return confirm('Yakin ingin menghapus data guru ini?')"
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