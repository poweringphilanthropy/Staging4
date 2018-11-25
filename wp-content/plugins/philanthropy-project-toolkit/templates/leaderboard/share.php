<?php
/**
 * The template for displaying the campaign sharing icons on the campaign page.
 *
 * Override this template by copying it to your-child-theme/charitable/campaign/summary.php
 *
 * @author  Studio 164a
 * @package Reach
 * @since   1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$term       = $view_args[ 'term' ];
$color = get_term_meta( $term->term_id, '_dashboard_color', true );

$style_bg_color = '';
$style_color = '';
if(!empty($color)){
    $style_bg_color = ' style="background:'.$color.'"';
    $style_color = ' style="color:'.$color.'"';
}

$permalink = get_term_link( $term );
?>
<ul class="campaign-sharing share horizontal rrssb-buttons">
    <li><h6><?php _e( 'Spread The Word:', 'reach' ) ?></h6></li>
    <li class="share-twitter">
        <a href="http://twitter.com/home?status=<?php echo $term->name; ?>%20<?php echo $permalink ?>" class="popup icon" data-icon="&#xf099;" <?php echo $style_color; ?>></a>
    </li>
    <li class="share-facebook">
        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $permalink ?>" class="popup icon" data-icon="&#xf09a;" <?php echo $style_color; ?>></a>
    </li>
<!--     <li class="share-pinterest">
        <a href="http://pinterest.com/pin/create/button/?url=<?php // echo $permalink ?>&amp;description=<?php // echo $title ?>" class="popup icon" data-icon="&#xf0d2;" <?php // echo $style_color; ?>></a>
    </li> -->
    <li class="share-email">
        <a title="Share via Email" href="mailto:?subject=Check out this campaign on Greeks4Good&body=<?= site_url($_SERVER['REQUEST_URI']) ?>" class="icon fa fa-envelope" <?php echo $style_color; ?>></a>
    </li>
</ul>