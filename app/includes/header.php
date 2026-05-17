<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'StockFlow — Gestão de Estoque' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'DM Sans', sans-serif; }
        .mono { font-family: 'DM Mono', monospace; }
        .sidebar-link { @apply flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-400 hover:text-white hover:bg-white/10 transition-all duration-200 text-sm font-medium; }
        .sidebar-link.active { @apply text-white bg-white/15 shadow-inner; }
        .card { @apply bg-white rounded-2xl border border-slate-100 shadow-sm; }
        .btn-primary { @apply bg-slate-900 text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-slate-700 transition-all duration-200 inline-flex items-center gap-2; }
        .btn-secondary { @apply bg-slate-100 text-slate-700 px-4 py-2 rounded-xl text-sm font-medium hover:bg-slate-200 transition-all duration-200 inline-flex items-center gap-2; }
        .btn-danger { @apply bg-red-50 text-red-600 px-4 py-2 rounded-xl text-sm font-medium hover:bg-red-100 transition-all duration-200 inline-flex items-center gap-2; }
        .input { @apply w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-slate-900 focus:border-transparent transition-all; }
        .label { @apply block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wide; }
        .badge { @apply px-2.5 py-1 rounded-lg text-xs font-semibold; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen">

<?php
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$navItems = [
    ['href' => '/index.php',        'label' => 'Dashboard',      'icon' => '◈', 'page' => 'index'],
    ['href' => '/pages/produtos.php','label' => 'Produtos',      'icon' => '▦', 'page' => 'produtos'],
    ['href' => '/pages/categorias.php','label' => 'Categorias',  'icon' => '◉', 'page' => 'categorias'],
    ['href' => '/pages/movimentacoes.php','label' => 'Movimentações','icon' => '⇅', 'page' => 'movimentacoes'],
];
?>

<div class="flex min-h-screen">
    <!-- Sidebar -->
    <aside class="w-64 bg-slate-900 min-h-screen flex flex-col fixed left-0 top-0 z-10">
        <!-- Logo -->
        <div class="p-6 border-b border-white/10">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-emerald-400 rounded-xl flex items-center justify-center text-slate-900 font-bold text-lg">S</div>
                <div>
                    <div class="text-white font-bold text-base leading-none">StockFlow</div>
                    <div class="text-slate-400 text-xs mt-0.5">Gestão de Estoque</div>
                </div>
            </div>
        </div>

        <!-- Nav -->
        <nav class="flex-1 p-4 space-y-1">
            <?php foreach ($navItems as $item): ?>
                <a href="<?= $item['href'] ?>"
                   class="sidebar-link <?= ($currentPage === $item['page']) ? 'active' : '' ?>">
                    <span class="text-base"><?= $item['icon'] ?></span>
                    <?= $item['label'] ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <!-- Footer da sidebar -->
        <div class="p-4 border-t border-white/10">
            <div class="text-xs text-slate-500 mono">v1.0.0 · Docker</div>
        </div>
    </aside>

    <!-- Main content -->
    <main class="flex-1 ml-64 min-h-screen">
        <!-- Topbar -->
        <header class="bg-white border-b border-slate-100 px-8 py-4 flex items-center justify-between sticky top-0 z-10">
            <div>
                <h1 class="text-lg font-bold text-slate-900"><?= $pageTitle ?? 'Dashboard' ?></h1>
                <?php if (!empty($pageSubtitle)): ?>
                    <p class="text-xs text-slate-400 mt-0.5"><?= $pageSubtitle ?></p>
                <?php endif; ?>
            </div>
            <div class="flex items-center gap-3">
                <div class="text-xs text-slate-400 mono bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-lg">
                    <?= date('d/m/Y H:i') ?>
                </div>
            </div>
        </header>

        <!-- Page content -->
        <div class="p-8">
