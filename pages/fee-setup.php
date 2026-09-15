<link rel="stylesheet" href="styles/cn.css">
<?php

$search = cure($_GET['search'] ?? '');
$type_filter = cure($_GET['type'] ?? '');
$term_filter = cure($_GET['term'] ?? '');

$all_fees = cn_get_fees();
$all_classes = cn_filter_classes(cn_get_classes(), '', '', false); // active only
$fee_types = cn_get_fee_types();

$filtered_fees = cn_filter_fees($all_fees, $search, $type_filter, $term_filter);
$terms = cn_distinct_terms($all_fees);
$grouped_classes = cn_group_by_section($all_classes);
?>
<?php render_toast(); ?>
<div class="ght-dashboard-content">
  <div class="ght-page-enter">

    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
      <div>
        <p class="m-0 text-sm text-[#737373]">Fee Management</p>
        <h2 class="ght-display mb-0 mt-2 text-3xl leading-none tracking-normal sm:text-4xl">Fee settings.</h2>
      </div>
      <div class="flex gap-2">
        <label for="cn-manage-types" class="ght-button ght-button--secondary text-sm font-medium"><span class="ght-button-label">Fee types</span></label>
        <label for="cn-add-fee" class="ght-button ght-button--primary text-sm font-medium">
          <span class="ght-button-icon"><svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg></span>
          <span class="ght-button-label">Add fee</span>
        </label>
      </div>
    </div>

    <section class="mt-6">
      <form method="get" class="cn-toolbar">
        <input type="hidden" name="p" value="fee-setup">
        <input type="text" name="search" class="cn-input" placeholder="Search fees…" value="<?= htmlspecialchars($search) ?>">
        <select name="type" class="cn-input" style="max-width:180px;">
          <option value="">All types</option>
          <?php foreach ($fee_types as $type): ?>
            <option value="<?= htmlspecialchars($type['name']) ?>" <?= $type['name'] === $type_filter ? 'selected' : '' ?>><?= htmlspecialchars($type['name']) ?></option>
          <?php endforeach; ?>
        </select>
        <select name="term" class="cn-input" style="max-width:180px;">
          <option value="">All terms</option>
          <?php foreach ($terms as $term_option): ?>
            <option value="<?= htmlspecialchars($term_option) ?>" <?= $term_option === $term_filter ? 'selected' : '' ?>><?= htmlspecialchars($term_option) ?></option>
          <?php endforeach; ?>
        </select>
        <button type="submit" class="ght-button ght-button--secondary text-sm font-medium"><span class="ght-button-label">Search</span></button>
        <?php if ($search !== '' || $type_filter !== '' || $term_filter !== ''): ?>
          <a href="index.php?p=fee-setup" class="cn-btn-text">Clear</a>
        <?php endif; ?>
      </form>

      <?php if (count($filtered_fees) === 0): ?>
        <div class="ght-card t-resize cn-empty">No fees match your search or filters.</div>
      <?php else: ?>
        <div class="cn-fee-list-wrap">
          <div class="cn-fee-list-header"><span>Fee</span><span>Amount</span><span>Session / Term</span><span>Due</span><span>Applicable classes</span><span></span></div>
          <?php foreach ($filtered_fees as $fee):
            $class_names = cn_resolve_class_names($fee['assignedClasses'] ?? [], $all_classes);
            $assigned_ids = $fee['assignedClasses'] ?? [];
          ?>
            <div class="cn-fee-list-row">
              <span><?= htmlspecialchars($fee['name']) ?><br><span style="font-size:12px; color:var(--ght-color-muted);"><?= htmlspecialchars($fee['type']) ?></span></span>
              <span><?= ght_format_naira($fee['amount']) ?></span>
              <span><?= htmlspecialchars($fee['academicSession']) ?> &middot; <?= htmlspecialchars($fee['term']) ?></span>
              <span><?= htmlspecialchars($fee['dueDate'] ?: '—') ?></span>
              <span><?= htmlspecialchars(cn_format_class_names($class_names)) ?></span>
              <span class="cn-list-row-actions">
                <label for="cn-edit-fee-<?= htmlspecialchars($fee['id']) ?>" class="cn-btn-text">Edit</label>
                <label for="cn-deactivate-fee-<?= htmlspecialchars($fee['id']) ?>" class="cn-btn-danger-text">Deactivate</label>
              </span>
            </div>

            <!-- Edit fee modal -->
            <input type="checkbox" id="cn-edit-fee-<?= htmlspecialchars($fee['id']) ?>" class="cn-modal-toggle">
            <div class="cn-modal-overlay">
              <label for="cn-edit-fee-<?= htmlspecialchars($fee['id']) ?>" class="cn-modal-backdrop" aria-hidden="true"></label>
              <div class="cn-modal-panel">
                <label for="cn-edit-fee-<?= htmlspecialchars($fee['id']) ?>" class="cn-modal-close" aria-label="Close">&times;</label>
                <h2 class="cn-modal-title">Edit fee</h2>
                <form method="post" action="index.php?p=fee-setup">
                  <input type="hidden" name="action" value="edit">
                  <input type="hidden" name="id" value="<?= htmlspecialchars($fee['id']) ?>">
                  <?php include __DIR__ . '/../components/cn-fee-form-fields.php'; ?>
                  <div class="cn-modal-actions">
                    <label for="cn-edit-fee-<?= htmlspecialchars($fee['id']) ?>" class="ght-button ght-button--secondary text-sm font-medium"><span class="ght-button-label">Cancel</span></label>
                    <button type="submit" class="ght-button ght-button--primary text-sm font-medium"><span class="ght-button-label">Save fee</span></button>
                  </div>
                </form>
              </div>
            </div>

            <!-- Deactivate confirm modal -->
            <input type="checkbox" id="cn-deactivate-fee-<?= htmlspecialchars($fee['id']) ?>" class="cn-modal-toggle">
            <div class="cn-modal-overlay">
              <label for="cn-deactivate-fee-<?= htmlspecialchars($fee['id']) ?>" class="cn-modal-backdrop" aria-hidden="true"></label>
              <div class="cn-modal-panel">
                <label for="cn-deactivate-fee-<?= htmlspecialchars($fee['id']) ?>" class="cn-modal-close" aria-label="Close">&times;</label>
                <h2 class="cn-modal-title">Deactivate fee</h2>
                <p class="m-0 text-sm text-[#737373]">Deactivate &quot;<?= htmlspecialchars($fee['name']) ?>&quot;? It will no longer be assignable to students.</p>
                <form method="post" action="index.php?p=fee-setup">
                  <input type="hidden" name="action" value="deactivate">
                  <input type="hidden" name="id" value="<?= htmlspecialchars($fee['id']) ?>">
                  <div class="cn-modal-actions">
                    <label for="cn-deactivate-fee-<?= htmlspecialchars($fee['id']) ?>" class="ght-button ght-button--secondary text-sm font-medium"><span class="ght-button-label">Cancel</span></label>
                    <button type="submit" class="ght-button text-sm font-medium" style="background: var(--ght-color-error); color:#fff;"><span class="ght-button-label">Deactivate</span></button>
                  </div>
                </form>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>

  </div>
