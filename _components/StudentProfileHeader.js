import { GhtChip } from './Chip.js';

// Static header layout for identity, account status, and financial totals.
const ghtStudentProfileHeaderTemplate = document.createElement('template');
ghtStudentProfileHeaderTemplate.innerHTML = '<section class="ght-card ght-profile-header"><div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between"><div class="flex items-start gap-4"><span class="ght-student-initials flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-[#f7e7d8] text-lg font-medium text-[#915239]"></span><div><p class="m-0 text-xs font-semibold uppercase tracking-[.08em] text-[#737373]">Student financial profile</p><h1 class="ght-display mb-0 mt-2 text-4xl leading-none tracking-normal ght-student-name"></h1><p class="ght-student-meta mb-0 mt-2 text-sm text-[#737373]"></p></div></div><div class="flex flex-wrap items-center gap-3 xl:justify-end"><div class="ght-student-status"></div><button class="ght-button ght-button--secondary text-sm font-medium" type="button" data-ght-profile-action="statement"><span class="ght-button-icon"><svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M6 3h12v18H6zM9 7h6M9 11h6M9 15h3"/></svg></span><span>Statement</span></button><button class="ght-button ght-button--primary text-sm font-medium" type="button" data-ght-profile-action="payment"><span class="ght-button-icon"><svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 7h18M5 4h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Zm1 11h4"/></svg></span><span>Record payment</span></button></div></div><div class="mt-7 grid grid-cols-1 gap-4 border-t border-[#f5f5f5] pt-5 sm:grid-cols-3"><div><p class="m-0 text-xs text-[#737373]">Amount payable</p><p class="ght-student-payable m-0 mt-1 text-lg font-medium"></p></div><div><p class="m-0 text-xs text-[#737373]">Paid to date</p><p class="ght-student-paid m-0 mt-1 text-lg font-medium text-[#16803b]"></p></div><div><p class="m-0 text-xs text-[#737373]">Outstanding balance</p><p class="ght-student-outstanding m-0 mt-1 text-lg font-medium"></p></div></div></section>';

// Clones the header, assigns student details, and appends the account-status chip.
export function GhtStudentProfileHeader({ ghtStudent, ghtFormatNaira }) {
  const ghtHeader = ghtStudentProfileHeaderTemplate.content.cloneNode(true).firstElementChild;
  ghtHeader.querySelector('.ght-student-meta').previousElementSibling.previousElementSibling.textContent = 'Student account';
  ghtHeader.querySelector('.ght-student-initials').textContent = ghtStudent.initials;
  ghtHeader.querySelector('.ght-student-name').textContent = ghtStudent.name;
  ghtHeader.querySelector('.ght-student-meta').textContent = `${ghtStudent.className} · ${ghtStudent.studentNumber} · ${ghtStudent.guardian}`;
  ghtHeader.querySelector('.ght-student-status').appendChild(GhtChip({ ghtLabel: ghtStudent.accountStatus, ghtTone: 'success' }));
  ghtHeader.querySelector('.ght-student-payable').textContent = ghtFormatNaira(ghtStudent.summary.payable);
  ghtHeader.querySelector('.ght-student-paid').textContent = ghtFormatNaira(ghtStudent.summary.paid);
  ghtHeader.querySelector('.ght-student-outstanding').textContent = ghtFormatNaira(ghtStudent.summary.outstanding);
  Object.defineProperty(ghtHeader, 'toString', { value: () => ghtHeader.outerHTML });
  return ghtHeader;
}
