<?php
require_once 'auth.php';
verificarPerfil(['tecnico', 'admin']); // Apenas técnicos e administradores entram aqui
require_once 'conexao.php';

// Filtros recebidos via GET
$mes = $_GET['mes'] ?? date('m');
$ano = $_GET['ano'] ?? date('Y');
$secretaria_id = $_GET['secretaria_id'] ?? '';
$servico_id = $_GET['servico_id'] ?? '';
$status_filtro = $_GET['status'] ?? '';
$busca = $_GET['busca'] ?? '';

// Montagem da query com filtros dinâmicos (adicionado o 'c.' para evitar ambiguidade)
$where = "WHERE MONTH(c.data_abertura) = :mes AND YEAR(c.data_abertura) = :ano";
$params = [':mes' => $mes, ':ano' => $ano];

if (!empty($secretaria_id)) {
    $where .= " AND c.secretaria_id = :sec";
    $params[':sec'] = $secretaria_id;
}
if (!empty($servico_id)) {
    $where .= " AND c.servico_id = :srv";
    $params[':srv'] = $servico_id;
}

// Cards de Resumo (adicionado o 'c' como alias da tabela chamados)
$sql_cards = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN c.status = 'novo' THEN 1 ELSE 0 END) as novos,
    SUM(CASE WHEN c.status = 'em_atendimento' THEN 1 ELSE 0 END) as andamento,
    SUM(CASE WHEN c.status = 'novo' OR c.status = 'em_atendimento' THEN 1 ELSE 0 END) as abertos,
    SUM(CASE WHEN c.status = 'concluido' THEN 1 ELSE 0 END) as concluidos
    FROM chamados c $where";
$stmt = $pdo->prepare($sql_cards);
$stmt->execute($params);
$totais = $stmt->fetch();

// Define a regra do status para a listagem
$condicao_status = "";
if (!empty($status_filtro)) {
    // Se o usuário filtrou por um status específico (ex: concluido), aplica ele
    $condicao_status = " AND c.status = :status_filtro";
    $params[':status_filtro'] = $status_filtro;
} else {
    // Caso contrário, padrão do index: oculta os concluídos
    $condicao_status = " AND c.status != 'concluido'";
}

// Condição dinâmica para a busca por palavra-chave em todas as colunas e tabelas relacionadas
$condicao_busca = "";
if (!empty($busca)) {
    $condicao_busca = " AND (
        c.titulo LIKE :busca 
        OR c.descricao LIKE :busca 
        OR c.protocolo LIKE :busca 
        OR s.nome LIKE :busca 
        OR sec.nome LIKE :busca 
        OR seto.nome LIKE :busca
    )";
    $params[':busca'] = "%" . $busca . "%";
}

// Lista de chamados ordenada por prioridade e data
$sql_lista = "SELECT c.*, s.nome as servico_nome, sec.nome as secretaria_nome, seto.nome as setor_nome 
              FROM chamados c 
              JOIN servicos s ON c.servico_id = s.id
              JOIN secretarias sec ON c.secretaria_id = sec.id 
              JOIN setores seto ON c.setor_id = seto.id 
              $where $condicao_status $condicao_busca 
              ORDER BY FIELD(c.prioridade, 'urgente', 'alta', 'media', 'baixa'), c.data_abertura DESC";

$stmt_lista = $pdo->prepare($sql_lista);
$stmt_lista->execute($params);
$chamados = $stmt_lista->fetchAll();

