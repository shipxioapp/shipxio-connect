/**
 * Shipxio Connect WordPress frontend
 *
 * Handles shipping rates, HS Code suggestions, and shipping estimates
 * for each Shipxio Connect shortcode on a WordPress page.
 *
 * Rules:
 * - The browser talks only to the plugin's REST endpoints. It never
 *   holds or sees the Website Integration credential.
 * - Shipxio owns every rate, total, and classification. Nothing here is
 *   recalculated; values are formatted for display only.
 * - Every value received from the API is escaped before it enters the DOM.
 */

(function () {
    'use strict';

    const widgetSelector = '.shipxio-connect[data-shipxio-api-base]';
    const initialized = new WeakSet();

    function initializeWidgets(scope) {
        const widgets = Array.from(scope.querySelectorAll(widgetSelector));

        if (scope.matches?.(widgetSelector)) {
            widgets.unshift(scope);
        }

        widgets.forEach((root) => {
        if (initialized.has(root)) {
            return;
        }

        initialized.add(root);
    const API_BASE = root.dataset.shipxioApiBase.replace(/\/$/, '');
    const field = (name) => root.querySelector(`[data-shipxio-id="${name}"]`);
    const t = (key, fallback) => typeof window.shipxioConnectText?.[key] === 'string'
        ? window.shipxioConnectText[key]
        : fallback;

    const SUGGESTION_DEBOUNCE_MS = 250;
    const MIN_SUGGESTION_LENGTH = 2;

    const ratesStatus = field('rates-status');
    const ratesHead = field('rates-head');
    const ratesList = field('rates-list');
    const ratesTableWrapper = field('rates-table-wrapper');

    const calculatorForm = field('calculator-form');
    const calculatorStatus = field('calculator-status');
    const calculateButton = field('calculate-button');
    const estimateResult = field('estimate-result');

    const itemSearch = field('item-search');
    const suggestions = field('suggestions');
    const suggestionsStatus = field('suggestions-status');
    const selectedItem = field('selected-item');
    const hsCodeInput = field('hs-code');

    let suggestionTimer = null;
    let suggestionController = null;
    let estimateController = null;
    let activeSuggestion = -1;

    /* ------------------------------------------------------------------ */
    /* Formatting                                                          */
    /* ------------------------------------------------------------------ */

    /**
     * Escape a value for safe insertion into HTML text or a quoted attribute.
     */
    function escapeHtml(value) {
        if (value === null || value === undefined) {
            return '';
        }

        return String(value).replace(/[&<>"']/g, (character) => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;',
        })[character]);
    }

    /**
     * Convert an API value to a number, treating a missing value as missing
     * rather than as zero. `Number(null)` is 0, which would display an absent
     * amount as a real one.
     */
    function toNumber(value) {
        if (value === null || value === undefined || value === '') {
            return null;
        }

        const number = Number(value);

        return Number.isFinite(number) ? number : null;
    }

    /**
     * Money arrives as a decimal string from Shipxio. It is only reformatted
     * for display here, never rounded into a different value.
     */
    function formatMoney(value, locale, prefix) {
        const number = toNumber(value);

        if (number === null) {
            return '—';
        }

        return prefix + number.toLocaleString(locale, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
    }

    function formatJmd(value) {
        return formatMoney(value, 'en-JM', 'J$');
    }

    function formatUsd(value) {
        return formatMoney(value, 'en-US', 'USD $');
    }

    /**
     * Rates arrive as decimal fractions such as "0.1500". Rounding the
     * multiplication keeps binary floating point out of the displayed value.
     */
    function formatPercent(value) {
        const number = toNumber(value);

        if (number === null) {
            return '—';
        }

        return `${(number * 100).toLocaleString('en-US', { maximumFractionDigits: 3 })}%`;
    }

    /**
     * Weight arrives as a number from rates and as a string from the estimate.
     */
    function formatWeight(value) {
        const number = toNumber(value);

        if (number === null) {
            return '—';
        }

        const text = number.toLocaleString('en-US', { maximumFractionDigits: 2 });

        return `${text} ${number === 1 ? t('weightSingular', 'lb') : t('weightPlural', 'lbs')}`;
    }

    /* ------------------------------------------------------------------ */
    /* Requests                                                            */
    /* ------------------------------------------------------------------ */

    /**
     * Read a JSON body without throwing when the response is not JSON.
     */
    async function readJson(response) {
        try {
            return await response.json();
        } catch {
            return null;
        }
    }

    /**
     * Resolve the message to show for a failed request, preferring the first
     * validation error Shipxio returned.
     */
    function failureMessage(data, fallback) {
        if (data && data.errors && typeof data.errors === 'object') {
            const first = Object.values(data.errors).flat()[0];

            if (typeof first === 'string' && first !== '') {
                return first;
            }
        }

        if (data && typeof data.message === 'string' && data.message !== '') {
            return data.message;
        }

        return fallback;
    }

    /**
     * Call a plugin REST endpoint and return its parsed JSON, throwing a
     * display-ready Error for any failure.
     */
    async function requestJson(path, options, fallback) {
        const response = await fetch(`${API_BASE}/${path}`, {
            ...options,
            credentials: 'omit',
            headers: { Accept: 'application/json', ...(options && options.headers) },
        });

        const data = await readJson(response);

        if (!response.ok) {
            throw new Error(failureMessage(data, fallback));
        }

        if (data === null) {
            throw new Error(fallback);
        }

        return data;
    }

    function isAbortError(error) {
        return error instanceof DOMException && error.name === 'AbortError';
    }

    /* ------------------------------------------------------------------ */
    /* Shipping rates                                                      */
    /* ------------------------------------------------------------------ */

    async function loadShippingRates() {
        const fallback = t('ratesFailed', 'Shipping rates could not be loaded.');

        try {
            const data = await requestJson('rates', {}, fallback);

            const rates = Array.isArray(data.rates) ? data.rates : [];

            if (!data.available || rates.length === 0) {
                ratesStatus.textContent = t('ratesUnavailable', 'Shipping rates are not available right now.');

                return;
            }

            renderRates(rates, typeof data.currency === 'string' ? data.currency : 'JMD');
        } catch (error) {
            ratesStatus.classList.add('status-error');
            ratesStatus.textContent = error instanceof Error ? error.message : fallback;
        }
    }

    /**
     * Render the rate ladder.
     *
     * The extra weight fee column is shown whenever any tier carries one.
     * Omitting it would imply a flat weight/price pair covers weights beyond
     * the listed tiers, which is not what the rate ladder means.
     */
    function renderRates(rates, currency) {
        const hasExtraWeightFee = rates.some(
            (rate) => rate.extra_weight_fee_jmd !== null && rate.extra_weight_fee_jmd !== undefined,
        );

        ratesHead.innerHTML = `
            <tr>
                <th scope="col">${escapeHtml(t('weight', 'Weight'))}</th>
                <th scope="col">${escapeHtml(t('shippingRate', 'Shipping Rate'))}</th>
                ${hasExtraWeightFee ? `<th scope="col">${escapeHtml(t('extraPound', 'Each Extra lb'))}</th>` : ''}
            </tr>
        `;

        ratesList.innerHTML = rates
            .map((rate) => {
                const extraWeightFee = hasExtraWeightFee
                    ? `<td>${rate.extra_weight_fee_jmd === null || rate.extra_weight_fee_jmd === undefined
                        ? '&mdash;'
                        : escapeHtml(formatJmd(rate.extra_weight_fee_jmd))}</td>`
                    : '';

                return `
                    <tr>
                        <td>${escapeHtml(formatWeight(rate.weight_lbs))}</td>
                        <td>${escapeHtml(formatJmd(rate.amount_jmd))}</td>
                        ${extraWeightFee}
                    </tr>
                `;
            })
            .join('');

        ratesStatus.hidden = true;
        ratesTableWrapper.hidden = false;
        ratesTableWrapper.setAttribute('aria-label', t('ratesInCurrency', 'Shipping rates in %s').replace('%s', currency));
    }

    /* ------------------------------------------------------------------ */
    /* HS Code suggestions                                                 */
    /* ------------------------------------------------------------------ */

    function closeSuggestions() {
        suggestions.hidden = true;
        suggestions.innerHTML = '';
        activeSuggestion = -1;

        itemSearch.setAttribute('aria-expanded', 'false');
        itemSearch.removeAttribute('aria-activedescendant');
    }

    function openSuggestions(html) {
        suggestions.innerHTML = html;
        suggestions.hidden = false;
        activeSuggestion = -1;

        itemSearch.setAttribute('aria-expanded', 'true');
        itemSearch.removeAttribute('aria-activedescendant');
    }

    function suggestionOptions() {
        return Array.from(suggestions.querySelectorAll('.suggestion'));
    }

    function setActiveSuggestion(index) {
        const options = suggestionOptions();

        if (options.length === 0) {
            return;
        }

        activeSuggestion = (index + options.length) % options.length;

        options.forEach((option, position) => {
            const isActive = position === activeSuggestion;

            option.classList.toggle('is-active', isActive);
            option.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });

        const active = options[activeSuggestion];

        itemSearch.setAttribute('aria-activedescendant', active.id);

        if (typeof active.scrollIntoView === 'function') {
            active.scrollIntoView({ block: 'nearest' });
        }
    }

    async function loadSuggestions(query) {
        // Abort the previous search so a slower earlier response can never
        // overwrite the results for what the visitor is typing now.
        if (suggestionController) {
            suggestionController.abort();
        }

        suggestionController = new AbortController();
        const controller = suggestionController;

        const fallback = t('suggestionsFailed', 'Suggestions could not be loaded.');

        openSuggestions(`<div class="suggestions-empty">${escapeHtml(t('searching', 'Searching…'))}</div>`);

        try {
            const data = await requestJson(
                `suggestions?query=${encodeURIComponent(query)}`,
                { signal: controller.signal },
                fallback,
            );

            if (controller !== suggestionController) {
                return;
            }

            const items = Array.isArray(data.suggestions) ? data.suggestions : [];

            if (items.length === 0) {
                const message = t('noMatches', 'No matching items found.');
                openSuggestions(`<div class="suggestions-empty">${escapeHtml(message)}</div>`);
                suggestionsStatus.textContent = message;

                return;
            }

            openSuggestions(items.map(renderSuggestion).join(''));

            suggestionsStatus.textContent = t(
                items.length === 1 ? 'suggestionSingular' : 'suggestionPlural',
                items.length === 1 ? '%d item suggestion available.' : '%d item suggestions available.',
            ).replace('%d', items.length);
        } catch (error) {
            if (isAbortError(error) || controller !== suggestionController) {
                return;
            }

            const message = error instanceof Error ? error.message : fallback;

            openSuggestions(`<div class="suggestions-empty">${escapeHtml(message)}</div>`);
            suggestionsStatus.textContent = message;
        }
    }

    function renderSuggestion(item, index) {
        const name = item.common_name || item.description || item.hs_code;
        const category = item.shipping_category || '';
        const detail = category ? `${item.hs_code} · ${category}` : String(item.hs_code ?? '');

        return `
            <button
                type="button"
                class="suggestion"
                id="${itemSearch.id}-suggestion-${index}"
                role="option"
                aria-selected="false"
                data-hs-code="${escapeHtml(item.hs_code)}"
                data-name="${escapeHtml(name)}"
                data-detail="${escapeHtml(detail)}"
            >
                <strong>${escapeHtml(name)}</strong>
                <span>${escapeHtml(detail)}</span>
            </button>
        `;
    }

    function selectSuggestion(button) {
        hsCodeInput.value = button.dataset.hsCode || '';
        itemSearch.value = button.dataset.name || '';

        selectedItem.innerHTML = `
            <div class="selected-item-text">
                <strong>${escapeHtml(button.dataset.name)}</strong>
                <span>${escapeHtml(button.dataset.detail)}</span>
            </div>

            <button type="button" class="selected-item-clear" aria-label="${escapeHtml(t('clearSelected', 'Clear selected item type'))}">
                ${escapeHtml(t('clear', 'Clear'))}
            </button>
        `;

        selectedItem.hidden = false;

        closeSuggestions();

        suggestionsStatus.textContent = t('selected', '%s selected.').replace('%s', button.dataset.name);
    }

    function clearSelection() {
        hsCodeInput.value = '';
        selectedItem.hidden = true;
        selectedItem.innerHTML = '';
    }

    itemSearch.addEventListener('input', () => {
        window.clearTimeout(suggestionTimer);

        if (suggestionController) {
            suggestionController.abort();
            suggestionController = null;
        }

        closeSuggestions();

        clearSelection();

        const query = itemSearch.value.trim();

        if (query.length < MIN_SUGGESTION_LENGTH) {
            return;
        }

        suggestionTimer = window.setTimeout(() => loadSuggestions(query), SUGGESTION_DEBOUNCE_MS);
    });

    itemSearch.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeSuggestions();

            return;
        }

        if (suggestions.hidden) {
            return;
        }

        if (event.key === 'ArrowDown') {
            event.preventDefault();
            setActiveSuggestion(activeSuggestion + 1);
        } else if (event.key === 'ArrowUp') {
            event.preventDefault();
            setActiveSuggestion(activeSuggestion - 1);
        } else if (event.key === 'Enter' && activeSuggestion >= 0) {
            // Only intercept Enter when a suggestion is highlighted, so the
            // form can still be submitted from the search field.
            event.preventDefault();
            selectSuggestion(suggestionOptions()[activeSuggestion]);
        }
    });

    suggestions.addEventListener('click', (event) => {
        const button = event.target.closest('.suggestion');

        if (button) {
            selectSuggestion(button);
        }
    });

    selectedItem.addEventListener('click', (event) => {
        if (event.target.closest('.selected-item-clear')) {
            clearSelection();
            itemSearch.focus();
        }
    });

    const onDocumentClick = (event) => {
        if (!root.isConnected) {
            document.removeEventListener('click', onDocumentClick);
            return;
        }

        if (!suggestions.hidden && !root.contains(event.target)) {
            closeSuggestions();
        }
    };

    document.addEventListener('click', onDocumentClick);

    /* ------------------------------------------------------------------ */
    /* Shipping estimate                                                   */
    /* ------------------------------------------------------------------ */

    calculatorForm.addEventListener('submit', async (event) => {
        event.preventDefault();

        closeSuggestions();

        calculatorStatus.hidden = true;
        estimateResult.hidden = true;

        // Replace any in-flight estimate so a stale result cannot land after
        // the one the visitor is waiting for.
        if (estimateController) {
            estimateController.abort();
        }

        estimateController = new AbortController();

        const controller = estimateController;

        calculateButton.disabled = true;
        calculateButton.textContent = t('calculating', 'Calculating…');

        const formData = new FormData(calculatorForm);

        const payload = {
            weight: formData.get('weight'),
            declared_value_usd: formData.get('declared_value_usd'),
        };

        if (hsCodeInput.value) {
            payload.hs_code = hsCodeInput.value;
        }

        const fallback = t('estimateFailed', 'The shipping estimate could not be calculated.');

        try {
            const data = await requestJson(
                'estimate',
                {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload),
                    signal: controller.signal,
                },
                fallback,
            );

            if (!data.estimate) {
                throw new Error(fallback);
            }

            renderEstimate(data.estimate);

            estimateResult.hidden = false;
            estimateResult.focus();
        } catch (error) {
            if (isAbortError(error)) {
                return;
            }

            calculatorStatus.textContent = error instanceof Error ? error.message : fallback;
            calculatorStatus.hidden = false;
        } finally {
            // A superseded request must not re-enable the button for the
            // request that replaced it.
            if (estimateController === controller) {
                calculateButton.disabled = false;
                calculateButton.textContent = t('calculateEstimate', 'Calculate Estimate');
            }
        }
    });

    /**
     * Render the authoritative estimate returned by Shipxio.
     *
     * Every figure below is displayed exactly as received. No total, fee, or
     * rate is derived, summed, or adjusted here.
     */
    function renderEstimate(estimate) {
        const classification = estimate.classification || {};
        const charges = estimate.charges || {};
        const declaredValue = estimate.declared_value || {};

        const classificationName =
            classification.common_name || classification.description || classification.hs_code || t('itemType', 'Item type');

        const classificationDetail = classification.shipping_category
            ? `${classification.hs_code} · ${classification.shipping_category}`
            : String(classification.hs_code ?? '');

        const miscellaneousFees = (Array.isArray(charges.miscellaneous_fees) ? charges.miscellaneous_fees : [])
            .map(
                (fee) => `
                    <div class="estimate-row">
                        <span>${escapeHtml(fee.fee_name)}</span>
                        <strong>${escapeHtml(formatJmd(fee.amount_jmd))}</strong>
                    </div>
                `,
            )
            .join('');

        // Shipxio reports how it resolved the HS Code. Anything other than the
        // package's own code means a default classification was applied.
        const fallbackNotice =
            classification.resolution_source === 'parcel'
                ? ''
                : `
                    <div class="estimate-notice">
                        ${escapeHtml(t('defaultClassification', 'A default item classification was used for this estimate.'))}
                    </div>
                `;

        estimateResult.innerHTML = `
            <section class="estimate-section">
                <div class="estimate-section-heading">
                    <h3>${escapeHtml(t('packageDetails', 'Package Details'))}</h3>
                    <p>${escapeHtml(t('packageDetailsHelp', 'Package details used for this estimate.'))}</p>
                </div>

                <div class="estimate-card">
                    <div class="estimate-grid">
                        <div>
                            <span>${escapeHtml(t('weight', 'Weight'))}</span>
                            <strong>${escapeHtml(formatWeight(estimate.weight_lbs))}</strong>
                        </div>

                        <div>
                            <span>${escapeHtml(t('itemValue', 'Item value'))}</span>
                            <strong>${escapeHtml(formatUsd(declaredValue.usd))}</strong>
                        </div>

                        <div>
                            <span>${escapeHtml(t('itemValueJmd', 'Item value (JMD)'))}</span>
                            <strong>${escapeHtml(formatJmd(declaredValue.jmd))}</strong>
                        </div>
                    </div>
                </div>
            </section>

            <section class="estimate-section">
                <div class="estimate-section-heading">
                    <h3>${escapeHtml(t('itemTypeHeading', 'Item Type'))}</h3>
                    <p>${escapeHtml(t('itemTypeHelp', 'Item type and customs rates used for this estimate.'))}</p>
                </div>

                <div class="estimate-card">
                    <div class="classification-heading">
                        <strong>${escapeHtml(classificationName)}</strong>
                        <span>${escapeHtml(classificationDetail)}</span>
                    </div>

                    <div class="estimate-grid estimate-grid-two">
                        <div>
                            <span>${escapeHtml(t('customsDutyRate', 'Customs duty rate'))}</span>
                            <strong>${escapeHtml(formatPercent(classification.customs_duty_rate))}</strong>
                        </div>

                        <div>
                            <span>${escapeHtml(t('gctRate', 'GCT rate'))}</span>
                            <strong>${escapeHtml(formatPercent(classification.gct_rate))}</strong>
                        </div>
                    </div>
                </div>

                ${fallbackNotice}
            </section>

            <section class="estimate-section">
                <div class="estimate-section-heading">
                    <h3>${escapeHtml(t('estimatedCharges', 'Estimated Charges'))}</h3>
                    <p>${escapeHtml(t('estimatedChargesHelp', 'Calculated using the company’s current rates.'))}</p>
                </div>

                <div class="estimate-card">
                    <div class="estimate-row">
                        <span>${escapeHtml(t('shippingFee', 'Shipping fee'))}</span>
                        <strong>${escapeHtml(formatJmd(charges.shipping_fee_jmd))}</strong>
                    </div>

                    <div class="estimate-row">
                        <div>
                            <span>${escapeHtml(t('customsDuty', 'Customs duty'))}</span>
                            <small>${escapeHtml(formatUsd(charges.customs_duty_usd))}</small>
                        </div>

                        <strong>${escapeHtml(formatJmd(charges.customs_duty_jmd))}</strong>
                    </div>

                    <div class="estimate-row">
                        <div>
                            <span>${escapeHtml(t('gct', 'GCT'))}</span>
                            <small>${escapeHtml(formatUsd(charges.gct_usd))}</small>
                        </div>

                        <strong>${escapeHtml(formatJmd(charges.gct_jmd))}</strong>
                    </div>

                    ${miscellaneousFees}

                    <div class="estimate-row">
                        <span>${escapeHtml(t('additionalFeesTotal', 'Additional fees total'))}</span>
                        <strong>${escapeHtml(formatJmd(charges.miscellaneous_fees_total_jmd))}</strong>
                    </div>

                    <div class="estimate-total">
                        <span>${escapeHtml(t('estimatedTotal', 'Estimated total'))}</span>
                        <strong>${escapeHtml(formatJmd(estimate.estimated_total_jmd))}</strong>
                    </div>
                </div>

                <div class="estimate-notes">
                    <p>${escapeHtml(t('estimateNote', 'This estimate is based on the package details and the company’s current rates. Final charges may change after the package is received and processed.'))}</p>

                    <p>${escapeHtml(t('excludedFeesNote', 'Storage, delivery, missing-invoice, and package-specific additional fees are not included.'))}</p>
                </div>
            </section>
        `;
    }

    loadShippingRates();
        });
    }

    let elementorHookRegistered = false;

    function registerElementorHook() {
        const hooks = window.elementorFrontend?.hooks;

        if (elementorHookRegistered || typeof hooks?.addAction !== 'function') {
            return;
        }

        elementorHookRegistered = true;
        hooks.addAction('frontend/element_ready/shortcode.default', ($scope) => {
            const scope = $scope?.[0] || $scope;

            if (scope?.querySelectorAll) {
                initializeWidgets(scope);
            }
        });
    }

    initializeWidgets(document);
    registerElementorHook();
    window.addEventListener('elementor/frontend/init', registerElementorHook);

    if (window.jQuery) {
        window.jQuery(window).on('elementor/frontend/init', registerElementorHook);
    }
})();
