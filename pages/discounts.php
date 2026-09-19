<link rel="stylesheet" href="styles/cn.css">
<?php

$search = cure($_GET['search'] ?? '');
$type_filter = cure($_GET['type'] ?? '');
$scope_filter = cure($_GET['scope'] ?? '');
$discount_error = cure($_GET['discount_error'] ?? '');

$all_discounts = cn_get_discounts();
$filtered_discounts = cn_filter_discounts($all_discounts, $search, $type_filter, $scope_filter);
$active_count = count(array_filter($all_discounts, fn($discount) => $discount['status'] === 'active'));
$tuition_count = count(array_filter($all_discounts, fn($discount) => $discount['appliesTo'] === 'tuition' && $discount['status'] === 'active'));
$total_fees_count = count(array_filter($all_discounts, fn($discount) => $discount['appliesTo'] === 'total_fees' && $discount['status'] === 'active'));
?>
<?php render_toast(); ?>
<div class="ght-dashboard-content">
  <div class="ght-page-enter">

    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
      <div>
        <p class="m-0 text-sm text-[#737373]">Student Account Rules</p>
        <h2 class="ght-display mb-0 mt-2 text-3xl leading-none tracking-normal sm:text-4xl">Discounts.</h2>
        <p class="mb-0 mt-3 max-w-2xl text-sm leading-6 text-[#737373]">Define approved concessions separately from the pupils who receive them. Tuition rules affect tuition only; total-fee rules affect every fee in the approved account.</p>
      </div>
      <label for="cn-add-discount" class="ght-button ght-button--primary text-sm font-medium">
        <span class="ght-button-icon"><svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg></span>
        <span class="ght-button-label">Add discount</span>
      </label>
    </div>

    <?php if ($discount_error !== ''): ?>
      <div class="cn-alert" role="alert"><?= htmlspecialchars($discount_error) ?></div>
    <?php endif; ?>

    <section class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-3">
      <div class="ght-card t-resize cn-discount-summary"><p class="m-0 text-sm text-[#737373]">Active rules</p><p class="m-0 mt-2 text-2xl font-medium"><?= $active_count ?></p></div>
      <div class="ght-card t-resize cn-discount-summary"><p class="m-0 text-sm text-[#737373]">Tuition rules</p><p class="m-0 mt-2 text-2xl font-medium"><?= $tuition_count ?></p></div>
      <div class="ght-card t-resize cn-discount-summary"><p class="m-0 text-sm text-[#737373]">Total-fee rules</p><p class="m-0 mt-2 text-2xl font-medium"><?= $total_fees_count ?></p></div>
    </section>

    <section class="mt-6">
      <form method="get" class="cn-toolbar">
        <input type="hidden" name="p" value="discounts">
        <input type="text" name="search" class="cn-input" placeholder="Search discounts…" value="<?= htmlspecialchars($search) ?>">
        <select name="type" class="cn-input" style="max-width:180px;">
          <option value="">All types</option>
          <?php foreach (['Percentage', 'Full waiver'] as $type): ?><option value="<?= htmlspecialchars($type) ?>" <?= $type_filter === $type ? 'selected' : '' ?>><?= htmlspecialchars($type) ?></option><?php endforeach; ?>
        </select>
        <select name="scope" class="cn-input" style="max-width:210px;">
          <option value="">All fee scopes</option>
          <option value="tuition" <?= $scope_filter === 'tuition' ? 'selected' : '' ?>>Tuition</option>
          <option value="total_fees" <?= $scope_filter === 'total_fees' ? 'selected' : '' ?>>Total school fees</option>
        </select>
        <button type="submit" class="ght-button ght-button--secondary text-sm font-medium"><span class="ght-button-label">Search</span></button>
        <?php if ($search !== '' || $type_filter !== '' || $scope_filter !== ''): ?><a href="index.php?p=discounts" class="cn-btn-text">Clear</a><?php endif; ?>
      </form>

      <?php if (count($filtered_discounts) === 0): ?>
        <div class="ght-card t-resize cn-empty">No discounts match your search or filters.</div>
      <?php else: ?>
        <div class="cn-discount-list-wrap">
          <div class="cn-discount-list-header"><span>Discount</span><span>Type / rate</span><span>Applies to</span><span>Eligibility</span><span>Status</span><span></span></div>
          <?php foreach ($filtered_discounts as $discount):
            $discount_id = htmlspecialchars($discount['id']);
            $is_active = $discount['status'] === 'active';
            $scope_label = $discount['appliesTo'] === 'tuition' ? 'Tuition' : 'Total school fees';
          ?>
            <div class="cn-discount-list-row">
              <span><strong><?= htmlspecialchars($discount['name']) ?></strong><br><small><?= htmlspecialchars($discount['id']) ?></small></span>
              <span><?= htmlspecialchars($discount['type']) ?><br><strong><?= htmlspecialchars((string) $discount['rate']) ?>%</strong></span>
              <span><span class="ght-chip ght-chip--<?= $discount['appliesTo'] === 'tuition' ? 'accent' : 'success' ?>"><?= htmlspecialchars($scope_label) ?></span></span>
              <span><?= htmlspecialchars($discount['eligibility']) ?></span>
              <span><span class="ght-chip ght-chip--<?= $is_active ? 'success' : 'neutral' ?>"><?= $is_active ? 'Active' : 'Inactive' ?></span></span>
              <span class="cn-list-row-actions"><label for="cn-edit-discount-<?= $discount_id ?>" class="cn-btn-text">Edit</label><label for="cn-toggle-discount-<?= $discount_id ?>" class="<?= $is_active ? 'cn-btn-danger-text' : 'cn-btn-text' ?>"><?= $is_active ? 'Deactivate' : 'Activate' ?></label></span>
            </div>

            <input type="checkbox" id="cn-edit-discount-<?= $discount_id ?>" class="cn-modal-toggle">
            <div class="cn-modal-overlay">
              <label for="cn-edit-discount-<?= $discount_id ?>" class="cn-modal-backdrop" aria-hidden="true"></label>
              <div class="cn-modal-panel">
                <label for="cn-edit-discount-<?= $discount_id ?>" class="cn-modal-close" aria-label="Close">&times;</label>
                <h2 class="cn-modal-title">Edit discount</h2>
                <?php $discount_form_id = $discount['id']; include __DIR__ . '/../components/cn-discount-form-fields.php'; ?>
                <div class="cn-modal-actions"><label for="cn-edit-discount-<?= $discount_id ?>" class="ght-button ght-button--secondary text-sm font-medium"><span class="ght-button-label">Cancel</span></label><button form="cn-edit-discount-form-<?= $discount_id ?>" type="submit" class="ght-button ght-button--primary text-sm font-medium"><span class="ght-button-label">Save discount</span></button></div>
              </div>
            </div>

            <input type="checkbox" id="cn-toggle-discount-<?= $discount_id ?>" class="cn-modal-toggle">
            <div class="cn-modal-overlay">
              <label for="cn-toggle-discount-<?= $discount_id ?>" class="cn-modal-backdrop" aria-hidden="true"></label>
              <div class="cn-modal-panel">
                <label for="cn-toggle-discount-<?= $discount_id ?>" class="cn-modal-close" aria-label="Close">&times;</label>
                <h2 class="cn-modal-title"><?= $is_active ? 'Deactivate' : 'Activate' ?> discount</h2>
                <p class="m-0 text-sm text-[#737373]"><?= $is_active ? 'This rule will no longer be available for new discount assignments.' : 'This rule will become available for new discount assignments.' ?></p>
                <form method="post" action="index.php?p=discounts"><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= $discount_id ?>"><div class="cn-modal-actions"><label for="cn-toggle-discount-<?= $discount_id ?>" class="ght-button ght-button--secondary text-sm font-medium"><span class="ght-button-label">Cancel</span></label><button type="submit" class="ght-button text-sm font-medium" style="background: <?= $is_active ? 'var(--ght-color-error)' : 'var(--ght-color-accent)' ?>; color:#fff;"><span class="ght-button-label"><?= $is_active ? 'Deactivate' : 'Activate' ?></span></button></div></form>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>
  </div>
</div>

<input type="checkbox" id="cn-add-discount" class="cn-modal-toggle">
<div class="cn-modal-overlay">
  <label for="cn-add-discount" class="cn-modal-backdrop" aria-hidden="true"></label>
  <div class="cn-modal-panel">
    <label for="cn-add-discount" class="cn-modal-close" aria-label="Close">&times;</label>
    <h2 class="cn-modal-title">Add discount</h2>
    <?php $discount = null; $discount_form_id = 'add'; include __DIR__ . '/../components/cn-discount-form-fields.php'; ?>
    <div class="cn-modal-actions"><label for="cn-add-discount" class="ght-button ght-button--secondary text-sm font-medium"><span class="ght-button-label">Cancel</span></label><button form="cn-add-discount-form" type="submit" class="ght-button ght-button--primary text-sm font-medium"><span class="ght-button-label">Save discount</span></button></div>
  </div>
</div>