<link rel="stylesheet" href="styles/cn.css">
<?php
$students_data = read_json('data/students.json');
$students = $students_data['students'] ?? [];
$student_id = cure($_GET['student'] ?? '') ?: 'sodiq-adeyemi';

$student = null;
foreach ($students as $candidate) {
    if ($candidate['id'] === $student_id) { $student = $candidate; break; }
}
if (!$student && $students) $student = $students[0];

if (!$student) {
    echo '<div class="p-8 text-center text-neutral-500">Student not found. <a href="index.php?p=students" class="underline">Back to list</a></div>';
    return;
}

$summary = $student['summary'];
$concession = $student['concession'] ?? ['amount' => 0];
$has_discount = !empty($concession['amount']);
$is_paid_full = ($student['accountStatus'] === 'Paid in full' || $summary['outstanding'] === 0);
$status_tone = $is_paid_full ? 'success' : ($student['accountStatus'] === 'Partially paid' ? 'accent' : 'neutral');
$stmt_ref = 'GHS-STM-' . date('Y') . '-' . substr(preg_replace('/[^0-9]/', '', $student['studentNumber']), -3);
?>
<style>
@media print {
  .no-print { display: none !important; }
  body { background: #fff !important; }
  .ght-app-main { padding-left: 0 !important; }
  .statement-sheet { border: none !important; box-shadow: none !important; padding: 0 !important; }
}
</style>

<div class="ght-dashboard-content pb-16">
  <div class="ght-page-enter max-w-4xl mx-auto">

    <!-- Action Bar (Hidden when printing) -->
    <div class="no-print flex items-center justify-between gap-4 mb-6">
      <a class="ght-transaction-link inline-flex items-center text-sm font-medium" href="index.php?p=student-profile&student=<?= urlencode($student['id']) ?>">
        &larr; Back to profile
      </a>
      <div class="flex items-center gap-3">
        <a href="index.php?p=student-profile&student=<?= urlencode($student['id']) ?>" class="ght-button ght-button--secondary text-sm font-medium">
          <span class="ght-button-icon">
            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 7h18M5 4h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Zm1 11h4"/></svg>
          </span>
          <span class="ght-button-label">Record payment</span>
        </a>
        <button onclick="window.print()" class="ght-button ght-button--primary text-sm font-medium cursor-pointer">
          <span class="ght-button-icon">
            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2M6 14h12v8H6z"/></svg>
          </span>
          <span class="ght-button-label">Print / Download PDF</span>
        </button>
      </div>
    </div>

    <!-- Official Statement Document -->
    <div class="statement-sheet bg-white border border-neutral-200 rounded-2xl p-8 sm:p-12 shadow-sm text-neutral-900">
      
      <!-- Letterhead Header -->
      <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-6 border-b border-neutral-200 pb-8">
        <div>
          <div class="flex items-center gap-3">
            <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#171717] text-white font-semibold text-lg tracking-wider">
              GH
            </span>
            <div>
              <h1 class="ght-display text-3xl font-semibold m-0 leading-tight">Greenhill School</h1>
              <p class="m-0 text-xs text-neutral-500 tracking-wide uppercase mt-0.5">Excellence · Integrity · Leadership</p>
            </div>
          </div>
          <p class="mt-3 text-xs text-neutral-500 leading-relaxed max-w-sm m-0">
            Plot 14, Admiralty Way, Lekki Phase 1, Lagos, Nigeria<br>
            Email: accounts@greenhill.sch.ng · Tel: +234 1 892 4000
          </p>
        </div>
        <div class="sm:text-right">
          <span class="inline-block px-3 py-1 bg-neutral-100 rounded-full text-xs font-semibold uppercase tracking-wider text-neutral-700">
            Account Statement
          </span>
          <p class="mt-2 text-xs text-neutral-400 m-0">Statement Ref: <strong class="text-neutral-900 font-mono"><?= $stmt_ref ?></strong></p>
          <p class="mt-0.5 text-xs text-neutral-400 m-0">Date Issued: <strong class="text-neutral-900"><?= date('j F Y') ?></strong></p>
          <p class="mt-0.5 text-xs text-neutral-400 m-0">Session / Term: <strong class="text-neutral-900">2026/2027 · First Term</strong></p>
        </div>
      </div>

      <!-- Student & Guardian Information Box -->
      <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6 bg-neutral-50 border border-neutral-100 rounded-xl p-6">
        <div>
          <p class="m-0 text-[11px] font-semibold uppercase tracking-wider text-neutral-400">Student Profile</p>
          <h2 class="text-lg font-bold text-neutral-900 mt-1 m-0"><?= htmlspecialchars($student['name']) ?></h2>
          <div class="mt-3 space-y-1 text-xs text-neutral-600">
            <div class="flex"><span class="w-24 text-neutral-400">Student ID:</span><strong class="font-mono text-neutral-900"><?= htmlspecialchars($student['studentNumber']) ?></strong></div>
            <div class="flex"><span class="w-24 text-neutral-400">Class:</span><strong class="text-neutral-900"><?= htmlspecialchars($student['className']) ?></strong></div>
            <div class="flex"><span class="w-24 text-neutral-400">Academic Year:</span><span>First Term 2026/27</span></div>
          </div>
        </div>

        <div>
          <p class="m-0 text-[11px] font-semibold uppercase tracking-wider text-neutral-400">Primary Contact (Guardian)</p>
          <h3 class="text-base font-semibold text-neutral-900 mt-1 m-0"><?= htmlspecialchars($student['guardian']) ?></h3>
          <div class="mt-3 space-y-1 text-xs text-neutral-600">
            <div class="flex"><span class="w-24 text-neutral-400">Relationship:</span><span><?= htmlspecialchars($student['guardianRelationship'] ?? 'Guardian') ?></span></div>
            <div class="flex"><span class="w-24 text-neutral-400">Phone:</span><span class="font-medium text-neutral-900"><?= htmlspecialchars($student['phone'] ?? '+234 803 000 0000') ?></span></div>
            <div class="flex"><span class="w-24 text-neutral-400">Email:</span><span><?= htmlspecialchars($student['email'] ?? 'guardian@example.com') ?></span></div>
          </div>
        </div>
      </div>

      <!-- Financial Summary KPI Boxes -->
      <div class="mt-8 grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="bg-neutral-50 border border-neutral-200/80 rounded-xl p-4">
          <p class="m-0 text-[11px] text-neutral-500">Assigned Fees</p>
          <p class="m-0 mt-1 text-base font-semibold text-neutral-900"><?= ght_format_naira($summary['assigned']) ?></p>
        </div>
        <div class="bg-[#f7e7d8]/50 border border-[#e8d2bf] rounded-xl p-4">
          <p class="m-0 text-[11px] text-[#915239]">Approved Discount</p>
          <p class="m-0 mt-1 text-base font-semibold text-[#915239]"><?= $has_discount ? '−' . ght_format_naira($concession['amount']) : '₦0' ?></p>
        </div>
        <div class="bg-neutral-50 border border-neutral-200/80 rounded-xl p-4">
          <p class="m-0 text-[11px] text-neutral-500">Total Paid</p>
          <p class="m-0 mt-1 text-base font-semibold text-[#16803b]"><?= ght_format_naira($summary['paid']) ?></p>
        </div>
        <div class="bg-neutral-50 border border-neutral-200/80 rounded-xl p-4">
          <p class="m-0 text-[11px] text-neutral-500">Balance Due</p>
          <p class="m-0 mt-1 text-base font-semibold <?= $summary['outstanding'] > 0 ? 'text-[#915239]' : 'text-[#16803b]' ?>">
            <?= ght_format_naira($summary['outstanding']) ?>
          </p>
        </div>
      </div>

      <!-- Itemized Fees Breakdown Table -->
      <div class="mt-8">
        <h3 class="text-sm font-semibold uppercase tracking-wider text-neutral-500 mb-3 m-0">1. Itemized Fees Assessment</h3>
        <table class="w-full border-collapse text-xs">
          <thead>
            <tr class="bg-neutral-100 text-neutral-600 border-y border-neutral-200 uppercase tracking-wider text-[11px]">
              <th class="py-2.5 px-3 text-left font-semibold">Fee Item</th>
              <th class="py-2.5 px-3 text-left font-semibold">Billing Term</th>
              <th class="py-2.5 px-3 text-right font-semibold">Assigned</th>
              <th class="py-2.5 px-3 text-right font-semibold">Discount</th>
              <th class="py-2.5 px-3 text-right font-semibold">Net Due</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-neutral-100 border-b border-neutral-200">
            <?php foreach ($student['fees'] as $f): 
              $has_f_disc = !empty($f['discount']);
            ?>
              <tr>
                <td class="py-3 px-3 font-medium text-neutral-900"><?= htmlspecialchars($f['name']) ?></td>
                <td class="py-3 px-3 text-neutral-500"><?= htmlspecialchars($f['note']) ?></td>
                <td class="py-3 px-3 text-right text-neutral-600"><?= ght_format_naira($f['assigned']) ?></td>
                <td class="py-3 px-3 text-right <?= $has_f_disc ? 'text-[#915239] font-medium' : 'text-neutral-400' ?>">
                  <?= $has_f_disc ? '−' . ght_format_naira($f['discount']) : '—' ?>
                </td>
                <td class="py-3 px-3 text-right font-semibold text-neutral-900"><?= ght_format_naira($f['payable']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr class="font-semibold text-xs text-neutral-900 bg-neutral-50">
              <td colspan="2" class="py-2.5 px-3 text-neutral-500">Totals</td>
              <td class="py-2.5 px-3 text-right text-neutral-600"><?= ght_format_naira($summary['assigned']) ?></td>
              <td class="py-2.5 px-3 text-right text-[#915239]"><?= $has_discount ? '−' . ght_format_naira($concession['amount']) : '—' ?></td>
              <td class="py-2.5 px-3 text-right text-neutral-900"><?= ght_format_naira($summary['payable']) ?></td>
            </tr>
          </tfoot>
        </table>
      </div>

      <!-- Approved Discount / Concession Endorsement (if applicable) -->
      <?php if ($has_discount): ?>
        <div class="mt-6 rounded-xl bg-[#f7e7d8]/60 border border-[#eed0ba] p-4 text-xs">
          <div class="flex items-start justify-between gap-3">
            <div>
              <p class="font-semibold text-[#915239] m-0 text-sm">Approved Concession: <?= htmlspecialchars($concession['name']) ?></p>
              <p class="text-neutral-600 mt-1 mb-0 leading-relaxed"><?= htmlspecialchars($concession['description']) ?></p>
            </div>
            <span class="shrink-0 font-bold text-sm text-[#915239]">−<?= ght_format_naira($concession['amount']) ?></span>
          </div>
          <p class="text-[11px] text-neutral-500 mt-2.5 mb-0">
            Endorsed by: <strong class="text-neutral-700"><?= htmlspecialchars($concession['approvedBy'] ?? 'Bursar') ?></strong> · 
            Rule: <strong><?= htmlspecialchars($concession['rule'] ?? '') ?></strong> · 
            Date: <?= htmlspecialchars($concession['appliedOn'] ?? '') ?>
          </p>
        </div>
      <?php endif; ?>

      <!-- Payment History / Ledger -->
      <div class="mt-8">
        <h3 class="text-sm font-semibold uppercase tracking-wider text-neutral-500 mb-3 m-0">2. Recorded Transactions & Receipts</h3>
        <?php if (empty($student['transactions'])): ?>
          <div class="border border-neutral-100 rounded-xl p-6 text-center text-xs text-neutral-400 bg-neutral-50">
            No payments have been recorded for this term. Outstanding balance is <strong><?= ght_format_naira($summary['outstanding']) ?></strong>.
          </div>
        <?php else: ?>
          <table class="w-full border-collapse text-xs">
            <thead>
              <tr class="bg-neutral-100 text-neutral-600 border-y border-neutral-200 uppercase tracking-wider text-[11px]">
                <th class="py-2 px-3 text-left font-semibold">Date</th>
                <th class="py-2 px-3 text-left font-semibold">Receipt Ref</th>
                <th class="py-2 px-3 text-left font-semibold">Description</th>
                <th class="py-2 px-3 text-left font-semibold">Recorded By</th>
                <th class="py-2 px-3 text-right font-semibold">Amount</th>
                <th class="py-2 px-3 text-center font-semibold">Type</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-neutral-100 border-b border-neutral-200">
              <?php foreach ($student['transactions'] as $tx): 
                $is_pmt = ($tx['status'] === 'Paid');
              ?>
                <tr>
                  <td class="py-2.5 px-3 text-neutral-600"><?= htmlspecialchars($tx['date']) ?></td>
                  <td class="py-2.5 px-3 font-mono font-medium text-neutral-800"><?= htmlspecialchars($tx['reference']) ?></td>
                  <td class="py-2.5 px-3 text-neutral-800"><?= htmlspecialchars($tx['description']) ?></td>
                  <td class="py-2.5 px-3 text-neutral-500"><?= htmlspecialchars($tx['actor']) ?></td>
                  <td class="py-2.5 px-3 text-right font-semibold <?= $is_pmt ? 'text-[#16803b]' : 'text-[#915239]' ?>">
                    <?= ($is_pmt ? '' : '−') . ght_format_naira($tx['amount']) ?>
                  </td>
                  <td class="py-2.5 px-3 text-center">
                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold <?= $is_pmt ? 'bg-emerald-50 text-[#16803b]' : 'bg-[#f7e7d8] text-[#915239]' ?>">
                      <?= htmlspecialchars($tx['status']) ?>
                    </span>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>

      <!-- Payment Settlement Details & Bank Instructions -->
      <div class="mt-8 border-t border-neutral-200 pt-6 grid grid-cols-1 md:grid-cols-2 gap-6 text-xs text-neutral-500">
        <div>
          <p class="font-semibold text-neutral-800 uppercase tracking-wider text-[11px] m-0">Payment Instructions</p>
          <p class="mt-1 leading-relaxed m-0">
            All fees are payable via direct transfer or bank teller to Greenhill School's designated collection account. Please quote the student number as payment reference.
          </p>
          <div class="mt-2.5 p-3 bg-neutral-50 rounded-lg border border-neutral-200/80 text-neutral-700">
            <div>Bank Name: <strong>Zenith Bank PLC</strong></div>
            <div>Account Name: <strong>Greenhill School Trust</strong></div>
            <div>Account Number: <strong class="font-mono text-neutral-900 text-sm">1014829012</strong></div>
          </div>
        </div>

        <div class="flex flex-col justify-end md:items-end">
          <div class="w-64 border-t border-neutral-300 pt-2 text-center mt-8">
            <p class="font-semibold text-neutral-900 m-0">Adaeze Nwosu</p>
            <p class="text-[11px] text-neutral-400 m-0">Bursar & Head of Accounts</p>
            <p class="text-[10px] text-neutral-400 mt-1 m-0">Official Electronic Document · Greenhill School</p>
          </div>
        </div>
      </div>

    </div>

  </div>
</div>