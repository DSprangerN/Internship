<?php
session_start(); // Inicia a sessão
include 'ligaBD.php'; // Inclui o ficheiro de ligação à base de dados

if ($_SESSION['username'] !== 'mvicente') {
    echo "<script>alert('Acesso restrito!');
    window.location.href='../Login.html';</script>";
    exit();
}


// Verifica se o utilizador tem sessão iniciada
if (!isset($_SESSION['username']) || !isset($_SESSION['user_id'])) {
    echo "<script>
        alert('Por favor, faça login primeiro.');
        window.location.href='../php/Login.php';
        </script>";
    exit();
}

$colaboradores = [];
$query = "SELECT id_user, Nome, Sobrenome, Ativo FROM users_login ORDER BY Nome, Sobrenome";
$result = mysqli_query($liga, $query);
while ($row = mysqli_fetch_assoc($result)) {
    $colaboradores[] = [
        'id' => $row['id_user'],
        'nome' => $row['Nome'] . ' ' . $row['Sobrenome'],
        'ativo' => $row['Ativo']
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

//Filtros de pesquisa tablea banco de horas
$colaboradoresSelecionados = isset($_GET['colaborador']) ? $_GET['colaborador'] : [];
$mes_inicio = $_GET['mes_inicio'] ?? 1;
$ano_inicio = $_GET['ano_inicio'] ?? $ano_atual;
$mes_fim = $_GET['mes_fim'] ?? 12;
$ano_fim = $_GET['ano_fim'] ?? $ano_atual;

// Filtros de pesquisa tabela horas a compensar

$ano_inicio_gozo = $_GET['ano_inicio_gozo'] ?? $ano_atual;
$mes_inicio_gozo = $_GET['mes_inicio_gozo'] ?? 1;
$mes_fim_gozo = $_GET['mes_fim_gozo'] ?? 12;
$ano_fim_gozo = $_GET['ano_fim_gozo'] ?? $ano_atual;
$colaboradores_gozo = isset($_GET['colaborador_gozo']) ? (array)$_GET['colaborador_gozo'] : [];

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
        $entrada_dt = (!empty($entrada)) ? new DateTime($entrada) : null;
        $saida_dt = (!empty($saida)) ? new DateTime($saida) : null;
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

//Acesso à base de dados para extrair as horas a compensar
$horas_compensadas_lista = [];
$total_compensadas = 0;
if (!empty($colaboradores_gozo)) {
    $placeholders = implode(',', array_fill(0, count($colaboradores_gozo), '?'));
    $data_inicio = sprintf('%04d-%02d-01', $ano_inicio_gozo, $mes_inicio_gozo);
    $data_fim = sprintf('%04d-%02d-31', $ano_fim_gozo, $mes_fim_gozo);
    $query = "SELECT c.id_descontos, c.Data, c.Horas, d.Nome, d.Sobrenome FROM horas_descontadas c
              JOIN users_login d ON c.id_user = d.id_user
              WHERE c.id_user IN ($placeholders)
              AND c.Data BETWEEN ? AND ?
              ORDER BY c.Data";
    $stmt = mysqli_prepare($liga, $query);

    // Bind dinâmico dos parâmetros
    $tipos = str_repeat('i', count($colaboradores_gozo)) . 'ss';
    $params = array_merge($colaboradores_gozo, [$data_inicio, $data_fim]);
    $bind_params = [];
    $bind_params[] = $tipos;
    foreach ($params as $key => $value) {
        $bind_params[] = &$params[$key];
    }
    call_user_func_array([$stmt, 'bind_param'], $bind_params);

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $horas = date('H:i', strtotime($row['Horas']));
        list($h, $m) = explode(':', $horas);
        $horas_decimal = $h + ($m / 60);
        $horas_compensadas_lista[] = [
            'id_descontos' => $row['id_descontos'],
            'colaborador' => $row['Nome'] . ' ' . $row['Sobrenome'],
            'data' => $row['Data'],
            'horas' => $horas_decimal
        ];
        $total_compensadas += $horas_decimal;
    }
}

$mensagem_compensacao = '';
$mensagem_tipo_compensacao = '';
$mensagem_colaboradores = '';
$mensagem_tipo_colaboradores = '';

// Atualizar a horas compensadas na base de dados
if (isset($_POST['inserir_compensacao'])) {
    $colaborador_insert = $_POST['colaborador_compensado_insert'];
    $data_insert = $_POST['data_compensada_insert'];
    $horas_insert = $_POST['horas_compensadas_insert'];

    // Verificar duplicados
    $query_check = "SELECT COUNT(*) as total FROM horas_descontadas WHERE id_user = ? AND Data = ?";
    $stmt_check = mysqli_prepare($liga, $query_check);
    mysqli_stmt_bind_param($stmt_check, 'is', $colaborador_insert, $data_insert);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);
    $row_check = mysqli_fetch_assoc($result_check);

    if ($row_check['total'] > 0) {
        $mensagem_compensacao = "Já existe uma compensação para este colaborador nesta data.";
        $mensagem_tipo_compensacao = "danger";
    } else {

        //Formatar para formato HH:MM
        $horas_int = floor($horas_insert);
        $minutos = round(($horas_insert - $horas_int) * 60);
        $horas_format = sprintf('%02d:%02d:00', $horas_int, $minutos);

        $query = "INSERT INTO horas_descontadas (id_user, Data, Horas) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($liga, $query);
        mysqli_stmt_bind_param($stmt, 'iss', $colaborador_insert, $data_insert, $horas_format);
        if (mysqli_stmt_execute($stmt)) {
            $mensagem_compensacao = "Compensação inserida com sucesso!";
            $mensagem_tipo_compensacao = "success";
        } else {
            $mensagem_compensacao = "Erro ao inserir compensação.";
            $mensagem_tipo_compensacao = "danger";
        }
    }
}

