<?php 
/**
 * Displays the campaign updates. 
 *
 * @author  Studio 164a
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$campaign = $view_args[ 'campaign' ];
$widget_title = apply_filters( 'widget_title', $view_args[ 'title' ] );

echo $view_args['before_widget'];

if ( ! empty( $widget_title ) ) :
    echo $view_args['before_title'] . $widget_title . $view_args['after_title'];
endif;

?>
<div class="campaign-updates">  
    <?php echo apply_filters( 'the_content', $campaign->updates ) ?>
</div>
<?php

echo $view_args['after_widget'];