<?php

$cn_action = cure($_POST['action'] ?? '');
$cn_id = cure($_POST['id'] ?? '');
$cn_name = cure($_POST['name'] ?? '');
$cn_section = cure($_POST['section'] ?? '');

if ($cn_action === 'add' && $cn_name !== '' && $cn_section !== '') {
    cn_add_class($cn_name, $cn_section);
    header('Location: index.php?p=classes' . toast_query('Adding class', 'Class added successfully', $cn_name));
    exit;
} elseif ($cn_action === 'edit' && $cn_id !== '' && $cn_name !== '' && $cn_section !== '') {
    cn_update_class($cn_id, $cn_name, $cn_section);
} elseif ($cn_action === 'archive' && $cn_id !== '') {
    cn_archive_class($cn_id);
}

header('Location: index.php?p=classes');
exit;
