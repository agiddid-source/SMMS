<?php
$is_edit = $discount !== null;
$form_id = 'cn-' . ($is_edit ? 'edit-discount-form-' . htmlspecialchars($discount_form_id) : 'add-discount-form');
$available_fees = $all_fees ?? cn_get_fees();
$active_fees = array_filter($available_fees, fn($f) => ($f['status'] ?? 'active') === 'active');
?>
<form id="<?= $form_id ?>" method="post" action="index.php?p=discounts">
  <input type="hidden" name="action" value="<?= $is_edit ? 'edit' : 'add' ?>">
  <?php if ($is_edit): ?><input type="hidden" name="id" value="<?= htmlspecialchars($discount['id']) ?>"><?php endif; ?>
  <div class="cn-field-grid">
    <div class="cn-field"><label>Discount name</label><input type="text" name="name" class="cn-input" value="<?= htmlspecialchars($discount['name'] ?? '') ?>" placeholder="e.g. Community children" required></div>
    <div class="cn-field"><label>Type</label><select name="type" class="cn-input" required><option value="Percentage" <?= ($discount['type'] ?? '') === 'Percentage' ? 'selected' : '' ?>>Percentage</option><option value="Full waiver" <?= ($discount['type'] ?? '') === 'Full waiver' ? 'selected' : '' ?>>Full waiver</option></select></div>
    <div class="cn-field"><label>Rate (%)</label><input type="number" name="rate" class="cn-input" min="0" max="100" step="1" value="<?= htmlspecialchars((string) ($discount['rate'] ?? '')) ?>" required></div>
    <div class="cn-field">
      <label>Applies to</label>
      <select name="appliesTo" class="cn-input" required>
        <optgroup label="General Scope">
          <option value="total_fees" <?= ($discount['appliesTo'] ?? '') === 'total_fees' ? 'selected' : '' ?>>Total school fees (All fees)</option>
        </optgroup>
        <optgroup label="Available Application Fees">
          <?php foreach ($active_fees as $f):
            $is_selected = ($discount['appliesTo'] ?? '') === $f['id']
              || (strtolower($discount['appliesTo'] ?? '') === 'tuition' && strtolower($f['name']) === 'tuition')
              || (($discount['appliesTo'] ?? '') === $f['name']);
            $fee_label = htmlspecialchars($f['name']) . ' (' . ght_format_naira($f['amount']) . ' · ' . htmlspecialchars($f['term']) . ')';
          ?>
            <option value="<?= htmlspecialchars($f['id']) ?>" <?= $is_selected ? 'selected' : '' ?>><?= $fee_label ?></option>
          <?php endforeach; ?>
        </optgroup>
      </select>
    </div>
  </div>
  <div class="cn-field"><label>Eligibility criteria</label><p class="m-0 mb-1 text-xs text-[#737373]">Explain who qualifies, what the school must approve, and whether any fees remain payable.</p><textarea name="eligibility" class="cn-input" rows="4" placeholder="e.g. Families with three enrolled children; apply to each approved child's tuition." required><?= htmlspecialchars($discount['eligibility'] ?? '') ?></textarea></div>
</form>