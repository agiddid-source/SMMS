<?php
require_once __DIR__ . '/_inc/config.php';
require_once __DIR__ . '/_inc/format.php';
require_once __DIR__ . '/_inc/route.inc.php';
require_once __DIR__ . '/_inc/cn-data.php';
require_once __DIR__ . '/_inc/toast.php';


?>
<!DOCTYPE html>
<html lang="en">
<?php require __DIR__ . '/_inc/head.inc.php'; ?>
<body class="min-h-screen bg-white" data-page="<?= htmlspecialchars($page) ?>">
<?php require __DIR__ . '/components/sidebar.php'; ?>
<main class="ght-app-main min-w-0 flex-1 lg:pl-[232px]">
<?php require __DIR__ . '/' . $file; ?>
</main>
</body>
</html>
