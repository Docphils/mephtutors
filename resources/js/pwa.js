const INSTALL_BUTTON_SELECTOR = '[data-pwa-install]';
const MOBILE_BREAKPOINT_QUERY = '(max-width: 767px)';

let deferredInstallPrompt = null;
let serviceWorkerRegistration = null;
let isRefreshingForUpdate = false;

const mobileViewportQuery = window.matchMedia(MOBILE_BREAKPOINT_QUERY);

function isStandaloneMode() {
    return window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
}

function isiOS() {
    return /iphone|ipad|ipod/i.test(window.navigator.userAgent);
}

function isIOSInstallHintEligible() {
    return isiOS() && !isStandaloneMode() && !deferredInstallPrompt;
}

function canShowInstallButton() {
    return mobileViewportQuery.matches && !isStandaloneMode() && (Boolean(deferredInstallPrompt) || isIOSInstallHintEligible());
}

function setInstallButtonState(button, { busy = false } = {}) {
    const textElement = button.querySelector('[data-pwa-install-text]');
    const installLabel = button.dataset.installLabel || 'Install App';
    const iosLabel = button.dataset.installIosLabel || 'How to Install';
    const busyLabel = button.dataset.installBusyLabel || 'Preparing...';

    const shouldShow = canShowInstallButton();
    button.classList.toggle('hidden', !shouldShow);
    button.setAttribute('aria-hidden', shouldShow ? 'false' : 'true');

    if (!shouldShow) {
        return;
    }

    const usesIOSHint = !deferredInstallPrompt && isIOSInstallHintEligible();
    if (textElement) {
        textElement.textContent = busy ? busyLabel : (usesIOSHint ? iosLabel : installLabel);
    }

    button.disabled = busy;
}

function refreshInstallButtons() {
    document.querySelectorAll(INSTALL_BUTTON_SELECTOR).forEach((button) => setInstallButtonState(button));
}

function showIOSInstallHint() {
    window.alert('To install MephEd on iPhone: tap Share, then choose "Add to Home Screen".');
}

async function onInstallButtonClick(event) {
    const button = event.currentTarget;
    if (!(button instanceof HTMLButtonElement)) {
        return;
    }

    if (!deferredInstallPrompt) {
        if (isIOSInstallHintEligible()) {
            showIOSInstallHint();
        }
        return;
    }

    setInstallButtonState(button, { busy: true });

    try {
        await deferredInstallPrompt.prompt();
        await deferredInstallPrompt.userChoice;
    } catch (error) {
        console.error('PWA install prompt failed:', error);
    } finally {
        deferredInstallPrompt = null;
        refreshInstallButtons();
    }
}

function bindInstallButtons(scope = document) {
    const buttons = scope.querySelectorAll(`${INSTALL_BUTTON_SELECTOR}:not([data-pwa-bound="1"])`);
    buttons.forEach((button) => {
        button.dataset.pwaBound = '1';
        button.addEventListener('click', onInstallButtonClick);
    });

    refreshInstallButtons();
}

function promptWaitingWorkerToActivate() {
    if (!serviceWorkerRegistration?.waiting) {
        return;
    }

    serviceWorkerRegistration.waiting.postMessage({ type: 'SKIP_WAITING' });
}

async function registerServiceWorker() {
    if (!('serviceWorker' in window.navigator) || !window.isSecureContext) {
        return;
    }

    try {
        serviceWorkerRegistration = await window.navigator.serviceWorker.register('/sw.js', { scope: '/' });

        if (serviceWorkerRegistration.waiting) {
            promptWaitingWorkerToActivate();
        }

        serviceWorkerRegistration.addEventListener('updatefound', () => {
            const installingWorker = serviceWorkerRegistration?.installing;
            if (!installingWorker) {
                return;
            }

            installingWorker.addEventListener('statechange', () => {
                if (installingWorker.state === 'installed' && window.navigator.serviceWorker.controller) {
                    promptWaitingWorkerToActivate();
                }
            });
        });

        window.navigator.serviceWorker.addEventListener('controllerchange', () => {
            if (isRefreshingForUpdate) {
                return;
            }

            isRefreshingForUpdate = true;
            window.location.reload();
        });
    } catch (error) {
        console.error('PWA service worker registration failed:', error);
    }
}

window.addEventListener('beforeinstallprompt', (event) => {
    event.preventDefault();
    deferredInstallPrompt = event;
    refreshInstallButtons();
});

window.addEventListener('appinstalled', () => {
    deferredInstallPrompt = null;
    refreshInstallButtons();
});

if (typeof mobileViewportQuery.addEventListener === 'function') {
    mobileViewportQuery.addEventListener('change', () => refreshInstallButtons());
} else if (typeof mobileViewportQuery.addListener === 'function') {
    mobileViewportQuery.addListener(() => refreshInstallButtons());
}
document.addEventListener('DOMContentLoaded', () => bindInstallButtons());
document.addEventListener('livewire:navigated', () => bindInstallButtons());
window.addEventListener('load', () => registerServiceWorker());
document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'hidden') {
        promptWaitingWorkerToActivate();
    }
});
