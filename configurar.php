<?php
require_once 'auth.php';
require_once 'conexao.php';

// Processamento de cadastros
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['acao'])) {
        if ($_POST['acao'] === 'nova_secretaria') {
            $stmt = $pdo->prepare("INSERT INTO secretarias (nome) VALUES (?)");
            $stmt->execute([$_POST['nome']]);
        } elseif ($_POST['acao'] === 'novo_setor') {
            $stmt = $pdo->prepare("INSERT INTO setores (secretaria_id, nome) VALUES (?, ?)");
            $stmt->execute([$_POST['secretaria_id'], $_POST['nome']]);
        } elseif ($_POST['acao'] === 'novo_servico') {
            $stmt = $pdo->prepare("INSERT INTO servicos (nome, sla_horas) VALUES (?, ?)");
            $stmt->execute([$_POST['nome'], $_POST['sla_horas']]);
        }
        header("Location: configurar.php");
        exit;
    }
}

$secretarias = $pdo->query("SELECT * FROM secretarias WHERE ativo = 1")->fetchAll();
$servicos = $pdo->query("SELECT * FROM servicos WHERE ativo = 1")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Configurações - Sistema de T.I</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="index.php">📊 Painel T.I</a>
            <a href="index.php" class="btn btn-outline-light btn-sm">Voltar ao Dashboard</a>
        </div>
    </nav>
    <div class="container">
        <h2 class="mb-4">Configurações e Cadastros Dinâmicos</h2>
        <div class="row">
            <!-- Secretaria -->
            <div class="col-md-4">
                <div class="card shadow-sm mb-3">
                    <div class="card-body">
                        <h5>Nova Secretaria</h5>
                        <form method="POST">
                            <input type="hidden" name="acao" value="nova_secretaria">
                            <div class="mb-3">
                                <label class="form-label">Nome da Secretaria</label>
                                <input type="text" name="nome" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Salvar</button>
                        </form>
                    </div>
                </div>
            </div>
            <!-- Setor -->
            <div class="col-md-4">
                <div class="card shadow-sm mb-3">
                    <div class="card-body">
                        <h5>Novo Setor / Departamento</h5>
                        <form method="POST">
                            <input type="hidden" name="acao" value="novo_setor">
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
                                <label class="form-label">Nome do Setor</label>
                                <input type="text" name="nome" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Salvar</button>
                        </form>
                    </div>
                </div>
            </div>
            <!-- Serviço / Assunto -->
            <div class="col-md-4">
                <div class="card shadow-sm mb-3">
                    <div class="card-body">
                        <h5>Novo Assunto / Serviço</h5>
                        <form method="POST">
                            <input type="hidden" name="acao" value="novo_servico">
                            <div class="mb-3">
                                <label class="form-label">Nome do Serviço (Ex: Instalação de Rede)</label>
                                <input type="text" name="nome" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">SLA (Horas)</label>
                                <input type="number" name="sla_horas" class="form-control" value="24" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Salvar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>