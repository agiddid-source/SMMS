(function (global) {
  'use strict';

  var uid = 0;
  var instances = [];

  // Starter phrases so the fields help from the very first record.
  var CATALOG = {
    descriptions: [
      'Generator servicing', 'Diesel purchase for generator', 'Electricity bill', 'Water supply',
      'Internet subscription', 'Staff salaries', 'Security services', 'Cleaning supplies',
      'Stationery and office supplies', 'Printing and photocopying', 'Textbooks purchase',
      'Classroom repairs', 'School bus fuel', 'School bus maintenance', 'Examination materials',
      'Sports equipment', 'Fumigation services', 'Borehole maintenance', 'Computer lab repairs',
      'Prize-giving day expenses'
    ],
    payees: [
      'Ikeja Electric', 'MTN Nigeria', 'Airtel Nigeria', 'Staff Payroll',
      'Local hardware supplier', 'Stationery supplier', 'Generator technician', 'Security company'
    ],
    notes: [
      'Paid in full', 'Part payment \u2014 balance to follow', 'Receipt to follow',
      'Invoice pending from vendor', 'Approved by the Proprietor', 'Approved by the Head Teacher',
      'Paid from petty cash', 'Recurring monthly expense'
    ],
    categories: [
      'Insurance', 'Security', 'Cleaning', 'Internet & Data', 'Fuel', 'Uniforms', 'Printing',
      'Repairs', 'Training', 'Medical', 'Feeding', 'Library', 'Sports', 'Furniture',
      'Legal & Professional Fees', 'Bank Charges'
    ]
  };

  function escapeRe(text) {
    return text.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
  }

  function splitWords(query) {
    return String(query || '').toLowerCase().split(/\s+/).filter(Boolean);
  }

  /** 0 = starts with the query, 1 = a word starts with it, 2 = contains it, -1 = no match. */
  function rank(text, query, words) {
    var lower = text.toLowerCase();
    for (var i = 0; i < words.length; i++) {
      if (lower.indexOf(words[i]) === -1) return -1;
    }
    if (!words.length || lower.indexOf(query) === 0) return 0;
    if (new RegExp('(^|[^a-z0-9])' + escapeRe(words[0])).test(lower)) return 1;
    return 2;
  }

  function match(items, query, limit) {
    var q = String(query || '').trim().toLowerCase();
    var words = splitWords(q);
    var seen = {};
    var scored = [];
    items.forEach(function (raw, index) {
      var item = typeof raw === 'string' ? { value: raw } : raw;
      if (!item || !item.value) return;
      var key = item.value.toLowerCase();
      if (seen[key]) return;
      seen[key] = true;
      if (key === q) return; 
      var score = rank(item.value, q, words);
      if (score === -1) return;
      scored.push({ item: item, score: score, index: index });
    });
    scored.sort(function (a, b) { return (a.score - b.score) || (a.index - b.index); });
    return scored.slice(0, limit).map(function (s) { return s.item; });
  }

  /** Builds a source() from groups: [{ hint: 'Payee', values: [...] }, …] (values may be a function). */
  function fromGroups(groups) {
    return function () {
      var out = [];
      groups.forEach(function (group) {
        var values = typeof group.values === 'function' ? group.values() : group.values;
        (values || []).forEach(function (value) { out.push({ value: value, hint: group.hint }); });
      });
      return out;
    };
  }

  function highlight(node, text, words) {
    if (!words.length) {
      node.appendChild(document.createTextNode(text));
      return;
    }
    var re = new RegExp('(' + words.slice().sort(function (a, b) { return b.length - a.length; })
      .map(escapeRe).join('|') + ')', 'gi');
    text.split(re).forEach(function (part, i) {
      if (!part) return;
      if (i % 2 === 1) {
        var mark = document.createElement('mark');
        mark.textContent = part;
        node.appendChild(mark);
      } else {
        node.appendChild(document.createTextNode(part));
      }
    });
  }

  function attach(input, options) {
    options = options || {};
    // Drop instances whose input has been removed from the page (e.g. a re-rendered filter row).
    instances = instances.filter(function (inst) {
      if (document.body.contains(inst.input)) return true;
      inst.destroy();
      return false;
    });

    var limit = options.limit || 6;
    var minChars = options.minChars == null ? 1 : options.minChars;
    var multiline = input.tagName === 'TEXTAREA';
    var listId = 'cn-suggest-' + (++uid);

    var list = document.createElement('ul');
    list.className = 'cn-suggest';
    list.id = listId;
    list.setAttribute('role', 'listbox');
    list.hidden = true;

    function mount() {
      var host = input.parentNode;
      if (!host) return false;
      if (list.parentNode !== host || list.previousSibling !== input) {
        if (window.getComputedStyle(host).position === 'static') host.style.position = 'relative';
        host.insertBefore(list, input.nextSibling);
      }
      return true;
    }

    input.setAttribute('autocomplete', 'off');
    input.setAttribute('role', 'combobox');
    input.setAttribute('aria-autocomplete', 'list');
    input.setAttribute('aria-expanded', 'false');
    input.setAttribute('aria-controls', listId);

    var shown = [];
    var active = -1;
    var isOpen = false;
    var suppress = false;

    function target() {
      var value = input.value;
      if (!multiline) return { start: 0, end: value.length, text: value };
      var caret = input.selectionStart == null ? value.length : input.selectionStart;
      var start = caret ? value.lastIndexOf('\n', caret - 1) + 1 : 0;
      var end = value.indexOf('\n', caret);
      if (end === -1) end = value.length;
      return { start: start, end: end, text: value.slice(start, caret) };
    }

    function position() {
      if (!document.body.contains(input)) { close(); return; }
      list.style.left = input.offsetLeft + 'px';
      list.style.width = Math.max(input.offsetWidth, 220) + 'px';
      // Decide above/below from on-screen space; place using the field's own offsets.
      var rect = input.getBoundingClientRect();
      var room = window.innerHeight - rect.bottom;
      var flip = room < list.getBoundingClientRect().height + 12 && rect.top > room;
      list.style.top = flip
        ? (input.offsetTop - list.offsetHeight - 4) + 'px'
        : (input.offsetTop + input.offsetHeight + 4) + 'px';
    }

    function setActive(index) {
      active = index;
      Array.prototype.forEach.call(list.children, function (node, i) {
        var on = i === index;
        node.classList.toggle('is-active', on);
        node.setAttribute('aria-selected', on ? 'true' : 'false');
        if (on && node.scrollIntoView) node.scrollIntoView({ block: 'nearest' });
      });
      if (index >= 0) input.setAttribute('aria-activedescendant', listId + '-' + index);
      else input.removeAttribute('aria-activedescendant');
    }

    function close() {
      isOpen = false;
      active = -1;
      list.hidden = true;
      input.setAttribute('aria-expanded', 'false');
      input.removeAttribute('aria-activedescendant');
    }

    function render(query) {
      var words = splitWords(query);
      while (list.firstChild) list.removeChild(list.firstChild);
      shown.forEach(function (item, i) {
        var row = document.createElement('li');
        row.className = 'cn-suggest-item';
        row.id = listId + '-' + i;
        row.setAttribute('role', 'option');
        row.setAttribute('aria-selected', 'false');
        var text = document.createElement('span');
        text.className = 'cn-suggest-text';
        highlight(text, item.value, words);
        row.appendChild(text);
        if (item.hint) {
          var hint = document.createElement('span');
          hint.className = 'cn-suggest-hint';
          hint.textContent = item.hint;
          row.appendChild(hint);
        }
        row.addEventListener('mousedown', function (event) {
          event.preventDefault();
          pick(i);
        });
        row.addEventListener('mousemove', function () { if (active !== i) setActive(i); });
        list.appendChild(row);
      });
    }

    function open(force) {
      var info = target();
      var query = info.text.trim();
      if (!force && query.length < minChars) { close(); return; }
      shown = match(options.source(query) || [], query, limit);
      if (!shown.length || !mount()) { close(); return; }
      render(query);
      list.hidden = false;
      isOpen = true;
      input.setAttribute('aria-expanded', 'true');
      setActive(-1);
      position();
    }

    function pick(index) {
      var item = shown[index];
      if (!item) return;
      var info = target();
      var value = input.value;
      if (multiline) {
        input.value = value.slice(0, info.start) + item.value + value.slice(info.end);
        var caret = info.start + item.value.length;
        input.setSelectionRange(caret, caret);
      } else {
        input.value = item.value;
      }
      close();
      // Let page code (live search, validation) react exactly as if the value had been typed.
      suppress = true;
      input.dispatchEvent(new Event('input', { bubbles: true }));
      suppress = false;
      input.dispatchEvent(new Event('cn-pick', { bubbles: true }));
      if (options.onPick) options.onPick(item);
    }

    function onInput() { if (!suppress) open(false); }

    function onKeydown(event) {
      var key = event.key;
      if (key === 'ArrowDown' || key === 'ArrowUp') {
        if (!isOpen) {
          if (multiline || key === 'ArrowUp') return; 
          open(true);
          if (isOpen) { event.preventDefault(); setActive(0); }
          return;
        }
        event.preventDefault();
        var next = key === 'ArrowDown' ? active + 1 : active - 1;
        if (next >= shown.length) next = 0;
        if (next < 0) next = shown.length - 1;
        setActive(next);
      } else if (key === 'Enter') {
        if (isOpen && active >= 0) {
          event.preventDefault();
          event.stopImmediatePropagation(); 
          pick(active);
        } else if (isOpen) {
          close();
        }
      } else if (key === 'Escape') {
        if (isOpen) {
          event.preventDefault();
          event.stopImmediatePropagation(); 
          close();
        }
      } else if (key === 'Tab') {
        close();
      }
    }

    function onReflow() { if (isOpen) position(); }

    input.addEventListener('input', onInput);
    input.addEventListener('keydown', onKeydown);
    input.addEventListener('blur', close);
    window.addEventListener('resize', onReflow);

    function destroy() {
      input.removeEventListener('input', onInput);
      input.removeEventListener('keydown', onKeydown);
      input.removeEventListener('blur', close);
      window.removeEventListener('resize', onReflow);
      if (list.parentNode) list.parentNode.removeChild(list);
    }

    var instance = { input: input, close: close, destroy: destroy };
    instances.push(instance);
    return instance;
  }

  global.CnSuggest = {
    attach: attach,
    fromGroups: fromGroups,
    match: match,
    catalog: CATALOG
  };
})(window);
