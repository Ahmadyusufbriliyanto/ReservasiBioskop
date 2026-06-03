<?php
require 'koneksi.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'customer') { header("Location: login.php"); exit; }
if (!isset($_GET['id_film'])) { header("Location: customer.php"); exit; }

$id_film = new MongoDB\BSON\ObjectId($_GET['id_film']);
$film = $film_collection->findOne(['_id' => $id_film]);

// 1. Siapkan Pilihan Jam Tayang
$pilihan_jam = ['10:30', '13:15', '15:30', '18:45', '21:00'];
if (!empty($film['Waktu_Tayang']) && !in_array($film['Waktu_Tayang'], $pilihan_jam)) {
    array_unshift($pilihan_jam, $film['Waktu_Tayang']);
    sort($pilihan_jam); 
}

$waktu_dipilih = isset($_GET['waktu']) ? $_GET['waktu'] : $pilihan_jam[0];

// 2. Cari kursi yang SUDAH DIBOOKING & Pecah komanya
$booked_query = $collection->find([
    'Judul_Film' => $film['Judul_Film'],
    'Tanggal_Tayang' => $film['Tanggal_Tayang'],
    'Waktu_Tayang' => $waktu_dipilih
]);

$booked_seats = [];
foreach ($booked_query as $b) { 
    $kursi_array = explode(',', $b['Nomor_Kursi']);
    foreach($kursi_array as $k) {
        $booked_seats[] = trim($k);
    }
}
$kapasitas = isset($film['Kapasitas']) && $film['Kapasitas'] > 0 ? (int)$film['Kapasitas'] : 40;

