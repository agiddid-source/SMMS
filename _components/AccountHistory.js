import { GhtChip } from './Chip.js';

// Static section containing the account activity heading and row list.
const ghtAccountHistoryTemplate = document.createElement('template');
ghtAccountHistoryTemplate.innerHTML = '<section class="ght-card"><div><p class="m-0 text-sm text-[#737373]">Payment history</p><h2 class="m-0 mt-2 text-xl font-medium tracking-[-.5px]">Payments and discounts</h2></div><ul class="ght-history-list m-0 mt-5 list-none divide-y divide-[#f5f5f5] p-0"></ul></section>';
// Static row layout for transaction description, date, actor, amount, and status.
const ghtHistoryRowTemplate = document.createElement('template');
ghtHistoryRowTemplate.innerHTML = '<li class="ght-history-row"><div><p class="ght-history-description m-0 text-sm font-medium"></p><p class="ght-history-date mb-0 mt-1 text-xs text-[#737373]"></p><p class="ght-history-actor mb-0 mt-1 text-xs text-[#737373]"></p></div><div class="text-left sm:text-right"><p class="ght-history-amount m-0 text-sm font-medium"></p><div class="ght-history-chip mt-2"></div></div></li>';

// Clones the section, formats each transaction, and appends the completed rows.
export function GhtAccountHistory({ ghtTransactions, ghtFormatNaira }) {
  const ghtSection = ghtAccountHistoryTemplate.content.cloneNode(true).firstElementChild;
  const ghtList = ghtSection.querySelector('.ght-history-list');
  // Clones a row, assigns transaction text, and appends its status chip.
  ghtTransactions.forEach((ghtTransaction) => {
    const ghtTone = ghtTransaction.status === 'Paid' ? 'success' : 'accent';
    const ghtPrefix = ghtTransaction.status === 'Discount' ? '−' : '';
    const ghtRow = ghtHistoryRowTemplate.content.cloneNode(true).firstElementChild;
    ghtRow.querySelector('.ght-history-description').textContent = ghtTransaction.description;
    ghtRow.querySelector('.ght-history-date').textContent = `${ghtTransaction.date} · ${ghtTransaction.reference}`;
    ghtRow.querySelector('.ght-history-actor').textContent = ghtTransaction.actor;
    ghtRow.querySelector('.ght-history-amount').textContent = `${ghtPrefix}${ghtFormatNaira(ghtTransaction.amount)}`;
    ghtRow.querySelector('.ght-history-chip').appendChild(GhtChip({ ghtLabel: ghtTransaction.status, ghtTone }));
    ghtList.appendChild(ghtRow);
  });
  Object.defineProperty(ghtSection, 'toString', { value: () => ghtSection.outerHTML });
  return ghtSection;
}
