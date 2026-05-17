<?php
function formatMoney(float $value): string {
    return 'R$ ' . number_format($value, 2, ',', '.');
}

function formatDate(string $date): string {
    return date('d/m/Y H:i', strtotime($date));
}

function statusEstoque(int $quantidade, int $minimo): array {
    if ($quantidade === 0) {
        return ['label' => 'Sem estoque', 'class' => 'bg-red-100 text-red-700 border border-red-200'];
    } elseif ($quantidade <= $minimo) {
        return ['label' => 'Estoque baixo', 'class' => 'bg-amber-100 text-amber-700 border border-amber-200'];
    }
    return ['label' => 'Normal', 'class' => 'bg-emerald-100 text-emerald-700 border border-emerald-200'];
}

function redirect(string $url): void {
    header("Location: $url");
    exit;
}

function sanitize(string $value): string {
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}
