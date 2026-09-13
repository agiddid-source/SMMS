<?php

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
$concession = $student['concession'];
$balance_status = $summary['outstanding'] === 0 ? 'Paid in full' : ght_format_naira($summary['outstanding']) . ' due';
$balance_tone = $summary['outstanding'] === 0 ? 'success' : 'accent';
$balance_note = $summary['outstanding'] === 0 ? 'All fees for this term are paid.' : 'Payments are recorded by the bursar.';

$has_balance = $summary['outstanding'] > 0;
$completion = $summary['payable'] ? round(($summary['paid'] / $summary['payable']) * 100) : 100;
$status_label = $has_balance ? ght_format_naira($summary['outstanding']) . ' due' : 'Paid in full';
$next_step = $has_balance ? 'Contact the guardian about the remaining balance.' : 'No payment is due for this term.';
$status_tone = $has_balance ? 'accent' : 'success';
$status_card_class = $has_balance ? 'ght-attention-card--pending' : 'ght-attention-card--clear';
$status_heading = $has_balance ? 'Payment needed.' : 'Paid in full.';
$status_mark = $has_balance ? '!' : '&#10003;';
$progress = min($completion, 100);
$first_name = explode(' ', $student['name'])[0];
?>
<div class="ght-dashboard-content ght-profile-content">
  <div class="ght-page-enter">
    <a class="ght-transaction-link inline-flex min-h-11 items-center text-sm font-medium" href="index.php?p=dashboard">&larr; Back to overview</a>

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
            <div><span class="ght-chip ght-chip--success"><?= htmlspecialchars($student['accountStatus']) ?></span></div>
            <a class="ght-button ght-button--secondary text-sm font-medium" href="index.php?p=coming-soon&module=Statement">
              <span class="ght-button-icon"><svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M6 3h12v18H6zM9 7h6M9 11h6M9 15h3"/></svg></span>
              <span class="ght-button-label">Statement</span>
            </a>
            <a class="ght-button ght-button--primary text-sm font-medium" href="index.php?p=coming-soon&module=Record+payment">
              <span class="ght-button-icon"><svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 7h18M5 4h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Zm1 11h4"/></svg></span>
              <span class="ght-button-label">Record payment</span>
            </a>
          </div>
        </div>
        <div class="mt-7 grid grid-cols-1 gap-4 border-t border-[#f5f5f5] pt-5 sm:grid-cols-3">
          <div><p class="m-0 text-xs text-[#737373]">Amount payable</p><p class="m-0 mt-1 text-lg font-medium"><?= ght_format_naira($summary['payable']) ?></p></div>
          <div><p class="m-0 text-xs text-[#737373]">Paid to date</p><p class="m-0 mt-1 text-lg font-medium text-[#16803b]"><?= ght_format_naira($summary['paid']) ?></p></div>
          <div><p class="m-0 text-xs text-[#737373]">Outstanding balance</p><p class="m-0 mt-1 text-lg font-medium"><?= ght_format_naira($summary['outstanding']) ?></p></div>
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
            <span class="ght-empty-orb flex items-center justify-center text-[#915239]" aria-hidden="true"><svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 21a8 8 0 0 0-16 0M12 13a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"/></svg></span>
          </div>
          <dl class="ght-summary-details mt-5">
            <div><dt>Relationship</dt><dd>Guardian</dd></div>
            <div><dt>Student ID</dt><dd><?= htmlspecialchars($student['studentNumber']) ?></dd></div>
            <div><dt>Class</dt><dd><?= htmlspecialchars($student['className']) ?></dd></div>
            <div><dt>Account status</dt><dd><?= htmlspecialchars($student['accountStatus']) ?></dd></div>
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
            <div><span class="ght-chip ght-chip--accent"><?= htmlspecialchars($concession['shortLabel']) ?></span></div>
          </div>
          <div class="mt-6 rounded-xl bg-[#f7e7d8] p-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:justify-between">
              <div>
                <p class="ght-concession-name m-0 text-sm font-medium"><?= htmlspecialchars($concession['name']) ?></p>
                <p class="ght-concession-description mb-0 mt-1 text-sm leading-5 text-[#737373]"><?= htmlspecialchars($concession['description']) ?></p>
              </div>
              <p class="ght-concession-amount m-0 shrink-0 text-lg font-medium text-[#915239]">−<?= ght_format_naira($concession['amount']) ?></p>
            </div>
            <p class="ght-concession-meta mb-0 mt-3 text-xs text-[#737373]">Applied <?= htmlspecialchars($concession['appliedOn']) ?> by <?= htmlspecialchars($concession['approvedBy']) ?> · <?= htmlspecialchars($concession['rule']) ?></p>
          </div>
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
            <span class="ght-fee-total text-[#737373]">Total fees <?= ght_format_naira($summary['assigned']) ?> · discount <?= ght_format_naira($concession['amount']) ?></span>
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
            <div class="flex justify-between gap-3 text-sm"><span class="text-[#737373]">Discount</span><span class="font-medium text-[#915239]">−<?= ght_format_naira($concession['amount']) ?></span></div>
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
      </section>
    </section>

  </div>
</div>
