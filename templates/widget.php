<?php
/** Shortcode wrapper. Renders the requested Shipxio Connect sections. */

if (! defined('ABSPATH')) {
    exit;
}

/** @var string $api_base */
/** @var string $id */
/** @var array $sections */
/** @var bool $show_intro */

$shipxio_connect_templates = array(
    'calculator' => __DIR__ . '/calculator.php',
    'rates'      => __DIR__ . '/rates.php',
);
?>
<div class="shipxio-connect" data-shipxio-api-base="<?php echo esc_url($api_base); ?>">
    <div class="page">
        <?php
        foreach ($sections as $shipxio_connect_section) {
            if (isset($shipxio_connect_templates[$shipxio_connect_section])) {
                include $shipxio_connect_templates[$shipxio_connect_section];
            }
        }
        ?>
    </div>
</div>
