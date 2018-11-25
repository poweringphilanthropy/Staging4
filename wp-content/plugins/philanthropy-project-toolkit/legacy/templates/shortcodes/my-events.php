<?php
/**
 * The template used to display the user's events.
 *
 * @author  Studio 164a
 * @since   1.0.0
 * @version 1.0.0
 */
global $wp;

$user = wp_get_current_user();
$events = tribe_get_events( array( 
    'posts_per_page' => -1, 
    'author' => $user->ID,
    'orderby'              => 'event_date',
    'order'                => 'DESC',
) );

/**
 * @hook    charitable_user_events_before
 */
do_action('charitable_user_events_before');
?>

<header class="entry-header">
    <?php the_title( '<h1 class="entry-title">', '</h1>' ) ?>
</header><!-- .entry-header -->
<?php 
if ( ! empty( $events ) ) : ?>

<table class="charitable-user-events pp-responsive-table">
    <thead>
        <tr>
            <th><?php _e( 'Event', 'pp-toolkit' ) ?></th>
            <th><?php _e( 'Linked Campaign', 'pp-toolkit' ) ?></th>
            <th><?php _e( 'Event Date', 'pp-toolkit' ) ?></th>
            <th><?php _e( '# Attendees', 'pp-toolkit' ) ?></th>
            <th><?php _e( 'Actions', 'pp-toolkit' ) ?></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ( $events as $event ) : 
        $campaign = get_post_meta( $event->ID, '_event_linked_campaign', true );

        // in case campaign deleted
        if( empty($campaign) || !get_permalink( $campaign ) )
            continue;

        $download_args = array(
            //GUM EDIT
            'event_id' => $event->ID,
            'download_attendees' => true,
            'download_attendees_nonce' => wp_create_nonce( 'download_attendees_' . $event->ID ),
            /*'post_type' => 'tribe_events',
            'page' => 'tickets-attendees',
            'event_id' => $event->ID, 
            'attendees_csv' => true,
            'attendees_csv_nonce' => wp_create_nonce( 'attendees_csv_nonce_' . $event->ID ),
            */
        );
        $attendees = Tribe__Tickets__Tickets::get_event_attendees( $event->ID );
        // print '<pre>';
        // print_r($attendees);
        // print '</pre>';
        
        
        // $ticket_id = $attendees[0]['product_id'];
        //$attendee_id = $attendees[0]['attendee_id'];
        //$ticket_id = 8719;
        // $attendee_meta = get_post_meta( $ticket_id, Tribe__Tickets_Plus__Meta::META_KEY, true );
        // $attendee_name = $attendee_meta['ticket-holder-name'];

        // print '<pre>';
        // print_r($attendee_meta);
        // print '</pre>';

        //$meta_data = get_post_meta( $item['attendee_id'], Tribe__Tickets_Plus__Meta::META_KEY, true );
        //$ticket_meta = Tribe__Tickets_Plus__Main::instance()->meta();
        //$ticket_meta_fields = $ticket_meta->get_meta_fields_by_ticket( $ticket_id );
        //GUM EDIT
        //from admin ?post_type=tribe_events&page=tickets-attendees&event_id=8831&attendees_csv=1&attendees_csv_nonce=50868db9ff
        
        // from page ?event_id=8831&download_attendees=1&download_attendees_nonce=c4f6a1a189
        ?>
        <tr>
            <td data-label="<?php _e( 'Event', 'pp-toolkit' ) ?>"><?php //GUM EDIT md($campaign,'var_dump',1); ?><a href="<?php echo get_permalink( $event->ID ) ?>" rel="bookmark"><?php echo get_the_title( $event->ID ) ?></a></td>
            <td data-label="<?php _e( 'Linked Campaign', 'pp-toolkit' ) ?>">
                <?php if ( $campaign ) : ?>
                    <a href="<?php echo get_permalink( $campaign ) ?>" rel="bookmark"><?php echo get_the_title( $campaign ) ?></a>
                <?php else : ?>
                    -
                <?php endif ?>
            </td>
            <td data-label="<?php _e( 'Event Date', 'pp-toolkit' ) ?>"><?php echo tribe_events_event_schedule_details( $event->ID ) ?></td>
            <td data-label="<?php _e( '# Attendees', 'pp-toolkit' ) ?>"><?php echo count( $attendees ) ?></td>
            <td data-label="<?php _e( 'Actions', 'pp-toolkit' ) ?>" class="<?php echo ( count( $attendees ) ) ? '' : 'hide-mobile'; ?>">
                <?php if ( count( $attendees ) ) : ?>
                    <a href="<?php echo home_url(add_query_arg($download_args,$wp->request)); ?>" target="_blank"><?php _e( 'Download Attendees List', 'pp-toolkit' ) ?></a> 
                <?php endif ?>
            </td>
        </tr>
<?php
// print '<pre>';
// print_r($attendees);
// print '</pre>';
?>
    <?php endforeach ?>
    </tbody>
</table>

<?php 


endif;