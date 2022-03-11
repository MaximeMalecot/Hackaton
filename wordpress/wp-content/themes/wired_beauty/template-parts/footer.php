<?php
/**
 * The template for displaying footer.
 *
 * @package HelloElementor
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

$footer_nav_menu = wp_nav_menu( [
    'theme_location' => 'menu-2',
    'fallback_cb' => false,
    'echo' => false,
] );

$logoPath = get_template_directory_uri() . '/assets/images/wired_beauty_logo.png';
?>
<footer id="site-footer" class="site-footer" role="contentinfo">
    <?php if ( $footer_nav_menu ) : ?>
        <nav class="site-navigation" role="navigation">
            <div>
                <img src="<?php echo $logoPath ?>" width="250px">
                <p>The better ways to research skincare with hybrids clinical trials and consumer studies.</p>
            </div>
            <?php echo $footer_nav_menu; ?>
        </nav>
    <?php endif; ?>
    <div class="copyright<?php if ( $footer_nav_menu ) : ?> copyrightMenu<?php endif;?>">© Copyright 2022 | WIRED BEAUTY | Tous droits réservés</div>
</footer>
