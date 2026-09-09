// Maps navigation icon names to their SVG path data.
const ghtIconPaths = {
  grid: 'M4 4h6v6H4V4Zm10 0h6v6h-6V4ZM4 14h6v6H4v-6Zm10 0h6v6h-6v-6Z',
  ledger: 'M4 6h16M4 12h16M4 18h16M7 3v6m7 0v6m3 0v6',
  school: 'M4 20V7l8-4 8 4v13M8 10h8M8 14h8M8 20v-3h-4v3',
  users: 'M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2M9.5 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm10.5 1v6m3-3h-6',
  payment: 'M3 7h18M5 4h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Zm1 11h4',
  report: 'M5 3h14v18H5V3Zm4 4h6M9 11h6M9 15h3',
  chart: 'M5 19V9m7 10V5m7 14v-7'
};

// Static responsive shell containing the top bar, scrim, drawer, and rail slots.
const ghtSidebarTemplate = document.createElement('template');
ghtSidebarTemplate.innerHTML = '<div class="ght-sidebar-root"><header class="ght-mobile-topbar lg:hidden"><a href="./index.html" class="flex items-center gap-2 text-sm font-semibold text-[#0a0a0a] no-underline"><span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#262626] text-sm text-white">G</span>Greenhill OS</a><button class="ght-icon-button" type="button" aria-label="Open navigation" aria-controls="ght-mobile-drawer" aria-expanded="false" data-ght-sidebar-open><svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg></button></header><div id="ght-sidebar-scrim" class="ght-sidebar-scrim lg:hidden" data-ght-sidebar-close></div><div class="ght-sidebar-drawer"></div><div class="ght-sidebar-desktop-rail"></div></div>';
// Static rail layout containing branding, navigation, and proprietor profile areas.
const ghtSidebarRailTemplate = document.createElement('template');
ghtSidebarRailTemplate.innerHTML = '<aside class="flex flex-col bg-white p-4"><div class="ght-sidebar-brand mb-7 flex min-h-11 items-center justify-between gap-3 px-2"><a href="./index.html" class="flex min-w-0 items-center gap-3 text-[#0a0a0a] no-underline"><span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#262626] text-sm font-semibold text-white">G</span><span class="ght-sidebar-label text-sm font-semibold tracking-[-.3px]">Greenhill OS</span></a><button class="ght-sidebar-collapse" type="button" aria-label="Collapse sidebar" aria-expanded="true" data-ght-sidebar-collapse><svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M5 4h14v16H5zM10 4v16M15 8l-4 4 4 4"/></svg></button></div><div class="ght-navigation"></div><div class="ght-sidebar-profile"><div class="flex items-center gap-3 px-2"><span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#f7e7d8] text-xs font-medium text-[#915239]">AN</span><div><p class="m-0 text-sm font-medium">Adaeze Nwosu</p><p class="m-0 text-xs text-[#737373]">Proprietor</p></div></div></div></aside>';

// Creates an accessible SVG icon using the configured path or grid fallback.
function ghtNavIcon(ghtIcon) {
  const ghtIconElement = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
  ghtIconElement.setAttribute('viewBox', '0 0 24 24');
  ghtIconElement.setAttribute('class', 'h-[18px] w-[18px] shrink-0');
  ghtIconElement.setAttribute('fill', 'none');
  ghtIconElement.setAttribute('stroke', 'currentColor');
  ghtIconElement.setAttribute('stroke-width', '1.7');
  ghtIconElement.setAttribute('aria-hidden', 'true');
  const ghtPath = document.createElementNS('http://www.w3.org/2000/svg', 'path');
  ghtPath.setAttribute('d', ghtIconPaths[ghtIcon] || ghtIconPaths.grid);
  ghtIconElement.appendChild(ghtPath);
  return ghtIconElement;
}

