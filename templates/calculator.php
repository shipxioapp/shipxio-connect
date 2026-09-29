<?php
/** The prototype calculator and rates markup inside a WordPress shortcode. */

if (! defined('ABSPATH')) {
    exit;
}
?>
<div class="shipxio-connect" data-shipxio-api-base="<?php echo esc_url($api_base); ?>">
    <div class="page">
        <header class="page-header">
            <h1><?php echo esc_html__('Shipxio Connect', 'shipxio-connect'); ?></h1>
            <p><?php echo esc_html__('Shipping Simplified.', 'shipxio-connect'); ?></p>
        </header>

        <section class="section" aria-labelledby="<?php echo esc_attr($id); ?>-calculator-title">
            <div class="section-header">
                <h2 id="<?php echo esc_attr($id); ?>-calculator-title"><?php echo esc_html__('Shipping Calculator', 'shipxio-connect'); ?></h2>
                <p><?php echo esc_html__('Estimate shipping costs using the company’s current rates.', 'shipxio-connect'); ?></p>
            </div>

            <form class="calculator-form" data-shipxio-id="calculator-form" novalidate>
                <div class="field">
                    <label for="<?php echo esc_attr($id); ?>-item-search"><?php echo esc_html__('Item type', 'shipxio-connect'); ?></label>
                    <p class="field-help" id="<?php echo esc_attr($id); ?>-item-search-help"><?php echo esc_html__('Search by item name, description, or HS Code. Optional.', 'shipxio-connect'); ?></p>
                    <input id="<?php echo esc_attr($id); ?>-item-search" data-shipxio-id="item-search" type="search" placeholder="<?php echo esc_attr__('Search for an item', 'shipxio-connect'); ?>" autocomplete="off" autocapitalize="off" spellcheck="false" role="combobox" aria-expanded="false" aria-controls="<?php echo esc_attr($id); ?>-suggestions" aria-autocomplete="list" aria-describedby="<?php echo esc_attr($id); ?>-item-search-help">
                    <div id="<?php echo esc_attr($id); ?>-suggestions" data-shipxio-id="suggestions" class="suggestions" role="listbox" aria-label="<?php echo esc_attr__('Item type suggestions', 'shipxio-connect'); ?>" hidden></div>
                    <p data-shipxio-id="suggestions-status" class="visually-hidden" role="status" aria-live="polite"></p>
                    <div data-shipxio-id="selected-item" class="selected-item" hidden></div>
                </div>

                <div class="form-grid">
                    <div class="field">
                        <label for="<?php echo esc_attr($id); ?>-declared-value"><?php echo esc_html__('Item value (USD)', 'shipxio-connect'); ?></label>
                        <input id="<?php echo esc_attr($id); ?>-declared-value" name="declared_value_usd" type="number" inputmode="decimal" min="0" step="0.01" placeholder="60.00" required>
                    </div>
                    <div class="field">
                        <label for="<?php echo esc_attr($id); ?>-weight"><?php echo esc_html__('Weight (lb)', 'shipxio-connect'); ?></label>
                        <input id="<?php echo esc_attr($id); ?>-weight" name="weight" type="number" inputmode="decimal" min="0.01" step="0.01" placeholder="2" required>
                    </div>
                </div>
                <input data-shipxio-id="hs-code" name="hs_code" type="hidden">
                <button data-shipxio-id="calculate-button" type="submit"><?php echo esc_html__('Calculate Estimate', 'shipxio-connect'); ?></button>
            </form>

            <p data-shipxio-id="calculator-status" class="status status-error" role="alert" hidden></p>
            <div data-shipxio-id="estimate-result" class="estimate-result" aria-label="<?php echo esc_attr__('Shipping estimate', 'shipxio-connect'); ?>" tabindex="-1" hidden></div>
        </section>

        <section class="section" aria-labelledby="<?php echo esc_attr($id); ?>-shipping-rates-title">
            <div class="section-header">
                <h2 id="<?php echo esc_attr($id); ?>-shipping-rates-title"><?php echo esc_html__('Shipping Rates', 'shipxio-connect'); ?></h2>
                <p><?php echo esc_html__('View current shipping rates by package weight.', 'shipxio-connect'); ?></p>
            </div>
            <p data-shipxio-id="rates-status" class="status" role="status" aria-live="polite"><?php echo esc_html__('Loading shipping rates…', 'shipxio-connect'); ?></p>
            <div data-shipxio-id="rates-table-wrapper" class="rates-table-wrapper" hidden>
                <table class="rates-table">
                    <caption class="visually-hidden"><?php echo esc_html__('Shipping rates by package weight', 'shipxio-connect'); ?></caption>
                    <thead data-shipxio-id="rates-head"></thead>
                    <tbody data-shipxio-id="rates-list"></tbody>
                </table>
            </div>
        </section>
    </div>
</div>
