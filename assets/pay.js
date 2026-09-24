/**
 * assets/pay.js — Bursar & Payments progressive enhancement.
 *
 * The record-payment form and receipt work fully without JavaScript: the modals
 * are CSS checkbox-hack, the form is a normal multipart POST, and the server
 * re-validates and clamps every allocation. This script only *enhances* the
 * experience the way the original JS prototype did — a live running total,
 * per-fee caps as you type, "Pay full balance", and scoped receipt printing.
 *
 * Loaded by pages/payments.php and pages/student-profile.php with `defer`, so
 * the DOM is already parsed when this runs.
 */
(function () {
  'use strict';

  // Mirrors ght_format_naira(): a naira sign then a comma-grouped integer.
  function ghtFormatNaira(amount) {
    var rounded = Math.round(Number(amount) || 0);
    return '₦' + rounded.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
  }

  // Wires one record-payment form: caps, live total, and "Pay full balance".
  function ghtBindPaymentForm(form) {
    var inputs = Array.prototype.slice.call(form.querySelectorAll('.ght-payfee-input'));
    var totalEl = form.querySelector('[data-pay-total]');
    var payFull = form.querySelector('[data-pay-full]');
    if (!inputs.length || !totalEl) return;

    function currentTotal() {
      return inputs.reduce(function (sum, input) {
        return sum + (Number(input.value) || 0);
      }, 0);
    }

    function updateTotal() {
      totalEl.textContent = ghtFormatNaira(currentTotal());
    }

    inputs.forEach(function (input) {
      var cap = Number(input.getAttribute('data-pay-cap')) || 0;
      input.addEventListener('input', function () {
        // Never let an allocation exceed that fee's outstanding balance.
        if (Number(input.value) > cap) input.value = cap;
        updateTotal();
      });
    });

    if (payFull) {
      payFull.addEventListener('click', function () {
        inputs.forEach(function (input) {
          input.value = input.getAttribute('data-pay-cap');
        });
        updateTotal();
      });
    }

    updateTotal();
  }

  // Wires a "Print receipt" button to print just the receipt sheet, using the
  // body flag the print rules in styles/pay.css key off.
  function ghtBindPrint(button) {
    button.addEventListener('click', function () {
      document.body.classList.add('ght-print-receipt');
      function cleanup() {
        document.body.classList.remove('ght-print-receipt');
        window.removeEventListener('afterprint', cleanup);
      }
      window.addEventListener('afterprint', cleanup);
      window.print();
    });
  }

  Array.prototype.forEach.call(document.querySelectorAll('[data-pay-form]'), ghtBindPaymentForm);
  Array.prototype.forEach.call(document.querySelectorAll('[data-pay-print]'), ghtBindPrint);
})();
