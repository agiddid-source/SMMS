<?php
require __DIR__ . '/../_inc/cn-filters.php';

$results = [];
function check($label, $pass) { global $results; $results[] = [$label, $pass]; }

$sampleClasses = [
    ['id' => 'C1', 'name' => 'JSS 3', 'section' => 'Secondary', 'status' => 'active'],
    ['id' => 'C2', 'name' => 'SS 3', 'section' => 'Secondary', 'status' => 'active'],
    ['id' => 'C3', 'name' => 'Year 1', 'section' => 'Primary', 'status' => 'active'],
    ['id' => 'C4', 'name' => 'Old Class', 'section' => 'Primary', 'status' => 'archived'],
];

check('cn_filter_classes excludes archived by default', count(cn_filter_classes($sampleClasses)) === 3);
check('cn_filter_classes search matches substring across names', count(cn_filter_classes($sampleClasses, 'SS 3')) === 2);
check('cn_filter_classes section filter narrows correctly', count(cn_filter_classes($sampleClasses, '', 'Primary')) === 1);
check('cn_filter_classes includeArchived brings archived back', count(cn_filter_classes($sampleClasses, '', '', true)) === 4);

$grouped = cn_group_by_section(cn_filter_classes($sampleClasses));
$groupedKeys = array_keys($grouped);
check('cn_group_by_section orders Primary before Secondary', $groupedKeys[0] === 'Primary' && $groupedKeys[1] === 'Secondary');
check('cn_group_by_section groups correctly', count($grouped['Secondary']) === 2);
check('cn_distinct_sections returns sorted unique sections', cn_distinct_sections($sampleClasses) === ['Primary', 'Secondary']);

$sampleFees = [
    ['id' => 'F1', 'name' => 'Tuition', 'type' => 'Tuition', 'term' => 'First Term', 'status' => 'active', 'assignedClasses' => ['C1', 'C3']],
    ['id' => 'F2', 'name' => 'WAEC Fee', 'type' => 'WAEC', 'term' => 'First Term', 'status' => 'active', 'assignedClasses' => ['C2']],
    ['id' => 'F3', 'name' => 'Old Fee', 'type' => 'Other', 'term' => 'Second Term', 'status' => 'inactive', 'assignedClasses' => []],
];

check('cn_filter_fees excludes inactive by default', count(cn_filter_fees($sampleFees)) === 2);
check('cn_filter_fees type filter narrows correctly', count(cn_filter_fees($sampleFees, '', 'WAEC')) === 1);
check('cn_resolve_class_names resolves IDs to names and drops unknown IDs', cn_resolve_class_names(['C1', 'C3', 'MISSING'], $sampleClasses) === ['JSS 3', 'Year 1']);
check('cn_distinct_terms returns sorted unique terms', cn_distinct_terms($sampleFees) === ['First Term', 'Second Term']);

check('cn_format_class_names: none assigned', cn_format_class_names([]) === 'None assigned');
check('cn_format_class_names: two or fewer joined plainly', cn_format_class_names(['A', 'B']) === 'A, B');
check('cn_format_class_names: truncates beyond two with "+N more"', cn_format_class_names(['A', 'B', 'C', 'D']) === 'A, B +2 more');

$failed = 0;
foreach ($results as [$label, $pass]) {
    echo ($pass ? 'PASS' : 'FAIL') . " — $label\n";
    if (!$pass) $failed++;
}
$total = count($results);
echo "\n" . ($total - $failed) . "/$total checks passed\n";
exit($failed > 0 ? 1 : 0);
