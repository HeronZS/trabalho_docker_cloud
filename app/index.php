<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/setup.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle    = 'Dashboard';
$pageSubtitle = 'Visão geral do estoque';

// KPIs
$totalProdutos   = $pdo->query("SELECT COUNT(*) FROM produtos")->fetchColumn();
$totalCategorias = $pdo->query("SELECT COUNT(*) FROM categorias")->fetchColumn();
$valorTotal      = $pdo->query("SELECT SUM(preco * quantidade) FROM produtos")->fetchColumn() ?: 0;
$estoqueBaixo    = $pdo->query("SELECT COUNT(*) FROM produtos WHERE quantidade <= estoque_minimo AND quantidade > 0")->fetchColumn();
$semEstoque      = $pdo->query("SELECT COUNT(*) FROM produtos WHERE quantidade = 0")->fetchColumn();

// Produtos com estoque crítico
$criticos = $pdo->query("
    SELECT p.*, c.nome AS categoria
    FROM produtos p
    LEFT JOIN categorias c ON p.categoria_id = c.id
    WHERE p.quantidade <= p.estoque_minimo
    ORDER BY p.quantidade ASC
    LIMIT 6
")->fetchAll();

// Últimas movimentações
$ultimas = $pdo->query("
    SELECT m.*, p.nome AS produto
    FROM movimentacoes m
    JOIN produtos p ON m.produto_id = p.id
    ORDER BY m.criado_em DESC
    LIMIT 8
")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<!-- KPIs -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <?php
    $kpis = [
        ['label' => 'Total de Produtos',   'value' => $totalProdutos,           'icon' => '▦', 'color' => 'bg-blue-50 text-blue-600'],
        ['label' => 'Categorias',          'value' => $totalCategorias,         'icon' => '◉', 'color' => 'bg-violet-50 text-violet-600'],
        ['label' => 'Valor em Estoque',    'value' => formatMoney($valorTotal), 'icon' => '$', 'color' => 'bg-emerald-50 text-emerald-600'],
        ['label' => 'Alertas de Estoque',  'value' => $estoqueBaixo + $semEstoque, 'icon' => '!', 'color' => 'bg-amber-50 text-amber-600'],
    ];
    foreach ($kpis as $kpi):
    ?>
    <div class="card p-5">
        <div class="flex items-start justify-between mb-3">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wide"><?= $kpi['label'] ?></span>
            <div class="w-8 h-8 <?= $kpi['color'] ?> rounded-lg flex items-center justify-center text-sm font-bold">
                <?= $kpi['icon'] ?>
            </div>
        </div>
        <div class="text-2xl font-bold text-slate-900"><?= $kpi['value'] ?></div>
    </div>
    <?php endforeach; ?>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    <!-- Alertas de estoque -->
    <div class="card">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-bold text-slate-900">⚠ Alertas de Estoque</h2>
            <a href="/pages/produtos.php" class="text-xs text-slate-400 hover:text-slate-700 transition-colors">Ver todos →</a>
        </div>
        <div class="divide-y divide-slate-50">
            <?php if (empty($criticos)): ?>
                <div class="p-8 text-center text-slate-400 text-sm">Nenhum alerta no momento ✓</div>
            <?php else: foreach ($criticos as $p):
                $st = statusEstoque($p['quantidade'], $p['estoque_minimo']);
            ?>
            <div class="px-5 py-3.5 flex items-center justify-between hover:bg-slate-50 transition-colors">
                <div>
                    <div class="text-sm font-semibold text-slate-800"><?= sanitize($p['nome']) ?></div>
                    <div class="text-xs text-slate-400 mt-0.5"><?= sanitize($p['categoria'] ?? 'Sem categoria') ?></div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="mono text-sm font-bold text-slate-700"><?= $p['quantidade'] ?> un</span>
                    <span class="badge <?= $st['class'] ?>"><?= $st['label'] ?></span>
                </div>
            </div>
            <?php endforeach; endif; ?>
        </div>
    </div>

    <!-- Últimas movimentações -->
    <div class="card">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-bold text-slate-900">⇅ Últimas Movimentações</h2>
            <a href="/pages/movimentacoes.php" class="text-xs text-slate-400 hover:text-slate-700 transition-colors">Ver todas →</a>
        </div>
        <div class="divide-y divide-slate-50">
            <?php if (empty($ultimas)): ?>
                <div class="p-8 text-center text-slate-400 text-sm">Nenhuma movimentação registrada</div>
            <?php else: foreach ($ultimas as $m): ?>
            <div class="px-5 py-3.5 flex items-center justify-between hover:bg-slate-50 transition-colors">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold
                        <?= $m['tipo'] === 'entrada' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' ?>">
                        <?= $m['tipo'] === 'entrada' ? '↑' : '↓' ?>
                    </div>
                    <div>
                        <div class="text-sm font-semibold text-slate-800"><?= sanitize($m['produto']) ?></div>
                        <div class="text-xs text-slate-400"><?= formatDate($m['criado_em']) ?></div>
                    </div>
                </div>
                <span class="mono text-sm font-bold <?= $m['tipo'] === 'entrada' ? 'text-emerald-600' : 'text-red-600' ?>">
                    <?= $m['tipo'] === 'entrada' ? '+' : '-' ?><?= $m['quantidade'] ?>
                </span>
            </div>
            <?php endforeach; endif; ?>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
