<link rel="stylesheet" href="styles/cn.css">
<?php
$students_data = read_json('data/students.json');
$all_students = $students_data['students'] ?? [];
$classes_data = read_json('data/classes.json') ?? [];

// Filtering
$search = strtolower(trim($_GET['search'] ?? ''));
$class_filter = cure($_GET['class'] ?? '');
$status_filter = cure($_GET['status'] ?? '');
$discount_filter = cure($_GET['discount'] ?? '');

// Compute metrics across all students
$total_all = count($all_students);
$count_paid_full = count(array_filter($all_students, fn($s) => $s['accountStatus'] === 'Paid in full'));
$count_partial   = count(array_filter($all_students, fn($s) => $s['accountStatus'] === 'Partially paid'));
$count_unpaid    = count(array_filter($all_students, fn($s) => $s['accountStatus'] === 'Not yet paid'));
$count_discount  = count(array_filter($all_students, fn($s) => !empty($s['concession']['amount'])));

$filtered_students = array_filter($all_students, function($student) use ($search, $class_filter, $status_filter, $discount_filter) {
    if ($class_filter !== '' && $student['className'] !== $class_filter) return false;
    if ($status_filter !== '' && $student['accountStatus'] !== $status_filter) return false;
    if ($discount_filter === '1' && empty($student['concession']['amount'])) return false;
    
    if ($search !== '') {
        $search_term = $student['name'] . ' ' . $student['studentNumber'] . ' ' . $student['guardian'];
        if (strpos(strtolower($search_term), $search) === false) return false;
    }
    return true;
});

// Pagination
$per_page = 15;
$total_students = count($filtered_students);
$total_pages = max(1, (int) ceil($total_students / $per_page));
$current_page = max(1, min((int)($_GET['page'] ?? 1), $total_pages));
$offset = ($current_page - 1) * $per_page;

$paginated_students = array_slice($filtered_students, $offset, $per_page);

// Helper to build page URLs while preserving filters
function student_page_url($page, $search, $class, $status, $discount) {
    $params = ['p' => 'students', 'page' => $page];
    if ($search !== '') $params['search'] = $search;
    if ($class !== '') $params['class'] = $class;
    if ($status !== '') $params['status'] = $status;
    if ($discount !== '') $params['discount'] = $discount;
    return 'index.php?' . http_build_query($params);
}

