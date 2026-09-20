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

function cn_render_seed() {
    $seed = [
        'classes'  => cn_get_classes(),
        'feeTypes' => cn_get_fee_types(),
        'fees'     => cn_get_fees(),
    ];
    echo '<script type="application/json" id="cn-seed">';
    echo json_encode($seed, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP);
    echo '</script>';
}
