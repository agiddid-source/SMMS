<?php

function ght_format_naira($amount) {
    return '₦' . number_format((float) $amount, 0);
}

function ght_format_compact_naira($amount) {
    return ght_format_naira($amount / 1000000) . 'm';
}

