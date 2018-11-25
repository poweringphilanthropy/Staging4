<?php 
/**
 * Checkout page template.
 *
 * This template is only used if Easy Digital Downloads is active.
 *
 * @package Reach
 */

if ( ! reach_has_edd() ) {
	return;
}

if ( class_exists( 'Charitable_EDD_Cart' ) ) :
	$cart      = new Charitable_EDD_Cart( edd_get_cart_contents(), edd_get_cart_fees( 'item' ) );
	$campaigns = $cart->get_benefits_by_campaign();
else :
	$campaigns = array();
endif;

if(class_exists('PP_Team_Fundraising')){
	$campaigns = PP_Team_Fundraising::remove_parent_from_campaign_benefits($campaigns);
}


get_header( 'stripped' );

?>    
<main id="main" class="site-main site-content cf">  
	<div class="layout-wrapper">    
		<div id="primary" class="content-area <?php if ( empty( $campaigns ) ) : ?>no-sidebar<?php endif ?>">      
		<?php

		if ( have_posts() ) :
			while ( have_posts() ) :
				the_post();

				get_template_part( 'partials/content', 'page' );

			endwhile;
		endif;

		?>
		</div><!-- #primary -->
		<?php if ( ! empty( $campaigns ) ) : ?>
			<aside id="secondary" class="campaign-benefiting" role="complementary">

				<p class="header"><?php echo _n( 'Thank you for supporting this campaign', 'Thank you for supporting these campaigns', count( $campaigns ), 'reach' ) ?></p>
				
				<?php foreach ( $campaigns as $campaign_id => $benefits ) :

					if ( has_post_thumbnail( $campaign_id ) ) :

						echo get_the_post_thumbnail( $campaign_id, 'reach-post-thumbnail-medium' );

					endif ?>

					<div class="campaign-title"><a href="<?php echo get_permalink( $campaign_id ) ?>" title="<?php the_title_attribute() ?>"><?php echo get_the_title( $campaign_id ) ?></a></div>

				<?php endforeach ?>

			</aside>
		<?php endif ?>
	</div><!-- .layout-wrapper -->
</main><!-- #main -->
<?php

get_footer( 'stripped' );
