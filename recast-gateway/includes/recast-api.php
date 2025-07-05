<?php

add_action('rest_api_init', function () {
    register_rest_route('recast/v1', '/intent', [
        'methods'  => 'POST',
        'callback' => 'recast_create_redirect_intent',
        'permission_callback' => '__return_true',
    ]);
});

function recast_create_redirect_intent(WP_REST_Request $request) {
    // Recast sandbox credentials
    $api_key    = 'vJsASsM4kBFY';
    $api_secret = 'c1JCJrjATCLGt36rw8SamyLTqk4KB7nK';
    $brand_id   = '5AqEN';
    $sandbox_base_url = 'https://api.recast-sandbox.tv';

    // Parse JSON body from POST
    $raw_body = $request->get_body();
    error_log("📨 RAW BODY: $raw_body");

    $body = json_decode($request->get_body(), true);
    $product_id = sanitize_text_field($body['product_id'] ?? '');
    $user_id    = sanitize_text_field($body['user_id'] ?? 'anonymous-user');

    error_log("📨 RAW BODY: " . $request->get_body());
    error_log("🔐 Parsed product_id: $product_id");
    error_log("🧑 Parsed user_id: $user_id");

    if (empty($product_id)) {
        return new WP_REST_Response(['error' => 'Missing product ID'], 400);
    }

    // Request JWT token
    $jwt_response = wp_remote_post("$sandbox_base_url/api/v0/vendor/keys/jwt", [
        'headers' => ['Content-Type' => 'application/json'],
        'body'    => json_encode([
            'key'     => $api_key,
            'secret'  => $api_secret,
            'brandId' => $brand_id
        ])
    ]);

    if (is_wp_error($jwt_response)) {
        return new WP_REST_Response(['error' => 'JWT request failed'], 500);
    }

    $jwt_body = json_decode(wp_remote_retrieve_body($jwt_response), true);
    $jwt_token = $jwt_body['item']['accessToken'] ?? null;

    if (!$jwt_token) {
        return new WP_REST_Response(['error' => 'Invalid JWT response'], 500);
    }

    // Create purchase intent
    $intent_response = wp_remote_post("$sandbox_base_url/api/v0/vendor/purchases/intent", [
        'headers' => [
            'Authorization' => "Bearer $jwt_token",
            'Content-Type'  => 'application/json'
        ],
        'body' => json_encode([
            'externalProductId' => $product_id,
            'externalUserId'    => $user_id,
            'tariffDetails'     => [
                'amount'   => 1,
                'currency' => 'CST'
            ]
        ])
    ]);

    if (is_wp_error($intent_response)) {
        return new WP_REST_Response(['error' => 'Intent request failed'], 500);
    }

    $intent_body = json_decode(wp_remote_retrieve_body($intent_response), true);
    $intent_token = $intent_body['item']['intentToken'] ?? null;

    if (!$intent_token) {
        return new WP_REST_Response([
            'error' => 'Failed to create intent token',
            'response' => $intent_body
        ], 400);
    }

    return new WP_REST_Response(['intent_token' => $intent_token], 200);
}
