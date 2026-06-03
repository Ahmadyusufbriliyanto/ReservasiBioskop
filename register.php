<?php
require 'koneksi.php';

if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') header("Location: admin.php");
    else header("Location: customer.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $konfirmasi = $_POST['konfirmasi_password'];
    $role = 'customer'; 

    // PAKAI VARIABEL $users_collection DARI koneksi.php
    $cek_user = $users_collection->findOne(['username' => $username]);

    if ($cek_user) {
        $error = "Username sudah digunakan, pilih yang lain!";
    } elseif ($password !== $konfirmasi) {
        $error = "Password dan Konfirmasi Password tidak cocok!";
    } else {
        // PAKAI VARIABEL $users_collection DARI koneksi.php
        $users_collection->insertOne([
            'username' => $username,
            'password' => $password, 
            'role' => $role
        ]);
        $sukses = "Akun berhasil dibuat! Silakan login.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Cinema Premiere</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Poppins', sans-serif; 
            background: linear-gradient(rgba(10, 10, 10, 0.8), rgba(10, 10, 10, 0.95)), url('https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?q=80&w=2070&auto=format&fit=crop') center/cover no-repeat;
            background-attachment: fixed;
            color: #e0e0e0; 
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-card { 
            background-color: rgba(18, 18, 18, 0.85); 
            backdrop-filter: blur(15px);
            border: 1px solid #333; 
            border-top: 4px solid #d4af37;
            border-radius: 15px; 
            box-shadow: 0 15px 35px rgba(0,0,0,0.5);
            padding: 3rem 2rem;
        }
        .brand-logo { color: #d4af37; font-size: 2.5rem; margin-bottom: 0.5rem; }
        .form-control { background-color: rgba(26, 26, 26, 0.8) !important; border: 1px solid #333 !important; color: #fff !important; padding: 0.8rem 1rem; border-radius: 8px;}
        .form-control:focus { border-color: #d4af37 !important; box-shadow: 0 0 0 0.2rem rgba(212, 175, 55, 0.25); }
        .form-control::placeholder { color: #666; }
        .input-group-text { background-color: rgba(26, 26, 26, 0.8); border: 1px solid #333; color: #d4af37; border-radius: 8px 0 0 8px; }
        .btn-gold { background-color: #d4af37; color: #0a0a0a; font-weight: 600; border: none; padding: 12px; border-radius: 8px; transition: 0.3s; letter-spacing: 1px;}
        .btn-gold:hover { background-color: #b5952f; color: #0a0a0a; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(212, 175, 55, 0.3);}
        .link-gold { color: #d4af37; text-decoration: none; transition: 0.3s; }
        .link-gold:hover { color: #fff; text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center py-5">
            <div class="col-12 col-md-8 col-lg-5">
                <div class="login-card text-center">
                    <i class="bi bi-person-plus brand-logo"></i>
                    <h4 class="fw-bold text-white mb-1">Daftar Akun Baru</h4>
                    <p class="text-muted small mb-4">Bergabunglah untuk pengalaman menonton terbaik</p>

                    <?php if(isset($error)): ?>
                        <div class="alert alert-danger py-2 small border-0" style="background-color: rgba(220,53,69,0.1); color: #ff4444;">
                            <i class="bi bi-exclamation-triangle me-1"></i> <?= $error ?>
                        </div>
                    <?php endif; ?>

                    <?php if(isset($sukses)): ?>
                        <div class="alert alert-success py-2 small border-0" style="background-color: rgba(40,167,69,0.1); color: #28a745;">
                            <i class="bi bi-check-circle me-1"></i> <?= $sukses ?> 
                            <a href="login.php" class="alert-link ms-1">Login di sini</a>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" class="text-start">
                        <div class="mb-3">
                            <label class="form-label text-secondary small mb-1">Username</label>
                            <div class="input-group">
                                <span class="input-group-text border-end-0"><i class="bi bi-person"></i></span>
                                <input type="text" name="username" class="form-control border-start-0" placeholder="Buat username..." required autofocus>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-secondary small mb-1">Password</label>
                            <div class="input-group">
                                <span class="input-group-text border-end-0"><i class="bi bi-lock"></i></span>
                                <input type="password" name="password" class="form-control border-start-0" placeholder="Buat password..." required>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label text-secondary small mb-1">Konfirmasi Password</label>
                            <div class="input-group">
                                <span class="input-group-text border-end-0"><i class="bi bi-check-circle"></i></span>
                                <input type="password" name="konfirmasi_password" class="form-control border-start-0" placeholder="Ketik ulang password..." required>
                            </div>
                        </div>
                        
                        <div class="d-grid mb-4">
                            <button type="submit" class="btn btn-gold"><i class="bi bi-person-plus-fill me-2"></i> BUAT AKUN</button>
                        </div>
                        
                        <p class="text-center text-secondary small mb-0">
                            Sudah punya akun? <a href="login.php" class="link-gold fw-medium">Masuk di sini</a>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>