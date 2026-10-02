<link rel="stylesheet" href="styles/pay.css">
<?php
/**
 * pages/statement.php — a student's Statement of Account.
 *
 * Phase 2 of the Ledger module and the per-student counterpart to the cashbook
 * (pages/ledger.php): for one student, every fee charged (debit), every
 * concession (contra-credit), and every payment (credit), in that order, with a
 * running balance that closes on what the family still owes. Derived live from
 * pay_get_statement() — nothing new is stored. Printable the same scoped way the
 * receipt is (the ght-print-statement body flag in styles/pay.css, toggled by
 * assets/pay.js). Reached from a ledger row's student link and the student
 * account's "Statement" button.
 */

$statement_student_id = cure($_GET['student'] ?? '');
$statement = $statement_student_id !== '' ? pay_get_statement($statement_student_id) : null;

if (!$statement) {
    ?>
    <div class="ght-dashboard-content ght-payments-content">
      <div class="ght-page-enter">
        <a class="ght-transaction-link inline-flex min-h-11 items-center text-sm font-medium" href="index.php?p=ledger">&larr; Back to ledger</a>
        <section class="ght-card t-resize mt-3">
          <p class="m-0 text-sm text-[#737373]">Statement unavailable</p>
          <h1 class="ght-display mb-0 mt-2 text-3xl">We could not find that student account.</h1>
          <p class="mb-0 mt-4 text-sm text-[#737373]">Open a statement from the <a class="font-medium text-[#0a0a0a] underline" href="index.php?p=ledger">ledger</a> or a student's account page.</p>
        </section>
      </div>
    </div>
    <?php
    return;
}

