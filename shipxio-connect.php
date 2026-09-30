<?php
/*
Plugin Name: Shipxio Connect
Plugin URI: https://shipxio.com/shipxio-connect
Description: Connects WordPress websites to Shipxio for shipping rates, shipping estimates, and HS Code suggestions.
Version: 1.0.4
Requires at least: 6.3
Requires PHP: 8.1
Author: Shipxio
Author URI: https://shipxio.com
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: shipxio-connect
Update URI: https://shipxio.com/shipxio-connect
*/

if (! defined('ABSPATH')) {
    exit;
}

define('SHIPXIO_CONNECT_VERSION', '1.0.4');
define('SHIPXIO_CONNECT_FILE', __FILE__);

// Appearance defaults, matching the design tokens the plugin ships with.
define('SHIPXIO_CONNECT_DEFAULT_COLOR', '#2a7fff');
define('SHIPXIO_CONNECT_DEFAULT_RADIUS', 8);
define('SHIPXIO_CONNECT_MIN_RADIUS', 0);
define('SHIPXIO_CONNECT_MAX_RADIUS', 24);

require_once __DIR__ . '/includes/client.php';
require_once __DIR__ . '/includes/rest.php';
require_once __DIR__ . '/includes/settings.php';
require_once __DIR__ . '/includes/frontend.php';
require_once __DIR__ . '/includes/updater.php';

add_action('rest_api_init', 'shipxio_connect_register_routes');
add_action('admin_init', 'shipxio_connect_register_settings');
add_action('admin_menu', 'shipxio_connect_register_settings_page');
add_action('admin_enqueue_scripts', 'shipxio_connect_enqueue_settings_assets');
add_action('init', 'shipxio_connect_register_assets');
add_shortcode('shipxio_connect', 'shipxio_connect_render_shortcode');
add_shortcode('shipxio_connect_calculator', 'shipxio_connect_render_calculator_shortcode');
add_shortcode('shipxio_connect_rates', 'shipxio_connect_render_rates_shortcode');

// Updates come from the project's own releases, matching the Update URI host.
add_filter('update_plugins_shipxio.com', 'shipxio_connect_check_for_update', 10, 3);
add_filter('plugins_api', 'shipxio_connect_plugin_information', 10, 3);
