<?php
/**
 * Promotion card element
 * 
 * @param array $promotion Promotion data
 */

$promotion = $promotion ?? [];
$title = $promotion['title'] ?? 'Promoción';
$description = $promotion['description'] ?? '';
$discount = $promotion['discount'] ?? '';
$type = $promotion['type'] ?? 'discount';
$active = $promotion['active'] ?? true;
$validUntil = $promotion['valid_until'] ?? '';

$typeClasses = [
    'discount' => 'promotion-discount',
    'benefit' => 'promotion-benefit',
    'special' => 'promotion-special',
];

$typeClass = $typeClasses[$type] ?? 'promotion-discount';
?>

<div class="promotion-card <?= $typeClass ?> <?= $active ? 'active' : 'inactive' ?>">
    <div class="promotion-header">
        <h3 class="promotion-title"><?= h($title) ?></h3>
        <?php if ($discount): ?>
            <div class="promotion-discount"><?= h($discount) ?></div>
        <?php endif; ?>
    </div>
    
    <div class="promotion-content">
        <p class="promotion-description"><?= h($description) ?></p>
        
        <?php if ($validUntil): ?>
            <div class="promotion-validity">
                <small>Válido hasta: <?= h($validUntil) ?></small>
            </div>
        <?php endif; ?>
    </div>
    
    <?php if ($active): ?>
        <div class="promotion-action">
            <button class="btn btn-primary btn-sm">Aplicar</button>
        </div>
    <?php endif; ?>
</div>
