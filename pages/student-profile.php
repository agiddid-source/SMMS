<link rel="stylesheet" href="styles/cn.css">
<?php
render_toast();

$students_data = read_json('data/students.json');
$students = $students_data['students'] ?? [];
$student_id = cure($_GET['student'] ?? '') ?: 'sodiq-adeyemi';

$student = null;
foreach ($students as $candidate) {
    if ($candidate['id'] === $student_id) { $student = $candidate; break; }
}
if (!$student && $students) $student = $students[0];

if (!$student) {
    ?>
    <main class="mx-auto flex min-h-screen max-w-xl items-center p-6">
      <section class="ght-card t-resize w-full">
        <p class="m-0 text-sm text-[#737373]">Student account unavailable</p>
        <h1 class="ght-display mb-0 mt-2 text-3xl">We could not load this financial profile.</h1>
        <p class="mb-0 mt-4 text-sm text-[#737373]">Check that data/students.json exists and is valid JSON.</p>
      </section>
    </main>
    <?php
    return;
}

$summary = $student['summary'];
$concession = $student['concession'] ?? ['amount' => 0];
$has_discount = !empty($concession['amount']);

$is_paid_full = ($student['accountStatus'] === 'Paid in full' || $summary['outstanding'] === 0);
$is_unpaid    = ($student['accountStatus'] === 'Not yet paid' || $summary['paid'] === 0);

if ($is_paid_full) {
    $status_tone = 'success';
    $status_card_class = 'ght-attention-card--clear';
    $status_heading = 'Paid in full.';
    $status_mark = '&#10003;';
    $status_label = 'Paid in full';
    $next_step = 'All approved school fees for this term have been fully settled. No payment is due.';
    $balance_status = 'Paid in full';
    $balance_tone = 'success';
    $balance_note = 'All fees for this term are paid.';
} elseif ($is_unpaid) {
    $status_tone = 'neutral';
    $status_card_class = 'ght-attention-card--pending';
    $status_heading = 'Payment pending.';
    $status_mark = '!';
    $status_label = ght_format_naira($summary['payable']) . ' due';
    $next_step = 'No payment has been recorded yet for this term. Please contact the guardian regarding the outstanding fees.';
    $balance_status = ght_format_naira($summary['outstanding']) . ' due';
    $balance_tone = 'accent';
    $balance_note = 'First term fees are awaiting payment.';
} else {
    $status_tone = 'accent';
    $status_card_class = 'ght-attention-card--pending';
    $status_heading = 'Partial balance due.';
    $status_mark = '!';
    $status_label = ght_format_naira($summary['outstanding']) . ' due';
    $next_step = 'Guardian has made partial payments. Contact guardian regarding the remaining balance.';
    $balance_status = ght_format_naira($summary['outstanding']) . ' due';
    $balance_tone = 'accent';
    $balance_note = 'Payments are recorded by the bursar desk.';
}

