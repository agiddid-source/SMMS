<?php

$cn_action = cure($_POST['action'] ?? '');
$cn_id = cure($_POST['id'] ?? '');
$cn_name = cure($_POST['name'] ?? '');
$cn_type = cure($_POST['type'] ?? '');
$cn_rate = (float) ($_POST['rate'] ?? -1);
$cn_applies_to = cure($_POST['appliesTo'] ?? '');
$cn_eligibility = cure($_POST['eligibility'] ?? '');

$cn_valid_types = ['Percentage', 'Full waiver'];
$cn_valid_scopes = ['tuition', 'total_fees'];

if ($cn_action === 'toggle' && $cn_id !== '') {
    $new_status = cn_toggle_discount_status($cn_id);
    if ($new_status !== null) {
        $status_label = $new_status === 'active' ? 'activated' : 'deactivated';
        header('Location: index.php?p=discounts' . toast_query('Updating discount', 'Discount ' . $status_label . ' successfully'));
        exit;
    }
    header('Location: index.php?p=discounts&discount_error=' . urlencode('The selected discount could not be found.'));
    exit;
}

if ($cn_action === 'add' || $cn_action === 'edit') {
    $cn_is_valid = $cn_name !== ''
        && in_array($cn_type, $cn_valid_types, true)
        && $cn_rate >= 0
        && $cn_rate <= 100
        && in_array($cn_applies_to, $cn_valid_scopes, true)
        && $cn_eligibility !== '';

    if (!$cn_is_valid) {
        header('Location: index.php?p=discounts&discount_error=' . urlencode('Enter a name, a rate from 0 to 100, a valid scope, and eligibility criteria.'));
        exit;
    }

    $cn_payload = [
        'name' => $cn_name,
        'type' => $cn_type,
        'rate' => $cn_rate,
        'appliesTo' => $cn_applies_to,
        'eligibility' => $cn_eligibility,
    ];

    if ($cn_action === 'edit' && $cn_id !== '') {
        $updated = cn_update_discount($cn_id, $cn_payload);
        if ($updated === null) {
            header('Location: index.php?p=discounts&discount_error=' . urlencode('The selected discount could not be found.'));
            exit;
        }
        header('Location: index.php?p=discounts' . toast_query('Saving discount', 'Discount updated successfully', $cn_name));
        exit;
    }

    cn_add_discount($cn_payload);
    header('Location: index.php?p=discounts' . toast_query('Adding discount', 'Discount added successfully', $cn_name));
    exit;
}

header('Location: index.php?p=discounts');
exit;