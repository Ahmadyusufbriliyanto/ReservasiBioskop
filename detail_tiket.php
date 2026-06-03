<?php
require 'koneksi.php';
// Cek ID
if (!isset($_GET['id'])) { die("Tiket tidak valid."); }
$id = new MongoDB\BSON\ObjectId($_GET['id']);
$tiket = $collection->findOne(['_id' => $id]);

if (!$tiket) { die("Data tiket tidak ditemukan!"); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>E-Ticket Details</title>
    <style>
        body { background: #e9e9e9; font-family: 'Courier New', Courier, monospace; display: flex; justify-content: center; padding: 20px; }
        .receipt { background: #fff; width: 300px; padding: 25px; border: 1px solid #ddd; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        h3 { text-align: center; text-transform: uppercase; margin: 0; }
        .divider { border-top: 2px dashed #000; margin: 15px 0; }
        .item { display: flex; justify-content: space-between; margin: 5px 0; }
        .footer { text-align: center; font-size: 0.8rem; margin-top: 20px; color: #555; }
        .status-box { text-align: center; font-weight: bold; font-size: 1.2rem; margin: 10px 0; border: 1px solid #000; padding: 5px; }
    </style>
</head>
<body>
    <div class="receipt">
        <h3>CINEMA PREMIERE</h3>
        <p style="text-align: center; font-size: 0.8rem;">STRUK TIKET DIGITAL</p>
        <div class="divider"></div>
        
        <div class="info">
            <div class="item"><span>Film:</span> <strong><?= htmlspecialchars($tiket['Judul_Film']) ?></strong></div>
            <div class="item"><span>Studio:</span> <?= htmlspecialchars($tiket['Studio']) ?></div>
            <div class="item"><span>Tanggal:</span> <?= htmlspecialchars($tiket['Tanggal_Tayang']) ?></div>
            <div class="item"><span>Jam:</span> <?= htmlspecialchars($tiket['Waktu_Tayang']) ?> WIB</div>
            <div class="item"><span>Kursi:</span> <?= htmlspecialchars($tiket['Nomor_Kursi']) ?></div>
        </div>
        
        <div class="divider"></div>
        <div class="status-box">STATUS: <?= htmlspecialchars($tiket['Status']) ?></div>
        <div class="divider"></div>
        
        <div class="footer">
            ID: <?= (string)$tiket['_id'] ?><br>
            Tiket ini sah sebagai bukti pembayaran.<br>
            Terima kasih telah menonton!
        </div>
    </div>
</body>
</html>
