<?php
// É feita uma verificação se o utilizador já está registado
// Se não estiver, é feito o registo e redirecionado para a página de login
session_start(); // Inicia a sessão

include 'ligaBD.php';

$nome = trim($_POST['Nome']); // trim - remove espaços em branco
$sobrenome = trim($_POST['Sobrenome']);
$username = trim($_POST['Username']);
$password = trim($_POST['Password']);
$repass = trim($_POST['repass']);

// Confirma que as duas passwords são iguais
if ($password !== $repass) {
    echo "<script>
        alert('As passwords não coincidem!');
        window.location.href='../Registo.php';
        </script>";
    exit(); // Termina o script se as passwords não coincidirem
}

// Script SQL para verificar se o username e a password existem na base de dados
$check_query = "SELECT * FROM users_login WHERE Username = ?";
$stmt = mysqli_prepare($liga, $check_query);
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
// Verifica se o utilizador já existe na base de dados

if (mysqli_num_rows($result) > 0) {
    // Caso o utilizador já exista, exibe uma mensagem de erro
    echo "<script>
        alert('Este utilizador já está registado!');
        window.location.href='../Login.php';
        </script>";
} else {
    //O utilizador não existe, então é feito o registo
    $hashed_password = password_hash($password, PASSWORD_BCRYPT); // Criptografa a password
    // Script SQL para inserir o novo utilizador na base de dados
    $query = "INSERT INTO users_login (Nome, Sobrenome, Username, Password)
              VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($liga, $query);
    mysqli_stmt_bind_param($stmt, "ssss", $nome, $sobrenome, $username, $hashed_password);

    if (mysqli_stmt_execute($stmt)) {
        // Registo bem-sucedido, redireciona para a página de login
        echo "<script>
            alert('Registo efetuado com sucesso!');
            window.location.href='../Login.html';
            </script>";
    } else {
        // Caso ocorra um erro durante o registo, exibe uma mensagem de erro
        echo "<script>
            alert('Não foi possível efetuar o registo!');
            </script>";
        echo "<p>Query: " . htmlspecialchars($query) . "<br>Erro: " . htmlspecialchars(mysqli_error($liga)) . "</p>";
        echo "<script>
            window.location.href='../Login.html';
            </script>";
        // Redireciona para a página de login
    }
}
// Fecha a ligação à base de dados
mysqli_close($liga);
