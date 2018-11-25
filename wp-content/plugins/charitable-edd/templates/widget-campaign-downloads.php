<?php
/**
 * Display the downloads that are connected to a campaign.
 *
 * @author  Studio 164a
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

if ( ! charitable_is_campaign_page() && 'current' == $view_args[ 'campaign_id' ] ) {
    return;
}

if ( isset( $_GET[ 'preview' ] ) && $_GET[ 'preview' ] ) {
    $status = array( 'publish', 'pending', 'draft' );
}
else {
    $status = array( 'publish' );
}

$widget_title   = apply_filters( 'widget_title', $view_args['title'] );
$campaign_id    = $view_args[ 'campaign_id' ] == 'current' ? get_the_ID() : $view_args[ 'campaign_id' ];
$campaign       = new Charitable_EDD_Campaign( $campaign_id );
$downloads      = $campaign->get_connected_downloads( array( 
    'post_status' => $status,
    'tax_query' => array(
        array(
            'taxonomy' => 'download_category',
            'field'    => 'id',
            'terms'    => $view_args[ 'download_category' ]
        )       
    ) )
);

if ( ! $downloads ) {
    return;
}

echo $view_args['before_widget'];

if ( ! empty( $widget_title ) ) :
    echo $view_args['before_title'] . $widget_title . $view_args['after_title'];
endif;

foreach ( $downloads as $download ) : 

    $excerpt = strlen( $download->post_excerpt ) ? $download->post_excerpt : $download->post_content;
    ?>

    <div class="charitable-edd-connected-download widget-block">
        <?php if ( $view_args['show_featured_image'] ) : ?>
            <?php echo get_the_post_thumbnail( $download->ID ) ?>
        <?php endif ?>
        <?php if ( $view_args['show_title'] ) : ?>
            <h4 class="download-title"><?php echo get_the_title( $download->ID ) ?></h4>
        <?php endif ?>
        <?php echo apply_filters( 'the_excerpt', $excerpt ) ?>
        <?php echo edd_get_purchase_link( array( 'download_id' => $download->ID ) ) ?>
    </div>

<?php endforeach;

echo $view_args['after_widget'];