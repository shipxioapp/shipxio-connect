/**
 * Shipxio Connect settings screen
 *
 * Two conveniences only: the WordPress color picker with a live preview of the
 * appearance settings, and copy buttons for the shortcodes. No setting is
 * saved from here; the form posts to options.php exactly as before.
 */

(function ($) {
    'use strict';

    function initPreview() {
        const preview = document.getElementById('shipxio-connect-preview');
        const color = document.getElementById('shipxio-connect-primary-color');
        const radius = document.getElementById('shipxio-connect-border-radius');

        if (!preview) {
            return function () {};
        }

        /** Only a valid hex reaches the preview, matching the server-side rule. */
        const applyColor = (value) => {
            if (/^#([0-9a-f]{3}|[0-9a-f]{6})$/i.test(String(value || '').trim())) {
                preview.style.setProperty('--shipxio-connect-preview-color', String(value).trim());
            }
        };

        const applyRadius = () => {
            if (!radius) {
                return;
            }

            const parsed = parseInt(radius.value, 10);
            const min = parseInt(radius.min, 10) || 0;
            const max = parseInt(radius.max, 10) || 24;

            if (Number.isFinite(parsed) && parsed >= min && parsed <= max) {
                preview.style.setProperty('--shipxio-connect-preview-radius', parsed + 'px');
            }
        };

        if (radius) {
            radius.addEventListener('input', applyRadius);
            radius.addEventListener('change', applyRadius);
        }

        if (color) {
            color.addEventListener('input', () => applyColor(color.value));
        }

        return applyColor;
    }

    function initCopyButtons() {
        const buttons = document.querySelectorAll('.shipxio-connect-copy');

        if (buttons.length === 0) {
            return;
        }

        /** Older browsers and insecure origins have no clipboard API. */
        const copy = (text) => {
            if (navigator.clipboard && window.isSecureContext) {
                return navigator.clipboard.writeText(text);
            }

            const field = document.createElement('textarea');
            field.value = text;
            field.setAttribute('readonly', 'readonly');
            field.style.position = 'fixed';
            field.style.opacity = '0';
            document.body.appendChild(field);
            field.select();

            try {
                document.execCommand('copy');
                return Promise.resolve();
            } catch (error) {
                return Promise.reject(error);
            } finally {
                document.body.removeChild(field);
            }
        };

        buttons.forEach((button) => {
            const label = button.querySelector('.shipxio-connect-copy-label');
            const original = label ? label.textContent : '';

            button.addEventListener('click', () => {
                copy(button.dataset.shipxioCopy || '').then(
                    () => {
                        if (!label) {
                            return;
                        }

                        button.classList.add('is-copied');
                        label.textContent = button.dataset.shipxioCopiedLabel || 'Copied';

                        window.setTimeout(() => {
                            button.classList.remove('is-copied');
                            label.textContent = original;
                        }, 1600);
                    },
                    () => {},
                );
            });
        });
    }

    $(function () {
        const applyColor = initPreview();
        const field = $('.shipxio-connect-color-field');

        if (field.length && typeof field.wpColorPicker === 'function') {
            field.wpColorPicker({
                change: function (event, ui) {
                    applyColor(ui.color.toString());
                },
                clear: function () {
                    applyColor($(this).data('default-color'));
                },
            });
        }

        initCopyButtons();
    });
})(jQuery);
