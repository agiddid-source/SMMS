<?php

function cn_get_classes() {
    return read_json('data/classes.json') ?? [];
}

function cn_get_fee_types() {
    return read_json('data/fee-types.json') ?? [];
}

function cn_get_fees() {
    return read_json('data/fees.json') ?? [];
}

function cn_get_expense_categories() {
    return read_json('data/expense-categories.json') ?? [];
}

function cn_get_expenses() {
    return read_json('data/expenses.json') ?? [];
}

/**
 * Payments are Module 3 (Bursar)'s data to own for real. 
 */
function cn_get_payments() {
    return read_json('data/payments.json') ?? [];
}


function cn_render_seed() {
    $seed = [
        'classes'           => cn_get_classes(),
        'feeTypes'          => cn_get_fee_types(),
        'fees'              => cn_get_fees(),
        'expenseCategories' => cn_get_expense_categories(),
        'expenses'          => cn_get_expenses(),
        'payments'          => cn_get_payments(),
    ];
    echo '<script type="application/json" id="cn-seed">';
    echo json_encode($seed, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP);
    echo '</script>';
}
