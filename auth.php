<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

// Função para verificar permissão de perfil
function verificarPerfil($perfisPermitidos = []) {
    if (!empty($perfisPermitidos)) {
        if (!in_array($_SESSION['usuario_perfil'], $perfisPermitidos)) {
            // Se for solicitante tentando acessar área restrita, manda para a listagem dele
            if ($_SESSION['usuario_perfil'] === 'solicitante') {
                header("Location: meus_chamados.php");
                exit;
            } else {
                header("Location: index.php");
                exit;
            }
        }
    }
}
?>