<?php
$servername = "localhost";
$username = "estrelinha";
$password = "EstrelinhaAmarela2025";
$dbname = "infantario_db";

// Criar conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexão
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully";
