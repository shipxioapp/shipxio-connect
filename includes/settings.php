<?php
/** WordPress-owned connection settings. */

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

function shipxio_connect_render_settings_page()
{
    if (! current_user_can('manage_options')) {
        wp_die(esc_html__('You are not allowed to manage these settings.', 'shipxio-connect'));
    }
    $base_url = (string) get_option('shipxio_connect_base_url', '');
    $configured = (bool) get_option('shipxio_connect_credential', '');
    ?>
    <div class="wrap">
        <h1><?php echo esc_html__('Shipxio Connect', 'shipxio-connect'); ?></h1>
        <?php settings_errors(); ?>
        <form method="post" action="options.php">
            <?php settings_fields('shipxio_connect'); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="shipxio-connect-base-url"><?php echo esc_html__('Shipxio base URL', 'shipxio-connect'); ?></label></th>
                    <td>
                        <input type="url" class="regular-text" id="shipxio-connect-base-url" name="shipxio_connect_base_url" value="<?php echo esc_attr($base_url); ?>" placeholder="https://shipxio.example" required>
                        <p class="description"><?php echo esc_html__('Copy the Shipxio base URL from your Website Integration settings in Shipxio and paste it here.', 'shipxio-connect'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="shipxio-connect-credential"><?php echo esc_html__('Website Integration credential', 'shipxio-connect'); ?></label></th>
                    <td>
                        <input type="password" class="regular-text" id="shipxio-connect-credential" name="shipxio_connect_credential" value="" autocomplete="new-password" spellcheck="false">
                        <p class="description"><?php echo esc_html__('Copy the Website Integration credential from Shipxio and paste it here. Keep it on the server, never in visitor-facing browser code.', 'shipxio-connect'); ?></p>
                        <?php if ($configured) : ?>
                            <p class="description"><?php echo esc_html__('Configured. Leave blank to keep the current credential.', 'shipxio-connect'); ?></p>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
        <p><?php echo esc_html__('Add [shipxio_connect] to a page to display the calculator and shipping rates.', 'shipxio-connect'); ?></p>
    </div>
    <?php
}