// Listas auxiliares para selects do filtro
$secretarias = $pdo->query("SELECT * FROM secretarias ORDER BY nome")->fetchAll();
$servicos = $pdo->query("SELECT * FROM servicos WHERE ativo = 1")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Atendimentos T.I</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="index.php">🖥️ Service Desk T.I</a>
            <div class="d-flex align-items-center gap-2">
                <span class="text-light me-2">Olá, <strong><?= htmlspecialchars($_SESSION['usuario_nome']) ?></strong></span>
                <a href="novo_chamado.php" class="btn btn-success btn-sm">+ Novo Chamado</a>
                <a href="usuarios.php" class="btn btn-outline-light btn-sm">Utilizadores</a>
                <a href="configurar.php" class="btn btn-outline-light btn-sm">Configurações</a>
                <a href="relatorios.php" class="btn btn-outline-light btn-sm">📊 Relatórios</a>
                <a href="logout.php" class="btn btn-danger btn-sm">Sair</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <!-- Filtros e Barra de Pesquisa Integrados no mesmo Formulário -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" class="row g-2 align-items-end">
                    <!-- Mantém o status ativo caso esteja visualizando os concluídos -->
                    <?php if (!empty($status_filtro)): ?>
                        <input type="hidden" name="status" value="<?= htmlspecialchars($status_filtro) ?>">
                    <?php endif; ?>

                    <div class="col-md-1">
                        <label class="form-label small mb-1">Mês</label>
                        <select name="mes" class="form-select form-select-sm">
                            <?php for($m=1; $m<=12; $m++): ?>
                                <option value="<?= $m ?>" <?= $mes == $m ? 'selected' : '' ?>><?= str_pad($m, 2, '0', STR_PAD_LEFT) ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-1">
                        <label class="form-label small mb-1">Ano</label>
                        <input type="number" name="ano" class="form-control form-control-sm" value="<?= htmlspecialchars($ano) ?>">
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label small mb-1">Secretaria</label>
                        <select name="secretaria_id" class="form-select form-select-sm">
                            <option value="">Todas</option>
                            <?php foreach ($secretarias as $sec): ?>
                                <option value="<?= $sec['id'] ?>" <?= $secretaria_id == $sec['id'] ? 'selected' : '' ?>><?= htmlspecialchars($sec['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label small mb-1">Assunto / Serviço</label>
                        <select name="servico_id" class="form-select form-select-sm">
                            <option value="">Todos</option>
                            <?php foreach ($servicos as $srv): ?>
                                <option value="<?= $srv['id'] ?>" <?= $servico_id == $srv['id'] ? 'selected' : '' ?>><?= htmlspecialchars($srv['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Pesquisar</label>
                        <input type="text" class="form-control form-control-sm" id="busca" name="busca" value="<?= htmlspecialchars($busca) ?>" placeholder="Título, protocolo...">
                    </div>

                    <!-- Botões alinhados perfeitamente ao lado na mesma linha -->
                    <div class="col-md-3 d-flex gap-1">
                        <button type="submit" class="btn btn-primary btn-sm w-50">Filtrar</button>
                        <a href="index.php" class="btn btn-secondary btn-sm w-50 text-center">Limpar</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Cards de Resumo -->
        <div class="row text-center mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white p-3 shadow-sm">
                    <h5>Total no Período</h5>
                    <h3><?= $totais['total'] ?? 0 ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-dark p-3 shadow-sm">
                    <h5>Novos</h5>
                    <h3><?= $totais['novos'] ?? 0 ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <a href="index.php" class="text-decoration-none text-white d-block">
                    <div class="card bg-info text-white p-3 shadow-sm">
                        <h5>Todos em aberto</h5>
                        <h3><?= $totais['abertos'] ?? 0 ?></h3>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="index.php?status=concluido<?= isset($_GET['mes']) ? '&mes='.$_GET['mes'] : '' ?><?= isset($_GET['ano']) ? '&ano='.$_GET['ano'] : '' ?>" class="text-decoration-none text-white d-block">
                    <div class="card bg-success text-white p-3 shadow-sm">
                        <h5>Concluídos</h5>
                        <h3><?= $totais['concluidos'] ?? 0 ?></h3>
                    </div>
                </a>
            </div>
        </div>

        <!-- Tabela de Chamados -->
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title mb-3">Listagem de Chamados Filtrados</h5>
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Protocolo</th>
                                <th>Título</th>
                                <th>Secretaria</th>
                                <th>Setor</th>
                                <th>Assunto</th>
                                <th>Prioridade</th>
                                <th>Status</th>
                                <th>Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($chamados) > 0): ?>
                                <?php foreach ($chamados as $ch): ?>
                                    <tr>
                                        <td><a href="detalhes_chamado.php?id=<?= $ch['id'] ?>" class="text-decoration-none"><strong><?= htmlspecialchars($ch['protocolo']) ?></strong></a></td>
                                        <td><?= htmlspecialchars($ch['titulo']) ?></td>
                                        <td><?= htmlspecialchars($ch['secretaria_nome']) ?></td>
                                        <td><?= htmlspecialchars($ch['setor_nome']) ?></td>
                                        <td><?= htmlspecialchars($ch['servico_nome']) ?></td>
                                        <td><span class="badge bg-secondary"><?= ucfirst($ch['prioridade']) ?></span></td>
                                        <td><span class="badge bg-dark"><?= ucfirst($ch['status']) ?></span></td>
                                        <td><?= date('d/m/Y H:i', strtotime($ch['data_abertura'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted">Nenhum chamado encontrado para os filtros selecionados.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
