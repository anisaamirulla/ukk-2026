
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Sistem Pelanggaran Siswa</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #1e1e20;
            font-family: Arial, Helvetica, sans-serif;
            position: relative;
        }

        /* Latar cokelat */
        body::before {
            content: "";
            position: fixed;
            top: 22px;
            left: 0;
            right: 12px;
            bottom: 43px;
            background: #5b422f;
        }

        /* Kotak login */
        .login-card {
            position: relative;
            z-index: 1;
            width: 360px;
            min-height: 420px;
            border: none;
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }

        .login-card .card-body {
            padding: 24px 28px 30px;
        }

        /* Judul tanpa gambar */
        .logo-login {
            height: 90px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .logo-login span {
            font-size: 30px;
            font-weight: 800;
            letter-spacing: 2px;
            color: #5b422f;
        }

        /* Label input */
        .login-card .form-label {
            display: block;
            margin: 0 8px 7px;
            font-size: 14px;
            font-weight: 700;
            color: #222;
        }

        /* Input */
        .login-card .form-control {
            height: 48px;
            padding: 12px 15px;
            border: none;
            border-radius: 12px;
            background: #dedede;
            font-size: 14px;
            font-weight: 500;
            box-shadow: none;
        }

        .login-card .form-control:focus {
            background: #dedede;
            box-shadow: 0 0 0 2px #c6b6a3;
        }

        /* Tombol login */
        .btn-login {
            height: 48px;
            margin-top: 10px;
            border: none;
            border-radius: 12px;
            background: #e5d9c8;
            color: #5b422f;
            font-size: 15px;
            font-weight: 800;
        }

        .btn-login:hover,
        .btn-login:focus {
            background: #d6c5ae;
            color: #493424;
        }

        @media (max-width: 400px) {
            .login-card {
                width: calc(100% - 32px);
                max-width: 360px;
            }

            body::before {
                top: 12px;
                right: 8px;
                bottom: 20px;
            }
        }
    </style>
</head>

<body>

    <div class="card login-card">
        <div class="card-body">

            <!-- Judul login tanpa gambar -->
            <div class="logo-login mb-3">
                <span>LOGIN</span>
            </div>

            <!-- Form login -->
            <form action="proses_login.php" method="POST">

                <div class="mb-4">
                    <label for="email" class="form-label">
                        Username / Email
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        id="email"
                        name="email"
                        placeholder="Masukkan username atau email"
                        autocomplete="username"
                        required
                    >
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">
                        Password
                    </label>

                    <input
                        type="password"
                        class="form-control"
                        id="password"
                        name="password"
                        placeholder="Masukkan password"
                        autocomplete="current-password"
                        required
                    >
                </div>

                <button type="submit" class="btn btn-login w-100">
                    LOGIN
                </button>

            </form>

        </div>
    </div>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>