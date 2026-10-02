(function () {
  'use strict';

  var el = window.CnDom.el;
  var clear = window.CnDom.clear;
  var store = window.CnStore;
  var filters = window.CnFilters;

  var view = { search: '', categoryId: '', from: '', to: '' };

  var listNode = document.getElementById('cn-expense-list');
  var searchInput = document.getElementById('cn-expense-search');
  var categoryFilterSelect = document.getElementById('cn-expense-category-filter');
  var fromInput = document.getElementById('cn-expense-from');
  var toInput = document.getElementById('cn-expense-to');
  var summaryNode = document.getElementById('cn-expense-summary');

  var formModal = new window.CnModal('cn-expense-form-modal');
  var confirm = new window.CnConfirm('cn-confirm-modal');

  var form = document.getElementById('cn-expense-form');
  var formTitle = document.getElementById('cn-expense-form-title');
  var formSubmit = document.getElementById('cn-expense-form-submit');
  var categoryField = form.elements.categoryId;
  var NEW_CATEGORY = '__new__';
  var lastCategory = '';
  var editingId = null;

  var inlineCategoryRow = document.getElementById('cn-inline-category-row');
  var inlineCategoryName = document.getElementById('cn-inline-category-name');
  var inlineCategoryAdd = document.getElementById('cn-inline-category-add');
  var inlineCategoryCancel = document.getElementById('cn-inline-category-cancel');

  var MAX_FILES = filters.maxFiles;

  function createFileSlots(kind) {
    var box = document.getElementById('cn-expense-' + kind + '-field');
    var rows = Array.prototype.map.call(box.querySelectorAll('.cn-file-row'), function (node) {
      return {
        node: node,
        input: node.querySelector('input[type="file"]'),
        pick: node.querySelector('.cn-file-pick'),
        pickLabel: node.querySelector('.ght-button-label'),
        name: node.querySelector('.cn-file-name'),
        clear: node.querySelector('.cn-file-clear'),
        value: ''
      };
    });

    function paint(row) {
      row.node.classList.toggle('has-file', !!row.value);
      row.name.textContent = row.value || 'No file chosen';
      row.name.title = row.value;
      row.pickLabel.textContent = row.value ? 'Change' : 'Choose file';
      row.clear.hidden = !row.value;
    }

    rows.forEach(function (row) {
      row.pick.addEventListener('click', function () { row.input.click(); });
      row.input.addEventListener('change', function () {
        var file = row.input.files && row.input.files[0];
        row.input.value = ''; 
        if (!file) return;    
        row.value = file.name;
        paint(row);
      });
      row.clear.addEventListener('click', function () {
        row.value = '';
        paint(row);
        row.pick.focus();
      });
      paint(row);
    });

    return {
      get: function () {
        return rows.map(function (r) { return r.value; }).filter(Boolean);
      },
      set: function (names) {
        rows.forEach(function (row, i) {
          row.value = (names && names[i]) || '';
          row.input.value = '';
          paint(row);
        });
      }
    };
  }

  var invoiceSlot = createFileSlots('invoice');
  var receiptSlot = createFileSlots('receipt');
  var touched = { category: false, method: false };

  // Suggestions 

  var suggest = window.CnSuggest;
  var catalog = suggest.catalog;

  function unique(list) {
    var seen = {};
    return list.filter(function (value) {
      var key = String(value || '').trim().toLowerCase();
      if (!key || seen[key]) return false;
      seen[key] = true;
      return true;
    });
  }

  /** Newest first - recent entries lead the suggestions. */
  function recorded(field) {
    return unique(filters.sortExpenses(store.getExpenses()).map(function (e) { return e[field]; }));
  }

  function lastRecordWith(field, value) {
    var key = String(value || '').trim().toLowerCase();
    return filters.sortExpenses(store.getExpenses()).filter(function (e) {
      return String(e[field] || '').trim().toLowerCase() === key;
    })[0] || null;
  }

  function prefillFrom(record, fields) {
    if (!record || editingId) return;
    if (fields.indexOf('payee') !== -1 && !form.elements.payee.value.trim()) {
      form.elements.payee.value = record.payee || '';
    }
    if (fields.indexOf('amount') !== -1 && !form.elements.amount.value) {
      form.elements.amount.value = record.amount;
    }
    if (fields.indexOf('category') !== -1 && !touched.category && categoryField.value !== NEW_CATEGORY
        && store.getExpenseCategories().some(function (c) { return c.id === record.categoryId; })) {
      categoryField.value = record.categoryId;
      lastCategory = record.categoryId;
    }
    if (fields.indexOf('method') !== -1 && !touched.method && record.paymentMethod) {
      form.elements.paymentMethod.value = record.paymentMethod;
    }
  }

  suggest.attach(form.elements.description, {
    onPick: function (item) { prefillFrom(lastRecordWith('description', item.value), ['payee', 'amount', 'category', 'method']); },
    source: suggest.fromGroups([
      { hint: 'Recorded before', values: function () { return recorded('description'); } },
      { hint: 'Suggested', values: catalog.descriptions }
    ])
  });
  suggest.attach(form.elements.payee, {
    onPick: function (item) { prefillFrom(lastRecordWith('payee', item.value), ['category', 'method']); },
    source: suggest.fromGroups([
      { hint: 'Recorded before', values: function () { return recorded('payee'); } },
      { hint: 'Suggested', values: catalog.payees }
    ])
  });
  suggest.attach(form.elements.notes, {
    source: suggest.fromGroups([
      { hint: 'Recorded before', values: function () { return recorded('notes'); } },
      { hint: 'Suggested', values: catalog.notes }
    ])
  });
  suggest.attach(inlineCategoryName, {
    source: suggest.fromGroups([{
      hint: 'Suggested',
      values: function () {
        var existing = store.getExpenseCategories().map(function (c) { return c.name.toLowerCase(); });
        return catalog.categories.filter(function (name) { return existing.indexOf(name.toLowerCase()) === -1; });
      }
    }])
  });
  suggest.attach(searchInput, {
    source: suggest.fromGroups([
      { hint: 'Description', values: function () { return recorded('description'); } },
      { hint: 'Payee', values: function () { return recorded('payee'); } },
      { hint: 'Category', values: function () { return store.getExpenseCategories().map(function (c) { return c.name; }); } }
    ])
  });

  // Expense list 

  function categoryName(categoryId) {
    var match = store.getExpenseCategories().filter(function (c) { return c.id === categoryId; })[0];
    return match ? match.name : 'Uncategorised';
  }

  function attachmentLine(label, names) {
    if (!names.length) return null;
    var line = el('span', {
      className: 'cn-attachment-line',
      title: label + (names.length > 1 ? 's' : '') + ': ' + names.join(', ')
    }, [
      el('span', { className: 'cn-attachment-kind' }, [label]),
      el('span', { className: 'cn-attachment-file' }, [names[0]])
    ]);
    if (names.length > 1) line.appendChild(el('span', { className: 'cn-attachment-more' }, ['+' + (names.length - 1)]));
    return line;
  }

  function attachmentsCell(exp) {
    var lines = [
      attachmentLine('Invoice', filters.expenseFiles(exp, 'invoice')),
      attachmentLine('Receipt', filters.expenseFiles(exp, 'receipt'))
    ].filter(Boolean);
    if (!lines.length) return el('span', { className: 'cn-attachment-none cn-exp-files' }, ['No attachments']);
    return el('span', { className: 'cn-attachments cn-exp-files' }, lines);
  }

  function expenseRow(exp) {
    return el('div', {
      className: 'cn-expense-list-row',
      dataset: { cnId: exp.id }
    }, [
      el('span', { className: 'cn-exp-desc' }, [
        el('span', { className: 'cn-expense-name' }, [exp.description]),
        el('span', { className: 'cn-expense-meta' }, [categoryName(exp.categoryId)])
      ]),
      el('span', { className: 'cn-expense-amount cn-exp-amount' }, [filters.formatNaira(exp.amount)]),
      el('span', { className: 'cn-exp-date' }, [filters.formatDate(exp.expenseDate)]),
      el('span', { className: 'cn-exp-payee' }, [exp.payee]),
      attachmentsCell(exp),
      el('span', { className: 'cn-list-row-actions cn-exp-actions' }, [
        el('button', {
          type: 'button', className: 'cn-btn-text',
          onclick: function () { openEdit(exp); }
        }, ['Edit']),
        el('button', {
          type: 'button', className: 'cn-btn-danger-text',
          onclick: function () { askDelete(exp); }
        }, ['Delete'])
      ])
    ]);
  }

  function renderList() {
    var visible = filters.filterExpenses(
      store.getExpenses(), view.search, view.categoryId, view.from, view.to, false, categoryName
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

    visible = filters.sortExpenses(visible);

    var wrap = el('div', { className: 'cn-expense-list-wrap' }, [
      el('div', { className: 'cn-expense-list-header' }, [
        el('span', { className: 'cn-exp-desc' }, ['Description']),
        el('span', { className: 'cn-exp-amount' }, ['Amount']),
        el('span', { className: 'cn-exp-date' }, ['Date']),
        el('span', { className: 'cn-exp-payee' }, ['Payee']),
        el('span', { className: 'cn-exp-files' }, ['Attachments']),
        el('span', { className: 'cn-exp-actions' }, [])
      ])
    ]);
    visible.forEach(function (exp) { wrap.appendChild(expenseRow(exp)); });
    listNode.appendChild(wrap);
  }

  function renderSummary() {
    var active = store.getExpenses().filter(function (e) { return e.status !== 'voided'; });
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
    view.search = ''; view.categoryId = ''; view.from = ''; view.to = '';
    searchInput.value = ''; fromInput.value = ''; toInput.value = '';
    render();
  }

  // Expense form 

  function renderCategoryOptions(current) {
    clear(categoryField);
    store.getExpenseCategories().forEach(function (c) {
      categoryField.appendChild(el('option', { value: c.id }, [c.name]));
    });
    categoryField.appendChild(el('option', { value: NEW_CATEGORY }, ['+ New category\u2026']));
    if (current) categoryField.value = current;
    inlineCategoryRow.hidden = categoryField.value !== NEW_CATEGORY;
    if (categoryField.value !== NEW_CATEGORY) lastCategory = categoryField.value;
  }

  function cancelNewCategory() {
    inlineCategoryName.value = '';
    categoryField.value = lastCategory || (categoryField.options[0] && categoryField.options[0].value) || '';
    inlineCategoryRow.hidden = categoryField.value !== NEW_CATEGORY;
    if (inlineCategoryRow.hidden) categoryField.focus();
  }

  function commitNewCategory() {
    var name = inlineCategoryName.value.trim();
    if (!name) { inlineCategoryName.focus(); return; }
    var before = store.getExpenseCategories().length;
    var record = store.addExpenseCategory(name);
    inlineCategoryName.value = '';
    renderCategoryOptions(record.id);
    if (store.getExpenseCategories().length > before) {
      showToast('Adding category', 'Category added', name);
    }
  }

  function resetFileFields() {
    invoiceSlot.set([]);
    receiptSlot.set([]);
  }

  function openAdd() {
    editingId = null;
    touched.category = false;
    touched.method = false;
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
    invoiceSlot.set(filters.expenseFiles(exp, 'invoice'));
    receiptSlot.set(filters.expenseFiles(exp, 'receipt'));
    formModal.open();
  }

  function askDelete(exp) {
    confirm.ask({
      title: 'Delete expense',
      body: 'Delete "' + exp.description + '" (' + filters.formatNaira(exp.amount) + ')? This removes it permanently, and it no longer counts toward totals or reports.',
      confirmLabel: 'Delete expense',
      onConfirm: function () {
        store.deleteExpense(exp.id);
        showToast('Deleting expense', 'Expense deleted', exp.description);
      }
    });
  }

  form.elements.paymentMethod.addEventListener('change', function () { touched.method = true; });

  categoryField.addEventListener('change', function () {
    touched.category = true;
    if (categoryField.value === NEW_CATEGORY) {
      inlineCategoryRow.hidden = false;
      inlineCategoryName.focus();
    } else {
      lastCategory = categoryField.value;
      inlineCategoryRow.hidden = true;
      inlineCategoryName.value = '';
    }
  });

  inlineCategoryAdd.addEventListener('click', commitNewCategory);
  inlineCategoryCancel.addEventListener('click', cancelNewCategory);
  inlineCategoryName.addEventListener('keydown', function (event) {
    if (event.key === 'Enter') {
      event.preventDefault();
      commitNewCategory();
    } else if (event.key === 'Escape') {
      event.stopPropagation();
      cancelNewCategory();
    }
  });

  form.addEventListener('submit', function (event) {
    event.preventDefault();
    if (categoryField.value === NEW_CATEGORY) {
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
      receipts: receiptSlot.get(),
      invoices: invoiceSlot.get(),
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

  // Wiring 

  window.CnSearch.bind(searchInput, function (value) { view.search = value; renderList(); });
  categoryFilterSelect.addEventListener('change', function () { view.categoryId = categoryFilterSelect.value; renderList(); });
  fromInput.addEventListener('change', function () { view.from = fromInput.value; renderList(); });
  toInput.addEventListener('change', function () { view.to = toInput.value; renderList(); });

  document.getElementById('cn-add-expense').addEventListener('click', openAdd);

  store.subscribe(function () {
    render();
    if (formModal.isOpen()) renderCategoryOptions(categoryField.value);
  });
  render();
})();
