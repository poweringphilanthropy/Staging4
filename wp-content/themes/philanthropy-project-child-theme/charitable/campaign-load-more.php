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

if ( ! $campaigns->have_posts() ) :
    return;
endif;

    $loop_class = 'campaign-loop campaigns-grid masonry-grid';


?>

    <div class="<?php echo $loop_class ?> campaigns-load-more">

        <?php while ( $campaigns->have_posts() ) : ?>

            <?php $campaigns->the_post() ?>

            <?php charitable_template( 'campaign-loop/campaign.php' ) ?>

        <?php endwhile; ?>

    </div>
