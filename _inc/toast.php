<?php
/**
 * _inc/toast.php
 *
 * Shared toast component — any module can use it (Classes & Fees built it,
 * but nothing Classes/Fees-specific is left in it). Required once,
 * globally, from index.php.
 *
 * Because this build is a frontend prototype, the toast is triggered in
 * the browser, not by a redirect: PHP only prints the empty container and
 * loads the assets.
 *
 * In your page markup, once, anywhere (the toast is position: fixed, so
 * where doesn't matter):
 *
 *   <?php render_toast(); ?>
 *
 * Then from any module's JS, at the moment the change happens:
 *
 *   showToast('Adding fee', 'Fee added', name);
 *
 * It returns a Promise that resolves when the success phase appears, so a
 * caller can wait for it if it wants to:
 *
 *   showToast('Recording payment', 'Payment recorded', ref)
 *     .then(() => refreshLedger());
 *
 * Third argument is optional; when given it is quoted into the loading
 * line — 'Adding fee "Tuition"…'.
 */

function render_toast() {
    ?>
    <link rel="stylesheet" href="styles/toast.css">
    <div id="toast-root" class="toast-root" role="status" aria-live="polite"></div>
    <script src="assets/js/toast.js"></script>
    <?php
}
