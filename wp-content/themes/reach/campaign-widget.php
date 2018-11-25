<?php
/**
 * Campaign widget template.
 *
 * This template is only used if Charitable is active.
 *
 * @package Reach
 */

remove_action( 'charitable_campaign_content_loop_after', 'charitable_template_campaign_description', 4 );

get_header( 'widget' );

if ( have_posts() ) :
	while ( have_posts() ) :
		the_post();

		$campaign = new Charitable_Campaign( get_post() );

		?>
		<div class="campaign-widget campaign block cf" style="width: 275px;">
			
			<?php charitable_template_campaign_loop_thumbnail( $campaign ) ?>
			
			<div class="iframe-text-wrapper">
				<div class="title-wrapper">
					<h3 class="block-title">
						<a href="<?php the_permalink() ?>" title="<?php the_title_attribute() ?>" target="_parent"><?php the_title() ?></a>
					</h3>
				</div><!-- .title-wrapper -->
				<?php charitable_template_campaign_description( $campaign ) ?>
			</div><!-- .iframe-text-wrapper -->
			<?php

			reach_template_campaign_loop_stats( $campaign );

			reach_template_campaign_loop_creator( $campaign );

			?>            
		</div><!-- .campaign-widget -->
		<?php

	endwhile;
endif;

get_footer( 'widget' );
