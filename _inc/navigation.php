<?php

$ght_navigation_config = [
    ['type' => 'link', 'label' => 'Overview', 'href' => 'index.php?p=dashboard', 'icon' => 'grid'],
    ['type' => 'link', 'label' => 'Ledger', 'href' => 'index.php?p=coming-soon&module=Ledger', 'icon' => 'ledger'],
    ['type' => 'group', 'label' => 'Classes & Fees', 'icon' => 'school', 'items' => [
        ['label' => 'Classes', 'href' => 'index.php?p=classes'],
        ['label' => 'Fee settings', 'href' => 'index.php?p=fee-setup'],
    ]],
    ['type' => 'group', 'label' => 'Student Accounts', 'icon' => 'users', 'items' => [
        ['label' => 'Student profiles', 'href' => 'index.php?p=student-profile&student=sodiq-adeyemi'],
        ['label' => 'Fee discounts', 'href' => 'index.php?p=student-profile&student=sodiq-adeyemi#ght-concessions'],
    ]],
    ['type' => 'group', 'label' => 'Bursar & Payments', 'icon' => 'payment', 'items' => [
        ['label' => 'Invoices', 'href' => 'index.php?p=invoices'],
        ['label' => 'Payments', 'href' => 'index.php?p=payments'],
    ]],
    ['type' => 'group', 'label' => 'Expenses & Reports', 'icon' => 'report', 'items' => [
        ['label' => 'Expenses', 'href' => 'index.php?p=coming-soon&module=Expenses'],
        ['label' => 'Reports', 'href' => 'index.php?p=coming-soon&module=Reports'],
    ]],
    ['type' => 'bottom', 'label' => 'Results', 'href' => 'index.php?p=coming-soon&module=Results', 'icon' => 'chart'],
    ['type' => 'bottom', 'label' => 'Staff', 'href' => 'index.php?p=coming-soon&module=Staff', 'icon' => 'users'],
    ['type' => 'bottom', 'label' => 'Payroll', 'href' => 'index.php?p=coming-soon&module=Payroll+%26+Payslips', 'icon' => 'ledger'],
];

function ght_nav_href_matches_current($href) {
    if (strpos($href, '#') !== false) return false;
    $query = parse_url($href, PHP_URL_QUERY);
    if (!$query) return false;
    parse_str($query, $target);
    foreach ($target as $key => $value) {
        if (!isset($_GET[$key]) || (string) $_GET[$key] !== (string) $value) return false;
    }
    return true;
}

function ght_nav_href_same_route($href, $ignore_keys = ['student']) {
    $href = explode('#', $href)[0];
    $query = parse_url($href, PHP_URL_QUERY);
    if (!$query) return false;
    parse_str($query, $target);
    foreach ($target as $key => $value) {
        if (in_array($key, $ignore_keys, true)) continue;
        if (!isset($_GET[$key]) || (string) $_GET[$key] !== (string) $value) return false;
    }
    return true;
}
