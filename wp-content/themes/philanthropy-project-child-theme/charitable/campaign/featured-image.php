<?php
/**
 * The template for displaying the campaign's featured image.
 *
 * @author  Studio 164a
 * @package Reach
 * @since   1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$campaign = $view_args[ 'campaign' ];

$thumbnail_size = PHILANTHROPY_PROJECT_CAMPAIGN_ID == $campaign->ID ? 'full' : 'campaign-thumbnail-large';

if ( ! has_post_thumbnail( $campaign->ID ) ) : 
    return;
endif;

?>
<div class="campaign-image campaign-image-<?php echo $thumbnail_size ?>">
    <?php 
    echo charitable_template_campaign_status_tag( $campaign );
            
    echo get_the_post_thumbnail( $campaign->ID, $thumbnail_size );
    ?>
</div>