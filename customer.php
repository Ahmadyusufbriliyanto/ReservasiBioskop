<?php
require 'koneksi.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'customer') { header("Location: login.php"); exit; }

// --- LOGIKA FILTER & PENCARIAN ---
$filter = []; 
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$genre_filter = isset($_GET['genre']) ? $_GET['genre'] : '';

if ($search_query !== '') {
    $filter['Judul_Film'] = new MongoDB\BSON\Regex($search_query, 'i');
}
if ($genre_filter !== '') {
    $filter['Genre'] = $genre_filter;
}

$daftar_film = $film_collection->find($filter)->toArray();
$daftar_genre = ['Action', 'Adventure', 'Animation', 'Comedy', 'Drama', 'Fantasy', 'Horror', 'Romance', 'Sci-Fi', 'Thriller'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    
    <title>Customer Dashboard - Cinema Premiere</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        html { scroll-behavior: smooth; }
        body { font-family: 'Poppins', sans-serif; background-color: #0a0a0a; color: #e0e0e0; }
        
        /* Navbar Responsive */
        .navbar-premium { background-color: rgba(10, 10, 10, 0.95); border-bottom: 1px solid #222; padding: 1rem 0; position: sticky; top: 0; z-index: 1000; backdrop-filter: blur(10px); }
        .brand-text { color: #d4af37; font-weight: 600; letter-spacing: 1.5px; text-transform: uppercase; font-size: 1.2rem; }
        .nav-link-custom { color: #888; text-decoration: none; font-weight: 500; transition: 0.3s; }
        .nav-link-custom:hover, .nav-link-custom.active { color: #d4af37; }
        
        /* Hero Header */
        .hero-section {
            background: linear-gradient(to bottom, rgba(10,10,10,0.3), rgba(10,10,10,1)), url('https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?q=80&w=2070&auto=format&fit=crop') center/cover;
            padding: 80px 15px; 
            margin-bottom: 3rem;
            border-bottom: 1px solid #222;
        }
        
        /* Input & Filter Style */
        .form-control, .form-select { background-color: #1a1a1a !important; border: 1px solid #333 !important; color: #fff !important; padding: 0.8rem; }
        .form-control:focus, .form-select:focus { border-color: #d4af37 !important; box-shadow: 0 0 0 0.2rem rgba(212, 175, 55, 0.25); }
        .form-control::placeholder { color: #666; }
        
        /* Movie Card */
        .movie-card { background-color: #121212; border: 1px solid #2a2a2a; border-radius: 12px; transition: all 0.3s ease; overflow: hidden; }
        .movie-card:hover { transform: translateY(-8px); border-color: #d4af37; box-shadow: 0 10px 25px rgba(212, 175, 55, 0.15); }
        .card-img-top { height: 360px; object-fit: cover; border-bottom: 1px solid #2a2a2a; }
        .badge-custom { background-color: rgba(212, 175, 55, 0.1); color: #d4af37; border: 1px solid #d4af37; font-weight: 500; }
        .badge-dark-custom { background-color: #1a1a1a; color: #ccc; border: 1px solid #333; font-weight: 500; }
        
        /* Buttons */
        .btn-gold { background-color: #d4af37; color: #0a0a0a; font-weight: 600; border: none; padding: 10px; width: 100%; transition: 0.3s; border-radius: 8px;}
        .btn-gold:hover { background-color: #b5952f; color: #0a0a0a; }
        .btn-gold-rounded { background-color: #d4af37; color: #0a0a0a; font-weight: 600; border: none; padding: 12px 30px; border-radius: 30px; transition: 0.3s; text-decoration: none; display: inline-block; }
        .btn-gold-rounded:hover { background-color: #b5952f; color: #0a0a0a; transform: scale(1.05); }
        
        /* Footer */
        .footer-premium { background-color: #050505; border-top: 1px solid #1a1a1a; margin-top: 5rem; }
        .footer-link { color: #888; text-decoration: none; transition: 0.3s; display: block; margin-bottom: 8px; }
        .footer-link:hover { color: #d4af37; padding-left: 5px; }
        .social-icon { color: #888; font-size: 1.2rem; margin-right: 15px; transition: 0.3s; }
        .social-icon:hover { color: #d4af37; transform: translateY(-3px); display: inline-block; }

        /* Mobile Styling (HP) */
        @media (max-width: 768px) {
            .hero-title { font-size: 2rem !important; }
            .card-img-top { height: 220px; } 
            .card-body { padding: 0.8rem; }
            .card-title { font-size: 0.95rem; }
            .movie-info-text { font-size: 0.75rem !important; }
            .movie-detail-text { font-size: 0.7rem !important; }
            .movie-price { font-size: 0.9rem !important; }
            .badge { font-size: 0.65rem; padding: 0.25em 0.4em; }
            .btn-gold { padding: 6px; font-size: 0.85rem; }
        }
    </style>
</head>
<body>
    
    <div class="navbar-premium">
        <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
            <div class="brand-text text-center"><i class="bi bi-camera-reels me-2"></i>Cinema Premiere</div>
            <div class="d-flex flex-wrap justify-content-center align-items-center gap-3">
                <a href="customer.php" class="nav-link-custom active"><i class="bi bi-film"></i> Sedang Tayang</a>
                <a href="my_tickets.php" class="nav-link-custom"><i class="bi bi-ticket-perforated"></i> Tiket Saya</a>
                <span class="d-none d-md-inline" style="font-size: 0.9rem; color: #333;">|</span>
                <span style="font-size: 0.9rem; color: #888;">Halo, <span class="text-white fw-medium"><?= htmlspecialchars($_SESSION['username']) ?></span></span>
                <a href="logout.php" class="btn btn-outline-danger btn-sm px-3" style="border-radius: 20px;">Logout</a>
            </div>
        </div>
    </div>

    <header class="hero-section text-center text-light">
        <div class="container">
            <h1 class="fw-bold mb-3 hero-title" style="color: #d4af37; letter-spacing: 2px; font-size: 3.5rem;">Pengalaman Menonton Kelas Satu</h1>
            <p class="lead mb-4 fw-light text-light mx-auto" style="max-width: 600px; font-size: 1rem;">
                Nikmati kenyamanan maksimal dan kualitas visual terbaik. Pilih kursi favoritmu dan jadilah bagian dari cerita epik hari ini.
            </p>
            <a href="#katalog" class="btn-gold-rounded shadow-lg">
                <i class="bi bi-ticket-detailed me-2"></i>Pesan Tiket Sekarang
            </a>
        </div>
    </header>

    <div class="container" id="katalog">
        
        <div class="row mb-4 align-items-center text-center text-md-start">
            <div class="col-md-4 mb-3 mb-md-0">
                <h4 class="text-white mb-0 fw-light"><i class="bi bi-play-circle me-2 text-warning"></i>Sedang Tayang</h4>
            </div>
            <div class="col-md-8">
                <form method="GET" action="customer.php">
                    <div class="row g-2">
                        <div class="col-12 col-md-5">
                            <input type="text" name="search" class="form-control" placeholder="Cari judul film..." value="<?= htmlspecialchars($search_query) ?>">
                        </div>
                        <div class="col-12 col-md-4">
                            <select name="genre" class="form-select">
                                <option value="">Semua Genre</option>
                                <?php foreach ($daftar_genre as $g): ?>
                                    <option value="<?= $g ?>" <?= ($genre_filter == $g) ? 'selected' : '' ?>><?= $g ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-3 d-flex gap-2">
                            <button type="submit" class="btn btn-gold flex-grow-1"><i class="bi bi-search"></i> Cari</button>
                            <?php if($search_query !== '' || $genre_filter !== ''): ?>
                                <a href="customer.php" class="btn btn-outline-secondary" title="Reset Filter" style="border-radius: 8px;"><i class="bi bi-arrow-counterclockwise"></i></a>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="row mb-5">
            <?php if(empty($daftar_film)): ?>
                <div class="col-12 text-center py-5 mt-3" style="border: 1px dashed #333; border-radius: 12px; background-color: #121212;">
                    <i class="bi bi-search display-1 text-muted mb-3 d-block"></i>
                    <h5 class="text-white fw-light">Film tidak ditemukan</h5>
                    <p class="text-muted small">Coba gunakan kata kunci atau pilih genre yang berbeda.</p>
                </div>
            <?php else: ?>
                <?php foreach ($daftar_film as $row): 
                    $terjual = $collection->countDocuments(['Judul_Film' => $row['Judul_Film']]);
                    $kapasitas = isset($row['Kapasitas']) ? (int)$row['Kapasitas'] : 0;
                    $is_full = ($terjual >= $kapasitas);
                    
                    $genre = !empty($row['Genre']) ? $row['Genre'] : 'Genre';
                    $durasi = !empty($row['Durasi']) ? $row['Durasi'] : 0;
                    $harga = !empty($row['Harga']) ? $row['Harga'] : 0;
                    
                    // Fallback untuk data lama yang belum punya sutradara & studio produksi
                    $sutradara = !empty($row['Sutradara']) ? $row['Sutradara'] : '-';
                    $studio_produksi = !empty($row['Studio_Produksi']) ? $row['Studio_Produksi'] : '-';
                ?>
                
                <div class="col-6 col-md-3 mb-4">
                    <div class="card movie-card h-100">
                        <a href="info_film.php?id_film=<?= $row['_id'] ?>">
                            <img src="<?= htmlspecialchars($row['Poster'] ?? 'https://via.placeholder.com/300x400') ?>" class="card-img-top" alt="Poster">
                        </a>
                        <div class="card-body d-flex flex-column justify-content-between">
                            <div>
                                <h5 class="card-title text-white mb-2 text-truncate" title="<?= htmlspecialchars($row['Judul_Film']) ?>"><?= htmlspecialchars($row['Judul_Film']) ?></h5>
                                
                                <div class="mb-2 d-flex gap-1 flex-wrap">
                                    <span class="badge badge-custom"><?= htmlspecialchars($genre) ?></span>
                                    <span class="badge badge-dark-custom"><i class="bi bi-clock"></i> <?= htmlspecialchars($durasi) ?>m</span>
                                </div>
                                
                                <div class="p-2 my-2 rounded" style="background-color: #1a1a1a; border: 1px solid #333; font-size: 0.75rem;">
    <div class="text-truncate mb-1" style="color: #aaa;">
        <i class="bi bi-person-video2 text-warning me-1"></i> Dir: <strong class="text-white fw-medium"><?= htmlspecialchars($sutradara) ?></strong>
    </div>
    <div class="text-truncate" style="color: #aaa;" title="<?= htmlspecialchars($studio_produksi) ?>">
        <i class="bi bi-building text-warning me-1"></i> Studio: <strong class="text-white fw-medium"><?= htmlspecialchars($studio_produksi) ?></strong>
    </div>
</div>
                            </div>
                            
                            <div>
                                <p class="text-warning fw-bold mb-1 mt-2 movie-info-text"><i class="bi bi-calendar3"></i> <?= htmlspecialchars($row['Tanggal_Tayang']) ?> <br class="d-md-none"> <?= htmlspecialchars($row['Waktu_Tayang']) ?> WIB</p>
                                <h6 class="text-white mt-1 mb-0 movie-price">Rp <?= number_format($harga, 0, ',', '.') ?></h6>
                                <p class="text-light mb-0 mt-1 movie-info-text">Sisa: <strong class="<?= $is_full ? 'text-danger' : 'text-success' ?>"><?= $kapasitas - $terjual ?>/<?= $kapasitas ?></strong></p>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-0 pb-3 pt-0">
                            <?php if ($is_full): ?>
                                <button class="btn btn-secondary w-100" style="border-radius: 8px;" disabled>Habis</button>
                            <?php else: ?>
                                <a href="pesan.php?id_film=<?= $row['_id'] ?>" class="btn btn-gold">Pesan</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <footer class="footer-premium pt-5 pb-3 text-center text-md-start">
        <div class="container">
            <div class="row mb-4">
                <div class="col-md-4 mb-4 mb-md-0">
                    <h5 class="brand-text mb-3"><i class="bi bi-camera-reels me-2"></i>Cinema Premiere</h5>
                    <p class="text-muted small lh-lg mx-auto mx-md-0" style="max-width: 90%;">
                        Hadir untuk memberikan pengalaman menonton tak unforgottable. Kami menggabungkan teknologi audio visual terkini dengan kenyamanan studio eksklusif.
                    </p>
                </div>
                <div class="col-md-4 mb-4 mb-md-0">
                    <h5 class="text-white fw-light mb-3">Tautan Cepat</h5>
                    <ul class="list-unstyled">
                        <li><a href="#katalog" class="footer-link d-inline-block d-md-block"><i class="bi bi-chevron-right small me-1"></i> Sedang Tayang</a></li>
                        <li><a href="my_tickets.php" class="footer-link d-inline-block d-md-block"><i class="bi bi-chevron-right small me-1"></i> Riwayat Tiket</a></li>
                        <li><a href="#" class="footer-link d-inline-block d-md-block"><i class="bi bi-chevron-right small me-1"></i> Syarat & Ketentuan</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5 class="text-white fw-light mb-3">Hubungi Kami</h5>
                    <p class="text-muted small mb-2"><i class="bi bi-geo-alt text-warning me-2"></i> Jl. Sinema Mewah No. 123, Jakarta Raya</p>
                    <p class="text-muted small mb-3"><i class="bi bi-envelope text-warning me-2"></i> hello@cinemapremiere.com</p>
                    <div>
                        <a href="#" class="social-icon"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="social-icon"><i class="bi bi-twitter-x"></i></a>
                        <a href="#" class="social-icon"><i class="bi bi-youtube"></i></a>
                    </div>
                </div>
            </div>
            <hr style="border-color: #222;">
            <div class="text-center text-muted small mt-3">
                &copy; 2026 Cinema Premiere. All rights reserved. <br>
                <span style="font-size: 0.7rem;">Dibuat untuk Tugas Praktikum Pemrograman Web.</span>
            </div>
        </div>
    </footer>

</body>
</html>