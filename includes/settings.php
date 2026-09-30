<?php
/** WordPress-owned connection and appearance settings. */

if (! defined('ABSPATH')) {
    exit;
}

function shipxio_connect_register_settings()
{
    register_setting('shipxio_connect', 'shipxio_connect_base_url', array(
        'type'              => 'string',
        'sanitize_callback' => 'shipxio_connect_sanitize_base_url',
        'default'           => '',
        'show_in_rest'      => false,
    ));
    register_setting('shipxio_connect', 'shipxio_connect_credential', array(
        'type'              => 'string',
        'sanitize_callback' => 'shipxio_connect_sanitize_credential',
        'default'           => '',
        'show_in_rest'      => false,
    ));
    register_setting('shipxio_connect', 'shipxio_connect_primary_color', array(
        'type'              => 'string',
        'sanitize_callback' => 'shipxio_connect_sanitize_primary_color',
        'default'           => SHIPXIO_CONNECT_DEFAULT_COLOR,
        'show_in_rest'      => false,
    ));
    register_setting('shipxio_connect', 'shipxio_connect_border_radius', array(
        'type'              => 'integer',
        'sanitize_callback' => 'shipxio_connect_sanitize_border_radius',
        'default'           => SHIPXIO_CONNECT_DEFAULT_RADIUS,
        'show_in_rest'      => false,
    ));

    // The sections group the fields; the settings page draws its own cards.
    add_settings_section('shipxio_connect_connection', '', '__return_false', 'shipxio-connect');
    add_settings_field(
        'shipxio_connect_base_url',
        __('Shipxio base URL', 'shipxio-connect'),
        'shipxio_connect_render_base_url_field',
        'shipxio-connect',
        'shipxio_connect_connection',
        array('label_for' => 'shipxio-connect-base-url')
    );
    add_settings_field(
        'shipxio_connect_credential',
        __('Website Integration credential', 'shipxio-connect'),
        'shipxio_connect_render_credential_field',
        'shipxio-connect',
        'shipxio_connect_connection',
        array('label_for' => 'shipxio-connect-credential')
    );

    add_settings_section('shipxio_connect_appearance', '', '__return_false', 'shipxio-connect');
    add_settings_field(
        'shipxio_connect_primary_color',
        __('Primary color', 'shipxio-connect'),
        'shipxio_connect_render_primary_color_field',
        'shipxio-connect',
        'shipxio_connect_appearance',
        array('label_for' => 'shipxio-connect-primary-color')
    );
    add_settings_field(
        'shipxio_connect_border_radius',
        __('Border radius', 'shipxio-connect'),
        'shipxio_connect_render_border_radius_field',
        'shipxio-connect',
        'shipxio_connect_appearance',
        array('label_for' => 'shipxio-connect-border-radius')
    );
}

function shipxio_connect_sanitize_base_url($value)
{
    $url = is_string($value) ? rtrim(trim($value), '/') : '';
    if ('' === $url) {
        return '';
    }
    if (! shipxio_connect_valid_base_url($url)) {
        add_settings_error('shipxio_connect_base_url', 'invalid_url', __('Enter a valid Shipxio base URL using HTTP or HTTPS, without a path or query.', 'shipxio-connect'));
        return get_option('shipxio_connect_base_url', '');
    }
    return esc_url_raw($url);
}

function shipxio_connect_sanitize_credential($value)
{
    $credential = is_string($value) ? trim($value) : '';
    // A blank password field keeps the existing credential; it is never rendered.
    if ('' === $credential) {
        return get_option('shipxio_connect_credential', '');
    }
    if (! preg_match('/\Asxw_[a-f0-9]{32}\.[a-f0-9]{64}\z/', $credential)) {
        add_settings_error('shipxio_connect_credential', 'invalid_credential', __('Enter a valid Website Integration credential.', 'shipxio-connect'));
        return get_option('shipxio_connect_credential', '');
    }
    return $credential;
}

function shipxio_connect_sanitize_primary_color($value)
{
    $color = is_scalar($value) ? sanitize_hex_color(trim((string) $value)) : null;
    if (null === $color || '' === $color) {
        add_settings_error('shipxio_connect_primary_color', 'invalid_color', __('Enter a valid hex color, such as #2A7FFF.', 'shipxio-connect'));
        return shipxio_connect_primary_color();
    }
    return strtolower($color);
}

