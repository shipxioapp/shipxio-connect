<?php
/** The prototype shipping rates markup inside a WordPress shortcode. */

if (! defined('ABSPATH')) {
    exit;
}

/** @var string $id */
?>
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
