<link rel="stylesheet" href="styles/cn.css">
<?php
/**
 * pages/classes.php
 *
 * Required by index.php inside <main>, after the shell has already been
 * echoed — so no header()/redirect can happen from here. Mutations go
 * through _inc/handlers/classes.php via index.php's generic POST dispatch
 * (see the comment there); this file only ever renders a GET.
 */

$search = cure($_GET['search'] ?? '');
$section_filter = cure($_GET['section'] ?? '');

$all_classes = cn_get_classes();
$filtered = cn_filter_classes($all_classes, $search, $section_filter);
$grouped = cn_group_by_section($filtered);
$sections = cn_distinct_sections($all_classes);
?>
<div class="ght-dashboard-content">
  <div class="ght-page-enter">

    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
      <div>
        <p class="m-0 text-sm text-[#737373]">Class &amp; Academic Structure</p>
        <h2 class="ght-display mb-0 mt-2 text-3xl leading-none tracking-normal sm:text-4xl">Classes.</h2>
      </div>
      <label for="cn-add-class" class="ght-button ght-button--primary text-sm font-medium">
        <span class="ght-button-icon"><svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg></span>
        <span class="ght-button-label">Add class</span>
      </label>
    </div>

    <section class="mt-6">
      <form method="get" class="cn-toolbar">
        <input type="hidden" name="p" value="classes">
        <input type="hidden" name="section" value="<?= htmlspecialchars($section_filter) ?>">
        <input type="text" name="search" class="cn-input" placeholder="Search classes…" value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="ght-button ght-button--secondary text-sm font-medium"><span class="ght-button-label">Search</span></button>
        <?php if ($search !== '' || $section_filter !== ''): ?>
          <a href="index.php?p=classes" class="cn-btn-text">Clear</a>
        <?php endif; ?>
      </form>

      <nav class="ght-chart-views mb-6" aria-label="Filter classes by section">
        <a class="ght-chart-view-link<?= $section_filter === '' ? ' ght-chart-view-link--active' : '' ?>" href="index.php?p=classes&search=<?= urlencode($search) ?>">All sections</a>
        <?php foreach ($sections as $s): ?>
          <a class="ght-chart-view-link<?= $section_filter === $s ? ' ght-chart-view-link--active' : '' ?>" href="index.php?p=classes&section=<?= urlencode($s) ?>&search=<?= urlencode($search) ?>"><?= htmlspecialchars($s) ?></a>
        <?php endforeach; ?>
      </nav>

      <?php if (count($filtered) === 0): ?>
        <div class="ght-card t-resize cn-empty">No classes match your search or filter.</div>
      <?php else: ?>
        <div class="cn-list-wrap">
          <div class="cn-list-header"><span>Class</span><span>Status</span><span></span></div>
          <?php foreach ($grouped as $section_name => $classes_in_section): ?>
            <div class="cn-list-section-row"><?= htmlspecialchars($section_name) ?></div>
            <?php foreach ($classes_in_section as $class): ?>
              <div class="cn-list-row">
                <span><?= htmlspecialchars($class['name']) ?></span>
                <span><span class="ght-chip ght-chip--success">Active</span></span>
                <span class="cn-list-row-actions">
                  <label for="cn-edit-<?= htmlspecialchars($class['id']) ?>" class="cn-btn-text">Edit</label>
                  <label for="cn-archive-<?= htmlspecialchars($class['id']) ?>" class="cn-btn-danger-text">Archive</label>
                </span>
              </div>

              <!-- Edit modal (checkbox-hack, no JS) — pre-filled server-side -->
              <input type="checkbox" id="cn-edit-<?= htmlspecialchars($class['id']) ?>" class="cn-modal-toggle">
              <div class="cn-modal-overlay">
                <label for="cn-edit-<?= htmlspecialchars($class['id']) ?>" class="cn-modal-backdrop" aria-hidden="true"></label>
                <div class="cn-modal-panel">
                  <label for="cn-edit-<?= htmlspecialchars($class['id']) ?>" class="cn-modal-close" aria-label="Close">&times;</label>
                  <h2 class="cn-modal-title">Edit class</h2>
                  <form method="post" action="index.php?p=classes">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($class['id']) ?>">
                    <div class="cn-field-grid">
                      <div class="cn-field">
                        <label>Class name</label>
                        <input type="text" name="name" class="cn-input" value="<?= htmlspecialchars($class['name']) ?>" required>
                      </div>
                      <div class="cn-field">
                        <label>Section</label>
                        <input type="text" name="section" class="cn-input" list="cn-section-datalist" value="<?= htmlspecialchars($class['section']) ?>" required>
                      </div>
                    </div>
                    <div class="cn-modal-actions">
                      <label for="cn-edit-<?= htmlspecialchars($class['id']) ?>" class="ght-button ght-button--secondary text-sm font-medium"><span class="ght-button-label">Cancel</span></label>
                      <button type="submit" class="ght-button ght-button--primary text-sm font-medium"><span class="ght-button-label">Save class</span></button>
                    </div>
                  </form>
                </div>
              </div>

              <!-- Archive confirm modal -->
              <input type="checkbox" id="cn-archive-<?= htmlspecialchars($class['id']) ?>" class="cn-modal-toggle">
              <div class="cn-modal-overlay">
                <label for="cn-archive-<?= htmlspecialchars($class['id']) ?>" class="cn-modal-backdrop" aria-hidden="true"></label>
                <div class="cn-modal-panel">
                  <label for="cn-archive-<?= htmlspecialchars($class['id']) ?>" class="cn-modal-close" aria-label="Close">&times;</label>
                  <h2 class="cn-modal-title">Archive class</h2>
                  <p class="m-0 text-sm text-[#737373]">Archive &quot;<?= htmlspecialchars($class['name']) ?>&quot;? It will no longer be selectable when assigning fees.</p>
                  <form method="post" action="index.php?p=classes">
                    <input type="hidden" name="action" value="archive">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($class['id']) ?>">
                    <div class="cn-modal-actions">
                      <label for="cn-archive-<?= htmlspecialchars($class['id']) ?>" class="ght-button ght-button--secondary text-sm font-medium"><span class="ght-button-label">Cancel</span></label>
                      <button type="submit" class="ght-button text-sm font-medium" style="background: var(--ght-color-error); color: #fff;"><span class="ght-button-label">Archive</span></button>
                    </div>
                  </form>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>

  </div>
