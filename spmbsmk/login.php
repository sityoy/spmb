<?php
session_start();
include 'koneksi.php';

if (isset($_SESSION['login'])) {
    header("Location: admin.php");
    exit;
}

$pesan = "";
if (isset($_POST['masuk'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    // Jalur Darurat Aktif
    if ($username === "Admin2" && $password === "SMKPB@#1") {
        $_SESSION['login'] = true;
        $_SESSION['admin_user'] = "admin";
        header("Location: admin.php");
        exit;
    } else {
        $pesan = "<div class='alert-danger'>Username atau Password Salah!</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Pengaman SPMB</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f3f4f6; display: flex; height: 100vh; align-items: center; justify-content: center; margin: 0; padding: 15px; box-sizing: border-box; }
        .card { background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); width: 100%; max-width: 360px; }
        h3 { text-align: center; margin: 0 0 6px 0; color: #4f46e5; }
        p { text-align: center; margin: 0 0 20px 0; font-size: 13px; color: #6b7280; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 6px; font-weight: 600; font-size: 13px; }
        .form-group input { width: 100%; padding: 10px; box-sizing: border-box; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; }
        .btn { width: 100%; padding: 10px; background: #4f46e5; color: white; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 15px; margin-top: 10px; }
        .btn:hover { background: #4338ca; }
        .alert-danger { background: #fde8e8; color: #9b1c1c; padding: 10px; border-radius: 6px; font-size: 13px; text-align: center; margin-bottom: 15px; border: 1px solid #f8b4b4; }
    </style>
</head>
<body>

<div class="card">
    <h3>Sistem Pengaman</h3>
    <p>PANITIA SPMB SMKS PERMATA BUNDA I</p>
    
    <?php if ($pesan != "") echo $pesan; ?>

    <form action="" method="POST">
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" required placeholder="Masukkan username">
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required placeholder="Masukkan password">
        </div>
        <button type="submit" name="masuk" class="btn">Masuk Ke Sistem</button>
    </form>
</div>

</body>
</html>