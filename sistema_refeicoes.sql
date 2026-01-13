-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 13/01/2026 às 18:38
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `sistema_refeicoes`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `menus`
--

CREATE TABLE `menus` (
  `id` int(11) NOT NULL,
  `data` date NOT NULL,
  `guarnicao` varchar(255) DEFAULT NULL,
  `proteina1` varchar(255) DEFAULT NULL,
  `proteina2` varchar(255) DEFAULT NULL,
  `opcoes_troca` text DEFAULT NULL,
  `status` enum('ativo','oculto') DEFAULT 'ativo',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `menus`
--

INSERT INTO `menus` (`id`, `data`, `guarnicao`, `proteina1`, `proteina2`, `opcoes_troca`, `status`, `created_at`) VALUES
(11, '2026-01-15', 'Batata c/ ervas', 'Cação á doré', 'Maminha', '[\"Omelete Simples\",\" Omelete com Queijo\",\" Omelete Completa\",\" Ovos Fritos\"]', 'ativo', '2026-01-13 13:42:29'),
(12, '2026-01-16', 'Lasanha', 'Filé de coxa assada', 'Kibe de bandeja', '[\"Omelete Simples\",\" Omelete com Queijo\",\" Omelete Completa\",\" Ovos Fritos\"]', 'ativo', '2026-01-13 13:43:13'),
(13, '2026-01-19', 'Batata frita', 'Contra filé', 'Linguiça de frango', '[\"Omelete Simples\",\" Omelete com Queijo\",\" Omelete Completa\",\" Ovos Fritos\"]', 'ativo', '2026-01-13 13:43:51'),
(14, '2026-01-20', 'Chuchu tropeiro', 'Rolê de frango', 'Polpetone', '[\"Omelete Simples\",\" Omelete com Queijo\",\" Omelete Completa\",\" Ovos Fritos\"]', 'ativo', '2026-01-13 13:44:29'),
(16, '2026-01-22', 'Caneloni frios - Legumes sautte', 'Cupim assado', 'Filé de frango à milanesa', '[\"Omelete Simples\",\" Omelete com Queijo\",\" Omelete Completa\",\" Ovos Fritos\"]', 'ativo', '2026-01-13 13:46:23'),
(17, '2026-01-23', 'Cebola tirolesa', 'Bobo de frango', 'Lagarto recheado', '[\"Omelete Simples\",\" Omelete com Queijo\",\" Omelete Completa\",\" Ovos Fritos\"]', 'ativo', '2026-01-13 13:48:13'),
(18, '2026-01-26', 'Batata palha', 'Strognoff carne', 'Filé de frango acebolado', '[\"Omelete Simples\",\" Omelete com Queijo\",\" Omelete Completa\",\" Ovos Fritos\"]', 'ativo', '2026-01-13 13:49:19'),
(19, '2026-01-27', 'Purê/Batata palha', 'Alcatra à cigana', 'Salsicha à juliana', '[\"Omelete Simples\",\" Omelete com Queijo\",\" Omelete Completa\",\" Ovos Fritos\"]', 'ativo', '2026-01-13 13:49:48'),
(21, '2026-01-29', 'Macarrão m. calabresa', 'Frango à caçadora', 'Hamburger à camões', '[\"Omelete Simples\",\" Omelete com Queijo\",\" Omelete Completa\",\" Ovos Fritos\"]', 'ativo', '2026-01-13 13:51:37'),
(22, '2026-01-30', 'Mini pizza', 'Costela assada', 'Iscas de frango', '[\"Omelete Simples\",\" Omelete com Queijo\",\" Omelete Completa\",\" Ovos Fritos\"]', 'ativo', '2026-01-13 13:52:20'),
(23, '2026-01-21', 'Chuchu tropeiro', 'Rolê de frango', 'Polpetone', '[\"Omelete Simples\",\" Omelete com Queijo\",\" Omelete Completa\",\" Ovos Fritos\"]', 'ativo', '2026-01-13 13:53:34'),
(24, '2026-01-14', 'Feijoada - Farofa/Couve', 'Torresmo', 'Filé de Frango', '[\"Omelete Simples\",\" Omelete com Queijo\",\" Omelete Completa\",\" Ovos Fritos\"]', 'ativo', '2026-01-13 14:05:38'),
(25, '2026-01-28', 'Feijoada - Farofa/Couve', 'Torresmo', 'Filé de Frango', '[\"Omelete Simples\",\" Omelete com Queijo\",\" Omelete Completa\",\" Ovos Fritos\"]', 'ativo', '2026-01-13 14:06:14');

-- --------------------------------------------------------

--
-- Estrutura para tabela `responses`
--

CREATE TABLE `responses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `cpf_registrado` varchar(20) DEFAULT NULL,
  `nome_visitante` varchar(255) DEFAULT NULL,
  `centro_custo_visitante` varchar(100) DEFAULT NULL,
  `data_refeicao` date NOT NULL,
  `presencial` enum('sim','nao') NOT NULL,
  `escolha_proteina` varchar(255) DEFAULT NULL,
  `escolha_troca` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `cpf` varchar(20) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `setor` varchar(100) DEFAULT NULL,
  `centro_custo` varchar(100) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `users`
--

INSERT INTO `users` (`id`, `nome`, `email`, `cpf`, `senha`, `setor`, `centro_custo`, `telefone`, `role`, `created_at`) VALUES
(1, 'Administrador', 'admin@tdm.energy', '00000000000', '$2y$10$wS.uY/..uQ/..uQ/..uQ/..uQ/..uQ/exampleHashPlaceHolder', 'TI', 'ADM', NULL, 'admin', '2026-01-12 11:45:36'),
(2, 'Felype Vieira de Lima', 'felype.lima@tdm.energy', '50118176870', '$2y$10$UzcOCsGdQLX9T26v4S6ktOaujYIR2HWztUZq0uU9YsmxI1JHBZiBK', 'RH', '01.00005', '11989593974', 'user', '2026-01-12 12:13:07');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `menus`
--
ALTER TABLE `menus`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `data` (`data`);

--
-- Índices de tabela `responses`
--
ALTER TABLE `responses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_response` (`cpf_registrado`,`data_refeicao`);

--
-- Índices de tabela `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `cpf` (`cpf`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `menus`
--
ALTER TABLE `menus`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de tabela `responses`
--
ALTER TABLE `responses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT de tabela `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
