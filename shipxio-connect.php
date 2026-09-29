<?php
/*
Plugin Name: Shipxio Connect
Plugin URI: https://shipxio.com/shipxio-connect
Description: Connects WordPress websites to Shipxio for shipping rates, shipping estimates, and HS Code suggestions.
Version: 1.0.1
Requires at least: 6.3
Requires PHP: 8.1
Author: Shipxio
Author URI: https://shipxio.com
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: shipxio-connect
*/

if (! defined('ABSPATH')) {
    exit;
}

define('SHIPXIO_CONNECT_VERSION', '1.0.1');
define('SHIPXIO_CONNECT_FILE', __FILE__);

require_once __DIR__ . '/includes/client.php';
require_once __DIR__ . '/includes/rest.php';
require_once __DIR__ . '/includes/settings.php';
require_once __DIR__ . '/includes/frontend.php';

add_action('rest_api_init', 'shipxio_connect_register_routes');
add_action('admin_init', 'shipxio_connect_register_settings');
add_action('admin_menu', 'shipxio_connect_register_settings_page');
add_action('init', 'shipxio_connect_register_assets');
add_shortcode('shipxio_connect', 'shipxio_connect_render_shortcode');
