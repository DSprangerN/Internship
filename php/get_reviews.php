<?php
// Ligar à base de dados

$host = 'localhost';
$dbname = 'estrelinha_amarela';
$username = 'roots';
$password = 'Prisonbreak10';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Busca todas as reviews
    $stmt = $pdo->query("SELECT name, message FROM reviews ORDER BY created_at DESC");
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($reviews);
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
