<?php
/**
 * Status indicator element
 * 
 * @param string $status Status value (active, pending, inactive)
 * @param string $label Status label
 * @param string $class Additional CSS class
 */

$status = $status ?? 'pending';
$label = $label ?? 'Pendiente';
$class = $class ?? '';

$statusClasses = [
    'active' => 'status-active',
    'pending' => 'status-pending',
    'inactive' => 'status-inactive',
];

$statusClass = $statusClasses[$status] ?? 'status-pending';
?>

<div class="status-indicator <?= $statusClass ?> <?= $class ?>">
    <div class="status-dot"></div>
    <span class="status-label"><?= h($label) ?></span>
</div>
