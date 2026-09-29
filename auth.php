<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function harus_login()
{
    if (!isset($_SESSION['login'])) {
        header("Location: ../login.php");
        exit;
    }
}

function harus_admin()
{
    harus_login();

    if ($_SESSION['role'] != 'admin') {
        echo "Anda tidak memiliki akses";
        exit;
    }
}

function harus_admin_guru()
{
    harus_login();

    if ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'guru') {
        echo "Anda tidak memiliki akses";
        exit;
    }
}

?>