// Eliminar Horas a Compensar

if (isset($_POST['eliminar_compensacao']) && isset($_POST['id_descontos'])) {
    $id_descontos = intval($_POST['id_descontos']);
    $query = "DELETE FROM horas_descontadas WHERE id_descontos = ?";
    $stmt = mysqli_prepare($liga, $query);
    mysqli_stmt_bind_param($stmt, 'i', $id_descontos);
    if (mysqli_stmt_execute($stmt)) {
        $mensagem_compensacao = "Registo eliminado com sucesso!";
        $mensagem_tipo_compensacao = "success";
    } else {
        $mensagem_compensacao = "Erro ao eliminar o registo.";
        $mensagem_tipo_compensacao = "danger";
    }
}


// Eliminar registos de colaboradores
if (isset($_POST['eliminar_registo']) && isset($_POST['id_colaborador'])) {
    $id_colaborador = intval($_POST['id_colaborador']);
    //Não permite eliminar o administrador
    if ($id_colaborador == $_SESSION['user_id']) {
        $mensagem_colaboradores = "Não pode eliminar o seu próprio registo!";
        $mensagem_tipo_colaboradores = "danger";
    } else {
        $query = "DELETE FROM users_login WHERE id_user= ?";
        $stmt = mysqli_prepare($liga, $query);
        mysqli_stmt_bind_param($stmt, 'i', $id_colaborador);
        if (mysqli_stmt_execute($stmt)) {
            $mensagem_colaboradores = "Registo eliminado com sucesso!";
            $mensagem_tipo_colaboradores = "success";
        } else {
            $mensagem_colaboradores = "Erro ao eliminar registo.";
            $mensagem_tipo_colaboradores = "danger";
        }
    }
}

// Ativar colaborador
if (isset($_POST['ativar_colaborador']) && isset($_POST['id_colaborador'])) {
    $id_colaborador = intval($_POST['id_colaborador']);
    $query = "UPDATE users_login SET ativo = 1 WHERE id_user = ?";
    $stmt = mysqli_prepare($liga, $query);
    mysqli_stmt_bind_param($stmt, 'i', $id_colaborador);
    if (mysqli_stmt_execute($stmt)) {
        $mensagem_colaboradores = "Colaborador ativado com sucesso!";
        $mensagem_tipo_colaboradores = "success";
    } else {
        $mensagem_colaboradores = "Erro ao ativar colaborador.";
        $mensagem_tipo_colaboradores = "danger";
    }
}

