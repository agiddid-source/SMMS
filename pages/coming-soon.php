<?php

$module = cure($_GET['module'] ?? '');
$heading = $module !== '' ? $module . ' is coming soon.' : 'This module is coming soon.';
?>
<main class="mx-auto flex min-h-screen max-w-xl items-center p-4 sm:p-6">
  <section class="ght-card t-resize w-full">
    <p class="m-0 text-sm text-[#737373]">Greenhill School OS</p>
    <h1 class="ght-display mb-0 mt-2 text-4xl"><?= htmlspecialchars($heading) ?></h1>
    <p class="mb-0 mt-4 text-sm leading-6 text-[#737373]">This destination is ready for its assigned team to build into. It remains linked so the application never strands an administrator at a dead end.</p>
    <div class="mt-7">
      <a class="ght-button ght-button--primary text-sm font-medium" href="index.php?p=dashboard">
        <span class="ght-button-label">Back to overview</span>
      </a>
    </div>
  </section>
</main>
