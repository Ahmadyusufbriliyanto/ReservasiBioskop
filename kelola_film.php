<?php
require 'koneksi.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header("Location: login.php"); exit; }

// Cek apakah sedang mode Edit atau Tambah Baru
$is_edit = isset($_GET['id']);
$film = null;

if ($is_edit) {
    $id = new MongoDB\BSON\ObjectId($_GET['id']);
    $film = $film_collection->findOne(['_id' => $id]);
}

// Proses Simpan Data (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data_simpan = [
        'Judul_Film' => $_POST['Judul_Film'],
        'Sutradara' => $_POST['Sutradara'],
        'Studio_Produksi' => $_POST['Studio_Produksi'],
        'Genre' => $_POST['Genre'],
        'Durasi' => (int)$_POST['Durasi'],
        'Rating_Usia' => $_POST['Rating_Usia'],
        'Sinopsis' => $_POST['Sinopsis'],
        'Studio' => $_POST['Studio'], // Studio 1, 2, IMAX
        'Tanggal_Tayang' => $_POST['Tanggal_Tayang'],
        'Waktu_Tayang' => $_POST['Waktu_Tayang'],
        'Kapasitas' => (int)$_POST['Kapasitas'],
        'Harga' => (int)$_POST['Harga']
    ];

    // Proses Upload Gambar Poster
    if (!empty($_FILES['Poster']['name'])) {
        $nama_file = $_FILES['Poster']['name'];
        $tmp_file = $_FILES['Poster']['tmp_name'];
        $path_simpan = "uploads/" . time() . "_" . $nama_file; 
        
        if (move_uploaded_file($tmp_file, $path_simpan)) {
            // Hapus poster lama kalau lagi mode edit dan posternya diganti
            if ($is_edit && isset($film['Poster']) && file_exists($film['Poster'])) {
                unlink($film['Poster']);
            }
            $data_simpan['Poster'] = $path_simpan;
        }
    }

    if ($is_edit) {
        $film_collection->updateOne(['_id' => $id], ['$set' => $data_simpan]);
        $pesan = "Jadwal dan Info Film berhasil diupdate!";
    } else {
        $film_collection->insertOne($data_simpan);
        $pesan = "Film baru berhasil ditambahkan!";
    }

    echo "<script>alert('$pesan'); window.location.href='admin.php';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $is_edit ? 'Edit' : 'Tambah' ?> Film | Cinema Premiere</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #0a0a0a; color: #e0e0e0; }
        .form-card { background-color: #121212; border: 1px solid #2a2a2a; border-radius: 15px; }
        .form-control, .form-select { background-color: #1a1a1a !important; border: 1px solid #333 !important; color: #fff !important; padding: 0.8rem; border-radius: 8px;}
        .form-control:focus, .form-select:focus { border-color: #d4af37 !important; box-shadow: 0 0 0 0.2rem rgba(212, 175, 55, 0.25); }
        .label-gold { color: #d4af37; font-size: 0.85rem; font-weight: 500; margin-bottom: 0.5rem; }
        
        .btn-gold { background-color: #d4af37; color: #0a0a0a; font-weight: 600; border: none; padding: 12px; border-radius: 8px; transition: 0.3s;}
        .btn-gold:hover { background-color: #b5952f; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(212, 175, 55, 0.3);}
        .btn-outline-gold { border: 1px solid #d4af37; color: #d4af37; padding: 12px; border-radius: 8px; text-decoration: none; text-align: center; display: block; transition: 0.3s;}
        .btn-outline-gold:hover { background-color: #d4af37; color: #0a0a0a; }

        textarea.form-control { min-height: 120px; }
    </style>
</head>
<body class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                
                <div class="d-flex align-items-center mb-4">
                    <a href="admin.php" class="text-secondary text-decoration-none me-3 fs-5"><i class="bi bi-arrow-left"></i></a>
                    <h3 class="mb-0 text-white fw-light"><?= $is_edit ? 'Edit Data Film' : 'Tambah Film Baru' ?></h3>
                </div>

                <div class="form-card p-4 p-md-5 shadow-lg">
                    <form method="POST" enctype="multipart/form-data">
                        
                        <?php if($is_edit): ?>
                        <div class="text-center mb-4 pb-4" style="border-bottom: 1px solid #222;">
                            <img src="<?= htmlspecialchars($film['Poster'] ?? 'https://via.placeholder.com/150') ?>" class="rounded shadow" style="height: 200px; width: 140px; object-fit: cover; border: 2px solid #333;">
                            <p class="text-muted small mt-2 mb-0">Poster Saat Ini</p>
                        </div>
                        <?php endif; ?>

                        <h5 class="text-white mb-4"><i class="bi bi-info-circle text-warning me-2"></i>Informasi Umum</h5>
                        
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="label-gold">Judul Film</label>
                                <input type="text" name="Judul_Film" class="form-control" placeholder="Contoh: The Batman" required value="<?= $is_edit ? htmlspecialchars($film['Judul_Film']) : '' ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="label-gold"><?= $is_edit ? 'Ganti Poster (Opsional)' : 'Upload Poster Film' ?></label>
                                <input type="file" name="Poster" class="form-control" accept="image/*" <?= $is_edit ? '' : 'required' ?>>
                            </div>
                            <div class="col-md-6">
                                <label class="label-gold">Sutradara</label>
                                <input type="text" name="Sutradara" class="form-control" placeholder="Contoh: Matt Reeves" required value="<?= $is_edit ? htmlspecialchars($film['Sutradara'] ?? '') : '' ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="label-gold">Studio Produksi</label>
                                <input type="text" name="Studio_Produksi" class="form-control" placeholder="Contoh: Warner Bros" required value="<?= $is_edit ? htmlspecialchars($film['Studio_Produksi'] ?? '') : '' ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="label-gold">Genre</label>
                                <input type="text" name="Genre" class="form-control" placeholder="Action, Sci-Fi..." required value="<?= $is_edit ? htmlspecialchars($film['Genre'] ?? '') : '' ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="label-gold">Durasi (Menit)</label>
                                <input type="number" name="Durasi" class="form-control" placeholder="176" required value="<?= $is_edit ? htmlspecialchars($film['Durasi'] ?? '') : '' ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="label-gold">Rating Usia</label>
                                <select name="Rating_Usia" class="form-select" required>
                                    <option value="SU" <?= ($is_edit && isset($film['Rating_Usia']) && $film['Rating_Usia'] == 'SU') ? 'selected' : '' ?>>Semua Umur (SU)</option>
                                    <option value="13+" <?= ($is_edit && isset($film['Rating_Usia']) && $film['Rating_Usia'] == '13+') ? 'selected' : '' ?>>Remaja (13+)</option>
                                    <option value="17+" <?= ($is_edit && isset($film['Rating_Usia']) && $film['Rating_Usia'] == '17+') ? 'selected' : '' ?>>Dewasa (17+)</option>
                                    <option value="21+" <?= ($is_edit && isset($film['Rating_Usia']) && $film['Rating_Usia'] == '21+') ? 'selected' : '' ?>>Dewasa (21+)</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="label-gold">Sinopsis</label>
                                <textarea name="Sinopsis" class="form-control" placeholder="Tuliskan jalan cerita singkat film ini..." required><?= $is_edit ? htmlspecialchars($film['Sinopsis'] ?? '') : '' ?></textarea>
                            </div>
                        </div>

                        <h5 class="text-white mb-4 mt-5"><i class="bi bi-camera-reels text-warning me-2"></i>Pengaturan Penayangan</h5>

                        <div class="row g-3 mb-5">
                            <div class="col-md-6">
                                <label class="label-gold">Studio Bioskop</label>
                                <select name="Studio" class="form-select" required>
                                    <option value="Studio 1" <?= ($is_edit && $film['Studio'] == 'Studio 1') ? 'selected' : '' ?>>Studio 1</option>
                                    <option value="Studio 2" <?= ($is_edit && $film['Studio'] == 'Studio 2') ? 'selected' : '' ?>>Studio 2</option>
                                    <option value="IMAX" <?= ($is_edit && $film['Studio'] == 'IMAX') ? 'selected' : '' ?>>IMAX</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="label-gold">Harga Tiket (Rp)</label>
                                <input type="number" name="Harga" class="form-control" placeholder="Contoh: 50000" min="1000" required value="<?= $is_edit ? htmlspecialchars($film['Harga'] ?? '') : '' ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="label-gold">Kapasitas Kursi</label>
                                <input type="number" name="Kapasitas" class="form-control" placeholder="Contoh: 40" min="10" required value="<?= $is_edit ? htmlspecialchars($film['Kapasitas'] ?? '') : '' ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="label-gold">Tanggal Tayang Perdana</label>
                                <input type="date" name="Tanggal_Tayang" class="form-control" required value="<?= $is_edit ? htmlspecialchars($film['Tanggal_Tayang']) : '' ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="label-gold">Jam Tayang Utama</label>
                                <input type="time" name="Waktu_Tayang" class="form-control" required value="<?= $is_edit ? htmlspecialchars($film['Waktu_Tayang']) : '' ?>">
                            </div>
                        </div>

                        <div class="d-flex gap-3">
                            <button type="submit" class="btn btn-gold w-100"><i class="bi bi-save me-2"></i> <?= $is_edit ? 'Update Data Film' : 'Simpan Film Baru' ?></button>
                            <a href="admin.php" class="btn btn-outline-gold w-50">Batal</a>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</body>
</html>