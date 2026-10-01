/**
 * Shipxio Connect settings screen
 *
 * Two conveniences only: the WordPress color picker with a live preview of the
 * appearance settings, and copy buttons for the shortcodes. No setting is
 * saved from here; the form posts to options.php exactly as before.
 */

(function ($) {
    'use strict';

    /** Which preview variable each color field drives. */
    const COLOR_FIELDS = {
        'shipxio-connect-primary-color': '--shipxio-connect-preview-color',
        'shipxio-connect-button-text-color': '--shipxio-connect-preview-text',
    };

    function initPreview() {
        const preview = document.getElementById('shipxio-connect-preview');
        const radius = document.getElementById('shipxio-connect-border-radius');
        const padding = document.getElementById('shipxio-connect-button-padding');

        if (!preview) {
            return function () {};
        }

        /** Only a valid hex reaches the preview, matching the server-side rule. */
        const applyColor = (field, value) => {
            const variable = COLOR_FIELDS[field && field.id];

            if (variable && /^#([0-9a-f]{3}|[0-9a-f]{6})$/i.test(String(value || '').trim())) {
                preview.style.setProperty(variable, String(value).trim());
            }
        };

        /** The horizontal padding tracks the vertical one, as it does live. */
        const applyPadding = () => {
            if (!padding) {
                return;
            }

            const parsed = parseInt(padding.value, 10);
            const min = parseInt(padding.min, 10) || 0;
            const max = parseInt(padding.max, 10) || 24;

            if (Number.isFinite(parsed) && parsed >= min && parsed <= max) {
                preview.style.setProperty('--shipxio-connect-preview-pad-y', parsed + 'px');
                preview.style.setProperty('--shipxio-connect-preview-pad-x', (parsed + 16) + 'px');
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

        if (padding) {
            padding.addEventListener('input', applyPadding);
            padding.addEventListener('change', applyPadding);
        }

        Object.keys(COLOR_FIELDS).forEach((id) => {
            const field = document.getElementById(id);

            if (field) {
                field.addEventListener('input', () => applyColor(field, field.value));
            }
        });

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
                    applyColor(this, ui.color.toString());
                },
                clear: function () {
                    applyColor(this, $(this).data('default-color'));
                },
            });
        }

        initCopyButtons();
    });
})(jQuery);
