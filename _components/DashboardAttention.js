// Builds the dashboard work queue from the financial summary and latest activity.
export function GhtDashboardAttention({ ghtDashboard, ghtTransactions, ghtFormatNaira }) {
  const ghtPendingTransactions = ghtTransactions.filter((ghtTransaction) => ghtTransaction.status === 'Pending');
  const ghtOutstandingMetric = ghtDashboard.metrics.find((ghtMetric) => ghtMetric.label === 'Outstanding fees');
  const ghtOverdueAmount = Number((ghtOutstandingMetric?.change.match(/[\d,]+/) || ['0'])[0].replace(/,/g, ''));
  const ghtOverdueLabel = `${ghtFormatNaira(ghtOverdueAmount)} overdue`;
  const ghtAttention = document.createElement('section');
  ghtAttention.className = 'ght-dashboard-attention grid gap-4 lg:grid-cols-[1.3fr_.7fr]';
  const ghtCollectionPercent = Math.round((ghtDashboard.collection.collected / ghtDashboard.collection.billed) * 100);
  ghtAttention.innerHTML = `<section class="ght-card ght-dashboard-attention-card"><div class="ght-dashboard-section-heading"><div><p class="m-0 text-xs font-semibold uppercase tracking-[.08em] text-[#737373]">Needs attention</p><h2 class="m-0 mt-2 text-2xl font-medium tracking-[-.5px]">Review these items</h2></div><span class="ght-attention-mark" aria-hidden="true">!</span></div><div class="ght-attention-list"><a href="./coming-soon.html?module=Ledger" class="ght-attention-item"><span class="ght-attention-icon ght-attention-icon--warm" aria-hidden="true">₦</span><span class="ght-attention-copy"><strong>${ghtOverdueLabel}</strong><small>47 families owe fees</small></span><span class="ght-attention-arrow" aria-hidden="true">→</span></a><a href="./coming-soon.html?module=Payments" class="ght-attention-item"><span class="ght-attention-icon" aria-hidden="true">↗</span><span class="ght-attention-copy"><strong>${ghtPendingTransactions.length || 0} pending payment${ghtPendingTransactions.length === 1 ? '' : 's'}</strong><small>Review pending payments</small></span><span class="ght-attention-arrow" aria-hidden="true">→</span></a><a href="./coming-soon.html?module=Expenses" class="ght-attention-item"><span class="ght-attention-icon" aria-hidden="true">₦</span><span class="ght-attention-copy"><strong>₦412,500 payroll due</strong><small>Due in September 2026</small></span><span class="ght-attention-arrow" aria-hidden="true">→</span></a></div></section><section class="ght-card ght-dashboard-status-card"><div class="ght-dashboard-section-heading"><div><p class="m-0 text-xs font-semibold uppercase tracking-[.08em] text-[#737373]">Fees collected</p><p class="ght-dashboard-status-caption">This term</p></div><span class="ght-chip ght-chip--success">On track</span></div><div class="ght-dashboard-health-row"><strong class="ght-dashboard-health-value">${ghtCollectionPercent}%</strong><span>${ghtFormatNaira(ghtDashboard.collection.collected)} collected</span></div><div class="ght-progress-track"><div class="ght-progress-fill" style="width: ${ghtCollectionPercent}%"></div></div><div class="ght-dashboard-health-meta"><span><small>Remaining</small><strong>${ghtFormatNaira(ghtDashboard.collection.due)}</strong></span><span><small>Families affected</small><strong>47</strong></span></div></section></section>`;
  Object.defineProperty(ghtAttention, 'toString', { value: () => ghtAttention.outerHTML });
  return ghtAttention;
}

// Creates a compact review list that turns recent pending activity into a next action.
export function GhtDashboardReview({ ghtTransactions, ghtFormatNaira }) {
  const ghtPendingTransactions = ghtTransactions.filter((ghtTransaction) => ghtTransaction.status === 'Pending');
  const ghtRows = ghtPendingTransactions.length ? ghtPendingTransactions : ghtTransactions.slice(0, 3);
  const ghtReview = document.createElement('section');
  ghtReview.className = 'ght-card ght-dashboard-review';
  ghtReview.innerHTML = `<div class="flex items-start justify-between gap-3"><div><p class="m-0 text-sm text-[#737373]">Payment review</p><h2 class="m-0 mt-2 text-xl font-medium tracking-[-.5px]">Recent payments</h2></div><span class="ght-chip ght-chip--${ghtPendingTransactions.length ? 'accent' : 'success'}">${ghtPendingTransactions.length ? `${ghtPendingTransactions.length} pending` : 'All clear'}</span></div><ul class="ght-dashboard-review-list m-0 mt-5 list-none divide-y divide-[#f5f5f5] p-0">${ghtRows.map((ghtTransaction) => `<li><div><strong>${ghtTransaction.name}</strong><small>${ghtTransaction.context} · ${ghtTransaction.time}</small></div><span>${ghtFormatNaira(ghtTransaction.amount)}</span></li>`).join('')}</ul><a class="ght-dashboard-inline-link mt-5 inline-flex text-sm font-medium" href="./coming-soon.html?module=Payments">View payments <span aria-hidden="true">→</span></a>`;
  Object.defineProperty(ghtReview, 'toString', { value: () => ghtReview.outerHTML });
  return ghtReview;
}
