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
        if (empty($name) || empty($message)) {
            http_response_code(400);
            echo json_encode(['error' => 'Nome e mensagem são obrigatórios.']);
            exit;
        }

        // Inserir a review na base de dados
        $stmt = $pdo->prepare("INSERT INTO reviews (name, message) VALUES (:name, :message)");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':message', $message);

        echo json_encode(['success' => 'Review inserida com sucesso.']);
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Métode não permitido.']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
