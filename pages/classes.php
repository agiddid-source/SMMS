<link rel="stylesheet" href="styles/cn.css">
<?php
/**
 * pages/classes.php
 *
 * Static shell only. PHP prints the page chrome and the fixture seed; the
 * list, the filters and every modal are rendered and driven by
 * assets/js/cn-classes.js. There is no form post anywhere on this page —
 * nothing here can reach the server, by design.
 */
?>
<?php render_toast(); ?>
<div class="ght-dashboard-content">
  <div class="ght-page-enter">

    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
      <div>
        <p class="m-0 text-sm text-[#737373]">Class &amp; academic structure</p>
        <h2 class="ght-display mb-0 mt-2 text-3xl leading-none tracking-normal sm:text-4xl">Classes.</h2>
        <p class="m-0 mt-2 text-sm text-[#737373]" id="cn-class-summary"></p>
      </div>
      <button type="button" id="cn-add-class" class="ght-button ght-button--primary text-sm font-medium">
        <span class="ght-button-icon"><svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg></span>
        <span class="ght-button-label">Add class</span>
      </button>
    </div>

    <section class="mt-6">
      <div class="cn-toolbar">
        <label class="ght-visually-hidden" for="cn-class-search">Search classes</label>
        <input type="search" id="cn-class-search" class="cn-input" placeholder="Search classes&hellip;" autocomplete="off">
        <nav class="ght-chart-views" id="cn-section-nav" aria-label="Filter classes by section"></nav>
        <label class="cn-switch">
          <input type="checkbox" id="cn-show-archived">
          <span>Show archived</span>
        </label>
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
          <label for="cn-class-name">Class name</label>
          <input type="text" id="cn-class-name" name="name" class="cn-input" placeholder="e.g. Year 6" data-cn-autofocus required>
        </div>
        <div class="cn-field">
          <label for="cn-class-section">Section</label>
          <input type="text" id="cn-class-section" name="section" class="cn-input" list="cn-section-datalist" placeholder="e.g. Primary" required>
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
<script src="assets/js/cn-classes.js"></script>
