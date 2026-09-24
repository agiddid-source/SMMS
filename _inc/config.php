<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

function cure($data) {
    if ($data === null) return '';
    $data = (string) $data;
    $data = trim(strip_tags($data));
    return $data;
}

function read_json($relative_path) {
    $full_path = __DIR__ . '/../' . ltrim($relative_path, '/');
    if (!is_file($full_path)) return null;
    $contents = file_get_contents($full_path);
    if ($contents === false || $contents === '') return null;
    $decoded = json_decode($contents, true);
    return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
}
