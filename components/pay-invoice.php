<?php
/**
 * components/pay-invoice.php
 *
 * A printable fee invoice — the "Amount Due" artifact of the Bursar module and
 * the billing counterpart to pay-receipt.php. Rendered server-side and shown
 * inline on pages/invoices.php when a student is selected (?student=<id>). The
 * bill is derived live from the student's balance (see pay_get_invoice), so it
 * always reflects what is currently owed.
 *
 * The sheet reuses the receipt's .ght-receipt-sheet, so "Print invoice" works
 * through the existing scoped-print path with no new CSS/JS: assets/pay.js
 * toggles the ght-print-receipt body flag around window.print(), and the print
 * rules in styles/pay.css reveal just the sheet while hiding .ght-receipt-actions.
 *
 * Expected variables (set by the including page before require):
 *   $pay_invoice          array   invoice view-model from pay_get_invoice()
 *   $pay_invoice_student  array   the student it bills
 *   $pay_invoice_done_url string  href for the Back / close action
 */

$pay_invoice_done_url = $pay_invoice_done_url ?? 'index.php?p=invoices';
$pay_inv = $pay_invoice;
$pay_inv_student = $pay_invoice_student;
$pay_inv_totals = $pay_inv['totals'];
$pay_inv_has_balance = $pay_inv_totals['outstanding'] > 0;
$pay_inv_generated = (new DateTime())->format('j F Y');
?>
<div class="ght-receipt ght-invoice mt-6">
  <div class="ght-receipt-sheet ght-invoice-sheet" id="ght-invoice-sheet">
    <div class="flex items-start justify-between gap-4 border-b border-b-color pb-4">
      <div>
        <p class="m-0 text-xs font-semibold uppercase tracking-[.08em] text-[#737373]">Greenhill School</p>
        <h3 class="ght-display m-0 mt-1 text-2xl leading-none">Fee invoice</h3>
      </div>
      <span class="ght-chip ght-chip--<?= htmlspecialchars($pay_inv['statusTone']) ?>"><?= htmlspecialchars($pay_inv['status']) ?></span>
    </div>

    <div class="mt-4 grid grid-cols-2 gap-4">
      <div>
        <p class="m-0 text-xs text-[#737373]">Invoice number</p>
        <p class="ght-receipt-number m-0 mt-1 text-sm font-medium"><?= htmlspecialchars($pay_inv['number']) ?></p>
      </div>
      <div class="text-right">
        <p class="m-0 text-xs text-[#737373]">Period</p>
        <p class="m-0 mt-1 text-sm font-medium"><?= htmlspecialchars($pay_inv['period']) ?></p>
      </div>
    </div>

    <div class="mt-4 grid grid-cols-2 gap-4">
      <div>
        <p class="m-0 text-xs text-[#737373]">Billed to</p>
        <p class="m-0 mt-1 text-sm font-medium"><?= htmlspecialchars($pay_inv_student['name']) ?></p>
        <p class="m-0 mt-1 text-xs text-[#737373]"><?= htmlspecialchars($pay_inv_student['className'] . ' · ' . $pay_inv_student['studentNumber']) ?></p>
      </div>
      <div class="text-right">
        <p class="m-0 text-xs text-[#737373]">Guardian</p>
        <p class="m-0 mt-1 text-sm font-medium"><?= htmlspecialchars($pay_inv_student['guardian']) ?></p>
      </div>
    </div>

    <!-- Line items: assigned / discount / payable per fee. Columns collapse to
         fee + payable on narrow screens; the full breakdown shows from sm up. -->
    <div class="ght-invoice-lines mt-5 tabular-nums">
      <div class="grid grid-cols-[1fr_auto] gap-x-6 gap-y-3 sm:grid-cols-[1fr_6rem_6rem_6rem]">
        <span class="text-xs font-semibold uppercase tracking-[.06em] text-[#737373]">Fee</span>
        <span class="hidden text-right text-xs font-semibold uppercase tracking-[.06em] text-[#737373] sm:block">Assigned</span>
        <span class="hidden text-right text-xs font-semibold uppercase tracking-[.06em] text-[#737373] sm:block">Discount</span>
        <span class="text-right text-xs font-semibold uppercase tracking-[.06em] text-[#737373]">Payable</span>

        <?php foreach ($pay_inv['lines'] as $pay_line): ?>
          <div class="min-w-0 border-t border-b-color pt-3">
            <p class="m-0 truncate text-sm font-medium"><?= htmlspecialchars($pay_line['name']) ?></p>
            <?php if (!empty($pay_line['note'])): ?><p class="m-0 mt-1 text-xs text-[#737373]"><?= htmlspecialchars($pay_line['note']) ?></p><?php endif; ?>
          </div>
          <div class="hidden border-t border-b-color pt-3 text-right text-sm text-[#737373] sm:block"><?= ght_format_naira($pay_line['assigned'] ?? 0) ?></div>
          <div class="hidden border-t border-b-color pt-3 text-right text-sm sm:block"><?= ($pay_line['discount'] ?? 0) > 0 ? '&minus;' . ght_format_naira($pay_line['discount']) : '&mdash;' ?></div>
          <div class="border-t border-b-color pt-3 text-right text-sm font-medium"><?= ght_format_naira($pay_line['payable'] ?? 0) ?></div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Totals: subtotal, discounts, net payable, paid, balance due. -->
    <div class="mt-5 border-t border-b-color pt-4">
      <div class="ml-auto max-w-xs space-y-2 text-sm tabular-nums">
        <div class="flex items-center justify-between gap-4"><span class="text-[#737373]">Subtotal</span><span class="font-medium"><?= ght_format_naira($pay_inv_totals['assigned']) ?></span></div>
        <?php if ($pay_inv_totals['discount'] > 0): ?>
          <div class="flex items-center justify-between gap-4"><span class="text-[#737373]">Discounts</span><span class="font-medium">&minus;<?= ght_format_naira($pay_inv_totals['discount']) ?></span></div>
        <?php endif; ?>
        <div class="flex items-center justify-between gap-4 border-t border-b-color pt-2"><span class="text-[#737373]">Net payable</span><span class="font-medium"><?= ght_format_naira($pay_inv_totals['payable']) ?></span></div>
        <div class="flex items-center justify-between gap-4"><span class="text-[#737373]">Paid to date</span><span class="font-medium text-[#16803b]">&minus;<?= ght_format_naira($pay_inv_totals['paid']) ?></span></div>
        <div class="flex items-center justify-between gap-4 border-t border-b-color pt-2 text-base"><span class="font-medium">Balance due</span><span class="ght-receipt-amount font-medium tracking-[-.3px]"><?= ght_format_naira($pay_inv_totals['outstanding']) ?></span></div>
      </div>
    </div>

    <div class="mt-5 border-t border-b-color pt-4">
      <p class="m-0 text-xs text-[#737373]">Generated <?= htmlspecialchars($pay_inv_generated) ?> · Greenhill School OS</p>
      <?php if ($pay_inv_has_balance): ?>
        <p class="m-0 mt-1 text-xs text-[#737373]">Please settle the balance due at the Bursar desk, quoting the invoice number above.</p>
      <?php else: ?>
        <p class="m-0 mt-1 text-xs text-[#16803b]">This invoice is settled in full. Thank you.</p>
      <?php endif; ?>
    </div>
  </div>

  <div class="ght-receipt-actions mt-6 flex flex-wrap justify-end gap-2">
    <a href="<?= htmlspecialchars($pay_invoice_done_url) ?>" class="ght-button ght-button--secondary text-sm font-medium">Back to invoices</a>
    <?php if ($pay_inv_has_balance): ?>
      <a href="index.php?p=payments&student=<?= urlencode($pay_inv_student['id']) ?>" class="ght-button ght-button--secondary text-sm font-medium">Record payment</a>
    <?php endif; ?>
    <button type="button" class="ght-button ght-button--primary text-sm font-medium" data-pay-print>
      <span class="ght-button-icon"><svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M6 9V3h12v6M6 18H4a1 1 0 0 1-1-1v-5a1 1 0 0 1 1-1h16a1 1 0 0 1 1 1v5a1 1 0 0 1-1 1h-2M6 14h12v7H6z"/></svg></span>
      <span>Print invoice</span>
    </button>
  </div>
</div>
