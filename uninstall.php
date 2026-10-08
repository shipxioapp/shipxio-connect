<?php
/**
 * Removes everything Shipxio Connect stored.
 *
 * WordPress loads this file only when the plugin is deleted. Deactivating the
 * plugin never reaches it, so settings survive a deactivate and reactivate.
 * The constant below is defined by WordPress immediately before the include,
 * so the guard also stops the file doing anything if it is reached any other
 * way, such as a direct request.
 */

if (! defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

/**
 * Delete the plugin's stored data for the site that is currently active.
 *
 * The Website Integration credential is among these, so deleting the plugin
 * is also how a site owner removes the credential from the database.
 */
function shipxio_connect_delete_site_data()
{
    $options = array(
        'shipxio_connect_base_url',
        'shipxio_connect_credential',
        'shipxio_connect_primary_color',
        'shipxio_connect_border_radius',
        'shipxio_connect_button_padding',
        'shipxio_connect_button_text_color',
        'shipxio_connect_sign_in_url',
        'shipxio_connect_sign_up_url',
        'shipxio_connect_redirect_text_color',
        'shipxio_connect_redirect_subtext_color',
        'shipxio_connect_redirect_spinner_color',
        'shipxio_connect_redirect_link_color',
    );

    foreach ($options as $option) {
        delete_option($option);
    }

    // The update check's short-lived failure flag. It expires on its own, but
    // a site without cron can keep the row long after the plugin is gone.
    delete_transient('shipxio_connect_update_failed');
}

if (is_multisite()) {
    // Every setting is per site, so each site on the network holds its own
    // credential and has to be cleared individually.
    foreach (get_sites(array('fields' => 'ids', 'number' => 0)) as $shipxio_connect_site_id) {
        switch_to_blog($shipxio_connect_site_id);
        shipxio_connect_delete_site_data();
        restore_current_blog();
    }
} else {
    shipxio_connect_delete_site_data();
}
