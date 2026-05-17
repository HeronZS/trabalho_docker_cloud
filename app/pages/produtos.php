<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/setup.php';
require_once __DIR__ . '/../includes/functions.php';

$pageTitle    = 'Produtos';
$pageSubtitle = 'Cadastro e consulta de produtos';

$msg = '';
$msgType = '';

// DELETE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'delete') {
    $id = (int)$_POST['id'];
    $pdo->prepare("DELETE FROM produtos WHERE id = ?")->execute([$id]);
    $msg = 'Produto excluído com sucesso.';
    $msgType = 'success';
}

// CREATE / UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($_POST['_action'] ?? '', ['create', 'update'])) {
    $id           = (int)($_POST['id'] ?? 0);
    $nome         = trim($_POST['nome'] ?? '');
    $descricao    = trim($_POST['descricao'] ?? '');
    $categoria_id = (int)($_POST['categoria_id'] ?? 0) ?: null;
    $preco        = (float)str_replace(',', '.', $_POST['preco'] ?? '0');
    $quantidade   = (int)($_POST['quantidade'] ?? 0);
    $estoque_min  = (int)($_POST['estoque_minimo'] ?? 5);
    $codigo       = trim($_POST['codigo_barras'] ?? '');

    if ($nome === '') {
        $msg = 'O nome do produto é obrigatório.';
        $msgType = 'error';
    } else {
        if ($_POST['_action'] === 'create') {
            $stmt = $pdo->prepare("INSERT INTO produtos (nome, descricao, categoria_id, preco, quantidade, estoque_minimo, codigo_barras) VALUES (?,?,?,?,?,?,?)");
            $stmt->execute([$nome, $descricao, $categoria_id, $preco, $quantidade, $estoque_min, $codigo]);
            $msg = 'Produto cadastrado com sucesso!';
        } else {
            $stmt = $pdo->prepare("UPDATE produtos SET nome=?, descricao=?, categoria_id=?, preco=?, quantidade=?, estoque_minimo=?, codigo_barras=? WHERE id=?");
            $stmt->execute([$nome, $descricao, $categoria_id, $preco, $quantidade, $estoque_min, $codigo, $id]);
            $msg = 'Produto atualizado com sucesso!';
        }
        $msgType = 'success';
    }
}

// Busca/filtro
$search = trim($_GET['q'] ?? '');
$catFilter = (int)($_GET['cat'] ?? 0);

$sql = "SELECT p.*, c.nome AS categoria FROM produtos p LEFT JOIN categorias c ON p.categoria_id = c.id WHERE 1=1";
$params = [];
if ($search) {
    $sql .= " AND (p.nome LIKE ? OR p.codigo_barras LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($catFilter) {
    $sql .= " AND p.categoria_id = ?";
    $params[] = $catFilter;
}
$sql .= " ORDER BY p.nome ASC";
$produtos = $pdo->prepare($sql);
$produtos->execute($params);
$produtos = $produtos->fetchAll();

$categorias = $pdo->query("SELECT * FROM categorias ORDER BY nome ASC")->fetchAll();

// Produto para edição
$editProduto = null;
if (isset($_GET['edit'])) {
    $editProduto = $pdo->prepare("SELECT * FROM produtos WHERE id = ?");
    $editProduto->execute([(int)$_GET['edit']]);
    $editProduto = $editProduto->fetch();
}

require_once __DIR__ . '/../includes/header.php';
?>