function shipxio_connect_sanitize_border_radius($value)
{
    $radius = is_scalar($value)
        ? filter_var(trim((string) $value), FILTER_VALIDATE_INT, array('options' => array(
            'min_range' => SHIPXIO_CONNECT_MIN_RADIUS,
            'max_range' => SHIPXIO_CONNECT_MAX_RADIUS,
        )))
        : false;
    if (false === $radius) {
        add_settings_error('shipxio_connect_border_radius', 'invalid_radius', sprintf(
            /* translators: 1: smallest allowed border radius, 2: largest allowed border radius. */
            __('Enter a border radius between %1$d and %2$d pixels.', 'shipxio-connect'),
            SHIPXIO_CONNECT_MIN_RADIUS,
            SHIPXIO_CONNECT_MAX_RADIUS
        ));
        return shipxio_connect_border_radius();
    }
    return $radius;
}

/** The stored primary color, already sanitized, falling back to the default. */
function shipxio_connect_primary_color()
{
    $color = sanitize_hex_color((string) get_option('shipxio_connect_primary_color', SHIPXIO_CONNECT_DEFAULT_COLOR));

    return null === $color || '' === $color ? SHIPXIO_CONNECT_DEFAULT_COLOR : strtolower($color);
}

/** The stored border radius in pixels, clamped to the supported range. */
function shipxio_connect_border_radius()
{
    $radius = filter_var(get_option('shipxio_connect_border_radius', SHIPXIO_CONNECT_DEFAULT_RADIUS), FILTER_VALIDATE_INT, array('options' => array(
        'min_range' => SHIPXIO_CONNECT_MIN_RADIUS,
        'max_range' => SHIPXIO_CONNECT_MAX_RADIUS,
    )));

    return false === $radius ? SHIPXIO_CONNECT_DEFAULT_RADIUS : $radius;
}

function shipxio_connect_register_settings_page()
{
    add_options_page(
        __('Shipxio Connect', 'shipxio-connect'),
        __('Shipxio Connect', 'shipxio-connect'),
        'manage_options',
        'shipxio-connect',
        'shipxio_connect_render_settings_page'
    );
}

function shipxio_connect_enqueue_settings_assets($hook)
{
    if ('settings_page_shipxio-connect' !== $hook || ! current_user_can('manage_options')) {
        return;
    }

    $url = plugin_dir_url(SHIPXIO_CONNECT_FILE);
    wp_enqueue_style('shipxio-connect-admin', $url . 'assets/css/admin.css', array('wp-color-picker'), SHIPXIO_CONNECT_VERSION);
    wp_enqueue_script(
        'shipxio-connect-admin',
        $url . 'assets/js/admin.js',
        array('jquery', 'wp-color-picker'),
        SHIPXIO_CONNECT_VERSION,
        true
    );
}


/** The plugin mark shipped with the plugin, never loaded from shipxio.com. */
function shipxio_connect_mark_url()
{
    return plugin_dir_url(SHIPXIO_CONNECT_FILE) . 'assets/images/shipxio-connect-mark.webp';
}

function shipxio_connect_render_base_url_field()
{
    $base_url = (string) get_option('shipxio_connect_base_url', '');
    ?>
    <input type="url" class="regular-text code" id="shipxio-connect-base-url" name="shipxio_connect_base_url" value="<?php echo esc_attr($base_url); ?>" placeholder="https://shipxio.example" required>
    <p class="description"><?php echo esc_html__('The Shipxio address only, such as https://shipxio.example. Do not add an API path.', 'shipxio-connect'); ?></p>
    <?php
}

function shipxio_connect_render_credential_field()
{
    $configured = '' !== (string) get_option('shipxio_connect_credential', '');
    $placeholder = $configured
        ? __('Enter a new credential to replace the saved one', 'shipxio-connect')
        : __('Paste the Website Integration credential', 'shipxio-connect');
    $help = $configured
        ? __('For security the saved credential is never shown again. Leave this field blank to keep it, or paste a new credential to replace it.', 'shipxio-connect')
        : __('Shipxio shows the credential once, when the Website Integration is created or its credential is rotated.', 'shipxio-connect');
    ?>
    <p class="shipxio-connect-credential-state <?php echo $configured ? 'is-configured' : 'is-missing'; ?>">
        <span class="dashicons <?php echo $configured ? 'dashicons-yes-alt' : 'dashicons-warning'; ?>" aria-hidden="true"></span>
        <strong><?php echo esc_html($configured ? __('Credential configured', 'shipxio-connect') : __('No credential saved', 'shipxio-connect')); ?></strong>
        <?php if ($configured) : ?>
            <?php // A fixed mask of a fixed length. The stored credential is never rendered. ?>
            <span class="shipxio-connect-credential-mask" aria-hidden="true">&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;</span>
        <?php endif; ?>
    </p>
    <input type="password" class="regular-text" id="shipxio-connect-credential" name="shipxio_connect_credential" value="" autocomplete="new-password" spellcheck="false" placeholder="<?php echo esc_attr($placeholder); ?>">
    <p class="description"><?php echo esc_html($help); ?></p>
    <?php
}

