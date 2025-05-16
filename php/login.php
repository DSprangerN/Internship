<?php
session_start();

include 'ligaBD.php';

$username = isset($_POST['username']) ? trim($_POST['username']) : null;
$password = isset($_POST['password']) ? trim($_POST['password']) : null;

// Verifica se os campos foram preenchidos
if (empty($username) || empty($password)) {
    echo "<script>
        alert('Por favor, preencha todos os campos!');
        window.location.href='../Login.html';
        </script>";
    exit();
}

// Script SQL para verificar se o username existe na base de dados
$query = "SELECT * FROM users_login WHERE username = ?";
$stmt = mysqli_prepare($liga, $query);
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    // Se o utilizador existir, verifica a senha
    $user = mysqli_fetch_assoc($result);
    if ($user['Password'] && password_verify($password, $user['Password'])) {
        // Senha correta
        $_SESSION['username'] = $user['Username'];
        $_SESSION['user_id'] = $user['id_user'];

        if ($user['Username'] === 'mvicente') {
            echo "<script>
                alert('Login efetuado com sucesso!');
                window.location.href='../php/admin.php';
                </script>";
        } else {
            echo "<script>
            alert('Login efetuado com sucesso!');
            window.location.href='../php/user.php';
            </script>";
        }
    } else {
        // Senha incorreta
        echo "<script>
            alert('Senha incorreta. Por favor, tente novamente.');
            window.location.href='../php/Login.php';
            </script>";
    }
} else {
    // Username não encontrado
    echo "<script>
        alert('Este utilizador não está registado, por favor efetue o registo!');
        window.location.href='../php/Login.php';
        </script>";
}

mysqli_close($liga); // Fecha a ligação à base de dados