<?php if ($msg): ?>
<div class="mb-6 px-4 py-3 rounded-xl text-sm font-medium
    <?= $msgType === 'success' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-red-50 text-red-700 border border-red-200' ?>">
    <?= sanitize($msg) ?>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    <!-- Formulário -->
    <div class="xl:col-span-1">
        <div class="card p-6">
            <h2 class="font-bold text-slate-900 mb-5">
                <?= $editProduto ? '✎ Editar Produto' : '+ Novo Produto' ?>
            </h2>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="_action" value="<?= $editProduto ? 'update' : 'create' ?>">
                <?php if ($editProduto): ?>
                    <input type="hidden" name="id" value="<?= $editProduto['id'] ?>">
                <?php endif; ?>

                <div>
                    <label class="label">Nome do Produto *</label>
                    <input type="text" name="nome" class="input" required
                           value="<?= sanitize($editProduto['nome'] ?? '') ?>" placeholder="Ex: Notebook Dell">
                </div>
                <div>
                    <label class="label">Descrição</label>
                    <textarea name="descricao" class="input" rows="2" placeholder="Descrição do produto..."><?= sanitize($editProduto['descricao'] ?? '') ?></textarea>
                </div>
                <div>
                    <label class="label">Categoria</label>
                    <select name="categoria_id" class="input">
                        <option value="">— Sem categoria —</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?= $cat['id'] ?>"
                                <?= ($editProduto['categoria_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                <?= sanitize($cat['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="label">Preço (R$)</label>
                        <input type="number" name="preco" step="0.01" min="0" class="input"
                               value="<?= $editProduto['preco'] ?? '0.00' ?>" placeholder="0,00">
                    </div>
                    <div>
                        <label class="label">Quantidade</label>
                        <input type="number" name="quantidade" min="0" class="input"
                               value="<?= $editProduto['quantidade'] ?? '0' ?>" placeholder="0">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="label">Estoque Mínimo</label>
                        <input type="number" name="estoque_minimo" min="0" class="input"
                               value="<?= $editProduto['estoque_minimo'] ?? '5' ?>" placeholder="5">
                    </div>
                    <div>
                        <label class="label">Código de Barras</label>
                        <input type="text" name="codigo_barras" class="input"
                               value="<?= sanitize($editProduto['codigo_barras'] ?? '') ?>" placeholder="EAN-13">
                    </div>
                </div>
                <div class="flex gap-2 pt-2">
                    <button type="submit" class="btn-primary flex-1 justify-center">
                        <?= $editProduto ? 'Salvar Alterações' : 'Cadastrar Produto' ?>
                    </button>
                    <?php if ($editProduto): ?>
                        <a href="/pages/produtos.php" class="btn-secondary">Cancelar</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Lista de produtos -->
    <div class="xl:col-span-2">
        <div class="card">
            <!-- Filtros -->
            <div class="p-5 border-b border-slate-100">
                <form method="GET" class="flex gap-3">
                    <input type="text" name="q" value="<?= sanitize($search) ?>" placeholder="Buscar produto ou código..."
                           class="input flex-1">
                    <select name="cat" class="input w-40">
                        <option value="">Todas</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $catFilter == $cat['id'] ? 'selected' : '' ?>>
                                <?= sanitize($cat['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn-primary whitespace-nowrap">Filtrar</button>
                    <?php if ($search || $catFilter): ?>
                        <a href="/pages/produtos.php" class="btn-secondary">Limpar</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Tabela -->
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 text-xs text-slate-400 uppercase tracking-wide">
                            <th class="text-left px-5 py-3 font-semibold">Produto</th>
                            <th class="text-left px-3 py-3 font-semibold">Categoria</th>
                            <th class="text-right px-3 py-3 font-semibold">Preço</th>
                            <th class="text-right px-3 py-3 font-semibold">Qtd</th>
                            <th class="text-center px-3 py-3 font-semibold">Status</th>
                            <th class="text-center px-3 py-3 font-semibold">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php if (empty($produtos)): ?>
                            <tr><td colspan="6" class="text-center py-12 text-slate-400">Nenhum produto encontrado.</td></tr>
                        <?php else: foreach ($produtos as $p):
                            $st = statusEstoque($p['quantidade'], $p['estoque_minimo']);
                        ?>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-5 py-3.5">
                                <div class="font-semibold text-slate-800"><?= sanitize($p['nome']) ?></div>
                                <?php if ($p['codigo_barras']): ?>
                                    <div class="mono text-xs text-slate-400 mt-0.5"><?= sanitize($p['codigo_barras']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-3 py-3.5 text-slate-500"><?= sanitize($p['categoria'] ?? '—') ?></td>
                            <td class="px-3 py-3.5 text-right mono font-medium text-slate-700"><?= formatMoney($p['preco']) ?></td>
                            <td class="px-3 py-3.5 text-right mono font-bold text-slate-900"><?= $p['quantidade'] ?></td>
                            <td class="px-3 py-3.5 text-center">
                                <span class="badge <?= $st['class'] ?>"><?= $st['label'] ?></span>
                            </td>
                            <td class="px-3 py-3.5 text-center">
                                <div class="flex gap-1.5 justify-center">
                                    <a href="?edit=<?= $p['id'] ?>" class="btn-secondary py-1.5 px-2.5 text-xs">Editar</a>
                                    <form method="POST" onsubmit="return confirm('Excluir este produto?')">
                                        <input type="hidden" name="_action" value="delete">
                                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                        <button type="submit" class="btn-danger py-1.5 px-2.5 text-xs">Excluir</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="px-5 py-3 border-t border-slate-100 text-xs text-slate-400">
                <?= count($produtos) ?> produto(s) encontrado(s)
            </div>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
