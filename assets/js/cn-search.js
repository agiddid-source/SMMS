(function (global) {
  'use strict';

  var DELAY = 350;
  var ICON = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" ' +
    'stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="6.5"/><path d="M16 16l4.5 4.5"/></svg>';

  /** Wraps the input and adds the button. Returns the wrapper (safe to call twice). */
  function enhance(input) {
    var existing = input.parentNode;
    if (existing && existing.classList && existing.classList.contains('cn-search')) return existing;

    var wrapper = document.createElement('div');
    wrapper.className = 'cn-search';
    if (existing) existing.insertBefore(wrapper, input);
    wrapper.appendChild(input);

    var button = document.createElement('button');
    button.type = 'button';
    button.className = 'cn-search-btn';
    button.setAttribute('aria-label', 'Search');
    button.title = 'Search';
    button.innerHTML = ICON;
    wrapper.appendChild(button);
    return wrapper;
  }

  function bind(input, onSearch, options) {
    var delay = options && options.delay != null ? options.delay : DELAY;
    var wrapper = enhance(input);
    var button = wrapper.querySelector('.cn-search-btn');
    var timer = null;

    function run() {
      if (timer) { clearTimeout(timer); timer = null; }
      onSearch(input.value);
    }

    input.addEventListener('input', function () {
      if (timer) clearTimeout(timer);
      timer = setTimeout(run, delay);
    });
    input.addEventListener('search', run);   
    input.addEventListener('cn-pick', run);  
    input.addEventListener('keydown', function (event) {
      if (event.key !== 'Enter') return;
      event.preventDefault();                
      run();
    });
    button.addEventListener('click', function () {
      run();
      input.focus();
    });

    return { wrapper: wrapper, flush: run };
  }

  global.CnSearch = { bind: bind, enhance: enhance, delay: DELAY };
})(window);
