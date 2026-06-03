<?php
require 'koneksi.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'customer') { header("Location: login.php"); exit; }

if (isset($_GET['batal'])) {
    $id = new MongoDB\BSON\ObjectId($_GET['batal']);
    $collection->deleteOne(['_id' => $id]);
    echo "<script>alert('Tiket berhasil dibatalkan!'); window.location.href='my_tickets.php';</script>";
}

$tiket_saya = $collection->find(['Pembuat_Reservasi' => $_SESSION['username']], ['sort' => ['_id' => -1]])->toArray();

$ada_pending = false;
if (!empty($tiket_saya)) {
    foreach ($tiket_saya as $t) {
        if (!isset($t['Status']) || $t['Status'] === 'Pending') {
            $ada_pending = true;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tiket Saya - Cinema Premiere</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #0a0a0a; color: #e0e0e0; }
        .navbar-premium { background-color: rgba(10, 10, 10, 0.95); border-bottom: 1px solid #222; padding: 1rem 0; position: sticky; top: 0; z-index: 1000; }
        .brand-text { color: #d4af37; font-weight: 600; letter-spacing: 1.5px; text-transform: uppercase; font-size: 1.2rem; }
        .nav-link-custom { color: #888; text-decoration: none; margin-right: 15px; font-weight: 500; transition: 0.3s; }
        .nav-link-custom:hover, .nav-link-custom.active { color: #d4af37; }
        
        .table-dark { background-color: #121212; }
        .table-hover tbody tr:hover { background-color: #1a1a1a !important; color: #fff; }
        
        /* Tombol Mobile Friendly */
        .btn-qr { background-color: #d4af37; color: #000; border: none; padding: 6px 12px; font-size: 0.8rem; border-radius: 5px; text-decoration: none; font-weight: 600; white-space: nowrap;}
        .btn-danger-minimal { background: transparent; color: #ff4444; border: 1px solid #333; padding: 6px 12px; font-size: 0.8rem; border-radius: 5px; text-decoration: none; white-space: nowrap;}
        
        /* MANTRA RESPONSIVE KHUSUS HP */
        @media (max-width: 768px) {
            .table { font-size: 0.75rem; } /* Font tabel mengecil di HP */
            .navbar-premium .d-flex { flex-wrap: wrap; justify-content: center; gap: 10px; }
            .btn-qr, .btn-danger-minimal { padding: 4px 8px; font-size: 0.7rem; }
            .badge { font-size: 0.65rem; padding: 0.3em; }
        }
    </style>
</head>
<body>

    <div class="navbar-premium mb-5">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="brand-text"><i class="bi bi-camera-reels me-2"></i>Cinema Premiere</div>
            <div class="d-flex align-items-center">
                <a href="customer.php" class="nav-link-custom"><i class="bi bi-film"></i> Sedang Tayang</a>
                <a href="my_tickets.php" class="nav-link-custom active"><i class="bi bi-ticket-perforated"></i> Tiket Saya</a>
                <span style="font-size: 0.9rem; color: #555; margin: 0 15px;">|</span>
                <span style="font-size: 0.9rem; color: #888; margin-right: 15px;">Halo, <span class="text-white"><?= htmlspecialchars($_SESSION['username']) ?></span></span>
                <a href="logout.php" class="btn btn-outline-danger btn-sm px-3">Logout</a>
            </div>
        </div>
    </div>

    <div class="container">
        <h4 class="text-white mb-4"><i class="bi bi-ticket-perforated me-2 text-success"></i>Daftar Tiket Saya</h4>
        
        <?php if ($ada_pending): ?>
        <div class="alert alert-warning border-0" style="background-color: rgba(212, 175, 55, 0.1); color: #d4af37;">
            <i class="bi bi-info-circle me-2"></i> <strong>Info:</strong> Ada pesanan tiket kamu yang sedang diproses. Tombol QR Code akan muncul otomatis setelah di-ACC Admin!
        </div>
        <?php endif; ?>

        <div class="card bg-dark border-0 shadow-sm p-2 p-md-3 mb-5">
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle mb-0">
                    <thead>
                        <tr class="text-secondary small">
                            <th>Film</th>
                            <th>Jadwal</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($tiket_saya)): ?>
                            <tr><td colspan="4" class="text-center text-muted py-4">Kamu belum memesan tiket apapun.</td></tr>
                        <?php else: ?>
                            <?php foreach ($tiket_saya as $row): 
                                $status = $row['Status'] ?? 'Pending'; 
                                $qr_data = "TIKET RESMI BIOSKOP | ID: " . $row['_id'] . " | Nama: " . $row['Nama_Pemesan'] . " | Film: " . $row['Judul_Film'] . " | Kursi: " . $row['Nomor_Kursi'];
                                $qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" . urlencode($qr_data);
                            ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($row['Judul_Film']) ?></strong><br><small class="text-muted">Kursi: <?= htmlspecialchars($row['Nomor_Kursi']) ?></small></td>
                                <td><?= htmlspecialchars($row['Tanggal_Tayang']) ?><br><small><?= htmlspecialchars($row['Waktu_Tayang']) ?> WIB</small></td>
                                <td>
                                    <?php if($status == 'Approved'): ?>
                                        <span class="badge bg-success"><i class="bi bi-check-circle"></i> Berhasil</span>
                                    <?php elseif($status == 'Rejected'): ?>
                                        <span class="badge bg-danger"><i class="bi bi-x-circle"></i> Ditolak</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split"></i> Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($status == 'Approved'): ?>
                                        <button type="button" class="btn-qr" data-bs-toggle="modal" data-bs-target="#qrModal<?= $row['_id'] ?>">
                                            <i class="bi bi-qr-code"></i> Tiket
                                        </button>
                                        
                                        <div class="modal fade" id="qrModal<?= $row['_id'] ?>" tabindex="-1" aria-hidden="true">
                                          <div class="modal-dialog modal-dialog-centered modal-sm">
                                            <div class="modal-content bg-dark border-0">
                                              <div class="modal-header border-bottom-0"><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
                                              <div class="modal-body text-center pb-4">
                                                <h6 class="text-white">E-Ticket</h6>
                                                <div class="bg-white p-2 rounded d-inline-block"><img src="<?= $qr_url ?>" alt="QR" class="img-fluid"></div>
                                                <p class="text-white mt-2 small"><?= htmlspecialchars($row['Judul_Film']) ?></p>
                                              </div>
                                            </div>
                                          </div>
                                        </div>
                                    <?php else: ?>
                                        <a href="my_tickets.php?batal=<?= $row['_id'] ?>" class="btn-danger-minimal" onclick="return confirm('Yakin batal?')">Batal</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>