import { GhtChip } from './Chip.js';


const ghtFeeBreakdownTemplate = document.createElement('template');
ghtFeeBreakdownTemplate.innerHTML = '<section id="ght-concessions" class="ght-card"><div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"><div><p class="ght-fee-eyebrow m-0 text-sm text-[#737373]">Assigned fees & concession</p><h2 class="ght-fee-heading m-0 mt-2 text-xl font-medium tracking-[-.5px]"></h2></div><div class="ght-fee-chip"></div></div><div class="mt-6 rounded-xl bg-[#f7e7d8] p-4"><div class="flex flex-col gap-3 sm:flex-row sm:justify-between"><div><p class="ght-concession-name m-0 text-sm font-medium"></p><p class="ght-concession-description mb-0 mt-1 text-sm leading-5 text-[#737373]"></p></div><p class="ght-concession-amount m-0 shrink-0 text-lg font-medium text-[#915239]"></p></div><p class="ght-concession-meta mb-0 mt-3 text-xs text-[#737373]"></p></div><div class="mt-6 hidden grid-cols-[1.55fr_.75fr_.75fr_.75fr] gap-4 border-b border-[#f5f5f5] pb-3 text-xs text-[#737373] md:grid"><span>Fee item</span><span>Assigned</span><span>Concession</span><span>Payable</span></div><ul class="ght-fee-list m-0 list-none divide-y divide-[#f5f5f5] p-0"></ul><div class="mt-5 flex flex-col gap-2 border-t border-[#f5f5f5] pt-5 text-sm sm:flex-row sm:items-center sm:justify-between"><span class="ght-fee-total text-[#737373]"></span><span class="ght-fee-payable font-medium"></span></div></section>';

const ghtFeeRowTemplate = document.createElement('template');
ghtFeeRowTemplate.innerHTML = '<li class="ght-fee-row"><div><p class="ght-fee-name m-0 text-sm font-medium"></p><p class="ght-fee-note mb-0 mt-1 text-xs text-[#737373]"></p></div><div><p class="m-0 text-xs text-[#737373] md:hidden">Assigned</p><p class="ght-fee-assigned m-0 text-sm text-[#737373]"></p></div><div><p class="m-0 text-xs text-[#737373] md:hidden">Concession</p><p class="ght-fee-discount m-0 text-sm"></p></div><div><p class="m-0 text-xs text-[#737373] md:hidden">Payable</p><p class="ght-fee-payable-value m-0 text-sm font-medium"></p></div></li>';

export function GhtFeeBreakdown({ ghtStudent, ghtFormatNaira }) {
  const ghtSection = ghtFeeBreakdownTemplate.content.cloneNode(true).firstElementChild;
  ghtSection.innerHTML = ghtSection.innerHTML.replace('Assigned fees & concession', 'Fees and discount').replace('Concession', 'Discount').replace('Payable', 'Amount due');
  const ghtFirstName = ghtStudent.name.split(' ')[0];
  ghtSection.querySelector('.ght-fee-heading').textContent = `Fees for ${ghtFirstName}`;
  ghtSection.querySelector('.ght-fee-chip').appendChild(GhtChip({ ghtLabel: ghtStudent.concession.shortLabel, ghtTone: 'accent' }));
  ghtSection.querySelector('.ght-concession-name').textContent = ghtStudent.concession.name;
  ghtSection.querySelector('.ght-concession-description').textContent = ghtStudent.concession.description;
  ghtSection.querySelector('.ght-concession-amount').textContent = `−${ghtFormatNaira(ghtStudent.concession.amount)}`;
  ghtSection.querySelector('.ght-concession-meta').textContent = `Applied ${ghtStudent.concession.appliedOn} by ${ghtStudent.concession.approvedBy} · ${ghtStudent.concession.rule}`;

  const ghtFeeList = ghtSection.querySelector('.ght-fee-list');

  ghtStudent.fees.forEach((ghtFee) => {
    const ghtFeeRow = ghtFeeRowTemplate.content.cloneNode(true).firstElementChild;
    ghtFeeRow.querySelector('.ght-fee-name').textContent = ghtFee.name;
    ghtFeeRow.querySelector('.ght-fee-note').textContent = ghtFee.note;
    ghtFeeRow.querySelector('.ght-fee-assigned').textContent = ghtFormatNaira(ghtFee.assigned);
    const ghtDiscount = ghtFeeRow.querySelector('.ght-fee-discount');
    ghtDiscount.textContent = ghtFee.discount ? `−${ghtFormatNaira(ghtFee.discount)}` : '—';
    ghtDiscount.classList.add(ghtFee.discount ? 'text-[#915239]' : 'text-[#737373]');
    ghtFeeRow.querySelector('.ght-fee-payable-value').textContent = ghtFormatNaira(ghtFee.payable);
    ghtFeeList.appendChild(ghtFeeRow);
  });
  ghtSection.querySelector('.ght-fee-total').textContent = `Total fees ${ghtFormatNaira(ghtStudent.summary.assigned)} · discount ${ghtFormatNaira(ghtStudent.concession.amount)}`;
  ghtSection.querySelector('.ght-fee-payable').textContent = `Amount due ${ghtFormatNaira(ghtStudent.summary.payable)}`;
  Object.defineProperty(ghtSection, 'toString', { value: () => ghtSection.outerHTML });
  return ghtSection;
}
