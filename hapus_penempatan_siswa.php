
<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

if (strtolower(trim($_SESSION['role'] ?? '')) !== 'admin') {
    exit("Anda tidak memiliki akses");
}

if (!isset($_SESSION['data_penempatan'])) {
    header("Location: penempatan_siswa.php?pesan=gagal_hapus");
    exit;
}

$id_hapus = (int)($_GET['id'] ?? 0);
$ditemukan = false;

foreach ($_SESSION['data_penempatan'] as $index => $row) {
    if ((int)$row['id'] === $id_hapus) {
        unset($_SESSION['data_penempatan'][$index]);
        $ditemukan = true;
        break;
    }
}

if ($ditemukan) {
    $_SESSION['data_penempatan'] = array_values($_SESSION['data_penempatan']);
    header("Location: penempatan_siswa.php?pesan=hapus");
    exit;
}

header("Location: penempatan_siswa.php?pesan=gagal_hapus");
exit;
