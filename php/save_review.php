<?php
// ligar a base de dados

$host = 'localhost';
$dbname = 'estrelinha_amarela';
$username = 'roots';
$password = 'Prisonbreak10';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Recebe os dados enviados via POST
    $name = $_POST['name'];
    $message = $_POST['message'];

    // Insere a review no banco de dados
    $stmt = $pdo->prepare("INSERT INTO reviews (name, message) VALUES (:name, :message)");
    $stmt->execute(['name' => $name, 'message' => $message]);

    echo "Review salva com sucesso!";
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
