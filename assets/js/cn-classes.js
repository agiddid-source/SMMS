(function () {
  'use strict';

  var el = window.CnDom.el;
  var clear = window.CnDom.clear;
  var store = window.CnStore;
  var filters = window.CnFilters;

  var view = { search: '', section: '' };

  var listNode = document.getElementById('cn-class-list');
  var sectionNav = document.getElementById('cn-section-nav');
  var searchInput = document.getElementById('cn-class-search');
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

  var toolbar = document.getElementById('cn-class-toolbar');
  var orderBar = document.getElementById('cn-order-bar');
  var orderToggle = document.getElementById('cn-order-toggle');
  var orderLive = document.getElementById('cn-order-live');
  var ordering = false;
  var dragging = null;     
  var pendingFocus = null; 

  // Rendering 

  function renderSectionNav() {
    var sections = filters.distinctSections(store.getClasses(), store.getSectionOrder());
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
    return el('span', { className: 'cn-list-row-actions' }, [
      el('button', {
        type: 'button', className: 'cn-btn-text',
        onclick: function () { openEdit(cls); }
      }, ['Edit']),
      el('button', {
        type: 'button', className: 'cn-btn-danger-text',
        onclick: function () { askDelete(cls); }
      }, ['Delete'])
    ]);
  }

  function renderList() {
    var visible = filters.filterClasses(
      store.getClasses(), view.search, view.section, false
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

    filters.groupBySectionNewestFirst(visible, store.getSectionOrder()).forEach(function (entry) {
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
          className: 'cn-list-row',
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
    toolbar.hidden = ordering;
    orderBar.hidden = !ordering;
    orderToggle.classList.toggle('is-active', ordering);
    orderToggle.setAttribute('aria-pressed', ordering ? 'true' : 'false');
    orderToggle.disabled = !ordering && !store.getClasses().length;
    renderSectionNav();
    renderDatalist();
    if (ordering) renderOrderList(); else renderList();
    renderSummary();
  }

  // Order mode — rearrange sections and classes 

  function announce(message) { orderLive.textContent = message; }

  function grip() {
    var node = el('span', { className: 'cn-grip' });
    node.setAttribute('aria-hidden', 'true');
    return node;
  }

  function moveButton(kind, key, label, dir, disabled) {
    var button = el('button', {
      type: 'button', className: 'cn-move-btn',
      disabled: disabled,
      dataset: { cnMove: dir < 0 ? 'up' : 'down' },
      onclick: function () { shift(kind, key, label, dir); }
    }, [dir < 0 ? '\u2191' : '\u2193']);
    button.setAttribute('aria-label', 'Move ' + label + (dir < 0 ? ' up' : ' down'));
    button.title = 'Move ' + (dir < 0 ? 'up' : 'down');
    return button;
  }

  function renderOrderList() {
    clear(listNode);
    var groups = filters.groupBySectionNewestFirst(store.getClasses(), store.getSectionOrder());
    var wrap = el('div', { className: 'cn-list-wrap is-ordering' });

    groups.forEach(function (entry, gi) {
      var section = entry[0];
      var classes = entry[1];
      wrap.appendChild(el('div', {
        className: 'cn-list-section-row cn-order-section',
        draggable: true,
        dataset: { cnOrderSection: section }
      }, [
        el('span', { className: 'cn-order-name' }, [grip(), section]),
        el('span', { className: 'cn-list-section-count' }, [
          classes.length + (classes.length === 1 ? ' class' : ' classes')
        ]),
        el('span', { className: 'cn-list-row-actions' }, [
          moveButton('section', section, section + ' section', -1, gi === 0),
          moveButton('section', section, section + ' section', 1, gi === groups.length - 1)
        ])
      ]));
      classes.forEach(function (cls, ci) {
        wrap.appendChild(el('div', {
          className: 'cn-list-row cn-order-row',
          draggable: true,
          dataset: { cnId: cls.id }
        }, [
          el('span', { className: 'cn-list-row-name cn-order-name' }, [grip(), cls.name]),
          el('span', { className: 'cn-list-row-actions' }, [
            moveButton('class', cls.id, cls.name, -1, ci === 0),
            moveButton('class', cls.id, cls.name, 1, ci === classes.length - 1)
          ])
        ]));
      });
    });

    listNode.appendChild(wrap);
    wireDrag(wrap);
    restoreFocus(wrap);
  }

  function restoreFocus(wrap) {
    if (!pendingFocus) return;
    var selector = pendingFocus.kind === 'class'
      ? '[data-cn-id="' + pendingFocus.key + '"]'
      : '[data-cn-order-section="' + pendingFocus.key + '"]';
    var row = wrap.querySelector(selector);
    pendingFocus = null;
    if (!row) return;
    var same = row.querySelector('[data-cn-move="' + (pendingFocusDir < 0 ? 'up' : 'down') + '"]');
    var other = row.querySelector('[data-cn-move="' + (pendingFocusDir < 0 ? 'down' : 'up') + '"]');
    var target = same && !same.disabled ? same : other;
    if (target && !target.disabled) target.focus();
  }
  var pendingFocusDir = 0;

  function shift(kind, key, label, dir) {
    pendingFocus = { kind: kind, key: key };
    pendingFocusDir = dir;
    var moved = kind === 'section' ? store.shiftSection(key, dir) : store.shiftClass(key, dir);
    if (moved) announce(label + ' moved ' + (dir < 0 ? 'up' : 'down') + '.');
    else pendingFocus = null;
  }

  function targetInfo(node) {
    if (node.dataset.cnId) return { type: 'class', id: node.dataset.cnId };
    return { type: 'section', name: node.dataset.cnOrderSection };
  }

  function clearIndicators(wrap) {
    Array.prototype.forEach.call(wrap.querySelectorAll('.drop-before, .drop-after, .drop-into'), function (n) {
      n.classList.remove('drop-before', 'drop-after', 'drop-into');
    });
  }

  /** Where a drop on `node` would land: 'before' | 'after' | 'into' (a class dropped on a heading). */
  function dropPlacement(node, event) {
    var t = targetInfo(node);
    if (dragging.type === 'class' && t.type === 'section') return 'into';
    var rect = node.getBoundingClientRect();
    return (event.clientY - rect.top) > rect.height / 2 ? 'after' : 'before';
  }

  function wireDrag(wrap) {
    var rowSelector = '[data-cn-id], [data-cn-order-section]';

    wrap.addEventListener('dragstart', function (event) {
      var row = event.target.closest ? event.target.closest(rowSelector) : null;
      if (!row) return;
      dragging = targetInfo(row);
      event.dataTransfer.effectAllowed = 'move';
      event.dataTransfer.setData('text/plain', dragging.id || dragging.name);
      row.classList.add('is-dragging');
      // Collapse to just the section headings so a section can be dragged a long way.
      if (dragging.type === 'section') setTimeout(function () { wrap.classList.add('is-section-dragging'); }, 0);
    });

    wrap.addEventListener('dragover', function (event) {
      if (!dragging) return;
      var node = event.target.closest ? event.target.closest(rowSelector) : null;
      if (!node) return;
      var t = targetInfo(node);
      if (dragging.type === 'section' && t.type !== 'section') return;
      event.preventDefault();
      event.dataTransfer.dropEffect = 'move';
      clearIndicators(wrap);
      node.classList.add('drop-' + dropPlacement(node, event));
    });

    wrap.addEventListener('dragleave', function (event) {
      if (!wrap.contains(event.relatedTarget)) clearIndicators(wrap);
    });

    wrap.addEventListener('drop', function (event) {
      if (!dragging) return;
      var node = event.target.closest ? event.target.closest(rowSelector) : null;
      if (!node) return;
      var t = targetInfo(node);
      if (dragging.type === 'section' && t.type !== 'section') return;
      event.preventDefault();
      var placement = dropPlacement(node, event);
      var drag = dragging;
      endDrag(wrap);
      applyDrop(drag, t, placement === 'after');
    });

    wrap.addEventListener('dragend', function () { endDrag(wrap); });
  }

  function endDrag(wrap) {
    dragging = null;
    clearIndicators(wrap);
    wrap.classList.remove('is-section-dragging');
    Array.prototype.forEach.call(wrap.querySelectorAll('.is-dragging'), function (n) { n.classList.remove('is-dragging'); });
  }

  function classById(id) {
    return store.getClasses().filter(function (c) { return c.id === id; })[0];
  }

  function applyDrop(drag, target, after) {
    if (drag.type === 'section') {
      if (store.moveSection(drag.name, target.name, after)) announce(drag.name + ' section moved.');
      return;
    }
    var before = classById(drag.id);
    var moved = target.type === 'section'
      ? store.moveClassToSection(drag.id, target.name)
      : store.moveClass(drag.id, target.id, after);
    if (!moved) return;
    announce(moved.name + ' moved.');
    if (before && before.section !== moved.section) {
      showToast('Moving class', 'Class moved', moved.name + ' \u2192 ' + moved.section);
    }
  }

  function setOrdering(on) {
    ordering = on;
    if (on) {
      view.search = '';
      view.section = '';
      searchInput.value = '';
    }
    announce(on ? 'Order mode on.' : 'Order mode off.');
    render();
    if (!on) orderToggle.focus();
  }

  // Actions 

  function resetFilters() {
    view.search = '';
    view.section = '';
    searchInput.value = '';
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

  function askDelete(cls) {
    confirm.ask({
      title: 'Delete class',
      body: 'Delete ' + cls.name + '? This removes it permanently and unassigns it from any fees it was attached to.',
      confirmLabel: 'Delete class',
      onConfirm: function () {
        store.deleteClass(cls.id);
        showToast('Deleting class', 'Class deleted', cls.name);
      }
    });
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

  window.CnSearch.bind(searchInput, function (value) {
    view.search = value;
    renderList();
  });

  document.getElementById('cn-add-class').addEventListener('click', openAdd);
  orderToggle.addEventListener('click', function () { setOrdering(!ordering); });
  document.getElementById('cn-order-done').addEventListener('click', function () { setOrdering(false); });
  document.getElementById('cn-order-reset').addEventListener('click', function () {
    confirm.ask({
      title: 'Reset order',
      body: 'Put sections back in curriculum order (Toddler \u2192 Secondary) and classes back in their original order? Classes you moved to another section stay there.',
      confirmLabel: 'Reset order',
      onConfirm: function () {
        store.resetOrder();
        announce('Order reset.');
      }
    });
  });

  store.subscribe(render);
  render();
})();
