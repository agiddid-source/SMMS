// Static section wrapper used by metric, module, and calculation cards.
const ghtCardTemplate = document.createElement('template');
ghtCardTemplate.innerHTML = '<section class="ght-card t-resize"></section>';

// Clones the section, applies its classes, and appends node or fragment content.
export function GhtCard({ ghtContent, ghtClassName = '' }) {
  const ghtCard = ghtCardTemplate.content.cloneNode(true).firstElementChild;
  ghtCard.className = `ght-card ${ghtClassName}`;
  if (ghtContent instanceof Node) ghtCard.appendChild(ghtContent);
  else if (ghtContent) ghtCard.appendChild(document.createRange().createContextualFragment(ghtContent));
  Object.defineProperty(ghtCard, 'toString', { value: () => ghtCard.outerHTML });
  return ghtCard;
}
