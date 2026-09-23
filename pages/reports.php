<link rel="stylesheet" href="styles/cn.css">

<div class="ght-dashboard-content">
  <div class="ght-page-enter">

    <div>
      <p class="m-0 text-sm text-[#737373]">Turning transaction data into management information</p>
      <h2 class="ght-display mb-0 mt-2 text-3xl leading-none tracking-normal sm:text-4xl">Reports.</h2>
    </div>

    <nav class="ght-chart-views mt-6 mb-6" id="cn-report-tabs" aria-label="Choose a report"></nav>

    <section id="cn-report-content"></section>

    <p class="cn-prototype-note">Prototype &mdash; totals are computed live from the fixtures in this browser tab and reset on reload.</p>
  </div>
</div>

<?php cn_render_seed(); ?>
<script src="assets/js/cn-store.js"></script>
<script src="assets/js/cn-modal.js"></script>
<script src="assets/js/cn-reports.js"></script>
