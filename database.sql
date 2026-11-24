-- Importar no phpMyAdmin DENTRO do banco if0_43526631_fjrotina
-- (opcional: o conexao.php já cria as tabelas automaticamente)

CREATE TABLE IF NOT EXISTS usuarios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS tarefas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NULL,
    titulo VARCHAR(180) NOT NULL,
    descricao TEXT NULL,
    categoria VARCHAR(80) NOT NULL DEFAULT 'Pessoal',
    prioridade ENUM('Baixa', 'Normal', 'Alta') NOT NULL DEFAULT 'Normal',
    prazo DATE NULL,
    concluida TINYINT(1) NOT NULL DEFAULT 0,
    criada_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizada_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tarefas_usuario (usuario_id),
    INDEX idx_tarefas_status (concluida),
    INDEX idx_tarefas_prazo (prazo)
) ENGINE=InnoDB;