// --- Upload da ementa (imagem ou PDF) ---
$mensagem_ementa = '';
$mensagem_tipo_ementa = '';
$ementa_html = '';

if (isset($_POST['upload_ementa']) && isset($_FILES['ementa_img'])) {
    $file = $_FILES['ementa_img'];
    $permitidos = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if ($file['error'] === 0 && in_array($file['type'], $permitidos)) {
        $nome_final = 'ementa_' . date('Ymd_His') . '.' . $ext;
        $destino = '../../img/ementas/' . $nome_final; // Caminho relativo para HTML
        $destino_php = '../img/ementas/' . $nome_final; // Caminho para mover o ficheiro

        if (!is_dir('../img/ementas')) {
            mkdir('../img/ementas', 0777, true);
        }
        if (move_uploaded_file($file['tmp_name'], $destino_php)) {
            // Gera o bloco HTML para inserir
            if ($file['type'] === 'application/pdf') {
                $bloco = '<embed id="ementa-img" src="' . $destino . '" type="application/pdf" width="100%" height="600px" />';
            } else {
                $bloco = '<img id="ementa-img" src="' . $destino . '" alt="Ementa Semanal" style="max-width:100%;height:auto;margin:20px auto;display:block;">';
            }

            // Ficheiros a atualizar
            $ficheiros = [
                '../HTML/PT/ementa.html',
                '../HTML/EN/menu.html'
            ];

            foreach ($ficheiros as $ficheiro) {
                if (file_exists($ficheiro)) {
                    $conteudo = file_get_contents($ficheiro);
                    // Substitui bloco antigo ou insere antes do #bebe-gourmet
                    if (strpos($conteudo, 'id="ementa-img"') !== false) {
                        // Substitui o bloco antigo (img ou embed)
                        $conteudo_atualizado = preg_replace(
                            '/<img id="ementa-img".*?>|<embed id="ementa-img".*?>/s',
                            $bloco,
                            $conteudo
                        );
                    } else {
                        // Se não existir, insere antes do #bebe-gourmet
                        $conteudo_atualizado = preg_replace(
                            '/(<div id="bebe-gourmet">)/',
                            $bloco . '$1',
                            $conteudo
                        );
                    }
                    file_put_contents($ficheiro, $conteudo_atualizado);
                }
            }
            $mensagem_ementa = "Ementa publicada com sucesso!";
            $mensagem_tipo_ementa = "success";
        } else {
            $mensagem_ementa = "Erro ao guardar o ficheiro.";
            $mensagem_tipo_ementa = "danger";
        }
    } else {
        $mensagem_ementa = "Ficheiro inválido. Só são permitidas imagens JPEG, PNG ou PDF.";
        $mensagem_tipo_ementa = "danger";
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
    <link rel="icon" type="image/x-icon" href="../img/favicon.ico">
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

    .ms-parent {
        width: 100% !important;
    }

    .ms-choice {
        height: 38px;
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
            <a href="logout.php" class="btn btn-danger logout-button">Log Out</a>
        </div>
        <img src="../img/Logo_Estrelinha-Amarela.png" alt="Logo Estrelinha Amarela" height="200px" width="200px">
    </div>

    <br>

    <div class="container mt-5">
        <h1 class="mb-4">Gestão da Estrelinha Amarela</h1>
        <br>

        <!-- Tabela para apresentar as horas que cada trabalhador efetua num determinado periodo de tempo -->

        <form method="get" class="row g-2 align-items-end mb-4">
            <div class="col-auto d-flex flex-column">
                <label for="ano_inicio" class="form-label">Ano Início</label>
                <select name="ano_inicio" id="ano_inicio" class="form-select">
                    <?php for ($a = $ano_atual - 0; $a <= $ano_atual + 30; $a++): ?>
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
                    <?php for ($a = $ano_atual - 0; $a <= $ano_atual + 30; $a++): ?>
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

            <input type="hidden" name="ano_inicio_gozo" value="<?= $ano_inicio_gozo ?>">
            <input type="hidden" name="mes_inicio_gozo" value="<?= $mes_inicio_gozo ?>">
            <input type="hidden" name="ano_fim_gozo" value="<?= $ano_fim_gozo ?>">
            <input type="hidden" name="mes_fim_gozo" value="<?= $mes_fim_gozo ?>">

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
                                <td><?= htmlspecialchars($linha['colaborador']) ?></td>
                                <td><?= htmlspecialchars($linha['data']) ?></td>
                                <td><?= isset($linha['entrada']) && $linha['entrada'] ? date('H:i', strtotime($linha['entrada'])) : '-' ?></td>
                                <td><?= isset($linha['saida']) && $linha['saida'] ? date('H:i', strtotime($linha['saida'])) : '-' ?></td>
                                <td><?= number_format($linha['horas'] ?? 0, 2, ',', '') ?></td>
                                <td><?= number_format($linha['saldo'] ?? 0, 2, ',', '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="5">Total</th>
                            <th><?= number_format($saldo_total ?? 0, 2, ',', '') ?></th>
                        </tr>
                    </tfoot>
                </table>
            <?php else: ?>
                <p>Selecione pelo menos um colaborador e um intervalo de datas para ver os registos</p>
            <?php endif; ?>
        </div>
    </div>
    <br>

    <hr>

    <!-- Tabela onde o admin faz a gestão de horas compensadas e por compensar -->

    <div class="container mt-5">
        <h3>Horas a Compensar</h3>

        <?php if (!empty($mensagem_compensacao)): ?>
            <div class="alert alert-<?= $mensagem_tipo_compensacao ?> alert-dismissible fade show" role="alert">
                <?= $mensagem_compensacao ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form method="get" class="row g-2 align-items-end mb-4">
            <div class="col-auto d-flex flex-column">
                <label for="ano_inicio_gozo" class="form-label">Ano Início</label>
                <select name="ano_inicio_gozo" id="ano_inicio_gozo" class="form-select">
                    <?php for ($a = $ano_atual - 0; $a <= $ano_atual + 100; $a++): ?>
                        <option value="<?= $a ?>" <?= ($ano_inicio_gozo == $a) ? 'selected' : '' ?>><?= $a ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-auto d-flex flex-column">
                <label for="mes_inicio_gozo" class="form-label">Mês Início</label>
                <select name="mes_inicio_gozo" id="mes_inicio_gozo" class="form-control">
                    <?php foreach ($meses as $m => $nome): ?>
                        <option value="<?= $m ?>" <?= ($mes_inicio_gozo == $m) ? 'selected' : '' ?>><?= $nome ?></option>
                    <?php endforeach ?>
                </select>
            </div>
            <div class="col-auto d-flex flex-column">
                <label for="ano_fim_gozo" class="form-label">Ano Fim</label>
                <select name="ano_fim_gozo" id="ano_fim_gozo" class="form-select">
                    <?php for ($a = $ano_atual - 0; $a <= $ano_atual + 100; $a++): ?>
                        <option value="<?= $a ?>" <?= ($ano_fim_gozo == $a) ? 'selected' : '' ?>><?= $a ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-auto d-flex flex-column">
                <label for="mes_fim_gozo" class="form-label">Mês Fim</label>
                <select name="mes_fim_gozo" id="mes_fim_gozo" class="form-control">
                    <?php foreach ($meses as $m => $nome): ?>
                        <option value="<?= $m ?>" <?= ($mes_fim_gozo == $m) ? 'selected' : '' ?>><?= $nome ?></option>
                    <?php endforeach ?>
                </select>
            </div>
            <div class="col-auto d-flex flex-column">
                <label for="colaborador_gozo" class="form-label">Colaborador</label>
                <select multiple="multiple" size="10" name="colaborador_gozo[]" id="colaborador_gozo" class="form-select" required>
                    <?php foreach ($colaboradores as $col): ?>
                        <option value="<?= $col['id'] ?>" <?= ($colaboradores_gozo == $col['id']) ? 'selected' : '' ?>><?= htmlspecialchars($col['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <input type="hidden" name="ano_inicio" value="<?= $ano_inicio ?>">
            <input type="hidden" name="mes_inicio" value="<?= $mes_inicio ?>">
            <input type="hidden" name="ano_fim" value="<?= $ano_fim ?>">
            <input type="hidden" name="mes_fim" value="<?= $mes_fim ?>">
            <?php foreach ((array)$colaboradoresSelecionados as $colId): ?>
                <input type="hidden" name="colaborador[]" value="<?= $colId ?>">
            <?php endforeach; ?>

            <div class="col-auto">
                <button type="submit" class="btn btn-secondary">Compensações</button>
            </div>
            <div class="col-auto">
                <button type="button" class="btn btn-success" data-bs-toggle="collapse" data-bs-target="#formInserirCompensacao">
                    Inserir Compensação
                </button>
            </div>
        </form>

        <div class="collapse mb-4" id="formInserirCompensacao">
            <form method="post" class="row g-2 align-items-end">
                <div class="col-auto d-flex flex-column">
                    <label for="colaborador_compensado_insert" class="form-label">Colaborador</label>
                    <select name="colaborador_compensado_insert" id="colaborador_compensado_insert" class="form-select" required>
                        <option value="">Selecione um colaborador</option>
                        <?php foreach ($colaboradores as $col): ?>
                            <option value="<?= $col['id'] ?>">
                                <?= htmlspecialchars($col['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-auto d-flex flex-column">
                    <label for="data_compensada_insert" class="form-label">Data</label>
                    <input type="date" name="data_compensada_insert" id="data_compensada_insert" class="form-control" required>
                </div>
                <div class="col-auto d-flex flex-column">
                    <label for="horas_compensadas_insert" class="form-label">Horas a Compensadas</label>
                    <input type="number" step="0.01" min="0" max="24" name="horas_compensadas_insert" id="horas_compensadas_insert" class="form-control" required>
                </div>
                <div class="col-auto">
                    <button type="submit" name="inserir_compensacao" class="btn btn-primary">Inserir</button>
                </div>
            </form>
        </div>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Colaborador</th>
                    <th>Data</th>
                    <th>Horas Compensadas</th>
                    <th>Horas por Compensar</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($horas_compensadas_lista)): ?>
                    <?php foreach ($horas_compensadas_lista as $linha): ?>
                        <tr>
                            <td><?= $linha['colaborador'] ?></td>
                            <td><?= $linha['data'] ?></td>
                            <td><?= number_format($linha['horas'], 2, ',', '') ?></td>
                            <td>
                                <?php
                                $nome = $linha['colaborador'];
                                $saldo = $saldos_colaboradores[$nome] ?? 0;
                                $compensadas = $compensadas_por_colaborador[$nome] ?? 0;
                                $por_compensar = $saldo - $compensadas;
                                echo number_format($por_compensar, 2, ',', '');
                                ?>
                            </td>
                            <td>
                                <form method="post" style="display:inline;" onsubmit="return confirmarEliminacaoCompensacao('<?= $linha['data'] ?>');">
                                    <input type="hidden" name="id_descontos" value="<?= $linha['id_descontos'] ?>">
                                    <button type="submit" name="eliminar_compensacao" class="btn btn-danger btn-sm">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">Nenhum registo encontrado para o intervalo selecionado.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3"> Total de horas Compensadas</th>
                    <th><?= number_format($total_compensadas, 2, ',', '') ?></th>
                </tr>
                <tr>
                    <th colspan="4">Horas por Compensar</th>
                </tr>
                <?php
                // Array para guardar o saldo de cada colaborador
                $saldos_colaboradores = [];
                foreach ($banco_horas as $linha) {
                    $nome = $linha['colaborador'];
                    if (!isset($saldos_colaboradores[$nome])) {
                        $saldos_colaboradores[$nome] = 0;
                    }
                    $saldos_colaboradores[$nome] += $linha['saldo'];
                }

                // Array para guardar o total compensado por cada colaborador
                $compensadas_por_colaborador = [];
                foreach ($horas_compensadas_lista as $linha) {
                    $nome = $linha['colaborador'];
                    if (!isset($compensadas_por_colaborador[$nome])) {
                        $compensadas_por_colaborador[$nome] = 0;
                    }
                    $compensadas_por_colaborador[$nome] += $linha['horas'];
                }

                // Divide o total de compensações por colaborador
                foreach ($saldos_colaboradores as $nome => $saldo) {
                    $compensadas = $compensadas_por_colaborador[$nome] ?? 0;
                    $por_compensar = $saldo - $compensadas;
                    echo '<tr>';
                    echo '<td colspan="3">' . htmlspecialchars($nome) . '</td>';
                    echo '<td>' . number_format($por_compensar, 2, ',', '') . '</td>';
                    echo '</tr>';
                }
                ?>
            </tfoot>
        </table>

        <script>
            function confirmarEliminacaoCompensacao(data) {
                return confirm('Tem a certeza que pretende eliminar o registo do dia "' + data + '"? Esta ação é irreversível.');
            }
        </script>
    </div>

    <br>
    <hr>
    <br>

    <h2>Gestão de Colaboradores</h2>
    <br>
    <button class="btn btn-info mb-3" type="button" data-bs-toggle="collapse" data-bs-target="#tabelaColaboradores">Ver Colaboradores</button>

    <?php if (!empty($mensagem_colaboradores)): ?>
        <div class="alert alert-<?= $mensagem_tipo_colaboradores ?> alert-dismissible fade show" role="alert">
            <?= $mensagem_colaboradores ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="collapse" id="tabelaColaboradores" style="margin: auto; width: 100%; max-width: 600px;">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Colaborador</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($colaboradores as $col): ?>
                    <tr>
                        <td><?= htmlspecialchars($col['nome']) ?></td>
                        <td>
                            <?php
                            if (!$col['ativo']): ?>
                                <form method="post" style="display: inline;">
                                    <input type="hidden" name="id_colaborador" value="<?= $col['id'] ?>">
                                    <button type="submit" name="ativar_colaborador" class="btn btn-primary btn-sm">Ativar</button>
                                </form>
                            <?php else: ?>
                                <span class="badge bg-success">Ativo</span>
                            <?php endif ?>
                            <form method="post" style="display: inline;" onsubmit="return confirmarEliminacao('<?= htmlspecialchars($col['nome']) ?>');">
                                <input type="hidden" name="id_colaborador" value="<?= $col['id'] ?>">
                                <button type="submit" name="eliminar_registo" class="btn btn-danger btn-sm">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <br>
    <br>

    <hr>

    <br>
    <br>
    <h1>Gestão de Ementas</h1>
    <br>
    <form method="post" action="admin.php" enctype="multipart/form-data" style="margin: auto; width: 100%; max-width: 600px;">
        <div class="mb-3">
            <label for="ementa_img" class="form-label">Selecionar imagem ou PDF da ementa (JPG, PNG, PDF):</label>
            <input type="file" name="ementa_img" id="ementa_img" class="form-control" accept="image/*,application/pdf" required>
        </div>
        <button type="submit" name="upload_ementa" class="btn btn-primary">Publicar Ementa</button>
    </form>
    <br>

    <?php if (!empty($mensagem_ementa)): ?>
        <div class="alert alert-<?= $mensagem_tipo_ementa ?> alert-dismissible fade show" role="alert">
            <?= $mensagem_ementa ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php
    if (!empty($ementa_html)) {
        echo $ementa_html;
    }
    ?>

    <script>
        $(function() {
            $('#colaborador').multipleSelect({
                filter: true,
                width: '100%'
            });
            $('#colaborador_gozo').multipleSelect({
                filter: true,
                width: '100%'
            });
        });

        function confirmarEliminacao(nome) {
            return confirm('Tem a certeza que pretende eliminar este registo "' + nome + '"? Esta ação é irreversível.');
        }
    </script>
</body>

<?php mysqli_close($liga); ?>

</html>