// Creates a link, applies active and coming-soon states, and inserts its label.
function ghtNavLink({ ghtLabel, ghtHref, ghtIcon, ghtCurrentPath, ghtSubLink = false, ghtComingSoon = false }) {
  const ghtIsActive = ghtHref === ghtCurrentPath;
  const ghtLink = document.createElement('a');
  ghtLink.className = `${ghtSubLink ? 'ght-nav-sub-link' : 'ght-nav-link'} ${ghtIsActive ? 'ght-nav-link--active' : ''}`;
  ghtLink.href = ghtHref;
  if (ghtComingSoon) ghtLink.setAttribute('aria-label', `${ghtLabel} — coming soon`);
  if (ghtIcon) ghtLink.appendChild(ghtNavIcon(ghtIcon));
  const ghtLabelElement = document.createElement('span');
  ghtLabelElement.textContent = ghtLabel;
  ghtLink.appendChild(ghtLabelElement);
  if (ghtComingSoon) {
    const ghtSoon = document.createElement('span');
    ghtSoon.className = 'ml-auto text-[10px] text-[#737373]';
    ghtSoon.textContent = 'Soon';
    ghtLink.appendChild(ghtSoon);
  }
  return ghtLink;
}

// Filters navigation config into primary links, disclosure groups, and bottom links.
function ghtNavigationMarkup(ghtNavigationConfig, ghtCurrentPath) {
  const ghtNavigation = document.createElement('nav');
  ghtNavigation.className = 'flex min-h-0 flex-1 flex-col';
  ghtNavigation.setAttribute('aria-label', 'Main navigation');
  const ghtPrimary = document.createElement('div');
  ghtPrimary.className = 'grid gap-1';
  ghtNavigationConfig.filter((ghtItem) => ghtItem.ghtType === 'link').forEach((ghtItem) => ghtPrimary.appendChild(ghtNavLink({ ...ghtItem, ghtCurrentPath })));
  ghtNavigation.appendChild(ghtPrimary);
  const ghtGroups = document.createElement('div');
  ghtGroups.className = 'mt-5 grid gap-1';
  // Creates each configured group with a toggle and nested link panel.
  ghtNavigationConfig.filter((ghtItem) => ghtItem.ghtType === 'group').forEach((ghtGroup, ghtIndex) => {
    const ghtIsExpanded = ghtGroup.ghtItems.some((ghtItem) => ghtCurrentPath === ghtItem.ghtHref || ghtCurrentPath === ghtItem.ghtHref.split('#')[0]);
    const ghtSection = document.createElement('section');
    ghtSection.className = `ght-nav-group ${ghtIsExpanded ? 'ght-nav-group--open' : ''}`;
    const ghtToggle = document.createElement('button');
    ghtToggle.className = 'ght-nav-group-toggle';
    ghtToggle.type = 'button';
    ghtToggle.setAttribute('aria-expanded', String(ghtIsExpanded));
    ghtToggle.setAttribute('aria-controls', `ght-nav-group-${ghtIndex}`);
    ghtToggle.setAttribute('data-ght-nav-group-toggle', '');
    ghtToggle.appendChild(ghtNavIcon(ghtGroup.ghtIcon));
    const ghtGroupLabel = document.createElement('span');
    ghtGroupLabel.textContent = ghtGroup.ghtLabel;
    ghtToggle.appendChild(ghtGroupLabel);
    const ghtChevron = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    ghtChevron.setAttribute('class', 'ml-auto h-4 w-4');
    ghtChevron.setAttribute('viewBox', '0 0 24 24');
    ghtChevron.setAttribute('fill', 'none');
    ghtChevron.setAttribute('stroke', 'currentColor');
    ghtChevron.setAttribute('stroke-width', '1.8');
    const ghtChevronPath = document.createElementNS('http://www.w3.org/2000/svg', 'path');
    ghtChevronPath.setAttribute('d', 'm6 9 6 6 6-6');
    ghtChevron.appendChild(ghtChevronPath);
    ghtToggle.appendChild(ghtChevron);
    const ghtPanel = document.createElement('div');
    ghtPanel.id = `ght-nav-group-${ghtIndex}`;
    ghtPanel.className = 'ght-nav-group-panel';
    const ghtInner = document.createElement('div');
    ghtInner.className = 'ght-nav-group-inner';
    ghtGroup.ghtItems.forEach((ghtItem) => ghtInner.appendChild(ghtNavLink({ ...ghtItem, ghtCurrentPath, ghtSubLink: true })));
    ghtPanel.appendChild(ghtInner);
    ghtSection.append(ghtToggle, ghtPanel);
    ghtGroups.appendChild(ghtSection);
  });
  ghtNavigation.appendChild(ghtGroups);
  const ghtBottom = document.createElement('div');
  ghtBottom.className = 'mt-5 grid gap-1 border-t border-[#f5f5f5] pt-5';
  ghtNavigationConfig.filter((ghtItem) => ghtItem.ghtType === 'bottom').forEach((ghtItem) => ghtBottom.appendChild(ghtNavLink({ ...ghtItem, ghtCurrentPath, ghtComingSoon: true })));
  ghtNavigation.appendChild(ghtBottom);
  return ghtNavigation;
}

