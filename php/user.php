<?php
session_start();
include 'ligaBD.php';

$mensagem = '';
$mensagem_tipo = ''; // success, danger, warning, info

// Função para evitar SQL duplicado ao buscar registo do dia
function obter_registo_dia($liga, $user_id, $data)
{
    $query = "SELECT Hora_Entrada, Hora_Saida FROM registo_horas WHERE id_user = ? AND Data = ?";
    $stmt = mysqli_prepare($liga, $query);
    mysqli_stmt_bind_param($stmt, "is", $user_id, $data);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

// Verifica se o utilizador está logado
if (!isset($_SESSION['username']) || !isset($_SESSION['user_id'])) {
    $_SESSION['mensagem'] = 'Por favor, faça login primeiro.';
    $_SESSION['mensagem_tipo'] = 'warning';
    header('Location: ../php/Login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Verifica se o utilizador está na rede Wi-Fi permitida (IP Local)
$allowed_public_ip = '95.92.13.189'; // Substituir pelo IP da escola
$userIP = $_SERVER['REMOTE_ADDR'];

if (
    $userIP !== $allowed_public_ip &&
    $userIP !== '127.0.0.1' &&
    $userIP !== '::1'
) {
    $_SESSION['mensagem'] = 'Erro ao verificar a ligação Wi-Fi. Tente novamente mais tarde.';
    $_SESSION['mensagem_tipo'] = 'danger';
    header('Location: ../Login.html');
    exit();
}

date_default_timezone_set('Europe/Lisbon');

// --- Tabela de registo instantâneo ---
$mostrar_registo_instantaneo = false;
$registo_instantaneo = [];

// Registo de Horas
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = $_POST['data_registo'] ?? date('Y-m-d');
    $hora_atual = date('Y-m-d H:i:s');

    // Entrada
    if (isset($_POST['registar_entrada'])) {
        $query = "SELECT * FROM registo_horas WHERE id_user = ? AND Data = ?";
        $stmt = mysqli_prepare($liga, $query);
        mysqli_stmt_bind_param($stmt, "is", $user_id, $data);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            $mensagem = 'Já existe uma entrada registada para este dia.';
            $mensagem_tipo = 'warning';
        } else {
            $query = "INSERT INTO registo_horas (id_user, Data, Hora_Entrada) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($liga, $query);
            mysqli_stmt_bind_param($stmt, "iss", $user_id, $data, $hora_atual);
            if (mysqli_stmt_execute($stmt)) {
                $mensagem = 'Entrada registada com sucesso.';
                $mensagem_tipo = 'success';
            } else {
                $mensagem = 'Erro ao registar entrada.';
                $mensagem_tipo = 'danger';
            }
        }
        // Buscar registo atualizado para mostrar na tabela
        $row = obter_registo_dia($liga, $user_id, $data);
        $mostrar_registo_instantaneo = true;
        $registo_instantaneo = [
            'data' => $data,
            'entrada' => $row['Hora_Entrada'] ? htmlspecialchars(date('H:i', strtotime($row['Hora_Entrada']))) : '',
            'saida' => $row['Hora_Saida'] ? htmlspecialchars(date('H:i', strtotime($row['Hora_Saida']))) : ''
        ];
    }

    // Saída
    if (isset($_POST['registar_saida'])) {
        $query = "SELECT * FROM registo_horas WHERE id_user = ? AND Data = ?";
        $stmt = mysqli_prepare($liga, $query);
        mysqli_stmt_bind_param($stmt, "is", $user_id, $data);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) == 0) {
            $mensagem = 'Por favor, registe a entrada antes de continuar.';
            $mensagem_tipo = 'warning';
        } else {
            $query = "UPDATE registo_horas SET Hora_Saida = ? WHERE id_user = ? AND Data = ?";
            $stmt = mysqli_prepare($liga, $query);
            mysqli_stmt_bind_param($stmt, "sis", $hora_atual, $user_id, $data);
            if (mysqli_stmt_execute($stmt)) {
                $mensagem = 'Saída registada com sucesso!';
                $mensagem_tipo = 'success';
            } else {
                $mensagem = 'Erro ao registar a saída.';
                $mensagem_tipo = 'danger';
            }
        }
        // Buscar registo atualizado para mostrar na tabela
        $row = obter_registo_dia($liga, $user_id, $data);
        $mostrar_registo_instantaneo = true;
        $registo_instantaneo = [
            'data' => $data,
            'entrada' => ($row && $row['Hora_Entrada']) ? htmlspecialchars(date('H:i', strtotime($row['Hora_Entrada']))) : '',
            'saida' => ($row && $row['Hora_Saida']) ? htmlspecialchars(date('H:i', strtotime($row['Hora_Saida']))) : ''
        ];
    }
}

