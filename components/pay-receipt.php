<?php
/**
 * components/pay-receipt.php
 *
 * The payment receipt — the artifact the Bursar flow produces after a payment is
 * recorded. Rendered server-side (the JS prototype built it in the browser) and
 * shown in a checkbox-hack modal that opens on load, because the page arrives
 * here via the handler's Post/Redirect/Get with ?receipt=<id>. "Print receipt"
 * is enhanced by assets/pay.js (scoped print via the ght-print-receipt body
 * flag); "Done" is a plain link that closes the modal and cleans the URL.
 *
 * Expected variables (set by the including page before require):
 *   $pay_receipt_payment   array   the saved payment record
 *   $pay_receipt_student   array   the student it was recorded for
 *   $pay_receipt_done_url   string  href for Done / close (URL without ?receipt)
 *   $pay_receipt_modal_id   string  toggle id (default 'pay-receipt-modal')
 */

$pay_receipt_modal_id = $pay_receipt_modal_id ?? 'pay-receipt-modal';
$pay_receipt_done_url = $pay_receipt_done_url ?? 'index.php?p=payments';
$pay_r = $pay_receipt_payment;
$pay_r_student = $pay_receipt_student;
?>
<input type="checkbox" id="<?= htmlspecialchars($pay_receipt_modal_id) ?>" class="pay-modal-toggle" checked>
<div class="pay-modal-overlay">
  <a href="<?= htmlspecialchars($pay_receipt_done_url) ?>" class="pay-modal-backdrop" aria-hidden="true"></a>
  <div class="pay-modal-panel">
    <a href="<?= htmlspecialchars($pay_receipt_done_url) ?>" class="pay-modal-close" aria-label="Close">&times;</a>
    <h2 class="pay-modal-title">Payment recorded</h2>

    <div class="ght-receipt">
      <div class="ght-receipt-sheet" id="ght-receipt-sheet">
        <div class="flex items-start justify-between gap-4 border-b border-[#f5f5f5] pb-4">
          <div>
            <p class="m-0 text-xs font-semibold uppercase tracking-[.08em] text-[#737373]">Greenhill School</p>
            <h3 class="ght-display m-0 mt-1 text-2xl leading-none">Payment receipt</h3>
          </div>
          <span class="ght-chip ght-chip--success"><?= htmlspecialchars($pay_r['status']) ?></span>
        </div>

        <div class="mt-4 grid grid-cols-2 gap-4">
          <div>
            <p class="m-0 text-xs text-[#737373]">Receipt number</p>
            <p class="ght-receipt-number m-0 mt-1 text-sm font-medium"><?= htmlspecialchars($pay_r['receiptNumber']) ?></p>
          </div>
          <div class="text-right">
            <p class="m-0 text-xs text-[#737373]">Date</p>
            <p class="m-0 mt-1 text-sm font-medium"><?= htmlspecialchars($pay_r['dateLabel']) ?></p>
          </div>
        </div>

        <div class="mt-4 grid grid-cols-2 gap-4">
          <div>
            <p class="m-0 text-xs text-[#737373]">Student</p>
            <p class="m-0 mt-1 text-sm font-medium"><?= htmlspecialchars($pay_r_student['name']) ?></p>
            <p class="m-0 mt-1 text-xs text-[#737373]"><?= htmlspecialchars($pay_r_student['className'] . ' · ' . $pay_r_student['studentNumber']) ?></p>
          </div>
          <div class="text-right">
            <p class="m-0 text-xs text-[#737373]">Payer</p>
            <p class="m-0 mt-1 text-sm font-medium"><?= htmlspecialchars($pay_r_student['guardian']) ?></p>
            <p class="m-0 mt-1 text-xs text-[#737373]"><?= htmlspecialchars($pay_r['method']) ?></p>
          </div>
        </div>

        <div class="mt-5 rounded-xl bg-[#fafafa] p-4">
          <p class="m-0 text-xs text-[#737373]">Amount paid</p>
          <p class="ght-receipt-amount m-0 mt-1 text-3xl font-medium tracking-[-1px]"><?= ght_format_naira($pay_r['amount']) ?></p>
          <p class="m-0 mt-1 text-xs text-[#737373]"><?= htmlspecialchars($pay_r['purpose']) ?></p>
        </div>

        <?php if (!empty($pay_r['allocations'])): ?>
          <div class="mt-5">
            <p class="m-0 text-xs font-semibold uppercase tracking-[.06em] text-[#737373]">Applied to</p>
            <div class="mt-3 space-y-2">
              <?php foreach ($pay_r['allocations'] as $pay_alloc): ?>
                <div class="flex items-center justify-between gap-3 text-sm">
                  <span class="text-[#737373]"><?= htmlspecialchars($pay_alloc['feeName']) ?></span>
                  <span class="font-medium"><?= ght_format_naira($pay_alloc['amount']) ?></span>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

        <?php if (!empty($pay_r['note'])): ?>
          <p class="mb-0 mt-5 text-xs leading-5 text-[#737373]"><?= htmlspecialchars($pay_r['note']) ?></p>
        <?php endif; ?>
        <?php if (!empty($pay_r['attachment'])): ?>
          <p class="mb-0 mt-3 text-xs text-[#737373]">Attachment:
            <?php if (is_array($pay_r['attachment'])): ?>
              <a class="font-medium text-[#0a0a0a] underline" href="<?= htmlspecialchars($pay_r['attachment']['file']) ?>" target="_blank" rel="noopener"><?= htmlspecialchars($pay_r['attachment']['name']) ?></a>
            <?php else: ?>
              <span class="font-medium text-[#0a0a0a]"><?= htmlspecialchars($pay_r['attachment']) ?></span>
            <?php endif; ?>
          </p>
        <?php endif; ?>

        <div class="mt-5 border-t border-[#f5f5f5] pt-4">
          <p class="m-0 text-xs text-[#737373]"><?= htmlspecialchars($pay_r['actor']) ?></p>
        </div>
      </div>

      <div class="ght-receipt-actions mt-6 flex justify-end gap-2">
        <button type="button" class="ght-button ght-button--secondary text-sm font-medium" data-pay-print>
          <span class="ght-button-icon"><svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M6 9V3h12v6M6 18H4a1 1 0 0 1-1-1v-5a1 1 0 0 1 1-1h16a1 1 0 0 1 1 1v5a1 1 0 0 1-1 1h-2M6 14h12v7H6z"/></svg></span>
          <span>Print receipt</span>
        </button>
        <a href="<?= htmlspecialchars($pay_receipt_done_url) ?>" class="ght-button ght-button--primary text-sm font-medium">Done</a>
      </div>
    </div>
  </div>
</div>
