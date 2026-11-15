<?php
declare(strict_types=1);
?>
<div class="activity-item py-2 border-bottom" data-id="<?= e($item['id']) ?>">
    <?= e($item['actor_name'] ?? 'Someone') ?>
    <?= e(humanize_action($item['action'], is_array($item['meta'] ?? null) ? $item['meta'] : [])) ?>
    <small class="text-body-secondary"><?= e($item['created_at']) ?></small>
</div>
