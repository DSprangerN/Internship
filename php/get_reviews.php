<?php
// Ligar à base de dados

$host = 'localhost';
$dbname = 'estrelinha_login';
$username = 'root';
$password = 'Prisonbreak10';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Retorna todas as reviews
    $stmt = $pdo->query("SELECT name, message, created_at FROM reviews ORDER BY created_at DESC");
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

    //Retorna as reviews como JSON
    header('Content-Type: application/json');
    echo json_encode($reviews);
} catch (PDOException $e) {
    header('Content-Type: application/json', true, 500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
