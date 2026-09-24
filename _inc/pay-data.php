<?php
/**
 * _inc/pay-data.php
 *
 * Bursar & Payments data layer. Mirrors _inc/cn-data.php: builds on the
 * shared read_json() from config.php and adds the pay_write_json()
 * counterpart it needs, kept in its own module file so nothing shared gets
 * touched. Plain prefixed functions, matching config.php / format.php /
 * cn-data.php.
 *
 * This is the single place the Bursar UI (payments page, record-payment
 * modal, receipt) reads and writes payment data. Today it persists to
 * data/payments.json server-side; swapping to a database later is a change
 * to this file only — the function names, arguments, and return shapes are
 * the contract the pages/handler depend on.
 */

// Writes a data file as pretty JSON. Named per-module (not added to
// config.php) so the shared config stays untouched — same choice cn-data.php
// made with cn_write_json().
function pay_write_json($relative_path, $data) {
    $full_path = __DIR__ . '/../' . ltrim($relative_path, '/');
    file_put_contents(
        $full_path,
        json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
    );
}

// Students ------------------------------------------------------------------

// Returns every student record (empty array when the file is missing/empty).
function pay_get_students() {
    $data = read_json('data/students.json');
    return $data['students'] ?? [];
}

// Returns one student by id, or null when not found.
function pay_get_student($student_id) {
    foreach (pay_get_students() as $student) {
        if ($student['id'] === $student_id) return $student;
    }
    return null;
}

// Returns the payable fee lines for a student (payable greater than zero) —
// the fees the record-payment form lets the bursar allocate against.
function pay_get_outstanding_fees($student_id) {
    $student = pay_get_student($student_id);
    if (!$student) return [];
    return array_values(array_filter($student['fees'], fn($fee) => $fee['payable'] > 0));
}

// Filters students by name, class, or student number (case-insensitive
// substring). Mirrors cn_filter_classes so the payments search behaves like
// the rest of the app.
function pay_filter_students($students, $search = '') {
    $needle = strtolower(trim($search));
    if ($needle === '') return array_values($students);
    return array_values(array_filter($students, function ($student) use ($needle) {
        return str_contains(strtolower($student['name']), $needle)
            || str_contains(strtolower($student['className']), $needle)
            || str_contains(strtolower($student['studentNumber']), $needle);
    }));
}

// Payments ------------------------------------------------------------------

// Returns all payments (seed + recorded), newest first. Optionally scoped to
// one student. Reads the { "payments": [...] } shape written by pay_save_payment.
function pay_get_payments($student_id = null) {
    $data = read_json('data/payments.json');
    $payments = $data['payments'] ?? [];
    if ($student_id !== null) {
        $payments = array_values(array_filter($payments, fn($p) => $p['studentId'] === $student_id));
    }
    // Sort by ISO date descending so the most recent payment leads the list.
    usort($payments, fn($a, $b) => strcmp($b['date'], $a['date']));
    return $payments;
}

// Returns one payment by its id (PAY-…), or null. Used to render the printable
// receipt after a record redirects with ?receipt=<id>.
function pay_find_payment($payment_id) {
    foreach (pay_get_payments() as $payment) {
        if ($payment['id'] === $payment_id) return $payment;
    }
    return null;
}

// Validates and stores an optional proof-of-payment upload. Returns
// ['name' => <display>, 'file' => 'uploads/<stored>'] on success, or null when
// there is no file, it failed, or it's the wrong type/size. Kept in the data
// layer so the handler stays declarative like the Classes & Fees handlers.
function pay_store_attachment($file) {
    if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return null;
    }
    // Reject empty or oversized (> 5 MB) uploads.
    if (($file['size'] ?? 0) <= 0 || $file['size'] > 5 * 1024 * 1024) {
        return null;
    }
    $original = (string) ($file['name'] ?? 'attachment');
    $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
    if (!in_array($ext, ['pdf', 'png', 'jpg', 'jpeg'], true)) {
        return null;
    }

    $uploads_dir = __DIR__ . '/../uploads';
    if (!is_dir($uploads_dir)) {
        mkdir($uploads_dir, 0775, true);
    }

    // Collision-safe stored name; keep a sanitized original for display.
    $stored = uniqid('pay_', true) . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $uploads_dir . '/' . $stored)) {
        return null;
    }
    $display = trim(preg_replace('/[^\w.\- ]+/u', '', $original));
    if ($display === '') $display = 'attachment.' . $ext;

    return ['name' => $display, 'file' => 'uploads/' . $stored];
}

