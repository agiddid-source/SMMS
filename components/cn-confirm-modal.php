<?php
/**
 * components/cn-confirm-modal.php
 *
 * One confirm dialog per page, reused for every destructive action on it —
 * the title, body and button label are filled in at click time by
 * CnConfirm (assets/js/cn-modal.js). Include it once, near the end of a
 * page, and ask for confirmation like this:
 *
 *   new CnConfirm('cn-confirm-modal').ask({
 *     title: 'Archive class',
 *     body: 'Archive Year 6?',
 *     confirmLabel: 'Archive class',
 *     onConfirm: function () { … }
 *   });
 */
?>
<div class="cn-modal" id="cn-confirm-modal" role="dialog" aria-modal="true" aria-labelledby="cn-confirm-title" hidden>
  <div class="cn-modal-backdrop" data-cn-dismiss></div>
  <div class="cn-modal-panel cn-modal-panel--narrow">
    <button type="button" class="cn-modal-close" data-cn-dismiss aria-label="Close">&times;</button>
    <h2 class="cn-modal-title" id="cn-confirm-title" data-cn-confirm-title></h2>
    <p class="cn-modal-body" data-cn-confirm-body></p>
    <div class="cn-modal-actions">
      <button type="button" class="ght-button ght-button--secondary text-sm font-medium" data-cn-dismiss><span class="ght-button-label">Cancel</span></button>
      <button type="button" class="ght-button cn-button--danger text-sm font-medium" data-cn-confirm-action><span class="ght-button-label">Confirm</span></button>
    </div>
  </div>
</div>
