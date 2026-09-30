<?php
/**
 * Self-hosted plugin updates.
 *
 * Shipxio Connect is distributed from shipxio.com and updated from its public
 * GitHub releases, not from WordPress.org. The plugin header carries an
 * `Update URI` on shipxio.com, so WordPress skips the WordPress.org check for
 * this plugin entirely and calls the hostname filter below instead.
 *
 * Nothing here touches the Website Integration credential. The only request it
 * makes is an unauthenticated GET for a public manifest.
 */

if (! defined('ABSPATH')) {
    exit;
}

/** Public manifest, served by the GitHub CDN. No authentication, no API quota. */
define('SHIPXIO_CONNECT_MANIFEST_URL', 'https://raw.githubusercontent.com/shipxioapp/shipxio-connect/main/update.json');

/** Release assets may only come from the project's own GitHub releases. */
define('SHIPXIO_CONNECT_PACKAGE_PREFIX', 'https://github.com/shipxioapp/shipxio-connect/releases/download/');

/**
 * Remembers only that a fetch failed. There is deliberately no cache of a
 * successful manifest: WordPress's own update_plugins transient already
 * decides how often plugin updates are checked.
 */
define('SHIPXIO_CONNECT_FAILURE_TRANSIENT', 'shipxio_connect_update_failed');

/** The installed directory name, which is also the slug WordPress asks about. */
function shipxio_connect_plugin_slug()
{
    $slug = dirname(plugin_basename(SHIPXIO_CONNECT_FILE));

    return ('.' === $slug || '' === $slug) ? 'shipxio-connect' : $slug;
}

/**
 * Icon metadata for the update and plugin details screens.
 *
 * Without it, Dashboard > Updates falls back to the generic grey plugin
 * icon. The mark ships inside the plugin and is served from the install, so
 * it needs no request to shipxio.com or GitHub and works on any domain.
 *
 * The file is 128x128, which is WordPress's 1x size. It is deliberately not
 * offered as 2x as well: core prefers 2x when present and would scale a 1x
 * image up. The default key is supplied because the details screen reads it
 * as a last resort.
 *
 * @return array<string, string>
 */
function shipxio_connect_plugin_icons()
{
    // One definition of the mark, shared with the settings screen, so the
    // file name cannot drift out of sync in two places.
    $mark = shipxio_connect_mark_url();

    return array(
        '1x'      => $mark,
        'default' => $mark,
    );
}

/**
 * Whether WordPress is running an update check the administrator asked for.
 *
 * Dashboard > Updates, including its "Check again" button, runs the plugin
 * check inside the load-update-core.php action, and a finished upgrade
 * re-checks inside upgrader_process_complete. In both cases nothing of ours
 * may stand between the administrator and a newly published release.
 */
function shipxio_connect_is_manual_update_check()
{
    return doing_action('load-update-core.php') || doing_action('upgrader_process_complete');
}

/**
 * Fetch the public manifest.
 *
 * A successful manifest is deliberately not cached. WordPress only calls the
 * update filter when it actually performs a check, and it already throttles
 * that to roughly twice a day, hourly on the plugins screen, and every minute
 * on the updates screen. Caching a good manifest on a separate six hour clock
 * added nothing and hid newly published releases until it expired.
 *
 * A failure is remembered briefly so an unreachable GitHub cannot stall the
 * plugins screen on every load. That short memory is skipped for a manual
 * check, which must always be allowed to reach the network.
 *
 * @return array|null The validated manifest, or null when unavailable.
 */
