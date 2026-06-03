<?php
require 'koneksi.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header("Location: login.php"); exit; }

// Proses ACC Tiket
if (isset($_GET['acc'])) {
    $id = new MongoDB\BSON\ObjectId($_GET['acc']);
    $collection->updateOne(['_id' => $id], ['$set' => ['Status' => 'Approved']]);
    echo "<script>alert('Tiket berhasil di-ACC!'); window.location.href='reservasi.php';</script>";
}

// Proses Tolak Tiket
if (isset($_GET['tolak'])) {
    $id = new MongoDB\BSON\ObjectId($_GET['tolak']);
    $collection->updateOne(['_id' => $id], ['$set' => ['Status' => 'Rejected']]);
    // Kalau mau langsung dihapus datanya bisa pakai deleteOne, tapi diubah statusnya lebih bagus buat history.
    echo "<script>alert('Tiket ditolak!'); window.location.href='reservasi.php';</script>";
}

// Ambil semua data reservasi terbaru dari user
$semua_reservasi = $collection->find([], ['sort' => ['_id' => -1]])->toArray();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Reservasi - Admin Premiere</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #0a0a0a; color: #e0e0e0; }
        .navbar-premium { background-color: rgba(10, 10, 10, 0.95); border-bottom: 1px solid #222; padding: 1rem 0; position: sticky; top: 0; z-index: 1000; }
        .brand-text { color: #d4af37; font-weight: 600; letter-spacing: 1.5px; text-transform: uppercase; font-size: 1.2rem; }
        .table-dark { background-color: #121212; }
        .table-hover tbody tr:hover { background-color: #1a1a1a !important; color: #fff; }
        .btn-acc { background-color: #28a745; color: white; border: none; padding: 5px 15px; font-size: 0.85rem; border-radius: 5px; text-decoration: none;}
        .btn-acc:hover { background-color: #218838; color: white; }
        .btn-tolak { background: transparent; color: #ff4444; border: 1px solid #333; padding: 5px 15px; font-size: 0.85rem; border-radius: 5px; text-decoration: none;}
        .btn-tolak:hover { border-color: #ff4444; background-color: rgba(255, 68, 68, 0.1); color: #ff4444;}
    </style>
</head>
<body>
    <div class="navbar-premium mb-5">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="brand-text"><i class="bi bi-camera-reels me-2"></i>Cinema Premiere</div>
            <div class="d-flex align-items-center gap-4">
                <a href="admin.php" class="btn btn-outline-secondary btn-sm px-3"><i class="bi bi-arrow-left"></i> Kembali ke Dashboard</a>
            </div>
        </div>
    </div>

    <div class="container">
        <h4 class="text-white mb-4"><i class="bi bi-list-check me-2 text-warning"></i>Daftar Persetujuan Tiket</h4>
        <div class="card bg-dark border-0 shadow-sm p-3 mb-5">
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle mb-0">
                    <thead>
                        <tr class="text-secondary small">
                            <th>Pemesan</th>
                            <th>Film</th>
                            <th>Jadwal & Kursi</th>
                            <th>Status Saat Ini</th>
                            <th>Aksi (Verifikasi)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($semua_reservasi as $row): 
                            $status = $row['Status'] ?? 'Pending';
                            if ($status == 'Approved') $badge = '<span class="badge bg-success">Di-ACC</span>';
                            elseif ($status == 'Rejected') $badge = '<span class="badge bg-danger">Ditolak</span>';
                            else $badge = '<span class="badge bg-warning text-dark">Menunggu ACC</span>';
                        ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($row['Nama_Pemesan']) ?></strong><br>
                                <small class="text-muted">Akun: <?= htmlspecialchars($row['Pembuat_Reservasi']) ?></small>
                            </td>
                            <td><?= htmlspecialchars($row['Judul_Film']) ?></td>
                            <td>
                                <?= htmlspecialchars($row['Tanggal_Tayang'] . ' | ' . $row['Waktu_Tayang']) ?><br>
                                <span class="text-warning">Kursi: <?= htmlspecialchars($row['Nomor_Kursi']) ?></span>
                            </td>
                            <td><?= $badge ?></td>
                            <td>
                                <?php if($status == 'pending'): ?>
                                    <a href="reservasi.php?acc=<?= $row['_id'] ?>" class="btn-acc me-2" onclick="return confirm('ACC tiket ini?')"><i class="bi bi-check2"></i> ACC</a>
                                    <a href="reservasi.php?tolak=<?= $row['_id'] ?>" class="btn-tolak" onclick="return confirm('Tolak tiket ini?')"><i class="bi bi-x-lg"></i> Tolak</a>
                                <?php else: ?>
                                    <span class="text-muted small">Sudah diverifikasi</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
