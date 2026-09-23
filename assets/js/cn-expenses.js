(function () {
  'use strict';

  var el = window.CnDom.el;
  var clear = window.CnDom.clear;
  var store = window.CnStore;
  var filters = window.CnFilters;

  var view = { search: '', categoryId: '', from: '', to: '', includeVoided: false };

  var listNode = document.getElementById('cn-expense-list');
  var searchInput = document.getElementById('cn-expense-search');
  var categoryFilterSelect = document.getElementById('cn-expense-category-filter');
  var fromInput = document.getElementById('cn-expense-from');
  var toInput = document.getElementById('cn-expense-to');
  var voidedToggle = document.getElementById('cn-show-voided');
  var summaryNode = document.getElementById('cn-expense-summary');

  var formModal = new window.CnModal('cn-expense-form-modal');
  var categoryModal = new window.CnModal('cn-category-modal');
  var confirm = new window.CnConfirm('cn-confirm-modal');

  var form = document.getElementById('cn-expense-form');
  var formTitle = document.getElementById('cn-expense-form-title');
  var formSubmit = document.getElementById('cn-expense-form-submit');
  var categoryField = form.elements.categoryId;
  var editingId = null;

  var inlineCategoryRow = document.getElementById('cn-inline-category-row');
  var inlineCategoryName = document.getElementById('cn-inline-category-name');
  var inlineCategoryAdd = document.getElementById('cn-inline-category-add');

  var receiptInput = document.getElementById('cn-expense-receipt');
  var receiptName = document.getElementById('cn-expense-receipt-name');
  var invoiceInput = document.getElementById('cn-expense-invoice');
  var invoiceName = document.getElementById('cn-expense-invoice-name');
  var pendingReceipt = '';
  var pendingInvoice = '';

  var categoryListNode = document.getElementById('cn-category-list');
  var categoryForm = document.getElementById('cn-category-form');

  // Expense list 

  function categoryName(categoryId) {
    var match = store.getExpenseCategories().filter(function (c) { return c.id === categoryId; })[0];
    return match ? match.name : 'Uncategorised';
  }

  function attachmentsCell(exp) {
    var files = [exp.receipt, exp.invoice].filter(Boolean);
    if (!files.length) return el('span', { className: 'cn-attachment-none' }, ['None']);
    return el('span', { className: 'cn-attachments' },
      files.map(function (name) { return el('span', { className: 'cn-attachment-chip' }, [name]); }));
  }

  function expenseRow(exp) {
    var voided = exp.status === 'voided';
    return el('div', {
      className: 'cn-expense-list-row' + (voided ? ' is-voided' : ''),
      dataset: { cnId: exp.id }
    }, [
      el('span', {}, [
        el('span', { className: 'cn-expense-name' }, [exp.description]),
        el('span', { className: 'cn-expense-meta' }, [categoryName(exp.categoryId) + (voided ? ' \u00B7 Voided' : '')])
      ]),
      el('span', { className: 'cn-expense-amount' }, [filters.formatNaira(exp.amount)]),
      el('span', {}, [filters.formatDate(exp.expenseDate)]),
      el('span', {}, [exp.payee]),
      attachmentsCell(exp),
      voided
        ? el('span', { className: 'cn-list-row-actions' }, [
            el('button', {
              type: 'button', className: 'cn-btn-text',
              onclick: function () {
                store.restoreExpense(exp.id);
                showToast('Restoring expense', 'Expense restored', exp.description);
              }
            }, ['Restore'])
          ])
        : el('span', { className: 'cn-list-row-actions' }, [
            el('button', {
              type: 'button', className: 'cn-btn-text',
              onclick: function () { openEdit(exp); }
            }, ['Edit']),
            el('button', {
              type: 'button', className: 'cn-btn-danger-text',
              onclick: function () { askVoid(exp); }
            }, ['Void'])
          ])
    ]);
  }

  function renderList() {
    var visible = filters.filterExpenses(
      store.getExpenses(), view.search, view.categoryId, view.from, view.to, view.includeVoided
    );
    clear(listNode);

    if (!visible.length) {
      var filtering = view.search || view.categoryId || view.from || view.to;
      listNode.appendChild(el('div', { className: 'ght-card cn-empty' }, [
        el('p', { className: 'cn-empty-title' }, ['No expenses to show']),
        el('p', { className: 'cn-empty-body' }, [
          filtering
            ? 'Nothing matches the current search or filters.'
            : 'Record your first expense to start tracking money leaving the school.'
        ]),
        filtering
          ? el('button', { type: 'button', className: 'cn-btn-text', onclick: resetFilters }, ['Clear filters'])
          : el('button', {
              type: 'button', className: 'ght-button ght-button--primary text-sm font-medium',
              onclick: function () { openAdd(); }
            }, [el('span', { className: 'ght-button-label' }, ['Record expense'])])
      ]));
      return;
    }

    visible = visible.slice().sort(function (a, b) { return b.expenseDate < a.expenseDate ? -1 : 1; });

    var wrap = el('div', { className: 'cn-expense-list-wrap' }, [
      el('div', { className: 'cn-expense-list-header' }, [
        el('span', {}, ['Description']), el('span', {}, ['Amount']), el('span', {}, ['Date']),
        el('span', {}, ['Payee']), el('span', {}, ['Attachments']), el('span', {}, [])
      ])
    ]);
    visible.forEach(function (exp) { wrap.appendChild(expenseRow(exp)); });
    listNode.appendChild(wrap);
  }

  function renderSummary() {
    var active = store.getExpenses().filter(function (e) { return e.status === 'active'; });
    var total = filters.sumAmounts(active);
    summaryNode.textContent = active.length + (active.length === 1 ? ' expense' : ' expenses')
      + ' recorded \u00B7 ' + filters.formatNaira(total) + ' total';
  }

  function renderCategoryFilterOptions() {
    var current = categoryFilterSelect.value;
    clear(categoryFilterSelect);
    categoryFilterSelect.appendChild(el('option', { value: '' }, ['All categories']));
    store.getExpenseCategories().forEach(function (c) {
      categoryFilterSelect.appendChild(el('option', { value: c.id }, [c.name]));
    });
    categoryFilterSelect.value = current;
  }

  function render() {
    renderCategoryFilterOptions();
    renderList();
    renderSummary();
  }

  function resetFilters() {
    view.search = ''; view.categoryId = ''; view.from = ''; view.to = ''; view.includeVoided = false;
    searchInput.value = ''; fromInput.value = ''; toInput.value = ''; voidedToggle.checked = false;
    render();
  }

  // Expense form 

  function renderCategoryOptions(current) {
    clear(categoryField);
    store.getExpenseCategories().forEach(function (c) {
      categoryField.appendChild(el('option', { value: c.id }, [c.name]));
    });
    categoryField.appendChild(el('option', { value: '__new__' }, ['+ New category\u2026']));
    if (current) categoryField.value = current;
    inlineCategoryRow.hidden = categoryField.value !== '__new__';
  }

  function resetFileFields() {
    receiptInput.value = '';
    invoiceInput.value = '';
    receiptName.textContent = 'No file chosen';
    invoiceName.textContent = 'No file chosen';
    pendingReceipt = '';
    pendingInvoice = '';
  }

  function openAdd() {
    editingId = null;
    form.reset();
    formTitle.textContent = 'Record expense';
    formSubmit.querySelector('.ght-button-label').textContent = 'Record expense';
    renderCategoryOptions();
    form.elements.expenseDate.value = new Date().toISOString().slice(0, 10);
    resetFileFields();
    formModal.open();
  }

  function openEdit(exp) {
    editingId = exp.id;
    formTitle.textContent = 'Edit expense';
    formSubmit.querySelector('.ght-button-label').textContent = 'Save changes';
    form.elements.description.value = exp.description;
    form.elements.amount.value = exp.amount;
    form.elements.expenseDate.value = exp.expenseDate;
    form.elements.payee.value = exp.payee;
    form.elements.paymentMethod.value = exp.paymentMethod || 'Cash';
    form.elements.notes.value = exp.notes || '';
    renderCategoryOptions(exp.categoryId);
    resetFileFields();
    pendingReceipt = exp.receipt || '';
    pendingInvoice = exp.invoice || '';
    receiptName.textContent = pendingReceipt || 'No file chosen';
    invoiceName.textContent = pendingInvoice || 'No file chosen';
    formModal.open();
  }

  function askVoid(exp) {
    confirm.ask({
      title: 'Void expense',
      body: 'Void "' + exp.description + '" (' + filters.formatNaira(exp.amount) + ')? It stays in the record but is excluded from totals and reports.',
      confirmLabel: 'Void expense',
      onConfirm: function () {
        store.voidExpense(exp.id);
        showToast('Voiding expense', 'Expense voided', exp.description);
      }
    });
  }

  categoryField.addEventListener('change', function () {
    inlineCategoryRow.hidden = categoryField.value !== '__new__';
    if (!inlineCategoryRow.hidden) inlineCategoryName.focus();
  });

  inlineCategoryAdd.addEventListener('click', function () {
    var name = inlineCategoryName.value.trim();
    if (!name) return;
    var before = store.getExpenseCategories().length;
    var record = store.addExpenseCategory(name);
    inlineCategoryName.value = '';
    renderCategoryOptions(record.id);
    if (store.getExpenseCategories().length > before) {
      showToast('Adding category', 'Category added', name);
    }
  });

  receiptInput.addEventListener('change', function () {
    pendingReceipt = receiptInput.files[0] ? receiptInput.files[0].name : '';
    receiptName.textContent = pendingReceipt || 'No file chosen';
  });
  invoiceInput.addEventListener('change', function () {
    pendingInvoice = invoiceInput.files[0] ? invoiceInput.files[0].name : '';
    invoiceName.textContent = pendingInvoice || 'No file chosen';
  });

  form.addEventListener('submit', function (event) {
    event.preventDefault();
    if (categoryField.value === '__new__') {
      inlineCategoryName.focus();
      return;
    }
    var payload = {
      description: form.elements.description.value.trim(),
      categoryId: categoryField.value,
      amount: Number(form.elements.amount.value),
      expenseDate: form.elements.expenseDate.value,
      payee: form.elements.payee.value.trim(),
      paymentMethod: form.elements.paymentMethod.value,
      receipt: pendingReceipt || null,
      invoice: pendingInvoice || null,
      notes: form.elements.notes.value.trim()
    };
    if (!payload.description || !payload.categoryId || !(payload.amount > 0) || !payload.expenseDate || !payload.payee) return;

    if (editingId) {
      store.updateExpense(editingId, payload);
      showToast('Saving expense', 'Expense updated', payload.description);
    } else {
      store.addExpense(payload);
      showToast('Recording expense', 'Expense recorded', payload.description);
    }
    formModal.close();
  });

  // Categories modal

  function renderCategoryList() {
    clear(categoryListNode);
    store.getExpenseCategories().forEach(function (c) {
      var count = store.getExpenses().filter(function (e) {
        return e.categoryId === c.id && e.status === 'active';
      }).length;
      categoryListNode.appendChild(el('li', { className: 'cn-type-row' }, [
        el('span', {}, [c.name]),
        el('span', { className: 'cn-type-count' }, [count ? count + (count === 1 ? ' expense' : ' expenses') : 'Unused'])
      ]));
    });
  }

  categoryForm.addEventListener('submit', function (event) {
    event.preventDefault();
    var name = categoryForm.elements.name.value.trim();
    if (!name) return;
    var before = store.getExpenseCategories().length;
    store.addExpenseCategory(name);
    categoryForm.reset();
    categoryForm.elements.name.focus();
    if (store.getExpenseCategories().length > before) {
      showToast('Adding category', 'Category added', name);
    }
  });

  // Wiring 

  searchInput.addEventListener('input', function () { view.search = searchInput.value; renderList(); });
  categoryFilterSelect.addEventListener('change', function () { view.categoryId = categoryFilterSelect.value; renderList(); });
  fromInput.addEventListener('change', function () { view.from = fromInput.value; renderList(); });
  toInput.addEventListener('change', function () { view.to = toInput.value; renderList(); });
  voidedToggle.addEventListener('change', function () { view.includeVoided = voidedToggle.checked; renderList(); });

  document.getElementById('cn-add-expense').addEventListener('click', openAdd);
  document.getElementById('cn-manage-categories').addEventListener('click', function () {
    renderCategoryList();
    categoryModal.open();
  });

  store.subscribe(function () {
    render();
    if (categoryModal.isOpen()) renderCategoryList();
    if (formModal.isOpen()) renderCategoryOptions(categoryField.value);
  });
  render();
})();
