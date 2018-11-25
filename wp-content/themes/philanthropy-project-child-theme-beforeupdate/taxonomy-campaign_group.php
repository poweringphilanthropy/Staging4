<?php 
global $wp_query;

$term_id = get_queried_object_id();
$image = get_term_meta($term_id, 'campaign_group' ,'_group_image');
$content = get_term_meta($term_id, 'campaign_group' ,'_group_content');

/**
 * Get total donations for campaign under this group
 * @var [type]
 */
$args = array(
	'post_type' => 'campaign',
	'tax_query' => array(
    	array(
		    'taxonomy' => 'campaign_group',
		    'field' => 'id',
		    'terms' => $term_id
	    )
	),
	'post_status' => 'any',
	'fields' => 'ids'
);

$query = new WP_Query( $args );
$campaign_ids = $query->posts;
$amount = charitable_get_table( 'campaign_donations' )->get_campaign_donated_amount( $campaign_ids );
// echo number_format($amount, 0);
$currency_helper = charitable_get_currency_helper();

$donors = philanthropy_get_multiple_donors($campaign_ids);

get_header();

?>
	<main class="site-main site-content cf leaderboard" id="main">
		<section class="leaderboard-heading">
			<div class="uk-width-1-1 ld-title">
				<h1><?php the_title(); ?></h1>
			</div>
			<?php  
			if ( !empty($image) ) : 
			$thumb_url = wp_get_attachment_url( $image );
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
					<?php echo $content; ?>
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
								<li><span class="count"><?php echo $wp_query->post_count; ?></span> Campaigns</li>
								<li><span class="count"><?php echo $donors->count(); ?></span> Supporters</li>
								<li><span class="count"><?php echo $currency_helper->get_monetary_amount( $amount ); ?></span> Raised</li>
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

					/**
					 * This renders a loop of campaigns that are displayed with the
					 * `reach/charitable/campaign-loop.php` template file.
					 *
					 * @see 	charitable_template_campaign_loop()
					 */
					charitable_template_campaign_loop( false, 3 );

					reach_paging_nav( __( 'Older Campaigns', 'reach' ), __( 'Newer Campaigns', 'reach' ) );

					?>
				</div>
			</div>
		</div><!-- .layout-wrapper -->
	</main>

<?php
get_footer();