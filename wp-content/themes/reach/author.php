<?php
/**
 * The template for displaying public user profiles.
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package     Reach
 */

$author = reach_get_current_author();

$first_name = strlen( $author->first_name ) ? $author->first_name : $author->display_name;

get_header();

?>
<main id="main" class="site-main site-content cf">  
	<div class="layout-wrapper">
		<div id="primary" class="content-area">
			<?php get_template_part( 'partials/banner', 'author' ) ?>
			<div class="entry-block block">
				<div class="entry cf">
					<div class="author-description">
						<?php get_template_part( 'partials/author', 'avatar' ) ?>                        
						<div class="author-facts">
							<h2><?php printf( _x( 'About %s', 'about person', 'reach' ), $first_name ) ?></h2>
							<p><?php printf( __( 'Joined %s', 'reach' ), date_i18n( 'F Y', strtotime( $author->user_registered ) ) ) ?></p>
							<?php echo reach_author_edit_profile_link( $author->ID ) ?>                            
						</div><!-- .author-facts -->            
						<div class="author-bio">                
							<h3><?php _e( 'Bio', 'reach' ) ?></h3>
							<?php echo apply_filters( 'the_content', $author->description ) ?>
						</div><!-- .author-bio -->
						<ul class="author-links">
							<?php if ( $author->user_url ) : ?>
								<li class="with-icon" data-icon="&#xf0c1;">
									<a target="_blank" href="<?php echo esc_url( $author->user_url ) ?>" title="<?php printf( esc_attr__( 'Visit %s\'s website', 'reach' ), $author->display_name ) ?>"><?php echo reach_condensed_url( $author->user_url ) ?></a>
								</li>
							<?php endif ?>

							<?php if ( $author->twitter ) : ?>
								<li class="with-icon" data-icon="&#xf099;">
									<a target="_blank" href="<?php echo esc_url( $author->twitter ) ?>" title="<?php printf( esc_attr__( 'Visit %s\'s Twitter profile', 'reach' ), $author->display_name ) ?>"><?php echo reach_condensed_url( $author->twitter ) ?></a>
								</li>
							<?php endif ?>

							<?php if ( $author->facebook ) : ?>
								<li class="with-icon" data-icon="&#xf09a;">
									<a target="_blank" href="<?php echo esc_url( $author->facebook ) ?>" title="<?php printf( esc_attr__( 'Visit %s\'s Facebook profile', 'reach' ), $author->display_name ) ?>"><?php echo reach_condensed_url( $author->facebook ) ?></a>
								</li>
							<?php endif ?>
						</ul><!-- .author-links -->
					</div><!-- .author-description -->      
					<div class="author-activity">                        
						<?php get_template_part( 'partials/author', 'activity' ) ?>
					</div><!-- .author-activity -->
				</div><!-- .entry -->
			</div><!-- .entry-block -->
		</div><!-- #primary -->            
	</div><!-- .layout-wrapper -->
</main><!-- #main -->   
<?php

get_footer();
