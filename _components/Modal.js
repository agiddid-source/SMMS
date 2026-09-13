// Reusable modal overlay, matching the ght-card visual language.
// Any module can use this for add/edit/confirm dialogs (Classes and Fee
// Management use it here; Bursar/Expenses can reuse it for the same
// purpose later).

const ghtModalTemplate = document.createElement('template');
ghtModalTemplate.innerHTML = `
  <div class="ght-modal-overlay fixed inset-0 z-[70] flex items-start justify-center overflow-y-auto bg-[#0a0a0a66] p-4 opacity-0 transition-opacity duration-200 sm:items-center" role="dialog" aria-modal="true">
    <div class="ght-modal-panel my-8 w-full max-w-lg -translate-y-2 rounded-2xl bg-white p-6 opacity-0 shadow-2xl transition-all duration-200">
      <div class="flex items-start justify-between gap-4">
        <h2 class="ght-modal-title m-0 text-xl font-medium tracking-[-.5px]"></h2>
        <button type="button" class="ght-icon-button ght-modal-close" aria-label="Close">
          <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
        </button>
      </div>
      <div class="ght-modal-body mt-5"></div>
    </div>
  </div>
`;

// Builds a closed, unattached modal overlay populated with the given content.
export function GhtModal({ ghtTitle, ghtContent }) {
  const ghtOverlay = ghtModalTemplate.content.cloneNode(true).firstElementChild;
  ghtOverlay.querySelector('.ght-modal-title').textContent = ghtTitle;
  const ghtBody = ghtOverlay.querySelector('.ght-modal-body');
  if (ghtContent instanceof Node) ghtBody.appendChild(ghtContent);
  else if (ghtContent) ghtBody.appendChild(document.createRange().createContextualFragment(ghtContent));
  Object.defineProperty(ghtOverlay, 'toString', { value: () => ghtOverlay.outerHTML });
  return ghtOverlay;
}

// Mounts a modal built by GhtModal, animates it in, and wires close affordances.
// Returns a close() function the caller can invoke on save/cancel.
export function GhtOpenModal(ghtOverlay, { ghtOnClose } = {}) {
  document.body.appendChild(ghtOverlay);
  const ghtPreviousOverflow = document.body.style.overflow;
  document.body.style.overflow = 'hidden';

  requestAnimationFrame(() => {
    ghtOverlay.classList.remove('opacity-0');
    ghtOverlay.querySelector('.ght-modal-panel').classList.remove('opacity-0', '-translate-y-2');
  });

  function ghtClose() {
    ghtOverlay.classList.add('opacity-0');
    ghtOverlay.querySelector('.ght-modal-panel').classList.add('opacity-0', '-translate-y-2');
    document.removeEventListener('keydown', ghtHandleEscape);
    window.setTimeout(() => {
      ghtOverlay.remove();
      document.body.style.overflow = ghtPreviousOverflow;
      if (ghtOnClose) ghtOnClose();
    }, 200);
  }

  function ghtHandleEscape(ghtEvent) {
    if (ghtEvent.key === 'Escape') ghtClose();
  }

  ghtOverlay.querySelector('.ght-modal-close').addEventListener('click', ghtClose);
  ghtOverlay.addEventListener('click', (ghtEvent) => {
    if (ghtEvent.target === ghtOverlay) ghtClose();
  });
  document.addEventListener('keydown', ghtHandleEscape);

  return ghtClose;
}

// Presents a Cancel/Confirm dialog for destructive actions (archive,
// deactivate, delete) and resolves true/false based on the user's choice —
// used instead of the browser's native confirm() so it matches the app's
// visual language.
export function GhtConfirm({ ghtTitle, ghtMessage, ghtConfirmLabel = 'Confirm', ghtTone = 'danger' }) {
  return new Promise((ghtResolve) => {
    const ghtBody = document.createElement('div');
    ghtBody.innerHTML = `
      <p class="m-0 text-sm leading-5 text-[#737373] ght-confirm-message"></p>
      <div class="mt-6 flex justify-end gap-2">
        <button type="button" class="ght-button ght-button--secondary text-sm font-medium ght-confirm-cancel">Cancel</button>
        <button type="button" class="ght-button text-sm font-medium ght-confirm-ok" style="background:${ghtTone === 'danger' ? '#ef4444' : 'var(--ght-color-primary)'}; color:#fff;"></button>
      </div>
    `;
    ghtBody.querySelector('.ght-confirm-message').textContent = ghtMessage;
    ghtBody.querySelector('.ght-confirm-ok').textContent = ghtConfirmLabel;

    const ghtOverlay = GhtModal({ ghtTitle, ghtContent: ghtBody });
    let ghtResult = false;
    const ghtClose = GhtOpenModal(ghtOverlay, { ghtOnClose: () => ghtResolve(ghtResult) });

    ghtBody.querySelector('.ght-confirm-cancel').addEventListener('click', () => ghtClose());
    ghtBody.querySelector('.ght-confirm-ok').addEventListener('click', () => {
      ghtResult = true;
      ghtClose();
    });
  });
}
