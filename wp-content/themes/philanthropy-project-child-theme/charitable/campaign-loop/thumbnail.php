<?php
/**
 * The template for displaying the campaign thumbnail within a loop of campaigns.
 *
 * @author  Studio 164a
 * @package Charitable/Templates/Campaign
 * @since   1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * @var Charitable_Campaign
 */
$campaign = $view_args[ 'campaign' ];

if(function_exists('aq_resize')):
$thumb_url = get_the_post_thumbnail_url( get_the_ID(), 'full' );
$thumb_url = aq_resize( $thumb_url, 1900, 600, true, true, true );
endif;
?>
<div class="campaign-image">
    <a href="<?php the_permalink() ?>" title="<?php printf( __( 'Go to %s', 'reach' ), get_the_title() ) ?>" target="_parent">
        <?php 
        if(function_exists('aq_resize')){
        	?>
			<img src="<?php echo $thumb_url; ?>" alt="">
        	<?php
       	} else {
       		echo get_the_post_thumbnail( get_the_ID(), 'campaign-thumbnail-medium' );
   		}       
   		?>
    </a>
</div>