function shipxio_connect_render_primary_color_field()
{
    ?>
    <input type="text" class="shipxio-connect-color-field regular-text code" id="shipxio-connect-primary-color" name="shipxio_connect_primary_color" value="<?php echo esc_attr(shipxio_connect_primary_color()); ?>" data-default-color="<?php echo esc_attr(SHIPXIO_CONNECT_DEFAULT_COLOR); ?>" maxlength="7">
    <p class="description">
        <?php
        printf(
            /* translators: %s: the default hex color. */
            esc_html__('Used for the calculate button, focus rings, and highlights. Defaults to %s.', 'shipxio-connect'),
            '<code>' . esc_html(strtoupper(SHIPXIO_CONNECT_DEFAULT_COLOR)) . '</code>'
        );
        ?>
    </p>
    <?php
}

function shipxio_connect_render_border_radius_field()
{
    ?>
    <span class="shipxio-connect-radius-control">
        <input type="number" class="small-text" id="shipxio-connect-border-radius" name="shipxio_connect_border_radius" value="<?php echo esc_attr((string) shipxio_connect_border_radius()); ?>" min="<?php echo esc_attr((string) SHIPXIO_CONNECT_MIN_RADIUS); ?>" max="<?php echo esc_attr((string) SHIPXIO_CONNECT_MAX_RADIUS); ?>" step="1">
        <span class="shipxio-connect-unit"><?php echo esc_html__('px', 'shipxio-connect'); ?></span>
    </span>
    <p class="description">
        <?php
        printf(
            /* translators: 1: smallest allowed border radius, 2: largest allowed border radius, 3: default border radius. */
            esc_html__('Corner rounding for fields, buttons, cards, and tables, between %1$d and %2$d pixels. Use %1$d for square corners. Defaults to %3$d.', 'shipxio-connect'),
            (int) SHIPXIO_CONNECT_MIN_RADIUS,
            (int) SHIPXIO_CONNECT_MAX_RADIUS,
            (int) SHIPXIO_CONNECT_DEFAULT_RADIUS
        );
        ?>
    </p>
    <?php
}

/** One card: a heading, a short lead, and the fields of one settings section. */
function shipxio_connect_render_card($section, $title, $lead)
{
    ?>
    <section class="shipxio-connect-card">
        <div class="shipxio-connect-card-head">
            <h2><?php echo esc_html($title); ?></h2>
            <p><?php echo esc_html($lead); ?></p>
        </div>
        <div class="shipxio-connect-card-body">
            <table class="form-table" role="presentation">
                <?php do_settings_fields('shipxio-connect', $section); ?>
            </table>
            <?php if ('shipxio_connect_appearance' === $section) : ?>
                <?php shipxio_connect_render_appearance_preview(); ?>
            <?php endif; ?>
        </div>
    </section>
    <?php
}

/**
 * A small, static preview of the two appearance settings. admin.js keeps it in
 * step with the fields; without JavaScript it still shows the saved values.
 */
function shipxio_connect_render_appearance_preview()
{
    $style = sprintf(
        '--shipxio-connect-preview-color:%1$s;--shipxio-connect-preview-radius:%2$dpx;',
        shipxio_connect_primary_color(),
        shipxio_connect_border_radius()
    );
    ?>
    <div class="shipxio-connect-preview" id="shipxio-connect-preview" style="<?php echo esc_attr($style); ?>">
        <span class="shipxio-connect-preview-label"><?php echo esc_html__('Preview', 'shipxio-connect'); ?></span>
        <div class="shipxio-connect-preview-stage" aria-hidden="true">
            <span class="shipxio-connect-preview-input"></span>
            <span class="shipxio-connect-preview-button"><?php echo esc_html__('Calculate Estimate', 'shipxio-connect'); ?></span>
        </div>
        <p class="description"><?php echo esc_html__('An impression of the button and field styling only. The live layout is unchanged.', 'shipxio-connect'); ?></p>
    </div>
    <?php
}

