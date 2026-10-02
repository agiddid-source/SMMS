<link rel="stylesheet" href="styles/pay.css">
<?php
/**
 * pages/ledger.php — the Ledger (school cashbook).
 *
 * Phase 1 of the Ledger module: every recorded payment as a credit, in time
 * order, with a running cash balance — the Bursar's cashbook for the term.
 * Single-entry (debits arrive with the Expenses module later). Read-only: the
 * whole view is derived live from the pay_* data layer, so it always reflects
 * what has actually been recorded. Filters (date range, method, free text) live
 * in the URL like the rest of the Bursar module, so a view is shareable.
 * Sibling to pages/invoices.php; shares its styles.
 */

$filters = [
    'from'   => cure($_GET['from'] ?? ''),
    'to'     => cure($_GET['to'] ?? ''),
    'method' => cure($_GET['method'] ?? ''),
    'search' => cure($_GET['search'] ?? ''),
];

$ledger  = pay_get_ledger($filters);
$rows    = $ledger['rows'];
$summary = $ledger['summary'];
$methods = pay_methods();
$has_filter = $filters['from'] !== '' || $filters['to'] !== '' || $filters['method'] !== '' || $filters['search'] !== '';

// School-wide outstanding (whole book, independent of the filter) — summed the
// same way pages/invoices.php does so the figure matches across the module.
$book_outstanding = 0.0;
foreach (pay_get_students() as $book_student) {
    $book_outstanding += (float) ($book_student['summary']['outstanding'] ?? 0);
}
?>
<div class="ght-dashboard-content ght-payments-content">
  <div class="ght-page-enter">
    <div>
      <p class="m-0 text-sm text-[#737373]">Bursar &amp; Payments</p>
      <h1 class="ght-display mb-0 mt-2 text-4xl leading-none tracking-normal">Ledger.</h1>
      <p class="mb-0 mt-3 max-w-xl text-sm leading-6 text-[#737373]">Every payment received, the school cashbook for the term.</p>
    </div>

    <section class="mt-7 grid grid-cols-1 gap-4 sm:grid-cols-3">
      <div class="ght-card">
        <p class="m-0 text-sm text-[#737373]"><?= $has_filter ? 'Collected (in view)' : 'Collected' ?></p>
        <p class="m-0 mt-2 text-2xl font-medium tracking-[-.5px] text-[#16803b]"><?= ght_format_naira($summary['periodCollected']) ?></p>
        <p class="m-0 mt-1 text-xs text-[#737373]"><?= (int) $summary['periodCount'] ?> transaction<?= $summary['periodCount'] === 1 ? '' : 's' ?></p>
      </div>
      <div class="ght-card">
        <p class="m-0 text-sm text-[#737373]">Total received</p>
        <p class="m-0 mt-2 text-2xl font-medium tracking-[-.5px]"><?= ght_format_naira($summary['allCollected']) ?></p>
        <p class="m-0 mt-1 text-xs text-[#737373]">All receipts, all time</p>
      </div>
      <div class="ght-card">
        <p class="m-0 text-sm text-[#737373]">Outstanding</p>
        <p class="m-0 mt-2 text-2xl font-medium tracking-[-.5px]"><?= ght_format_naira($book_outstanding) ?></p>
        <p class="m-0 mt-1 text-xs text-[#737373]">Still due across the school</p>
      </div>
    </section>

    <?php if (!empty($summary['byMethod'])): ?>
      <div class="mt-4 flex flex-wrap items-center gap-2">
        <span class="text-xs text-[#737373]">By method:</span>
        <?php foreach ($summary['byMethod'] as $m_name => $m_sum): ?>
          <span class="ght-chip ght-chip--neutral"><?= htmlspecialchars($m_name) ?> &middot; <?= ght_format_naira($m_sum) ?></span>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <section class="ght-card mt-4">
      <form method="get" class="flex flex-wrap items-end gap-3">
        <input type="hidden" name="p" value="ledger">
        <label class="ght-field min-w-[220px] flex-1">
          <span class="ght-field-label">Search</span>
          <input type="search" name="search" class="ght-input" placeholder="Student, number, purpose or receipt" value="<?= htmlspecialchars($filters['search']) ?>">
        </label>
        <label class="ght-field min-w-[150px] flex-1">
          <span class="ght-field-label">From</span>
          <input type="date" name="from" class="ght-input" value="<?= htmlspecialchars($filters['from']) ?>">
        </label>
        <label class="ght-field min-w-[150px] flex-1">
          <span class="ght-field-label">To</span>
          <input type="date" name="to" class="ght-input" value="<?= htmlspecialchars($filters['to']) ?>">
        </label>
        <label class="ght-field min-w-[160px] flex-1">
          <span class="ght-field-label">Method</span>
          <select name="method" class="ght-input">
            <option value="">All methods</option>
            <?php foreach ($methods as $m_opt): ?>
              <option value="<?= htmlspecialchars($m_opt) ?>"<?= $filters['method'] === $m_opt ? ' selected' : '' ?>><?= htmlspecialchars($m_opt) ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <div class="flex shrink-0 items-end gap-2">
          <button type="submit" class="ght-button ght-button--primary text-sm font-medium">Apply</button>
          <?php if ($has_filter): ?>
            <a href="index.php?p=ledger" class="ght-button ght-button--secondary text-sm font-medium">Clear</a>
          <?php endif; ?>
        </div>
      </form>
    </section>

    <section class="ght-card mt-4">
      <?php if (count($rows) === 0): ?>
        <p class="py-8 text-center text-sm text-[#737373]">No transactions<?= $has_filter ? ' match these filters' : ' recorded yet' ?>.</p>
      <?php else: ?>
        <div class="overflow-x-auto">
          <table class="ght-ledger-table w-full text-sm">
            <thead>
              <tr class="text-left text-xs uppercase tracking-[.06em] text-[#737373]">
                <th class="py-3 pr-4 font-medium">Date</th>
                <th class="py-3 pr-4 font-medium">Student</th>
                <th class="py-3 pr-4 font-medium">Purpose</th>
                <th class="py-3 pr-4 font-medium">Method</th>
                <th class="py-3 pr-4 font-medium">Received by</th>
                <th class="py-3 pr-4 text-right font-medium">Amount</th>
                <th class="py-3 text-right font-medium">Balance</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($rows as $r): ?>
                <tr class="border-t border-[#f5f5f5] align-top">
                  <td class="py-3 pr-4 whitespace-nowrap">
                    <span class="block"><?= htmlspecialchars($r['dateLabel'] !== '' ? $r['dateLabel'] : substr($r['date'], 0, 10)) ?></span>
                    <?php if ($r['receiptNumber'] !== ''): ?><span class="mt-1 block text-xs text-[#737373]"><?= htmlspecialchars($r['receiptNumber']) ?></span><?php endif; ?>
                  </td>
                  <td class="py-3 pr-4">
                    <a class="font-medium text-[#0a0a0a] hover:underline" href="index.php?p=statement&student=<?= urlencode($r['studentId']) ?>"><?= htmlspecialchars($r['studentName']) ?></a>
                    <?php $meta = trim($r['className'] . ' · ' . $r['studentNumber'], ' ·'); ?>
                    <?php if ($meta !== ''): ?><span class="mt-1 block text-xs text-[#737373]"><?= htmlspecialchars($meta) ?></span><?php endif; ?>
                  </td>
                  <td class="py-3 pr-4"><?= htmlspecialchars($r['purpose']) ?></td>
                  <td class="py-3 pr-4 whitespace-nowrap"><?= htmlspecialchars($r['method']) ?></td>
                  <td class="py-3 pr-4 whitespace-nowrap"><?= htmlspecialchars($r['receivedBy']) ?></td>
                  <td class="py-3 pr-4 text-right font-medium tabular-nums whitespace-nowrap text-[#16803b]">+<?= ght_format_naira($r['amount']) ?></td>
                  <td class="py-3 text-right font-medium tabular-nums whitespace-nowrap"><?= ght_format_naira($r['balance']) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </section>
  </div>
</div>
<script src="assets/pay.js" defer></script>