$has_active_filters = ($search !== '' || $class_filter !== '' || $status_filter !== '' || $discount_filter !== '');
?>
<div class="ght-dashboard-content">
  <div class="ght-page-enter">

    <!-- Header Section -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
      <div>
        <p class="m-0 text-sm text-[#737373]">Student Accounts</p>
        <h2 class="ght-display mb-0 mt-2 text-3xl leading-none tracking-normal sm:text-4xl">Students.</h2>
        <p class="mb-0 mt-3 max-w-2xl text-sm leading-6 text-[#737373]">View and manage student financial profiles, approved discounts, and recorded term payments.</p>
      </div>
      <div class="flex items-center gap-2">
        <a href="index.php?p=discounts" class="ght-button ght-button--secondary text-sm font-medium">
          <span class="ght-button-icon">
            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
          </span>
          <span class="ght-button-label">Discount rules</span>
        </a>
        <a href="index.php?p=fee-setup" class="ght-button ght-button--secondary text-sm font-medium">
          <span class="ght-button-icon">
            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 6V4m0 2a2 2 0 1 0 0 4m0-4a2 2 0 1 1 0 4m-6 8a2 2 0 1 0 0-4m0 4a2 2 0 1 1 0-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 1 0 0-4m0 4a2 2 0 1 1 0-4m0 4v2m0-6V4"/></svg>
          </span>
          <span class="ght-button-label">Fee setup</span>
        </a>
      </div>
    </div>

    <!-- Quick Status Filter Pills -->
    <div class="mt-6 flex flex-wrap items-center gap-2 pb-1 border-b border-[#f0f0f0]">
      <a href="<?= student_page_url(1, $search, $class_filter, '', '') ?>"
         class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-medium transition-all <?= ($status_filter === '' && $discount_filter === '') ? 'bg-[#171717] text-white shadow-sm' : 'bg-white text-neutral-600 border border-neutral-200 hover:border-neutral-300 hover:text-neutral-900' ?>">
        <span>All students</span>
        <span class="px-1.5 py-0.2 rounded-full text-[11px] <?= ($status_filter === '' && $discount_filter === '') ? 'bg-neutral-800 text-neutral-200' : 'bg-neutral-100 text-neutral-600' ?>"><?= $total_all ?></span>
      </a>

      <a href="<?= student_page_url(1, $search, $class_filter, 'Paid in full', '') ?>"
         class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-medium transition-all <?= ($status_filter === 'Paid in full') ? 'bg-[#16803b] text-white shadow-sm' : 'bg-white text-neutral-600 border border-neutral-200 hover:border-neutral-300 hover:text-neutral-900' ?>">
        <span class="w-1.5 h-1.5 rounded-full <?= ($status_filter === 'Paid in full') ? 'bg-emerald-200' : 'bg-[#16803b]' ?>"></span>
        <span>Paid in full</span>
        <span class="px-1.5 py-0.2 rounded-full text-[11px] <?= ($status_filter === 'Paid in full') ? 'bg-emerald-800 text-emerald-100' : 'bg-emerald-50 text-[#16803b]' ?>"><?= $count_paid_full ?></span>
      </a>

      <a href="<?= student_page_url(1, $search, $class_filter, 'Partially paid', '') ?>"
         class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-medium transition-all <?= ($status_filter === 'Partially paid') ? 'bg-[#915239] text-white shadow-sm' : 'bg-white text-neutral-600 border border-neutral-200 hover:border-neutral-300 hover:text-neutral-900' ?>">
        <span class="w-1.5 h-1.5 rounded-full <?= ($status_filter === 'Partially paid') ? 'bg-amber-200' : 'bg-[#915239]' ?>"></span>
        <span>Partially paid</span>
        <span class="px-1.5 py-0.2 rounded-full text-[11px] <?= ($status_filter === 'Partially paid') ? 'bg-[#733e2a] text-amber-100' : 'bg-amber-50 text-[#915239]' ?>"><?= $count_partial ?></span>
      </a>

      <a href="<?= student_page_url(1, $search, $class_filter, 'Not yet paid', '') ?>"
         class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-medium transition-all <?= ($status_filter === 'Not yet paid') ? 'bg-neutral-700 text-white shadow-sm' : 'bg-white text-neutral-600 border border-neutral-200 hover:border-neutral-300 hover:text-neutral-900' ?>">
        <span class="w-1.5 h-1.5 rounded-full <?= ($status_filter === 'Not yet paid') ? 'bg-neutral-300' : 'bg-neutral-400' ?>"></span>
        <span>Not yet paid</span>
        <span class="px-1.5 py-0.2 rounded-full text-[11px] <?= ($status_filter === 'Not yet paid') ? 'bg-neutral-800 text-neutral-200' : 'bg-neutral-100 text-neutral-600' ?>"><?= $count_unpaid ?></span>
      </a>

      <a href="<?= student_page_url(1, $search, $class_filter, '', '1') ?>"
         class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-medium transition-all <?= ($discount_filter === '1') ? 'bg-[#915239] text-white shadow-sm' : 'bg-white text-neutral-600 border border-neutral-200 hover:border-neutral-300 hover:text-neutral-900' ?>">
        <svg viewBox="0 0 24 24" class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        <span>With discounts</span>
        <span class="px-1.5 py-0.2 rounded-full text-[11px] <?= ($discount_filter === '1') ? 'bg-[#733e2a] text-amber-100' : 'bg-[#f7e7d8] text-[#915239]' ?>"><?= $count_discount ?></span>
      </a>
    </div>

    <!-- Filter & Search Toolbar -->
    <section class="mt-4">
      <form method="get" class="flex flex-wrap items-center gap-3">
        <input type="hidden" name="p" value="students">
        <?php if ($discount_filter): ?><input type="hidden" name="discount" value="<?= htmlspecialchars($discount_filter) ?>"><?php endif; ?>

        <!-- Search Input with Icon -->
        <div class="relative flex-1 min-w-[220px]">
          <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-neutral-400">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
          </span>
          <input type="text" name="search"
                 class="w-full pl-9 pr-3.5 py-2.5 bg-white border border-neutral-200 rounded-lg text-sm text-neutral-900 placeholder:text-neutral-400 focus:outline-none focus:ring-2 focus:ring-neutral-900 focus:border-transparent transition-all"
                 placeholder="Search student name, ID or guardian..."
                 value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
        </div>

        <!-- Class Filter Select -->
        <div class="relative min-w-[160px]">
          <select name="class" class="w-full appearance-none pl-3.5 pr-8 py-2.5 bg-white border border-neutral-200 rounded-lg text-sm text-neutral-800 focus:outline-none focus:ring-2 focus:ring-neutral-900 cursor-pointer transition-all">
            <option value="">All Classes</option>
            <?php foreach ($classes_data as $c): ?>
              <option value="<?= htmlspecialchars($c['name']) ?>" <?= $class_filter === $c['name'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($c['name']) ?> (<?= htmlspecialchars($c['section']) ?>)
              </option>
            <?php endforeach; ?>
          </select>
          <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-neutral-400">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
          </span>
        </div>

        <!-- Status Filter Select -->
        <div class="relative min-w-[160px]">
          <select name="status" class="w-full appearance-none pl-3.5 pr-8 py-2.5 bg-white border border-neutral-200 rounded-lg text-sm text-neutral-800 focus:outline-none focus:ring-2 focus:ring-neutral-900 cursor-pointer transition-all">
            <option value="">All Statuses</option>
            <option value="Paid in full" <?= $status_filter === 'Paid in full' ? 'selected' : '' ?>>Paid in full</option>
            <option value="Partially paid" <?= $status_filter === 'Partially paid' ? 'selected' : '' ?>>Partially paid</option>
            <option value="Not yet paid" <?= $status_filter === 'Not yet paid' ? 'selected' : '' ?>>Not yet paid</option>
          </select>
          <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-neutral-400">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
          </span>
        </div>

        <!-- Filter Action Button -->
        <button type="submit" class="ght-button ght-button--primary text-sm font-medium shrink-0">
          <span class="ght-button-icon">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 3H2l8 9.46V19l4 2v-8.54L22 3z"/></svg>
          </span>
          <span class="ght-button-label">Filter</span>
        </button>

        <!-- Clear Action -->
        <?php if ($has_active_filters): ?>
          <a href="index.php?p=students" class="ght-button ght-button--secondary text-sm font-medium text-neutral-600 hover:text-neutral-900 shrink-0">
            <span class="ght-button-label">Clear filters</span>
          </a>
        <?php endif; ?>
      </form>

      <!-- Active Filter Badges -->
      <?php if ($has_active_filters): ?>
        <div class="mt-3 flex flex-wrap items-center gap-2">
          <span class="text-xs text-neutral-500">Active filters:</span>
          <?php if ($search !== ''): ?>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-neutral-100 text-xs text-neutral-700">
              <span>Search: "<?= htmlspecialchars($search) ?>"</span>
              <a href="<?= student_page_url(1, '', $class_filter, $status_filter, $discount_filter) ?>" class="text-neutral-400 hover:text-neutral-900">&times;</a>
            </span>
          <?php endif; ?>
          <?php if ($class_filter !== ''): ?>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-neutral-100 text-xs text-neutral-700">
              <span>Class: <?= htmlspecialchars($class_filter) ?></span>
              <a href="<?= student_page_url(1, $search, '', $status_filter, $discount_filter) ?>" class="text-neutral-400 hover:text-neutral-900">&times;</a>
            </span>
          <?php endif; ?>
          <?php if ($status_filter !== ''): ?>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-neutral-100 text-xs text-neutral-700">
              <span>Status: <?= htmlspecialchars($status_filter) ?></span>
              <a href="<?= student_page_url(1, $search, $class_filter, '', $discount_filter) ?>" class="text-neutral-400 hover:text-neutral-900">&times;</a>
            </span>
          <?php endif; ?>
          <?php if ($discount_filter === '1'): ?>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-[#f7e7d8] text-xs text-[#915239]">
              <span>With discount</span>
              <a href="<?= student_page_url(1, $search, $class_filter, $status_filter, '') ?>" class="text-[#915239] hover:text-neutral-900">&times;</a>
            </span>
          <?php endif; ?>
          <a href="index.php?p=students" class="text-xs text-neutral-500 hover:text-neutral-900 underline ml-1">Reset all</a>
        </div>
      <?php endif; ?>

      <!-- Students Table -->
      <div class="mt-4 cn-table-wrap" style="border-radius: var(--ght-radius-lg); overflow: hidden; border: 1px solid var(--ght-color-border); background: var(--ght-color-surface);">
        <table class="cn-table" style="width: 100%; border-collapse: collapse; font-size: 14px;">
          <thead>
            <tr style="background: var(--ght-color-tertiary); text-transform: uppercase; font-size: 12px; letter-spacing: 0.02em; color: var(--ght-color-muted); border-bottom: 1px solid var(--ght-color-border);">
              <th style="padding: 12px 16px; text-align: left; font-weight: 500;">Student</th>
              <th style="padding: 12px 16px; text-align: left; font-weight: 500;">Class</th>
              <th style="padding: 12px 16px; text-align: left; font-weight: 500;">Guardian</th>
              <th style="padding: 12px 16px; text-align: right; font-weight: 500;">Amount Payable</th>
              <th style="padding: 12px 16px; text-align: right; font-weight: 500;">Paid</th>
              <th style="padding: 12px 16px; text-align: right; font-weight: 500;">Outstanding</th>
              <th style="padding: 12px 16px; text-align: center; font-weight: 500;">Status</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($paginated_students as $student): 
              $summary = $student['summary'];
              $concession = $student['concession'] ?? [];
              $has_discount = !empty($concession['amount']);
              
              if ($student['accountStatus'] === 'Paid in full') {
                  $status_tone = 'success';
              } elseif ($student['accountStatus'] === 'Partially paid') {
                  $status_tone = 'accent';
              } else {
                  $status_tone = 'neutral';
              }
            ?>
              <tr class="hover:bg-[#fafafa] transition-colors cursor-pointer group" onclick="window.location.href='index.php?p=student-profile&student=<?= urlencode($student['id']) ?>'" style="border-bottom: 1px solid var(--ght-color-border);">
                <td style="padding: 12px 16px;">
                  <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#f7e7d8] text-xs font-semibold text-[#915239] transition-transform group-hover:scale-105">
                      <?= htmlspecialchars($student['initials']) ?>
                    </span>
                    <div>
                      <div class="flex items-center gap-2">
                        <strong class="text-neutral-900 group-hover:text-black font-medium"><?= htmlspecialchars($student['name']) ?></strong>
                        <?php if ($has_discount): ?>
                          <span class="inline-flex items-center gap-0.5 rounded px-1.5 py-0.5 text-[10px] font-medium bg-[#f7e7d8] text-[#915239]" title="<?= htmlspecialchars($concession['rule'] ?? '') ?>">
                            <?= htmlspecialchars($concession['shortLabel'] ?? 'Discount') ?>
                          </span>
                        <?php endif; ?>
                      </div>
                      <span style="color: var(--ght-color-muted); font-size: 12px;"><?= htmlspecialchars($student['studentNumber']) ?></span>
                    </div>
                  </div>
                </td>
                <td style="padding: 12px 16px;">
                  <span class="ght-chip ght-chip--neutral"><?= htmlspecialchars($student['className']) ?></span>
                </td>
                <td style="padding: 12px 16px; color: var(--ght-color-muted); font-size: 13px;">
                  <?= htmlspecialchars($student['guardian']) ?>
                </td>
                <td style="padding: 12px 16px; text-align: right; font-weight: 500;">
                  <?= ght_format_naira($summary['payable']) ?>
                  <?php if ($has_discount): ?>
                    <div class="text-[11px] text-[#915239]">−<?= ght_format_naira($concession['amount']) ?> disc.</div>
                  <?php endif; ?>
                </td>
                <td style="padding: 12px 16px; text-align: right; color: #16803b; font-weight: 500;">
                  <?= ght_format_naira($summary['paid']) ?>
                </td>
                <td style="padding: 12px 16px; text-align: right; font-weight: 500; <?= $summary['outstanding'] > 0 ? 'text-[#915239]' : 'text-[#16803b]' ?>">
                  <?= ght_format_naira($summary['outstanding']) ?>
                </td>
                <td style="padding: 12px 16px; text-align: center;">
                  <span class="ght-chip ght-chip--<?= $status_tone ?>"><?= htmlspecialchars($student['accountStatus']) ?></span>
                </td>
              </tr>
            <?php endforeach; ?>

            <?php if (empty($paginated_students)): ?>
              <tr>
                <td colspan="7" style="padding: 48px 16px; text-align: center; color: var(--ght-color-muted);">
                  <div class="flex flex-col items-center justify-center">
                    <svg class="w-10 h-10 text-neutral-300 mb-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    <p class="font-medium text-neutral-800 m-0">No students match your criteria</p>
                    <p class="text-xs text-neutral-400 mt-1 mb-3">Try adjusting your search terms or filters</p>
                    <a href="index.php?p=students" class="ght-button ght-button--secondary text-xs font-medium"><span class="ght-button-label">Reset filters</span></a>
                  </div>
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
        
        <!-- Pagination UI -->
        <?php if ($total_students > 0): ?>
          <div class="px-4 py-3.5 bg-white border-t border-neutral-100 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
            <div class="text-neutral-500">
              Showing <span class="font-semibold text-neutral-900"><?= $offset + 1 ?></span> to <span class="font-semibold text-neutral-900"><?= min($offset + $per_page, $total_students) ?></span> of <span class="font-semibold text-neutral-900"><?= $total_students ?></span> students
            </div>

            <?php if ($total_pages > 1): ?>
              <div class="flex items-center gap-1.5">
                <!-- Previous Page Button -->
                <?php if ($current_page > 1): ?>
                  <a href="<?= student_page_url($current_page - 1, $search, $class_filter, $status_filter, $discount_filter) ?>"
                     class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-neutral-200 bg-white text-neutral-700 hover:bg-neutral-50 font-medium transition-colors">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
                    <span>Previous</span>
                  </a>
                <?php else: ?>
                  <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-neutral-100 bg-neutral-50 text-neutral-300 font-medium cursor-not-allowed">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
                    <span>Previous</span>
                  </span>
                <?php endif; ?>

                <!-- Page Number Pills -->
                <div class="flex items-center gap-1">
                  <?php
                  // Compute visible page numbers with ellipsis
                  $pages_to_show = [];
                  if ($total_pages <= 7) {
                      $pages_to_show = range(1, $total_pages);
                  } else {
                      if ($current_page <= 4) {
                          $pages_to_show = [1, 2, 3, 4, 5, '...', $total_pages];
                      } elseif ($current_page >= $total_pages - 3) {
                          $pages_to_show = [1, '...', $total_pages - 4, $total_pages - 3, $total_pages - 2, $total_pages - 1, $total_pages];
                      } else {
                          $pages_to_show = [1, '...', $current_page - 1, $current_page, $current_page + 1, '...', $total_pages];
                      }
                  }

                  foreach ($pages_to_show as $p):
                    if ($p === '...'):
                  ?>
                    <span class="inline-flex items-center justify-center w-8 h-8 text-neutral-400">…</span>
                  <?php elseif ($p === $current_page): ?>
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-[#171717] text-white font-semibold shadow-sm">
                      <?= $p ?>
                    </span>
                  <?php else: ?>
                    <a href="<?= student_page_url($p, $search, $class_filter, $status_filter, $discount_filter) ?>"
                       class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-neutral-200 bg-white text-neutral-700 hover:bg-neutral-50 font-medium transition-colors">
                      <?= $p ?>
                    </a>
                  <?php endif; endforeach; ?>
                </div>

                <!-- Next Page Button -->
                <?php if ($current_page < $total_pages): ?>
                  <a href="<?= student_page_url($current_page + 1, $search, $class_filter, $status_filter, $discount_filter) ?>"
                     class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-neutral-200 bg-white text-neutral-700 hover:bg-neutral-50 font-medium transition-colors">
                    <span>Next</span>
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                  </a>
                <?php else: ?>
                  <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-neutral-100 bg-neutral-50 text-neutral-300 font-medium cursor-not-allowed">
                    <span>Next</span>
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                  </span>
                <?php endif; ?>
              </div>
            <?php endif; ?>
          </div>
        <?php endif; ?>

      </div>
    </section>

  </div>
</div>

