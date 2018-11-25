<?php
/**
 * Display the events that are connected to a campaign.
 *
 * @author  Studio 164a
 * @since   1.0.0
 */

$widget_title = apply_filters('widget_title', $view_args['title']);
$campaign_id = $view_args['campaign_id'] == 'current' ? get_the_ID() : $view_args['campaign_id'];
$events = get_post_meta($campaign_id, '_campaign_events', true);

if (false === $events || empty($events))
    return;


echo $view_args['before_widget'];

if (!empty($widget_title))
    echo $view_args['before_title'].$widget_title.$view_args['after_title'];


foreach ($events as $event_id):
    $GLOBALS['post'] = get_post($event_id);
    $tickets = pp_get_event_tickets_ids($event_id);

    $has_purchaseable_tickets = false;

    ?>

    <div class="charitable-connected-event widget-block">

        <?php if ( $view_args['show_featured_image'] ) : ?>
            <figure>
                <div class="crop-height">
                    <?= get_the_post_thumbnail($event_id) ?>
                </div>
            </figure>
        <?php endif ?>

        <h4 class="event-title download-title">
	        <a class="tribe-event-url" href="<?= get_the_permalink($event_id) ?>" title="<?= get_the_title($event_id) ?>">
	        <?= get_the_title($event_id) ?>
	        </a>
        </h4>

        <?= tribe_events_event_schedule_details(get_the_ID(), '<div class="event-schedule">', '</div>') ?>
        <?= apply_filters('the_content', get_post_field('post_content', $event_id)) ?>

        <?php Tribe__Tickets_Plus__Commerce__EDD__Main::get_instance()->front_end_tickets_form(''); ?>

    </div>

<?php endforeach;

wp_reset_postdata();

echo $view_args['after_widget'];