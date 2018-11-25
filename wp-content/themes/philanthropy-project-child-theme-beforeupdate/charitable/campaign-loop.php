<?php 
/**
 * Displays the campaign loop.
 *
 * This overrides the default Charitable template defined at charitable/templates/campaign-loop.php
 *
 * @author  Studio 164a
 * @package Reach
 * @since   1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$campaigns = $view_args[ 'campaigns' ];
$columns = $view_args[ 'columns' ];
$args = charitable_campaign_loop_args( $view_args );
if ( ! $campaigns->have_posts() ) :
    return;
endif;

if ( $columns > 1 ) :
    $loop_class = sprintf( 'campaign-loop campaigns-grid masonry-grid campaign-grid-%d', $columns );
else : 
    $loop_class = 'campaign-loop campaigns-grid masonry-grid';
endif;

/**
 * @hook charitable_campaign_loop_before
 */
 //GUM EDIT -  added array() param to function
do_action( 'charitable_campaign_loop_before', $campaigns, array() );
?>
<div class="campaigns-grid-wrapper facetwp-template">
    <div class="<?php echo $loop_class ?>">

        <?php while ( $campaigns->have_posts() ) : ?>

            <?php $campaigns->the_post() ?>

            <?php charitable_template( 'campaign-loop/campaign.php', $args ); ?>

        <?php endwhile; ?>

    </div>
    <?php if (is_front_page()): ?>
    <p class="center">
        <a class="button button-alt load-more-button"
           href="<?= add_query_arg('offset', 15, site_url('/load-more-campaigns/')); ?>">
            Load More
        </a>
    </p>
    <?php endif; ?>


</div>
<?php
/**
 * @hook charitable_campaign_loop_after
 */
do_action( 'charitable_campaign_loop_after', $campaigns );