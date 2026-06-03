<?php
require 'koneksi.php';
// Cek ID
if (!isset($_GET['id'])) { die("Tiket tidak valid."); }
$id = new MongoDB\BSON\ObjectId($_GET['id']);
$tiket = $collection->findOne(['_id' => $id]);

if (!$tiket) { die("Data tiket tidak ditemukan!"); }

// Generate URL untuk QR Code
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$domain = $_SERVER['HTTP_HOST'];
$url_tiket = $protocol . $domain . "/detail_tiket.php?id=" . (string)$tiket['_id'];
$qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" . urlencode($url_tiket);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Ticket: <?= htmlspecialchars($tiket['Judul_Film']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { background-color: #050505; font-family: 'Poppins', sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .ticket-card { width: 320px; background: #121212; border-radius: 20px; padding: 30px; position: relative; border: 1px solid #333; box-shadow: 0 10px 30px rgba(0,0,0,0.5); color: #fff; }
        
        /* Garis Emas di atas */
        .ticket-card::before { content: ""; position: absolute; top: 0; left: 0; width: 100%; height: 6px; background: linear-gradient(90deg, #d4af37, #f3e5ab, #d4af37); border-radius: 20px 20px 0 0; }
        
        .brand { color: #d4af37; font-size: 0.75rem; letter-spacing: 3px; text-transform: uppercase; text-align: center; margin-bottom: 15px; }
        .title { font-size: 1.3rem; font-weight: 600; text-align: center; margin-bottom: 20px; line-height: 1.3; color: #fff; }
        
        .info-row { display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 0.9rem; border-bottom: 1px solid #222; padding-bottom: 8px; }
        .info-row span:first-child { color: #777; }
        .info-row span:last-child { color: #fff; font-weight: 500; }
        
        .status-badge { text-align: center; background: #d4af37; color: #000; padding: 6px 0; border-radius: 50px; font-weight: 600; font-size: 0.85rem; margin: 25px 0; }
        
        .qr-section { text-align: center; background: #fff; padding: 15px; border-radius: 12px; }
        .qr-section img { width: 140px; }
        
        .footer { text-align: center; font-size: 0.7rem; color: #555; margin-top: 20px; }
    </style>
</head>
<body>

    <div class="ticket-card">
        <div class="brand">Cinema Premiere</div>
        <div class="title"><?= htmlspecialchars($tiket['Judul_Film']) ?></div>
        
        <div class="info-row"><span>Studio</span><span><?= htmlspecialchars($tiket['Studio']) ?></span></div>
        <div class="info-row"><span>Tanggal</span><span><?= htmlspecialchars($tiket['Tanggal_Tayang']) ?></span></div>
        <div class="info-row"><span>Jam</span><span><?= htmlspecialchars($tiket['Waktu_Tayang']) ?> WIB</span></div>
        <div class="info-row"><span>Kursi</span><span><?= htmlspecialchars($tiket['Nomor_Kursi']) ?></span></div>
        
        <div class="status-badge">TICKET VALID</div>

        <div class="qr-section">
            <img src="<?= $qr_url ?>" alt="QR Tiket">
        </div>
        
        <div class="footer">
            ID: <?= (string)$tiket['_id'] ?><br>
            Tunjukkan tiket ini di loket bioskop.
        </div>
    </div>

</body>
</html>
