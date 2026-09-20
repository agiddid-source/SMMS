(function (global) {
  'use strict';

  /** el('span', { className: 'x' }, ['text', childNode]) */
  function el(tag, props, children) {
    var node = document.createElement(tag);
    Object.keys(props || {}).forEach(function (key) {
      if (key === 'dataset') {
        Object.keys(props.dataset).forEach(function (dataKey) {
          node.dataset[dataKey] = props.dataset[dataKey];
        });
      } else if (key in node) {
        node[key] = props[key];
      } else {
        node.setAttribute(key, props[key]);
      }
    });
    (children || []).forEach(function (child) {
      if (child === null || child === undefined) return;
      node.appendChild(typeof child === 'string' ? document.createTextNode(child) : child);
    });
    return node;
  }

  function clear(node) {
    while (node.firstChild) node.removeChild(node.firstChild);
  }

  var openModal = null;

  function CnModal(root) {
    if (typeof root === 'string') root = document.getElementById(root);
    this.root = root;
    this.onClose = null;
    var self = this;

    root.addEventListener('click', function (event) {
      if (event.target.hasAttribute('data-cn-dismiss')) {
        event.preventDefault();
        self.close();
      }
    });
  }

  CnModal.prototype.open = function () {
    if (openModal && openModal !== this) openModal.close();
    this.root.hidden = false;
    // Next frame, so the transition has a frame to animate from.
    var root = this.root;
    requestAnimationFrame(function () { root.classList.add('is-open'); });
    openModal = this;
    document.body.classList.add('cn-modal-open');
    var focusable = this.root.querySelector('[data-cn-autofocus], input, select, button');
    if (focusable) focusable.focus();
  };

  CnModal.prototype.close = function () {
    this.root.classList.remove('is-open');
    this.root.hidden = true;
    if (openModal === this) openModal = null;
    document.body.classList.remove('cn-modal-open');
    if (this.onClose) this.onClose();
  };

  CnModal.prototype.isOpen = function () {
    return !this.root.hidden;
  };

  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape' && openModal) openModal.close();
  });

  function CnConfirm(root) {
    this.modal = new CnModal(root);
    this.root = this.modal.root;
    this.titleNode = this.root.querySelector('[data-cn-confirm-title]');
    this.bodyNode = this.root.querySelector('[data-cn-confirm-body]');
    this.buttonNode = this.root.querySelector('[data-cn-confirm-action]');
    this.handler = null;
    var self = this;
    this.buttonNode.addEventListener('click', function () {
      var run = self.handler;
      self.modal.close();
      if (run) run();
    });
  }

  CnConfirm.prototype.ask = function (options) {
    this.titleNode.textContent = options.title;
    this.bodyNode.textContent = options.body;
    this.buttonNode.querySelector('.ght-button-label').textContent = options.confirmLabel;
    this.handler = options.onConfirm;
    this.modal.open();
  };

  global.CnDom = { el: el, clear: clear };
  global.CnModal = CnModal;
  global.CnConfirm = CnConfirm;
})(window);
