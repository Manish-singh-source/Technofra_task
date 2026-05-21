let deferredInstallPrompt = null;

const isIos = /iphone|ipad|ipod/i.test(window.navigator.userAgent);
const isStandalone =
  window.matchMedia('(display-mode: standalone)').matches
  || window.navigator.standalone === true;

function showElement(element) {
  if (element) element.classList.remove('hidden');
}

function hideElement(element) {
  if (element) element.classList.add('hidden');
}

function showInstalledToast() {
  const toast = document.getElementById('pwa-installed-toast');
  if (!toast) return;

  showElement(toast);
  setTimeout(() => hideElement(toast), 3000);
}

function initInstallPrompt() {
  const installButton = document.getElementById('pwa-install-btn');
  const installedBadge = document.getElementById('pwa-installed-badge');
  const installedText = `Installed ${String.fromCharCode(0x2713)}`;

  if (!installButton || !installedBadge) return;

  window.addEventListener('beforeinstallprompt', (event) => {
    event.preventDefault();
    deferredInstallPrompt = event;
    showElement(installButton);
  });

  installButton.addEventListener('click', async () => {
    if (!deferredInstallPrompt) return;

    deferredInstallPrompt.prompt();
    const choiceResult = await deferredInstallPrompt.userChoice;

    if (choiceResult.outcome === 'accepted') {
      hideElement(installButton);
      installedBadge.textContent = installedText;
      showElement(installedBadge);
      const toast = document.getElementById('pwa-installed-toast');
      if (toast) toast.textContent = installedText;
      showInstalledToast();
    }

    deferredInstallPrompt = null;
  });

  window.addEventListener('appinstalled', () => {
    hideElement(installButton);
    installedBadge.textContent = installedText;
    showElement(installedBadge);
    const toast = document.getElementById('pwa-installed-toast');
    if (toast) toast.textContent = installedText;
    showInstalledToast();
  });
}

function initIosBanner() {
  const iosBanner = document.getElementById('ios-install-banner');
  if (!iosBanner) return;

  if (isIos && !isStandalone) {
    showElement(iosBanner);
  } else {
    hideElement(iosBanner);
  }
}

function registerServiceWorker() {
  if (!('serviceWorker' in navigator)) return;

  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/sw.js').catch((error) => {
      console.error('Service worker registration failed:', error);
    });
  });
}

document.addEventListener('DOMContentLoaded', () => {
  registerServiceWorker();
  initInstallPrompt();
  initIosBanner();
});
