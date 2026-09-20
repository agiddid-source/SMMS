<link rel="stylesheet" href="styles/cn.css">
<?php
$cn_default_session = '2026/2027';
?>
<?php render_toast(); ?>
<div class="ght-dashboard-content">
  <div class="ght-page-enter">

    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
      <div>
        <p class="m-0 text-sm text-[#737373]">Fee management</p>
        <h2 class="ght-display mb-0 mt-2 text-3xl leading-none tracking-normal sm:text-4xl">Fee settings.</h2>
        <p class="m-0 mt-2 text-sm text-[#737373]" id="cn-fee-summary"></p>
      </div>
      <div class="flex gap-2">
        <button type="button" id="cn-manage-types" class="ght-button ght-button--secondary text-sm font-medium"><span class="ght-button-label">Fee types</span></button>
        <button type="button" id="cn-add-fee" class="ght-button ght-button--primary text-sm font-medium">
          <span class="ght-button-icon"><svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg></span>
          <span class="ght-button-label">Add fee</span>
        </button>
      </div>
    </div>

    <section class="mt-6">
      <div class="cn-toolbar">
        <label class="ght-visually-hidden" for="cn-fee-search">Search fees</label>
        <input type="search" id="cn-fee-search" class="cn-input" placeholder="Search fees&hellip;" autocomplete="off">
        <label class="ght-visually-hidden" for="cn-fee-type-filter">Filter by type</label>
        <select id="cn-fee-type-filter" class="cn-input cn-input--compact"></select>
        <label class="ght-visually-hidden" for="cn-fee-term-filter">Filter by term</label>
        <select id="cn-fee-term-filter" class="cn-input cn-input--compact"></select>
        <label class="cn-switch">
          <input type="checkbox" id="cn-show-inactive">
          <span>Show inactive</span>
        </label>
      </div>

      <div id="cn-fee-list"></div>
      <p class="cn-prototype-note">Prototype &mdash; changes live in this browser tab only and reset when you reload.</p>
    </section>

  </div>
</div>

<!-- Add / edit fee -->
<div class="cn-modal" id="cn-fee-form-modal" role="dialog" aria-modal="true" aria-labelledby="cn-fee-form-title" hidden>
  <div class="cn-modal-backdrop" data-cn-dismiss></div>
  <div class="cn-modal-panel">
    <button type="button" class="cn-modal-close" data-cn-dismiss aria-label="Close">&times;</button>
    <h2 class="cn-modal-title" id="cn-fee-form-title">Add fee</h2>
    <form id="cn-fee-form" novalidate>
      <div class="cn-field-grid">
        <div class="cn-field">
          <label for="cn-fee-name">Fee name</label>
          <input type="text" id="cn-fee-name" name="name" class="cn-input" placeholder="e.g. Tuition" data-cn-autofocus required>
        </div>
        <div class="cn-field">
          <label for="cn-fee-type">Type</label>
          <select id="cn-fee-type" name="type" class="cn-input" required></select>
        </div>
        <div class="cn-field">
          <label for="cn-fee-amount">Amount (&#8358;)</label>
          <input type="number" id="cn-fee-amount" name="amount" class="cn-input" min="0" step="100" placeholder="0" required>
        </div>
        <div class="cn-field">
          <label for="cn-fee-session">Academic session</label>
          <input type="text" id="cn-fee-session" name="academicSession" class="cn-input" value="<?= htmlspecialchars($cn_default_session) ?>" data-cn-default="<?= htmlspecialchars($cn_default_session) ?>" required>
        </div>
        <div class="cn-field">
          <label for="cn-fee-term">Term</label>
          <select id="cn-fee-term" name="term" class="cn-input">
            <option value="First Term">First Term</option>
            <option value="Second Term">Second Term</option>
            <option value="Third Term">Third Term</option>
          </select>
        </div>
        <div class="cn-field">
          <label for="cn-fee-due">Due date</label>
          <input type="date" id="cn-fee-due" name="dueDate" class="cn-input">
        </div>
      </div>

      <div class="cn-field">
        <label for="cn-fee-description">Description</label>
        <input type="text" id="cn-fee-description" name="description" class="cn-input" placeholder="Optional">
      </div>

      <div class="cn-picker">
        <div class="cn-picker-head">
          <div>
            <p class="m-0 text-sm font-medium">Applicable classes</p>
            <p class="m-0 mt-1 text-xs text-[#737373]" id="cn-picker-count">No classes selected</p>
          </div>
          <label class="ght-visually-hidden" for="cn-picker-search">Search classes to assign</label>
          <input type="search" id="cn-picker-search" class="cn-input cn-input--compact" placeholder="Find a class&hellip;" autocomplete="off">
        </div>
        <div class="cn-picker-list" id="cn-class-picker"></div>
      </div>

      <div class="cn-modal-actions">
        <button type="button" class="ght-button ght-button--secondary text-sm font-medium" data-cn-dismiss><span class="ght-button-label">Cancel</span></button>
        <button type="submit" id="cn-fee-form-submit" class="ght-button ght-button--primary text-sm font-medium"><span class="ght-button-label">Add fee</span></button>
      </div>
    </form>
  </div>
</div>

<!-- Fee types -->
<div class="cn-modal" id="cn-fee-types-modal" role="dialog" aria-modal="true" aria-labelledby="cn-fee-types-title" hidden>
  <div class="cn-modal-backdrop" data-cn-dismiss></div>
  <div class="cn-modal-panel cn-modal-panel--narrow">
    <button type="button" class="cn-modal-close" data-cn-dismiss aria-label="Close">&times;</button>
    <h2 class="cn-modal-title" id="cn-fee-types-title">Fee types</h2>
    <p class="cn-modal-body">Types group fees for filtering and reporting. Adding one here makes it available in every fee form.</p>
    <ul class="cn-type-list" id="cn-fee-types-list"></ul>
    <form id="cn-fee-type-form" class="cn-inline-form" novalidate>
      <label class="ght-visually-hidden" for="cn-fee-type-name">New fee type</label>
      <input type="text" id="cn-fee-type-name" name="name" class="cn-input" placeholder="e.g. Excursion" required>
      <button type="submit" class="ght-button ght-button--primary text-sm font-medium"><span class="ght-button-label">Add type</span></button>
    </form>
    <div class="cn-modal-actions">
      <button type="button" class="ght-button ght-button--secondary text-sm font-medium" data-cn-dismiss><span class="ght-button-label">Done</span></button>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../components/cn-confirm-modal.php'; ?>

<?php cn_render_seed(); ?>
<script src="assets/js/cn-store.js"></script>
<script src="assets/js/cn-modal.js"></script>
<script src="assets/js/cn-fees.js"></script>
