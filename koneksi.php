<?php
session_start();
require 'vendor/autoload.php';

try {
    $client = new MongoDB\Client("mongodb+srv://ahmadyusuf:ahmad17@cluster0.eqnjhyz.mongodb.net/");
    $db = $client->Bioskop;
    
    // 3 Collection yang dipakai
    $collection = $db->ReservasiBioskop; // Data tiket customer
    $film_collection = $db->Films;       // Data jadwal tayang dari Admin
    $users_collection = $db->users;      // Data akun login
    
} catch (Exception $e) {
    die("Koneksi ke MongoDB gagal: " . $e->getMessage());
}
?>