// --- Banco de Horas ---
$banco_horas = [];
$mostrar_banco_horas = false;
$erro_intervalo = null;
$ano_atual = date('Y');
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

if (
    isset($_GET['ano_inicio']) && isset($_GET['mes_inicio']) &&
    isset($_GET['ano_fim']) && isset($_GET['mes_fim'])
) {
    $ano_inicio = $_GET['ano_inicio'];
    $mes_inicio = $_GET['mes_inicio'];
    $ano_fim = $_GET['ano_fim'];
    $mes_fim = $_GET['mes_fim'];

    $data_inicio = sprintf('%04d-%02d-01', $ano_inicio, $mes_inicio);
    $ultimo_dia = date('t', strtotime("$ano_fim-$mes_fim-01"));
    $data_fim = sprintf('%04d-%02d-%02d', $ano_fim, $mes_fim, $ultimo_dia);

    // Validação do intervalo
    if (strtotime($data_inicio) > strtotime($data_fim)) {
        $erro_intervalo = "O intervalo de datas é inválido.";
    } else {
        $query = "SELECT Data, Hora_Entrada, Hora_Saida FROM registo_horas 
                  WHERE id_user = ? AND Data BETWEEN ? AND ? ORDER BY Data";
        $stmt = mysqli_prepare($liga, $query);
        mysqli_stmt_bind_param($stmt, "iss", $user_id, $data_inicio, $data_fim);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        while ($row = mysqli_fetch_assoc($result)) {
            $entrada = $row['Hora_Entrada'];
            $saida = $row['Hora_Saida'];

            $entrada_dt = $entrada ? new DateTime($entrada) : null;
            $saida_dt = $saida ? new DateTime($saida) : null;
            $intervalo = ($entrada_dt && $saida_dt) ? $entrada_dt->diff($saida_dt) : null;

            if ($intervalo) {
                $total_minutos = ($intervalo->h * 60 + $intervalo->i + floor($intervalo->s / 60)) - 60;
                if ($total_minutos < 0) $total_minutos = 0;
                $total_minutos_arredondado = ceil($total_minutos / 30) * 30;
                $horas_trabalhadas_h = floor($total_minutos / 60);
                $horas_trabalhadas_m = $total_minutos_arredondado % 60;
                $horas_trabalhadas_str = sprintf('%dh%02d', $horas_trabalhadas_h, $horas_trabalhadas_m);
                $horas_trabalhadas_decimal = $total_minutos_arredondado / 60;
                $saldo = $horas_trabalhadas_decimal - 8;
            } else {
                $horas_trabalhadas_str = '0h00';
                $saldo = 0;
            }
            $banco_horas[] = [
                'data' => $row['Data'],
                'entrada' => $entrada,
                'saida' => $saida,
                'horas' => $horas_trabalhadas_str,
                'saldo' => $saldo
            ];
        }
        $mostrar_banco_horas = true;
    }
}

mysqli_close($liga);
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registo de Horas</title>
    <link rel="icon" type="image/x-icon" href="../img/favicon.ico">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
</head>

<style>
    @media (max-width: 768px) {
        body {
            width: 100%;
            margin: 0 auto;
        }
    }

    body {
        font-family: "Lato", sans-serif;
        margin: auto;
        text-align: center;
        background-color: #f0f8ff;
        overflow-x: hidden;
    }

    header {
        position: relative;
        padding: 20px 0;
    }

    h1 {
        margin: auto;
        text-align: center;
        font-size: 36px;
        color: #003366;
    }

    h2 {
        font-size: 36px;
        color: #003366;
        margin: 20px 0;
    }
</style>

