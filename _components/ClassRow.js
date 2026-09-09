import { GhtChip } from './Chip.js';

const ghtClassManageRowTemplate = document.createElement('template');
ghtClassManageRowTemplate.innerHTML = `
  <li class="ght-fee-row flex items-center justify-between gap-3 py-3">
    <span class="min-w-0 flex-1 truncate text-sm font-medium"></span>
    <span class="ght-class-status shrink-0"></span>
    <span class="flex shrink-0 items-center gap-1">
      <button type="button" class="ght-class-edit-btn min-h-9 rounded-md px-2 text-sm font-medium text-[#737373]" data-ght-action="edit">Edit</button>
      <button type="button" class="ght-class-archive-btn min-h-9 rounded-md px-2 text-sm font-medium text-[#ef4444]" data-ght-action="archive">Archive</button>
    </span>
  </li>
`;

const ghtClassPickerRowTemplate = document.createElement('template');
ghtClassPickerRowTemplate.innerHTML = `
  <label class="flex min-h-9 cursor-pointer items-center gap-2 rounded-md px-1 text-sm">
    <input type="checkbox" class="ght-class-picker-checkbox h-4 w-4 shrink-0 rounded border-[#d4d4d4]" />
    <span class="truncate"></span>
  </label>
`;

export function GhtClassManageRow({ ghtClass }) {
  const ghtRow = ghtClassManageRowTemplate.content.cloneNode(true).firstElementChild;
  ghtRow.dataset.ghtId = ghtClass.id;
  ghtRow.querySelector('span.min-w-0').textContent = ghtClass.name;
  ghtRow.querySelector('.ght-class-status').appendChild(
    GhtChip({ ghtLabel: ghtClass.status === 'archived' ? 'Archived' : 'Active', ghtTone: ghtClass.status === 'archived' ? 'neutral' : 'success' })
  );
  Object.defineProperty(ghtRow, 'toString', { value: () => ghtRow.outerHTML });
  return ghtRow;
}

export function GhtClassPickerRow({ ghtClass, ghtChecked = false }) {
  const ghtRow = ghtClassPickerRowTemplate.content.cloneNode(true).firstElementChild;
  const ghtCheckbox = ghtRow.querySelector('.ght-class-picker-checkbox');
  ghtCheckbox.checked = ghtChecked;
  ghtCheckbox.dataset.ghtId = ghtClass.id;
  ghtRow.querySelector('span.truncate').textContent = ghtClass.name;
  Object.defineProperty(ghtRow, 'toString', { value: () => ghtRow.outerHTML });
  return ghtRow;
}
