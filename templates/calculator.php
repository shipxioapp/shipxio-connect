<?php
/** The prototype shipping calculator markup inside a WordPress shortcode. */

if (! defined('ABSPATH')) {
    exit;
}

/** @var string $id */
/** @var bool $show_intro */
?>
<?php // Without the heading the section still needs an accessible name. ?>
<?php if ($show_intro) : ?>
<section class="section" aria-labelledby="<?php echo esc_attr($id); ?>-calculator-title">
    <div class="section-header">
        <h2 id="<?php echo esc_attr($id); ?>-calculator-title"><?php echo esc_html__('Shipping Calculator', 'shipxio-connect'); ?></h2>
        <p><?php echo esc_html__('Estimate shipping costs using the company’s current rates.', 'shipxio-connect'); ?></p>
    </div>
<?php else : ?>
<section class="section" aria-label="<?php echo esc_attr__('Shipping Calculator', 'shipxio-connect'); ?>">
<?php endif; ?>

    <form class="calculator-form" data-shipxio-id="calculator-form" novalidate>
        <div class="field">
            <label for="<?php echo esc_attr($id); ?>-item-search"><?php echo esc_html__('Item type', 'shipxio-connect'); ?></label>
            <p class="field-help" id="<?php echo esc_attr($id); ?>-item-search-help"><?php echo esc_html__('Search by item name, description, or HS Code. Optional.', 'shipxio-connect'); ?></p>
            <div class="input-surface">
                <svg class="control-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                    <circle cx="10.5" cy="10.5" r="6.5" />
                    <path d="m16 16 4 4" />
                </svg>
                <input id="<?php echo esc_attr($id); ?>-item-search" data-shipxio-id="item-search" type="search" placeholder="<?php echo esc_attr__('Search for an item', 'shipxio-connect'); ?>" autocomplete="off" autocapitalize="off" spellcheck="false" role="combobox" aria-expanded="false" aria-controls="<?php echo esc_attr($id); ?>-suggestions" aria-autocomplete="list" aria-describedby="<?php echo esc_attr($id); ?>-item-search-help">
            </div>
            <div id="<?php echo esc_attr($id); ?>-suggestions" data-shipxio-id="suggestions" class="suggestions" role="listbox" aria-label="<?php echo esc_attr__('Item type suggestions', 'shipxio-connect'); ?>" hidden></div>
            <p data-shipxio-id="suggestions-status" class="visually-hidden" role="status" aria-live="polite"></p>
            <div data-shipxio-id="selected-item" class="selected-item" hidden></div>
        </div>

        <div class="form-grid">
            <div class="field">
                <label for="<?php echo esc_attr($id); ?>-declared-value"><?php echo esc_html__('Item value (USD)', 'shipxio-connect'); ?></label>
                <div class="input-surface">
                    <svg class="control-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                        <path d="M12 3v18m5-14H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H7" />
                    </svg>
                    <input id="<?php echo esc_attr($id); ?>-declared-value" name="declared_value_usd" type="number" inputmode="decimal" min="0" step="0.01" placeholder="60.00" required>
                </div>
            </div>
            <div class="field">
                <label for="<?php echo esc_attr($id); ?>-weight"><?php echo esc_html__('Weight (lb)', 'shipxio-connect'); ?></label>
                <div class="input-surface">
                    <svg class="control-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                        <path d="m12 3 9 5-9 5-9-5 9-5Zm-9 5v10l9 5 9-5V8M12 13v10M7.5 5.5l9 5" />
                    </svg>
                    <input id="<?php echo esc_attr($id); ?>-weight" name="weight" type="number" inputmode="decimal" min="0.01" step="0.01" placeholder="2" required>
                </div>
            </div>
        </div>
        <input data-shipxio-id="hs-code" name="hs_code" type="hidden">
        <button data-shipxio-id="calculate-button" type="submit"><?php echo esc_html__('Calculate Estimate', 'shipxio-connect'); ?></button>
    </form>

    <p data-shipxio-id="calculator-status" class="status status-error" role="alert" hidden></p>
    <div data-shipxio-id="estimate-result" class="estimate-result" aria-label="<?php echo esc_attr__('Shipping estimate', 'shipxio-connect'); ?>" tabindex="-1" hidden></div>
</section>
