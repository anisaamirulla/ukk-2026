<?php

session_start();

include "config/koneksi.php";

$email = $_POST['email'];
$password = $_POST['password'];

$query = mysqli_query(
    $koneksi,
    "SELECT * FROM t_user WHERE email='$email'"
);

$user = mysqli_fetch_assoc($query);

if (!$user) {
    echo "Email tidak ditemukan.";
    exit;
}

if (!password_verify($password, $user['password'])) {
    echo "Password salah.";
    exit;
}

$_SESSION['login'] = true;
$_SESSION['user_id'] = $user['id'];
$_SESSION['nama'] = $user['name'];
$_SESSION['role'] = $user['role'];

header("Location: dashboard.php");
exit;

?>