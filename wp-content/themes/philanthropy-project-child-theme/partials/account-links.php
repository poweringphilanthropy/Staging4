<?php 
/**
 * Account links
 *
 * @package reach
 */

if ( ! reach_has_charitable() ) {
    return;
}

$profile_page   = charitable_get_permalink( 'profile_page' );
$submit_page    = charitable_get_permalink( 'campaign_submission_page' );
?>
<div class="account-links">
    <?php if ( $submit_page ) : ?>

        <a class="user-campaign button button-altx" href="<?php echo $submit_page ?>"><?php _e( 'START A CAMPAIGN', 'reach' ) ?></a>

    <?php endif ?>

    <?php if ( is_user_logged_in() ) : ?>

        <?php if ( $profile_page ) : ?>
            <a class="user-account button button-altx" href="<?php echo $profile_page ?>"><i class="fa fa-user"></i> <?php _e('PROFILE', 'reach') ?></a>
        <?php endif ?>

        <a class="logoutx with-icon" href="<?php echo wp_logout_url( get_permalink() ) ?>" data-icon="&#xf08b;"><?php _e('Log out', 'reach') ?></a>

    <?php else : ?>

        <!-- <a class="user-register" href="<?php // echo charitable_get_permalink( 'registration_page' ) ?>"><?php _e('Sign Up', 'reach') ?></a> -->
        <a class="user-login" href="<?php echo charitable_get_permalink( 'login_page' ) ?>"><?php _e('Login', 'reach') ?></a>

    <?php endif ?>

    <?php 
    // display cart total
    $cart_total = edd_get_cart_quantity();
    ?>
    <a class="pp-cart-menu with-icon" href="<?php echo edd_get_checkout_uri(); ?>">
        <i class="fa fa-shopping-cart"></i>
        <span class="header-cart edd-cart-quantity"><?php echo $cart_total; ?></span> <?php echo ($cart_total != 1) ? __('ITEMS', 'philanthropy') : __('ITEM', 'philanthropy'); ?>
    </a>
</div><!-- .account-links -->