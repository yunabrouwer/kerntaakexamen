<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
startSecureSession();
$pageTitle = 'Bedankt';
require __DIR__ . '/includes/header.php';
?>
<main>
    <section class="card" style="max-width: 650px; margin: 0 auto; text-align: center;">
        <h1>Bedankt voor je feedback!</h1>
        <p class="muted">We waarderen dat je de tijd hebt genomen om je ervaring te delen.</p>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
