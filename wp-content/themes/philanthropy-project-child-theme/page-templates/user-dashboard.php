<?php
/**
 * Template name: User Dashboard
 *
 * @package 	Reach
 */

get_header();

?>
<main id="main" class="site-main site-content cf user-dashboard-content <?php echo ( charitable_is_page( 'campaign_submission_page' ) ) ? 'campaign_submission_page' : ''; ?>">  
	<div class="layout-wrapper">
		<div id="primary" class="content-area">
		<?php

		if ( have_posts() ) :
			while ( have_posts() ) :
				the_post();

				?>
				<div class="dashboard-title">
					<h1><?php the_title(); ?></h1>
				</div>
				<?php

				// get_template_part( 'partials/content', 'user-dashboard' );
				get_template_part( 'partials/content', 'user-dashboard-side-nav' );

			endwhile;
		endif;

		?>
		</div><!-- #primary -->
	</div><!-- .layout-wrapper -->
</main><!-- #main -->
<?php

get_footer();
