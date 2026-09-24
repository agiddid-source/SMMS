<link rel="stylesheet" href="styles/pay.css">
<?php
/**
 * pages/invoices.php — Bursar invoices (the "Amount Due" side of the lifecycle).
 *
 * Lists every student's standing term invoice; selecting one (?student=<id>)
 * shows a printable invoice document. Invoices are derived live from each
 * student's fee lines and balance through the pay_* data layer — there is no
 * separate invoice store — so a bill always reflects the current balance.
 * Selection lives in the URL like the payments desk, so an invoice is shareable
 * and survives a refresh. Sibling to pages/payments.php; shares its styles/JS.
 */

$search = cure($_GET['search'] ?? '');
$selected_id = cure($_GET['student'] ?? '');

$all_students = pay_get_students();

$selected = $selected_id !== '' ? pay_get_student($selected_id) : null;
$invoice = $selected ? pay_get_invoice($selected) : null;

// Book totals for the overview tiles — summed across every student, independent
// of the search filter below (the whole term book, not just the visible rows).
$book_payable = 0.0;
$book_paid = 0.0;
$book_outstanding = 0.0;
foreach ($all_students as $book_student) {
    $book_payable += (float) ($book_student['summary']['payable'] ?? 0);
    $book_paid += (float) ($book_student['summary']['paid'] ?? 0);
    $book_outstanding += (float) ($book_student['summary']['outstanding'] ?? 0);
}

$results = pay_filter_students($all_students, $search);
?>
<div class="ght-dashboard-content ght-payments-content">
  <div class="ght-page-enter">
    <?php if ($invoice): ?>
      <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
          <p class="m-0 text-sm text-[#737373]">Bursar &amp; Payments</p>
          <h1 class="ght-display mb-0 mt-2 text-4xl leading-none tracking-normal">Invoice</h1>
          <p class="mb-0 mt-3 max-w-xl text-sm leading-6 text-[#737373]">The term bill for <?= htmlspecialchars($selected['name']) ?>, derived live from their current balance.</p>
        </div>
        <a class="ght-button ght-button--secondary text-sm font-medium" href="index.php?p=invoices">
          <span class="ght-button-icon"><svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M19 12H5M11 6l-6 6 6 6"/></svg></span>
          <span>All invoices</span>
        </a>
      </div>
      <?php
        $pay_invoice = $invoice;
        $pay_invoice_student = $selected;
        $pay_invoice_done_url = 'index.php?p=invoices';
        require __DIR__ . '/../components/pay-invoice.php';
      ?>
    <?php else: ?>
      <div>
        <p class="m-0 text-sm text-[#737373]">Bursar &amp; Payments</p>
        <h1 class="ght-display mb-0 mt-2 text-4xl leading-none tracking-normal">Invoices.</h1>
        <p class="mb-0 mt-3 max-w-xl text-sm leading-6 text-[#737373]">Every student&rsquo;s fee invoice for the term. Open one to review the breakdown and print it.</p>
      </div>

      <section class="mt-7 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="ght-card">
          <p class="m-0 text-sm text-[#737373]">Total billed</p>
          <p class="m-0 mt-2 text-2xl font-medium tracking-[-.5px]"><?= ght_format_naira($book_payable) ?></p>
          <p class="m-0 mt-1 text-xs text-[#737373]">Net payable across <?= count($all_students) ?> students</p>
        </div>
        <div class="ght-card">
          <p class="m-0 text-sm text-[#737373]">Collected</p>
          <p class="m-0 mt-2 text-2xl font-medium tracking-[-.5px] text-[#16803b]"><?= ght_format_naira($book_paid) ?></p>
          <p class="m-0 mt-1 text-xs text-[#737373]">Received to date</p>
        </div>
        <div class="ght-card">
          <p class="m-0 text-sm text-[#737373]">Outstanding</p>
          <p class="m-0 mt-2 text-2xl font-medium tracking-[-.5px]"><?= ght_format_naira($book_outstanding) ?></p>
          <p class="m-0 mt-1 text-xs text-[#737373]">Still due this term</p>
        </div>
      </section>

      <section class="ght-card mt-4">
        <form method="get" class="ght-field">
          <input type="hidden" name="p" value="invoices">
          <span class="ght-field-label">Search invoices</span>
          <div class="flex gap-2">
            <input type="search" name="search" class="ght-input" placeholder="Name, class or student number" value="<?= htmlspecialchars($search) ?>" aria-label="Search invoices">
            <button type="submit" class="ght-button ght-button--secondary text-sm font-medium">Search</button>
          </div>
        </form>

        <ul class="ght-student-results m-0 mt-4 list-none divide-y divide-b-color p-0">
          <?php if (count($results) === 0): ?>
            <li class="py-4 text-sm text-[#737373]">No invoices match &ldquo;<?= htmlspecialchars($search) ?>&rdquo;.</li>
          <?php else: foreach ($results as $result):
            $row_invoice = pay_get_invoice($result);
            $row_totals = $row_invoice['totals'];
            $row_href = 'index.php?p=invoices&student=' . urlencode($result['id']) . ($search !== '' ? '&search=' . urlencode($search) : '');
          ?>
            <li>
              <a class="ght-student-result flex items-center gap-4 py-4" href="<?= htmlspecialchars($row_href) ?>">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-b-color text-xs font-medium text-[#262626]"><?= htmlspecialchars($result['initials']) ?></span>
                <span class="min-w-0 flex-1">
                  <span class="block truncate text-sm font-medium"><?= htmlspecialchars($result['name']) ?></span>
                  <span class="mt-1 block truncate text-xs text-[#737373]"><?= htmlspecialchars($row_invoice['number'] . ' · ' . $row_invoice['period']) ?></span>
                  <span class="mt-1 block truncate text-xs text-[#737373]"><?= htmlspecialchars($result['className'] . ' · ' . $result['studentNumber']) ?></span>
                </span>
                <span class="hidden text-right sm:block">
                  <span class="block text-xs text-[#737373]">Payable</span>
                  <span class="block text-sm font-medium tabular-nums"><?= ght_format_naira($row_totals['payable']) ?></span>
                </span>
                <span class="hidden text-right sm:block">
                  <span class="block text-xs text-[#737373]">Paid</span>
                  <span class="block text-sm font-medium tabular-nums text-[#16803b]"><?= ght_format_naira($row_totals['paid']) ?></span>
                </span>
                <span class="text-right">
                  <span class="block text-xs text-[#737373]">Balance</span>
                  <span class="block text-sm font-medium tabular-nums"><?= ght_format_naira($row_totals['outstanding']) ?></span>
                  <span class="mt-1 block"><span class="ght-chip ght-chip--<?= htmlspecialchars($row_invoice['statusTone']) ?>"><?= htmlspecialchars($row_invoice['status']) ?></span></span>
                </span>
                <svg viewBox="0 0 24 24" class="h-4 w-4 shrink-0 text-[#a3a3a3]" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m9 6 6 6-6 6"/></svg>
              </a>
            </li>
          <?php endforeach; endif; ?>
        </ul>
      </section>
    <?php endif; ?>
  </div>
</div>
<script src="assets/pay.js" defer></script>
