<?php

include "config/koneksi.php";

$admin = password_hash("admin123", PASSWORD_DEFAULT);
$guru  = password_hash("guru123", PASSWORD_DEFAULT);


// Ubah akun Admin
$query_admin = mysqli_query($koneksi, "
    UPDATE t_users
    SET
        name = 'Admin',
        email = 'admin@gmail.com',
        password = '$admin',
        role = 'admin'
    WHERE id = 1
");


// Ubah akun Guru
$query_guru = mysqli_query($koneksi, "
    UPDATE t_users
    SET
        name = 'Guru',
        email = 'guru@gmail.com',
        password = '$guru',
        role = 'guru'
    WHERE id = 2
");


if (!$query_admin) {
    die("Gagal Admin: " . mysqli_error($koneksi));
}

if (!$query_guru) {
    die("Gagal Guru: " . mysqli_error($koneksi));
}


echo "BERHASIL!<br><br>";

echo "Admin<br>";
echo "Email: admin@gmail.com<br>";
echo "Password: admin123<br><br>";

echo "Guru<br>";
echo "Email: guru@gmail.com<br>";
echo "Password: guru123";

?>