<?php
// ligar a base de dados

$host = 'localhost';
$dbname = 'estrelinha_login';
$username = 'roots';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Verifica se a review foi enviada por POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = $_POST['name'] ?? '';
        $nessage = $_POST['message'] ?? '';

        //Validar os dados
        if (empty($name) || empty)
    }

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
