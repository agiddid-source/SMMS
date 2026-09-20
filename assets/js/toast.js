(function (global) {
  'use strict';

  var VISIBLE_AFTER_SUCCESS_MS = 2200;

  function message(value) {
    var node = document.createElement('span');
    node.className = 'toast-message';
    node.textContent = value;
    return node;
  }

  function showToast(loadingMessage, successMessage, subject) {
    var root = document.getElementById('toast-root');
    if (!root) return Promise.resolve();

    var loadingLine = subject ? loadingMessage + ' \u201C' + subject + '\u201D' : loadingMessage;
    var spinMs = 2000 + Math.floor(Math.random() * 3000);

    var toast = document.createElement('div');
    toast.className = 'toast';

    var loading = document.createElement('span');
    loading.className = 'toast-phase toast-phase--loading';
    loading.innerHTML = '<span class="toast-spinner" aria-hidden="true"></span>';
    loading.appendChild(message(loadingLine + '\u2026'));

    var done = document.createElement('span');
    done.className = 'toast-phase toast-phase--done';
    done.innerHTML = '<span class="toast-check" aria-hidden="true">\u2713</span>';
    done.appendChild(message(successMessage));

    toast.appendChild(loading);
    toast.appendChild(done);
    root.appendChild(toast);

    requestAnimationFrame(function () { toast.classList.add('is-open'); });

    return new Promise(function (resolve) {
      setTimeout(function () {
        toast.classList.add('is-done');
        resolve();
        setTimeout(function () {
          toast.classList.remove('is-open');
          setTimeout(function () {
            if (toast.parentNode) toast.parentNode.removeChild(toast);
          }, 260);
        }, VISIBLE_AFTER_SUCCESS_MS);
      }, spinMs);
    });
  }

  global.showToast = showToast;
})(window);
