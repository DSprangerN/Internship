<?php
session_start(); // Inicia a sessão
include 'ligaBD.php'; // Inclui o ficheiro de ligação à base de dados

if ($_SESSION['username'] !== 'mvicente') {
    echo "<script>alert('Acesso restrito!');
    window.location.href='../Login.html';</script>";
    exit();
}


// Verifica se o utilizador está logado
if (!isset($_SESSION['username']) || !isset($_SESSION['user_id'])) {
    echo "<script>
        alert('Por favor, faça login primeiro.');
        window.location.href='../php/Login.php';
        </script>";
    exit();
}

$colaboradores = [];
$query = "SELECT id_user, Nome, Sobrenome FROM users_login ORDER BY Nome, Sobrenome";
$result = mysqli_query($liga, $query);
while ($row = mysqli_fetch_assoc($result)) {
    $colaboradores[] = [
        'id' => $row['id_user'],
        'nome' => $row['Nome'] . ' ' . $row['Sobrenome']
    ];
}

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

//Ano atual
$ano_atual = date('Y');

//Filtros de pesquisa
$colaboradoresSelecionados = isset($_GET['colaborador']) ? $_GET['colaborador'] : [];
$mes_inicio = $_GET['mes_inicio'] ?? 1;
$ano_inicio = $_GET['ano_inicio'] ?? $ano_atual;
$mes_fim = $_GET['mes_fim'] ?? 12;
$ano_fim = $_GET['ano_fim'] ?? $ano_atual;

