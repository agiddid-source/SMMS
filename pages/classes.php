<link rel="stylesheet" href="styles/cn.css">
<link rel="stylesheet" href="styles/cn-ui.css">

<?php render_toast(); ?>
<div class="ght-dashboard-content">
  <div class="ght-page-enter">

    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
      <div>
        <p class="m-0 text-sm text-[#737373]">Class &amp; academic structure</p>
        <h2 class="ght-display mb-0 mt-2 text-3xl leading-none tracking-normal sm:text-4xl">Classes.</h2>
        <p class="m-0 mt-2 text-sm text-[#737373]" id="cn-class-summary"></p>
      </div>
      <div class="cn-page-actions">
        <button type="button" id="cn-order-toggle" class="ght-button ght-button--secondary text-sm font-medium" aria-pressed="false" title="Rearrange sections and classes">
          <span class="ght-button-icon"><svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 4v16M8 4L4.5 7.5M8 4l3.5 3.5M16 20V4M16 20l-3.5-3.5M16 20l3.5-3.5"/></svg></span>
          <span class="ght-button-label">Order</span>
        </button>
        <button type="button" id="cn-add-class" class="ght-button ght-button--primary text-sm font-medium">
          <span class="ght-button-icon"><svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg></span>
          <span class="ght-button-label">Add class</span>
        </button>
      </div>
    </div>

    <section class="mt-6">
      <div class="cn-order-bar" id="cn-order-bar" hidden>
        <p class="cn-order-hint"><strong>Arranging classes.</strong> Drag a row by its handle, or use the arrows. Drop a class on another section to move it there.</p>
        <div class="cn-order-actions">
          <button type="button" id="cn-order-reset" class="ght-button ght-button--secondary text-sm font-medium"><span class="ght-button-label">Reset order</span></button>
          <button type="button" id="cn-order-done" class="ght-button ght-button--primary text-sm font-medium"><span class="ght-button-label">Done</span></button>
        </div>
        <p class="ght-visually-hidden" id="cn-order-live" role="status" aria-live="polite"></p>
      </div>

      <div class="cn-toolbar" id="cn-class-toolbar">
        <label class="ght-visually-hidden" for="cn-class-search">Search classes</label>
        <input type="search" id="cn-class-search" class="cn-input" placeholder="Search by class name or section&hellip;" autocomplete="off">
        <nav class="ght-chart-views" id="cn-section-nav" aria-label="Filter classes by section"></nav>
      </div>

      <div id="cn-class-list"></div>
      <p class="cn-prototype-note">Prototype &mdash; changes live in this browser tab only and reset when you reload.</p>
    </section>

  </div>
</div>

<datalist id="cn-section-datalist"></datalist>

<!-- Add / edit class -->
<div class="cn-modal" id="cn-class-form-modal" role="dialog" aria-modal="true" aria-labelledby="cn-class-form-title" hidden>
  <div class="cn-modal-backdrop" data-cn-dismiss></div>
  <div class="cn-modal-panel">
    <button type="button" class="cn-modal-close" data-cn-dismiss aria-label="Close">&times;</button>
    <h2 class="cn-modal-title" id="cn-class-form-title">Add class</h2>
    <form id="cn-class-form" novalidate>
      <div class="cn-field-grid">
        <div class="cn-field">
          <label for="cn-class-section">Section</label>
          <input type="text" id="cn-class-section" name="section" class="cn-input" list="cn-section-datalist" placeholder="e.g. Primary" required>
        </div>
        <div class="cn-field">
          <label for="cn-class-name">Class name</label>
          <input type="text" id="cn-class-name" name="name" class="cn-input" placeholder="e.g. Year 6" data-cn-autofocus required>
        </div>
      </div>
      <div class="cn-modal-actions">
        <button type="button" class="ght-button ght-button--secondary text-sm font-medium" data-cn-dismiss><span class="ght-button-label">Cancel</span></button>
        <button type="submit" id="cn-class-form-submit" class="ght-button ght-button--primary text-sm font-medium"><span class="ght-button-label">Add class</span></button>
      </div>
    </form>
  </div>
</div>

<?php require __DIR__ . '/../components/cn-confirm-modal.php'; ?>

<?php cn_render_seed(); ?>
<script src="assets/js/cn-store.js"></script>
<script src="assets/js/cn-modal.js"></script>
<script src="assets/js/cn-search.js"></script>
<script src="assets/js/cn-classes.js"></script>
