<?php
require_once 'auth.php';
require_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $protocolo = 'CH-' . date('Ymd') . '-' . rand(1000, 9999);
    $stmt = $pdo->prepare("INSERT INTO chamados (protocolo, solicitante_id, secretaria_id, setor_id, servico_id, titulo, descricao, prioridade) VALUES (?, ?, ?, ?, ?, ?, ?, 'media')");
    $stmt->execute([
        $protocolo,
        $_SESSION['usuario_id'],
        $_POST['secretaria_id'],
        $_POST['setor_id'],
        $_POST['servico_id'],
        $_POST['titulo'],
        $_POST['descricao']
    ]);
    header("Location: index.php?sucesso=1");
    exit;
}

$secretarias = $pdo->query("SELECT * FROM secretarias WHERE ativo = 1")->fetchAll();
$setores = $pdo->query("SELECT * FROM setores WHERE ativo = 1")->fetchAll();
$servicos = $pdo->query("SELECT * FROM servicos WHERE ativo = 1")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Novo Chamado - T.I</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5" style="max-width: 600px;">
        <div class="card shadow">
            <div class="card-body">
                <h3 class="mb-4">Abrir Novo Chamado de T.I</h3>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Secretaria</label>
                        <select name="secretaria_id" class="form-select" required>
                            <option value="">Selecione...</option>
                            <?php foreach ($secretarias as $sec): ?>
                                <option value="<?= $sec['id'] ?>"><?= htmlspecialchars($sec['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Setor</label>
                        <select name="setor_id" class="form-select" required>
                            <option value="">Selecione...</option>
                            <?php foreach ($setores as $set): ?>
                                <option value="<?= $set['id'] ?>"><?= htmlspecialchars($set['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Assunto / Serviço</label>
                        <select name="servico_id" class="form-select" required>
                            <option value="">Selecione...</option>
                            <?php foreach ($servicos as $srv): ?>
                                <option value="<?= $srv['id'] ?>"><?= htmlspecialchars($srv['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Título do Problema</label>
                        <input type="text" name="titulo" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Descrição Detalhada</label>
                        <textarea name="descricao" class="form-control" rows="4" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-success w-100">Enviar Chamado</button>
                    <a href="index.php" class="btn btn-link w-100 mt-2">Voltar</a>
                </form>
            </div>
        </div>
    </div>
</body>
</html>