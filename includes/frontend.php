<?php
/** Frontend assets and shortcode. */

if (! defined('ABSPATH')) {
    exit;
}

function shipxio_connect_register_assets()
{
    $url = plugin_dir_url(SHIPXIO_CONNECT_FILE);
    wp_register_style('shipxio-connect-vars', $url . 'assets/css/var.css', array(), SHIPXIO_CONNECT_VERSION);
    wp_register_style('shipxio-connect', $url . 'assets/css/app.css', array('shipxio-connect-vars'), SHIPXIO_CONNECT_VERSION);
    wp_register_script('shipxio-connect', $url . 'assets/js/app.js', array(), SHIPXIO_CONNECT_VERSION, array('in_footer' => true));
    wp_localize_script('shipxio-connect', 'shipxioConnectText', array(
        'weight' => __('Weight', 'shipxio-connect'),
        'weightSingular' => __('lb', 'shipxio-connect'),
        'weightPlural' => __('lbs', 'shipxio-connect'),
        'ratesFailed' => __('Shipping rates could not be loaded.', 'shipxio-connect'),
        'ratesUnavailable' => __('Shipping rates are not available right now.', 'shipxio-connect'),
        'shippingRate' => __('Shipping Rate', 'shipxio-connect'),
        'extraPound' => __('Each Extra lb', 'shipxio-connect'),
        'ratesInCurrency' => __('Shipping rates in %s', 'shipxio-connect'),
        'suggestionsFailed' => __('Suggestions could not be loaded.', 'shipxio-connect'),
        'searching' => __('Searching…', 'shipxio-connect'),
        'noMatches' => __('No matching items found.', 'shipxio-connect'),
        'suggestionSingular' => __('%d item suggestion available.', 'shipxio-connect'),
        'suggestionPlural' => __('%d item suggestions available.', 'shipxio-connect'),
        'clearSelected' => __('Clear selected item type', 'shipxio-connect'),
        'clear' => __('Clear', 'shipxio-connect'),
        'selected' => __('%s selected.', 'shipxio-connect'),
        'calculating' => __('Calculating…', 'shipxio-connect'),
        'calculateEstimate' => __('Calculate Estimate', 'shipxio-connect'),
        'estimateFailed' => __('The shipping estimate could not be calculated.', 'shipxio-connect'),
        'itemType' => __('Item type', 'shipxio-connect'),
        'defaultClassification' => __('A default item classification was used for this estimate.', 'shipxio-connect'),
        'packageDetails' => __('Package Details', 'shipxio-connect'),
        'packageDetailsHelp' => __('Package details used for this estimate.', 'shipxio-connect'),
        'itemValue' => __('Item value', 'shipxio-connect'),
        'itemValueJmd' => __('Item value (JMD)', 'shipxio-connect'),
        'itemTypeHeading' => __('Item Type', 'shipxio-connect'),
        'itemTypeHelp' => __('Item type and customs rates used for this estimate.', 'shipxio-connect'),
        'customsDutyRate' => __('Customs duty rate', 'shipxio-connect'),
        'gctRate' => __('GCT rate', 'shipxio-connect'),
        'estimatedCharges' => __('Estimated Charges', 'shipxio-connect'),
        'estimatedChargesHelp' => __('Calculated using the company’s current rates.', 'shipxio-connect'),
        'shippingFee' => __('Shipping fee', 'shipxio-connect'),
        'customsDuty' => __('Customs duty', 'shipxio-connect'),
        'gct' => __('GCT', 'shipxio-connect'),
        'additionalFeesTotal' => __('Additional fees total', 'shipxio-connect'),
        'estimatedTotal' => __('Estimated total', 'shipxio-connect'),
        'estimateNote' => __('This estimate is based on the package details and the company’s current rates. Final charges may change after the package is received and processed.', 'shipxio-connect'),
        'excludedFeesNote' => __('Storage, delivery, missing-invoice, and package-specific additional fees are not included.', 'shipxio-connect'),
    ));
}

function shipxio_connect_render_shortcode()
{
    wp_enqueue_style('shipxio-connect');
    wp_enqueue_script('shipxio-connect');

    // Elementor may render widgets in separate editor requests on one page.
    $id = 'shipxio-connect-' . wp_generate_uuid4();
    $api_base = rest_url('shipxio-connect/v1');
    ob_start();
    include __DIR__ . '/../templates/calculator.php';
    return (string) ob_get_clean();
}
