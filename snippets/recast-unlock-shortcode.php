function recast_unlock_shortcode($atts) {
  $a = shortcode_atts(array(
    'image' => '',
    'product' => '',
    'vimeo' => '',
    'live' => 'false',
  ), $atts);

  $vimeo_id = esc_attr($a['vimeo']);
  $is_live = $a['live'] === 'true';

  ob_start(); ?>

  <div id="recast-container" style="position: relative; text-align: center;">
    <?php if (isset($_GET['success']) && $_GET['success'] === 'true') : ?>
      <div style="padding:56.25% 0 0 0;position:relative;">
        <iframe
          id="vimeo-player-<?php echo $vimeo_id; ?>"
          src="https://vimeo.com/event/<?php echo $vimeo_id; ?>/embed/interaction"
          frameborder="0"
          allow="autoplay; fullscreen; picture-in-picture"
          allowfullscreen
          style="position:absolute;top:0;left:0;width:100%;height:100%;">
        </iframe>
      </div>
    <?php else : ?>
      <img
        src="<?php echo esc_url($a['image']); ?>"
        alt="Watch Now"
        style="width: 100%; display: block;"
      />

      <button
        class="recast-play-button"
        data-product-id="<?php echo esc_attr($a['product']); ?>"
        data-iframe-id="vimeo-player-<?php echo $vimeo_id; ?>"
        style="
          position: absolute;
          bottom: 20px;
          left: 50%;
          transform: translateX(-50%);
          padding: 12px 24px;
          background: rgba(0, 0, 0, 0.6);
          color: white;
          border: none;
          font-size: 18px;
          cursor: pointer;
          border-radius: 6px;
        "
      >
        ▶ Watch Now
      </button>
    <?php endif; ?>
  </div>

  <?php return ob_get_clean();
}
add_shortcode('recast_unlock', 'recast_unlock_shortcode');
