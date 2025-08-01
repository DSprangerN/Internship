<?php
// ligar a base de dados

$host = 'localhost';
$dbname = 'aestrel1_estrelinha_login';
$username = 'aestrel1_root';
$password = 'Familia20*';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Verifica se a review foi enviada por POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nome = $_POST['nome'] ?? '';
        $review = $_POST['review'] ?? '';

        //Validar os dados
        if (empty($nome) || empty($review)) {
            http_response_code(400);
            echo json_encode(['error' => 'Nome e mensagem são obrigatórios.']);
            exit;
        }

        // Inserir a review na base de dados
        $stmt = $pdo->prepare("INSERT INTO reviews (nome, review) VALUES (:nome, :review)");
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':review', $review);
        $stmt->execute();

        echo json_encode(['success' => 'Review inserida com sucesso.']);
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Métode não permitido.']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