</div>

<datalist id="cn-section-datalist">
  <?php foreach ($sections as $s): ?><option value="<?= htmlspecialchars($s) ?>"></option><?php endforeach; ?>
</datalist>

<!-- Add class modal -->
<input type="checkbox" id="cn-add-class" class="cn-modal-toggle">
<div class="cn-modal-overlay">
  <label for="cn-add-class" class="cn-modal-backdrop" aria-hidden="true"></label>
  <div class="cn-modal-panel">
    <label for="cn-add-class" class="cn-modal-close" aria-label="Close">&times;</label>
    <h2 class="cn-modal-title">Add class</h2>
    <form method="post" action="index.php?p=classes">
      <input type="hidden" name="action" value="add">
      <div class="cn-field-grid">
        <div class="cn-field">
          <label>Class name</label>
          <input type="text" name="name" class="cn-input" placeholder="e.g. Year 6" required>
        </div>
        <div class="cn-field">
          <label>Section</label>
          <input type="text" name="section" class="cn-input" list="cn-section-datalist" placeholder="e.g. Primary" required>
        </div>
      </div>
      <div class="cn-modal-actions">
        <label for="cn-add-class" class="ght-button ght-button--secondary text-sm font-medium"><span class="ght-button-label">Cancel</span></label>
        <button type="submit" class="ght-button ght-button--primary text-sm font-medium"><span class="ght-button-label">Save class</span></button>
      </div>
    </form>
  </div>
</div>
