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
<main id="main" class="site-main site-content cf" role="main">  
	<div id="primary" class="content-area">
		<?php 

		if ( have_posts() ) :
            while ( have_posts() ) :
                the_post();

                ?>
                <article id="post-<?php the_ID() ?>" <?php post_class() ?>>
					<div class="shadow-wrapper">
						<div class="layout-wrapper">	
							<div class="entry">
								<?php the_content() ?>
							</div><!-- .entry -->
						</div><!-- .layout-wrapper -->
					</div><!-- .shadow-wrapper -->
				</article><!-- post-<?php the_ID() ?> -->
				<div class="layout-wrapper">			
					<div class="second-content">
						<?php //the_field('second_content') ?>
					</div>
					<?php get_template_part('partials/campaign', 'grid') ?>
				</div>
			<?php 

			endwhile;
		endif;

		?>
	</div> <!-- #primary -->
</main><!-- #main -->
<?php 

get_footer();