$st_student = $statement['student'];
$st_entries = $statement['entries'];
$st_totals  = $statement['totals'];
$st_meta    = trim($st_student['className'] . ' · ' . $st_student['studentNumber'], ' ·');
$st_as_of   = date('j F Y');
$st_settled = $st_totals['outstanding'] <= 0;
?>
<div class="ght-dashboard-content ght-payments-content">
  <div class="ght-page-enter">
    <a class="ght-transaction-link inline-flex min-h-11 items-center text-sm font-medium" href="index.php?p=ledger">&larr; Back to ledger</a>

    <div class="mt-3 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
      <div>
        <p class="m-0 text-sm text-[#737373]">Bursar &amp; Payments</p>
        <h1 class="ght-display mb-0 mt-2 text-4xl leading-none tracking-normal">Statement.</h1>
        <p class="mb-0 mt-3 max-w-xl text-sm leading-6 text-[#737373]">A full account for one student — charges, concessions and payments, with the balance owed.</p>
      </div>
      <div class="flex flex-wrap items-center gap-2">
        <a class="ght-button ght-button--secondary text-sm font-medium" href="index.php?p=invoices&student=<?= urlencode($st_student['id']) ?>">
          <span class="ght-button-icon"><svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M15 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8l-5-5ZM14 3v5h5M9 13h6M9 17h4"/></svg></span>
          <span>View invoice</span>
        </a>
        <button type="button" class="ght-button ght-button--primary text-sm font-medium" data-pay-print data-print-flag="ght-print-statement">
          <span class="ght-button-icon"><svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M6 9V3h12v6M6 18H4a1 1 0 0 1-1-1v-5a1 1 0 0 1 1-1h16a1 1 0 0 1 1 1v5a1 1 0 0 1-1 1h-2M6 14h12v7H6z"/></svg></span>
          <span>Print statement</span>
        </button>
      </div>
    </div>

    <?php if (!$statement['reconciles']): ?>
      <div class="mt-5 rounded-xl border border-[#e9b949] bg-[#fdf6e3] p-4">
        <p class="m-0 text-sm font-medium text-[#8a6d1b]">This statement does not reconcile.</p>
        <p class="mb-0 mt-1 text-xs leading-5 text-[#8a6d1b]">The running balance (<?= ght_format_naira($st_totals['closing']) ?>) does not match the recorded outstanding (<?= ght_format_naira($st_totals['outstanding']) ?>). Check this student's fee lines and payment allocations.</p>
      </div>
    <?php endif; ?>

    <section class="ght-card mt-5">
      <div class="ght-statement-sheet" id="ght-statement-sheet">
        <div class="flex items-start justify-between gap-4 border-b border-[#f5f5f5] pb-4">
          <div>
            <p class="m-0 text-xs font-semibold uppercase tracking-[.08em] text-[#737373]">Greenhill School</p>
            <h2 class="ght-display m-0 mt-1 text-2xl leading-none">Statement of account</h2>
          </div>
          <span class="ght-chip ght-chip--<?= $st_settled ? 'success' : 'accent' ?>"><?= $st_settled ? 'Settled' : ght_format_naira($st_totals['outstanding']) . ' due' ?></span>
        </div>

        <div class="mt-4 grid grid-cols-2 gap-4">
          <div>
            <p class="m-0 text-xs text-[#737373]">Student</p>
            <p class="m-0 mt-1 text-sm font-medium"><?= htmlspecialchars($st_student['name']) ?></p>
            <?php if ($st_meta !== ''): ?><p class="m-0 mt-1 text-xs text-[#737373]"><?= htmlspecialchars($st_meta) ?></p><?php endif; ?>
            <?php if (!empty($st_student['guardian'])): ?><p class="m-0 mt-1 text-xs text-[#737373]">Guardian: <?= htmlspecialchars($st_student['guardian']) ?></p><?php endif; ?>
          </div>
          <div class="text-right">
            <p class="m-0 text-xs text-[#737373]">As of</p>
            <p class="m-0 mt-1 text-sm font-medium"><?= htmlspecialchars($st_as_of) ?></p>
            <p class="m-0 mt-1 text-xs text-[#737373]">First Term · 2026/27</p>
          </div>
        </div>

        <div class="mt-5 overflow-x-auto">
          <table class="ght-statement-table w-full text-sm">
            <thead>
              <tr class="text-left text-xs uppercase tracking-[.06em] text-[#737373]">
                <th class="py-2 pr-4 font-medium">Date</th>
                <th class="py-2 pr-4 font-medium">Description</th>
                <th class="py-2 pr-4 text-right font-medium">Charge</th>
                <th class="py-2 pr-4 text-right font-medium">Credit</th>
                <th class="py-2 text-right font-medium">Balance</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($st_entries as $e):
                $credit_class = $e['kind'] === 'payment' ? 'text-[#16803b]' : 'text-[#915239]';
              ?>
                <tr class="border-t border-[#f5f5f5] align-top">
                  <td class="py-3 pr-4 whitespace-nowrap text-[#737373]"><?= htmlspecialchars($e['dateLabel']) ?></td>
                  <td class="py-3 pr-4">
                    <span class="block font-medium"><?= htmlspecialchars($e['title']) ?></span>
                    <?php if ($e['meta'] !== ''): ?><span class="mt-1 block text-xs text-[#737373]"><?= htmlspecialchars($e['meta']) ?></span><?php endif; ?>
                  </td>
                  <td class="py-3 pr-4 text-right tabular-nums whitespace-nowrap"><?= $e['charge'] > 0 ? ght_format_naira($e['charge']) : '<span class="text-[#d4d4d4]">—</span>' ?></td>
                  <td class="py-3 pr-4 text-right tabular-nums whitespace-nowrap <?= $credit_class ?>"><?= $e['credit'] > 0 ? '−' . ght_format_naira($e['credit']) : '<span class="text-[#d4d4d4]">—</span>' ?></td>
                  <td class="py-3 text-right font-medium tabular-nums whitespace-nowrap"><?= ght_format_naira($e['balance']) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <div class="mt-5 border-t border-[#f5f5f5] pt-4">
          <div class="ml-auto w-full max-w-xs space-y-2 text-sm">
            <div class="flex justify-between gap-4"><span class="text-[#737373]">Total charged</span><span class="tabular-nums"><?= ght_format_naira($st_totals['charged']) ?></span></div>
            <div class="flex justify-between gap-4"><span class="text-[#737373]">Concessions</span><span class="tabular-nums text-[#915239]">−<?= ght_format_naira($st_totals['discount']) ?></span></div>
            <div class="flex justify-between gap-4"><span class="text-[#737373]">Net payable</span><span class="tabular-nums"><?= ght_format_naira($st_totals['netCharged']) ?></span></div>
            <div class="flex justify-between gap-4"><span class="text-[#737373]">Paid to date</span><span class="tabular-nums text-[#16803b]">−<?= ght_format_naira($st_totals['paid']) ?></span></div>
            <div class="flex justify-between gap-4 border-t border-[#f5f5f5] pt-2 text-base font-medium"><span>Balance due</span><span class="tabular-nums"><?= ght_format_naira($st_totals['closing']) ?></span></div>
          </div>
        </div>

        <p class="mb-0 mt-6 border-t border-[#f5f5f5] pt-4 text-xs text-[#737373]">Charges are shown at their full term amount with concessions applied as credits. Payments are listed oldest first. This statement is generated from the school's records as of <?= htmlspecialchars($st_as_of) ?>.</p>
      </div>
    </section>
  </div>
</div>
<script src="assets/pay.js" defer></script>
