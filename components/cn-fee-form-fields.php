<?php
/**
 * components/cn-fee-form-fields.php
 *
 * Included (not required-once) by both the Add and Edit fee modals in
 * pages/fee-setup.php, so the two forms can never drift out of sync.
 * Expects $fee (null for Add, the fee record for Edit), $fee_types,
 * $grouped_classes and $assigned_ids to already be set in the including
 * scope — PHP's include() shares the caller's variable scope, so no
 * arguments need passing explicitly.
 */
?>
<div class="cn-field-grid">
  <div class="cn-field">
    <label>Fee name</label>
    <input type="text" name="name" class="cn-input" placeholder="e.g. Tuition" value="<?= htmlspecialchars($fee['name'] ?? '') ?>" required>
  </div>
  <div class="cn-field">
    <label>Type</label>
    <select name="type" class="cn-input" required>
      <?php foreach ($fee_types as $type): ?>
        <option value="<?= htmlspecialchars($type['name']) ?>" <?= ($fee['type'] ?? '') === $type['name'] ? 'selected' : '' ?>><?= htmlspecialchars($type['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="cn-field">
    <label>Amount (&#8358;)</label>
    <input type="number" name="amount" class="cn-input" min="0" step="100" value="<?= htmlspecialchars((string) ($fee['amount'] ?? '')) ?>" required>
  </div>
  <div class="cn-field">
    <label>Academic session</label>
    <input type="text" name="academicSession" class="cn-input" placeholder="2026/2027" value="<?= htmlspecialchars($fee['academicSession'] ?? '2026/2027') ?>" required>
  </div>
  <div class="cn-field">
    <label>Term</label>
    <select name="term" class="cn-input">
      <?php foreach (['First Term', 'Second Term', 'Third Term'] as $term_option): ?>
        <option value="<?= $term_option ?>" <?= ($fee['term'] ?? 'First Term') === $term_option ? 'selected' : '' ?>><?= $term_option ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="cn-field">
    <label>Due date</label>
    <input type="date" name="dueDate" class="cn-input" value="<?= htmlspecialchars($fee['dueDate'] ?? '') ?>">
  </div>
</div>

<div class="cn-field">
  <label>Description</label>
  <input type="text" name="description" class="cn-input" placeholder="Optional" value="<?= htmlspecialchars($fee['description'] ?? '') ?>">
</div>

<div style="margin-top:16px; border-top:1px solid var(--ght-color-border); padding-top:14px;">
  <p class="m-0 text-sm font-medium">Applicable classes</p>
  <p class="m-0 mt-1 text-xs text-[#737373]">Select one or more classes this fee applies to.</p>
  <div class="cn-picker-list" style="margin-top:10px;">
    <?php foreach ($grouped_classes as $section_name => $classes_in_section): ?>
      <div style="margin-bottom:8px;">
        <p class="m-0 text-xs font-medium" style="color:var(--ght-color-muted);"><?= htmlspecialchars($section_name) ?></p>
        <?php foreach ($classes_in_section as $class): ?>
          <label class="cn-picker-row">
            <input type="checkbox" name="classIds[]" value="<?= htmlspecialchars($class['id']) ?>" <?= in_array($class['id'], $assigned_ids, true) ? 'checked' : '' ?>>
            <?= htmlspecialchars($class['name']) ?>
          </label>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>
  </div>
</div>
