<?php
// Cambia estos valores si tu MySQL tiene contraseña.
$host = 'localhost';
$dbname = 'sepe';
$user = 'root';
$pass = 'Yam.idk12';
if (is_file(__DIR__ . '/config.local.php')) { require __DIR__ . '/config.local.php'; }
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
} catch (PDOException $e) {
    http_response_code(500);
    exit('No se pudo conectar a la base de datos. Revisa config.php y la importación de sepe.sql.');
}
