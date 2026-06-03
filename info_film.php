<?php
require 'koneksi.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'customer') { header("Location: login.php"); exit; }

if (!isset($_GET['id_film'])) { header("Location: customer.php"); exit; }

$id_film = new MongoDB\BSON\ObjectId($_GET['id_film']);
$film = $film_collection->findOne(['_id' => $id_film]);

if (!$film) { 
    echo "<script>alert('Film tidak ditemukan!'); window.location.href='customer.php';</script>"; 
    exit; 
}

// Hitung Sisa Kursi
$terjual = $collection->countDocuments(['Judul_Film' => $film['Judul_Film']]);
$kapasitas = isset($film['Kapasitas']) ? (int)$film['Kapasitas'] : 0;
$is_full = ($terjual >= $kapasitas);

// Fallback Data
$genre = !empty($film['Genre']) ? $film['Genre'] : '-';
$durasi = !empty($film['Durasi']) ? $film['Durasi'] : 0;
$sutradara = !empty($film['Sutradara']) ? $film['Sutradara'] : 'Unknown';
$studio = !empty($film['Studio_Produksi']) ? $film['Studio_Produksi'] : 'Unknown';
$harga = !empty($film['Harga']) ? $film['Harga'] : 0;
$sinopsis = !empty($film['Sinopsis']) ? $film['Sinopsis'] : 'Tidak ada sinopsis tersedia untuk film ini.';
$rating_usia = !empty($film['Rating_Usia']) ? $film['Rating_Usia'] : 'PG-13'; 
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($film['Judul_Film']) ?> - Cinema Premiere</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #0a0a0a; color: #e0e0e0; }
        .navbar-premium { background-color: rgba(10, 10, 10, 0.95); border-bottom: 1px solid #222; padding: 1rem 0; position: sticky; top: 0; z-index: 1000; backdrop-filter: blur(10px); }
        .brand-text { color: #d4af37; font-weight: 600; letter-spacing: 1.5px; text-transform: uppercase; font-size: 1.2rem; }
        .nav-link-custom { color: #888; text-decoration: none; font-weight: 500; transition: 0.3s; }
        .nav-link-custom:hover { color: #d4af37; }
        
        .poster-container { background-color: #121212; border: 1px solid #2a2a2a; border-radius: 15px; padding: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        .poster-img { width: 100%; border-radius: 10px; object-fit: cover; }
        
        .badge-outline-gold { border: 1px solid #d4af37; color: #d4af37; background: transparent; padding: 6px 12px; font-weight: 500; border-radius: 20px;}
        .badge-outline-gray { border: 1px solid #444; color: #ccc; background: transparent; padding: 6px 12px; font-weight: 500; border-radius: 20px;}
        .badge-outline-green { border: 1px solid #198754; color: #20c997; background: rgba(25, 135, 84, 0.1); padding: 6px 12px; font-weight: 600; border-radius: 20px;}
        
        .movie-title { font-weight: 700; font-size: 2.5rem; color: #fff; text-transform: uppercase; letter-spacing: 1px; margin-top: 15px;}
        .rating-number { font-size: 3.5rem; font-weight: 600; color: #d4af37; line-height: 1;}
        .star-icons { color: #d4af37; font-size: 1.2rem;}
        
        .btn-gold { background-color: #d4af37; color: #0a0a0a; font-weight: 600; border: none; padding: 12px 30px; border-radius: 8px; font-size: 1.1rem; transition: 0.3s;}
        .btn-gold:hover { background-color: #b5952f; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(212, 175, 55, 0.3);}
        .btn-secondary-custom { background-color: #333; color: #888; font-weight: 600; padding: 12px 30px; border-radius: 8px; border: none;}

        @media (max-width: 768px) {
            .movie-title { font-size: 1.8rem; }
        }
    </style>
</head>
<body>

    <div class="navbar-premium mb-4 mb-md-5">
        <div class="container d-flex justify-content-between align-items-center">
            <a href="customer.php" class="nav-link-custom"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
            <div class="brand-text d-none d-md-block"><i class="bi bi-camera-reels me-2"></i>Cinema Premiere</div>
            <a href="my_tickets.php" class="nav-link-custom"><i class="bi bi-ticket-perforated me-1"></i> Tiket Saya</a>
        </div>
    </div>

    <div class="container pb-5">
        <div class="row g-4 g-lg-5">
            <div class="col-md-4 col-lg-3">
                <div class="poster-container">
                    <img src="<?= htmlspecialchars($film['Poster'] ?? 'https://via.placeholder.com/300x400') ?>" class="poster-img" alt="Poster Film">
                    <div class="mt-3 text-center">
                        <span class="badge badge-outline-gray d-block mb-2"><?= htmlspecialchars($film['Studio']) ?></span>
                        <small class="text-muted d-block">Sisa Kursi: <strong class="<?= $is_full ? 'text-danger' : 'text-success' ?>"><?= $kapasitas - $terjual ?> / <?= $kapasitas ?></strong></small>
                    </div>
                </div>
            </div>

            <div class="col-md-8 col-lg-9">
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <span class="badge badge-outline-gold"><?= htmlspecialchars($genre) ?></span>
                    <span class="badge badge-outline-gray"><i class="bi bi-alarm"></i> <?= htmlspecialchars($durasi) ?> menit</span>
                    <span class="badge badge-outline-gray"><i class="bi bi-person-video2"></i> <?= htmlspecialchars($rating_usia) ?></span>
                    <span class="badge badge-outline-green">Now Playing</span>
                </div>

                <h1 class="movie-title"><?= htmlspecialchars($film['Judul_Film']) ?></h1>
                <p class="text-secondary" style="font-size: 1.1rem;">Sutradara: <strong class="text-light"><?= htmlspecialchars($sutradara) ?></strong></p>

                <div class="d-flex align-items-center gap-3 my-4">
                    <div class="rating-number">5.0</div>
                    <div>
                        <div class="star-icons mb-1">
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                        </div>
                        <span class="text-muted small">Rating penonton</span>
                    </div>
                </div>

                <div class="mb-4 mb-md-5">
                    <p class="text-light" style="line-height: 1.6;"><?= nl2br(htmlspecialchars($sinopsis)) ?></p>
                </div>

                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between p-4 rounded" style="background-color: #121212; border: 1px solid #2a2a2a;">
                    <div class="mb-3 mb-md-0">
                        <span class="text-muted small text-uppercase" style="letter-spacing: 1px;">Harga Tiket</span>
                        <div class="d-flex align-items-baseline gap-2">
                            <h2 class="text-info fw-bold mb-0">Rp <?= number_format($harga, 0, ',', '.') ?></h2>
                            <span class="text-secondary small">per kursi</span>
                        </div>
                        <div class="text-warning mt-1 small"><i class="bi bi-calendar-event"></i> Tayang: <?= htmlspecialchars($film['Tanggal_Tayang']) ?> (<?= htmlspecialchars($film['Waktu_Tayang']) ?> WIB)</div>
                    </div>
                    
                    <div>
                        <?php if ($is_full): ?>
                            <button class="btn btn-secondary-custom w-100" disabled><i class="bi bi-x-circle me-2"></i>Tiket Habis</button>
                        <?php else: ?>
                            <a href="pesan.php?id_film=<?= $id_film ?>" class="btn btn-gold w-100"><i class="bi bi-ticket-detailed me-2"></i>Pesan Kursi Sekarang</a>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>

</body>
</html>