// Static button layout with separate icon and label insertion points.
const ghtButtonTemplate = document.createElement('template');
ghtButtonTemplate.innerHTML = '<button class="ght-button text-sm font-medium"><span class="ght-button-icon"></span><span class="ght-button-label"></span></button>';

// Clones the button, applies its tone and type, then inserts icon and attributes.
export function GhtButton({ ghtLabel, ghtTone = 'primary', ghtIcon = '', ghtType = 'button', ghtAttributes = '' }) {
  const ghtButton = ghtButtonTemplate.content.cloneNode(true).firstElementChild;
  ghtButton.type = ghtType;
  ghtButton.classList.add(`ght-button--${ghtTone}`);
  ghtButton.querySelector('.ght-button-label').textContent = ghtLabel;
  if (ghtIcon) ghtButton.querySelector('.ght-button-icon').appendChild(document.createRange().createContextualFragment(ghtIcon));
  else ghtButton.querySelector('.ght-button-icon').remove();
  if (ghtAttributes) {
    const ghtAttributesElement = document.createRange().createContextualFragment(`<span ${ghtAttributes}></span>`).firstElementChild;
    // Transfers caller-supplied attributes such as IDs onto the button.
    Array.from(ghtAttributesElement.attributes).forEach((ghtAttribute) => ghtButton.setAttribute(ghtAttribute.name, ghtAttribute.value));
  }
  Object.defineProperty(ghtButton, 'toString', { value: () => ghtButton.outerHTML });
  return ghtButton;
}
