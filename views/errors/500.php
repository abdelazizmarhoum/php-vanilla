<?php
declare(strict_types=1);
?>
<div class="text-center py-5">
    <h1>Something went wrong</h1>
    <p class="text-muted">An unexpected error occurred. It has been logged.</p>
    <?= $details ?? '' ?>
    <a href="/" class="btn btn-primary">Back home</a>
</div>
