<?php

function toast_query($loading_message, $success_message, $subject = '') {
    $params = ['toast_loading' => $loading_message, 'toast_success' => $success_message];
    if ($subject !== '') {
        $params['toast_subject'] = $subject;
    }
    return '&' . http_build_query($params);
}

function render_toast() {
    if (!isset($_GET['toast_loading']) || !isset($_GET['toast_success'])) {
        return;
    }

    $loading = cure($_GET['toast_loading']);
    $success = cure($_GET['toast_success']);
    $subject = cure($_GET['toast_subject'] ?? '');
    $spin_ms = random_int(2000, 5000);

    $loading_label = $subject !== '' ? "{$loading} \"{$subject}\"" : $loading;
    ?>
    <link rel="stylesheet" href="styles/toast.css">
    <div class="toast" role="status" aria-live="polite" style="--toast-spin-duration: <?= (int) $spin_ms ?>ms;">
      <span class="toast-phase toast-phase--loading">
        <span class="toast-spinner" aria-hidden="true"></span>
        <span><?= htmlspecialchars($loading_label) ?>&hellip;</span>
      </span>
      <span class="toast-phase toast-phase--done">
        <span class="toast-check" aria-hidden="true">&#10003;</span>
        <span><?= htmlspecialchars($success) ?></span>
      </span>
    </div>
    <?php
}