$completion = $summary['payable'] ? round(($summary['paid'] / $summary['payable']) * 100) : 100;
$progress = min($completion, 100);
$first_name = explode(' ', $student['name'])[0];
?>
<div class="ght-dashboard-content ght-profile-content">
  <div class="ght-page-enter">
    <a class="ght-transaction-link inline-flex min-h-11 items-center text-sm font-medium" href="index.php?p=students">&larr; Back to students</a>

    <div class="mt-3">
      <section class="ght-card t-resize ght-profile-header">
        <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
          <div class="flex items-start gap-4">
            <span class="ght-student-initials flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-[#f7e7d8] text-lg font-medium text-[#915239]"><?= htmlspecialchars($student['initials']) ?></span>
            <div>
              <p class="m-0 text-xs font-semibold uppercase tracking-[.08em] text-[#737373]">Student account</p>
              <h1 class="ght-display mb-0 mt-2 text-4xl leading-none tracking-normal"><?= htmlspecialchars($student['name']) ?></h1>
              <p class="mb-0 mt-2 text-sm text-[#737373]"><?= htmlspecialchars($student['className'] . ' · ' . $student['studentNumber'] . ' · ' . $student['guardian']) ?></p>
            </div>
          </div>
          <div class="flex flex-wrap items-center gap-3 xl:justify-end">
            <div><span class="ght-chip ght-chip--<?= $status_tone ?>"><?= htmlspecialchars($student['accountStatus']) ?></span></div>
            <a class="ght-button ght-button--secondary text-sm font-medium" href="index.php?p=statement&student=<?= urlencode($student['id']) ?>">
              <span class="ght-button-icon"><svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M6 3h12v18H6zM9 7h6M9 11h6M9 15h3"/></svg></span>
              <span class="ght-button-label">Statement</span>
            </a>
            <label for="cn-record-payment" class="ght-button ght-button--primary text-sm font-medium cursor-pointer">
              <span class="ght-button-icon"><svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 7h18M5 4h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Zm1 11h4"/></svg></span>
              <span class="ght-button-label">Record payment</span>
            </label>
          </div>
        </div>
        <div class="mt-7 grid grid-cols-1 gap-4 border-t border-[#f5f5f5] pt-5 sm:grid-cols-3">
          <div><p class="m-0 text-xs text-[#737373]">Amount payable</p><p class="m-0 mt-1 text-lg font-medium"><?= ght_format_naira($summary['payable']) ?></p></div>
          <div><p class="m-0 text-xs text-[#737373]">Paid to date</p><p class="m-0 mt-1 text-lg font-medium text-[#16803b]"><?= ght_format_naira($summary['paid']) ?></p></div>
          <div><p class="m-0 text-xs text-[#737373]">Outstanding balance</p><p class="m-0 mt-1 text-lg font-medium <?= $summary['outstanding'] > 0 ? 'text-[#915239]' : 'text-[#16803b]' ?>"><?= ght_format_naira($summary['outstanding']) ?></p></div>
        </div>
      </section>
    </div>

    <div class="mt-6">
      <section class="ght-admin-summary grid gap-4 lg:grid-cols-[1.1fr_.9fr]">
        <section class="ght-card t-resize ght-attention-card <?= $status_card_class ?>">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="m-0 text-xs font-semibold uppercase tracking-[.08em] text-[#737373]">Payment status</p>
              <h2 class="m-0 mt-2 text-2xl font-medium tracking-[-.5px]"><?= htmlspecialchars($status_heading) ?></h2>
            </div>
            <div class="ght-attention-mark" aria-hidden="true"><?= $status_mark ?></div>
          </div>
          <p class="mb-0 mt-3 max-w-xl text-sm leading-6 text-[#525252]"><?= htmlspecialchars($next_step) ?></p>
          <div class="mt-6 flex flex-wrap items-center gap-3">
            <span class="ght-chip ght-chip--<?= $status_tone ?>"><?= htmlspecialchars($status_label) ?></span>
            <span class="text-xs text-[#737373]"><?= $completion ?>% of payable fees covered</span>
          </div>
          <div class="ght-progress-track mt-4"><div class="ght-progress-fill" style="width: <?= $progress ?>%"></div></div>
        </section>
        <section class="ght-card t-resize">
          <div class="flex items-start justify-between gap-3">
            <div>
              <p class="m-0 text-xs font-semibold uppercase tracking-[.08em] text-[#737373]">Primary contact</p>
              <h2 class="m-0 mt-2 text-xl font-medium tracking-[-.4px]"><?= htmlspecialchars($student['guardian']) ?></h2>
            </div>
            <span class="ght-empty-orb flex items-center justify-center text-[#915239]" aria-hidden="true">
              <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 21a8 8 0 0 0-16 0M12 13a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"/></svg>
            </span>
          </div>
          <dl class="ght-summary-details mt-5">
            <div><dt>Relationship</dt><dd><?= htmlspecialchars($student['guardianRelationship'] ?? 'Parent / Guardian') ?></dd></div>
            <div><dt>Phone number</dt><dd><a href="tel:<?= htmlspecialchars($student['phone'] ?? '+2348000000000') ?>" class="text-[#171717] hover:underline"><?= htmlspecialchars($student['phone'] ?? '+234 803 000 0000') ?></a></dd></div>
            <div><dt>Email address</dt><dd><a href="mailto:<?= htmlspecialchars($student['email'] ?? 'guardian@example.com') ?>" class="text-[#171717] hover:underline truncate block" title="<?= htmlspecialchars($student['email'] ?? 'guardian@example.com') ?>"><?= htmlspecialchars($student['email'] ?? 'guardian@example.com') ?></a></dd></div>
            <div><dt>Student ID</dt><dd><?= htmlspecialchars($student['studentNumber']) ?></dd></div>
            <div><dt>Class</dt><dd><?= htmlspecialchars($student['className']) ?></dd></div>
            <div><dt>Account status</dt><dd><span class="<?= $is_paid_full ? 'text-[#16803b]' : 'text-[#915239]' ?>"><?= htmlspecialchars($student['accountStatus']) ?></span></dd></div>
          </dl>
        </section>
      </section>
    </div>

    <section class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-[1.25fr_.75fr]">
      <div>
        <section id="ght-concessions" class="ght-card t-resize">
          <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
              <p class="ght-fee-eyebrow m-0 text-sm text-[#737373]">Fees and discount</p>
              <h2 class="ght-fee-heading m-0 mt-2 text-xl font-medium tracking-[-.5px]">Fees for <?= htmlspecialchars($first_name) ?></h2>
            </div>
            <div>
              <?php if ($has_discount && !empty($concession['shortLabel'])): ?>
                <span class="ght-chip ght-chip--accent"><?= htmlspecialchars($concession['shortLabel']) ?></span>
              <?php endif; ?>
            </div>
          </div>
          
          <?php if ($has_discount && !empty($concession['name'])): ?>
            <div class="mt-6 rounded-xl bg-[#f7e7d8] p-4">
              <div class="flex flex-col gap-3 sm:flex-row sm:justify-between">
                <div>
                  <p class="ght-concession-name m-0 text-sm font-medium"><?= htmlspecialchars($concession['name']) ?></p>
                  <p class="ght-concession-description mb-0 mt-1 text-sm leading-5 text-[#737373]"><?= htmlspecialchars($concession['description'] ?? '') ?></p>
                </div>
                <p class="ght-concession-amount m-0 shrink-0 text-lg font-medium text-[#915239]">−<?= ght_format_naira($concession['amount'] ?? 0) ?></p>
              </div>
              <p class="ght-concession-meta mb-0 mt-3 text-xs text-[#737373]">Applied <?= htmlspecialchars($concession['appliedOn'] ?? '') ?> by <?= htmlspecialchars($concession['approvedBy'] ?? '') ?> <?= !empty($concession['rule']) ? '· ' . htmlspecialchars($concession['rule']) : '' ?></p>
            </div>
          <?php endif; ?>

          <div class="mt-6 hidden grid-cols-[1.55fr_.75fr_.75fr_.75fr] gap-4 border-b border-[#f5f5f5] pb-3 text-xs text-[#737373] md:grid">
            <span>Fee item</span><span>Assigned</span><span>Discount</span><span>Amount due</span>
          </div>
          <ul class="ght-fee-list m-0 list-none divide-y divide-[#f5f5f5] p-0">
            <?php foreach ($student['fees'] as $fee):
              $discount_class = $fee['discount'] ? 'text-[#915239]' : 'text-[#737373]';
              $discount_text = $fee['discount'] ? '−' . ght_format_naira($fee['discount']) : '—';
            ?>
              <li class="ght-fee-row">
                <div><p class="ght-fee-name m-0 text-sm font-medium"><?= htmlspecialchars($fee['name']) ?></p><p class="ght-fee-note mb-0 mt-1 text-xs text-[#737373]"><?= htmlspecialchars($fee['note']) ?></p></div>
                <div><p class="m-0 text-xs text-[#737373] md:hidden">Assigned</p><p class="ght-fee-assigned m-0 text-sm text-[#737373]"><?= ght_format_naira($fee['assigned']) ?></p></div>
                <div><p class="m-0 text-xs text-[#737373] md:hidden">Discount</p><p class="ght-fee-discount m-0 text-sm <?= $discount_class ?>"><?= $discount_text ?></p></div>
                <div><p class="m-0 text-xs text-[#737373] md:hidden">Amount due</p><p class="ght-fee-payable-value m-0 text-sm font-medium"><?= ght_format_naira($fee['payable']) ?></p></div>
              </li>
            <?php endforeach; ?>
          </ul>
          <div class="mt-5 flex flex-col gap-2 border-t border-[#f5f5f5] pt-5 text-sm sm:flex-row sm:items-center sm:justify-between">
            <span class="ght-fee-total text-[#737373]">
              Total fees <?= ght_format_naira($summary['assigned']) ?><?php if ($has_discount): ?> · discount <?= ght_format_naira($concession['amount']) ?><?php endif; ?>
            </span>
            <span class="ght-fee-payable font-medium">Amount due <?= ght_format_naira($summary['payable']) ?></span>
          </div>
        </section>
      </div>

      <aside class="grid gap-4">
        <section class="ght-card t-resize">
          <p class="m-0 text-sm text-[#737373]">Term</p>
          <h2 class="m-0 mt-2 text-xl font-medium tracking-[-.5px]">First Term · 2026/27</h2>
          <div class="mt-6 flex items-center justify-between border-t border-[#f5f5f5] pt-5">
            <span class="text-sm text-[#737373]">Payment status</span>
            <span class="ght-chip ght-chip--<?= $balance_tone ?>"><?= htmlspecialchars($balance_status) ?></span>
          </div>
          <p class="mb-0 mt-4 text-sm leading-5 text-[#737373]"><?= htmlspecialchars($balance_note) ?></p>
        </section>
        <section class="ght-card t-resize">
          <p class="m-0 text-sm text-[#737373]">Fee summary</p>
          <div class="mt-5 space-y-4">
            <div class="flex justify-between gap-3 text-sm"><span class="text-[#737373]">Assigned fees</span><span class="font-medium"><?= ght_format_naira($summary['assigned']) ?></span></div>
            <?php if ($has_discount): ?>
              <div class="flex justify-between gap-3 text-sm"><span class="text-[#737373]">Discount</span><span class="font-medium text-[#915239]">−<?= ght_format_naira($concession['amount']) ?></span></div>
            <?php endif; ?>
            <div class="flex justify-between gap-3 border-t border-[#f5f5f5] pt-4 text-sm"><span class="font-medium">Amount payable</span><span class="font-medium"><?= ght_format_naira($summary['payable']) ?></span></div>
          </div>
        </section>
      </aside>
    </section>

    <section class="mt-6">
      <section class="ght-card t-resize">
        <div>
          <p class="m-0 text-sm text-[#737373]">Payment history</p>
          <h2 class="m-0 mt-2 text-xl font-medium tracking-[-.5px]">Payments and discounts</h2>
        </div>
        <?php if (empty($student['transactions'])): ?>
          <div class="py-12 text-center text-sm text-[#737373]">
            <p class="font-medium text-neutral-800 m-0">No transactions recorded yet</p>
            <p class="text-xs text-neutral-400 mt-1">Payment receipts and approved concessions will appear here once recorded.</p>
          </div>
        <?php else: ?>
          <ul class="ght-history-list m-0 mt-5 list-none divide-y divide-[#f5f5f5] p-0">
            <?php foreach ($student['transactions'] as $transaction):
              $history_tone = $transaction['status'] === 'Paid' ? 'success' : 'accent';
              $history_prefix = $transaction['status'] === 'Discount' ? '−' : '';
            ?>
              <li class="ght-history-row">
                <div>
                  <p class="ght-history-description m-0 text-sm font-medium"><?= htmlspecialchars($transaction['description']) ?></p>
                  <p class="ght-history-date mb-0 mt-1 text-xs text-[#737373]"><?= htmlspecialchars($transaction['date'] . ' · ' . $transaction['reference']) ?></p>
                  <p class="ght-history-actor mb-0 mt-1 text-xs text-[#737373]"><?= htmlspecialchars($transaction['actor']) ?></p>
                </div>
                <div class="text-left sm:text-right">
                  <p class="ght-history-amount m-0 text-sm font-medium"><?= $history_prefix ?><?= ght_format_naira($transaction['amount']) ?></p>
                  <div class="ght-history-chip mt-2"><span class="ght-chip ght-chip--<?= $history_tone ?>"><?= htmlspecialchars($transaction['status']) ?></span></div>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </section>
    </section>

  </div>
