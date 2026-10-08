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
    wp_register_style('shipxio-connect-redirect', $url . 'assets/css/redirect.css', array('shipxio-connect-vars'), SHIPXIO_CONNECT_VERSION);
    wp_register_script('shipxio-connect-redirect', $url . 'assets/js/redirect.js', array(), SHIPXIO_CONNECT_VERSION, array('in_footer' => true));

    // The appearance settings override the plugin's scoped design tokens only.
    $appearance = shipxio_connect_appearance_css();
    if ('' !== $appearance) {
        wp_add_inline_style('shipxio-connect', $appearance);
        wp_add_inline_style('shipxio-connect-redirect', str_replace('.shipxio-connect{', '.shipxio-connect-redirect{', $appearance));
    }
    $redirect_colors = '';
    foreach (array('text', 'subtext', 'link', 'spinner') as $kind) {
        $color = shipxio_connect_redirect_color($kind);
        if ('' !== $color) {
            $redirect_colors .= '--sxc-redirect-' . $kind . '-color:' . $color . ';';
        }
    }
    if ('' !== $redirect_colors) {
        wp_add_inline_style('shipxio-connect-redirect', '.shipxio-connect-redirect{' . $redirect_colors . '}');
    }

    wp_localize_script('shipxio-connect', 'shipxioConnectText', array(
        'weight' => __('Weight', 'shipxio-connect'),
        'weightSingular' => __('lb', 'shipxio-connect'),
        'weightPlural' => __('lbs', 'shipxio-connect'),
        'ratesFailed' => __('Shipping rates could not be loaded.', 'shipxio-connect'),
        'ratesUnavailable' => __('Shipping rates are not available right now.', 'shipxio-connect'),
        'shippingRate' => __('Shipping Rate', 'shipxio-connect'),
        'extraPound' => __('Each Extra lb', 'shipxio-connect'),
        /* translators: %s: currency code the shipping rates are quoted in, such as JMD. */
        'ratesInCurrency' => __('Shipping rates in %s', 'shipxio-connect'),
        'suggestionsFailed' => __('Suggestions could not be loaded.', 'shipxio-connect'),
        'searching' => __('Searching…', 'shipxio-connect'),
        'noMatches' => __('No matching items found.', 'shipxio-connect'),
        /* translators: %d: number of item type suggestions found, always 1 for this string. */
        'suggestionSingular' => __('%d item suggestion available.', 'shipxio-connect'),
        /* translators: %d: number of item type suggestions found. */
        'suggestionPlural' => __('%d item suggestions available.', 'shipxio-connect'),
        'clearSelected' => __('Clear selected item type', 'shipxio-connect'),
        'clear' => __('Clear', 'shipxio-connect'),
        /* translators: %s: name of the item type the visitor selected. */
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

/**
 * Build the CSS that applies the administrator's appearance settings.
 *
 * Only values that differ from the plugin defaults are emitted, so an
 * untouched installation renders exactly the stylesheet it ships with.
 */
function shipxio_connect_appearance_css()
{
    $declarations = '';

    $color = shipxio_connect_primary_color();
    if (SHIPXIO_CONNECT_DEFAULT_COLOR !== $color) {
        $hover = shipxio_connect_darken_hex_color($color, 0.6);
        $declarations .= '--sxc-clr-pri:' . $color . ';'
            . '--sxc-clr-pri-rgb:' . shipxio_connect_hex_color_rgb($color) . ';'
            . '--sxc-clr-sec:' . $hover . ';'
            . '--sxc-clr-sec-rgb:' . shipxio_connect_hex_color_rgb($hover) . ';';
    }

    $radius = shipxio_connect_border_radius();
    if (SHIPXIO_CONNECT_DEFAULT_RADIUS !== $radius) {
        $declarations .= '--sxc-brd-sm:' . $radius . 'px;'
            . '--sxc-brd-md:' . (int) round($radius * 1.5) . 'px;'
            . '--sxc-brd-lg:' . ($radius * 2) . 'px;';
    }

    // One setting drives both axes: the horizontal padding tracks the
    // vertical one so the button keeps its shape at any size.
    $padding = shipxio_connect_button_padding();
    if (SHIPXIO_CONNECT_DEFAULT_BUTTON_PADDING !== $padding) {
        $declarations .= '--sxc-btn-pad-y:' . $padding . 'px;'
            . '--sxc-btn-pad-x:' . ($padding + 16) . 'px;';
    }

    $button_text = shipxio_connect_button_text_color();
    if (SHIPXIO_CONNECT_DEFAULT_BUTTON_TEXT_COLOR !== $button_text) {
        $declarations .= '--sxc-clr-btn-text:' . $button_text . ';';
    }

    return '' === $declarations ? '' : '.shipxio-connect{' . $declarations . '}';
}

/**
 * Expand a sanitized hex color into the `r, g, b` triplet the translucent
 * tints in the stylesheet are built from.
 */
function shipxio_connect_hex_color_rgb($color)
{
    $hex = shipxio_connect_expand_hex_color($color);

    return hexdec(substr($hex, 0, 2)) . ', ' . hexdec(substr($hex, 2, 2)) . ', ' . hexdec(substr($hex, 4, 2));
}

/**
 * Derive the hover/active shade from the primary color. The plugin exposes a
 * single color setting, so the darker companion token is computed here rather
 * than configured separately.
 */
function shipxio_connect_darken_hex_color($color, $factor)
{
    $hex = shipxio_connect_expand_hex_color($color);
    $darkened = '#';
    for ($offset = 0; $offset < 6; $offset += 2) {
        $channel = (int) round(hexdec(substr($hex, $offset, 2)) * $factor);
        $darkened .= str_pad(dechex(max(0, min(255, $channel))), 2, '0', STR_PAD_LEFT);
    }

    return $darkened;
}

/** Normalize `#abc` and `#aabbcc` to six lowercase digits without the `#`. */
function shipxio_connect_expand_hex_color($color)
{
    $hex = strtolower(ltrim((string) $color, '#'));
    if (3 === strlen($hex)) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }

    return $hex;
}

/**
 * Read the shared shortcode attributes.
 *
 * Every shortcode parses its attributes here, so `show_intro` means the
 * same thing whichever one is used. The documented syntax is "true" and
 * "false"; other common boolean spellings are accepted rather than
 * silently treated as true.
 *
 * @param array|string $atts Raw shortcode attributes.
 * @param string       $tag  Shortcode tag, for the shortcode_atts filter.
 * @return bool Whether the built-in title and description block is rendered.
 */
function shipxio_connect_show_intro($atts, $tag)
{
    $parsed = shortcode_atts(array('show_intro' => 'true'), $atts, $tag);
    $value = $parsed['show_intro'];

    if (is_string($value)) {
        $normalized = strtolower(trim($value));
        if (in_array($normalized, array('false', 'no', 'off', '0', ''), true)) {
            return false;
        }
    }

    return wp_validate_boolean($value);
}

function shipxio_connect_render_sign_in_shortcode($atts = array(), $content = null)
{
    return shipxio_connect_render_redirect('sign_in');
}

function shipxio_connect_render_sign_up_shortcode($atts = array(), $content = null)
{
    return shipxio_connect_render_redirect('sign_up');
}

/** Redirect destinations come exclusively from the administrator's settings. */
function shipxio_connect_render_redirect($purpose)
{
    $redirect_url = shipxio_connect_redirect_url($purpose);
    if ('' === $redirect_url) {
        return '';
    }
    $heading = 'sign_in' === $purpose
        ? __('Opening your dashboard', 'shipxio-connect')
        : __('Opening your sign-up page', 'shipxio-connect');

    wp_enqueue_style('shipxio-connect-redirect');
    wp_enqueue_script('shipxio-connect-redirect');
    ob_start();
    include __DIR__ . '/../templates/redirect.php';
    return (string) ob_get_clean();
}

function shipxio_connect_render_shortcode($atts = array(), $content = null, $tag = 'shipxio_connect')
{
    return shipxio_connect_render_widget(array('calculator', 'rates'), shipxio_connect_show_intro($atts, $tag));
}

function shipxio_connect_render_calculator_shortcode($atts = array(), $content = null, $tag = 'shipxio_connect_calculator')
{
    return shipxio_connect_render_widget(array('calculator'), shipxio_connect_show_intro($atts, $tag));
}

function shipxio_connect_render_rates_shortcode($atts = array(), $content = null, $tag = 'shipxio_connect_rates')
{
    return shipxio_connect_render_widget(array('rates'), shipxio_connect_show_intro($atts, $tag));
}

/**
 * Render the shared widget markup for the requested sections. Every shortcode
 * variant renders through this one path, so they share the same assets,
 * markup, and browser behavior.
 *
 * @param string[] $sections
 * @param bool     $show_intro Whether each section renders its title block.
 */
function shipxio_connect_render_widget($sections, $show_intro = true)
{
    wp_enqueue_style('shipxio-connect');
    wp_enqueue_script('shipxio-connect');

    // Elementor may render widgets in separate editor requests on one page.
    $id = 'shipxio-connect-' . wp_generate_uuid4();
    $api_base = rest_url('shipxio-connect/v1');
    ob_start();
    include __DIR__ . '/../templates/widget.php';
    return (string) ob_get_clean();
}
