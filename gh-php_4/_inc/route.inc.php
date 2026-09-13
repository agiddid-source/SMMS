<?php

$pages = [
    'dashboard' => [
        'file' => 'pages/dashboard.php',
        'title' => 'Overview · Greenhill School OS',
    ],
    'student-profile' => [
        'file' => 'pages/student-profile.php',
        'title' => 'Student account · Greenhill School OS',
    ],
    'coming-soon' => [
        'file' => 'pages/coming-soon.php',
        'title' => 'Coming soon · Greenhill School OS',
    ],
    'invoices' => [
        'file' => 'pages/invoices.php',
        'title' => 'Invoices'
    ],
    'classes' => [
        'file' => 'pages/classes.php',
        'title' => 'Classes · Greenhill School OS',
    ],
    'fee-setup' => [
        'file' => 'pages/fee-setup.php',
        'title' => 'Fee settings · Greenhill School OS',
    ],
];

$p = cure($_GET['p'] ?? '');
if ($p === '') $p = 'dashboard';

if (!isset($pages[$p])) $p = '404';

if ($p === '404') {
    $file = 'pages/404.php';
    $title = 'Not found · Greenhill School OS';
} else {
    $file = $pages[$p]['file'];
    $title = $pages[$p]['title'];
    if ($p === 'coming-soon' && !empty($_GET['module'])) {
        $title = cure($_GET['module']) . ' · Greenhill School OS';
    }
}

$page = $p;
