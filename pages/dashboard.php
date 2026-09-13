<?php

$dashboard = read_json('data/metrics.json');
$transactions = read_json('data/transactions.json') ?? [];

if (!$dashboard) {
    ?>
    <main class="mx-auto flex min-h-screen max-w-xl items-center p-6">
      <section class="ght-card t-resize w-full">
        <p class="m-0 text-sm text-[#737373]">Dashboard unavailable</p>
        <h1 class="ght-display mb-0 mt-2 text-3xl">We could not load Greenhill's financial overview.</h1>
        <p class="mb-0 mt-4 text-sm text-[#737373]">Check that data/metrics.json exists and is valid JSON.</p>
      </section>
    </main>
    <?php
    return;
}

$collection = $dashboard['collection'];
$collection_percent = round(($collection['collected'] / $collection['billed']) * 100);

$chart_view_key = cure($_GET['view'] ?? '');
if (!isset($dashboard['collectionViews'][$chart_view_key])) {
    $chart_view_key = isset($dashboard['collectionViews']['monthly']) ? 'monthly' : array_key_first($dashboard['collectionViews']);
}
$chart_view = $dashboard['collectionViews'][$chart_view_key];
$chart_max_value = max(array_merge($chart_view['values'], [1]));
$chart_unit = max($chart_view['values']) >= 1000000 ? 'Amounts in millions of naira' : 'Amounts in thousands of naira';

