<?php
/*
Plugin Name: Recast Gateway
Description: Integrates Recast access control for video content.
Version: 1.0
Author: Howard Bolter
*/

// ✅ Load the API route handler
require_once plugin_dir_path(__FILE__) . 'includes/recast-api.php';
error_log("✅ Recast Gateway Plugin loaded");

if (!function_exists('recast_enqueue_scripts')) {
    function recast_enqueue_scripts() {
        wp_enqueue_script(
            'recast-main',
            plugin_dir_url(__FILE__) . 'main.js',
            [],
            time(), // 🔁 Forces fresh version on every page load
            true
        );

        // ✅ Load Vimeo Player API for autoplay support
        wp_enqueue_script(
            'vimeo-player',
            'https://player.vimeo.com/api/player.js',
            [],
            null,
            true
        );
    }
    add_action('wp_enqueue_scripts', 'recast_enqueue_scripts');
}

/**
 * ✅ Shortcode: [recast_button video_id="123456789" product_id="RECAST-PRODUCT-CODE"]
 */
function recast_button_shortcode($atts) {
    $atts = shortcode_atts([
        'video_id' => '',
        'product_id' => ''
    ], $atts);

    $video_id = esc_attr($atts['video_id']);
    $product_id = esc_attr($atts['product_id']);

    if (empty($video_id) || empty($product_id)) return '';

    ob_start();
    ?>
    <div class="recast-video-wrapper" style="position: relative; width: 100%; padding-top: 56.25%;">
        <iframe
            id="vimeo-player-<?php echo $video_id; ?>"
            src="https://player.vimeo.com/video/<?php echo $video_id; ?>?autoplay=0"
            frameborder="0"
            allow="autoplay; fullscreen"
            allowfullscreen
            style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 1;"
        ></iframe>

        <div class="recast-overlay"
             id="recast-overlay-<?php echo $video_id; ?>"
             style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;
                    background: rgba(0,0,0,0.7); z-index: 2; display: flex; align-items: center;
                    justify-content: center;">
            <button class="recast-play-button"
                data-product-id="<?php echo $product_id; ?>"
                data-iframe-id="vimeo-player-<?php echo $video_id; ?>"
                style="padding: 12px 24px; background: rgba(255,255,255,0.9); color: black;
                       border: none; font-size: 18px; cursor: pointer;">
                ▶ Watch Now
            </button>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('recast_button', 'recast_button_shortcode');
