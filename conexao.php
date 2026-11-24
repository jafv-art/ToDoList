<?php
declare(strict_types=1);

require_once __DIR__ . '/class/rb.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$host = getenv('DB_HOST') ?: 'sql112.infinityfree.com';
$port = getenv('DB_PORT') ?: '3306';
$name = getenv('DB_NAME') ?: 'if0_43526631_fjrotina';
$user = getenv('DB_USER') ?: 'if0_43526631';
$pass = getenv('DB_PASS') ?: '74Jj4j5kchks6w6';

R::setup("mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4", $user, $pass);

R::exec('CREATE TABLE IF NOT EXISTS usuarios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB');

R::exec('CREATE TABLE IF NOT EXISTS tarefas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NULL,
    titulo VARCHAR(180) NOT NULL,
    descricao TEXT NULL,
    categoria VARCHAR(80) NOT NULL DEFAULT "Pessoal",
    prioridade ENUM("Baixa", "Normal", "Alta") NOT NULL DEFAULT "Normal",
    prazo DATE NULL,
    concluida TINYINT(1) NOT NULL DEFAULT 0,
    criada_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizada_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tarefas_usuario (usuario_id),
    INDEX idx_tarefas_status (concluida),
    INDEX idx_tarefas_prazo (prazo)
) ENGINE=InnoDB');

$colunaUsuario = R::getAll("SHOW COLUMNS FROM tarefas LIKE 'usuario_id'");
if (!$colunaUsuario) {
    R::exec('ALTER TABLE tarefas ADD usuario_id INT UNSIGNED NULL AFTER id');
}
$indiceUsuario = R::getAll("SHOW INDEX FROM tarefas WHERE Key_name = 'idx_tarefas_usuario'");
if (!$indiceUsuario) {
    R::exec('ALTER TABLE tarefas ADD INDEX idx_tarefas_usuario (usuario_id)');
}

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function usuarioAtual(): ?array
{
    if (empty($_SESSION['usuario_id'])) {
        return null;
    }

    return [
        'id' => (int) $_SESSION['usuario_id'],
        'nome' => (string) ($_SESSION['usuario_nome'] ?? 'Usuário'),
    ];
}

function exigirLogin(): array
{
    $usuario = usuarioAtual();
    if (!$usuario) {
        header('Location: login.php');
        exit;
    }
    return $usuario;
}

function cabecalho(string $titulo): void
{
    $usuario = usuarioAtual();
    ?>
    <!doctype html>
    <html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= e($titulo) ?> · Rotina em dia</title>
        <link rel="stylesheet" href="assets/css/style.css">
    </head>
    <body>
        <header class="topbar">
            <a class="brand" href="<?= $usuario ? 'index.php' : 'login.php' ?>">
                <span class="brand-symbol">●</span>
                <span>Rotina em dia</span>
            </a>
            <nav class="nav-links">
                <?php if ($usuario): ?>
                    <a href="index.php">Hoje</a>
                    <span class="user-greeting">Olá, <?= e($usuario['nome']) ?></span>
                    <a class="logout-link" href="logout.php">Sair</a>
                <?php else: ?>
                    <a href="login.php">Entrar</a>
                    <a class="nav-register" href="cadastro.php">Criar conta</a>
                <?php endif; ?>
            </nav>
        </header>
        <main class="page-wrap">
    <?php
}

function rodape(): void
{
    ?>
        </main>
        <footer class="footer">Rotina em dia <span>—</span> uma lista clara para uma semana mais leve.</footer>
    </body>
    </html>
    <?php
}
