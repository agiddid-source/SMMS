// Static inline label used for neutral, accent, and success statuses.
const ghtChipTemplate = document.createElement('template');
ghtChipTemplate.innerHTML = '<span class="ght-chip"></span>';


export function GhtChip({ ghtLabel, ghtTone = 'neutral' }) {
  const ghtChip = ghtChipTemplate.content.cloneNode(true).firstElementChild;
  ghtChip.classList.add(`ght-chip--${ghtTone}`);
  ghtChip.textContent = ghtLabel;
  Object.defineProperty(ghtChip, 'toString', { value: () => ghtChip.outerHTML });
  return ghtChip;
}
