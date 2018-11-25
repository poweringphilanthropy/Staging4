<?php 
/**
 * Template Name: Leaderboard
 *
 */
get_header();

$campaigns = Charitable_Campaigns::query( array(
	'posts_per_page' => 9,
) );

// echo "<pre>";
// print_r($campaigns);
// echo "</pre>";
if ( have_posts() ) :
	while ( have_posts() ) :
		the_post();
?>
	<main class="site-main site-content cf leaderboard" id="main">
		<section class="leaderboard-heading">
			<div class="uk-width-1-1 ld-title">
				<h1><?php the_title(); ?></h1>
			</div>
			<?php  
			if ( has_post_thumbnail( $post->ID ) ) : 
			$thumb_url = get_the_post_thumbnail_url( $post->ID, 'full' );
			$thumb_url = aq_resize( $thumb_url, 1900, 600, true, true, true );
			?>
			<div class="uk-width-1-1 ld-image">
				<img src="<?php echo $thumb_url; ?>" alt="">
			</div>
			<?php  
			endif;
			?>
		</section>
		<section class="section-heading-button">
			<div class="heading-button-container">
				<div class="heading-button-col">
					<a class="heading-button" href="#campaign-list">DONATE TO A CAMPAIGN</a>
				</div>
				<div class="heading-button-col">
					<a class="heading-button" href="<?php echo home_url( 'create-campaign' ); ?>">CREATE A CAMPAIGN</a>
				</div>
			</div>
		</section>
		<div class="layout-wrapper">
			<div class="ld-content uk-grid">
				<div class="uk-width-medium-6-10 ld-desc">
					<?php the_content(); ?>
				</div>
				<div class="uk-width-medium-4-10 ld-stats">
					<div class="uk-grid">
						<div class="uk-width-small-4-10 ld-icon uk-text-center uk-vertical-align">
							<div class="uk-vertical-align-middle">
								<img class="ld-icon" src="<?php echo get_stylesheet_directory_uri(); ?>/media/LeaderBoardIcon.png" alt="">
							</div>
						</div>
						<div class="uk-width-small-6-10">
							<ul class="stats">
								<li><span class="count"><?php echo $campaigns->found_posts; ?></span> Campaigns</li>
								<li><span class="count">1,114</span> Supporters</li>
								<li><span class="count">$365,000</span> Raised</li>
							</ul>
						</div>
					</div>
				</div>
			</div>
			<div id="campaign-list" class="ld-campaigns">
				<div class="uk-width-1-1 ld-subtitle">
					<h2>Browse Campaigns:</h2>
				</div>
				<div class="campaigns-grid-wrapper">
					<?php 

				    charitable_template_campaign_loop( $campaigns, 3 );			

					wp_reset_postdata();

					?>
				</div>
			</div>
		</div><!-- .layout-wrapper -->
	</main>

<?php
	endwhile;
endif;
get_footer();