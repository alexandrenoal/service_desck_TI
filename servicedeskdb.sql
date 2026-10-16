-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 16/10/2026 às 19:18
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `sistema_ti`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `chamados`
--

CREATE TABLE `chamados` (
  `id` int(11) NOT NULL,
  `protocolo` varchar(20) NOT NULL,
  `solicitante_id` int(11) NOT NULL,
  `tecnico_id` int(11) DEFAULT NULL,
  `secretaria_id` int(11) NOT NULL,
  `setor_id` int(11) NOT NULL,
  `servico_id` int(11) NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `descricao` text NOT NULL,
  `prioridade` enum('baixa','media','alta','urgente') DEFAULT 'media',
  `status` enum('novo','em_atendimento','pendente','concluido','cancelado') DEFAULT 'novo',
  `data_abertura` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_conclusao` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `chamados`
--

INSERT INTO `chamados` (`id`, `protocolo`, `solicitante_id`, `tecnico_id`, `secretaria_id`, `setor_id`, `servico_id`, `titulo`, `descricao`, `prioridade`, `status`, `data_abertura`, `data_conclusao`) VALUES
(1, 'CH-20261014-1234', 3, 1, 1, 1, 1, 'teste', 'teste', 'media', 'concluido', '2026-10-14 21:08:30', '2026-10-14 23:21:04'),
(2, 'CH-20261014-4608', 1, 1, 1, 1, 1, 'teste2', 'teste', 'alta', 'concluido', '2026-10-14 21:31:50', '2026-10-15 19:01:27'),
(3, 'CH-20261014-7809', 3, 1, 1, 1, 1, 'teste3', 'testasasdasd', 'baixa', 'em_atendimento', '2026-10-14 21:39:38', NULL),
(4, 'CH-20261015-1317', 1, 1, 3, 2, 2, 'Autocad', 'erro autocad', 'urgente', 'em_atendimento', '2026-10-15 15:40:26', NULL),
(5, 'CH-20261015-4250', 1, NULL, 3, 2, 2, 'sdadasd', 'asdasdasdasdasd', 'media', 'novo', '2026-10-15 17:05:57', NULL),
(6, 'CH-20261015-7557', 3, 1, 1, 1, 1, 'nao imprime senhas', 'nao imprime senhas', 'urgente', 'pendente', '2026-10-15 19:18:42', '2026-10-15 21:21:20');

-- --------------------------------------------------------

--
-- Estrutura para tabela `chamado_interacoes`
--

CREATE TABLE `chamado_interacoes` (
  `id` int(11) NOT NULL,
  `chamado_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `mensagem` text NOT NULL,
  `privado` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `chamado_interacoes`
--

INSERT INTO `chamado_interacoes` (`id`, `chamado_id`, `usuario_id`, `mensagem`, `privado`, `created_at`) VALUES
(1, 1, 1, 'chamado resolvido', 0, '2026-10-14 21:18:54'),
(2, 2, 1, 'em atendimento', 0, '2026-10-14 21:32:07'),
(3, 4, 1, 'teste', 0, '2026-10-15 15:40:57'),
(4, 4, 1, 'radasdasd', 0, '2026-10-15 16:58:29'),
(5, 3, 3, 'IAI VAO RESOLVER????', 0, '2026-10-15 16:59:36'),
(6, 6, 1, 'problema na impressora', 0, '2026-10-15 19:19:50'),
(7, 6, 1, 'impressora substituída.', 0, '2026-10-15 19:20:32'),
(8, 4, 1, '133333', 0, '2026-10-16 17:11:47');

-- --------------------------------------------------------

--
-- Estrutura para tabela `secretarias`
--

CREATE TABLE `secretarias` (
  `id` int(11) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `secretarias`
--

INSERT INTO `secretarias` (`id`, `nome`, `ativo`, `created_at`) VALUES
(1, 'SOF', 1, '2026-10-14 20:42:50'),
(3, 'SMADU', 1, '2026-10-15 15:39:30');

-- --------------------------------------------------------

--
-- Estrutura para tabela `servicos`
--

CREATE TABLE `servicos` (
  `id` int(11) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `descricao` text DEFAULT NULL,
  `sla_horas` int(11) DEFAULT 24,
  `ativo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `servicos`
--

INSERT INTO `servicos` (`id`, `nome`, `descricao`, `sla_horas`, `ativo`) VALUES
(1, 'Fichas', NULL, 1, 1),
(2, 'Autocad', NULL, 5, 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `setores`
--

CREATE TABLE `setores` (
  `id` int(11) NOT NULL,
  `secretaria_id` int(11) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `ativo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `setores`
--

INSERT INTO `setores` (`id`, `secretaria_id`, `nome`, `ativo`) VALUES
(1, 1, 'Arrecadação', 1),
(2, 3, 'Meio ambiente', 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `perfil` enum('solicitante','tecnico','admin') DEFAULT 'solicitante',
  `setor_id` int(11) DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `perfil`, `setor_id`, `ativo`) VALUES
(1, 'Alexandre Noal Gonzales', 'alexandrenoal88@gmail.com', '$2y$10$xvsiuIn5.Qjf2mGFbIB6OezgR8g2c0cCnKsIxQXuIqtyXc8Dy2z8C', 'admin', NULL, 1),
(2, 'Administrador T.I', 'admin@ti.com', '$2y$10$e.wPZfJ7Yx2Y5yWjVlR3.e6G5Y3sV3hX0yK4aJ2lM2N3P4Q5R6S7T', 'admin', NULL, 1),
(3, 'solicitande', 'solicitante@gmail.com', '$2y$10$uUEW6mlGyE2Y09up9OKYae4oRO1MrwTwn28AhfBE2Is0iwN5IXOiC', 'solicitante', NULL, 1),
(4, 'admin', 'admin@gmail.com', '$2y$10$.42AliMgg2kwfve1oJ.zEul.6JzBGa8N/N1USHVrenhbQ0uGCsyQ.', 'admin', NULL, 1);

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `chamados`
--
ALTER TABLE `chamados`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `protocolo` (`protocolo`),
  ADD KEY `solicitante_id` (`solicitante_id`),
  ADD KEY `tecnico_id` (`tecnico_id`),
  ADD KEY `secretaria_id` (`secretaria_id`),
  ADD KEY `setor_id` (`setor_id`),
  ADD KEY `servico_id` (`servico_id`);

--
-- Índices de tabela `chamado_interacoes`
--
ALTER TABLE `chamado_interacoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `chamado_id` (`chamado_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `secretarias`
--
ALTER TABLE `secretarias`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `servicos`
--
ALTER TABLE `servicos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `setores`
--
ALTER TABLE `setores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `secretaria_id` (`secretaria_id`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `setor_id` (`setor_id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `chamados`
--
ALTER TABLE `chamados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `chamado_interacoes`
--
ALTER TABLE `chamado_interacoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `secretarias`
--
ALTER TABLE `secretarias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `servicos`
--
ALTER TABLE `servicos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `setores`
--
ALTER TABLE `setores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `chamados`
--
ALTER TABLE `chamados`
  ADD CONSTRAINT `chamados_ibfk_1` FOREIGN KEY (`solicitante_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `chamados_ibfk_2` FOREIGN KEY (`tecnico_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `chamados_ibfk_3` FOREIGN KEY (`secretaria_id`) REFERENCES `secretarias` (`id`),
  ADD CONSTRAINT `chamados_ibfk_4` FOREIGN KEY (`setor_id`) REFERENCES `setores` (`id`),
  ADD CONSTRAINT `chamados_ibfk_5` FOREIGN KEY (`servico_id`) REFERENCES `servicos` (`id`);

--
-- Restrições para tabelas `chamado_interacoes`
--
ALTER TABLE `chamado_interacoes`
  ADD CONSTRAINT `chamado_interacoes_ibfk_1` FOREIGN KEY (`chamado_id`) REFERENCES `chamados` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `chamado_interacoes_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Restrições para tabelas `setores`
--
ALTER TABLE `setores`
  ADD CONSTRAINT `setores_ibfk_1` FOREIGN KEY (`secretaria_id`) REFERENCES `secretarias` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`setor_id`) REFERENCES `setores` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
