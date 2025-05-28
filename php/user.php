<?php
session_start(); // Inicia a sessão
include 'ligaBD.php'; // Inclui o ficheiro de ligação à base de dados


// Verifica se o utilizador está logado
if (!isset($_SESSION['username']) || !isset($_SESSION['user_id'])) {
    echo "<script>
        alert('Por favor, faça login primeiro.');
        window.location.href='../php/Login.php';
        </script>";
    exit();
}

$user_id = $_SESSION['user_id'];


// Verifica se o utilizador está na rede Wi-Fi permitida (IP Local)
// Verificação em php por ser mais seguro no servidor que no cliente
$allowed_public_ip = '95.92.13.189'; // Subsituir pelo IP da escola
$userIP = $_SERVER['REMOTE_ADDR'];
echo "<script>alert('O seu IP é: $userIP');</script>";

if (
    $userIP !== $allowed_public_ip &&
    $userIP !== '127.0.0.1' &&
    $userIP !== '::1'
) {
    echo "<script>
        alert('Erro ao verificar o Wifi. Tente novamente mais tarde.');
        window.location.href = '../Login.html';
        </script>";
    exit();
}

// Registo de Horas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registar_horas'])) {
    $data = $_POST['data_registo'];
    $hora_entrada = $_POST['hora_entrada'];
    $hora_saida = $_POST['hora_saida'];

    //Verificar se já existem dados para a data selecionada
    $query = "SELECT * FROM registo_horas WHERE id_user = ? AND Data = ?";
    $stmt = mysqli_prepare($liga, $query);
    mysqli_stmt_bind_param($stmt, "is", $user_id, $data);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        echo "<script>alert('Já existe um registo de horas para esta data.');</script>";
    } else {
        if (!$hora_entrada || !$hora_saida) {
            echo "<script>alert('Preencha ambas as horas.');</script>";
        } else {
            $query = "INSERT INTO registo_horas (id_user, Data, Hora_Entrada, Hora_Saida) VALUES (?,?,?,?)";
            $stmt = mysqli_prepare($liga, $query);
            mysqli_stmt_bind_param($stmt, "isss", $user_id, $data, $hora_entrada, $hora_saida);
            if (mysqli_stmt_execute($stmt)) {
                echo "<script>alert('Horas registadas com sucesso!');</script>";
            } else {
                echo "<script>alert('Erro ao registar horas.');</script>";
            }
        }
    }
}

