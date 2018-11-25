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

global $post;
$permalink = urlencode( get_the_permalink( $post->ID ) );
$title = urlencode( get_the_title( $post->ID ) );
$color = get_post_meta( $post->ID, '_page_color', true );

$style_bg_color = '';
if(!empty($color)){
    $style_bg_color = ' style="background:'.$color.'"';
}
$style_color = '';
if(!empty($color)){
    $style_color = ' style="color:'.$color.'"';
}
?>
<ul class="campaign-sharing share horizontal rrssb-buttons">
    <li><h6><?php _e( 'Spread The Word:', 'reach' ) ?></h6></li>
    <li class="share-twitter">
        <a href="http://twitter.com/home?status=<?php echo $title ?>%20<?php echo $permalink ?>" class="popup icon" data-icon="&#xf099;" <?php echo $style_color; ?>></a>
    </li>
    <li class="share-facebook">
        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $permalink ?>" class="popup icon" data-icon="&#xf09a;" <?php echo $style_color; ?>></a>
    </li>
    <li class="share-pinterest">
        <a href="http://pinterest.com/pin/create/button/?url=<?php echo $permalink ?>&amp;description=<?php echo $title ?>" class="popup icon" data-icon="&#xf0d2;" <?php echo $style_color; ?>></a>
    </li>
    <li class="share-email">
        <a title="Share via Email" href="mailto:?subject=Check out this campaign on Greeks4Good&body=<?= site_url($_SERVER['REQUEST_URI']) ?>" class="icon fa fa-envelope" <?php echo $style_color; ?>></a>
    </li>
</ul>