</div>

<!-- Record Payment Modal -->
<input type="checkbox" id="cn-record-payment" class="cn-modal-toggle">
<div class="cn-modal-overlay">
  <label for="cn-record-payment" class="cn-modal-backdrop" aria-hidden="true"></label>
  <div class="cn-modal-panel">
    <label for="cn-record-payment" class="cn-modal-close" aria-label="Close">&times;</label>
    
    <div class="flex items-center gap-3 mb-4">
      <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#171717] text-white">
        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 7h18M5 4h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Zm1 11h4"/></svg>
      </span>
      <div>
        <h2 class="cn-modal-title m-0">Record Fee Payment</h2>
        <p class="m-0 text-xs text-neutral-500 mt-0.5">Post a payment to <?= htmlspecialchars($student['name']) ?>'s account</p>
      </div>
    </div>

    <!-- Outstanding Balance Pill Box -->
    <div class="rounded-xl bg-neutral-50 border border-neutral-200/80 p-3.5 mb-5 flex items-center justify-between">
      <div>
        <span class="text-xs text-neutral-500 block">Current Outstanding Balance</span>
        <span class="text-lg font-bold <?= $summary['outstanding'] > 0 ? 'text-[#915239]' : 'text-[#16803b]' ?>">
          <?= ght_format_naira($summary['outstanding']) ?>
        </span>
      </div>
      <div class="text-right text-xs text-neutral-500">
        <div>Billed: <?= ght_format_naira($summary['payable']) ?></div>
        <div>Paid: <strong class="text-[#16803b]"><?= ght_format_naira($summary['paid']) ?></strong></div>
      </div>
    </div>

    <form method="post" action="index.php?p=student-profile&student=<?= urlencode($student['id']) ?>">
      <input type="hidden" name="action" value="record_payment">
      <input type="hidden" name="student_id" value="<?= htmlspecialchars($student['id']) ?>">

      <div class="space-y-4">
        <div>
          <label class="block text-xs font-semibold text-neutral-700 uppercase tracking-wider mb-1.5">
            Amount Paid (₦) *
          </label>
          <input type="number" name="amount" required min="100" max="500000" step="100"
                 class="cn-input text-base font-semibold"
                 placeholder="Enter amount in Naira"
                 value="<?= $summary['outstanding'] > 0 ? $summary['outstanding'] : '' ?>">
          <p class="m-0 text-[11px] text-neutral-400 mt-1">Pre-filled with remaining outstanding balance.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-semibold text-neutral-700 uppercase tracking-wider mb-1.5">
              Payment Method *
            </label>
            <select name="payment_method" class="cn-input cursor-pointer" required>
              <option value="Bank transfer">Bank Transfer</option>
              <option value="POS payment">POS Terminal</option>
              <option value="Cash payment">Cash (Bursary Desk)</option>
              <option value="Mobile transfer">Mobile Transfer / App</option>
              <option value="Bank draft">Bank Draft / Cheque</option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-semibold text-neutral-700 uppercase tracking-wider mb-1.5">
              Reference / Teller No.
            </label>
            <input type="text" name="reference" class="cn-input" placeholder="e.g. TRF-98234190 or Teller #">
          </div>
        </div>

        <div>
          <label class="block text-xs font-semibold text-neutral-700 uppercase tracking-wider mb-1.5">
            Payment Description / Note
          </label>
          <input type="text" name="description" class="cn-input" placeholder="e.g. First Term balance payment">
        </div>
      </div>

      <div class="cn-modal-actions mt-6">
        <label for="cn-record-payment" class="ght-button ght-button--secondary text-sm font-medium cursor-pointer">
          <span class="ght-button-label">Cancel</span>
        </label>
        <button type="submit" class="ght-button ght-button--primary text-sm font-medium">
          <span class="ght-button-icon">
            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg>
          </span>
          <span class="ght-button-label">Confirm payment</span>
        </button>
      </div>
    </form>
  </div>
</div>

