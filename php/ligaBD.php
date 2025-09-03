<?php

// Variáveis de ligação à base de dados
$servername = "localhost"; // Endereço do servidor da base de dados
$user = "root";            // Nome de usuário
$passwd = "Prisonbreak10"; // Senha para a conexão com a base de dados
$bd = "estrelinha_login"; // Nome da base dados a que nos pretendemos conectar

// Estabelece a conexão com a base de dados usando as variáveis definidas acima
$liga = mysqli_connect($servername, $user, $passwd, $bd);

// Verifica se a conexão foi bem-sucedida
if (!$liga) {
    // Caso a conexão falhe, exibe uma mensagem de erro
    // Exibe uma mensagem de erro utilizando JavaScript
    echo "<script> alert('A Ligação com a base de dados falhou'); </script>";
    echo "Erro: " . mysqli_connect_error(); // Exibe o erro específico da conexão com o banco de dados
    echo "<script> window.location.href='../php/formulario.php'; </script>"; // Redireciona o usuário para a página '../php/formulario.php' em caso de falha
}
