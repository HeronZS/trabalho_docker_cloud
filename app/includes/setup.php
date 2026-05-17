<?php
require_once __DIR__ . '/db.php';

$pdo->exec("
    CREATE TABLE IF NOT EXISTS categorias (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(100) NOT NULL,
        descricao TEXT,
        criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS produtos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(150) NOT NULL,
        descricao TEXT,
        categoria_id INT,
        preco DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        quantidade INT NOT NULL DEFAULT 0,
        estoque_minimo INT NOT NULL DEFAULT 5,
        codigo_barras VARCHAR(50),
        criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS movimentacoes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        produto_id INT NOT NULL,
        tipo ENUM('entrada','saida') NOT NULL,
        quantidade INT NOT NULL,
        observacao TEXT,
        criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

// Seed de categorias
$count = $pdo->query("SELECT COUNT(*) FROM categorias")->fetchColumn();
if ($count == 0) {
    $pdo->exec("
        INSERT INTO categorias (nome, descricao) VALUES
        ('Eletrônicos', 'Produtos eletrônicos em geral'),
        ('Roupas', 'Vestuário e acessórios'),
        ('Alimentos', 'Produtos alimentícios'),
        ('Higiene', 'Produtos de higiene pessoal'),
        ('Ferramentas', 'Ferramentas e equipamentos');
    ");

    $pdo->exec("
        INSERT INTO produtos (nome, descricao, categoria_id, preco, quantidade, estoque_minimo, codigo_barras) VALUES
        ('Smartphone Samsung A54', 'Celular 128GB', 1, 1299.90, 15, 3, '7891234560001'),
        ('Notebook Dell Inspiron', 'Intel i5, 8GB RAM', 1, 3499.00, 8, 2, '7891234560002'),
        ('Camiseta Básica M', 'Algodão 100%', 2, 49.90, 50, 10, '7891234560003'),
        ('Calça Jeans 42', 'Jeans tradicional', 2, 129.90, 20, 5, '7891234560004'),
        ('Arroz 5kg', 'Arroz tipo 1', 3, 28.50, 100, 20, '7891234560005'),
        ('Feijão Carioca 1kg', 'Feijão tipo 1', 3, 8.90, 80, 15, '7891234560006'),
        ('Shampoo 400ml', 'Shampoo hidratante', 4, 18.90, 60, 10, '7891234560007'),
        ('Chave de Fenda', 'Chave Phillips 6mm', 5, 12.50, 4, 5, '7891234560008');
    ");
}
