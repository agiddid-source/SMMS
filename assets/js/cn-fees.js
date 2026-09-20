(function () {
  'use strict';

  var el = window.CnDom.el;
  var clear = window.CnDom.clear;
  var store = window.CnStore;
  var filters = window.CnFilters;

  var view = { search: '', type: '', term: '', includeInactive: false };
  var pickerSearch = '';
  var selectedClassIds = [];

  var listNode = document.getElementById('cn-fee-list');
  var searchInput = document.getElementById('cn-fee-search');
  var typeSelect = document.getElementById('cn-fee-type-filter');
  var termSelect = document.getElementById('cn-fee-term-filter');
  var inactiveToggle = document.getElementById('cn-show-inactive');
  var summaryNode = document.getElementById('cn-fee-summary');

  var formModal = new window.CnModal('cn-fee-form-modal');
  var typesModal = new window.CnModal('cn-fee-types-modal');
  var confirm = new window.CnConfirm('cn-confirm-modal');

  var form = document.getElementById('cn-fee-form');
  var formTitle = document.getElementById('cn-fee-form-title');
  var formSubmit = document.getElementById('cn-fee-form-submit');
  var typeField = form.elements.type;
  var pickerNode = document.getElementById('cn-class-picker');
  var pickerSearchInput = document.getElementById('cn-picker-search');
  var pickerCount = document.getElementById('cn-picker-count');
  var editingId = null;

  var typesList = document.getElementById('cn-fee-types-list');
  var typesForm = document.getElementById('cn-fee-type-form');

  // ---- Fee list -----------------------------------------------------

  function feeRow(fee) {
    var classes = store.getClasses();
    var names = filters.resolveClassNames(fee.assignedClasses, classes);
    var inactive = fee.status === 'inactive';

    return el('div', {
      className: 'cn-fee-list-row' + (inactive ? ' is-inactive' : ''),
      dataset: { cnId: fee.id }
    }, [
      el('span', {}, [
        el('span', { className: 'cn-fee-name' }, [fee.name]),
        el('span', { className: 'cn-fee-meta' }, [
          fee.type + (inactive ? ' \u00B7 Inactive' : '')
        ])
      ]),
      el('span', { className: 'cn-fee-amount' }, [filters.formatNaira(fee.amount)]),
      el('span', {}, [fee.academicSession + ' \u00B7 ' + fee.term]),
      el('span', {}, [filters.formatDate(fee.dueDate)]),
      el('span', {
        title: names.length ? names.join(', ') : 'None assigned'
      }, [filters.formatClassNames(names)]),
      inactive
        ? el('span', { className: 'cn-list-row-actions' }, [
            el('button', {
              type: 'button', className: 'cn-btn-text',
              onclick: function () {
                store.reactivateFee(fee.id);
                showToast('Reactivating fee', 'Fee reactivated', fee.name);
              }
            }, ['Reactivate'])
          ])
        : el('span', { className: 'cn-list-row-actions' }, [
            el('button', {
              type: 'button', className: 'cn-btn-text',
              onclick: function () { openEdit(fee); }
            }, ['Edit']),
            el('button', {
              type: 'button', className: 'cn-btn-danger-text',
              onclick: function () { askDeactivate(fee); }
            }, ['Deactivate'])
          ])
    ]);
  }

  function renderList() {
    var visible = filters.filterFees(
      store.getFees(), view.search, view.type, view.term, view.includeInactive
    );
    clear(listNode);

    if (!visible.length) {
      var filtering = view.search || view.type || view.term;
      listNode.appendChild(el('div', { className: 'ght-card cn-empty' }, [
        el('p', { className: 'cn-empty-title' }, ['No fees to show']),
        el('p', { className: 'cn-empty-body' }, [
          filtering
            ? 'Nothing matches the current search or filters.'
            : 'Create a fee, assign it to classes, and it will appear here.'
        ]),
        filtering
          ? el('button', {
              type: 'button', className: 'cn-btn-text',
              onclick: resetFilters
            }, ['Clear filters'])
          : el('button', {
              type: 'button', className: 'ght-button ght-button--primary text-sm font-medium',
              onclick: function () { openAdd(); }
            }, [el('span', { className: 'ght-button-label' }, ['Add fee'])])
      ]));
      return;
    }

    var wrap = el('div', { className: 'cn-fee-list-wrap' }, [
      el('div', { className: 'cn-fee-list-header' }, [
        el('span', {}, ['Fee']),
        el('span', {}, ['Amount']),
        el('span', {}, ['Session / term']),
        el('span', {}, ['Due']),
        el('span', {}, ['Applicable classes']),
        el('span', {}, [])
      ])
    ]);
    visible.forEach(function (fee) { wrap.appendChild(feeRow(fee)); });
    listNode.appendChild(wrap);
  }

  function renderSummary() {
    var active = store.getFees().filter(function (f) { return f.status === 'active'; });
    var total = active.reduce(function (sum, fee) { return sum + Number(fee.amount || 0); }, 0);
    summaryNode.textContent = active.length + (active.length === 1 ? ' active fee' : ' active fees')
      + ' \u00B7 ' + filters.formatNaira(total) + ' billable per assigned class';
  }

  function renderFilters() {
    var fees = store.getFees();
    fillSelect(typeSelect, 'All types', store.getFeeTypes().map(function (t) { return t.name; }), view.type);
    fillSelect(termSelect, 'All terms', filters.distinctTerms(fees), view.term);
  }

  function fillSelect(select, allLabel, values, current) {
    clear(select);
    select.appendChild(el('option', { value: '' }, [allLabel]));
    values.forEach(function (value) {
      select.appendChild(el('option', { value: value, selected: value === current }, [value]));
    });
    select.value = current;
  }

  function render() {
    renderFilters();
    renderList();
    renderSummary();
  }

  function resetFilters() {
    view.search = '';
    view.type = '';
    view.term = '';
    view.includeInactive = false;
    searchInput.value = '';
    inactiveToggle.checked = false;
    render();
  }

  // ---- Fee form -----------------------------------------------------

  function renderTypeOptions(current) {
    clear(typeField);
    store.getFeeTypes().forEach(function (type) {
      typeField.appendChild(el('option', { value: type.name }, [type.name]));
    });
    if (current) typeField.value = current;
  }

  function renderPicker() {
    var active = filters.filterClasses(store.getClasses(), pickerSearch, '', false);
    clear(pickerNode);

    if (!active.length) {
      pickerNode.appendChild(el('p', { className: 'cn-picker-empty' }, ['No classes match that search.']));
    }

    filters.groupBySection(active).forEach(function (entry) {
      var section = entry[0];
      var classes = entry[1];
      var ids = classes.map(function (c) { return c.id; });

      var sectionButton = el('button', {
        type: 'button', className: 'cn-btn-text cn-btn-text--small',
        onclick: function () { toggleSection(ids, sectionButton); }
      }, [sectionAllSelected(ids) ? 'Clear section' : 'Select section']);

      pickerNode.appendChild(el('div', { className: 'cn-picker-section' }, [
        el('span', { className: 'cn-picker-section-name' }, [section]),
        sectionButton
      ]));

      classes.forEach(function (cls) {
        var box = el('input', {
          type: 'checkbox', value: cls.id, checked: selectedClassIds.indexOf(cls.id) !== -1,
          onchange: function () { toggleClass(cls.id, box, ids, sectionButton); }
        });
        pickerNode.appendChild(el('label', { className: 'cn-picker-row' }, [box, cls.name]));
      });
    });

    updatePickerCount();
  }

  function sectionAllSelected(ids) {
    return ids.every(function (id) { return selectedClassIds.indexOf(id) !== -1; });
  }

  function updatePickerCount() {
    pickerCount.textContent = selectedClassIds.length
      ? selectedClassIds.length + (selectedClassIds.length === 1 ? ' class selected' : ' classes selected')
      : 'No classes selected';
  }

  function toggleClass(classId, box, sectionIds, sectionButton) {
    if (box.checked) {
      if (selectedClassIds.indexOf(classId) === -1) selectedClassIds.push(classId);
    } else {
      selectedClassIds = selectedClassIds.filter(function (id) { return id !== classId; });
    }
    sectionButton.textContent = sectionAllSelected(sectionIds) ? 'Clear section' : 'Select section';
    updatePickerCount();
  }

  function toggleSection(ids, sectionButton) {
    var willSelect = !sectionAllSelected(ids);
    ids.forEach(function (id) {
      if (willSelect) {
        if (selectedClassIds.indexOf(id) === -1) selectedClassIds.push(id);
      } else {
        selectedClassIds = selectedClassIds.filter(function (existing) { return existing !== id; });
      }
    });
    pickerNode.querySelectorAll('input[type="checkbox"]').forEach(function (box) {
      if (ids.indexOf(box.value) !== -1) box.checked = willSelect;
    });
    sectionButton.textContent = willSelect ? 'Clear section' : 'Select section';
    updatePickerCount();
  }

  function openAdd() {
    editingId = null;
    form.reset();
    formTitle.textContent = 'Add fee';
    formSubmit.querySelector('.ght-button-label').textContent = 'Add fee';
    renderTypeOptions();
    form.elements.academicSession.value = form.elements.academicSession.dataset.cnDefault || '';
    selectedClassIds = [];
    pickerSearch = '';
    pickerSearchInput.value = '';
    renderPicker();
    formModal.open();
  }

  function openEdit(fee) {
    editingId = fee.id;
    formTitle.textContent = 'Edit fee';
    formSubmit.querySelector('.ght-button-label').textContent = 'Save changes';
    renderTypeOptions(fee.type);
    form.elements.name.value = fee.name;
    form.elements.amount.value = fee.amount;
    form.elements.academicSession.value = fee.academicSession;
    form.elements.term.value = fee.term;
    form.elements.dueDate.value = fee.dueDate || '';
    form.elements.description.value = fee.description || '';
    selectedClassIds = (fee.assignedClasses || []).slice();
    pickerSearch = '';
    pickerSearchInput.value = '';
    renderPicker();
    formModal.open();
  }

  function askDeactivate(fee) {
    confirm.ask({
      title: 'Deactivate fee',
      body: 'Deactivate ' + fee.name + '? It stops applying to new student accounts, and existing records keep it.',
      confirmLabel: 'Deactivate fee',
      onConfirm: function () {
        store.deactivateFee(fee.id);
        showToast('Deactivating fee', 'Fee deactivated', fee.name);
      }
    });
  }

  form.addEventListener('submit', function (event) {
    event.preventDefault();
    var payload = {
      name: form.elements.name.value.trim(),
      type: form.elements.type.value,
      amount: Number(form.elements.amount.value),
      academicSession: form.elements.academicSession.value.trim(),
      term: form.elements.term.value,
      dueDate: form.elements.dueDate.value,
      description: form.elements.description.value.trim(),
      assignedClasses: selectedClassIds.slice()
    };
    if (!payload.name || !payload.type || !(payload.amount > 0) || !payload.academicSession) return;

    if (editingId) {
      store.updateFee(editingId, payload);
      showToast('Saving fee', 'Fee updated', payload.name);
    } else {
      store.addFee(payload);
      showToast('Adding fee', 'Fee added', payload.name);
    }
    formModal.close();
  });

  // Fee types 

  function renderTypes() {
    clear(typesList);
    store.getFeeTypes().forEach(function (type) {
      var inUse = store.getFees().filter(function (fee) {
        return fee.type === type.name && fee.status === 'active';
      }).length;
      typesList.appendChild(el('li', { className: 'cn-type-row' }, [
        el('span', {}, [type.name]),
        el('span', { className: 'cn-type-count' }, [
          inUse ? inUse + (inUse === 1 ? ' fee' : ' fees') : 'Unused'
        ])
      ]));
    });
  }

  typesForm.addEventListener('submit', function (event) {
    event.preventDefault();
    var name = typesForm.elements.name.value.trim();
    if (!name) return;
    var before = store.getFeeTypes().length;
    store.addFeeType(name);
    typesForm.reset();
    typesForm.elements.name.focus();
    if (store.getFeeTypes().length > before) {
      showToast('Adding fee type', 'Fee type added', name);
    }
  });

  // Wiring 

  searchInput.addEventListener('input', function () {
    view.search = searchInput.value;
    renderList();
  });
  typeSelect.addEventListener('change', function () {
    view.type = typeSelect.value;
    renderList();
  });
  termSelect.addEventListener('change', function () {
    view.term = termSelect.value;
    renderList();
  });
  inactiveToggle.addEventListener('change', function () {
    view.includeInactive = inactiveToggle.checked;
    renderList();
  });
  pickerSearchInput.addEventListener('input', function () {
    pickerSearch = pickerSearchInput.value;
    renderPicker();
  });

  document.getElementById('cn-add-fee').addEventListener('click', openAdd);
  document.getElementById('cn-manage-types').addEventListener('click', function () {
    renderTypes();
    typesModal.open();
  });

  store.subscribe(function () {
    render();
    if (typesModal.isOpen()) renderTypes();
    if (formModal.isOpen()) renderPicker();
  });
  render();
})();
