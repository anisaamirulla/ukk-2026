
<?php

session_start();
include "config/koneksi.php";

// Memastikan form login dikirim
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit;
}

// Mengambil data dari form
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Memeriksa apakah email dan password kosong
if ($email === '' || $password === '') {
    header("Location: login.php?error=1");
    exit;
}

// Mencari akun berdasarkan email
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT * FROM t_user WHERE email = ? LIMIT 1"
);

if (!$stmt) {
    die("Error database: " . mysqli_error($koneksi));
}

mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);

$query = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($query);

mysqli_stmt_close($stmt);

// Memeriksa akun
if ($data) {

    $password_database = $data['password'];

    // Memeriksa password yang sudah berbentuk hash
    $password_benar = password_verify(
        $password,
        $password_database
    );

    // Memeriksa password lama yang masih berupa teks biasa
    if (
        !$password_benar &&
        hash_equals((string)$password_database, (string)$password)
    ) {

        $password_benar = true;

        // Mengubah password lama menjadi hash
        $password_baru = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        $update = mysqli_prepare(
            $koneksi,
            "UPDATE t_user SET password = ? WHERE id = ?"
        );

        if ($update) {
            mysqli_stmt_bind_param(
                $update,
                "si",
                $password_baru,
                $data['id']
            );

            mysqli_stmt_execute($update);
            mysqli_stmt_close($update);
        }
    }

    // Jika password benar
    if ($password_benar) {

        session_regenerate_id(true);

        $_SESSION['id'] = $data['id'];
        $_SESSION['name'] = $data['name'];
        $_SESSION['role'] = strtolower(trim($data['role']));

        // Mengarahkan berdasarkan role
        if ($_SESSION['role'] === 'admin') {

            header("Location: dashboard.php");
            exit;

        } elseif ($_SESSION['role'] === 'guru') {

            header("Location: dashboard.php");
            exit;

        } else {

            session_unset();
            session_destroy();

            header("Location: login.php?error=1");
            exit;
        }

    } else {

        header("Location: login.php?error=1");
        exit;
    }

} else {

    header("Location: login.php?error=1");
    exit;
}

?>
