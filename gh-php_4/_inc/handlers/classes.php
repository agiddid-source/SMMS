<?php
/**
 * _inc/handlers/classes.php
 *
 * Required by index.php's generic POST dispatch (see the comment there)
 * before any HTML output has started, so header('Location: ...') is safe
 * here. Not required by pages/classes.php itself — a GET always renders
 * fresh from disk, which already reflects whatever this handler just wrote.
 */

$cn_action = cure($_POST['action'] ?? '');
$cn_id = cure($_POST['id'] ?? '');
$cn_name = cure($_POST['name'] ?? '');
$cn_section = cure($_POST['section'] ?? '');

if ($cn_action === 'add' && $cn_name !== '' && $cn_section !== '') {
    cn_add_class($cn_name, $cn_section);
} elseif ($cn_action === 'edit' && $cn_id !== '' && $cn_name !== '' && $cn_section !== '') {
    cn_update_class($cn_id, $cn_name, $cn_section);
} elseif ($cn_action === 'archive' && $cn_id !== '') {
    cn_archive_class($cn_id);
}

header('Location: index.php?p=classes');
exit;
