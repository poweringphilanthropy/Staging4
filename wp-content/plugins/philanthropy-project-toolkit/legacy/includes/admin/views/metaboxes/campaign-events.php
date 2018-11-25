<?php
global $post;

$disable = true;
$events = get_post_meta( $post->ID, '_campaign_events', true );

?>

<div class="charitable-metabox charitable-metabox-wrap">
	<?php  
	if(!empty($events)):
		foreach ( $events as $event_id ) :
			// $event = get_post($event_id);
   // //          echo "<pre>";
			// // print_r($event);
			// // echo "</pre>";
			?>
			<div class="charitable-metabox-block charitable-event">
				<div class="charitable-event-summary">
					<span class="summary"><?php echo get_the_title( $event_id ); ?></span>
					<span class="alignright">
						<a href="#" data-charitable-toggle="campaign_event_<?php echo esc_attr( $event_id )  ?>" data-charitable-toggle-text="<?php esc_attr_e( 'Close', 'charitable' ) ?>"><?php ($disable) ? _e( 'View', 'charitable' ) : _e( 'Edit', 'charitable' ); ?></a>&nbsp;&nbsp;&nbsp;
						
						<?php if(!$disable): ?>
						<a href="#" data-campaign-event-delete="<?php echo esc_attr( $event_id ) ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'pp-delete-event' ) ) ?>"><?php _e( 'Delete', 'charitable' ) ?></a>
						<?php endif; ?>
					</span>
				</div>

				<div id="campaign_event_<?php echo $event_id ?>" class="charitable-metabox-wrap" style="display: none;"> 
					<div id="campaign-event" class="charitable-media-block">
				        <div class="event-image charitable-media-image">
				            <?php  
				            $thumbnail_id = get_post_thumbnail_id( $event_id );
                			echo wp_get_attachment_image( $thumbnail_id );
				            ?>
				        </div><!--.event-image-->
				        <div class="event-facts charitable-media-body">
				            <h3 class="event-name"><a href="<?php echo get_edit_post_link( $event_id ); ?>"><?php // echo get_the_title( $event_id ); ?></a></h3>
				            <?php echo get_post_field( 'post_content', $event_id ); ?>
				        </div><!--.event-facts-->        
				    </div><!--#campaign-event-->
				</div>
			</div>
			<?php

        endforeach;
    else:
    	?>
		<p><?php _e('No event.', 'pp-toolkit'); ?></p>
    	<?php

    endif;
	?>
</div>