<body>

    <?php if (isset($_SESSION['mensagem']) && isset($_SESSION['mensagem_tipo'])): ?>
        <div class="alert alert-<?= $_SESSION['mensagem_tipo'] ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['mensagem']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    <?php
        unset($_SESSION['mensagem'], $_SESSION['mensagem_tipo']);
    endif;
    ?>

    <div class="container mt-3">
        <div class="d-flex justify-content-start">
            <a href="logout.php" class="btn btn-danger logout-button">Log Out</a>
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
                    <input type="text" id="data_registo" name="data_registo" class="form-control" placeholder="AAAA-MM-DD" required autocomplete="off">
                </div>
                <div class="col-auto">
                    <button type="submit" name="registar_entrada" class="btn btn-success">Entrada</button>
                </div>
                <div class="col-auto">
                    <button type="submit" name="registar_saida" class="btn btn-warning">Saída</button>
                </div>
            </div>
        </form>

        <!-- Tabela de registo instantâneo -->
        <?php if ($mostrar_registo_instantaneo && !empty($registo_instantaneo)): ?>
            <div class="mb-4">
                <h4>Registo do Dia</h4>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Dia</th>
                            <th>Hora de Entrada</th>
                            <th>Hora de Saída</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?= htmlspecialchars($registo_instantaneo['data']) ?></td>
                            <td><?= $registo_instantaneo['entrada'] ?></td>
                            <td><?= $registo_instantaneo['saida'] ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <div class="mb-4">
            <form method="get" class="row g-2 align-items-end">
                <div class="col-auto">
                    <label for="ano_inicio" class="form-label">Ano Início</label>
                    <select id="ano_inicio" name="ano_inicio" class="form-select">
                        <?php
                        $ano_atual = date('Y');
                        for ($a = $ano_atual - 0; $a <= $ano_atual + 30; $a++): ?>
                            <option value="<?= $a ?>" <?= (isset($_GET['ano_inicio']) && $_GET['ano_inicio'] == $a) ? 'selected' : '' ?>>
                                <?= $a ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <label for="mes_inicio" class="form-label">Mês Início</label>
                    <select id="mes_inicio" name="mes_inicio" class="form-control">
                        <?php
                        for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?= $m ?>" <?= (isset($_GET['mes_inicio']) && $_GET['mes_inicio'] == $m) ? 'selected' : '' ?>>
                                <?= $meses[$m] ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <label for="ano_fim" class="form-label">Ano Fim</label>
                    <select id="ano_fim" name="ano_fim" class="form-select">
                        <?php
                        for ($a = $ano_atual - 0; $a <= $ano_atual + 30; $a++): ?>
                            <option value="<?= $a ?>" <?= (isset($_GET['ano_fim']) && $_GET['ano_fim'] == $a) ? 'selected' : '' ?>>
                                <?= $a ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <label for="mes_fim" class="form-label">Mês Fim</label>
                    <select id="mes_fim" name="mes_fim" class="form-control">
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?= $m ?>" <?= (isset($_GET['mes_fim']) && $_GET['mes_fim'] == $m) ? 'selected' : '' ?>>
                                <?= $meses[$m] ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-secondary">Ver Banco de Horas</button>
                </div>
            </form>
        </div>

        <?php if (isset($erro_intervalo)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($erro_intervalo) ?></div>
        <?php endif; ?>

        <?php if ($mostrar_banco_horas): ?>
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
                                    <td><?= htmlspecialchars($linha['data']) ?></td>
                                    <td><?= isset($linha['entrada']) && $linha['entrada'] ? htmlspecialchars(date('H:i', strtotime($linha['entrada']))) : '' ?></td>
                                    <td><?= isset($linha['saida']) && $linha['saida'] ? htmlspecialchars(date('H:i', strtotime($linha['saida']))) : '' ?></td>
                                    <td><?= htmlspecialchars($linha['horas']) ?></td>
                                    <td><?= number_format($linha['saldo'] ?? 0, 2, ',', '') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="4">Saldo Total do Período</th>
                                <th><?= number_format($saldo_total ?? 0, 2, ',', '') ?></th>
                            </tr>
                        </tfoot>
                    </table>
                <?php else: ?>
                    <p>Não existem registos para o intervalo selecionado.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <footer>
        <p>
            <a href="../HTML/PT/privacidade.html">Política de Privacidade</a>
        </p>
    </footer>

    <script>
        $(document).ready(function() {
            $('#data_registo').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
                todayHighlight: true
            });

            // Validação do intervalo de datas no formulário do banco de horas
            $('form[method="get"]').on('submit', function(e) {
                const anoInicio = parseInt($('#ano_inicio').val());
                const mesInicio = parseInt($('#mes_inicio').val());
                const anoFim = parseInt($('#ano_fim').val());
                const mesFim = parseInt($('#mes_fim').val());
                if (anoInicio > anoFim || (anoInicio === anoFim && mesInicio > mesFim)) {
                    alert('O intervalo de datas é inválido.');
                    e.preventDefault();
                }
            });
        });
    </script>
</body>

</html>