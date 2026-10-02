
(function (global) {
  'use strict';

  var SECTION_ORDER = ['Toddler', 'Nursery', 'KG', 'Primary', 'Secondary'];
  var TERM_ORDER = ['First Term', 'Second Term', 'Third Term'];

  // Pure helpers 
  var CnFilters = {
    sectionOrder: SECTION_ORDER,
    maxFiles: 3,
    termOrder: TERM_ORDER,

    filterClasses: function (classes, search, section, includeArchived) {
      var words = String(search || '').trim().toLowerCase().split(/\s+/).filter(Boolean);
      return classes.filter(function (cls) {
        if (!includeArchived && cls.status === 'archived') return false;
        if (section && cls.section !== section) return false;
        if (words.length) {
          var hay = (cls.name + ' ' + cls.section).toLowerCase();
          for (var i = 0; i < words.length; i++) {
            if (hay.indexOf(words[i]) === -1) return false;
          }
        }
        return true;
      });
    },

    groupBySectionNewestFirst: function (classes, order) {
      var stamp = function (c) { return c.addedAt || 0; };
      var groups = this.groupBySection(classes, order).map(function (entry, gi) {
        var indexed = entry[1].map(function (c, i) { return { c: c, i: i }; });
        indexed.sort(function (a, b) { return (stamp(b.c) - stamp(a.c)) || (a.i - b.i); });
        var list = indexed.map(function (x) { return x.c; });
        return { entry: [entry[0], list], newest: Math.max.apply(null, list.map(stamp).concat(0)), gi: gi };
      });
      groups.sort(function (a, b) { return (b.newest - a.newest) || (a.gi - b.gi); });
      return groups.map(function (g) { return g.entry; });
    },

    groupBySection: function (classes, order) {
      var base = order && order.length ? order : SECTION_ORDER;
      var buckets = {};
      classes.forEach(function (cls) {
        (buckets[cls.section] = buckets[cls.section] || []).push(cls);
      });
      var known = base.filter(function (s) { return buckets[s]; });
      var other = Object.keys(buckets).filter(function (s) {
        return base.indexOf(s) === -1;
      }).sort();
      return known.concat(other).map(function (s) { return [s, buckets[s]]; });
    },

    distinctSections: function (classes, order) {
      var base = order && order.length ? order : SECTION_ORDER;
      var seen = classes.map(function (c) { return c.section; });
      var unique = seen.filter(function (s, i) { return seen.indexOf(s) === i; });
      var known = base.filter(function (s) { return unique.indexOf(s) !== -1; });
      var other = unique.filter(function (s) { return base.indexOf(s) === -1; }).sort();
      return known.concat(other);
    },

    filterFees: function (fees, search, type, term, includeInactive) {
      var needle = String(search || '').trim().toLowerCase();
      return fees.filter(function (fee) {
        if (!includeInactive && fee.status === 'inactive') return false;
        if (type && fee.type !== type) return false;
        if (term && CnFilters.feeTerms(fee).indexOf(term) === -1) return false;
        if (needle && fee.name.toLowerCase().indexOf(needle) === -1) return false;
        return true;
      });
    },

    expenseFiles: function (exp, kind) {
      var list = exp[kind + 's'];
      if (Array.isArray(list)) return list.filter(Boolean);
      return exp[kind] ? [exp[kind]] : [];
    },

    feeTerms: function (fee) {
      if (Array.isArray(fee.terms) && fee.terms.length) return fee.terms.slice();
      return fee.term ? [fee.term] : [];
    },

    distinctTerms: function (fees) {
      var all = [];
      fees.forEach(function (f) {
        CnFilters.feeTerms(f).forEach(function (t) { if (all.indexOf(t) === -1) all.push(t); });
      });
      var known = TERM_ORDER.filter(function (t) { return all.indexOf(t) !== -1; });
      var other = all.filter(function (t) { return TERM_ORDER.indexOf(t) === -1; }).sort();
      return known.concat(other);
    },

    formatTerms: function (terms) {
      if (!terms.length) return '\u2014';
      if (terms.length === TERM_ORDER.length) return 'All terms';
      return terms.join(', ');
    },

    resolveClassNames: function (classIds, classes) {
      return (classIds || []).map(function (id) {
        var match = classes.filter(function (c) { return c.id === id; })[0];
        return match ? match.name : null;
      }).filter(Boolean);
    },

    formatClassNames: function (names) {
      if (!names.length) return 'None assigned';
      if (names.length <= 2) return names.join(', ');
      return names.slice(0, 2).join(', ') + ' +' + (names.length - 2) + ' more';
    },

    formatNaira: function (amount) {
      return '\u20A6' + Number(amount || 0).toLocaleString('en-NG', { maximumFractionDigits: 0 });
    },

    /** 2026-09-30 → 30 Sep 2026. Falls back to the raw value if unparseable. */
    formatDate: function (iso) {
      if (!iso) return '\u2014';
      var parts = String(iso).split('-');
      if (parts.length !== 3) return iso;
      var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
      var month = months[Number(parts[1]) - 1];
      return month ? Number(parts[2]) + ' ' + month + ' ' + parts[0] : iso;
    },

    /** True if iso (YYYY-MM-DD) falls within [from, to] — either bound optional. */
    inDateRange: function (iso, from, to) {
      if (!iso) return false;
      if (from && iso < from) return false;
      if (to && iso > to) return false;
      return true;
    },

    filterExpenses: function (expenses, search, categoryId, from, to, includeVoided, categoryName) {
      var words = String(search || '').trim().toLowerCase().split(/\s+/).filter(Boolean);
      var self = this;
      return expenses.filter(function (exp) {
        if (!includeVoided && exp.status === 'voided') return false;
        if (categoryId && exp.categoryId !== categoryId) return false;
        if ((from || to) && !self.inDateRange(exp.expenseDate, from, to)) return false;
        if (words.length) {
          var text = [
            exp.description, exp.payee, categoryName ? categoryName(exp.categoryId) : '',
            exp.notes, exp.paymentMethod,
            self.expenseFiles(exp, 'receipt').join(' '), self.expenseFiles(exp, 'invoice').join(' ')
          ].join(' ').toLowerCase();
          var amounts = (String(exp.amount) + ' ' + self.formatNaira(exp.amount)).toLowerCase();
          for (var i = 0; i < words.length; i++) {
            var w = words[i];
            if (text.indexOf(w) !== -1) continue;
            if (w.length >= 3 && /\d/.test(w) && amounts.indexOf(w) !== -1) continue;
            return false;
          }
        }
        return true;
      });
    },

    /** Expenses recorded in this session lead (newest first); the rest by date, newest first. */
    sortExpenses: function (expenses) {
      return expenses.map(function (e, i) { return { e: e, i: i }; }).sort(function (a, b) {
        var x = a.e.addedAt || 0, y = b.e.addedAt || 0;
        if (x !== y) return y - x;
        if (a.e.expenseDate !== b.e.expenseDate) return a.e.expenseDate < b.e.expenseDate ? 1 : -1;
        return a.i - b.i;
      }).map(function (x) { return x.e; });
    },

    /** extraText(payment) is optional — extra searchable text such as class or fee name. */
    filterPayments: function (payments, search, classId, feeId, from, to, extraText) {
      var words = String(search || '').trim().toLowerCase().split(/\s+/).filter(Boolean);
      var self = this;
      return payments.filter(function (pay) {
        if (classId && pay.classId !== classId) return false;
        if (feeId && pay.feeId !== feeId) return false;
        if ((from || to) && !self.inDateRange(pay.paymentDate, from, to)) return false;
        if (words.length) {
          var hay = [pay.studentName, pay.receiptNumber, pay.paymentMethod, extraText ? extraText(pay) : '']
            .join(' ').toLowerCase();
          for (var i = 0; i < words.length; i++) {
            if (hay.indexOf(words[i]) === -1) return false;
          }
        }
        return true;
      });
    },

    sumAmounts: function (records) {
      return records.reduce(function (sum, r) { return sum + Number(r.amount || 0); }, 0);
    },

    sumByKey: function (records, keyFn) {
      var totals = {};
      records.forEach(function (r) {
        var key = keyFn(r);
        totals[key] = (totals[key] || 0) + Number(r.amount || 0);
      });
      return Object.keys(totals)
        .map(function (key) { return { key: key, total: totals[key] }; })
        .sort(function (a, b) { return b.total - a.total; });
    }
  };

  // Store 

  function readSeed() {
    var node = document.getElementById('cn-seed');
    var empty = { classes: [], feeTypes: [], fees: [], expenseCategories: [], expenses: [], payments: [] };
    if (!node) return empty;
    try {
      var parsed = JSON.parse(node.textContent || '{}');
      return {
        classes: parsed.classes || [],
        feeTypes: parsed.feeTypes || [],
        fees: parsed.fees || [],
        expenseCategories: parsed.expenseCategories || [],
        expenses: parsed.expenses || [],
        payments: parsed.payments || []
      };
    } catch (err) {
      console.error('[cn-store] seed is not valid JSON', err);
      return empty;
    }
  }

  function nextId(prefix, records) {
    var highest = records.reduce(function (max, record) {
      var n = parseInt(String(record.id).split('-')[1], 10);
      return isNaN(n) ? max : Math.max(max, n);
    }, 0);
    return prefix + '-' + String(highest + 1).padStart(3, '0');
  }

  /** Keeps `terms` (source of truth) and the legacy `term` string in step. */
  function withTerms(fee) {
    var terms = CnFilters.feeTerms(fee);
    return Object.assign({}, fee, { terms: terms, term: terms.join(', ') });
  }

  var MAX_FILES = 3;
  function withFiles(exp) {
    var receipts = CnFilters.expenseFiles(exp, 'receipt').slice(0, MAX_FILES);
    var invoices = CnFilters.expenseFiles(exp, 'invoice').slice(0, MAX_FILES);
    return Object.assign({}, exp, {
      receipts: receipts, invoices: invoices,
      receipt: receipts[0] || null, invoice: invoices[0] || null
    });
  }

  var classSequence = 0;
  var classRank = {};   
  var expenseSequence = 0;

  function createStore(seed) {
    seed.classes.forEach(function (c, i) { classRank[c.id] = i; });
    var state = {
      classes: seed.classes.slice(),
      feeTypes: seed.feeTypes.slice(),
      fees: seed.fees.map(withTerms),
      deletedClassIds: [],
      sectionOrder: null,
      expenseCategories: seed.expenseCategories.slice(),
      expenses: seed.expenses.map(withFiles),
      payments: seed.payments.slice()
    };
    var listeners = [];

    function normalizeOrder() {
      var groups = CnFilters.groupBySectionNewestFirst(state.classes, state.sectionOrder);
      state.sectionOrder = groups.map(function (g) { return g[0]; });
      var flat = [];
      groups.forEach(function (g) {
        g[1].forEach(function (c) {
          var n = Object.assign({}, c);
          delete n.addedAt;
          flat.push(n);
        });
      });
      state.classes = flat;
    }

    function notify() {
      listeners.forEach(function (fn) { fn(state); });
    }

    function findIndex(collection, id) {
      for (var i = 0; i < collection.length; i++) {
        if (collection[i].id === id) return i;
      }
      return -1;
    }

    return {

      getClasses: function () { return state.classes.map(function (c) { return Object.assign({}, c); }); },
      getFeeTypes: function () { return state.feeTypes.map(function (t) { return Object.assign({}, t); }); },
      getFees: function () { return state.fees.map(function (f) { return Object.assign({}, f); }); },
      getExpenseCategories: function () { return state.expenseCategories.map(function (c) { return Object.assign({}, c); }); },
      getExpenses: function () { return state.expenses.map(function (e) { return Object.assign({}, e); }); },
      /** Read-only from this side — Bursar & Payments owns writing these
       *  for real; Reports only aggregates. */
      getPayments: function () { return state.payments.map(function (p) { return Object.assign({}, p); }); },

      getClass: function (id) {
        var i = findIndex(state.classes, id);
        return i === -1 ? null : Object.assign({}, state.classes[i]);
      },
      getFee: function (id) {
        var i = findIndex(state.fees, id);
        return i === -1 ? null : Object.assign({}, state.fees[i]);
      },

      addClass: function (payload) {
        var taken = state.classes.concat(state.deletedClassIds.map(function (id) { return { id: id }; }));
        var record = {
          id: nextId('CLASS', taken),
          name: payload.name,
          section: payload.section,
          status: 'active',
          addedAt: ++classSequence
        };
        classRank[record.id] = 1000 + classSequence;
        state.classes = state.classes.concat([record]);
        notify();
        return record;
      },

      updateClass: function (id, payload) {
        var i = findIndex(state.classes, id);
        if (i === -1) return null;
        state.classes = state.classes.slice();
        state.classes[i] = Object.assign({}, state.classes[i], payload);
        notify();
        return state.classes[i];
      },

      // ---- Arranging classes and sections (the Classes page's Order mode) ----

      /** Custom section order, or null while the default curriculum order applies. */
      getSectionOrder: function () { return state.sectionOrder ? state.sectionOrder.slice() : null; },

      moveSection: function (name, targetName, after) {
        if (name === targetName) return false;
        normalizeOrder();
        var order = state.sectionOrder.filter(function (s) { return s !== name; });
        var at = order.indexOf(targetName);
        if (at === -1 || state.sectionOrder.indexOf(name) === -1) return false;
        order.splice(after ? at + 1 : at, 0, name);
        state.sectionOrder = order;
        notify();
        return true;
      },

      /** dir = -1 (up) or +1 (down). Returns false at either end. */
      shiftSection: function (name, dir) {
        normalizeOrder();
        var order = state.sectionOrder.slice();
        var i = order.indexOf(name);
        var j = i + dir;
        if (i === -1 || j < 0 || j >= order.length) return false;
        order[i] = order[j];
        order[j] = name;
        state.sectionOrder = order;
        notify();
        return true;
      },

      /** Moves a class one place up/down within its own section. */
      shiftClass: function (id, dir) {
        normalizeOrder();
        var cls = state.classes[findIndex(state.classes, id)];
        if (!cls) return false;
        var siblings = state.classes.filter(function (c) { return c.section === cls.section; });
        var k = siblings.indexOf(cls) + dir;
        if (k < 0 || k >= siblings.length) return false;
        var list = state.classes.slice();
        var a = list.indexOf(cls);
        var b = list.indexOf(siblings[k]);
        list[a] = siblings[k];
        list[b] = cls;
        state.classes = list;
        notify();
        return true;
      },

      /** Drops a class before/after another one — joining that class's section if it differs. */
      moveClass: function (id, targetId, after) {
        if (id === targetId) return null;
        normalizeOrder();
        var from = findIndex(state.classes, id);
        var target = state.classes[findIndex(state.classes, targetId)];
        if (from === -1 || !target) return null;
        var moving = Object.assign({}, state.classes[from], { section: target.section });
        var list = state.classes.filter(function (c) { return c.id !== id; });
        var at = list.indexOf(target);
        list.splice(after ? at + 1 : at, 0, moving);
        state.classes = list;
        notify();
        return moving;
      },

      /** Drops a class onto a section heading: it becomes that section's first class. */
      moveClassToSection: function (id, section) {
        normalizeOrder();
        var from = findIndex(state.classes, id);
        if (from === -1) return null;
        var moving = Object.assign({}, state.classes[from], { section: section });
        var list = state.classes.filter(function (c) { return c.id !== id; });
        var first = list.findIndex(function (c) { return c.section === section; });
        list.splice(first === -1 ? list.length : first, 0, moving);
        state.classes = list;
        if (state.sectionOrder.indexOf(section) === -1) state.sectionOrder = state.sectionOrder.concat([section]);
        notify();
        return moving;
      },

      /** Back to the default arrangement: curriculum order for sections, original order for classes. */
      resetOrder: function () {
        state.sectionOrder = null;
        state.classes = state.classes.map(function (c) {
          var n = Object.assign({}, c);
          delete n.addedAt;
          return n;
        }).sort(function (a, b) { return (classRank[a.id] || 0) - (classRank[b.id] || 0); });
        notify();
      },

      /** Permanent. Also unassigns the class from any fee that used it. */
      deleteClass: function (id) {
        var i = findIndex(state.classes, id);
        if (i === -1) return null;
        var removed = state.classes[i];
        state.classes = state.classes.filter(function (c) { return c.id !== id; });
        state.deletedClassIds = state.deletedClassIds.concat([id]);
        state.fees = state.fees.map(function (fee) {
          var assigned = fee.assignedClasses || [];
          if (assigned.indexOf(id) === -1) return fee;
          return Object.assign({}, fee, {
            assignedClasses: assigned.filter(function (x) { return x !== id; })
          });
        });
        notify();
        return removed;
      },

      addFeeType: function (name) {
        var existing = state.feeTypes.filter(function (t) {
          return t.name.toLowerCase() === String(name).toLowerCase();
        })[0];
        if (existing) return existing;
        var record = { id: nextId('TYPE', state.feeTypes), name: name, status: 'active' };
        state.feeTypes = state.feeTypes.concat([record]);
        notify();
        return record;
      },

      addFee: function (payload) {
        var record = withTerms(Object.assign({
          id: nextId('FEE', state.fees),
          status: 'active',
          createdAt: new Date().toISOString().slice(0, 10)
        }, payload));
        state.fees = state.fees.concat([record]);
        notify();
        return record;
      },

      updateFee: function (id, payload) {
        var i = findIndex(state.fees, id);
        if (i === -1) return null;
        state.fees = state.fees.slice();
        state.fees[i] = withTerms(Object.assign({}, state.fees[i], payload));
        notify();
        return state.fees[i];
      },

      deactivateFee: function (id) {
        return this.updateFee(id, { status: 'inactive' });
      },

      reactivateFee: function (id) {
        return this.updateFee(id, { status: 'active' });
      },

      addExpenseCategory: function (name) {
        var existing = state.expenseCategories.filter(function (c) {
          return c.name.toLowerCase() === String(name).toLowerCase();
        })[0];
        if (existing) return existing;
        var record = { id: nextId('CAT', state.expenseCategories), name: name, status: 'active' };
        state.expenseCategories = state.expenseCategories.concat([record]);
        notify();
        return record;
      },

      addExpense: function (payload) {
        var record = Object.assign({
          id: nextId('EXP', state.expenses),
          account: 'MAIN-SCHOOL-ACCOUNT',
          status: 'active',
          createdAt: new Date().toISOString().slice(0, 10),
          addedAt: ++expenseSequence
        }, payload);
        record = withFiles(record);
        state.expenses = state.expenses.concat([record]);
        notify();
        return record;
      },

      updateExpense: function (id, payload) {
        var i = findIndex(state.expenses, id);
        if (i === -1) return null;
        state.expenses = state.expenses.slice();
        state.expenses[i] = withFiles(Object.assign({}, state.expenses[i], payload));
        notify();
        return state.expenses[i];
      },

      /** Permanent — the expense drops out of the list, totals and reports. */
      deleteExpense: function (id) {
        var i = findIndex(state.expenses, id);
        if (i === -1) return null;
        var removed = state.expenses[i];
        state.expenses = state.expenses.filter(function (e) { return e.id !== id; });
        notify();
        return removed;
      },

      voidExpense: function (id) {
        return this.updateExpense(id, { status: 'voided' });
      },

      restoreExpense: function (id) {
        return this.updateExpense(id, { status: 'active' });
      },

      subscribe: function (fn) {
        listeners.push(fn);
        return function () {
          listeners = listeners.filter(function (existing) { return existing !== fn; });
        };
      }
    };
  }

  global.CnFilters = CnFilters;
  global.CnStore = createStore(readSeed());
})(window);
