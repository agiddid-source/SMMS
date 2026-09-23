(function () {
  'use strict';

  var el = window.CnDom.el;
  var clear = window.CnDom.clear;
  var store = window.CnStore;
  var filters = window.CnFilters;

  var tabsNav = document.getElementById('cn-report-tabs');
  var content = document.getElementById('cn-report-content');

  var activeTab = 'collection';
  var state = {
    collection: { session: '', term: '', classId: '' },
    outstanding: { session: '', term: '', classId: '' },
    payments: { search: '', classId: '', from: '', to: '' },
    expenses: { search: '', categoryId: '', from: '', to: '' },
    incomeExpenditure: { from: '', to: '' }
  };

  var TABS = [
    { id: 'collection', label: 'Fee collection', render: renderCollection },
    { id: 'outstanding', label: 'Outstanding fees', render: renderOutstanding },
    { id: 'payments', label: 'Payments', render: renderPayments },
    { id: 'expenses', label: 'Expenses', render: renderExpenseReport },
    { id: 'incomeExpenditure', label: 'Income vs expenditure', render: renderIncomeExpenditure }
  ];

  // Small shared builders 

  function statCard(label, value, tone) {
    return el('div', { className: 'ght-card t-resize cn-stat-card' }, [
      el('p', { className: 'cn-stat-label' }, [label]),
      el('p', { className: 'cn-stat-value' + (tone ? ' cn-stat-value--' + tone : '') }, [value])
    ]);
  }

  function filterRow(children) {
    return el('div', { className: 'cn-toolbar' }, children);
  }

  function select(options, value, placeholder, onChange) {
    var node = el('select', { className: 'cn-input cn-input--compact' });
    node.onchange = function () { onChange(node.value); };
    if (placeholder) node.appendChild(el('option', { value: '' }, [placeholder]));
    options.forEach(function (opt) {
      node.appendChild(el('option', { value: opt.value }, [opt.label]));
    });
    node.value = value || '';
    return node;
  }

  function dateInput(value, label, onChange) {
    var node = el('input', { type: 'date', className: 'cn-input cn-input--compact', value: value || '' });
    node.setAttribute('aria-label', label);
    node.onchange = function () { onChange(node.value); };
    return node;
  }

  function searchBox(value, placeholder, onChange) {
    var node = el('input', { type: 'search', className: 'cn-input', placeholder: placeholder, value: value || '' });
    node.oninput = function () { onChange(node.value); };
    return node;
  }

  /** cols: [label,...]; colsClass: one of the cn-report-cols--* modifiers;
   *  rows: array of arrays of strings/nodes, one per cell, in cols order. */
  function reportTable(cols, colsClass, rows, emptyMessage) {
    var wrap = el('div', { className: 'cn-report-table-wrap' });
    wrap.appendChild(el('div', { className: 'cn-report-header ' + colsClass },
      cols.map(function (c) { return el('span', {}, [c]); })));

    if (!rows.length) {
      wrap.appendChild(el('div', { className: 'cn-empty' }, [
        el('p', { className: 'cn-empty-body' }, [emptyMessage || 'Nothing matches the current filters.'])
      ]));
      return wrap;
    }

    rows.forEach(function (cells) {
      wrap.appendChild(el('div', { className: 'cn-report-row ' + colsClass },
        cells.map(function (cell, i) {
          return el('span', { dataset: { cnLabel: cols[i] } }, [cell]);
        })));
    });
    return wrap;
  }

  function uniqueSorted(values) {
    return values.filter(function (v, i) { return v && values.indexOf(v) === i; }).sort();
  }
  function distinctFeeSessions() { return uniqueSorted(store.getFees().map(function (f) { return f.academicSession; })); }
  function distinctFeeTerms() { return uniqueSorted(store.getFees().map(function (f) { return f.term; })); }

  function classOptions() {
    return store.getClasses()
      .filter(function (c) { return c.status === 'active'; })
      .map(function (c) { return { value: c.id, label: c.name + ' (' + c.section + ')' }; });
  }

  function className(classId) {
    var match = store.getClasses().filter(function (c) { return c.id === classId; })[0];
    return match ? match.name : classId;
  }

  function feeById(feeId) {
    return store.getFees().filter(function (f) { return f.id === feeId; })[0] || null;
  }

  function categoryName(categoryId) {
    var match = store.getExpenseCategories().filter(function (c) { return c.id === categoryId; })[0];
    return match ? match.name : 'Uncategorised';
  }

  // Tab 1: Fee collection 

  function renderCollection() {
    var s = state.collection;
    var fees = store.getFees().filter(function (f) {
      if (f.status !== 'active') return false;
      if (s.session && f.academicSession !== s.session) return false;
      if (s.term && f.term !== s.term) return false;
      if (s.classId && (f.assignedClasses || []).indexOf(s.classId) === -1) return false;
      return true;
    });
    var payments = store.getPayments();

    var totalExpected = 0, totalCollected = 0, totalOutstanding = 0;
    var rows = fees.map(function (fee) {
      var assignedClasses = s.classId
        ? (fee.assignedClasses || []).filter(function (id) { return id === s.classId; })
        : (fee.assignedClasses || []);
      var expected = fee.amount * assignedClasses.length;
      var matchingPayments = payments.filter(function (p) {
        return p.feeId === fee.id && (!s.classId || p.classId === s.classId);
      });
      var collected = filters.sumAmounts(matchingPayments);
      var outstanding = Math.max(expected - collected, 0);

      totalExpected += expected;
      totalCollected += collected;
      totalOutstanding += outstanding;

      var statusCell = outstanding > 0
        ? el('span', {}, [filters.formatNaira(outstanding) + ' outstanding'])
        : el('span', { className: 'ght-chip ght-chip--success' }, ['Fully collected']);

      return [
        fee.name, fee.type,
        assignedClasses.length + (assignedClasses.length === 1 ? ' class' : ' classes'),
        filters.formatNaira(expected), filters.formatNaira(collected), statusCell
      ];
    });

    clear(content);
    content.appendChild(filterRow([
      select(distinctFeeSessions().map(function (v) { return { value: v, label: v }; }), s.session, 'All sessions',
        function (v) { s.session = v; renderCollection(); }),
      select(distinctFeeTerms().map(function (v) { return { value: v, label: v }; }), s.term, 'All terms',
        function (v) { s.term = v; renderCollection(); }),
      select(classOptions(), s.classId, 'All classes',
        function (v) { s.classId = v; renderCollection(); })
    ]));
    content.appendChild(el('div', { className: 'cn-stat-cards' }, [
      statCard('Total expected', filters.formatNaira(totalExpected)),
      statCard('Total collected', filters.formatNaira(totalCollected), 'positive'),
      statCard('Total outstanding', filters.formatNaira(totalOutstanding), totalOutstanding > 0 ? 'negative' : 'positive')
    ]));
    content.appendChild(el('p', { className: 'cn-report-note' }, [
      '\u201CExpected\u201D is the fee amount \u00D7 the number of classes it is assigned to \u2014 a per-class approximation, ' +
      'since per-student enrolment isn\u2019t tracked until Student Fee Accounts is built.'
    ]));
    content.appendChild(reportTable(
      ['Fee', 'Type', 'Assigned classes', 'Expected', 'Collected', 'Status'],
      'cn-report-cols--collection', rows, 'No active fees match these filters.'
    ));
  }

  // Tab 2: Outstanding fees 

  function renderOutstanding() {
    var s = state.outstanding;
    var fees = store.getFees().filter(function (f) {
      if (f.status !== 'active') return false;
      if (s.session && f.academicSession !== s.session) return false;
      if (s.term && f.term !== s.term) return false;
      return true;
    });
    var payments = store.getPayments();

    var rows = [];
    var totalOutstanding = 0;
    fees.forEach(function (fee) {
      (fee.assignedClasses || []).forEach(function (classId) {
        if (s.classId && classId !== s.classId) return;
        var collected = filters.sumAmounts(payments.filter(function (p) {
          return p.feeId === fee.id && p.classId === classId;
        }));
        var outstanding = fee.amount - collected;
        if (outstanding <= 0) return;
        totalOutstanding += outstanding;
        rows.push([
          className(classId), fee.name,
          filters.formatNaira(fee.amount), filters.formatNaira(collected),
          filters.formatNaira(outstanding)
        ]);
      });
    });

    clear(content);
    content.appendChild(filterRow([
      select(distinctFeeSessions().map(function (v) { return { value: v, label: v }; }), s.session, 'All sessions',
        function (v) { s.session = v; renderOutstanding(); }),
      select(distinctFeeTerms().map(function (v) { return { value: v, label: v }; }), s.term, 'All terms',
        function (v) { s.term = v; renderOutstanding(); }),
      select(classOptions(), s.classId, 'All classes',
        function (v) { s.classId = v; renderOutstanding(); })
    ]));
    content.appendChild(el('div', { className: 'cn-stat-cards' }, [
      statCard('Classes with a balance', String(rows.length)),
      statCard('Total outstanding', filters.formatNaira(totalOutstanding), totalOutstanding > 0 ? 'negative' : 'positive')
    ]));
    content.appendChild(el('p', { className: 'cn-report-note' }, [
      'Shown per class, not per student \u2014 individual student balances belong to Student Fee Accounts, which isn\u2019t built yet.'
    ]));
    content.appendChild(reportTable(
      ['Class', 'Fee', 'Expected', 'Collected', 'Outstanding'],
      'cn-report-cols--outstanding', rows, 'Nothing outstanding for these filters \u2014 everything matched is fully collected.'
    ));
  }

  // Tab 3: Payments 

  function renderPayments() {
    var s = state.payments;
    var visible = filters.filterPayments(store.getPayments(), s.search, s.classId, '', s.from, s.to)
      .slice().sort(function (a, b) { return b.paymentDate < a.paymentDate ? -1 : 1; });

    clear(content);
    content.appendChild(filterRow([
      searchBox(s.search, 'Search student or receipt no.\u2026', function (v) { s.search = v; renderPayments(); }),
      select(classOptions(), s.classId, 'All classes', function (v) { s.classId = v; renderPayments(); }),
      dateInput(s.from, 'From date', function (v) { s.from = v; renderPayments(); }),
      dateInput(s.to, 'To date', function (v) { s.to = v; renderPayments(); })
    ]));
    content.appendChild(el('div', { className: 'cn-stat-cards' }, [
      statCard('Payments', String(visible.length)),
      statCard('Total collected', filters.formatNaira(filters.sumAmounts(visible)), 'positive')
    ]));
    content.appendChild(reportTable(
      ['Date', 'Student', 'Fee', 'Class', 'Amount', 'Method', 'Receipt no.'],
      'cn-report-cols--payments',
      visible.map(function (p) {
        var fee = feeById(p.feeId);
        return [
          filters.formatDate(p.paymentDate), p.studentName, fee ? fee.name : p.feeId, className(p.classId),
          filters.formatNaira(p.amount), p.paymentMethod, p.receiptNumber
        ];
      }),
      'No payments recorded for these filters.'
    ));
  }

  // Tab 4: Expenses 

  function renderExpenseReport() {
    var s = state.expenses;
    var visible = filters.filterExpenses(store.getExpenses(), s.search, s.categoryId, s.from, s.to, false)
      .slice().sort(function (a, b) { return b.expenseDate < a.expenseDate ? -1 : 1; });

    var categoryOptions = store.getExpenseCategories().map(function (c) { return { value: c.id, label: c.name }; });

    clear(content);
    content.appendChild(filterRow([
      searchBox(s.search, 'Search description or payee\u2026', function (v) { s.search = v; renderExpenseReport(); }),
      select(categoryOptions, s.categoryId, 'All categories', function (v) { s.categoryId = v; renderExpenseReport(); }),
      dateInput(s.from, 'From date', function (v) { s.from = v; renderExpenseReport(); }),
      dateInput(s.to, 'To date', function (v) { s.to = v; renderExpenseReport(); })
    ]));
    content.appendChild(el('div', { className: 'cn-stat-cards' }, [
      statCard('Expenses', String(visible.length)),
      statCard('Total spent', filters.formatNaira(filters.sumAmounts(visible)), 'negative')
    ]));
    content.appendChild(reportTable(
      ['Date', 'Description', 'Category', 'Payee', 'Amount'],
      'cn-report-cols--expenses',
      visible.map(function (e) {
        return [filters.formatDate(e.expenseDate), e.description, categoryName(e.categoryId), e.payee, filters.formatNaira(e.amount)];
      }),
      'No expenses recorded for these filters. Voided expenses are always excluded.'
    ));
  }

  // Tab 5: Income vs expenditure 

  function renderIncomeExpenditure() {
    var s = state.incomeExpenditure;
    var payments = store.getPayments().filter(function (p) {
      return (!s.from && !s.to) || filters.inDateRange(p.paymentDate, s.from, s.to);
    });
    var expenses = store.getExpenses().filter(function (e) {
      return e.status === 'active' && ((!s.from && !s.to) || filters.inDateRange(e.expenseDate, s.from, s.to));
    });

    var income = filters.sumAmounts(payments);
    var expenditure = filters.sumAmounts(expenses);
    var net = income - expenditure;

    var incomeByFee = filters.sumByKey(payments, function (p) {
      var fee = feeById(p.feeId);
      return fee ? fee.name : p.feeId;
    });
    var expenditureByCategory = filters.sumByKey(expenses, function (e) { return categoryName(e.categoryId); });

    clear(content);
    content.appendChild(filterRow([
      dateInput(s.from, 'From date', function (v) { s.from = v; renderIncomeExpenditure(); }),
      dateInput(s.to, 'To date', function (v) { s.to = v; renderIncomeExpenditure(); })
    ]));
    content.appendChild(el('div', { className: 'cn-stat-cards' }, [
      statCard('Total income', filters.formatNaira(income), 'positive'),
      statCard('Total expenditure', filters.formatNaira(expenditure), 'negative'),
      statCard('Net position', filters.formatNaira(net), net >= 0 ? 'positive' : 'negative')
    ]));

    content.appendChild(el('p', { className: 'cn-report-breakdown-title' }, ['Income by fee']));
    content.appendChild(reportTable(['Fee', 'Amount'], 'cn-report-cols--breakdown',
      incomeByFee.map(function (row) { return [row.key, filters.formatNaira(row.total)]; }),
      'No payments in this range.'));

    content.appendChild(el('p', { className: 'cn-report-breakdown-title' }, ['Expenditure by category']));
    content.appendChild(reportTable(['Category', 'Amount'], 'cn-report-cols--breakdown',
      expenditureByCategory.map(function (row) { return [row.key, filters.formatNaira(row.total)]; }),
      'No expenses in this range.'));
  }

  // Tabs 

  function renderTabs() {
    clear(tabsNav);
    TABS.forEach(function (tab) {
      var active = activeTab === tab.id;
      var btn = el('button', {
        type: 'button',
        className: 'ght-chart-view-link' + (active ? ' ght-chart-view-link--active' : '')
      }, [tab.label]);
      btn.setAttribute('aria-pressed', active ? 'true' : 'false');
      btn.onclick = function () { activeTab = tab.id; render(); };
      tabsNav.appendChild(btn);
    });
  }

  function render() {
    renderTabs();
    var tab = TABS.filter(function (t) { return t.id === activeTab; })[0];
    (tab || TABS[0]).render();
  }

  store.subscribe(render);
  render();
})();
