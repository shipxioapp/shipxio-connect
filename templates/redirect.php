<?php
/** Embeddable redirect component; the host owns the page layout. */

if (! defined('ABSPATH')) {
    exit;
}

/** @var string $redirect_url */
/** @var string $heading */
?>
<div class="shipxio-connect-redirect" data-shipxio-redirect>
    <div class="shipxio-connect-redirect__content">
        <span class="shipxio-connect-redirect__spinner" aria-hidden="true"></span>
        <div role="status" aria-live="polite" aria-atomic="true">
            <h1 class="shipxio-connect-redirect__heading"><?php echo esc_html($heading); ?></h1>
            <p class="shipxio-connect-redirect__message">
                <?php echo esc_html__('Please wait a moment while we take you there.', 'shipxio-connect'); ?>
                <br>
                <?php
                printf(
                    /* translators: %s: the linked word Continue. */
                    esc_html__('If you’re not redirected automatically, click %s.', 'shipxio-connect'),
                    '<a class="shipxio-connect-redirect__continue" href="' . esc_url($redirect_url, array('http', 'https')) . '">' . esc_html__('Continue', 'shipxio-connect') . '</a>'
                );
                ?>
            </p>
        </div>
    </div>
</div>
