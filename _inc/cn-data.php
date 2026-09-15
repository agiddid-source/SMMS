<?php
/**
 * _inc/cn-data.php
 *
 * Classes & Fee Management's data layer. Builds on the existing
 * read_json() from config.php rather than duplicating it, and adds the
 * write_json() counterpart it needs — kept in its own file instead of
 * added to config.php so nothing shared gets touched. Plain functions
 * (not a class), matching how config.php/format.php are already written.
 */

function cn_write_json($relative_path, $data) {
    $full_path = __DIR__ . '/../' . ltrim($relative_path, '/');
    file_put_contents(
        $full_path,
        json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
    );
}

function cn_next_id($prefix, $existing) {
    return sprintf('%s-%03d', $prefix, count($existing) + 1);
}

// Classes 

function cn_get_classes() {
    return read_json('data/classes.json') ?? [];
}

function cn_add_class($name, $section) {
    $classes = cn_get_classes();
    $record = ['id' => cn_next_id('CLASS', $classes), 'name' => $name, 'section' => $section, 'status' => 'active'];
    $classes[] = $record;
    cn_write_json('data/classes.json', $classes);
    return $record;
}

function cn_update_class($id, $name, $section) {
    $classes = cn_get_classes();
    foreach ($classes as &$class) {
        if ($class['id'] === $id) {
            $class['name'] = $name;
            $class['section'] = $section;
            cn_write_json('data/classes.json', $classes);
            return $class;
        }
    }
    return null;
}

function cn_archive_class($id) {
    $classes = cn_get_classes();
    foreach ($classes as &$class) {
        if ($class['id'] === $id) {
            $class['status'] = 'archived';
            cn_write_json('data/classes.json', $classes);
            return true;
        }
    }
    return false;
}

// Fee types

function cn_get_fee_types() {
    return read_json('data/fee-types.json') ?? [];
}

function cn_add_fee_type($name) {
    $types = cn_get_fee_types();
    foreach ($types as $type) {
        if (strcasecmp($type['name'], $name) === 0) return $type;
    }
    $record = ['id' => cn_next_id('TYPE', $types), 'name' => $name, 'status' => 'active'];
    $types[] = $record;
    cn_write_json('data/fee-types.json', $types);
    return $record;
}

// Fees

function cn_get_fees() {
    return read_json('data/fees.json') ?? [];
}

function cn_add_fee($payload) {
    $fees = cn_get_fees();
    $record = array_merge(
        ['id' => cn_next_id('FEE', $fees), 'status' => 'active', 'createdAt' => date('Y-m-d')],
        $payload
    );
    $fees[] = $record;
    cn_write_json('data/fees.json', $fees);
    return $record;
}

function cn_update_fee($id, $payload) {
    $fees = cn_get_fees();
    foreach ($fees as &$fee) {
        if ($fee['id'] === $id) {
            $fee = array_merge($fee, $payload);
            cn_write_json('data/fees.json', $fees);
            return $fee;
        }
    }
    return null;
}

function cn_deactivate_fee($id) {
    $fees = cn_get_fees();
    foreach ($fees as &$fee) {
        if ($fee['id'] === $id) {
            $fee['status'] = 'inactive';
            cn_write_json('data/fees.json', $fees);
            return true;
        }
    }
    return false;
}