// Banco de Horas
$banco_horas = [];
if (isset($_GET['mes'])) {
    $mes = $_GET['mes'];
    $ano = $_GET['ano'];
    $query = "SELECT Data, Hora_Entrada, Hora_Saida FROM registo_horas WHERE id_user = ? AND MONTH(Data) = ? AND YEAR(Data) = ?";
    $stmt = mysqli_prepare($liga, $query);
    mysqli_stmt_bind_param($stmt, "iii", $user_id, $mes, $ano);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $entrada = $row['Hora_Entrada'];
        $saida = $row['Hora_Saida'];
        $ano = $row['Data'];

        //Horas trabalhadas
        $entrada_dt = Datetime::createFromFormat('H:i:s', $entrada);
        $saida_dt = Datetime::createFromFormat('H:i:s', $saida);
        $intervalo = $entrada_dt && $saida_dt ? $entrada_dt->diff($saida_dt) : null;
        $horas_trabalhadas = $intervalo ? ($intervalo->h + $intervalo->i / 60 - 1) : 0; //-1hora de almoço

        $banco_horas[] = [
            'data' => $row['Data'],
            'entrada' => $entrada,
            'saida' => $saida,
            'horas' => $horas_trabalhadas,
            'saldo' => $horas_trabalhadas - 8
        ];
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registo de Horas</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
    <link rel="icon" type="image/x-icon" href="../img/favicon.ico">
</head>

<style>
    @media (max-width: 768px) {
        body {
            width: 100%;
            /* Ocupa toda a largura da tela em dispositivos menores */
            margin: 0 auto;
            /* Centraliza o elemento */
        }
    }

    body {
        font-family: "Lato", sans-serif;
        margin: auto;
        text-align: center;
        background-color: #f0f8ff;
        overflow-x: hidden;
    }

    /* Estilo para o cabeçalho */
    header {
        position: relative;
        padding: 20px 0;
    }

    /* Estilo para o título principal (h1) */
    h1 {
        margin: auto;
        /* Centraliza o título horizontalmente */
        text-align: center;
        /* Centraliza o texto */
        font-size: 36px;
        /* Define o tamanho da fonte */
        color: #003366;
        /* Define a cor do texto em azul escuro */
    }

    /* Estilo para o título principal */
    h2 {
        font-size: 36px;
        color: #003366;
        margin: 20px 0;
    }
</style>

<body>
    <div class="container mt-3">
        <div class="d-flex justify-content-start">
            <a href="../Login.html" class="btn btn-danger logout-button">Log Out</a>
        </div>
    </div>
    <img src="../img/Logo_Estrelinha-Amarela.png" alt="Logo Estrelinha Amarela" height="200px" width="200px">
    <br>
    <div class="container mt-5">
        <h1 class="mb-4">Registo de Horas</h1>
        <form method="post" action="user.php" class="mb-4">
            <div class="row g-2 align-items-end">
                <div class="col-auto">
                    <label for="data_registo" class="form-label">Dia</label>
                    <input type="text" id="data_registo" name="data_registo" class="form-control" required autocomplete="off" readonly>
                </div>
                <div class="col-auto">
                    <label for="hora_entrada" class="form-label">Hora de Entrada</label>
                    <input type="time" id="hora_entrada" name="hora_entrada" class="form-control" required>
                </div>
                <div class="col-auto">
                    <label for="hora_saida" class="form-labe">Hora de Saída</label>
                    <input type="time" id="hora_saida" name="hora_saida" class="form-control" required>
                </div>
                <div class="col-auto">
                    <button type="submit" name="registar_horas" class="btn btn-primary">Registar</button>
                </div>
            </div>
        </form>

        <div class="mb-4">
            <form method="get" class="row g-2 align-items-end">
                <div class="col-auto">
                    <label for="mes" class="form-label">Mês</label>
                    <select id="mes" name="mes" class="form-control">
                        <?php
                        $meses = [
                            1 => 'Janeiro',
                            2 => 'Fevereiro',
                            3 => 'Março',
                            4 => 'Abril',
                            5 => 'Maio',
                            6 => 'Junho',
                            7 => 'Julho',
                            8 => 'Agosto',
                            9 => 'Setembro',
                            10 => 'Outubro',
                            11 => 'Novembro',
                            12 => 'Dezembro'
                        ];
                        for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?= $m ?>" <?= (isset($_GET['mes']) && $_GET['mes'] == $m) ? 'selected' : '' ?>>
                                <?= $meses[$m] ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <label for="ano" class="form-label">Ano</label>
                    <select name="ano" id="ano" class="form-select">
                        <?php
                        $ano_atual = date('Y');
                        for ($a = $ano_atual - 0; $a <= $ano_atual + 30; $a++): ?>
                            <option value="<?= $a ?>" <?= (isset($_GET['ano']) && $_GET['ano'] == $a) ? 'selected' : '' ?>>
                                <?= $a ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-secondary">Ver Banco de Horas</button>
                </div>
            </form>
        </div>

        <div id="banco_horas" class="mb-4">
            <h3>Banco de Horas</h3>
            <?php if (!empty($banco_horas)): ?>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Entrada</th>
                            <th>Saída</th>
                            <th>Horas Trabalhadas</th>
                            <th>Saldo do Dia</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $saldo_total = 0;
                        foreach ($banco_horas as $linha):
                            $saldo_total += $linha['saldo'];
                        ?>
                            <tr>
                                <td><?= $linha['data'] ?></td>
                                <td><?= isset($linha['entrada']) ? substr($linha['entrada'], 0, 5) : '-' ?></td>
                                <td><?= isset($linha['saida']) ? substr($linha['saida'], 0, 5) : '-' ?></td>
                                <td><?= number_format($linha['horas'] ?? 0, 2, ',', '') ?></td>
                                <td><?= number_format($linha['saldo'] ?? 0, 2, ',', '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="4">Saldo Total do Mês</th>
                            <th><?= number_format($saldo_total ?? 0, 2, ',', '') ?></th>
                        </tr>
                    </tfoot>
                </table>
            <?php else: ?>
                <p>Selecione um mês e ano para ver os seus registos</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#data_registo').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
                todayHighlight: true
            });
        });
    </script>
</body>

<footer>
    <a href="../HTML/PT/privacidade.html">Política de Privacidade</a>
</footer>

</html>