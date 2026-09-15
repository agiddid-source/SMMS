<?php

$cn_action = cure($_POST['action'] ?? '');

if ($cn_action === 'add_type') {
    $cn_name = cure($_POST['name'] ?? '');
    if ($cn_name !== '') {
        cn_add_fee_type($cn_name);
    }
    header('Location: index.php?p=fee-setup');
    exit;
}

if ($cn_action === 'add' || $cn_action === 'edit') {
    $cn_id = cure($_POST['id'] ?? '');
    $cn_name = cure($_POST['name'] ?? '');
    $cn_type = cure($_POST['type'] ?? '');
    $cn_amount = (float) ($_POST['amount'] ?? 0);
    $cn_session = cure($_POST['academicSession'] ?? '');
    $cn_term = cure($_POST['term'] ?? '');
    $cn_due_date = cure($_POST['dueDate'] ?? '');
    $cn_description = cure($_POST['description'] ?? '');
    $cn_class_ids = array_map('strval', $_POST['classIds'] ?? []);

    if ($cn_name !== '' && $cn_type !== '' && $cn_amount > 0 && $cn_session !== '') {
        $cn_payload = [
            'name' => $cn_name,
            'type' => $cn_type,
            'amount' => $cn_amount,
            'academicSession' => $cn_session,
            'term' => $cn_term,
            'dueDate' => $cn_due_date,
            'description' => $cn_description,
            'assignedClasses' => $cn_class_ids,
        ];
        if ($cn_action === 'edit' && $cn_id !== '') {
            cn_update_fee($cn_id, $cn_payload);
        } else {
            cn_add_fee($cn_payload);
            header('Location: index.php?p=fee-setup' . toast_query('Adding fee', 'Fee added successfully', $cn_name));
            exit;
        }
    }
} elseif ($cn_action === 'deactivate') {
    $cn_id = cure($_POST['id'] ?? '');
    if ($cn_id !== '') {
        cn_deactivate_fee($cn_id);
    }
}

header('Location: index.php?p=fee-setup');
exit;
