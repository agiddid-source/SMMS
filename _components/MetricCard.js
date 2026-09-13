import { GhtCard } from './Card.js';
import { GhtChip } from './Chip.js';

// Static metric content layout for label, value, detail, trend, and accent icon.
const ghtMetricCardTemplate = document.createElement('template');
ghtMetricCardTemplate.innerHTML = '<div class="flex items-start justify-between gap-3"><p class="ght-metric-label m-0 text-sm text-[#737373]"></p><span class="ght-metric-accent flex items-center justify-center" aria-hidden="true"><svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 16.5 9 11l3 3 8-8"/><path d="M15 6h5v5"/></svg></span></div><div><p class="ght-metric-value m-0"></p><p class="ght-metric-detail mb-0 mt-2 text-sm text-[#737373]"></p></div><div class="ght-metric-chip"></div><a class="ght-metric-action" hidden></a>';

// Formats the value, inserts metric text, adds the trend chip, and wraps the card.
export function GhtMetricCard({ ghtLabel, ghtValue, ghtDetail, ghtChange, ghtTone, ghtFormatNaira, ghtActionHref, ghtActionLabel }) {
  const ghtValueText = ghtFormatNaira ? ghtFormatNaira(ghtValue) : new Intl.NumberFormat('en-NG').format(ghtValue);
  const ghtChipTone = ghtTone === 'success' ? 'success' : ghtTone === 'accent' ? 'accent' : 'neutral';
  const ghtContent = ghtMetricCardTemplate.content.cloneNode(true);
  ghtContent.querySelector('.ght-metric-label').textContent = ghtLabel;
  const ghtValueElement = ghtContent.querySelector('.ght-metric-value');
  ghtValueElement.className = 'ght-metric-value t-digit-group is-animating m-0';
  ghtValueText.split('').forEach((ghtCharacter, ghtIndex, ghtCharacters) => {
    const ghtDigit = document.createElement('span');
    ghtDigit.className = 't-digit';
    ghtDigit.textContent = ghtCharacter;
    if (ghtIndex === ghtCharacters.length - 2) ghtDigit.dataset.stagger = '1';
    if (ghtIndex === ghtCharacters.length - 1) ghtDigit.dataset.stagger = '2';
    ghtValueElement.appendChild(ghtDigit);
  });
  ghtContent.querySelector('.ght-metric-detail').textContent = ghtDetail;
  ghtContent.querySelector('.ght-metric-chip').appendChild(GhtChip({ ghtLabel: ghtChange, ghtTone: ghtChipTone }));
  const ghtAction = ghtContent.querySelector('.ght-metric-action');
  if (ghtActionHref) {
    ghtAction.href = ghtActionHref;
    ghtAction.textContent = `${ghtActionLabel} →`;
    ghtAction.hidden = false;
  }
  const ghtCard = GhtCard({
    ghtClassName: 'ght-metric-card flex flex-col justify-between',
    ghtContent
  });
  return ghtCard;
}
