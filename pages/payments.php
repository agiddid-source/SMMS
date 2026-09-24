<link rel="stylesheet" href="styles/pay.css">
<?php
/**
 * pages/payments.php — the Bursar desk.
 *
 * Search for a student, review what they owe, record a payment and issue a
 * receipt. Server-rendered port of the JS payments page: selection and the
 * shown receipt live in the URL (?student=, ?receipt=) so the flow survives the
 * handler's Post/Redirect/Get and every state is shareable. The record form and
 * receipt are shared components; the shared toast confirms the save.
 */

$search = cure($_GET['search'] ?? '');
$selected_id = cure($_GET['student'] ?? '');
$receipt_id = cure($_GET['receipt'] ?? '');

$all_students = pay_get_students();
$results = pay_filter_students($all_students, $search);

$selected = $selected_id !== '' ? pay_get_student($selected_id) : null;
$selected_fees = $selected ? pay_get_outstanding_fees($selected['id']) : [];
$selected_payments = $selected ? pay_get_payments($selected['id']) : [];
$selected_has_balance = $selected && $selected['summary']['outstanding'] > 0;

// A receipt is shown when the handler redirected back with ?receipt=<id>.
$receipt = $receipt_id !== '' ? pay_find_payment($receipt_id) : null;
$receipt_student = $receipt ? pay_get_student($receipt['studentId']) : null;
?>
<?php render_toast(); ?>
<div class="ght-dashboard-content ght-payments-content">
  <div class="ght-page-enter">
    <div>
      <p class="m-0 text-sm text-[#737373]">Bursar &amp; Payments</p>
      <h1 class="ght-display mb-0 mt-2 text-4xl leading-none tracking-normal">Record a fee payment.</h1>
      <p class="mb-0 mt-3 max-w-xl text-sm leading-6 text-[#737373]">Search for a student, review what they owe, record the payment and issue a receipt.</p>
    </div>

    <section class="mt-7 grid grid-cols-1 gap-4 lg:grid-cols-[.85fr_1.15fr]">
      <div class="ght-card">
        <form method="get" class="ght-field">
          <input type="hidden" name="p" value="payments">
          <?php if ($selected_id !== ''): ?><input type="hidden" name="student" value="<?= htmlspecialchars($selected_id) ?>"><?php endif; ?>
          <span class="ght-field-label">Search student</span>
          <div class="flex gap-2">
            <input type="search" name="search" class="ght-input" placeholder="Name, class or student number" value="<?= htmlspecialchars($search) ?>" aria-label="Search student">
            <button type="submit" class="ght-button ght-button--secondary text-sm font-medium">Search</button>
          </div>
        </form>

        <ul class="ght-student-results m-0 mt-4 list-none divide-y divide-[#f5f5f5] p-0">
          <?php if (count($results) === 0): ?>
            <li class="py-4 text-sm text-[#737373]">No students match &ldquo;<?= htmlspecialchars($search) ?>&rdquo;.</li>
          <?php else: foreach ($results as $result):
            $result_outstanding = $result['summary']['outstanding'];
            $result_href = 'index.php?p=payments&student=' . urlencode($result['id']) . ($search !== '' ? '&search=' . urlencode($search) : '');
          ?>
            <li>
              <a class="ght-student-result flex w-full items-center gap-3 py-3 text-left<?= $selected && $selected['id'] === $result['id'] ? ' ght-student-result--active' : '' ?>" href="<?= htmlspecialchars($result_href) ?>">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#f5f5f5] text-xs font-medium text-[#262626]"><?= htmlspecialchars($result['initials']) ?></span>
                <span class="min-w-0 flex-1">
                  <span class="block truncate text-sm font-medium"><?= htmlspecialchars($result['name']) ?></span>
                  <span class="mt-1 block truncate text-xs text-[#737373]"><?= htmlspecialchars($result['className'] . ' · ' . $result['studentNumber']) ?></span>
                </span>
                <span class="text-xs font-medium <?= $result_outstanding > 0 ? 'text-[#915239]' : 'text-[#16803b]' ?>"><?= $result_outstanding > 0 ? ght_format_naira($result_outstanding) : 'Paid' ?></span>
              </a>
            </li>
          <?php endforeach; endif; ?>
        </ul>
      </div>

      <div class="ght-selected-panel">
        <?php if (!$selected): ?>
          <div class="ght-card flex min-h-[280px] flex-col items-center justify-center text-center">
            <span class="ght-empty-orb flex items-center justify-center text-[#915239]" aria-hidden="true">
              <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m21 21-4.35-4.35M11 19a8 8 0 1 0 0-16 8 8 0 0 0 0 16Z"/></svg>
            </span>
            <p class="m-0 mt-4 text-sm font-medium">No student selected</p>
            <p class="mb-0 mt-2 max-w-xs text-sm leading-5 text-[#737373]">Search on the left and choose a student to see their outstanding fees and payment history.</p>
          </div>
        <?php else:
          $balance_label = $selected_has_balance ? ght_format_naira($selected['summary']['outstanding']) . ' due' : 'Paid in full';
          $balance_tone = $selected_has_balance ? 'accent' : 'success';
        ?>
          <div class="ght-card">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
              <div class="flex items-start gap-4">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-[#f7e7d8] text-sm font-medium text-[#915239]"><?= htmlspecialchars($selected['initials']) ?></span>
                <div>
                  <h2 class="m-0 text-xl font-medium tracking-[-.5px]"><?= htmlspecialchars($selected['name']) ?></h2>
                  <p class="m-0 mt-1 text-sm text-[#737373]"><?= htmlspecialchars($selected['className'] . ' · ' . $selected['studentNumber']) ?></p>
                  <p class="m-0 mt-1 text-xs text-[#737373]">Guardian · <?= htmlspecialchars($selected['guardian']) ?></p>
                </div>
              </div>
              <span class="ght-chip ght-chip--<?= $balance_tone ?>"><?= htmlspecialchars($balance_label) ?></span>
            </div>

            <div class="mt-6 grid grid-cols-3 gap-4 border-t border-[#f5f5f5] pt-5">
              <div><p class="m-0 text-xs text-[#737373]">Payable</p><p class="m-0 mt-1 text-sm font-medium"><?= ght_format_naira($selected['summary']['payable']) ?></p></div>
              <div><p class="m-0 text-xs text-[#737373]">Paid</p><p class="m-0 mt-1 text-sm font-medium text-[#16803b]"><?= ght_format_naira($selected['summary']['paid']) ?></p></div>
              <div><p class="m-0 text-xs text-[#737373]">Outstanding</p><p class="m-0 mt-1 text-sm font-medium"><?= ght_format_naira($selected['summary']['outstanding']) ?></p></div>
            </div>

            <div class="mt-6 flex flex-wrap gap-2 border-t border-[#f5f5f5] pt-5">
              <?php if ($selected_has_balance): ?>
                <label for="pay-record-modal" class="ght-button ght-button--primary text-sm font-medium">
                  <span class="ght-button-icon"><svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 7h18M5 4h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Zm1 11h4"/></svg></span>
                  <span>Record payment</span>
                </label>
              <?php else: ?>
                <button type="button" class="ght-button ght-button--primary text-sm font-medium" disabled>
                  <span class="ght-button-icon"><svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 7h18M5 4h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Zm1 11h4"/></svg></span>
                  <span>Record payment</span>
                </button>
              <?php endif; ?>
              <a class="ght-button ght-button--secondary text-sm font-medium" href="index.php?p=student-profile&student=<?= urlencode($selected['id']) ?>">View full profile</a>
            </div>
          </div>

          <section class="ght-card mt-4">
            <div>
              <p class="m-0 text-sm text-[#737373]">Payment history</p>
              <h2 class="m-0 mt-2 text-xl font-medium tracking-[-.5px]">Receipts &amp; transactions</h2>
            </div>
            <ul class="ght-history-list m-0 mt-5 list-none divide-y divide-[#f5f5f5] p-0">
              <?php if (count($selected_payments) === 0): ?>
                <li class="py-4 text-sm text-[#737373]">No payments recorded yet.</li>
              <?php else: foreach ($selected_payments as $payment): ?>
                <li class="ght-history-row">
                  <div>
                    <p class="m-0 text-sm font-medium"><?= htmlspecialchars($payment['purpose']) ?></p>
                    <p class="mb-0 mt-1 text-xs text-[#737373]"><?= htmlspecialchars($payment['dateLabel'] . ' · ' . $payment['receiptNumber']) ?></p>
                    <p class="mb-0 mt-1 text-xs text-[#737373]"><?= htmlspecialchars($payment['method'] . ' · ' . $payment['actor']) ?></p>
                  </div>
                  <div class="text-left sm:text-right">
                    <p class="m-0 text-sm font-medium"><?= ght_format_naira($payment['amount']) ?></p>
                    <div class="mt-2"><span class="ght-chip ght-chip--success"><?= htmlspecialchars($payment['status']) ?></span></div>
                  </div>
                </li>
              <?php endforeach; endif; ?>
            </ul>
          </section>
        <?php endif; ?>
      </div>
    </section>
  </div>
</div>

<?php
// Record-payment modal for the selected student (toggle opened by the label
// above). Rendered whenever a student is selected so the form is present.
if ($selected):
    $pay_form_student = $selected;
    $pay_form_fees = $selected_fees;
    $pay_form_return_to = 'payments';
    $pay_form_modal_id = 'pay-record-modal';
    require __DIR__ . '/../components/pay-payment-form.php';
endif;

// Receipt modal, shown open when we arrived here via ?receipt=<id>.
if ($receipt && $receipt_student):
    $pay_receipt_payment = $receipt;
    $pay_receipt_student = $receipt_student;
    $pay_receipt_done_url = 'index.php?p=payments&student=' . urlencode($receipt['studentId']);
    require __DIR__ . '/../components/pay-receipt.php';
endif;
?>
<script src="assets/pay.js" defer></script>
