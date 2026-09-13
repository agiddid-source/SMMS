import { GhtChip } from './Chip.js';


const ghtFeeRowTemplate = document.createElement('template');
ghtFeeRowTemplate.innerHTML = `
  <li class="ght-fee-row" style="grid-template-columns: 1.4fr .8fr .9fr 1.4fr auto;">
    <div>
      <p class="m-0 text-sm font-medium ght-fee-row-name"></p>
      <p class="mb-0 mt-1 text-xs text-[#737373] ght-fee-row-note"></p>
    </div>
    <div>
      <p class="m-0 text-xs text-[#737373] md:hidden">Amount</p>
      <p class="m-0 text-sm font-medium ght-fee-row-amount"></p>
    </div>
    <div>
      <p class="m-0 text-xs text-[#737373] md:hidden">Due</p>
      <p class="m-0 text-sm text-[#737373] ght-fee-row-due"></p>
    </div>
    <div>
      <p class="m-0 text-xs text-[#737373] md:hidden">Applicable classes</p>
      <p class="m-0 text-sm text-[#737373] ght-fee-row-classes"></p>
    </div>
    <div class="flex shrink-0 items-center justify-end gap-1">
      <button type="button" class="ght-fee-row-edit-btn min-h-9 rounded-md px-2 text-sm font-medium text-[#737373]" data-ght-action="edit">Edit</button>
      <button type="button" class="ght-fee-row-deactivate-btn min-h-9 rounded-md px-2 text-sm font-medium text-[#ef4444]" data-ght-action="deactivate">Deactivate</button>
    </div>
  </li>
`;

// Formats an array of resolved class names for compact display in the row.
function ghtFormatClassNames(ghtNames) {
  if (ghtNames.length === 0) return 'None assigned';
  if (ghtNames.length <= 2) return ghtNames.join(', ');
  return `${ghtNames.slice(0, 2).join(', ')} +${ghtNames.length - 2} more`;
}

export function GhtFeeRow({ ghtFee, ghtTypeLabel, ghtClassNames, ghtFormatNaira }) {
  const ghtRow = ghtFeeRowTemplate.content.cloneNode(true).firstElementChild;
  ghtRow.dataset.ghtId = ghtFee.id;
  ghtRow.querySelector('.ght-fee-row-name').textContent = ghtFee.name;
  ghtRow.querySelector('.ght-fee-row-note').textContent = `${ghtTypeLabel} · ${ghtFee.academicSession} · ${ghtFee.term}`;
  ghtRow.querySelector('.ght-fee-row-amount').textContent = ghtFormatNaira(ghtFee.amount);
  ghtRow.querySelector('.ght-fee-row-due').textContent = ghtFee.dueDate || '—';
  ghtRow.querySelector('.ght-fee-row-classes').textContent = ghtFormatClassNames(ghtClassNames);
  Object.defineProperty(ghtRow, 'toString', { value: () => ghtRow.outerHTML });
  return ghtRow;
}
