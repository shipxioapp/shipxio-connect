<?php
/** Public visitor routes. The integration credential never enters the request. */

if (! defined('ABSPATH')) {
    exit;
}

function shipxio_connect_register_routes()
{
    $routes = array(
        'connection'  => array('GET', 'shipxio_connect_connection'),
        'rates'       => array('GET', 'shipxio_connect_rates'),
        'estimate'    => array('POST', 'shipxio_connect_estimate'),
        'suggestions' => array('GET', 'shipxio_connect_suggestions'),
    );

    foreach ($routes as $route => $definition) {
        register_rest_route('shipxio-connect/v1', '/' . $route, array(
            'methods'             => $definition[0],
            'callback'            => $definition[1],
            'permission_callback' => '__return_true',
        ));
    }
}

function shipxio_connect_connection()
{
    return shipxio_connect_request('GET', '/api/website/v1/connection', array(), null, __('Could not connect to Shipxio.', 'shipxio-connect'));
}

function shipxio_connect_rates()
{
    return shipxio_connect_request('GET', '/api/website/v1/rates', array(), null, __('Could not load shipping rates from Shipxio.', 'shipxio-connect'));
}

function shipxio_connect_estimate($request)
{
    $input = $request->get_json_params();
    if (! is_array($input) || array_is_list($input) || strlen($request->get_body()) > 16384) {
        return shipxio_connect_response(array('message' => __('Invalid request.', 'shipxio-connect')), 400);
    }

    $weight = shipxio_connect_scalar_text($input['weight'] ?? null);
    $declared_value = shipxio_connect_scalar_text($input['declared_value_usd'] ?? null);
    if ('' === $weight || ! is_numeric($weight)) {
        return shipxio_connect_response(array('message' => __('Enter the package weight.', 'shipxio-connect')), 400);
    }
    if ('' === $declared_value || ! is_numeric($declared_value)) {
        return shipxio_connect_response(array('message' => __('Enter the item value.', 'shipxio-connect')), 400);
    }

    $payload = array('weight' => $weight, 'declared_value_usd' => $declared_value);
    $hs_code = shipxio_connect_scalar_text($input['hs_code'] ?? null);
    if ('' !== $hs_code) {
        if (! preg_match('/\A[0-9]{10}\z/', $hs_code)) {
            return shipxio_connect_response(array('message' => __('Invalid request.', 'shipxio-connect')), 400);
        }
        $payload['hs_code'] = $hs_code;
    }

    return shipxio_connect_request('POST', '/api/website/v1/estimate', array(), $payload, __('Could not calculate shipping with Shipxio.', 'shipxio-connect'));
}

function shipxio_connect_suggestions($request)
{
    $query = shipxio_connect_scalar_text($request->get_param('query'));
    if ('' === $query) {
        return shipxio_connect_response(array('message' => __('Enter a search term.', 'shipxio-connect')), 400);
    }
    $length = function_exists('mb_strlen') ? mb_strlen($query) : strlen($query);
    if ($length < 2 || $length > 255) {
        return shipxio_connect_response(array('message' => __('Enter between 2 and 255 characters to search.', 'shipxio-connect')), 400);
    }

    $parameters = array('query' => $query);
    $limit = $request->get_param('limit');
    if (null !== $limit && '' !== $limit) {
        if (! is_scalar($limit)) {
            return shipxio_connect_response(array('message' => __('Invalid request.', 'shipxio-connect')), 400);
        }
        $parsed = filter_var($limit, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1, 'max_range' => 20)));
        if (false === $parsed) {
            return shipxio_connect_response(array('message' => __('Invalid request.', 'shipxio-connect')), 400);
        }
        $parameters['limit'] = $parsed;
    }

    return shipxio_connect_request('GET', '/api/website/v1/suggestions', $parameters, null, __('Could not load HS Code suggestions from Shipxio.', 'shipxio-connect'));
}

function shipxio_connect_scalar_text($value)
{
    return is_scalar($value) ? trim((string) $value) : '';
}
