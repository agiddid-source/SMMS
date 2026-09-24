<?php
/**
 * components/pay-payment-form.php
 *
 * The Record Payment modal — the heart of the Bursar module. Rendered as a
 * shared partial so both the payments desk and a student profile open the same
 * form. Follows the Classes & Fees checkbox-hack modal pattern (no JS needed to
 * open/close or submit); assets/pay.js layers on the live total, per-fee caps
 * and "Pay full balance" as progressive enhancement. The form posts multipart
 * to the payments handler, which re-validates and clamps everything server-side.
 *
 * Expected variables (set by the including page before require):
 *   $pay_form_student    array   the student being paid for
 *   $pay_form_fees       array   their outstanding fee lines (payable > 0)
 *   $pay_form_return_to  string  'payments' | 'student-profile' — where to return
 *   $pay_form_modal_id   string  toggle id a <label for> opens it with
 */

$pay_form_modal_id = $pay_form_modal_id ?? 'pay-record-modal';
$pay_form_return_to = $pay_form_return_to ?? 'payments';
$pay_methods = ['Cash', 'Bank transfer', 'POS', 'Cheque', 'Mobile money'];
$pay_form_has_fees = count($pay_form_fees) > 0;
?>
<input type="checkbox" id="<?= htmlspecialchars($pay_form_modal_id) ?>" class="pay-modal-toggle">
<div class="pay-modal-overlay">
  <label for="<?= htmlspecialchars($pay_form_modal_id) ?>" class="pay-modal-backdrop" aria-hidden="true"></label>
  <div class="pay-modal-panel">
    <label for="<?= htmlspecialchars($pay_form_modal_id) ?>" class="pay-modal-close" aria-label="Close">&times;</label>
    <h2 class="pay-modal-title">Record payment</h2>

    <form class="ght-payment-form" method="post" action="index.php?p=payments" enctype="multipart/form-data" data-pay-form>
      <input type="hidden" name="action" value="record">
      <input type="hidden" name="studentId" value="<?= htmlspecialchars($pay_form_student['id']) ?>">
      <input type="hidden" name="return_to" value="<?= htmlspecialchars($pay_form_return_to) ?>">

      <div class="ght-payment-payer rounded-xl bg-[#fafafa] p-4">
        <div class="flex items-center justify-between gap-3">
          <div class="min-w-0">
            <p class="m-0 text-sm font-medium"><?= htmlspecialchars($pay_form_student['name']) ?></p>
            <p class="m-0 mt-1 text-xs text-[#737373]"><?= htmlspecialchars($pay_form_student['className'] . ' · ' . $pay_form_student['studentNumber'] . ' · ' . $pay_form_student['guardian']) ?></p>
          </div>
          <div class="text-right">
            <p class="m-0 text-xs text-[#737373]">Outstanding</p>
            <p class="m-0 mt-1 text-sm font-medium"><?= ght_format_naira($pay_form_student['summary']['outstanding']) ?></p>
          </div>
        </div>
      </div>

      <?php if ($pay_form_has_fees): ?>
        <div class="mt-5">
          <div class="flex items-center justify-between">
            <p class="m-0 text-xs font-semibold uppercase tracking-[.06em] text-[#737373]">Allocate payment to fees</p>
            <button type="button" class="ght-link-button" data-pay-full>Pay full balance</button>
          </div>
          <div class="ght-payfee-list mt-3">
            <?php foreach ($pay_form_fees as $pay_fee): ?>
              <div class="ght-payfee-row">
                <div class="min-w-0">
                  <p class="m-0 text-sm font-medium"><?= htmlspecialchars($pay_fee['name']) ?></p>
                  <p class="m-0 mt-1 text-xs text-[#737373]"><?= htmlspecialchars($pay_fee['note']) ?> · <?= ght_format_naira($pay_fee['payable']) ?> due</p>
                </div>
                <div class="ght-payfee-input-wrap">
                  <span class="ght-payfee-currency" aria-hidden="true">&#8358;</span>
                  <!-- alloc_name[] and alloc_amount[] stay parallel: the handler
                       zips them back together into per-fee allocations. -->
                  <input type="hidden" name="alloc_name[]" value="<?= htmlspecialchars($pay_fee['name']) ?>">
                  <input
                    type="number" min="0" step="500" inputmode="numeric"
                    class="ght-input ght-payfee-input"
                    name="alloc_amount[]"
                    data-pay-cap="<?= htmlspecialchars((string) $pay_fee['payable']) ?>"
                    placeholder="0"
                    aria-label="Amount to pay for <?= htmlspecialchars($pay_fee['name']) ?>">
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
          <label class="ght-field">
            <span class="ght-field-label">Payment method</span>
            <select class="ght-input" name="method">
              <?php foreach ($pay_methods as $pay_method): ?>
                <option value="<?= htmlspecialchars($pay_method) ?>"><?= htmlspecialchars($pay_method) ?></option>
              <?php endforeach; ?>
            </select>
          </label>
          <label class="ght-field">
            <span class="ght-field-label">Payment date</span>
            <input type="text" class="ght-input" value="Today" disabled aria-label="Payment date is today">
          </label>
        </div>

        <label class="ght-field mt-4">
          <span class="ght-field-label">Purpose / note <span class="text-[#a3a3a3]">(optional)</span></span>
          <input type="text" class="ght-input" name="note" placeholder="e.g. First Term fees">
        </label>

        <label class="ght-field mt-4">
          <span class="ght-field-label">Supporting attachment <span class="text-[#a3a3a3]">(optional)</span></span>
          <input type="file" class="ght-input" name="attachment" accept="image/*,application/pdf">
          <span class="ght-field-hint">Bank slip, POS receipt or transfer screenshot.</span>
        </label>

        <div class="mt-5 flex items-center justify-between border-t border-[#f5f5f5] pt-4">
          <div>
            <p class="m-0 text-xs text-[#737373]">Total payment</p>
            <p class="m-0 mt-1 text-xl font-medium tracking-[-.5px]" data-pay-total><?= ght_format_naira(0) ?></p>
          </div>
          <div class="flex gap-2">
            <label for="<?= htmlspecialchars($pay_form_modal_id) ?>" class="ght-button ght-button--secondary text-sm font-medium">Cancel</label>
            <button type="submit" class="ght-button ght-button--primary text-sm font-medium" data-pay-submit>Record payment</button>
          </div>
        </div>
      <?php else: ?>
        <p class="mt-5 rounded-xl bg-[#f0fdf4] p-4 text-sm text-[#16803b]">This student has no outstanding fees for this term.</p>
        <div class="mt-5 flex justify-end">
          <label for="<?= htmlspecialchars($pay_form_modal_id) ?>" class="ght-button ght-button--secondary text-sm font-medium">Close</label>
        </div>
      <?php endif; ?>
    </form>
  </div>
</div>
