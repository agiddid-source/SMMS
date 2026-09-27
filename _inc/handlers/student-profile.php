<?php

$action = cure($_POST['action'] ?? '');
$student_id = cure($_POST['student_id'] ?? '');

if ($action === 'record_payment' && $student_id !== '') {
    $amount = (float) str_replace([',', ' '], '', $_POST['amount'] ?? 0);
    $method = cure($_POST['payment_method'] ?? 'Bank transfer');
    $reference = cure($_POST['reference'] ?? '');
    $note = cure($_POST['description'] ?? '');
    $actor = cure($_POST['actor'] ?? 'Recorded by Bursar desk');
    
    if ($amount > 0) {
        $students = cn_get_students();
        $found = false;
        
        foreach ($students as &$st) {
            if ($st['id'] === $student_id) {
                $found = true;
                if (empty($reference)) {
                    $reference = 'GHS-REC-' . date('ymd') . '-' . sprintf('%03d', rand(100, 999));
                }
                
                $description = !empty($note) ? $note : "{$method} · Term fees";
                $date_str = date('j F Y, H:i');
                
                $new_tx = [
                    'reference'   => $reference,
                    'date'        => $date_str,
                    'description' => $description,
                    'amount'      => $amount,
                    'status'      => 'Paid',
                    'actor'       => $actor,
                ];
                
                if (!isset($st['transactions']) || !is_array($st['transactions'])) {
                    $st['transactions'] = [];
                }
                array_unshift($st['transactions'], $new_tx);
                
                $st['summary']['paid'] = ($st['summary']['paid'] ?? 0) + $amount;
                $st['summary']['outstanding'] = max(0, ($st['summary']['payable'] ?? 0) - $st['summary']['paid']);
                
                if ($st['summary']['outstanding'] <= 0) {
                    $st['accountStatus'] = 'Paid in full';
                } else {
                    $st['accountStatus'] = 'Partially paid';
                }
                break;
            }
        }
        
        if ($found) {
            cn_write_json('data/students.json', ['students' => $students]);
            header('Location: index.php?p=student-profile&student=' . urlencode($student_id) . toast_query('Payment recorded', '₦' . number_format($amount) . ' recorded successfully', 'Receipt #' . $reference));
            exit;
        }
    }
}

header('Location: index.php?p=student-profile&student=' . urlencode($student_id));
exit;