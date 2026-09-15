<?php
require_once __DIR__ . '/_inc/config.php';
require_once __DIR__ . '/_inc/format.php';
require_once __DIR__ . '/_inc/route.inc.php';
require_once __DIR__ . '/_inc/cn-data.php';
require_once __DIR__ . '/_inc/cn-filters.php';
require_once __DIR__ . '/_inc/toast.php';

// Generic per-page POST handler dispatch. Runs before any HTML output, so
// a handler is free to header('Location: ...') and exit — something a
// page file itself can no longer do once it's require'd below, since the
// shell (doctype/head/sidebar) will already have been echoed by then.
// Keyed by $page so this isn't Classes & Fees-specific: any future module
// gets Post/Redirect/Get for free just by adding _inc/handlers/{page}.php.
$cn_handler_file = __DIR__ . "/_inc/handlers/{$page}.php";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && is_file($cn_handler_file)) {
    require $cn_handler_file;
}
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
