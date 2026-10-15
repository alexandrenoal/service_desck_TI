<?php
require_once 'auth.php';
require_once 'conexao.php';

$chamado_id = $_GET['id'] ?? null;

if (!$chamado_id) {
    header("Location: index.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$perfil     = $_SESSION['usuario_perfil'];

// Se for solicitante, garante que ele só pode ver os chamados dele próprio
if ($perfil === 'solicitante') {
    $stmt = $pdo->prepare("SELECT c.* FROM chamados c WHERE c.id = ? AND c.solicitante_id = ?");
    $stmt->execute([$chamado_id, $usuario_id]);
} else {
    // Técnicos e Admins podem ver qualquer chamado
    $stmt = $pdo->prepare("SELECT c.* FROM chamados c WHERE c.id = ?");
    $stmt->execute([$chamado_id]);
}
$chamado = $stmt->fetch();

if (!$chamado) {
    die("Chamado não encontrado ou sem permissão para visualizá-lo.");
}

// Processar novas interações (respostas / notas / fechamento)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'enviar_mensagem') {
        $mensagem = trim($_POST['mensagem'] ?? '');
        if (!empty($mensagem)) {
            $stmt = $pdo->prepare("INSERT INTO chamado_interacoes (chamado_id, usuario_id, mensagem) VALUES (?, ?, ?)");
            $stmt->execute([$chamado_id, $usuario_id, $mensagem]);
        }
    } elseif ($acao === 'atualizar_status' && $perfil !== 'solicitante') {
        $novo_status   = $_POST['status'] ?? '';
$nova_prioridade = $_POST['prioridade'] ?? $chamado['prioridade'];
$tecnico_id    = !empty($_POST['tecnico_id']) ? $_POST['tecnico_id'] : null;
        
        $data_conclusao = ($novo_status === 'concluido' && empty($chamado['data_conclusao'])) ? date('Y-m-d H:i:s') : $chamado['data_conclusao'];

        $stmt = $pdo->prepare("UPDATE chamados SET status = ?, prioridade = ?, tecnico_id = ?, data_conclusao = ? WHERE id = ?");
        $stmt->execute([$novo_status, $nova_prioridade, $tecnico_id, $data_conclusao, $chamado_id]);
        
        // Recarrega os dados atualizados
        header("Location: detalhes_chamado.php?id=" . $chamado_id);
        exit;
    }
}

