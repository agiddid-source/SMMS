import { GhtSidebar } from './Sidebar.js';

// Combines the responsive sidebar and supplied page content into one fragment.
export function GhtAppShell({ ghtNavigationConfig, ghtCurrentPath, ghtContent }) {
  const ghtShell = document.createDocumentFragment();
  ghtShell.appendChild(GhtSidebar({ ghtNavigationConfig, ghtCurrentPath }));
  const ghtMain = document.createElement('main');
  ghtMain.className = 'ght-app-main min-w-0 flex-1 lg:pl-[232px]';
  if (ghtContent instanceof Node) ghtMain.appendChild(ghtContent);
  else if (ghtContent) ghtMain.appendChild(document.createRange().createContextualFragment(ghtContent));
  ghtShell.appendChild(ghtMain);
  Object.defineProperty(ghtShell, 'toString', {
    value: () => {
      const ghtSerializationContainer = document.createElement('div');
      // Serializes cloned shell children for the existing HTML mount boundary.
      Array.from(ghtShell.childNodes).forEach((ghtChild) => ghtSerializationContainer.appendChild(ghtChild.cloneNode(true)));
      return ghtSerializationContainer.innerHTML;
    }
  });
  return ghtShell;
}
