<?php

session_start();
include "config/koneksi.php";

$email = $_POST['email'];
$password = $_POST['password'];

$query = mysqli_query($koneksi, "SELECT * FROM t_user WHERE email='$email' AND password='$password'");

$data = mysqli_fetch_assoc($query);

if ($data) {

    $_SESSION['id'] = $data['id'];
    $_SESSION['name'] = $data['name'];
    $_SESSION['role'] = $data['role'];

    header("Location: dashboard.php");
    exit;

} else {

    echo "Email atau password salah";

}

?>