// Buscar dados complementares do chamado
$stmt_info = $pdo->prepare("
    SELECT c.*, 
           s.nome as servico_nome, 
           sec.nome as secretaria_nome, 
           setor.nome as setor_nome,
           sol.nome as solicitante_nome,
           tec.nome as tecnico_nome
    FROM chamados c 
    JOIN servicos s ON c.servico_id = s.id
    JOIN secretarias sec ON c.secretaria_id = sec.id 
    JOIN setores setor ON c.setor_id = setor.id
    JOIN usuarios sol ON c.solicitante_id = sol.id
    LEFT JOIN usuarios tec ON c.tecnico_id = tec.id
    WHERE c.id = ?
");
$stmt_info->execute([$chamado_id]);
$dados = $stmt_info->fetch();

// Buscar histórico de interações
$stmt_inter = $pdo->prepare("
    SELECT i.*, u.nome as usuario_nome, u.perfil as usuario_perfil 
    FROM chamado_interacoes i 
    JOIN usuarios u ON i.usuario_id = u.id 
    WHERE i.chamado_id = ? 
    ORDER BY i.created_at ASC
");
$stmt_inter->execute([$chamado_id]);
$interacoes = $stmt_inter->fetchAll();

// Lista de técnicos para atribuição
$tecnicos = $pdo->query("SELECT id, nome FROM usuarios WHERE perfil IN ('tecnico', 'admin') AND ativo = 1")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Detalhes do Chamado - <?= htmlspecialchars($dados['protocolo']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="<?= $perfil === 'solicitante' ? 'meus_chamados.php' : 'index.php' ?>">🖥️ Voltar ao Painel</a>
            <span class="text-light">Protocolo: <strong><?= htmlspecialchars($dados['protocolo']) ?></strong></span>
        </div>
    </nav>

    <div class="container pb-5">
        <div class="row">
            <!-- Informações do Chamado -->
            <div class="col-md-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Informações</h5>
                        <p class="mb-2"><strong>Solicitante:</strong><br><?= htmlspecialchars($dados['solicitante_nome']) ?></p>
                        <p class="mb-2"><strong>Secretaria / Setor:</strong><br><?= htmlspecialchars($dados['secretaria_nome']) ?> - <?= htmlspecialchars($dados['setor_nome']) ?></p>
                        <p class="mb-2"><strong>Assunto:</strong><br><?= htmlspecialchars($dados['servico_nome']) ?></p>
                        <p class="mb-2"><strong>Prioridade:</strong><br><span class="badge bg-secondary"><?= ucfirst($dados['prioridade']) ?></span></p>
                        <p class="mb-2"><strong>Status Atual:</strong><br>
                            <?php 
                                $badgeBg = match($dados['status']) {
                                    'novo' => 'bg-warning text-dark',
                                    'em_atendimento' => 'bg-info text-dark',
                                    'pendente' => 'bg-secondary',
                                    'concluido' => 'bg-success',
                                    default => 'bg-dark'
                                };
                            ?>
                            <span class="badge <?= $badgeBg ?>"><?= ucfirst(str_replace('_', ' ', $dados['status'])) ?></span>
                        </p>
                        <p class="mb-2"><strong>Técnico Responsável:</strong><br><?= htmlspecialchars($dados['tecnico_nome'] ?? 'Não atribuído') ?></p>
                        <p class="mb-0"><strong>Abertura:</strong><br><?= date('d/m/Y H:i', strtotime($dados['data_abertura'])) ?></p>
                    </div>
                </div>

                <!-- Painel de Alteração de Status (Exclusivo para Técnicos/Admins) -->
                <?php if ($perfil !== 'solicitante'): ?>
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Gerir Atendimento</h5>
                        <form method="POST">
                            <input type="hidden" name="acao" value="atualizar_status">
                            <div class="mb-3">
                                <label class="form-label">Classificar Prioridade</label>
                                <select name="prioridade" class="form-select">
                                    <option value="baixa" <?= $dados['prioridade'] == 'baixa' ? 'selected' : '' ?>>Baixa</option>
                                    <option value="media" <?= $dados['prioridade'] == 'media' ? 'selected' : '' ?>>Média</option>
                                    <option value="alta" <?= $dados['prioridade'] == 'alta' ? 'selected' : '' ?>>Alta</option>
                                    <option value="urgente" <?= $dados['prioridade'] == 'urgente' ? 'selected' : '' ?>>Urgente</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Alterar Status</label>
                                <select name="status" class="form-select">
                                    <option value="novo" <?= $dados['status'] == 'novo' ? 'selected' : '' ?>>Novo</option>
                                    <option value="em_atendimento" <?= $dados['status'] == 'em_atendimento' ? 'selected' : '' ?>>Em Atendimento</option>
                                    <option value="pendente" <?= $dados['status'] == 'pendente' ? 'selected' : '' ?>>Pendente</option>
                                    <option value="concluido" <?= $dados['status'] == 'concluido' ? 'selected' : '' ?>>Concluído (Fechado)</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Atribuir a Técnico</label>
                                <select name="tecnico_id" class="form-select">
                                    <option value="">Nenhum</option>
                                    <?php foreach ($tecnicos as $tec): ?>
                                        <option value="<?= $tec['id'] ?>" <?= $dados['tecnico_id'] == $tec['id'] ? 'selected' : '' ?>><?= htmlspecialchars($tec['nome']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Atualizar Chamado</button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Conteúdo e Linha do Tempo de Mensagens -->
            <div class="col-md-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h4 class="card-title"><?= htmlspecialchars($dados['titulo']) ?></h4>
                        <hr>
                        <p class="text-muted small">Descrição inicial do problema:</p>
                        <div class="p-3 bg-light rounded border mb-3" style="white-space: pre-line;"><?= htmlspecialchars($dados['descricao']) ?></div>
                    </div>
                </div>

                <!-- Histórico de Interações -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Histórico de Interações e Retornos</h5>
                        
                        <?php if (count($interacoes) > 0): ?>
                            <div class="timeline">
                                <?php foreach ($interacoes as $inter): ?>
                                    <div class="card mb-3 border-start border-4 <?= $inter['usuario_perfil'] === 'solicitante' ? 'border-secondary' : 'border-primary' ?>">
                                        <div class="card-body py-2 px-3">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <strong><?= htmlspecialchars($inter['usuario_nome']) ?> 
                                                    <span class="badge bg-<?= $inter['usuario_perfil'] === 'solicitante' ? 'secondary' : 'primary' ?> font-monospace" style="font-size: 10px;">
                                                        <?= ucfirst($inter['usuario_perfil']) ?>
                                                    </span>
                                                </strong>
                                                <small class="text-muted"><?= date('d/m/Y H:i', strtotime($inter['created_at'])) ?></small>
                                            </div>
                                            <p class="mb-0" style="white-space: pre-line;"><?= htmlspecialchars($inter['mensagem']) ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-center">Nenhuma interação registrada ainda neste chamado.</p>
                        <?php endif; ?>

                        <!-- Formulário para Enviar Nova Mensagem / Retorno -->
                        <?php if ($dados['status'] !== 'concluido' || $perfil !== 'solicitante'): ?>
                        <form method="POST" class="mt-4">
                            <input type="hidden" name="acao" value="enviar_mensagem">
                            <div class="mb-3">
                                <label class="form-label">Adicionar Resposta / Relato do que foi feito</label>
                                <textarea name="mensagem" class="form-control" rows="3" placeholder="Escreva aqui sua orientação, dúvida ou o procedimento técnico realizado..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-success">Enviar Resposta</button>
                        </form>
                        <?php else: ?>
                            <div class="alert alert-secondary mt-3 mb-0 text-center">Este chamado encontra-se concluído/fechado.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>