function shipxio_connect_update_manifest()
{
    if (! shipxio_connect_is_manual_update_check()
        && 'unavailable' === get_transient(SHIPXIO_CONNECT_FAILURE_TRANSIENT)) {
        return null;
    }

    $response = wp_safe_remote_get(SHIPXIO_CONNECT_MANIFEST_URL, array(
        'timeout'             => 10,
        'redirection'         => 2,
        'headers'             => array('Accept' => 'application/json'),
        'limit_response_size' => 65536,
    ));

    if (is_wp_error($response) || 200 !== (int) wp_remote_retrieve_response_code($response)) {
        set_transient(SHIPXIO_CONNECT_FAILURE_TRANSIENT, 'unavailable', 5 * MINUTE_IN_SECONDS);
        return null;
    }

    $manifest = shipxio_connect_validate_manifest(json_decode((string) wp_remote_retrieve_body($response), true));
    if (null === $manifest) {
        set_transient(SHIPXIO_CONNECT_FAILURE_TRANSIENT, 'unavailable', 5 * MINUTE_IN_SECONDS);
        return null;
    }

    delete_transient(SHIPXIO_CONNECT_FAILURE_TRANSIENT);

    return $manifest;
}

/**
 * Accept only a manifest that is complete and safe to act on.
 *
 * The package URL decides what WordPress downloads and installs, so it must be
 * a release asset of this project and nothing else.
 *
 * @return array|null
 */
function shipxio_connect_validate_manifest($data)
{
    if (! is_array($data) || shipxio_connect_is_list($data)) {
        return null;
    }

    $text = static function ($value) {
        return is_scalar($value) ? trim((string) $value) : '';
    };

    $version = $text($data['version'] ?? null);
    $package = $text($data['download_url'] ?? null);
    if (! preg_match('/\A[0-9]+\.[0-9]+\.[0-9]+\z/', $version)) {
        return null;
    }
    if (0 !== strpos($package, SHIPXIO_CONNECT_PACKAGE_PREFIX)) {
        return null;
    }

    $manifest = array(
        'version'      => $version,
        'download_url' => esc_url_raw($package),
        'homepage'     => esc_url_raw($text($data['homepage'] ?? null)),
        'requires'     => $text($data['requires'] ?? null),
        'tested'       => $text($data['tested'] ?? null),
        'requires_php' => $text($data['requires_php'] ?? null),
        'last_updated' => $text($data['last_updated'] ?? null),
        'sections'     => array(),
    );

    // Optional prose for the details modal; absent from the minimal manifest.
    if (isset($data['sections']) && is_array($data['sections'])) {
        foreach ($data['sections'] as $name => $body) {
            if (is_string($name) && is_string($body)) {
                $manifest['sections'][sanitize_key($name)] = wp_kses_post($body);
            }
        }
    }

    return $manifest;
}

/**
 * Answer the update check WordPress runs for plugins whose Update URI points
 * at shipxio.com.
 *
 * Returning false leaves the plugin exactly as WordPress found it, which is
 * what should happen when the manifest is missing, malformed, or not newer.
 *
 * @param array|false $update      The update offered so far.
 * @param array       $plugin_data Headers of the plugin being checked.
 * @param string      $plugin_file Plugin basename, e.g. shipxio-connect/shipxio-connect.php.
 * @return array|false
 */
function shipxio_connect_check_for_update($update, $plugin_data, $plugin_file)
{
    if (plugin_basename(SHIPXIO_CONNECT_FILE) !== $plugin_file) {
        return $update;
    }

    $manifest = shipxio_connect_update_manifest();
    if (null === $manifest) {
        return $update;
    }

    if (! version_compare($manifest['version'], SHIPXIO_CONNECT_VERSION, '>')) {
        return $update;
    }

    $offer = array(
        'slug'    => shipxio_connect_plugin_slug(),
        'version' => $manifest['version'],
        'package' => $manifest['download_url'],
        'icons'   => shipxio_connect_plugin_icons(),
    );

    foreach (array('requires', 'tested', 'requires_php') as $field) {
        if ('' !== $manifest[$field]) {
            $offer[$field] = $manifest[$field];
        }
    }
    if ('' !== $manifest['homepage']) {
        $offer['url'] = $manifest['homepage'];
    }

    return $offer;
}

