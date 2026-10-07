/** The automatic redirect and manual fallback share the rendered link URL. */
(function () {
    'use strict';

    document.querySelectorAll('[data-shipxio-redirect]').forEach((component) => {
        const continueLink = component.querySelector('.shipxio-connect-redirect__continue');

        if (!continueLink) {
            return;
        }

        window.setTimeout(() => {
            window.location.replace(continueLink.href);
        }, 900);
    });
})();
