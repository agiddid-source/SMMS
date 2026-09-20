(function () {
  'use strict';

  var el = window.CnDom.el;
  var clear = window.CnDom.clear;
  var store = window.CnStore;
  var filters = window.CnFilters;

  var view = { search: '', section: '', includeArchived: false };

  var listNode = document.getElementById('cn-class-list');
  var sectionNav = document.getElementById('cn-section-nav');
  var searchInput = document.getElementById('cn-class-search');
  var archivedToggle = document.getElementById('cn-show-archived');
  var summaryNode = document.getElementById('cn-class-summary');
  var sectionDatalist = document.getElementById('cn-section-datalist');

  var formModal = new window.CnModal('cn-class-form-modal');
  var confirm = new window.CnConfirm('cn-confirm-modal');
  var form = document.getElementById('cn-class-form');
  var formTitle = document.getElementById('cn-class-form-title');
  var formSubmit = document.getElementById('cn-class-form-submit');
  var nameField = form.elements.name;
  var sectionField = form.elements.section;
  var editingId = null;

  // Rendering 

  function renderSectionNav() {
    var sections = filters.distinctSections(store.getClasses());
    clear(sectionNav);
    sectionNav.appendChild(pill('All sections', ''));
    sections.forEach(function (section) {
      sectionNav.appendChild(pill(section, section));
    });
  }

  function pill(label, value) {
    var active = view.section === value;
    return el('button', {
      type: 'button',
      className: 'ght-chart-view-link' + (active ? ' ght-chart-view-link--active' : ''),
      'aria-pressed': active ? 'true' : 'false',
      dataset: { cnSection: value },
      onclick: function () {
        view.section = value;
        render();
      }
    }, [label]);
  }

  function renderDatalist() {
    clear(sectionDatalist);
    filters.distinctSections(store.getClasses()).forEach(function (section) {
      sectionDatalist.appendChild(el('option', { value: section }));
    });
  }

  function statusChip(cls) {
    var archived = cls.status === 'archived';
    return el('span', {
      className: 'ght-chip ' + (archived ? 'ght-chip--neutral' : 'ght-chip--success')
    }, [archived ? 'Archived' : 'Active']);
  }

  function rowActions(cls) {
    if (cls.status === 'archived') {
      return el('span', { className: 'cn-list-row-actions' }, [
        el('button', {
          type: 'button', className: 'cn-btn-text',
          onclick: function () { restoreClass(cls); }
        }, ['Restore'])
      ]);
    }
    return el('span', { className: 'cn-list-row-actions' }, [
      el('button', {
        type: 'button', className: 'cn-btn-text',
        onclick: function () { openEdit(cls); }
      }, ['Edit']),
      el('button', {
        type: 'button', className: 'cn-btn-danger-text',
        onclick: function () { askArchive(cls); }
      }, ['Archive'])
    ]);
  }

  function renderList() {
    var visible = filters.filterClasses(
      store.getClasses(), view.search, view.section, view.includeArchived
    );
    clear(listNode);

    if (!visible.length) {
      listNode.appendChild(el('div', { className: 'ght-card cn-empty' }, [
        el('p', { className: 'cn-empty-title' }, ['No classes here yet']),
        el('p', { className: 'cn-empty-body' }, [
          view.search || view.section
            ? 'Nothing matches the current search or section.'
            : 'Add your first class to start assigning fees to it.'
        ]),
        view.search || view.section
          ? el('button', {
              type: 'button', className: 'cn-btn-text',
              onclick: function () { resetFilters(); }
            }, ['Clear filters'])
          : el('button', {
              type: 'button', className: 'ght-button ght-button--primary text-sm font-medium',
              onclick: function () { openAdd(); }
            }, [el('span', { className: 'ght-button-label' }, ['Add class'])])
      ]));
      return;
    }

    var wrap = el('div', { className: 'cn-list-wrap' }, [
      el('div', { className: 'cn-list-header' }, [
        el('span', {}, ['Class']),
        el('span', {}, ['Status']),
        el('span', {}, [])
      ])
    ]);

    filters.groupBySection(visible).forEach(function (entry) {
      var section = entry[0];
      var classes = entry[1];
      wrap.appendChild(el('div', { className: 'cn-list-section-row' }, [
        el('span', {}, [section]),
        el('span', { className: 'cn-list-section-count' }, [
          classes.length + (classes.length === 1 ? ' class' : ' classes')
        ])
      ]));
      classes.forEach(function (cls) {
        wrap.appendChild(el('div', {
          className: 'cn-list-row' + (cls.status === 'archived' ? ' is-archived' : ''),
          dataset: { cnId: cls.id }
        }, [
          el('span', { className: 'cn-list-row-name' }, [cls.name]),
          el('span', {}, [statusChip(cls)]),
          rowActions(cls)
        ]));
      });
    });

    listNode.appendChild(wrap);
  }

  function renderSummary() {
    var all = store.getClasses();
    var active = all.filter(function (c) { return c.status === 'active'; });
    var sections = filters.distinctSections(active);
    summaryNode.textContent = active.length + (active.length === 1 ? ' active class' : ' active classes')
      + ' across ' + sections.length + (sections.length === 1 ? ' section' : ' sections');
  }

  function render() {
    renderSectionNav();
    renderDatalist();
    renderList();
    renderSummary();
  }

  // Actions 

  function resetFilters() {
    view.search = '';
    view.section = '';
    view.includeArchived = false;
    searchInput.value = '';
    archivedToggle.checked = false;
    render();
  }

  function openAdd() {
    editingId = null;
    formTitle.textContent = 'Add class';
    formSubmit.querySelector('.ght-button-label').textContent = 'Add class';
    form.reset();
    if (view.section) sectionField.value = view.section;
    formModal.open();
  }

  function openEdit(cls) {
    editingId = cls.id;
    formTitle.textContent = 'Edit class';
    formSubmit.querySelector('.ght-button-label').textContent = 'Save changes';
    nameField.value = cls.name;
    sectionField.value = cls.section;
    formModal.open();
  }

  function askArchive(cls) {
    confirm.ask({
      title: 'Archive class',
      body: 'Archive ' + cls.name + '? It stays in the records but can no longer be assigned new fees.',
      confirmLabel: 'Archive class',
      onConfirm: function () {
        store.archiveClass(cls.id);
        showToast('Archiving class', 'Class archived', cls.name);
      }
    });
  }

  function restoreClass(cls) {
    store.restoreClass(cls.id);
    showToast('Restoring class', 'Class restored', cls.name);
  }

  form.addEventListener('submit', function (event) {
    event.preventDefault();
    var payload = {
      name: nameField.value.trim(),
      section: sectionField.value.trim()
    };
    if (!payload.name || !payload.section) return;

    if (editingId) {
      store.updateClass(editingId, payload);
      showToast('Saving class', 'Class updated', payload.name);
    } else {
      store.addClass(payload);
      showToast('Adding class', 'Class added', payload.name);
    }
    formModal.close();
  });

  searchInput.addEventListener('input', function () {
    view.search = searchInput.value;
    renderList();
  });

  archivedToggle.addEventListener('change', function () {
    view.includeArchived = archivedToggle.checked;
    renderList();
  });

  document.getElementById('cn-add-class').addEventListener('click', openAdd);

  store.subscribe(render);
  render();
})();
