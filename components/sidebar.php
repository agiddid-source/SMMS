<?php

require_once __DIR__ . '/../_inc/navigation.php';

$ght_nav_icon_paths = [
    'grid' => 'M4 4h6v6H4V4Zm10 0h6v6h-6V4ZM4 14h6v6H4v-6Zm10 0h6v6h-6v-6Z',
    'ledger' => 'M4 6h16M4 12h16M4 18h16M7 3v6m7 0v6m3 0v6',
    'school' => 'M4 20V7l8-4 8 4v13M8 10h8M8 14h8M8 20v-3h-4v3',
    'users' => 'M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2M9.5 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm10.5 1v6m3-3h-6',
    'payment' => 'M3 7h18M5 4h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Zm1 11h4',
    'report' => 'M5 3h14v18H5V3Zm4 4h6M9 11h6M9 15h3',
    'chart' => 'M5 19V9m7 10V5m7 14v-7',
];
?>
<input type="checkbox" id="ght-drawer-toggle" class="ght-visually-hidden">
<input type="checkbox" id="ght-sidebar-collapse-toggle" class="ght-visually-hidden">
<div class="ght-sidebar-root">

  <header class="ght-mobile-topbar lg:hidden">
    <a href="index.php?p=dashboard" class="flex items-center gap-2 text-sm font-semibold text-[#0a0a0a] no-underline">
      <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#262626] text-sm text-white">G</span>Greenhill OS
    </a>
    <label class="ght-icon-button" for="ght-drawer-toggle" aria-label="Open navigation">
      <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
    </label>
  </header>

  <label class="ght-sidebar-scrim lg:hidden" for="ght-drawer-toggle" aria-hidden="true"></label>

  <aside class="ght-mobile-drawer flex flex-col bg-white p-4" id="ght-mobile-drawer">
    <div class="mb-6 flex items-center justify-between">
      <span class="text-sm font-semibold">Menu</span>
      <label class="ght-icon-button" for="ght-drawer-toggle" aria-label="Close navigation">
        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 6l12 12M18 6 6 18"/></svg>
      </label>
    </div>

    <div class="ght-sidebar-brand mb-7 flex min-h-11 items-center justify-between gap-3 px-2">
      <a href="index.php?p=dashboard" class="flex min-w-0 items-center gap-3 text-[#0a0a0a] no-underline">
        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#262626] text-sm font-semibold text-white">G</span>
        <span class="ght-sidebar-label text-sm font-semibold tracking-[-.3px]">Greenhill OS</span>
      </a>
    </div>

    <nav class="flex min-h-0 flex-1 flex-col" aria-label="Main navigation">
      <div class="grid gap-1">
        <?php foreach ($ght_navigation_config as $item): if ($item['type'] !== 'link') continue; ?>
          <a class="ght-nav-link<?= ght_nav_href_matches_current($item['href']) ? ' ght-nav-link--active' : '' ?>" href="<?= htmlspecialchars($item['href']) ?>">
            <svg viewBox="0 0 24 24" class="h-[18px] w-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="<?= $ght_nav_icon_paths[$item['icon']] ?? $ght_nav_icon_paths['grid'] ?>"/></svg>
            <span><?= htmlspecialchars($item['label']) ?></span>
          </a>
        <?php endforeach; ?>
      </div>

      <div class="mt-5 grid gap-1">
        <?php foreach ($ght_navigation_config as $group): if ($group['type'] !== 'group') continue;
          $group_open = false;
          foreach ($group['items'] as $group_item) { if (ght_nav_href_same_route($group_item['href'])) { $group_open = true; break; } }
        ?>
          <details class="ght-nav-group"<?= $group_open ? ' open' : '' ?>>
            <summary class="ght-nav-group-toggle">
              <svg viewBox="0 0 24 24" class="h-[18px] w-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="<?= $ght_nav_icon_paths[$group['icon']] ?? $ght_nav_icon_paths['grid'] ?>"/></svg>
              <span><?= htmlspecialchars($group['label']) ?></span>
              <svg class="ml-auto h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m6 9 6 6 6-6"/></svg>
            </summary>
            <div class="ght-nav-group-panel">
              <div class="ght-nav-group-inner">
                <?php foreach ($group['items'] as $group_item): ?>
                  <a class="ght-nav-sub-link<?= ght_nav_href_matches_current($group_item['href']) ? ' ght-nav-link--active' : '' ?>" href="<?= htmlspecialchars($group_item['href']) ?>">
                    <span><?= htmlspecialchars($group_item['label']) ?></span>
                  </a>
                <?php endforeach; ?>
              </div>
            </div>
          </details>
        <?php endforeach; ?>
      </div>

      <div class="mt-5 grid gap-1 border-t border-[#f5f5f5] pt-5">
        <?php foreach ($ght_navigation_config as $item): if ($item['type'] !== 'bottom') continue; ?>
          <a class="ght-nav-link<?= ght_nav_href_matches_current($item['href']) ? ' ght-nav-link--active' : '' ?>" href="<?= htmlspecialchars($item['href']) ?>" aria-label="<?= htmlspecialchars($item['label']) ?> — coming soon">
            <svg viewBox="0 0 24 24" class="h-[18px] w-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="<?= $ght_nav_icon_paths[$item['icon']] ?? $ght_nav_icon_paths['grid'] ?>"/></svg>
            <span><?= htmlspecialchars($item['label']) ?></span>
            <span class="ml-auto text-[10px] text-[#737373]">Soon</span>
          </a>
        <?php endforeach; ?>
      </div>
    </nav>

    <div class="ght-sidebar-profile">
      <div class="flex items-center gap-3 px-2">
        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#f7e7d8] text-xs font-medium text-[#915239]">AN</span>
        <div><p class="m-0 text-sm font-medium">Adaeze Nwosu</p><p class="m-0 text-xs text-[#737373]">Proprietor</p></div>
      </div>
    </div>
  </aside>

  <aside class="ght-sidebar ght-sidebar-desktop flex flex-col bg-white p-4">
    <div class="ght-sidebar-brand mb-7 flex min-h-11 items-center justify-between gap-3 px-2">
      <a href="index.php?p=dashboard" class="flex min-w-0 items-center gap-3 text-[#0a0a0a] no-underline">
        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#262626] text-sm font-semibold text-white">G</span>
        <span class="ght-sidebar-label text-sm font-semibold tracking-[-.3px]">Greenhill OS</span>
      </a>
      <label class="ght-sidebar-collapse" for="ght-sidebar-collapse-toggle" aria-label="Collapse sidebar">
        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M5 4h14v16H5zM10 4v16M15 8l-4 4 4 4"/></svg>
      </label>
    </div>

    <nav class="flex min-h-0 flex-1 flex-col" aria-label="Main navigation">
      <div class="grid gap-1">
        <?php foreach ($ght_navigation_config as $item): if ($item['type'] !== 'link') continue; ?>
          <a class="ght-nav-link<?= ght_nav_href_matches_current($item['href']) ? ' ght-nav-link--active' : '' ?>" href="<?= htmlspecialchars($item['href']) ?>">
            <svg viewBox="0 0 24 24" class="h-[18px] w-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="<?= $ght_nav_icon_paths[$item['icon']] ?? $ght_nav_icon_paths['grid'] ?>"/></svg>
            <span><?= htmlspecialchars($item['label']) ?></span>
          </a>
        <?php endforeach; ?>
      </div>

      <div class="mt-5 grid gap-1">
        <?php foreach ($ght_navigation_config as $group): if ($group['type'] !== 'group') continue;
          $group_open = false;
          foreach ($group['items'] as $group_item) { if (ght_nav_href_same_route($group_item['href'])) { $group_open = true; break; } }
        ?>
          <details class="ght-nav-group"<?= $group_open ? ' open' : '' ?>>
            <summary class="ght-nav-group-toggle">
              <svg viewBox="0 0 24 24" class="h-[18px] w-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="<?= $ght_nav_icon_paths[$group['icon']] ?? $ght_nav_icon_paths['grid'] ?>"/></svg>
              <span><?= htmlspecialchars($group['label']) ?></span>
              <svg class="ml-auto h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m6 9 6 6 6-6"/></svg>
            </summary>
            <div class="ght-nav-group-panel">
              <div class="ght-nav-group-inner">
                <?php foreach ($group['items'] as $group_item): ?>
                  <a class="ght-nav-sub-link<?= ght_nav_href_matches_current($group_item['href']) ? ' ght-nav-link--active' : '' ?>" href="<?= htmlspecialchars($group_item['href']) ?>">
                    <span><?= htmlspecialchars($group_item['label']) ?></span>
                  </a>
                <?php endforeach; ?>
              </div>
            </div>
          </details>
        <?php endforeach; ?>
      </div>

      <div class="mt-5 grid gap-1 border-t border-[#f5f5f5] pt-5">
        <?php foreach ($ght_navigation_config as $item): if ($item['type'] !== 'bottom') continue; ?>
          <a class="ght-nav-link<?= ght_nav_href_matches_current($item['href']) ? ' ght-nav-link--active' : '' ?>" href="<?= htmlspecialchars($item['href']) ?>" aria-label="<?= htmlspecialchars($item['label']) ?> — coming soon">
            <svg viewBox="0 0 24 24" class="h-[18px] w-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="<?= $ght_nav_icon_paths[$item['icon']] ?? $ght_nav_icon_paths['grid'] ?>"/></svg>
            <span><?= htmlspecialchars($item['label']) ?></span>
            <span class="ml-auto text-[10px] text-[#737373]">Soon</span>
          </a>
        <?php endforeach; ?>
      </div>
    </nav>

    <div class="ght-sidebar-profile">
      <div class="flex items-center gap-3 px-2">
        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#f7e7d8] text-xs font-medium text-[#915239]">AN</span>
        <div><p class="m-0 text-sm font-medium">Adaeze Nwosu</p><p class="m-0 text-xs text-[#737373]">Proprietor</p></div>
      </div>
    </div>
  </aside>

</div>
