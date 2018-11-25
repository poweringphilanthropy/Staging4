<?php
/**
 * Template name: Homepage
 *
 * This is a homepage template with a content block at the top and campaigns grid below.
 *
 * @package 	Reach
 */

get_header();

?>
<main id="main" class="site-main site-content cf">  
	<div id="primary" class="content-area">
		<?php

		if ( have_posts() ) :
			while ( have_posts() ) :
				the_post();

				?>
				<article id="post-<?php the_ID() ?>" <?php post_class() ?>>
					<div class="shadow-wrapper">
						<div class="layout-wrapper">						
							<div class="media-container"><?php
								echo reach_get_media( array( 'split_media' => true ) );
							?></div><!-- .media-container -->
							<header>
								<h1 class="page-title"><?php the_title() ?></h1>
							</header>
							<div class="entry">
								<?php the_content() ?>
							</div><!-- .entry -->
						</div><!-- .layout-wrapper -->
					</div><!-- .shadow-wrapper -->
				</article><!-- post-<?php the_ID() ?> -->
				<?php

			endwhile;
		endif;

		?>
	</div><!-- #primary -->
	<div class="layout-wrapper">			
		<?php get_template_part( 'partials/campaign', 'grid' ) ?>
	</div><!-- .layout-wrapper -->
</main><!-- #main -->
<?php

get_footer();