// Builds a receipt number in the house format GHS-REC-YYMMDD-nnn. nnn is a
// per-day running count so numbers stay readable and unique within a day.
// The server owns issuance now (the JS prototype did this client-side) — the
// correct home for it once a backend exists.
function pay_generate_receipt_number($date) {
    $stamp = $date->format('ymd');
    $same_day = 0;
    foreach (pay_get_payments() as $payment) {
        if (str_contains($payment['receiptNumber'] ?? '', "REC-{$stamp}")) $same_day++;
    }
    $sequence = str_pad((string) ($same_day + 1), 3, '0', STR_PAD_LEFT);
    return "GHS-REC-{$stamp}-{$sequence}";
}

// Records a payment: assigns identifiers/timestamps and the receipt number,
// persists it to data/payments.json, and returns the complete saved record
// for immediate receipt display. Input keys: studentId, amount, method,
// purpose, allocations, note, attachment.
function pay_save_payment($input) {
    $now = new DateTime();
    $receipt_number = pay_generate_receipt_number($now);

    $record = [
        'id' => str_replace('GHS-REC', 'PAY', $receipt_number),
        'receiptNumber' => $receipt_number,
        'studentId' => $input['studentId'],
        'date' => $now->format('Y-m-d\TH:i:s'),
        'dateLabel' => $now->format('j F Y, H:i'),
        'amount' => $input['amount'],
        'method' => $input['method'],
        'status' => 'Paid',
        'purpose' => ($input['purpose'] ?? '') !== '' ? $input['purpose'] : 'Fee payment',
        'allocations' => $input['allocations'] ?? [],
        'note' => $input['note'] ?? '',
        'attachment' => $input['attachment'] ?? null,
        'actor' => 'Recorded by Bursar desk',
    ];

    $data = read_json('data/payments.json');
    $payments = $data['payments'] ?? [];
    $payments[] = $record;
    pay_write_json('data/payments.json', ['payments' => $payments]);

    return $record;
}

// Invoices ------------------------------------------------------------------

// The house invoice number for a student's standing term bill. Receipts are
// issued per payment (per day, sequential); an invoice is the one bill for a
// student's term, so its number is deterministic and derived from the student
// number: GH-2026-041 -> GHS-INV-2026-041. Stable across views with nothing to
// persist. When invoices grow into issued documents with their own lifecycle,
// server-side issuance slots in here, exactly as pay_generate_receipt_number does.
function pay_invoice_number($student) {
    $parts = explode('-', (string) ($student['studentNumber'] ?? ''));
    $year = $parts[1] ?? 'TERM';
    $serial = $parts[2] ?? '000';
    return "GHS-INV-{$year}-{$serial}";
}

// Builds the invoice view-model for one student: the term bill derived live from
// their fee lines and balance summary, so an invoice always reflects the current
// amount due. Returns null for an unknown student. Keeps pages/invoices.php
// declarative — the same contract idea the other pay_* functions follow.
function pay_get_invoice($student) {
    if (!$student) return null;

    $fees = $student['fees'] ?? [];
    $summary = $student['summary'] ?? [];

    // Per-line sums drive the invoice table; the student summary is the figure
    // the rest of the app trusts, so prefer it for the balance and fall back to
    // the sums only when a summary is absent.
    $assigned = 0.0;
    $discount = 0.0;
    $line_payable = 0.0;
    foreach ($fees as $fee) {
        $assigned += (float) ($fee['assigned'] ?? 0);
        $discount += (float) ($fee['discount'] ?? 0);
        $line_payable += (float) ($fee['payable'] ?? 0);
    }

    $payable = isset($summary['payable']) ? (float) $summary['payable'] : $line_payable;
    $paid = isset($summary['paid']) ? (float) $summary['paid'] : 0.0;
    $outstanding = isset($summary['outstanding'])
        ? (float) $summary['outstanding']
        : max($payable - $paid, 0);

    // Status mirrors the payments desk's balance chip — settled vs owing — with a
    // middle "Part paid" once something has been received against the bill.
    if ($payable <= 0) {
        $status = 'No charge';
        $tone = 'neutral';
    } elseif ($outstanding <= 0) {
        $status = 'Paid';
        $tone = 'success';
    } elseif ($paid > 0) {
        $status = 'Part paid';
        $tone = 'accent';
    } else {
        $status = 'Unpaid';
        $tone = 'accent';
    }

    return [
        'number' => pay_invoice_number($student),
        'period' => $fees[0]['note'] ?? 'Current term',
        'status' => $status,
        'statusTone' => $tone,
        'lines' => $fees,
        'totals' => [
            'assigned' => $assigned,
            'discount' => $discount,
            'payable' => $payable,
            'paid' => $paid,
            'outstanding' => $outstanding,
        ],
    ];
}