// 3. Proses Pembayaran Multi-Kursi
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kursi_dipesan = explode(',', $_POST['Nomor_Kursi']);
    $kursi_dipesan = array_map('trim', $kursi_dipesan);
    $jumlah_tiket = count($kursi_dipesan);

    // Validasi 1: Cek apakah ada kursi yang keduluan
    foreach ($kursi_dipesan as $k) {
        if (in_array($k, $booked_seats)) {
            echo "<script>alert('Waduh, kursi $k sudah diambil orang lain! Pilih ulang ya.'); window.location.href='pesan.php?id_film=".$_GET['id_film']."&waktu=$waktu_dipilih';</script>";
            exit;
        }
    }

    // Validasi 2: Cek kapasitas sisa
    $terjual = count($booked_seats);
    if (($terjual + $jumlah_tiket) > $kapasitas) {
        echo "<script>alert('Maaf, kapasitas kursi sisa tidak cukup!'); window.location.href='pesan.php?id_film=".$_GET['id_film']."&waktu=$waktu_dipilih';</script>";
        exit;
    }

    $harga_satuan = (int)($film['Harga'] ?? 0);
    $total_harga = $harga_satuan * $jumlah_tiket;
    $metode = $_POST['Metode_Pembayaran'];

    $collection->insertOne([
        'Pembuat_Reservasi' => $_SESSION['username'],
        'Nama_Pemesan' => $_POST['Nama_Pemesan'],
        'Judul_Film' => $film['Judul_Film'],
        'Studio' => $film['Studio'],
        'Tanggal_Tayang' => $film['Tanggal_Tayang'],
        'Waktu_Tayang' => $_POST['Waktu_Tayang'],
        'Nomor_Kursi' => $_POST['Nomor_Kursi'], // "A1, A2, A3"
        'Metode_Pembayaran' => $metode,
        'Jumlah_Tiket' => $jumlah_tiket,
        'Harga' => $total_harga,
        'Status' => 'Lunas'
    ]);
    
    echo "<script>alert('Pembayaran via $metode BERHASIL untuk $jumlah_tiket kursi!'); window.location.href='my_tickets.php';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Kursi & Bayar | Cinema Premiere</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #0a0a0a; color: #e0e0e0; }
        .card-premium { background-color: #121212; border: 1px solid #2a2a2a; border-radius: 12px; overflow: hidden; }
        .form-control { background-color: #1a1a1a !important; border: 1px solid #333 !important; color: #fff !important; padding: 0.8rem; }
        .form-control:focus { border-color: #d4af37 !important; box-shadow: 0 0 0 0.2rem rgba(212, 175, 55, 0.25); }
        .label-gold { color: #d4af37; font-size: 0.85rem; font-weight: 500; margin-bottom: 0.5rem; display: block; }
        
        .btn-gold { background-color: #d4af37; color: #0a0a0a; font-weight: 600; border: none; padding: 12px; transition: 0.3s; }
        .btn-gold:hover { background-color: #b5952f; color: #0a0a0a; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(212, 175, 55, 0.3);}
        
        /* Seat Map */
        .cinema-screen { background: linear-gradient(to bottom, #d4af37, #121212); height: 40px; width: 100%; border-top-left-radius: 50%; border-top-right-radius: 50%; margin-bottom: 30px; box-shadow: 0 -15px 30px rgba(212, 175, 55, 0.2); text-align: center; color: #0a0a0a; font-weight: bold; line-height: 40px; letter-spacing: 5px; }
        .seat-container { display: flex; flex-direction: column; align-items: center; gap: 10px; }
        .seat-row { display: flex; gap: 10px; align-items: center; }
        .seat { width: 35px; height: 35px; background-color: #333; border-radius: 8px 8px 4px 4px; cursor: pointer; display: flex; justify-content: center; align-items: center; font-size: 0.7rem; font-weight: bold; color: transparent; transition: 0.2s; }
        .seat:hover:not(.booked) { background-color: #d4af37; color: #0a0a0a; transform: scale(1.1); }
        .seat.selected { background-color: #d4af37; color: #0a0a0a; transform: scale(1.1); box-shadow: 0 0 10px rgba(212, 175, 55, 0.5); }
        .seat.booked { background-color: #444; cursor: not-allowed; opacity: 0.3; }
        .seat-placeholder { width: 35px; height: 35px; }
        .row-label { width: 20px; text-align: center; font-weight: bold; color: #666; }
        
        /* Jam Tayang & Pembayaran */
        .time-badge { text-decoration: none; border: 1px solid #444; color: #ccc; padding: 6px 15px; border-radius: 8px; transition: 0.3s; display: inline-block; font-size: 0.9rem;}
        .time-badge.active { background-color: #d4af37; color: #000; border-color: #d4af37; font-weight: 600; }
        .time-badge:hover:not(.active) { background-color: rgba(212, 175, 55, 0.1); border-color: #d4af37; color: #d4af37; }
        
        .pay-label { display: flex; align-items: center; justify-content: center; border: 1px solid #333; padding: 10px; border-radius: 8px; cursor: pointer; color: #888; font-weight: 600; transition: 0.3s; height: 100%;}
        .btn-check:checked + .pay-label { border-color: #d4af37; background-color: rgba(212, 175, 55, 0.1); color: #fff;}
        .btn-check:checked + .pay-label.dana { color: #118EEA; }
        .btn-check:checked + .pay-label.gopay { color: #00AED6; }
        .btn-check:checked + .pay-label.ovo { color: #4C2A86; }
        .btn-check:checked + .pay-label.spay { color: #EE4D2D; }
    </style>
</head>
<body class="d-flex align-items-center min-vh-100 py-4 py-md-5">
    <div class="container">
        
        <div class="mb-4 d-flex justify-content-between align-items-center">
            <a href="info_film.php?id_film=<?= $id_film ?>" class="text-secondary text-decoration-none"><i class="bi bi-arrow-left"></i> Kembali</a>
            <h5 class="text-white mb-0 fw-light d-none d-md-block">Checkout Pesanan</h5>
        </div>

        <div class="card card-premium shadow-lg border-0">
            <div class="row g-0">
                
                <div class="col-lg-7 p-4 p-md-5" style="border-right: 1px solid #2a2a2a;">
                    <div class="cinema-screen">LAYAR BIOSKOP</div>
                    <div class="seat-container">
                        <?php 
                        $cols = 8;
                        $num_rows = ceil($kapasitas / $cols);
                        $seat_counter = 0;

                        for ($r = 0; $r < $num_rows; $r++) {
                            $row_label = chr(65 + $r); 
                            echo '<div class="seat-row"><div class="row-label">'.$row_label.'</div>';
                            
                            for ($c = 1; $c <= $cols; $c++) {
                                if ($seat_counter < $kapasitas) {
                                    $seatNumber = $row_label . $c;
                                    $statusClass = in_array($seatNumber, $booked_seats) ? 'booked' : 'available';
                                    echo '<div class="seat '.$statusClass.'" data-seat="'.$seatNumber.'">'.$seatNumber.'</div>';
                                    $seat_counter++;
                                } else {
                                    echo '<div class="seat-placeholder"></div>';
                                }
                                if ($c == 4) echo '<div style="width: 20px;"></div>';
                            }
                            echo '<div class="row-label">'.$row_label.'</div></div>';
                        }
                        ?>
                    </div>

                    <div class="d-flex justify-content-center gap-4 mt-5 font-monospace small text-muted">
                        <div><div style="width:15px;height:15px;background:#333;display:inline-block;border-radius:4px;margin-right:5px;"></div> Tersedia</div>
                        <div><div style="width:15px;height:15px;background:#d4af37;display:inline-block;border-radius:4px;margin-right:5px;"></div> Pilihan</div>
                        <div><div style="width:15px;height:15px;background:#444;display:inline-block;border-radius:4px;margin-right:5px;"></div> Terisi</div>
                    </div>
                </div>

                <div class="col-lg-5 p-4 p-md-5">
                    <h4 class="mb-4 text-white fw-light">Detail Reservasi</h4>
                    
                    <div class="p-3 mb-4 rounded d-flex justify-content-between align-items-center" style="background-color: #1a1a1a; border-left: 4px solid #d4af37;">
                        <div>
                            <h5 class="mb-1 text-white"><?= htmlspecialchars($film['Judul_Film']) ?></h5>
                            <small class="text-secondary d-block"><?= htmlspecialchars($film['Studio']) ?> • <?= htmlspecialchars($film['Tanggal_Tayang']) ?></small>
                        </div>
                        <div class="text-end">
                            <small class="text-muted d-block">Total Bayar</small>
                            <h4 class="text-info mt-1 mb-0" id="totalHargaUI">Rp 0</h4>
                        </div>
                    </div>

                    <form method="POST" id="formPesanan">
                        <div class="mb-4">
                            <label class="label-gold mb-2">Pilih Jam Tayang</label>
                                <div class="d-flex flex-wrap gap-2">
                                    <?php foreach ($pilihan_jam as $jam): ?>
                                        <a href="javascript:void(0)"
                                            class="time-badge <?= $waktu_dipilih == $jam ? 'active' : '' ?>" 
                                            onclick="pindahJam('<?= $jam ?>')">
                                            <?= $jam ?> WIB
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                        </div>
                        <input type="hidden" name="Waktu_Tayang" value="<?= $waktu_dipilih ?>">
                        
                        <div class="mb-3">
                            <label class="label-gold">Nama Lengkap</label>
                            <input type="text" name="Nama_Pemesan" class="form-control" placeholder="Tulis nama penonton..." required>
                        </div>
                        
                        <div class="mb-4">
                            <label class="label-gold">Nomor Kursi (Maks 5)</label>
                            <input type="text" 
                                   name="Nomor_Kursi" 
                                   id="inputKursi" 
                                   class="form-control" 
                                   placeholder="Pilih dari layar bioskop" 
                                   required 
                                   readonly 
                                   value="<?= htmlspecialchars(isset($_GET['kursi']) ? $_GET['kursi'] : '') ?>" 
                                   style="background-color: #222 !important; cursor: not-allowed; text-align: center; font-size: 1.2rem; font-weight: bold; color: #d4af37 !important;">
                        </div>

                        <div class="mb-4">
                            <label class="label-gold">Metode Pembayaran (E-Wallet)</label>
                            <div class="row g-2">
                                <div class="col-6"><input type="radio" class="btn-check" name="Metode_Pembayaran" id="pay_dana" value="DANA" required><label class="pay-label dana" for="pay_dana">DANA</label></div>
                                <div class="col-6"><input type="radio" class="btn-check" name="Metode_Pembayaran" id="pay_gopay" value="GoPay"><label class="pay-label gopay" for="pay_gopay">GoPay</label></div>
                                <div class="col-6"><input type="radio" class="btn-check" name="Metode_Pembayaran" id="pay_ovo" value="OVO"><label class="pay-label ovo" for="pay_ovo">OVO</label></div>
                                <div class="col-6"><input type="radio" class="btn-check" name="Metode_Pembayaran" id="pay_spay" value="ShopeePay"><label class="pay-label spay" for="pay_spay">ShopeePay</label></div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-gold w-100 d-flex justify-content-between align-items-center">
                            <span>Bayar Sekarang</span>
                            <i class="bi bi-arrow-right-circle-fill"></i>
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>

<script>
    // 1. Pas halaman selesai loading, dia otomatis baca kursi dari input box
    document.addEventListener("DOMContentLoaded", function() {
        const inputKursi = document.getElementById('inputKursi');
        
        // Cek kalau ada data kursi di input box
        if (inputKursi.value !== "") {
            const daftarKursi = inputKursi.value.split(', ');
            
            // Cari elemen kursi di layar dan kasih class 'selected' biar warnanya kuning
            daftarKursi.forEach(seatNum => {
                const seatEl = document.querySelector(`[data-seat="${seatNum}"]`);
                if(seatEl) {
                    seatEl.classList.add('selected');
                }
            });
        }
    });

    // 2. Logic Klik Kursi (Tetap sama)
    const seats = document.querySelectorAll('.seat.available');
    const inputKursi = document.getElementById('inputKursi');
    const totalHargaUI = document.getElementById('totalHargaUI');
    const hargaSatuan = <?= (int)($film['Harga'] ?? 0) ?>;
    let selectedSeats = inputKursi.value ? inputKursi.value.split(', ') : [];

    seats.forEach(seat => {
        seat.addEventListener('click', () => {
            const seatNum = seat.dataset.seat;
            if (selectedSeats.includes(seatNum)) {
                selectedSeats = selectedSeats.filter(s => s !== seatNum);
                seat.classList.remove('selected');
            } else {
                if (selectedSeats.length >= 5) {
                    alert('Maksimal 5 kursi!');
                    return;
                }
                selectedSeats.push(seatNum);
                seat.classList.add('selected');
            }
            inputKursi.value = selectedSeats.join(', ');
            totalHargaUI.innerText = 'Rp ' + (selectedSeats.length * hargaSatuan).toLocaleString('id-ID');
        });
    });

    // 3. Fungsi Pindah Jam (Wajib ada)
    function pindahJam(jam) {
        const kursi = document.getElementById('inputKursi').value;
        // Pindah sambil bawa kursi yang sudah dipilih tadi
        window.location.href = 'pesan.php?id_film=<?= $id_film ?>&waktu=' + jam + '&kursi=' + encodeURIComponent(kursi);
    }
</script>
</body>
</html>