</div>

<!-- Add fee modal -->
<?php $fee = null; $assigned_ids = []; ?>
<input type="checkbox" id="cn-add-fee" class="cn-modal-toggle">
<div class="cn-modal-overlay">
  <label for="cn-add-fee" class="cn-modal-backdrop" aria-hidden="true"></label>
  <div class="cn-modal-panel">
    <label for="cn-add-fee" class="cn-modal-close" aria-label="Close">&times;</label>
    <h2 class="cn-modal-title">Add fee</h2>
    <form method="post" action="index.php?p=fee-setup">
      <input type="hidden" name="action" value="add">
      <?php include __DIR__ . '/../components/cn-fee-form-fields.php'; ?>
      <div class="cn-modal-actions">
        <label for="cn-add-fee" class="ght-button ght-button--secondary text-sm font-medium"><span class="ght-button-label">Cancel</span></label>
        <button type="submit" class="ght-button ght-button--primary text-sm font-medium"><span class="ght-button-label">Save fee</span></button>
      </div>
    </form>
  </div>
</div>

<!-- Fee types management modal -->
<input type="checkbox" id="cn-manage-types" class="cn-modal-toggle">
<div class="cn-modal-overlay">
  <label for="cn-manage-types" class="cn-modal-backdrop" aria-hidden="true"></label>
  <div class="cn-modal-panel">
    <label for="cn-manage-types" class="cn-modal-close" aria-label="Close">&times;</label>
    <h2 class="cn-modal-title">Fee types</h2>
    <ul style="list-style:none; padding:0; margin:0 0 16px;">
      <?php foreach ($fee_types as $type): ?>
        <li style="padding:6px 0; border-bottom:1px solid var(--ght-color-border); font-size:14px;"><?= htmlspecialchars($type['name']) ?></li>
      <?php endforeach; ?>
    </ul>
    <form method="post" action="index.php?p=fee-setup" style="display:flex; gap:8px;">
      <input type="hidden" name="action" value="add_type">
      <input type="text" name="name" class="cn-input" placeholder="New fee type name" required>
      <button type="submit" class="ght-button ght-button--primary text-sm font-medium"><span class="ght-button-label">Add</span></button>
    </form>
    <div class="cn-modal-actions">
      <label for="cn-manage-types" class="ght-button ght-button--secondary text-sm font-medium"><span class="ght-button-label">Close</span></label>
    </div>
  </div>
</div>
