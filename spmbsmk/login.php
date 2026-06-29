<?php
session_start();
require_once __DIR__ . '/config/koneksi.php';

if (isset($_SESSION['login'])) {
header("Location: ../admin/admin.php");
exit;
}

$pesan = "";
if (isset($_POST['masuk'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Menyesuaikan query ke tabel 'pengguna'
    $stmt = $conn->prepare("SELECT id, password FROM pengguna WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // Verifikasi hash password
        if (password_verify($password, $row['password'])) {
            $_SESSION['login'] = true;
            $_SESSION['admin_user'] = $username;
            
            header("Location: admin.php");
            exit;
        } else {
            $pesan = "<div class='alert-danger'>Password salah!</div>";
        }
    } else {
        $pesan = "<div class='alert-danger'>Username tidak terdaftar!</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Pengaman SPMB</title>
    <link rel="icon" type="image/x-icon" href="logo/logosmkpb.png">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f3f4f6; display: flex; height: 100vh; align-items: center; justify-content: center; margin: 0; padding: 15px; box-sizing: border-box; flex-direction: column; gap: 10px; }
        .card { background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); width: 100%; max-width: 360px; box-sizing: border-box; }
        h3 { text-align: center; margin: 0 0 6px 0; color: #4f46e5; }
        p { text-align: center; margin: 0 0 20px 0; font-size: 13px; color: #6b7280; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 6px; font-weight: 600; font-size: 13px; }
        .form-group input[type="text"], .form-group input[type="password"] { width: 100%; padding: 10px; box-sizing: border-box; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; }
        
        /* Style Tambahan untuk Checkbox Lihat Password */
        .show-password-wrapper { display: flex; align-items: center; gap: 6px; margin-top: 8px; }
        .show-password-wrapper input[type="checkbox"] { width: auto; margin: 0; cursor: pointer; transform: scale(1.1); }
        .show-password-wrapper label { margin: 0; font-size: 12.5px; color: #4b5563; font-weight: 500; cursor: pointer; user-select: none; }
        
        .btn { width: 100%; padding: 10px; background: #4f46e5; color: white; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 15px; margin-top: 10px; transition: 0.2s; }
        .btn:hover { background: #4338ca; }
        .btn-kembali { background: #6b7280; margin-top: 5px; text-decoration: none; display: block; text-align: center; color: white; padding: 10px; border-radius: 6px; font-weight: bold; font-size: 15px; box-sizing: border-box; }
        .btn-kembali:hover { background: #4b5563; }
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
            <input type="text" name="username" required placeholder="Masukkan username" autocomplete="off">
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" id="input-password" required placeholder="Masukkan password">
            
            <div class="show-password-wrapper">
                <input type="checkbox" id="check-show-password">
                <label for="check-show-password">Lihat Password</label>
            </div>
        </div>
        <button type="submit" name="masuk" class="btn">Masuk Ke Sistem</button>
    </form>
    
    <a href="../index.php" class="btn-kembali">Kembali</a>
</div>

<script>
    const passwordField = document.getElementById('input-password');
    const toggleCheckbox = document.getElementById('check-show-password');

    toggleCheckbox.addEventListener('change', function() {
        if (this.checked) {
            passwordField.type = 'text'; // Mengubah input menjadi teks biasa agar terlihat
        } else {
            passwordField.type = 'password'; // Mengembalikan menjadi bintang/bullet kembali
        }
    });
</script>

</body>
</html>