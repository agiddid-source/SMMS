(function (global) {
  'use strict';

  var SECTION_ORDER = ['Toddler', 'Nursery', 'KG', 'Primary', 'Secondary'];

  // Helpers for filtering and formatting data in the store. 
  // These are pure functions that don't mutate state, 
  // so they can be used in any context (store, view, etc.).

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

    formatDate: function (iso) {
      if (!iso) return '\u2014';
      var parts = String(iso).split('-');
      if (parts.length !== 3) return iso;
      var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
      var month = months[Number(parts[1]) - 1];
      return month ? Number(parts[2]) + ' ' + month + ' ' + parts[0] : iso;
    }
  };

  // Store 

  function readSeed() {
    var node = document.getElementById('cn-seed');
    if (!node) return { classes: [], feeTypes: [], fees: [] };
    try {
      var parsed = JSON.parse(node.textContent || '{}');
      return {
        classes: parsed.classes || [],
        feeTypes: parsed.feeTypes || [],
        fees: parsed.fees || []
      };
    } catch (err) {
      console.error('[cn-store] seed is not valid JSON', err);
      return { classes: [], feeTypes: [], fees: [] };
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
      fees: seed.fees.slice()
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