/**
 * Supply the data behind the "View details" link.
 *
 * Without this, WordPress asks WordPress.org about a plugin it has never heard
 * of and the modal reports an error. Any other plugin's request passes through
 * untouched.
 *
 * @param false|object|array $result The response so far.
 * @param string             $action The plugins_api action being performed.
 * @param object             $args   Arguments for the request.
 * @return false|object|array
 */
function shipxio_connect_plugin_information($result, $action, $args)
{
    if ('plugin_information' !== $action) {
        return $result;
    }
    if (! isset($args->slug) || shipxio_connect_plugin_slug() !== $args->slug) {
        return $result;
    }

    $headers = shipxio_connect_plugin_headers();
    $manifest = shipxio_connect_update_manifest();

    $information = new stdClass();
    $information->name          = '' !== $headers['Name'] ? $headers['Name'] : 'Shipxio Connect';
    $information->slug          = shipxio_connect_plugin_slug();
    $information->icons         = shipxio_connect_plugin_icons();
    $information->version       = null === $manifest ? SHIPXIO_CONNECT_VERSION : $manifest['version'];
    $information->author        = '' !== $headers['AuthorURI'] && '' !== $headers['Author']
        ? '<a href="' . esc_url($headers['AuthorURI']) . '">' . esc_html($headers['Author']) . '</a>'
        : esc_html($headers['Author']);
    $information->requires      = $headers['RequiresWP'];
    $information->requires_php  = $headers['RequiresPHP'];
    $information->homepage      = '' !== $headers['PluginURI'] ? $headers['PluginURI'] : 'https://shipxio.com/shipxio-connect';
    $information->sections      = array(
        'description' => '' !== $headers['Description']
            ? wpautop(esc_html($headers['Description']))
            : '',
    );

    if (null !== $manifest) {
        if ('' !== $manifest['homepage']) {
            $information->homepage = $manifest['homepage'];
        }
        if ('' !== $manifest['requires']) {
            $information->requires = $manifest['requires'];
        }
        if ('' !== $manifest['requires_php']) {
            $information->requires_php = $manifest['requires_php'];
        }
        if ('' !== $manifest['tested']) {
            $information->tested = $manifest['tested'];
        }
        if ('' !== $manifest['last_updated']) {
            $information->last_updated = $manifest['last_updated'];
        }
        if (version_compare($manifest['version'], SHIPXIO_CONNECT_VERSION, '>')) {
            $information->download_link = $manifest['download_url'];
        }
        foreach ($manifest['sections'] as $name => $body) {
            $information->sections[$name] = $body;
        }
    }

    if (! isset($information->sections['changelog'])) {
        $information->sections['changelog'] = wpautop(sprintf(
            /* translators: %s: link to the plugin release notes. */
            esc_html__('Release notes for every version are published at %s.', 'shipxio-connect'),
            '<a href="https://github.com/shipxioapp/shipxio-connect/releases" target="_blank" rel="noopener noreferrer">github.com/shipxioapp/shipxio-connect/releases</a>'
        ));
    }

    return $information;
}

/** Headers of the installed plugin, used as the fallback for the details modal. */
function shipxio_connect_plugin_headers()
{
    $defaults = array(
        'Name' => '', 'PluginURI' => '', 'Description' => '', 'Author' => '',
        'AuthorURI' => '', 'RequiresWP' => '', 'RequiresPHP' => '',
    );

    if (! function_exists('get_plugin_data')) {
        $plugin_admin = ABSPATH . 'wp-admin/includes/plugin.php';
        if (! is_readable($plugin_admin)) {
            return $defaults;
        }
        require_once $plugin_admin;
    }

    $data = get_plugin_data(SHIPXIO_CONNECT_FILE, false, false);

    foreach ($defaults as $key => $unused) {
        if (isset($data[$key]) && is_string($data[$key])) {
            $defaults[$key] = $data[$key];
        }
    }

    return $defaults;
}
