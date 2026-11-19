
<?php
session_start();
require_once 'koneksi.php';

// Jika sudah login, langsung ke dashboard
if (isset($_SESSION['pengguna'])) {
    header("Location: index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $koneksi->prepare(
        "SELECT * FROM pengguna WHERE nama_pengguna = ? LIMIT 1"
    );
    $stmt->execute([$username]);

    $pengguna = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($pengguna && password_verify($password, $pengguna['kata_sandi'])) {
        session_regenerate_id(true);

        $_SESSION['pengguna'] = [
            'id' => $pengguna['id'],
            'nama' => $pengguna['nama'],
            'username' => $pengguna['nama_pengguna'],
            'peran' => $pengguna['peran']
        ];

        header("Location: index.php");
        exit;
    } else {
        $error = "Username atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SPK Laptop</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #f5f1f2;
        }

        .login-container {
            width: 380px;
            max-width: 90%;
            background: white;
            padding: 35px;
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.10);
        }

        h2 {
            text-align: center;
            color: #800020;
            margin-bottom: 8px;
        }

        .subtitle {
            text-align: center;
            color: #777;
            font-size: 13px;
            margin-bottom: 28px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 7px;
        }

        button {
            width: 100%;
            padding: 13px;
            border: none;
            border-radius: 7px;
            background: #800020;
            color: white;
            font-weight: bold;
            cursor: pointer;
        }

        button:hover {
            background: #600018;
        }

        .error {
            background: #ffe5e5;
            color: #a00000;
            padding: 11px;
            border-radius: 6px;
            margin-bottom: 18px;
            font-size: 13px;
        }
    </style>
</head>

<body>

<div class="login-container">

    <h2>SPK LAPTOP</h2>
    <p class="subtitle">Sistem Pendukung Keputusan Metode SAW</p>

    <?php if ($error): ?>
        <div class="error">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST">

        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username"
                   placeholder="Masukkan username" required>
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password"
                   placeholder="Masukkan password" required>
        </div>

        <button type="submit">Login</button>

    </form>

</div>

</body>
</html>
