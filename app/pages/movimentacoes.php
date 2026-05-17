<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/setup.php';
require_once __DIR__ . '/../includes/functions.php';

$pageTitle    = 'Movimentações';
$pageSubtitle = 'Registre entradas e saídas de produtos';

$msg = '';
$msgType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $produto_id  = (int)($_POST['produto_id'] ?? 0);
    $tipo        = $_POST['tipo'] ?? '';
    $quantidade  = (int)($_POST['quantidade'] ?? 0);
    $observacao  = trim($_POST['observacao'] ?? '');

    if (!$produto_id || !in_array($tipo, ['entrada', 'saida']) || $quantidade <= 0) {
        $msg = 'Preencha todos os campos corretamente.';
        $msgType = 'error';
    } else {
        // Valida saída
        if ($tipo === 'saida') {
            $atual = $pdo->prepare("SELECT quantidade FROM produtos WHERE id = ?");
            $atual->execute([$produto_id]);
            $qtdAtual = (int)$atual->fetchColumn();
            if ($quantidade > $qtdAtual) {
                $msg = "Quantidade insuficiente em estoque. Disponível: $qtdAtual unidades.";
                $msgType = 'error';
            }
        }

        if (!$msg) {
            $pdo->prepare("INSERT INTO movimentacoes (produto_id, tipo, quantidade, observacao) VALUES (?,?,?,?)")
                ->execute([$produto_id, $tipo, $quantidade, $observacao]);

            $delta = $tipo === 'entrada' ? $quantidade : -$quantidade;
            $pdo->prepare("UPDATE produtos SET quantidade = quantidade + ? WHERE id = ?")
                ->execute([$delta, $produto_id]);

            $msg = ucfirst($tipo) . ' registrada com sucesso!';
            $msgType = 'success';
        }
    }
}

$produtos = $pdo->query("SELECT id, nome, quantidade FROM produtos ORDER BY nome ASC")->fetchAll();

// Filtros
$filtroTipo = $_GET['tipo'] ?? '';
$filtroProd = (int)($_GET['produto'] ?? 0);

$sql = "SELECT m.*, p.nome AS produto FROM movimentacoes m JOIN produtos p ON m.produto_id = p.id WHERE 1=1";
$params = [];
if ($filtroTipo) { $sql .= " AND m.tipo = ?"; $params[] = $filtroTipo; }
if ($filtroProd) { $sql .= " AND m.produto_id = ?"; $params[] = $filtroProd; }
$sql .= " ORDER BY m.criado_em DESC LIMIT 100";
$movimentacoes = $pdo->prepare($sql);
$movimentacoes->execute($params);
$movimentacoes = $movimentacoes->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<?php if ($msg): ?>
<div class="mb-6 px-4 py-3 rounded-xl text-sm font-medium
    <?= $msgType === 'success' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-red-50 text-red-700 border border-red-200' ?>">
    <?= sanitize($msg) ?>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    <!-- Formulário de movimentação -->
    <div class="xl:col-span-1">
        <div class="card p-6">
            <h2 class="font-bold text-slate-900 mb-5">⇅ Nova Movimentação</h2>
            <form method="POST" class="space-y-4">

                <div>
                    <label class="label">Tipo de Movimentação *</label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="flex items-center gap-2 p-3 border-2 border-slate-200 rounded-xl cursor-pointer hover:border-emerald-400 transition-colors has-[:checked]:border-emerald-400 has-[:checked]:bg-emerald-50">
                            <input type="radio" name="tipo" value="entrada" class="accent-emerald-600" required>
                            <span class="text-sm font-semibold text-emerald-700">↑ Entrada</span>
                        </label>
                        <label class="flex items-center gap-2 p-3 border-2 border-slate-200 rounded-xl cursor-pointer hover:border-red-400 transition-colors has-[:checked]:border-red-400 has-[:checked]:bg-red-50">
                            <input type="radio" name="tipo" value="saida" class="accent-red-600">
                            <span class="text-sm font-semibold text-red-700">↓ Saída</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="label">Produto *</label>
                    <select name="produto_id" class="input" required>
                        <option value="">— Selecione —</option>
                        <?php foreach ($produtos as $p): ?>
                            <option value="<?= $p['id'] ?>">
                                <?= sanitize($p['nome']) ?> (<?= $p['quantidade'] ?> em estoque)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="label">Quantidade *</label>
                    <input type="number" name="quantidade" min="1" class="input" required placeholder="0">
                </div>

                <div>
                    <label class="label">Observação</label>
                    <textarea name="observacao" class="input" rows="2" placeholder="Ex: Compra de fornecedor, venda ao cliente..."></textarea>
                </div>

                <button type="submit" class="btn-primary w-full justify-center">Registrar Movimentação</button>
            </form>
        </div>
    </div>

    <!-- Histórico -->
    <div class="xl:col-span-2">
        <div class="card">
            <div class="p-5 border-b border-slate-100">
                <form method="GET" class="flex gap-3">
                    <select name="tipo" class="input w-36">
                        <option value="">Todos tipos</option>
                        <option value="entrada" <?= $filtroTipo === 'entrada' ? 'selected' : '' ?>>↑ Entrada</option>
                        <option value="saida"   <?= $filtroTipo === 'saida'   ? 'selected' : '' ?>>↓ Saída</option>
                    </select>
                    <select name="produto" class="input flex-1">
                        <option value="">Todos produtos</option>
                        <?php foreach ($produtos as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= $filtroProd == $p['id'] ? 'selected' : '' ?>>
                                <?= sanitize($p['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn-primary whitespace-nowrap">Filtrar</button>
                    <?php if ($filtroTipo || $filtroProd): ?>
                        <a href="/pages/movimentacoes.php" class="btn-secondary">Limpar</a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 text-xs text-slate-400 uppercase tracking-wide">
                            <th class="text-left px-5 py-3 font-semibold">Produto</th>
                            <th class="text-center px-3 py-3 font-semibold">Tipo</th>
                            <th class="text-center px-3 py-3 font-semibold">Qtd</th>
                            <th class="text-left px-3 py-3 font-semibold">Observação</th>
                            <th class="text-left px-3 py-3 font-semibold">Data</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php if (empty($movimentacoes)): ?>
                            <tr><td colspan="5" class="text-center py-12 text-slate-400">Nenhuma movimentação registrada.</td></tr>
                        <?php else: foreach ($movimentacoes as $m): ?>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-5 py-3.5 font-semibold text-slate-800"><?= sanitize($m['produto']) ?></td>
                            <td class="px-3 py-3.5 text-center">
                                <span class="badge <?= $m['tipo'] === 'entrada' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' ?>">
                                    <?= $m['tipo'] === 'entrada' ? '↑ Entrada' : '↓ Saída' ?>
                                </span>
                            </td>
                            <td class="px-3 py-3.5 text-center mono font-bold
                                <?= $m['tipo'] === 'entrada' ? 'text-emerald-600' : 'text-red-600' ?>">
                                <?= $m['tipo'] === 'entrada' ? '+' : '-' ?><?= $m['quantidade'] ?>
                            </td>
                            <td class="px-3 py-3.5 text-slate-500 text-xs max-w-xs truncate">
                                <?= $m['observacao'] ? sanitize($m['observacao']) : '—' ?>
                            </td>
                            <td class="px-3 py-3.5 text-slate-500 text-xs mono whitespace-nowrap">
                                <?= formatDate($m['criado_em']) ?>
                            </td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-3 border-t border-slate-100 text-xs text-slate-400">
                <?= count($movimentacoes) ?> movimentação(ões) encontrada(s)
            </div>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
