<?php
require_once 'auth.php';
verificarPerfil(['tecnico', 'admin']);
require_once 'conexao.php';

// Filtros de Período
$mes = $_GET['mes'] ?? date('m');
$ano = $_GET['ano'] ?? date('Y');

// DIAGNÓSTICO: Vamos testar se existem chamados na tabela para este mês/ano
$sql_teste = "SELECT COUNT(*) as total_geral FROM chamados WHERE MONTH(data_abertura) = :mes AND YEAR(data_abertura) = :ano";
$stmt_teste = $pdo->prepare($sql_teste);
$stmt_teste->execute([':mes' => $mes, ':ano' => $ano]);
$teste_resultado = $stmt_teste->fetch(PDO::FETCH_ASSOC);
$total_encontrado = $teste_resultado['total_geral'] ?? 0;

// 1. Dados para o Gráfico de Chamados por Dia
$sql_dias = "SELECT DAY(data_abertura) as dia, COUNT(*) as total 
             FROM chamados c 
             WHERE MONTH(data_abertura) = :mes AND YEAR(data_abertura) = :ano 
             GROUP BY DAY(data_abertura) 
             ORDER BY dia ASC";
$stmt_dias = $pdo->prepare($sql_dias);
$stmt_dias->execute([':mes' => $mes, ':ano' => $ano]);
$dados_dias = $stmt_dias->fetchAll(PDO::FETCH_ASSOC);

$dias_labels = [];
$dias_valores = [];
$total_dias_mes = cal_days_in_month(CAL_GREGORIAN, $mes, $ano);
for ($d = 1; $d <= $total_dias_mes; $d++) {
    $dias_labels[] = str_pad($d, 2, '0', STR_PAD_LEFT);
    $dias_valores[$d] = 0;
}
foreach ($dados_dias as $item) {
    $dias_valores[(int)$item['dia']] = (int)$item['total'];
}
$dias_valores = array_values($dias_valores);

// 2. Dados para o Gráfico de Pizza (Status)
$sql_status = "SELECT status, COUNT(*) as total 
               FROM chamados c 
               WHERE MONTH(data_abertura) = :mes AND YEAR(data_abertura) = :ano 
               GROUP BY status";
$stmt_status = $pdo->prepare($sql_status);
$stmt_status->execute([':mes' => $mes, ':ano' => $ano]);
$dados_status = $stmt_status->fetchAll(PDO::FETCH_ASSOC);

$status_map = [
    'novo' => 'Novos',
    'em_atendimento' => 'Em Atendimento',
    'concluido' => 'Concluídos',
    'cancelado' => 'Cancelados'
];
$status_labels = [];
$status_valores = [];
foreach ($dados_status as $item) {
    $status_labels[] = $status_map[$item['status']] ?? ucfirst($item['status']);
    $status_valores[] = (int)$item['total'];
}

// 3. Dados para o Gráfico de Barras (Secretarias)
$sql_sec = "SELECT sec.nome as secretaria_name, COUNT(c.id) as total 
            FROM chamados c 
            JOIN secretarias sec ON c.secretaria_id = sec.id 
            WHERE MONTH(data_abertura) = :mes AND YEAR(data_abertura) = :ano 
            GROUP BY sec.id, sec.nome 
            ORDER BY total DESC LIMIT 5";
$stmt_sec = $pdo->prepare($sql_sec);
$stmt_sec->execute([':mes' => $mes, ':ano' => $ano]);
$dados_sec = $stmt_sec->fetchAll(PDO::FETCH_ASSOC);

$sec_labels = [];
$sec_valores = [];
foreach ($dados_sec as $item) {
    $sec_labels[] = $item['secretaria_name'];
    $sec_valores[] = (int)$item['total'];
}

// 4. Dados para o Gráfico de Barras (Chamados por SETOR)
$sql_sec = "SELECT seto.nome as setor_name, COUNT(c.id) as total 
            FROM chamados c 
            JOIN setores seto ON c.setor_id = seto.id 
            WHERE MONTH(data_abertura) = :mes AND YEAR(data_abertura) = :ano 
            GROUP BY seto.id, seto.nome 
            ORDER BY total DESC LIMIT 5";
$stmt_sec = $pdo->prepare($sql_sec);
$stmt_sec->execute([':mes' => $mes, ':ano' => $ano]);
$dados_sec = $stmt_sec->fetchAll(PDO::FETCH_ASSOC);

