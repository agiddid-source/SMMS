<link rel="stylesheet" href="styles/cn.css">

<?php render_toast(); ?>
<div class="ght-dashboard-content">
  <div class="ght-page-enter">

    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
      <div>
        <p class="m-0 text-sm text-[#737373]">Money leaving the school</p>
        <h2 class="ght-display mb-0 mt-2 text-3xl leading-none tracking-normal sm:text-4xl">Expenses.</h2>
        <p class="m-0 mt-2 text-sm text-[#737373]" id="cn-expense-summary"></p>
      </div>
      <div class="flex gap-2">
        <button type="button" id="cn-manage-categories" class="ght-button ght-button--secondary text-sm font-medium"><span class="ght-button-label">Categories</span></button>
        <button type="button" id="cn-add-expense" class="ght-button ght-button--primary text-sm font-medium">
          <span class="ght-button-icon"><svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg></span>
          <span class="ght-button-label">Record expense</span>
        </button>
      </div>
    </div>

    <section class="mt-6">
      <div class="cn-toolbar">
        <label class="ght-visually-hidden" for="cn-expense-search">Search expenses</label>
        <input type="search" id="cn-expense-search" class="cn-input" placeholder="Search description or payee&hellip;" autocomplete="off">
        <label class="ght-visually-hidden" for="cn-expense-category-filter">Filter by category</label>
        <select id="cn-expense-category-filter" class="cn-input cn-input--compact"></select>
        <label class="ght-visually-hidden" for="cn-expense-from">From date</label>
        <input type="date" id="cn-expense-from" class="cn-input cn-input--compact" aria-label="From date">
        <label class="ght-visually-hidden" for="cn-expense-to">To date</label>
        <input type="date" id="cn-expense-to" class="cn-input cn-input--compact" aria-label="To date">
        <label class="cn-switch">
          <input type="checkbox" id="cn-show-voided">
          <span>Show voided</span>
        </label>
      </div>

      <div id="cn-expense-list"></div>
      <p class="cn-prototype-note">Prototype &mdash; changes live in this browser tab only and reset on reload. Receipt/invoice pickers only remember the file's name; nothing is uploaded or stored.</p>
    </section>

  </div>
</div>

<!-- Add / edit expense -->
<div class="cn-modal" id="cn-expense-form-modal" role="dialog" aria-modal="true" aria-labelledby="cn-expense-form-title" hidden>
  <div class="cn-modal-backdrop" data-cn-dismiss></div>
  <div class="cn-modal-panel">
    <button type="button" class="cn-modal-close" data-cn-dismiss aria-label="Close">&times;</button>
    <h2 class="cn-modal-title" id="cn-expense-form-title">Record expense</h2>
    <form id="cn-expense-form" novalidate>
      <div class="cn-field">
        <label for="cn-expense-description">Description</label>
        <input type="text" id="cn-expense-description" name="description" class="cn-input" placeholder="e.g. Generator servicing" data-cn-autofocus required>
      </div>

      <div class="cn-field-grid">
        <div class="cn-field">
          <label for="cn-expense-category">Category</label>
          <select id="cn-expense-category" name="categoryId" class="cn-input" required></select>
        </div>
        <div class="cn-field">
          <label for="cn-expense-amount">Amount (&#8358;)</label>
          <input type="number" id="cn-expense-amount" name="amount" class="cn-input" min="0" step="100" placeholder="0" required>
        </div>
        <div class="cn-field">
          <label for="cn-expense-date">Expense date</label>
          <input type="date" id="cn-expense-date" name="expenseDate" class="cn-input" required>
        </div>
        <div class="cn-field">
          <label for="cn-expense-payee">Payee / vendor</label>
          <input type="text" id="cn-expense-payee" name="payee" class="cn-input" placeholder="e.g. ABC Generator Services" required>
        </div>
        <div class="cn-field">
          <label for="cn-expense-method">Payment method</label>
          <select id="cn-expense-method" name="paymentMethod" class="cn-input">
            <option value="Cash">Cash</option>
            <option value="Bank Transfer">Bank Transfer</option>
            <option value="POS">POS</option>
            <option value="Card">Card</option>
            <option value="Other">Other</option>
          </select>
        </div>
        <div class="cn-field">
          <label for="cn-expense-account">Account</label>
          <input type="text" id="cn-expense-account" class="cn-input" value="Main School Account" disabled>
          <p class="m-0 mt-1 text-xs text-[#737373]">Every expense links to the one Main School Account for this MVP.</p>
        </div>
      </div>

      <!-- "+ New category…" -->
      <div class="cn-field" id="cn-inline-category-row" hidden>
        <label for="cn-inline-category-name">New category name</label>
        <div class="cn-inline-form">
          <input type="text" id="cn-inline-category-name" class="cn-input" placeholder="e.g. Insurance">
          <button type="button" id="cn-inline-category-add" class="ght-button ght-button--secondary text-sm font-medium"><span class="ght-button-label">Add</span></button>
        </div>
      </div>

      <div class="cn-field-grid">
        <div class="cn-field">
          <label for="cn-expense-receipt">Receipt</label>
          <div class="cn-file-field">
            <input type="file" id="cn-expense-receipt">
            <span class="cn-file-field-name" id="cn-expense-receipt-name">No file chosen</span>
          </div>
        </div>
        <div class="cn-field">
          <label for="cn-expense-invoice">Invoice</label>
          <div class="cn-file-field">
            <input type="file" id="cn-expense-invoice">
            <span class="cn-file-field-name" id="cn-expense-invoice-name">No file chosen</span>
          </div>
        </div>
      </div>

      <div class="cn-field">
        <label for="cn-expense-notes">Notes</label>
        <input type="text" id="cn-expense-notes" name="notes" class="cn-input" placeholder="Optional">
      </div>

      <div class="cn-modal-actions">
        <button type="button" class="ght-button ght-button--secondary text-sm font-medium" data-cn-dismiss><span class="ght-button-label">Cancel</span></button>
        <button type="submit" id="cn-expense-form-submit" class="ght-button ght-button--primary text-sm font-medium"><span class="ght-button-label">Record expense</span></button>
      </div>
    </form>
  </div>
</div>

<!-- Categories -->
<div class="cn-modal" id="cn-category-modal" role="dialog" aria-modal="true" aria-labelledby="cn-category-title" hidden>
  <div class="cn-modal-backdrop" data-cn-dismiss></div>
  <div class="cn-modal-panel cn-modal-panel--narrow">
    <button type="button" class="cn-modal-close" data-cn-dismiss aria-label="Close">&times;</button>
    <h2 class="cn-modal-title" id="cn-category-title">Expense categories</h2>
    <p class="cn-modal-body">Categories group expenses for filtering and reporting. Adding one here makes it available in every expense form.</p>
    <ul class="cn-type-list" id="cn-category-list"></ul>
    <form id="cn-category-form" class="cn-inline-form" novalidate>
      <label class="ght-visually-hidden" for="cn-category-name">New category</label>
      <input type="text" id="cn-category-name" name="name" class="cn-input" placeholder="e.g. Insurance" required>
      <button type="submit" class="ght-button ght-button--primary text-sm font-medium"><span class="ght-button-label">Add category</span></button>
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
<script src="assets/js/cn-expenses.js"></script>
