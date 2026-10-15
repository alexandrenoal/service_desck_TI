<?php
require_once 'auth.php';
require_once 'conexao.php';

$usuario_id = $_SESSION['usuario_id'];
$status_filtro = $_GET['status'] ?? '';

// Monta a query trazendo apenas os chamados do usuário logado
$sql = "SELECT c.*, s.nome as servico_nome, sec.nome as secretaria_nome 
        FROM chamados c 
        JOIN servicos s ON c.servico_id = s.id
        JOIN secretarias sec ON c.secretaria_id = sec.id 
        WHERE c.solicitante_id = :usuario_id";

$params = [':usuario_id' => $usuario_id];

if (!empty($status_filtro)) {
    $sql .= " AND c.status = :status";
    $params[':status'] = $status_filtro;
}

$sql .= " ORDER BY FIELD(c.prioridade, 'urgente', 'alta', 'media', 'baixa'), c.data_abertura DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$meus_chamados = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Meus Chamados - Service Desk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="meus_chamados.php">🖥️ Meus Chamados de T.I</a>
            <div class="d-flex align-items-center gap-2">
                <span class="text-light me-2">Olá, <strong><?= htmlspecialchars($_SESSION['usuario_nome']) ?></strong></span>
                <a href="novo_chamado.php" class="btn btn-success btn-sm">+ Novo Chamado</a>
                <a href="logout.php" class="btn btn-danger btn-sm">Sair</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <!-- Filtro por status -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Filtrar por Status</label>
                        <select name="status" class="form-select">
                            <option value="">Todos os meus chamados</option>
                            <option value="novo" <?= $status_filtro == 'novo' ? 'selected' : '' ?>>Novo</option>
                            <option value="em_atendimento" <?= $status_filtro == 'em_atendimento' ? 'selected' : '' ?>>Em Atendimento</option>
                            <option value="pendente" <?= $status_filtro == 'pendente' ? 'selected' : '' ?>>Pendente</option>
                            <option value="concluido" <?= $status_filtro == 'concluido' ? 'selected' : '' ?>>Concluído</option>
                        </select>
                    </div>
                    <div class="col-md-2 mt-0">
                        <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabela de Chamados do Solicitante -->
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title mb-3">Acompanhamento dos Meus Pedidos</h5>
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Protocolo</th>
                                <th>Título</th>
                                <th>Secretaria / Assunto</th>
                                <th>Prioridade</th>
                                <th>Status</th>
                                <th>Data de Abertura</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($meus_chamados) > 0): ?>
                                <?php foreach ($meus_chamados as $ch): ?>
                                    <tr>
                                        <td><a href="detalhes_chamado.php?id=<?= $ch['id'] ?>" class="text-decoration-none"><strong><?= htmlspecialchars($ch['protocolo']) ?></strong></a></td>
                                        <td><?= htmlspecialchars($ch['titulo']) ?></td>
                                        <td>
                                            <small class="text-muted d-block"><?= htmlspecialchars($ch['secretaria_nome']) ?></small>
                                            <?= htmlspecialchars($ch['servico_nome']) ?>
                                        </td>
                                        <td><span class="badge bg-secondary"><?= ucfirst($ch['prioridade']) ?></span></td>
                                        <td>
                                            <?php 
                                                $badgeBg = match($ch['status']) {
                                                    'novo' => 'bg-warning text-dark',
                                                    'em_atendimento' => 'bg-info text-dark',
                                                    'pendente' => 'bg-secondary',
                                                    'concluido' => 'bg-success',
                                                    default => 'bg-dark'
                                                };
                                            ?>
                                            <span class="badge <?= $badgeBg ?>"><?= ucfirst(str_replace('_', ' ', $ch['status'])) ?></span>
                                        </td>
                                        <td><?= date('d/m/Y H:i', strtotime($ch['data_abertura'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Você ainda não abriu nenhum chamado com estes filtros.</td>
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