$sec_labels = [];
$sec_valores = [];
foreach ($dados_sec as $item) {
    $sec_labels[] = $item['setor_name'];
    $sec_valores[] = (int)$item['total'];
}

// 5. Ranking de Secretarias
$sql_ranking = "SELECT sec.nome as secretaria_nome, COUNT(c.id) as total_chamados 
                FROM chamados c 
                JOIN secretarias sec ON c.secretaria_id = sec.id 
                WHERE MONTH(data_abertura) = :mes AND YEAR(data_abertura) = :ano 
                GROUP BY sec.id, sec.nome 
                ORDER BY total_chamados DESC 
                LIMIT 5";
$stmt_ranking = $pdo->prepare($sql_ranking);
$stmt_ranking->execute([':mes' => $mes, ':ano' => $ano]);
$ranking_secretarias = $stmt_ranking->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Relatórios e Gráficos - Service Desk T.I</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="index.php">🖥️ Service Desk T.I</a>
            <div class="d-flex align-items-center gap-2">
                <a href="index.php" class="btn btn-outline-light btn-sm">← Voltar ao Dashboard</a>
                <a href="logout.php" class="btn btn-danger btn-sm">Sair</a>
            </div>
        </div>
    </nav>

    <div class="container pb-5">
        <!-- Alerta de Diagnóstico -->
        <div class="alert alert-info py-2 mb-3">
            🔍 <strong>Diagnóstico para o período (Mês: <?= $mes ?> / Ano: <?= $ano ?>):</strong> 
            Foram encontrados <strong><?= $total_encontrado ?></strong> chamados registados no banco de dados.
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>📊 Relatórios e Estatísticas</h2>
            <form method="GET" class="row g-2 align-items-center">
                <div class="col-auto">
                    <select name="mes" class="form-select form-select-sm">
                        <?php for($m=1; $m<=12; $m++): ?>
                            <option value="<?= $m ?>" <?= $mes == $m ? 'selected' : '' ?>><?= str_pad($m, 2, '0', STR_PAD_LEFT) ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <input type="number" name="ano" class="form-control form-control-sm" value="<?= htmlspecialchars($ano) ?>" style="width: 90px;">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-sm">Filtrar Período</button>
                </div>
            </form>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title mb-3">📈 Chamados por Dia no Mês</h5>
                <div style="height: 300px;">
                    <canvas id="graficoDias"></canvas>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body d-flex flex-column align-items-center">
                        <h5 class="card-title mb-3 w-100 text-start">🍕 Distribuição por Status</h5>
                        <div style="width: 260px; height: 260px;" class="my-auto">
                            <canvas id="graficoStatus"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title mb-3">🏛️ Top Setores com Mais Chamados</h5>
                        <div style="height: 260px;">
                            <canvas id="graficoSecretarias"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title mb-3">🏆 Secretarias com Mais Chamados</h5>
                <div class="table-responsive">
                    <table class="table table-striped align-middle mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Secretaria</th>
                                <th class="text-center">Total de Chamados</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($ranking_secretarias) > 0): ?>
                                <?php $pos = 1; foreach ($ranking_secretarias as $req): ?>
                                    <tr>
                                        <td><strong><?= $pos++ ?>º</strong></td>
                                        <td><?= htmlspecialchars($req['secretaria_nome']) ?></td>
                                        <td class="text-center"><span class="badge bg-primary px-3 py-2"><?= $req['total_chamados'] ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted">Nenhum dado encontrado para o período selecionado.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        const ctxDias = document.getElementById('graficoDias').getContext('2d');
        new Chart(ctxDias, {
            type: 'line',
            data: {
                labels: <?= json_encode($dias_labels) ?>,
                datasets: [{
                    label: 'Chamados Abertos',
                    data: <?= json_encode($dias_valores) ?>,
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
        });

        const ctxStatus = document.getElementById('graficoStatus').getContext('2d');
        new Chart(ctxStatus, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($status_labels) ?>,
                datasets: [{
                    data: <?= json_encode($status_valores) ?>,
                    backgroundColor: ['#ffc107', '#0dcaf0', '#198754', '#dc3545']
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });

        const ctxSec = document.getElementById('graficoSecretarias').getContext('2d');
        new Chart(ctxSec, {
            type: 'bar',
            data: {
                labels: <?= json_encode($sec_labels) ?>,
                datasets: [{
                    label: 'Nº de Chamados',
                    data: <?= json_encode($sec_valores) ?>,
                    backgroundColor: '#6c757d'
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
        });
    </script>
</body>
</html>