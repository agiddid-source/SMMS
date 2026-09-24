<?php
/**
 * _inc/handlers/payments.php
 *
 * Bursar & Payments POST handler. index.php dispatches here before any HTML is
 * emitted, so a successful record can Post/Redirect/Get straight to a printable
 * receipt. Mirrors the Classes & Fees handlers: cure() the input, do the work
 * through the pay_* data layer, redirect with the shared toast_query(); fall
 * through to a plain redirect on anything invalid.
 */

$pay_action = cure($_POST['action'] ?? '');

// Whitelist the page we bounce back to. The record form is reachable from both
// the payments desk and a student profile, and each wants to return to itself —
// but a crafted return_to must not be able to redirect anywhere else.
$pay_return_to = cure($_POST['return_to'] ?? 'payments');
if (!in_array($pay_return_to, ['payments', 'student-profile'], true)) {
    $pay_return_to = 'payments';
}

if ($pay_action === 'record') {
    $pay_student_id = cure($_POST['studentId'] ?? '');
    $pay_student = pay_get_student($pay_student_id);

    if ($pay_student) {
        $pay_method = cure($_POST['method'] ?? '') ?: 'Cash';
        $pay_note = cure($_POST['note'] ?? '');

        // Fees this student can actually be paid against, keyed by name with the
        // remaining balance as each per-line cap. The form clamps live in JS,
        // but the server re-clamps here so a hand-crafted POST can neither
        // over-allocate a fee nor invent one that isn't outstanding.
        $pay_caps = [];
        foreach (pay_get_outstanding_fees($pay_student_id) as $pay_fee) {
            $pay_caps[$pay_fee['name']] = (float) $pay_fee['payable'];
        }

        $pay_names = $_POST['alloc_name'] ?? [];
        $pay_amounts = $_POST['alloc_amount'] ?? [];
        $pay_allocations = [];
        $pay_total = 0.0;

        foreach ($pay_names as $pay_i => $pay_raw_name) {
            $pay_name = cure($pay_raw_name);
            $pay_amount = (float) ($pay_amounts[$pay_i] ?? 0);
            if ($pay_name === '' || $pay_amount <= 0 || !isset($pay_caps[$pay_name])) continue;
            // Never allocate more than the outstanding balance on that fee.
            $pay_amount = min($pay_amount, $pay_caps[$pay_name]);
            if ($pay_amount <= 0) continue;
            $pay_allocations[] = ['feeName' => $pay_name, 'amount' => $pay_amount];
            $pay_total += $pay_amount;
        }

        if ($pay_total > 0) {
            // Optional proof-of-payment upload, validated for type/size and
            // stored under uploads/ with a collision-safe name (null otherwise).
            $pay_attachment = pay_store_attachment($_FILES['attachment'] ?? null);

            // Purpose mirrors the JS prototype: the bursar's note when given,
            // otherwise the names of the fees the payment was applied to.
            $pay_purpose = $pay_note !== ''
                ? $pay_note
                : implode(', ', array_column($pay_allocations, 'feeName'));

            $pay_record = pay_save_payment([
                'studentId' => $pay_student_id,
                'amount' => $pay_total,
                'method' => $pay_method,
                'purpose' => $pay_purpose,
                'allocations' => $pay_allocations,
                'note' => $pay_note,
                'attachment' => $pay_attachment,
            ]);

            // ?receipt= makes the destination page render the printable receipt.
            $pay_toast = toast_query(
                'Recording payment',
                'Payment recorded · Receipt ' . $pay_record['receiptNumber'],
                $pay_student['name']
            );
            header('Location: index.php?p=' . $pay_return_to
                . '&student=' . urlencode($pay_student_id)
                . '&receipt=' . urlencode($pay_record['id'])
                . $pay_toast);
            exit;
        }
    }

    // Invalid: unknown student, or nothing left to allocate. Return quietly to
    // the form (no toast) with the student still selected where we know it.
    header('Location: index.php?p=' . $pay_return_to
        . ($pay_student_id !== '' ? '&student=' . urlencode($pay_student_id) : ''));
    exit;
}

header('Location: index.php?p=' . $pay_return_to);
exit;
