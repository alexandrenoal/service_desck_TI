<?php
require_once 'auth.php';
require_once 'conexao.php';

// Processar o formulário de cadastro de utilizador
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome     = $_POST['nome'] ?? '';
    $email    = $_POST['email'] ?? '';
    $senha    = !empty($_POST['senha']) ? password_hash($_POST['senha'], PASSWORD_DEFAULT) : null;
    $perfil   = $_POST['perfil'] ?? 'solicitante';
    $setor_id = !empty($_POST['setor_id']) ? $_POST['setor_id'] : null;

    if (!empty($nome) && !empty($email) && $senha) {
        $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, perfil, setor_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$nome, $email, $senha, $perfil, $setor_id]);
        header("Location: usuarios.php?sucesso=1");
        exit;
    }
}

// Procurar lista de utilizadores e setores
$usuarios = $pdo->query("SELECT u.*, s.nome as setor_nome FROM usuarios u LEFT JOIN setores s ON u.setor_id = s.id ORDER BY u.nome ASC")->fetchAll();
$setores  = $pdo->query("SELECT s.*, sec.nome as secretaria_nome FROM setores s JOIN secretarias sec ON s.secretaria_id = sec.id WHERE s.ativo = 1 ORDER BY sec.nome, s.nome")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gestão de Utilizadores - T.I</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="index.php">🖥️ Service Desk T.I</a>
            <div>
                <a href="index.php" class="btn btn-outline-light btn-sm">Dashboard</a>
                <a href="configurar.php" class="btn btn-outline-light btn-sm">Configurações</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <h2 class="mb-4">Gestão de Utilizadores</h2>

        <div class="row">
            <!-- Formulário de Cadastro -->
            <div class="col-md-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Novo Utilizador</h5>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Nome Completo</label>
                                <input type="text" name="nome" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">E-mail</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Palavra-passe</label>
                                <input type="password" name="senha" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Perfil de Acesso</label>
                                <select name="perfil" class="form-select" required>
                                    <option value="solicitante">Solicitante (Utilizador comum)</option>
                                    <option value="tecnico">Técnico de T.I</option>
                                    <option value="admin">Administrador do Sistema</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Setor / Departamento</label>
                                <select name="setor_id" class="form-select">
                                    <option value="">Nenhum / Não se aplica</option>
                                    <?php foreach ($setores as $set): ?>
                                        <option value="<?= $set['id'] ?>">
                                            <?= htmlspecialchars($set['secretaria_nome']) ?> - <?= htmlspecialchars($set['nome']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Guardar Utilizador</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Tabela de Utilizadores Cadastrados -->
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Utilizadores Registados</h5>
                        <div class="table-responsive">
                            <table class="table table-striped align-middle">
                                <thead>
                                    <tr>
                                        <th>Nome</th>
                                        <th>E-mail</th>
                                        <th>Perfil</th>
                                        <th>Setor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($usuarios) > 0): ?>
                                        <?php foreach ($usuarios as $u): ?>
                                            <tr>
                                                <td><strong><?= htmlspecialchars($u['nome']) ?></strong></td>
                                                <td><?= htmlspecialchars($u['email']) ?></td>
                                                <td>
                                                    <?php if ($u['perfil'] === 'admin'): ?>
                                                        <span class="badge bg-danger">Administrador</span>
                                                    <?php elseif ($u['perfil'] === 'tecnico'): ?>
                                                        <span class="badge bg-primary">Técnico</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Solicitante</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($u['setor_nome'] ?? '—') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">Nenhum utilizador registado ainda.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>