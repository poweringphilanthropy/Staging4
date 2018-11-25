<?php 
/*
 Template name: Featured Campaigns
 */

get_header();	

get_template_part( 'partials/banner', 'page' ); 
?>			
<?php //get_template_part( 'partials/featured-image' ); ?>
<div class="layout-wrapper">
	<main class="site-main content-area" role="main">
		<?php 
		if ( have_posts() ) :
			while ( have_posts() ) :
				the_post() ?>						
				<article id="post-<?php the_ID(); ?>" <?php post_class( 'home-content' ) ?>>
					<?php the_content() ?>
				</article>
			<?php 
			endwhile; 
		endif; 
		?>
		<div class="campaigns-grid-wrapper">
			<?php 
			$campaigns = Charitable_Campaigns::query( array( 
		        'post__not_in' => array( PHILANTHROPY_PROJECT_CAMPAIGN_ID )
		    ) );

		    charitable_template_campaign_loop( $campaigns, 3 );			

			wp_reset_postdata();

    		if ( $campaigns->max_num_pages > 1 ) : ?> 

		        <p class="center">
		            <a class="button button-alt" href="<?php echo site_url( apply_filters( 'reach_previous_campaigns_link', '/campaigns/page/2/' ) ) ?>">
		                <?php echo apply_filters( 'reach_previous_campaigns_text', __( 'Previous Campaigns', 'reach' ) ) ?>
		            </a>
		        </p>

    		<?php endif ?>
		</div>
	</main><!-- .site-main -->	
	<?php get_sidebar( 'get-inspired' ) ?>					
</div><!-- .layout-wrapper -->
<?php

get_footer();
