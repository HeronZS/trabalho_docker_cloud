<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/setup.php';
require_once __DIR__ . '/../includes/functions.php';

$pageTitle    = 'Categorias';
$pageSubtitle = 'Gerencie as categorias de produtos';

$msg = '';
$msgType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['_action'] ?? '';

    if ($action === 'delete') {
        $id = (int)$_POST['id'];
        $count = $pdo->prepare("SELECT COUNT(*) FROM produtos WHERE categoria_id = ?");
        $count->execute([$id]);
        if ($count->fetchColumn() > 0) {
            $msg = 'Não é possível excluir: existem produtos nesta categoria.';
            $msgType = 'error';
        } else {
            $pdo->prepare("DELETE FROM categorias WHERE id = ?")->execute([$id]);
            $msg = 'Categoria excluída com sucesso.';
            $msgType = 'success';
        }
    }

    if (in_array($action, ['create', 'update'])) {
        $id       = (int)($_POST['id'] ?? 0);
        $nome     = trim($_POST['nome'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');

        if ($nome === '') {
            $msg = 'O nome da categoria é obrigatório.';
            $msgType = 'error';
        } else {
            if ($action === 'create') {
                $pdo->prepare("INSERT INTO categorias (nome, descricao) VALUES (?, ?)")->execute([$nome, $descricao]);
                $msg = 'Categoria criada com sucesso!';
            } else {
                $pdo->prepare("UPDATE categorias SET nome=?, descricao=? WHERE id=?")->execute([$nome, $descricao, $id]);
                $msg = 'Categoria atualizada com sucesso!';
            }
            $msgType = 'success';
        }
    }
}

$categorias = $pdo->query("
    SELECT c.*, COUNT(p.id) AS total_produtos
    FROM categorias c
    LEFT JOIN produtos p ON p.categoria_id = c.id
    GROUP BY c.id
    ORDER BY c.nome ASC
")->fetchAll();

$editCat = null;
if (isset($_GET['edit'])) {
    $editCat = $pdo->prepare("SELECT * FROM categorias WHERE id = ?");
    $editCat->execute([(int)$_GET['edit']]);
    $editCat = $editCat->fetch();
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
                <?= $editCat ? '✎ Editar Categoria' : '+ Nova Categoria' ?>
            </h2>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="_action" value="<?= $editCat ? 'update' : 'create' ?>">
                <?php if ($editCat): ?>
                    <input type="hidden" name="id" value="<?= $editCat['id'] ?>">
                <?php endif; ?>
                <div>
                    <label class="label">Nome *</label>
                    <input type="text" name="nome" class="input" required
                           value="<?= sanitize($editCat['nome'] ?? '') ?>" placeholder="Ex: Eletrônicos">
                </div>
                <div>
                    <label class="label">Descrição</label>
                    <textarea name="descricao" class="input" rows="3"
                              placeholder="Descrição da categoria..."><?= sanitize($editCat['descricao'] ?? '') ?></textarea>
                </div>
                <div class="flex gap-2 pt-2">
                    <button type="submit" class="btn-primary flex-1 justify-center">
                        <?= $editCat ? 'Salvar' : 'Criar Categoria' ?>
                    </button>
                    <?php if ($editCat): ?>
                        <a href="/pages/categorias.php" class="btn-secondary">Cancelar</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Lista -->
    <div class="xl:col-span-2">
        <div class="card">
            <div class="p-5 border-b border-slate-100">
                <h2 class="font-bold text-slate-900">Categorias cadastradas</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 text-xs text-slate-400 uppercase tracking-wide">
                            <th class="text-left px-5 py-3 font-semibold">Nome</th>
                            <th class="text-left px-3 py-3 font-semibold">Descrição</th>
                            <th class="text-center px-3 py-3 font-semibold">Produtos</th>
                            <th class="text-center px-3 py-3 font-semibold">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php if (empty($categorias)): ?>
                            <tr><td colspan="4" class="text-center py-12 text-slate-400">Nenhuma categoria cadastrada.</td></tr>
                        <?php else: foreach ($categorias as $cat): ?>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-5 py-3.5 font-semibold text-slate-800"><?= sanitize($cat['nome']) ?></td>
                            <td class="px-3 py-3.5 text-slate-500 text-xs"><?= sanitize($cat['descricao'] ?? '—') ?></td>
                            <td class="px-3 py-3.5 text-center">
                                <span class="badge bg-blue-50 text-blue-700 border border-blue-100"><?= $cat['total_produtos'] ?></span>
                            </td>
                            <td class="px-3 py-3.5 text-center">
                                <div class="flex gap-1.5 justify-center">
                                    <a href="?edit=<?= $cat['id'] ?>" class="btn-secondary py-1.5 px-2.5 text-xs">Editar</a>
                                    <form method="POST" onsubmit="return confirm('Excluir esta categoria?')">
                                        <input type="hidden" name="_action" value="delete">
                                        <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                                        <button type="submit" class="btn-danger py-1.5 px-2.5 text-xs">Excluir</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
