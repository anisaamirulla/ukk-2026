<?php

session_start();

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

?>

<!DOCTYPE html>
<html>

<head>
    <title>Dashboard</title>
</head>

<body>

<h1>Dashboard</h1>

<p>
    Selamat datang,
    <?php echo $_SESSION['nama']; ?>
</p>

<p>
    Role:
    <?php echo $_SESSION['role']; ?>
</p>

<hr>

<?php if ($_SESSION['role'] == 'admin') { ?>

    <h3>Menu Admin</h3>

    <a href="admin/siswa.php">Kelola Siswa</a><br>
    <a href="admin/kelas.php">Kelola Kelas</a><br>
    <a href="admin/kategori_pelanggaran.php">
        Kelola Kategori Pelanggaran
    </a><br>
    <a href="admin/pengguna.php">Kelola Pengguna</a><br>
    <a href="admin/wali_kelas.php">Kelola Wali Kelas</a><br>

<?php } ?>


<?php if ($_SESSION['role'] == 'guru') { ?>

    <h3>Menu Guru</h3>

    <a href="guru/catat_pelanggaran.php">
        Catat Pelanggaran
    </a><br>

    <a href="guru/statistik.php">
        Statistik
    </a><br>

    <a href="guru/laporan.php">
        Laporan
    </a><br>

<?php } ?>


<br>

<a href="logout.php">Logout</a>

</body>

</html>