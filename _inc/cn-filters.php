<?php

const CN_SECTION_ORDER = ['Toddler', 'Nursery', 'KG', 'Primary', 'Secondary'];

function cn_filter_classes($classes, $search = '', $section = '', $include_archived = false) {
    $term = strtolower(trim($search));
    return array_values(array_filter($classes, function ($class) use ($term, $section, $include_archived) {
        if (!$include_archived && $class['status'] === 'archived') return false;
        if ($section !== '' && $class['section'] !== $section) return false;
        if ($term !== '' && !str_contains(strtolower($class['name']), $term)) return false;
        return true;
    }));
}

function cn_group_by_section($classes) {
    $groups = [];
    foreach ($classes as $class) {
        $groups[$class['section']][] = $class;
    }
    $known = array_values(array_filter(CN_SECTION_ORDER, fn($s) => isset($groups[$s])));
    $other = array_diff(array_keys($groups), CN_SECTION_ORDER);
    sort($other);
    $result = [];
    foreach (array_merge($known, $other) as $section) {
        $result[$section] = $groups[$section];
    }
    return $result;
}

function cn_distinct_sections($classes) {
    $sections = array_unique(array_map(fn($c) => $c['section'], $classes));
    sort($sections);
    return array_values($sections);
}

function cn_filter_fees($fees, $search = '', $type = '', $term = '', $include_inactive = false) {
    $needle = strtolower(trim($search));
    return array_values(array_filter($fees, function ($fee) use ($needle, $type, $term, $include_inactive) {
        if (!$include_inactive && $fee['status'] === 'inactive') return false;
        if ($type !== '' && $fee['type'] !== $type) return false;
        if ($term !== '' && $fee['term'] !== $term) return false;
        if ($needle !== '' && !str_contains(strtolower($fee['name']), $needle)) return false;
        return true;
    }));
}

function cn_resolve_class_names($class_ids, $classes) {
    $names = [];
    foreach ($class_ids as $id) {
        foreach ($classes as $class) {
            if ($class['id'] === $id) { $names[] = $class['name']; break; }
        }
    }
    return $names;
}

function cn_distinct_terms($fees) {
    $terms = array_unique(array_map(fn($f) => $f['term'], $fees));
    sort($terms);
    return array_values($terms);
}

function cn_format_class_names($names) {
    if (count($names) === 0) return 'None assigned';
    if (count($names) <= 2) return implode(', ', $names);
    return implode(', ', array_slice($names, 0, 2)) . ' +' . (count($names) - 2) . ' more';
}

function cn_filter_discounts($discounts, $search = '', $type = '', $applies_to = '', $include_inactive = true) {
    $needle = strtolower(trim($search));
    return array_values(array_filter($discounts, function ($discount) use ($needle, $type, $applies_to, $include_inactive) {
        if (!$include_inactive && $discount['status'] === 'inactive') return false;
        if ($type !== '' && $discount['type'] !== $type) return false;
        if ($applies_to !== '' && $discount['appliesTo'] !== $applies_to) return false;
        if ($needle !== '' && !str_contains(strtolower($discount['name'] . ' ' . $discount['eligibility']), $needle)) return false;
        return true;
    }));
}
