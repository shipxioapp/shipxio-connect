<?php
/** Server-side client for the Shipxio Website Integration API. */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * @return WP_REST_Response
 */
function shipxio_connect_request($method, $path, $query = array(), $payload = null, $failure_message = 'Could not reach Shipxio.')
{
    $base_url = rtrim((string) get_option('shipxio_connect_base_url', ''), '/');
    $credential = (string) get_option('shipxio_connect_credential', '');

    if (! shipxio_connect_valid_base_url($base_url) || ! preg_match('/\Asxw_[a-f0-9]{32}\.[a-f0-9]{64}\z/', $credential)) {
        return shipxio_connect_response(array('message' => __('Shipxio is not configured correctly.', 'shipxio-connect')), 503);
    }

    $url = $base_url . $path;
    if ($query) {
        $url = add_query_arg($query, $url);
    }

    $args = array(
        'method'      => $method,
        'timeout'     => 15,
        'redirection' => 0,
        'headers'     => array(
            'Accept'        => 'application/json',
            'Authorization' => 'Bearer ' . $credential,
        ),
        'limit_response_size' => 512001,
    );

    if (null !== $payload) {
        $body = wp_json_encode($payload);
        if (false === $body) {
            return shipxio_connect_response(array('message' => $failure_message), 500);
        }
        $args['headers']['Content-Type'] = 'application/json';
        $args['body'] = $body;
    }

    // WordPress checks the destination URL and no redirect can replay the credential.
    $response = wp_safe_remote_request($url, $args);
    if (is_wp_error($response)) {
        return shipxio_connect_response(array('message' => $failure_message), 502);
    }

    $status = (int) wp_remote_retrieve_response_code($response);
    $body = (string) wp_remote_retrieve_body($response);
    if ($status < 200 || $status > 599 || ($status >= 300 && $status < 400) || strlen($body) > 512000) {
        return shipxio_connect_response(array('message' => $failure_message), 502);
    }

    $data = json_decode($body, true);
    if (! is_array($data) || shipxio_connect_is_list($data) || json_last_error() !== JSON_ERROR_NONE || shipxio_connect_contains_credential($data)) {
        return shipxio_connect_response(array('message' => $failure_message), 502);
    }

    // Shipxio owns the public response contract, including validation errors.
    if ($status >= 500) {
        return shipxio_connect_response(array('message' => $failure_message), 502);
    }

    return shipxio_connect_response($data, $status);
}

function shipxio_connect_response($data, $status)
{
    $response = new WP_REST_Response($data, $status);
    $response->header('Cache-Control', 'no-store, private');
    return $response;
}

/**
 * Whether a decoded JSON value came in as an array rather than an object.
 *
 * array_is_list() would say the same thing, but WordPress only polyfills it
 * from 6.5 and this plugin supports 6.3, so the test is done here instead.
 * The empty array, which json_decode() returns for both `{}` and `[]`, counts
 * as a list, exactly as array_is_list() reports it.
 */
function shipxio_connect_is_list(array $value)
{
    $expected = 0;
    foreach ($value as $key => $unused) {
        if ($key !== $expected) {
            return false;
        }
        $expected++;
    }

    return true;
}

function shipxio_connect_contains_credential($value)
{
    if (is_string($value)) {
        return (bool) preg_match('/sxw_[a-f0-9]{32}\.[a-f0-9]{64}/i', $value);
    }
    if (is_array($value)) {
        foreach ($value as $key => $item) {
            if (is_string($key) && in_array(strtolower($key), array('credential', 'secret', 'secret_hash', 'authorization', 'access_token'), true)) {
                return true;
            }
            if (shipxio_connect_contains_credential($key) || shipxio_connect_contains_credential($item)) {
                return true;
            }
        }
    }
    return false;
}

function shipxio_connect_valid_base_url($url)
{
    if (! is_string($url) || strlen($url) > 2048 || ! filter_var($url, FILTER_VALIDATE_URL)) {
        return false;
    }
    $parts = wp_parse_url($url);
    return is_array($parts)
        && in_array(strtolower($parts['scheme'] ?? ''), array('http', 'https'), true)
        && ! empty($parts['host'])
        && empty($parts['user']) && empty($parts['pass'])
        && empty($parts['query']) && empty($parts['fragment'])
        && (! isset($parts['path']) || '/' === $parts['path']);
}
