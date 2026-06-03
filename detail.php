<?php
require 'koneksi.php';
if (!isset($_SESSION['username'])) { header("Location: login.php"); exit; }

$action = isset($_GET['action']) ? $_GET['action'] : 'tambah';

// 1. PROSES HAPUS
if ($action == 'hapus' && isset($_GET['id'])) {
    $id = new MongoDB\BSON\ObjectId($_GET['id']);
    $collection->deleteOne(['_id' => $id]);
    echo "<script>alert('Berhasil dihapus!'); window.location.href='index.php';</script>";
    exit;
}

// 2. PROSES SIMPAN (TAMBAH / EDIT)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Siapkan data tanpa kolom Pembuat_Reservasi
    $data_input = [
        'Nama_Pemesan' => $_POST['Nama_Pemesan'],
        'Judul_Film' => $_POST['Judul_Film'],
        'Studio' => $_POST['Studio'],
        'Tanggal_Tayang' => $_POST['Tanggal_Tayang'],
        'Waktu_Tayang' => $_POST['Waktu_Tayang'],
        'Nomor_Kursi' => $_POST['Nomor_Kursi']
    ];

    if ($action == 'tambah') {
        // Hanya saat tambah data baru, nama akun dicatat
        $data_input['Pembuat_Reservasi'] = $_SESSION['username'];
        $collection->insertOne($data_input);
        $pesan = "Tiket berhasil dipesan!";
    } elseif ($action == 'edit') {
        // Saat edit, hanya ubah data tiket, pemilik aslinya tetap
        $id = new MongoDB\BSON\ObjectId($_POST['id']);
        $collection->updateOne(['_id' => $id], ['$set' => $data_input]);
        $pesan = "Tiket berhasil diupdate!";
    }
    
    // Redirect otomatis diatur oleh index.php (kembali ke admin/customer)
    echo "<script>alert('$pesan'); window.location.href='index.php';</script>";
    exit;
}

// 3. AMBIL DATA JIKA SEDANG EDIT
$data_edit = null;
if ($action == 'edit' && isset($_GET['id'])) {
    $id = new MongoDB\BSON\ObjectId($_GET['id']);
    $data_edit = $collection->findOne(['_id' => $id]);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Detail Tiket</title>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="col-md-8 mx-auto card shadow-sm">
            <div class="card-header bg-dark text-white">
                <h4 class="mb-0"><?= $action == 'edit' ? 'Edit Tiket' : 'Pesan Tiket Baru' ?></h4>
            </div>
            <div class="card-body">
                <form method="POST" action="detail.php?action=<?= $action ?>">
                    <?php if($action == 'edit'): ?>
                        <input type="hidden" name="id" value="<?= $data_edit['_id'] ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="Nama_Pemesan" class="form-control" placeholder="Contoh: Monkey D. Luffy" required 
                               value="<?= $data_edit ? $data_edit['Nama_Pemesan'] : '' ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Judul Film</label>
                        <input type="text" name="Judul_Film" class="form-control" placeholder="Contoh: One Piece Film: Red" required
                               value="<?= $data_edit ? $data_edit['Judul_Film'] : '' ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Studio</label>
                        <select name="Studio" class="form-select" required>
                            <option value="">-- Pilih Studio --</option>
                            <option value="Studio 1" <?= ($data_edit && $data_edit['Studio'] == 'Studio 1') ? 'selected' : '' ?>>Studio 1</option>
                            <option value="Studio 2" <?= ($data_edit && $data_edit['Studio'] == 'Studio 2') ? 'selected' : '' ?>>Studio 2</option>
                            <option value="IMAX" <?= ($data_edit && $data_edit['Studio'] == 'IMAX') ? 'selected' : '' ?>>IMAX</option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tanggal</label>
                            <input type="date" name="Tanggal_Tayang" class="form-control" required value="<?= $data_edit ? $data_edit['Tanggal_Tayang'] : '' ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Jam</label>
                            <input type="time" name="Waktu_Tayang" class="form-control" required value="<?= $data_edit ? $data_edit['Waktu_Tayang'] : '' ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Kursi</label>
                            <input type="text" name="Nomor_Kursi" class="form-control" placeholder="A1, B3..." required value="<?= $data_edit ? $data_edit['Nomor_Kursi'] : '' ?>">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success mt-3">Simpan</button>
                    <a href="index.php" class="btn btn-secondary mt-3">Batal / Kembali</a>
                </form>
            </div>
        </div>
    </div>
</body>
</html>