/** One row of the shortcode reference, with a copy button. */
function shipxio_connect_render_shortcode_row($shortcode, $title, $description)
{
    ?>
    <li class="shipxio-connect-shortcode">
        <div class="shipxio-connect-shortcode-text">
            <strong><?php echo esc_html($title); ?></strong>
            <span><?php echo esc_html($description); ?></span>
        </div>
        <div class="shipxio-connect-shortcode-action">
            <code><?php echo esc_html($shortcode); ?></code>
            <button type="button" class="button button-secondary shipxio-connect-copy" data-shipxio-copy="<?php echo esc_attr($shortcode); ?>" data-shipxio-copied-label="<?php echo esc_attr__('Copied', 'shipxio-connect'); ?>">
                <span class="dashicons dashicons-clipboard" aria-hidden="true"></span>
                <span class="shipxio-connect-copy-label"><?php echo esc_html__('Copy', 'shipxio-connect'); ?></span>
            </button>
        </div>
    </li>
    <?php
}

function shipxio_connect_render_settings_page()
{
    if (! current_user_can('manage_options')) {
        wp_die(esc_html__('You are not allowed to manage these settings.', 'shipxio-connect'));
    }
    ?>
    <div class="wrap shipxio-connect-admin">
        <?php // WordPress anchors admin notices after the first heading in .wrap. ?>
        <h1 class="screen-reader-text"><?php echo esc_html__('Shipxio Connect', 'shipxio-connect'); ?></h1>
        <?php settings_errors(); ?>

        <header class="shipxio-connect-masthead">
            <img class="shipxio-connect-mark" src="<?php echo esc_url(shipxio_connect_mark_url()); ?>" width="48" height="48" alt="" decoding="async">
            <div class="shipxio-connect-masthead-text">
                <p class="shipxio-connect-wordmark"><?php echo esc_html__('Shipxio Connect', 'shipxio-connect'); ?></p>
                <p class="shipxio-connect-tagline"><?php echo esc_html__('Connect your WordPress website to Shipxio.', 'shipxio-connect'); ?></p>
            </div>
            <span class="shipxio-connect-version"><?php echo esc_html('v' . SHIPXIO_CONNECT_VERSION); ?></span>
        </header>

        <form method="post" action="options.php" class="shipxio-connect-form">
            <?php
            settings_fields('shipxio_connect');

            shipxio_connect_render_card(
                'shipxio_connect_connection',
                __('Connection', 'shipxio-connect'),
                __('Copy these values from your Website Integration settings in Shipxio. They are stored on this server and are never sent to visitors.', 'shipxio-connect')
            );

            shipxio_connect_render_card(
                'shipxio_connect_appearance',
                __('Appearance', 'shipxio-connect'),
                __('These settings apply to the Shipxio Connect shortcodes only. They never change the rest of your website, and the plugin keeps using your theme font.', 'shipxio-connect')
            );
            ?>

            <div class="shipxio-connect-actions">
                <?php submit_button(__('Save Changes', 'shipxio-connect'), 'primary', 'submit', false); ?>
                <span class="shipxio-connect-actions-note"><?php echo esc_html__('Leaving the credential field blank keeps the credential already saved.', 'shipxio-connect'); ?></span>
            </div>
        </form>

        <section class="shipxio-connect-card">
            <div class="shipxio-connect-card-head">
                <h2><?php echo esc_html__('Shortcodes', 'shipxio-connect'); ?></h2>
                <p><?php echo esc_html__('Add one of these to any WordPress page or post. They also work inside Elementor\'s standard Shortcode widget.', 'shipxio-connect'); ?></p>
            </div>
            <div class="shipxio-connect-card-body">
                <ul class="shipxio-connect-shortcodes">
                    <?php
                    shipxio_connect_render_shortcode_row(
                        '[shipxio_connect]',
                        __('Full experience', 'shipxio-connect'),
                        __('The shipping calculator and the shipping rates.', 'shipxio-connect')
                    );
                    shipxio_connect_render_shortcode_row(
                        '[shipxio_connect_calculator]',
                        __('Calculator only', 'shipxio-connect'),
                        __('The shipping calculator on its own.', 'shipxio-connect')
                    );
                    shipxio_connect_render_shortcode_row(
                        '[shipxio_connect_rates]',
                        __('Rates only', 'shipxio-connect'),
                        __('The shipping rates on its own.', 'shipxio-connect')
                    );
                    ?>
                </ul>
                <p class="shipxio-connect-hint"><?php echo esc_html__('Shipxio Connect fills the width of the container you place it in, so your theme keeps control of the page layout.', 'shipxio-connect'); ?></p>
            </div>
        </section>
    </div>
    <?php
}
