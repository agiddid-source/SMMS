import { GhtChip } from './Chip.js';

// Static row layout for initials, transaction context, amount, and status.
const ghtTransactionRowTemplate = document.createElement('template');
ghtTransactionRowTemplate.innerHTML = '<li class="ght-transaction-row"><div class="ght-transaction-row-link"><span class="ght-transaction-initials flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#f5f5f5] text-xs font-medium text-[#262626]"></span><span class="min-w-0 flex-1"><span class="ght-transaction-name block truncate text-sm font-medium"></span><span class="ght-transaction-context mt-1 block truncate text-xs text-[#737373]"></span></span><span class="text-left sm:text-right"><span class="ght-transaction-amount block text-sm font-medium"></span><span class="ght-transaction-chip mt-1 block"></span></span></div></li>';

// Clones the row, optionally converts its wrapper to a link, and inserts values.
export function GhtTransactionRow({ ghtTransaction, ghtFormatNaira, ghtHref = '' }) {
  const ghtTone = ghtTransaction.status === 'Paid' ? 'success' : ['Concession', 'Discount'].includes(ghtTransaction.status) ? 'accent' : 'neutral';
  const ghtRow = ghtTransactionRowTemplate.content.cloneNode(true).firstElementChild;
  let ghtLink = ghtRow.querySelector('.ght-transaction-row-link');
  if (ghtHref) {
    const ghtAnchor = document.createElement('a');
    ghtAnchor.className = ghtLink.className;
    ghtAnchor.href = ghtHref;
    while (ghtLink.firstChild) ghtAnchor.appendChild(ghtLink.firstChild);
    ghtLink.replaceWith(ghtAnchor);
    ghtLink = ghtAnchor;
  }
  ghtRow.querySelector('.ght-transaction-initials').textContent = ghtTransaction.initials;
  ghtRow.querySelector('.ght-transaction-name').textContent = ghtTransaction.name;
  ghtRow.querySelector('.ght-transaction-context').textContent = `${ghtTransaction.context} · ${ghtTransaction.time}`;
  ghtRow.querySelector('.ght-transaction-amount').textContent = ghtFormatNaira(ghtTransaction.amount);
  ghtRow.querySelector('.ght-transaction-chip').appendChild(GhtChip({ ghtLabel: ghtTransaction.status, ghtTone }));
  Object.defineProperty(ghtRow, 'toString', { value: () => ghtRow.outerHTML });
  return ghtRow;
}