$pending_transactions = array_values(array_filter($transactions, fn($t) => $t['status'] === 'Pending'));
$review_rows = $pending_transactions ?: array_slice($transactions, 0, 3);
?>
<div class="ght-dashboard-content">
  <div class="ght-page-enter">

    <section class="ght-banner ght-card" aria-label="<?= htmlspecialchars($dashboard['school']) ?> financial overview">
      <div class="ght-banner-placeholder" aria-hidden="true"></div>
      <img src="assets/15.png" alt="" class="absolute inset-0 h-full w-full object-cover">
      <svg class="ght-banner-art" viewBox="0 0 320 240" fill="none" aria-hidden="true"><path d="M28 196c39-92 95-141 168-147 47-4 77 16 96 53" stroke="currentColor" stroke-width="2"/><path d="M18 212c66-49 131-52 193-8 35 25 64 28 94 19" stroke="currentColor" stroke-width="2"/><circle cx="185" cy="49" r="20" stroke="currentColor" stroke-width="2"/></svg>
      <div class="ght-banner-copy t-stagger is-shown">
        <p class="ght-banner-eyebrow t-stagger-line t-stagger-line--1"><?= htmlspecialchars($dashboard['term']) ?></p>
        <h1 class="ght-display ght-banner-title t-stagger-line t-stagger-line--2"><span class="ght-banner-school"><?= htmlspecialchars($dashboard['school']) ?></span><br>Finance overview</h1>
        <p class="ght-banner-updated t-stagger-line t-stagger-line--2"><?= htmlspecialchars($dashboard['updated']) ?></p>
      </div>
    </section>

    <div class="mt-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
      <div>
        <p class="m-0 text-sm text-[#737373]">Finance</p>
        <h2 class="ght-display mb-0 mt-2 text-3xl leading-none tracking-normal sm:text-4xl">Review these items.</h2>
      </div>
      <div class="flex flex-wrap items-center gap-3">
        <span class="ght-chip ght-chip--neutral"><?= htmlspecialchars($dashboard['term']) ?></span>
        <a class="ght-button ght-button--primary text-sm font-medium" href="index.php?p=coming-soon&module=Ledger">
          <span class="ght-button-icon"><svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
          <span class="ght-button-label">View ledger</span>
        </a>
      </div>
    </div>

    <section class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4" aria-label="Key financial figures">
      <?php foreach ($dashboard['metrics'] as $metric):
        $format_naira = ($metric['format'] ?? '') !== 'number';
        $value_text = $format_naira ? ght_format_naira($metric['value']) : number_format((float) $metric['value']);
        $chip_tone = $metric['tone'] === 'success' ? 'success' : ($metric['tone'] === 'accent' ? 'accent' : 'neutral');
        $action_href = '';
        $action_label = '';
        if ($metric['label'] === 'Outstanding fees') { $action_href = 'index.php?p=coming-soon&module=Ledger'; $action_label = 'Review ledger'; }
        elseif ($metric['label'] === "This month\xe2\x80\x99s expenses") { $action_href = 'index.php?p=coming-soon&module=Expenses'; $action_label = 'View expenses'; }
        $value_characters = preg_split('//u', $value_text, -1, PREG_SPLIT_NO_EMPTY);
        $value_count = count($value_characters);
      ?>
        <div class="ght-stagger-enter">
          <section class="ght-card t-resize ght-metric-card flex flex-col justify-between">
            <div class="flex items-start justify-between gap-3">
              <p class="m-0 text-sm text-[#737373]"><?= htmlspecialchars($metric['label']) ?></p>
              <span class="ght-metric-accent flex items-center justify-center" aria-hidden="true"><svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 16.5 9 11l3 3 8-8"/><path d="M15 6h5v5"/></svg></span>
            </div>
            <div>
              <p class="ght-metric-value t-digit-group is-animating m-0"><?php foreach ($value_characters as $index => $character):
                $stagger = $index === $value_count - 2 ? ' data-stagger="1"' : ($index === $value_count - 1 ? ' data-stagger="2"' : '');
              ?><span class="t-digit"<?= $stagger ?>><?= htmlspecialchars($character) ?></span><?php endforeach; ?></p>
              <p class="ght-metric-detail mb-0 mt-2 text-sm text-[#737373]"><?= htmlspecialchars($metric['detail']) ?></p>
            </div>
            <div class="ght-metric-chip"><span class="ght-chip ght-chip--<?= $chip_tone ?>"><?= htmlspecialchars($metric['change']) ?></span></div>
            <?php if ($action_href): ?><a class="ght-metric-action" href="<?= htmlspecialchars($action_href) ?>"><?= htmlspecialchars($action_label) ?> &rarr;</a><?php endif; ?>
          </section>
        </div>
      <?php endforeach; ?>
    </section>

    <section class="mt-8 grid grid-cols-1 gap-4 lg:grid-cols-[1.22fr_.78fr]">
      <div>
        <section class="ght-card t-resize">
          <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
              <p class="m-0 text-sm text-[#737373]">Weekly fees collected</p>
              <h3 class="m-0 mt-2 text-xl font-medium tracking-[-.5px]"><?= ght_format_compact_naira($collection['collected']) ?> collected this term</h3>
            </div>
            <span class="ght-chip ght-chip--success">On track</span>
          </div>

          <section class="ght-collection-chart mt-7">
            <div class="ght-chart-heading">
              <div>
                <p class="ght-chart-kicker">Fees collected</p>
                <p class="ght-chart-caption"><?= htmlspecialchars($chart_view['caption']) ?></p>
                <p class="ght-chart-period"><?= htmlspecialchars($chart_view['period']) ?></p>
              </div>
              <nav class="ght-chart-views" aria-label="Chart period">
                <?php foreach ($dashboard['collectionViews'] as $view_key => $view): ?>
                  <a class="ght-chart-view-link<?= $view_key === $chart_view_key ? ' ght-chart-view-link--active' : '' ?>" href="index.php?p=dashboard&view=<?= urlencode($view_key) ?>"><?= htmlspecialchars($view['label']) ?></a>
                <?php endforeach; ?>
              </nav>
            </div>
            <div class="ght-chart-grid">
              <?php foreach ($chart_view['values'] as $index => $value):
                $height = max(($value / $chart_max_value) * 100, 8);
                $bar_label = $chart_view['labels'][$index];
                $bar_amount = $value >= 1000000 ? '₦' . number_format($value / 1000000, ($value % 1000000) ? 1 : 0) . 'm' : '₦' . round($value / 1000) . 'k';
              ?>
                <div class="ght-chart-column">
                  <span class="ght-chart-value"><?= $bar_amount ?></span>
                  <div class="ght-chart-bar<?= $index === count($chart_view['values']) - 1 ? ' ght-chart-bar--current' : '' ?>" style="height: <?= $height ?>%" title="<?= htmlspecialchars($bar_label . ': ' . $bar_amount . ' collected') ?>"></div>
                  <span class="ght-chart-label"><?= htmlspecialchars($bar_label) ?></span>
                </div>
              <?php endforeach; ?>
            </div>
            <div class="ght-chart-legend">
              <span><i class="ght-chart-dot"></i>Collected</span>
              <span class="ght-chart-unit"><?= $chart_unit ?></span>
              <span>Highest collection: <?= '₦' . ($chart_max_value >= 1000000 ? number_format($chart_max_value / 1000000, ($chart_max_value % 1000000) ? 1 : 0) . 'm' : round($chart_max_value / 1000) . 'k') ?></span>
            </div>
          </section>

          <div class="mt-6 grid grid-cols-1 gap-4 border-t border-b-color pt-5 sm:grid-cols-3">
            <div><p class="m-0 text-xs text-[#737373]">Billed</p><p class="mb-0 mt-1 text-sm font-medium"><?= ght_format_compact_naira($collection['billed']) ?></p></div>
            <div><p class="m-0 text-xs text-[#737373]">Collected</p><p class="mb-0 mt-1 text-sm font-medium text-[#16803b]"><?= ght_format_compact_naira($collection['collected']) ?></p></div>
            <div><p class="m-0 text-xs text-[#737373]">Remaining</p><p class="mb-0 mt-1 text-sm font-medium"><?= ght_format_compact_naira($collection['due']) ?></p></div>
          </div>
        </section>
      </div>

      <div>
        <section class="ght-card t-resize">
          <div class="flex items-center justify-between gap-4">
            <div>
              <p class="m-0 text-sm text-[#737373]">Fees collected</p>
              <h3 class="m-0 mt-2 text-xl font-medium tracking-[-.5px]"><?= $collection_percent ?>% received</h3>
            </div>
            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full border-[5px] border-[#f7e7d8] text-xs font-semibold text-[#915239]"><?= $collection_percent ?>%</span>
          </div>
          <div class="mt-6">
            <div class="flex justify-between gap-3 text-sm"><span class="text-[#737373]">Collected</span><span class="font-medium"><?= ght_format_naira($collection['collected']) ?></span></div>
            <div class="ght-progress-track mt-3"><div class="ght-progress-fill" style="width:<?= $collection_percent ?>%"></div></div>
            <p class="mb-0 mt-3 text-xs text-[#737373]"><?= ght_format_naira($collection['due']) ?> remains across 47 families.</p>
          </div>
          <div class="mt-6 border-t border-b-color pt-5">
            <p class="m-0 text-sm font-medium">Next step</p>
            <p class="mb-0 mt-2 text-sm leading-5 text-[#737373]">Follow up on 12 overdue accounts.</p>
          </div>
        </section>
      </div>
    </section>

    <section class="mt-8 grid grid-cols-1 gap-8 lg:grid-cols-[1.22fr_.78fr]">
      <div>
        <section class="ght-card t-resize">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="m-0 text-sm text-[#737373]">Recent payments</p>
              <h3 class="m-0 mt-2 text-xl font-medium tracking-[-.5px]">Recent payment activity</h3>
            </div>
            <a class="ght-transaction-link min-h-11 pt-2 text-sm font-medium" href="index.php?p=student-profile&student=sodiq-adeyemi">View profile</a>
          </div>
          <ul class="m-0 mt-3 list-none divide-y divide-b-color p-0">
            <?php foreach ($transactions as $transaction):
              $tone = $transaction['status'] === 'Paid' ? 'success' : (in_array($transaction['status'], ['Concession', 'Discount']) ? 'accent' : 'neutral');
              $href = $transaction['name'] === 'Sodiq Adeyemi' ? 'index.php?p=student-profile&student=sodiq-adeyemi' : '';
            ?>
              <li class="ght-transaction-row">
                <<?= $href ? 'a' : 'div' ?> class="ght-transaction-row-link"<?= $href ? ' href="' . htmlspecialchars($href) . '"' : '' ?>>
                  <span class="ght-transaction-initials flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-b-color text-xs font-medium text-[#262626]"><?= htmlspecialchars($transaction['initials']) ?></span>
                  <span class="min-w-0 flex-1">
                    <span class="ght-transaction-name block truncate text-sm font-medium"><?= htmlspecialchars($transaction['name']) ?></span>
                    <span class="ght-transaction-context mt-1 block truncate text-xs text-[#737373]"><?= htmlspecialchars($transaction['context'] . ' · ' . $transaction['time']) ?></span>
                  </span>
                  <span class="text-left sm:text-right">
                    <span class="ght-transaction-amount block text-sm font-medium"><?= ght_format_naira($transaction['amount']) ?></span>
                    <span class="ght-transaction-chip mt-1 block"><span class="ght-chip ght-chip--<?= $tone ?>"><?= htmlspecialchars($transaction['status']) ?></span></span>
                  </span>
                </<?= $href ? 'a' : 'div' ?>>
              </li>
            <?php endforeach; ?>
          </ul>
        </section>
      </div>

      <div>
        <div class="mb-4">
          <p class="m-0 text-sm text-[#737373]">Other modules</p>
          <h3 class="m-0 mt-2 text-xl font-medium tracking-[-.5px]">Not available yet.</h3>
        </div>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-1">
          <?php foreach ($dashboard['moduleSummaries'] as $module): ?>
            <section class="ght-card t-resize flex min-h-[142px] flex-col justify-between">
              <div class="flex items-start justify-between gap-3">
                <div>
                  <h3 class="m-0 text-base font-medium"><?= htmlspecialchars($module['title']) ?></h3>
                  <p class="mb-0 mt-2 text-sm leading-5 text-[#737373]"><?= htmlspecialchars($module['description']) ?></p>
                </div>
                <span class="ght-empty-orb flex shrink-0 items-center justify-center text-[#915239]" aria-hidden="true"><svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 5v14M5 12h14"/></svg></span>
              </div>
              <p class="mb-0 mt-4 text-xs font-medium text-[#737373]"><?= htmlspecialchars($module['detail']) ?></p>
            </section>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <section class="ght-card t-resize ght-dashboard-review">
      <div class="flex items-start justify-between gap-3">
        <div>
          <p class="m-0 text-sm text-[#737373]">Payment review</p>
          <h2 class="m-0 mt-2 text-xl font-medium tracking-[-.5px]">Recent payments</h2>
        </div>
        <span class="ght-chip ght-chip--<?= $pending_transactions ? 'accent' : 'success' ?>"><?= $pending_transactions ? count($pending_transactions) . ' pending' : 'All clear' ?></span>
      </div>
      <ul class="ght-dashboard-review-list m-0 mt-5 list-none divide-y divide-b-color p-0">
        <?php foreach ($review_rows as $transaction): ?>
          <li>
            <div><strong><?= htmlspecialchars($transaction['name']) ?></strong><small><?= htmlspecialchars($transaction['context'] . ' · ' . $transaction['time']) ?></small></div>
            <span><?= ght_format_naira($transaction['amount']) ?></span>
          </li>
        <?php endforeach; ?>
      </ul>
      <a class="ght-dashboard-inline-link mt-5 inline-flex text-sm font-medium" href="index.php?p=coming-soon&module=Payments">View payments <span aria-hidden="true">&rarr;</span></a>
    </section>

  </div>
</div>
