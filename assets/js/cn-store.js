
(function (global) {
  'use strict';

  var SECTION_ORDER = ['Toddler', 'Nursery', 'KG', 'Primary', 'Secondary'];

  // Pure helpers 
  var CnFilters = {
    sectionOrder: SECTION_ORDER,

    filterClasses: function (classes, search, section, includeArchived) {
      var term = String(search || '').trim().toLowerCase();
      return classes.filter(function (cls) {
        if (!includeArchived && cls.status === 'archived') return false;
        if (section && cls.section !== section) return false;
        if (term && cls.name.toLowerCase().indexOf(term) === -1) return false;
        return true;
      });
    },

    /** Returns [[sectionName, classes], …] in curriculum order, unknown sections last. */
    groupBySection: function (classes) {
      var buckets = {};
      classes.forEach(function (cls) {
        (buckets[cls.section] = buckets[cls.section] || []).push(cls);
      });
      var known = SECTION_ORDER.filter(function (s) { return buckets[s]; });
      var other = Object.keys(buckets).filter(function (s) {
        return SECTION_ORDER.indexOf(s) === -1;
      }).sort();
      return known.concat(other).map(function (s) { return [s, buckets[s]]; });
    },

    distinctSections: function (classes) {
      var seen = classes.map(function (c) { return c.section; });
      var unique = seen.filter(function (s, i) { return seen.indexOf(s) === i; });
      var known = SECTION_ORDER.filter(function (s) { return unique.indexOf(s) !== -1; });
      var other = unique.filter(function (s) { return SECTION_ORDER.indexOf(s) === -1; }).sort();
      return known.concat(other);
    },

    filterFees: function (fees, search, type, term, includeInactive) {
      var needle = String(search || '').trim().toLowerCase();
      return fees.filter(function (fee) {
        if (!includeInactive && fee.status === 'inactive') return false;
        if (type && fee.type !== type) return false;
        if (term && fee.term !== term) return false;
        if (needle && fee.name.toLowerCase().indexOf(needle) === -1) return false;
        return true;
      });
    },

    distinctTerms: function (fees) {
      var seen = fees.map(function (f) { return f.term; });
      return seen.filter(function (t, i) { return t && seen.indexOf(t) === i; }).sort();
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

    filterExpenses: function (expenses, search, categoryId, from, to, includeVoided) {
      var needle = String(search || '').trim().toLowerCase();
      var self = this;
      return expenses.filter(function (exp) {
        if (!includeVoided && exp.status === 'voided') return false;
        if (categoryId && exp.categoryId !== categoryId) return false;
        if ((from || to) && !self.inDateRange(exp.expenseDate, from, to)) return false;
        if (needle) {
          var hay = (exp.description + ' ' + exp.payee).toLowerCase();
          if (hay.indexOf(needle) === -1) return false;
        }
        return true;
      });
    },

    filterPayments: function (payments, search, classId, feeId, from, to) {
      var needle = String(search || '').trim().toLowerCase();
      var self = this;
      return payments.filter(function (pay) {
        if (classId && pay.classId !== classId) return false;
        if (feeId && pay.feeId !== feeId) return false;
        if ((from || to) && !self.inDateRange(pay.paymentDate, from, to)) return false;
        if (needle) {
          var hay = (pay.studentName + ' ' + pay.receiptNumber).toLowerCase();
          if (hay.indexOf(needle) === -1) return false;
        }
        return true;
      });
    },

    sumAmounts: function (records) {
      return records.reduce(function (sum, r) { return sum + Number(r.amount || 0); }, 0);
    },

    /** [{key, total}], sorted highest first — the income-by-fee /
     *  expenditure-by-category breakdowns on the Income vs Expenditure report. */
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

  function createStore(seed) {
    var state = {
      classes: seed.classes.slice(),
      feeTypes: seed.feeTypes.slice(),
      fees: seed.fees.slice(),
      expenseCategories: seed.expenseCategories.slice(),
      expenses: seed.expenses.slice(),
      payments: seed.payments.slice()
    };
    var listeners = [];

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
        var record = {
          id: nextId('CLASS', state.classes),
          name: payload.name,
          section: payload.section,
          status: 'active'
        };
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

      archiveClass: function (id) {
        return this.updateClass(id, { status: 'archived' });
      },

      restoreClass: function (id) {
        return this.updateClass(id, { status: 'active' });
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
        var record = Object.assign({
          id: nextId('FEE', state.fees),
          status: 'active',
          createdAt: new Date().toISOString().slice(0, 10)
        }, payload);
        state.fees = state.fees.concat([record]);
        notify();
        return record;
      },

      updateFee: function (id, payload) {
        var i = findIndex(state.fees, id);
        if (i === -1) return null;
        state.fees = state.fees.slice();
        state.fees[i] = Object.assign({}, state.fees[i], payload);
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
          createdAt: new Date().toISOString().slice(0, 10)
        }, payload);
        state.expenses = state.expenses.concat([record]);
        notify();
        return record;
      },

      updateExpense: function (id, payload) {
        var i = findIndex(state.expenses, id);
        if (i === -1) return null;
        state.expenses = state.expenses.slice();
        state.expenses[i] = Object.assign({}, state.expenses[i], payload);
        notify();
        return state.expenses[i];
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
