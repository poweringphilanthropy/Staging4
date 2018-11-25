<?php
/**
 * Campaign post type archive.
 *
 * This template is only used when Charitable is activated.
 *
 * @package     Reach
 */
global $wp_query;

get_header('dashboard');

$term = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) );
$color = get_term_meta( $term->term_id, '_dashboard_color', true );

$style_bg_color = '';
$style_color = '';
if(!empty($color)){
    $style_bg_color = ' style="background:'.$color.'"';
    $style_color = ' style="color:'.$color.'"';
}

$campaign_ids = wp_list_pluck( $wp_query->posts, 'ID' );
$leaderboard_data = new PP_Leaderboard_Data($campaign_ids);

$currency_helper = charitable_get_currency_helper();

$report = new PP_Campaign_Donation_Reports($campaign_ids);
$dashboard_link = trailingslashit( get_term_link( $term ) ) . 'report/';
$report_url = add_query_arg( array(
	'download-dashboard-reports' => $term->term_id,
	'taxonomy' => $term->taxonomy,
), wp_nonce_url( $dashboard_link, 'pp-download-report', 'key' ) );

$download_report_transactions_url = add_query_arg( array(
	'type' => 'transactions'
), $report_url );

// echo "<pre>";
// print_r($download_report_transactions_url);
// echo "</pre>";
// exit();

?>  
<main id="main" class="site-main site-content cf">  
	<?php if( post_password_required() ): ?>
	<div id="dashboard-report" class="layout-wrapper">
		<?php echo get_the_password_form(); ?>
	</div>
	<?php else: ?>
	<section class="leaderboard-heading">
        <div class="uk-width-1-1 ld-title">
            <h1 <?php echo $style_bg_color; ?>><?php echo $term->name; ?></h1>
        </div>
    </section>
	<div id="dashboard-report" class="layout-wrapper">
		<div id="primary" class="content-area no-sidebar">
			<div class="charitable-user-campaigns pp-campaign-report">
				<div class="report-summary campaign-summary report-section">
					<div class="section-content ld-content">
						<div class="uk-grid ld-stats">
							<div class="uk-width-small-5-10 ld-icon uk-text-center uk-vertical-align">
								<div class="uk-vertical-align-middle dashboard-report-stat-image">
									<img class="ld-icon" src="<?php echo get_stylesheet_directory_uri(); ?>/media/black_Impact.png" alt="">
								</div>
							</div>
							<div class="uk-width-small-5-10">
								<ul class="stats">
									<li><span class="count" <?php echo $style_color; ?>><?php echo $wp_query->found_posts; ?></span> Campaigns</li>
		                            <li><span class="count" <?php echo $style_color; ?>><?php echo $leaderboard_data->get_total_donors(); ?></span> Supporters</li>
		                            <li><span class="count" <?php echo $style_color; ?>><?php echo $currency_helper->get_monetary_amount( $leaderboard_data->get_total_donations(), 0 ); ?></span> Raised</li>
		                            <?php if( get_term_meta( $term->term_id, '_enable_log_service_hours', true ) == 'yes' ): ?>
		                            <li><span class="count" <?php echo $style_color; ?>><?php echo $leaderboard_data->get_total_service_hours(true); ?></span> Service Hours</li>
		                            <?php endif; ?>
								</ul>
							</div>
						</div>
					</div>
				</div>
				
				<div id="dashboard-campaigns-earnings" class="section-content" style="text-align: center;">
					<div class="download-report-button">
					  	<a href="<?php echo $download_report_transactions_url; ?>">
							<div class="inner icon">
								<img src="<?php echo pp_toolkit()->directory_url . 'assets/img/my-campaigns/download-report.png'; ?>" alt="icon">
						  	</div>
						  	<div class="inner">
						  		<div>Download All Transactions</div>
						  	</div>
					  	</a>
					</div>
				</div>
				<div id="dashboard-report-wrapper" data-timestamp="<?php echo current_time( 'timestamp' ); ?>" data-campaigns="<?php echo implode(',', $campaign_ids ); ?>"> </div>
				<div class="loading-report" style="text-align: center;">
					<img src="<?php echo pp_toolkit()->directory_url . 'assets/img/spinner_22x22.gif'; ?>" alt="">
					<br>
					<h4>Generating report data..</h4>
				</div>
			</div>
		</div><!-- #primary -->            
	</div><!-- .layout-wrapper -->
	<?php 
	endif; // password required ?>
</main><!-- #main -->   

<?php do_action( 'after_dashboard_report_template', $term ); ?>

<?php pp_toolkit_template('reports/dashboard/js-templates/report-campaigns.php', array('report' => $report, 'download_report_url' => $report_url, 'color' => $color)); ?>
<?php pp_toolkit_template('reports/dashboard/js-templates/report-fundraisers.php', array('report' => $report, 'download_report_url' => $report_url, 'color' => $color)); ?>
<?php pp_toolkit_template('reports/dashboard/js-templates/report-donations.php', array('report' => $report, 'download_report_url' => $report_url, 'color' => $color)); ?>
<?php pp_toolkit_template('reports/dashboard/js-templates/report-tickets.php', array('report' => $report, 'download_report_url' => $report_url, 'color' => $color)); ?>
<?php pp_toolkit_template('reports/dashboard/js-templates/report-merchandises.php', array('report' => $report, 'download_report_url' => $report_url, 'color' => $color)); ?>

<?php

get_footer('dashboard');
