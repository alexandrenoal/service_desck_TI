<?php
session_start();
require_once 'conexao.php';

// Se já estiver logado, redireciona para o index
if (isset($_SESSION['usuario_id'])) {
    if ($_SESSION['usuario_perfil'] === 'solicitante') {
        header("Location: meus_chamados.php");
    } else {
        header("Location: index.php");
    }
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (!empty($email) && !empty($senha)) {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ? AND ativo = 1");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        if ($usuario && password_verify($senha, $usuario['senha'])) {
            $_SESSION['usuario_id']     = $usuario['id'];
            $_SESSION['usuario_nome']   = $usuario['nome'];
            $_SESSION['usuario_email']  = $usuario['email'];
            $_SESSION['usuario_perfil'] = $usuario['perfil'];

            // Redireciona conforme o perfil
            if ($usuario['perfil'] === 'solicitante') {
                header("Location: meus_chamados.php");
            } else {
                header("Location: index.php");
            }
            exit;
        } else {
            $erro = 'E-mail ou palavra-passe incorretos!';
        }
    } else {
        $erro = 'Por favor, preencha todos os campos.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login - Service Desk T.I</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center vh-100">
    <div class="container" style="max-width: 400px;">
        <div class="card shadow">
            <div class="card-body p-4">
                <h3 class="text-center mb-4">🖥️ Service Desk</h3>
                
                <?php if ($erro): ?>
                    <div class="alert alert-danger p-2 fs-6" role="alert">
                        <?= htmlspecialchars($erro) ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">E-mail</label>
                        <input type="email" name="email" class="form-control" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Palavra-passe</label>
                        <input type="password" name="senha" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2">Entrar</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>