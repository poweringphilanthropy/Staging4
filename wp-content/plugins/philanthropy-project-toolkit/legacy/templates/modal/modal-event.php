<?php 
/**
 * Displays the modal content
 *
 * @author Lafif <[<email address>]>
 * @since   1.0
 */

$campaign = $view_args[ 'campaign' ];
$event_ids = $view_args['events'];
$form_id     = (isset($view_args[ 'form_id' ])) ? $view_args[ 'form_id' ] : $view_args[ 'id' ];
?>
<div class="p_popup" data-p_popup="<?php echo $view_args[ 'id' ]; ?>">
    <div class="p_popup-inner">
        <a class="p_popup-close" data-p_popup-close="<?php echo $view_args[ 'id' ] ?>" href="#">x</a>
        
    	<form id="<?php echo $form_id; ?>" class="philanthropy-modal form-default" data-action="tickets" method="post" style="padding: 0px;">
	        <div class="p_popup-header">
				<h2 class="p_popup-title"><span><?php echo $view_args[ 'label' ] ?></span></h2>
				<div class="p_popup-notices uk-width-1-1"></div>
	        </div>
	        <div class="p_popup-content">

	        	<input type="hidden" name="campaign_id" value="<?php echo $campaign->ID; ?>">
	        	
				<?php  
				// add nonce
				wp_nonce_field( 'form_modal_tickets', '_nonce' );

				$event_with_tickets = array();
				foreach ($event_ids as $key => $event_id) {
					$event = get_post($event_id);
					setup_postdata( $GLOBALS['post'] =& $event );

					?>
					<div class="modal-items">
						<div class="uk-grid item-content">
							<div class="uk-width-1-1">
								<div class="item-title">
									<span><?php the_title(); ?></span>
								</div>
								<?php echo tribe_events_event_schedule_details( get_the_ID(), '<div class="event-schedule">', '</div>'); ?>
							</div>

							<?php if(has_post_thumbnail()): ?>
							<div class="uk-width-1-1 p_popup-item-image uk-width-small-4-10">
								<?php echo get_the_post_thumbnail( get_the_ID(), array(250,250) ); ?>
							</div>
							<?php endif; ?>

							<div class="uk-width-1-1 uk-width-small-6-10">
								<div class="uk-width-1-1">
									<?php the_content(); ?>

									<?php do_action( 'tribe_events_single_event_after_the_content' ) ?>
									
								</div>
							</div>

							<?php if ( tribe_has_organizer() ) { ?>
							<div class="uk-width-1-1 uk-width-small-5-10">
								<?php tribe_get_template_part( 'modules/meta/organizer' ); ?>
							</div>
							<?php } ?>

							<div class="uk-width-1-1 uk-width-small-5-10">
								<?php  
								tribe_get_template_part( 'modules/meta/venue' );
								// tribe_get_template_part( 'modules/meta/map' );
								?>
							</div>

							
						</div>
						<?php 

						if ( $campaign->has_ended() ) :
							echo sprintf('<p>Tickets are no longer available</p>');
						else:
							$ticket_ids = Tribe__Tickets_Plus__Commerce__EDD__Main::get_instance()->get_tickets_ids( get_the_ID() );
							if(!empty($ticket_ids)){
								$event_with_tickets[] = get_the_ID();
							}

							// hooks for tickets
							do_action( 'philanthropy_modal_event_tickets', $ticket_ids );
					
						endif; ?>
					</div>

					<?php
				} // end foreach

				wp_reset_postdata();

	        	?>
	        </div>
	        <div class="p_popup-footer">
	        	<?php if(!empty($event_with_tickets) && !$campaign->has_ended() ): ?>
				<div class="p_popup-submit-field">
					<button disabled="disabled" class="button button-primary philanthropy-add-to-cart uk-width-1-1 uk-width-small-5-10 uk-width-medium-4-10" type="submit" name="add_to_cart"><?php _e( 'Add to Cart', 'philanthropy' ); ?></button>
					<a href="<?php echo edd_get_checkout_uri(); ?>" class="button philanthropy-go-to-checkout uk-width-1-1 uk-width-small-5-10"><?php _e('Proceed to Checkout', 'philanthropy'); ?></a>
				</div>
			<?php endif; ?>
	        </div>
	    </form>
    </div>
</div>