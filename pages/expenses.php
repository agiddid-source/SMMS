<link rel="stylesheet" href="styles/cn.css">
<link rel="stylesheet" href="styles/cn-ui.css">

<?php render_toast(); ?>
<div class="ght-dashboard-content">
  <div class="ght-page-enter">

    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
      <div>
        <p class="m-0 text-sm text-[#737373]">Money leaving the school</p>
        <h2 class="ght-display mb-0 mt-2 text-3xl leading-none tracking-normal sm:text-4xl">Expenses.</h2>
        <p class="m-0 mt-2 text-sm text-[#737373]" id="cn-expense-summary"></p>
      </div>
      <button type="button" id="cn-add-expense" class="ght-button ght-button--primary text-sm font-medium">
        <span class="ght-button-icon"><svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg></span>
        <span class="ght-button-label">Record expense</span>
      </button>
    </div>

    <section class="mt-6">
      <div class="cn-toolbar">
        <label class="ght-visually-hidden" for="cn-expense-search">Search expenses</label>
        <input type="search" id="cn-expense-search" class="cn-input" placeholder="Search description, payee or category&hellip;" autocomplete="off">
        <label class="ght-visually-hidden" for="cn-expense-category-filter">Filter by category</label>
        <select id="cn-expense-category-filter" class="cn-input cn-input--compact"></select>
        <label class="ght-visually-hidden" for="cn-expense-from">From date</label>
        <input type="date" id="cn-expense-from" class="cn-input cn-input--compact" aria-label="From date">
        <label class="ght-visually-hidden" for="cn-expense-to">To date</label>
        <input type="date" id="cn-expense-to" class="cn-input cn-input--compact" aria-label="To date">
      </div>

      <div id="cn-expense-list"></div>
      <p class="cn-prototype-note">Prototype &mdash; changes live in this browser tab only and reset on reload. Invoice and receipt fields (3 each) only remember the files' names; nothing is uploaded or stored.</p>
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
          <p class="m-0 mt-1 text-xs text-[#737373]">Every expense links to the one Main School Account.</p>
        </div>
      </div>

       <div class="cn-field">
        <label for="cn-expense-description">Description</label>
        <textarea id="cn-expense-description" name="description" class="cn-input cn-textarea" rows="2" placeholder="e.g. Generator servicing" data-cn-autofocus required></textarea>
      </div>

      <!-- "+ New category…" -->
      <div class="cn-field" id="cn-inline-category-row" hidden>
        <label for="cn-inline-category-name">New category name</label>
        <div class="cn-inline-form">
          <input type="text" id="cn-inline-category-name" class="cn-input" placeholder="e.g. Insurance">
          <button type="button" id="cn-inline-category-add" class="ght-button ght-button--primary text-sm font-medium"><span class="ght-button-label">Add</span></button>
          <button type="button" id="cn-inline-category-cancel" class="ght-button ght-button--secondary text-sm font-medium"><span class="ght-button-label">Cancel</span></button>
        </div>
      </div>

      <div class="cn-field-grid cn-attach-grid">
        <div class="cn-field">
          <label for="cn-expense-invoice-pick-1">Invoice</label>
          <div class="cn-attach" id="cn-expense-invoice-field">
            <div class="cn-file-row">
              <input type="file" id="cn-expense-invoice-1" class="ght-visually-hidden" tabindex="-1" aria-label="Invoice 1 file">
              <button type="button" class="ght-button ght-button--secondary text-sm font-medium cn-file-pick" id="cn-expense-invoice-pick-1" aria-label="Choose invoice 1 file"><span class="ght-button-label">Choose file</span></button>
              <span class="cn-file-name">No file chosen</span>
              <button type="button" class="cn-file-clear" aria-label="Remove invoice 1 file" title="Remove" hidden>&times;</button>
            </div>
            <div class="cn-file-row">
              <input type="file" id="cn-expense-invoice-2" class="ght-visually-hidden" tabindex="-1" aria-label="Invoice 2 file">
              <button type="button" class="ght-button ght-button--secondary text-sm font-medium cn-file-pick" id="cn-expense-invoice-pick-2" aria-label="Choose invoice 2 file"><span class="ght-button-label">Choose file</span></button>
              <span class="cn-file-name">No file chosen</span>
              <button type="button" class="cn-file-clear" aria-label="Remove invoice 2 file" title="Remove" hidden>&times;</button>
            </div>
            <div class="cn-file-row">
              <input type="file" id="cn-expense-invoice-3" class="ght-visually-hidden" tabindex="-1" aria-label="Invoice 3 file">
              <button type="button" class="ght-button ght-button--secondary text-sm font-medium cn-file-pick" id="cn-expense-invoice-pick-3" aria-label="Choose invoice 3 file"><span class="ght-button-label">Choose file</span></button>
              <span class="cn-file-name">No file chosen</span>
              <button type="button" class="cn-file-clear" aria-label="Remove invoice 3 file" title="Remove" hidden>&times;</button>
            </div>
          </div>
        </div>
        <div class="cn-field">
          <label for="cn-expense-receipt-pick-1">Receipt</label>
          <div class="cn-attach" id="cn-expense-receipt-field">
            <div class="cn-file-row">
              <input type="file" id="cn-expense-receipt-1" class="ght-visually-hidden" tabindex="-1" aria-label="Receipt 1 file">
              <button type="button" class="ght-button ght-button--secondary text-sm font-medium cn-file-pick" id="cn-expense-receipt-pick-1" aria-label="Choose receipt 1 file"><span class="ght-button-label">Choose file</span></button>
              <span class="cn-file-name">No file chosen</span>
              <button type="button" class="cn-file-clear" aria-label="Remove receipt 1 file" title="Remove" hidden>&times;</button>
            </div>
            <div class="cn-file-row">
              <input type="file" id="cn-expense-receipt-2" class="ght-visually-hidden" tabindex="-1" aria-label="Receipt 2 file">
              <button type="button" class="ght-button ght-button--secondary text-sm font-medium cn-file-pick" id="cn-expense-receipt-pick-2" aria-label="Choose receipt 2 file"><span class="ght-button-label">Choose file</span></button>
              <span class="cn-file-name">No file chosen</span>
              <button type="button" class="cn-file-clear" aria-label="Remove receipt 2 file" title="Remove" hidden>&times;</button>
            </div>
            <div class="cn-file-row">
              <input type="file" id="cn-expense-receipt-3" class="ght-visually-hidden" tabindex="-1" aria-label="Receipt 3 file">
              <button type="button" class="ght-button ght-button--secondary text-sm font-medium cn-file-pick" id="cn-expense-receipt-pick-3" aria-label="Choose receipt 3 file"><span class="ght-button-label">Choose file</span></button>
              <span class="cn-file-name">No file chosen</span>
              <button type="button" class="cn-file-clear" aria-label="Remove receipt 3 file" title="Remove" hidden>&times;</button>
            </div>
          </div>
        </div>
      </div>

      <div class="cn-field">
        <label for="cn-expense-notes">Notes</label>
        <textarea id="cn-expense-notes" name="notes" class="cn-input cn-textarea" rows="3" placeholder="Optional"></textarea>
      </div>

      <div class="cn-modal-actions">
        <button type="button" class="ght-button ght-button--secondary text-sm font-medium" data-cn-dismiss><span class="ght-button-label">Cancel</span></button>
        <button type="submit" id="cn-expense-form-submit" class="ght-button ght-button--primary text-sm font-medium"><span class="ght-button-label">Record expense</span></button>
      </div>
    </form>
  </div>
</div>

<?php require __DIR__ . '/../components/cn-confirm-modal.php'; ?>

<?php cn_render_seed(); ?>
<script src="assets/js/cn-store.js"></script>
<script src="assets/js/cn-modal.js"></script>
<script src="assets/js/cn-suggest.js"></script>
<script src="assets/js/cn-search.js"></script>
<script src="assets/js/cn-expenses.js"></script>
