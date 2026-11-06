<?php
require_once __DIR__ . '/config/koneksi.php';

$email = 'user32@ukk2026.sch.id';
$passwordBaru = 'AldiRPL2026!';

$hash = password_hash($passwordBaru, PASSWORD_DEFAULT);

$stmt = mysqli_prepare(
    $koneksi,
    "UPDATE t_user SET password = ? WHERE email = ?"
);

mysqli_stmt_bind_param($stmt, "ss", $hash, $email);

if (mysqli_stmt_execute($stmt)) {
    if (mysqli_stmt_affected_rows($stmt) >= 0) {
        echo "Password Aldi Maulana berhasil diatur.";
    }
} else {
    echo "Gagal mengatur password.";
}

mysqli_stmt_close($stmt);
?>