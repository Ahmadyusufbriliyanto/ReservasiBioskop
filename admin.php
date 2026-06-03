<?php
require 'koneksi.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header("Location: login.php"); exit; }

// Proses Hapus Film
if (isset($_GET['hapus'])) {
    $id = new MongoDB\BSON\ObjectId($_GET['hapus']);
    
    $film_dihapus = $film_collection->findOne(['_id' => $id]);
    if (isset($film_dihapus['Poster']) && file_exists($film_dihapus['Poster'])) {
        unlink($film_dihapus['Poster']); 
    }
    
    $film_collection->deleteOne(['_id' => $id]);
    echo "<script>alert('Jadwal film dihapus!'); window.location.href='admin.php';</script>";
}

// Data Array
$daftar_film = $film_collection->find()->toArray();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Cinema Premiere</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #0a0a0a; /* Hitam pekat minimalis */
            color: #e0e0e0;
        }
        
        /* Navbar Clean Minimalist */
        .navbar-premium {
            background-color: rgba(10, 10, 10, 0.95);
            border-bottom: 1px solid #222;
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .brand-text {
            color: #d4af37; /* Warna Champagne Gold */
            font-weight: 600;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            font-size: 1.2rem;
        }

        /* Card Elegan */
        .movie-card {
            background-color: #121212;
            border: 1px solid #2a2a2a;
            border-radius: 8px;
            transition: all 0.3s ease;
            overflow: hidden;
        }

        .movie-card:hover {
            transform: translateY(-8px);
            border-color: #d4af37;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.8);
        }

        .card-img-top {
            height: 360px;
            object-fit: cover;
            border-bottom: 1px solid #2a2a2a;
        }

        .card-body {
            padding: 1.25rem;
        }

        .card-title {
            color: #ffffff;
            font-weight: 500;
            font-size: 1.1rem;
            margin-bottom: 0.2rem;
        }

        /* Detail Teks */
        .movie-info {
            font-size: 0.85rem;
            color: #888;
        }
        
        .movie-info i {
            color: #d4af37;
        }

        /* Tombol Premium */
        .btn-gold {
            background-color: #d4af37;
            color: #0a0a0a;
            font-weight: 500;
            border: none;
            border-radius: 4px;
            transition: all 0.2s;
            font-size: 0.9rem;
            padding: 8px 20px;
        }
        
        .btn-gold:hover {
            background-color: #b5952f;
            color: #0a0a0a;
        }

        .btn-outline-gold {
            background-color: transparent;
            color: #d4af37;
            border: 1px solid #d4af37;
            font-weight: 500;
            border-radius: 4px;
            transition: all 0.2s;
            font-size: 0.9rem;
        }

        .btn-outline-gold:hover {
            background-color: #d4af37;
            color: #0a0a0a;
        }

        .btn-danger-minimal {
            background-color: transparent;
            color: #ff4444;
            border: 1px solid #333;
            font-weight: 500;
            border-radius: 4px;
            transition: all 0.2s;
            font-size: 0.9rem;
        }

        .btn-danger-minimal:hover {
            border-color: #ff4444;
            background-color: rgba(255, 68, 68, 0.1);
        }

        .badge-studio {
            background: rgba(10, 10, 10, 0.8);
            border: 1px solid #d4af37;
            color: #d4af37;
            font-weight: 400;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 0.75rem;
            backdrop-filter: blur(4px);
        }
    </style>
</head>
<body>

    <div class="navbar-premium mb-5">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="brand-text"><i class="bi bi-camera-reels me-2"></i>Cinema Premiere</div>
            <div class="d-flex align-items-center gap-4">
                <span style="font-size: 0.9rem; color: #888;">Admin: <span class="text-white"><?= $_SESSION['username'] ?></span></span>
                <a href="logout.php" class="btn btn-danger-minimal px-3 py-1">Keluar</a>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-4 pb-3" style="border-bottom: 1px solid #222;">
            <div>
                <h3 class="fw-light text-white mb-1">Jadwal Tayang</h3>
                <p class="text-muted small mb-0">Kelola daftar film dan jam tayang bioskop.</p>
            </div>
            <div class="d-flex gap-3">
                <a href="reservasi.php" class="btn btn-outline-light px-4">
                    <i class="bi bi-ticket-detailed me-1"></i> Kelola Reservasi
                </a>
                <a href="kelola_film.php" class="btn btn-gold px-4">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Film
                </a>
            </div>
        </div>

        <div class="row">
            <?php foreach ($daftar_film as $row): ?>
            <div class="col-6 col-md-3 mb-4">
                <div class="card movie-card h-100">
                    <div class="position-relative">
                        <img src="<?= htmlspecialchars($row['Poster'] ?? 'https://via.placeholder.com/300x400') ?>" class="card-img-top w-100" alt="Poster">
                        <span class="badge badge-studio position-absolute" style="top: 15px; left: 15px;">
                            <?= htmlspecialchars($row['Studio']) ?>
                        </span>
                    </div>
                    
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title text-truncate" title="<?= htmlspecialchars($row['Judul_Film']) ?>">
                            <?= htmlspecialchars($row['Judul_Film']) ?>
                        </h5>
                        
                        <p class="text-muted small mb-2" style="font-size: 0.8rem;">
                            <?= htmlspecialchars($row['Genre'] ?? 'Tanpa Genre') ?> • <?= htmlspecialchars($row['Durasi'] ?? 0) ?> Menit
                        </p>
                        
                        <div class="movie-info mt-1 mb-2">
                            <div class="mb-1"><i class="bi bi-calendar3 me-2"></i> <?= htmlspecialchars($row['Tanggal_Tayang']) ?></div>
                            <div><i class="bi bi-clock me-2"></i> <?= htmlspecialchars($row['Waktu_Tayang']) ?> WIB</div>
                        </div>

                        <h6 class="text-warning mb-3 mt-1">Rp <?= number_format($row['Harga'] ?? 0, 0, ',', '.') ?></h6>
                        
                        <div class="mt-auto pt-3" style="border-top: 1px solid #222;">
                            <div class="d-flex gap-2">
                                <a href="kelola_film.php?id=<?= $row['_id'] ?>" class="btn btn-outline-gold w-50 text-center">Edit</a>
                                <a href="admin.php?hapus=<?= $row['_id'] ?>" class="btn btn-danger-minimal w-50 text-center" onclick="return confirm('Hapus film ini?')">Hapus</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <?php if(empty($daftar_film)): ?>
        <div class="text-center py-5 mt-4" style="border: 1px dashed #333; border-radius: 8px;">
            <i class="bi bi-film fs-1 text-muted mb-3 d-block"></i>
            <h5 class="text-white fw-light">Tidak ada jadwal tayang</h5>
            <p class="text-muted small">Mulai tambahkan daftar film untuk ditampilkan ke customer.</p>
        </div>
        <?php endif; ?>

    </div>
</body>
</html>