// Clones a rail, adds drawer-only controls, and inserts generated navigation.
function ghtSidebarRail({ ghtNavigationConfig, ghtCurrentPath, ghtDrawer = false }) {
  const ghtRail = ghtSidebarRailTemplate.content.cloneNode(true).firstElementChild;
  ghtRail.className = `${ghtDrawer ? 'ght-mobile-drawer' : 'ght-sidebar ght-sidebar-desktop'} flex flex-col bg-white p-4`;
  if (ghtDrawer) {
    ghtRail.id = 'ght-mobile-drawer';
    ghtRail.setAttribute('aria-hidden', 'true');
    const ghtMenu = document.createElement('div');
    ghtMenu.className = 'mb-6 flex items-center justify-between';
    ghtMenu.innerHTML = '<span class="text-sm font-semibold">Menu</span><button class="ght-icon-button" type="button" aria-label="Close navigation" data-ght-sidebar-close><svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 6l12 12M18 6 6 18"/></svg></button>';
    ghtRail.prepend(ghtMenu);
  }
  ghtRail.querySelector('.ght-navigation').replaceWith(ghtNavigationMarkup(ghtNavigationConfig, ghtCurrentPath));
  return ghtRail;
}

// Clones the shell and replaces its drawer and desktop slots with configured rails.
export function GhtSidebar({ ghtNavigationConfig, ghtCurrentPath }) {
  const ghtSidebar = ghtSidebarTemplate.content.cloneNode(true).firstElementChild;
  ghtSidebar.querySelector('.ght-sidebar-drawer').replaceWith(ghtSidebarRail({ ghtNavigationConfig, ghtCurrentPath, ghtDrawer: true }));
  ghtSidebar.querySelector('.ght-sidebar-desktop-rail').replaceWith(ghtSidebarRail({ ghtNavigationConfig, ghtCurrentPath }));
  return ghtSidebar;
}

// Connects drawer controls and group toggles to their DOM state changes.
export function GhtBindSidebar() {
  const ghtDrawer = document.querySelector('#ght-mobile-drawer');
  const ghtScrim = document.querySelector('#ght-sidebar-scrim');
  const ghtOpenButton = document.querySelector('[data-ght-sidebar-open]');
  const ghtCollapseButton = document.querySelector('.ght-sidebar-desktop [data-ght-sidebar-collapse]');
  const ghtSetDrawer = (ghtOpen) => {
    ghtDrawer.classList.toggle('ght-is-open', ghtOpen);
    ghtScrim.classList.toggle('ght-is-open', ghtOpen);
    ghtDrawer.setAttribute('aria-hidden', String(!ghtOpen));
    ghtOpenButton.setAttribute('aria-expanded', String(ghtOpen));
    document.body.classList.toggle('ght-drawer-open', ghtOpen);
  };
  ghtOpenButton.addEventListener('click', () => ghtSetDrawer(true));
  ghtCollapseButton.addEventListener('click', () => {
    const ghtCollapsed = document.body.classList.toggle('ght-sidebar-is-collapsed');
    ghtCollapseButton.setAttribute('aria-expanded', String(!ghtCollapsed));
    ghtCollapseButton.setAttribute('aria-label', ghtCollapsed ? 'Expand sidebar' : 'Collapse sidebar');
  });
  document.querySelectorAll('[data-ght-sidebar-close], #ght-mobile-drawer a').forEach((ghtElement) => ghtElement.addEventListener('click', () => ghtSetDrawer(false)));
  document.querySelectorAll('[data-ght-nav-group-toggle]').forEach((ghtToggle) => ghtToggle.addEventListener('click', () => {
    const ghtGroup = ghtToggle.closest('.ght-nav-group');
    const ghtOpen = !ghtGroup.classList.contains('ght-nav-group--open');
    ghtGroup.classList.toggle('ght-nav-group--open', ghtOpen);
    ghtToggle.setAttribute('aria-expanded', String(ghtOpen));
  }));
}
