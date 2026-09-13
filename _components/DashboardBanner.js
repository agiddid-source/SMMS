// Static banner layout for school identity, term, update date, and artwork.
const ghtDashboardBannerTemplate = document.createElement('template');
ghtDashboardBannerTemplate.innerHTML = '<section class="ght-banner ght-card"><div class="ght-banner-placeholder" aria-hidden="true"></div><svg class="ght-banner-art" viewBox="0 0 320 240" fill="none" aria-hidden="true"><path d="M28 196c39-92 95-141 168-147 47-4 77 16 96 53" stroke="currentColor" stroke-width="2"/><path d="M18 212c66-49 131-52 193-8 35 25 64 28 94 19" stroke="currentColor" stroke-width="2"/><circle cx="185" cy="49" r="20" stroke="currentColor" stroke-width="2"/></svg><div class="ght-banner-copy t-stagger is-shown"><p class="ght-banner-eyebrow t-stagger-line t-stagger-line--1"></p><h1 class="ght-display ght-banner-title t-stagger-line t-stagger-line--2"><span class="ght-banner-school"></span><br>Finance overview</h1><p class="ght-banner-updated t-stagger-line t-stagger-line--2"></p></div></section>';

// Clones the banner, assigns its text and label, and inserts the optional image.
export function GhtDashboardBanner({ ghtSchool, ghtTerm, ghtUpdated, ghtImageSource = '' }) {
  const ghtBanner = ghtDashboardBannerTemplate.content.cloneNode(true).firstElementChild;
  ghtBanner.setAttribute('aria-label', `${ghtSchool} financial overview`);
  ghtBanner.querySelector('.ght-banner-eyebrow').textContent = ghtTerm;
  ghtBanner.querySelector('.ght-banner-school').textContent = ghtSchool;
  ghtBanner.querySelector('.ght-banner-updated').textContent = ghtUpdated;
  if (ghtImageSource) {
    const ghtImage = document.createElement('img');
    ghtImage.src = ghtImageSource;
    ghtImage.alt = '';
    ghtImage.className = 'absolute inset-0 h-full w-full object-cover';
    ghtImage.addEventListener('error', () => ghtImage.remove());
    ghtBanner.insertBefore(ghtImage, ghtBanner.querySelector('.ght-banner-art'));
  }
  Object.defineProperty(ghtBanner, 'toString', { value: () => ghtBanner.outerHTML });
  return ghtBanner;
}