//Acesso à base de dados para extrair os colaboradores
$banco_horas = [];
if (!empty($colaboradoresSelecionados)) {
    $placeholders = implode(',', array_fill(0, count($colaboradoresSelecionados), '?'));
    $tipos = str_repeat('i', count($colaboradoresSelecionados)) . 'iiiiii';
    $params = array_merge($colaboradoresSelecionados, [$ano_inicio, $ano_inicio, $mes_inicio, $ano_fim, $ano_fim, $mes_fim]);

    $query = "SELECT a.Nome, a.Sobrenome, b.Data, b.Hora_Entrada, b.Hora_Saida FROM registo_horas b
              JOIN users_login a ON b.id_user = a.id_user
              WHERE b.id_user IN ($placeholders)
              AND (
                    (YEAR(b.Data) > ? OR (YEAR(b.Data) = ? AND MONTH(b.Data) >= ?))
              AND 
                    (YEAR(b.Data) < ? OR (YEAR(b.Data) = ? AND MONTH(b.Data) <= ?))
                )
                ORDER BY a.Nome, b.Data";
    $stmt = mysqli_prepare($liga, $query);

    //Bind dinâmico dos parâmetros
    $bind_params = [];
    $bind_params[] = $tipos;
    foreach ($params as $key => $value) {
        $bind_params[] = &$params[$key];
    }
    call_user_func_array([$stmt, 'bind_param'], $bind_params);

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $entrada = $row['Hora_Entrada'];
        $saida = $row['Hora_Saida'];
        $entrada_dt = DateTime::createFromFormat('H:i:s', $entrada);
        $saida_dt = DateTime::createFromFormat('H:i:s', $saida);
        $intervalo = $entrada_dt && $saida_dt ? $entrada_dt->diff($saida_dt) : null;
        $horas_trabalhadas = $intervalo ? ($intervalo->h + $intervalo->i / 60) : 0;

        $banco_horas[] = [
            'colaborador' => $row['Nome'] . ' ' . $row['Sobrenome'],
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/multiple-select@1.7.0/dist/multiple-select.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/multiple-select@1.7.0/dist/multiple-select.min.js"></script>
</head>

<style>
    @media (max-width: 768px) {
        body {
            width: 100%;
            margin: 0 auto;
            padding-left: 5px;
            padding-right: 5px;
        }
    }

    body {
        font-family: "Lato", sans-serif;
        margin: auto;
        text-align: center;
        background-color: #f0f8ff;
        overflow-x: hidden;
    }

    h1 {
        margin: auto;
        text-align: center;
        font-size: 36px;
        color: #003366;
    }

    /* Estilo para o Multiple Select igual ao Bootstrap */
    .ms-parent {
        width: 100% !important;
    }

    .ms-choice {
        height: 38px;
        /* igual ao .form-select do Bootstrap */
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
        padding: 0.375rem 0.75rem;
        font-size: 1rem;
        line-height: 1.5;
        background-color: #fff;
        color: #212529;
        box-shadow: none;
        transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
    }
</style>

<body>
    <div class="container mt-3">
        <div class="d-flex justify-content-start">
            <a href="../Login.html" class="btn btn-danger logout-button">Log Out</a>
        </div>
    </div>
    <br>

    <div class="container mt-5">
        <h1 class="mb-4">Gestão de Horas</h1>
        <br>
        <form method="get" class="row g-2 align-items-end mb-4">
            <div class="col-auto d-flex flex-column">
                <label for="ano_inicio" class="form-label">Ano Início</label>
                <select name="ano_inicio" id="ano_inicio" class="form-select">
                    <?php for ($a = $ano_atual - 0; $a <= $ano_atual + 100; $a++): ?>
                        <option value="<?= $a ?>" <?= ($ano_inicio == $a) ? 'selected' : '' ?>><?= $a ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-auto d-flex flex-column">
                <label for="mes_inicio" class="form-label">Mês Início</label>
                <select name="mes_inicio" id="mes_inicio" class="form-control">
                    <?php foreach ($meses as $m => $nome): ?>
                        <option value="<?= $m ?>" <?= ($mes_inicio == $m) ? 'selected' : '' ?>><?= $nome ?></option>
                    <?php endforeach ?>
                </select>
            </div>
            <div class="col-auto d-flex flex-column">
                <label for="ano_fim" class="form-label">Ano Fim</label>
                <select name="ano_fim" id="ano_fim" class="form-select">
                    <?php for ($a = $ano_atual - 0; $a <= $ano_atual + 100; $a++): ?>
                        <option value="<?= $a ?>" <?= ($ano_fim == $a) ? 'selected' : '' ?>><?= $a ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-auto d-flex flex-column">
                <label for="mes_fim" class="form-label">Mês Fim</label>
                <select name="mes_fim" id="mes_fim" class="form-control">
                    <?php foreach ($meses as $m => $nome) : ?>
                        <option value="<?= $m ?>" <?= ($mes_fim == $m) ? 'selected' : '' ?>><?= $nome ?></option>
                    <?php endforeach ?>
                </select>
            </div>
            <div class="col-auto d-flex flex-column">
                <label for="colaborador" class="form-label">Colaboradores</label>
                <select multiple="multiple" size="10" name="colaborador[]" id="colaborador">
                    <?php foreach ($colaboradores as $col): ?>
                        <option value="<?= $col['id'] ?>" <?= (isset($colaboradoresSelecionados) && in_array($col['id'], (array)$colaboradoresSelecionados)) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($col['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <br>
            <div class="col-auto">
                <button type="submit" class="btn btn-secondary">Ver Banco de Horas</button>
            </div>
        </form>
        <br>
        <div id="banco_horas" class="mb-4">
            <h3>Banco de horas</h3>
            <?php if (!empty($banco_horas)): ?>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Colaborador</th>
                            <th>Data</th>
                            <th>Entrada</th>
                            <th>Saída</th>
                            <th>Horas Trabalhadas</th>
                            <th>Saldo de Horas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $saldo_total = 0;
                        foreach ($banco_horas as $linha):
                            $saldo_total += $linha['saldo'];
                        ?>
                            <tr>
                                <td><?= $linha['colaborador'] ?></td>
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
                            <th colspan="5">Saldo Total</th>
                            <th><?= number_format($saldo_total ?? 0, 2, ',', '') ?></th>
                        </tr>
                    </tfoot>
                </table>
            <?php else: ?>
                <p>Selecione pelo menos um colaborador e um intervalo de datas para ver os registos</p>
            <?php endif; ?>
        </div>
    </div>
    <script>
        $(function() {
            $('#colaborador').multipleSelect({
                filter: true,
                width: '100%'
            });
        });
    </script>
</body>

</html>