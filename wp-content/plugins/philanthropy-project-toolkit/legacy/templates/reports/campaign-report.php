<?php
/**
 * The template for displaying the campaign heading image
 *
 * Override this template by copying it to your-child-theme/charitable/reports/campaign-report.php
 *
 * @author lafif <[<email address>]>
 * @since 1.0 [<description>]
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * @var 	Charitable_Campaign
 */
$campaign = $view_args['campaign'];

$max_table_display = 5;

$reports = PP_Reports::get_campaign_reports($campaign->ID);

global $wp;
$download_args = array(
    'download_campaign_report' => 'all',
	'campaign_id' => $campaign->ID,
    'report_nonce' => wp_create_nonce( 'download_campaign_report-' . $campaign->ID ),
);

?>
<div class="charitable-user-campaigns pp-campaign-report">
	<div class="report-summary campaign-summary report-section">
		<div class="report-title">
			<?php echo get_the_title( $campaign->ID ); ?>
		</div>
		<div class="section-content">
			<div class="uk-grid">
				<div class="uk-width-1-1 uk-width-medium-2-5">
					<div class="cf">
	                    <?php charitable_template( 'campaign/progress-barometer.php', array( 'campaign' => $campaign ) ); ?>
	                    <?php charitable_template( 'campaign/stats.php', array( 'campaign' => $campaign ) ); ?>
	                    <?php charitable_template_campaign_time_left($campaign); ?>
	                </div>
				</div>
				<div class="uk-width-1-1 uk-width-medium-1-5">
				</div>
				<div class="uk-width-1-1 uk-width-medium-2-5">
					<div class="download-all">
						<div class="download-report-button">
						  	<a href="<?php echo home_url(add_query_arg($download_args, $wp->request)); ?>">
								<div class="inner icon">
									<img src="<?php echo pp_toolkit()->directory_url . 'assets/img/my-campaigns/download-report.png'; ?>" alt="icon">
							  	</div>
							  	<div class="inner">
							  		<div>Download complete list<br>of donations, tickets +<br>merchandise purchased</div>
							  	</div>
						  	</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<?php  

	/**
	 * FUNDRAISERS
	 */
	$report_fundraisers = (isset($reports['fundraisers'])) ? $reports['fundraisers'] : array();
	$total_fundraising = (isset($report_fundraisers['total_amount'])) ? $report_fundraisers['total_amount'] : 0;
	$fundraiser_details = (isset($report_fundraisers['details']) && is_array($report_fundraisers['details'])) ? $report_fundraisers['details'] : array();
	if(!empty($fundraiser_details)){

		$download_args['download_campaign_report'] = 'fundraisers';

		pp_toolkit_template('reports/report-fundraisers.php', array(
		    'campaign' => $campaign,
		    'total_fundraising' => $total_fundraising,
		    'fundraiser_details' => $fundraiser_details,
		    'max_table_display' => $max_table_display,
		    'report_url' => home_url(add_query_arg($download_args, $wp->request)),
		));
	}
	?>
	
	<?php  
	/**
	 * DONATIONS
	 */
	$report_donations = (isset($reports['donations'])) ? $reports['donations'] : array();
	$total_donations = (isset($report_donations['total_amount'])) ? $report_donations['total_amount'] : 0;
	$donation_details = (isset($report_donations['details']) && is_array($report_donations['details'])) ? $report_donations['details'] : array();
	
	if(!empty($donation_details)){
		$download_args['download_campaign_report'] = 'donations';

		pp_toolkit_template('reports/report-donations.php', array(
		    'campaign' => $campaign,
		    'total_donations' => $total_donations,
		    'donation_details' => $donation_details,
		    'max_table_display' => $max_table_display,
		    'report_url' => home_url(add_query_arg($download_args, $wp->request)),
		));
	}
	?>
	
	<?php  
	/**
	 * TICKETS
	 */
	$report_tickets = (isset($reports['tickets'])) ? $reports['tickets'] : array();
	$total_tickets = (isset($report_tickets['total_amount'])) ? $report_tickets['total_amount'] : 0;
	$tickets_details = (isset($report_tickets['details']) && is_array($report_tickets['details'])) ? $report_tickets['details'] : array();
	$options_details = (isset($report_tickets['qty_by_options']) && is_array($report_tickets['qty_by_options'])) ? $report_tickets['qty_by_options'] : array();
	
	if(!empty($tickets_details)){
		$download_args['download_campaign_report'] = 'tickets';

		pp_toolkit_template('reports/report-tickets.php', array(
		    'campaign' => $campaign,
		    'total_tickets' => $total_tickets,
		    'tickets_details' => $tickets_details,
		    'options_details' => $options_details,
		    'max_table_display' => $max_table_display,
		    'report_url' => home_url(add_query_arg($download_args, $wp->request)),
		));
	}
	?>
	
	<?php  
	/**
	 * MERCHANDISE
	 */
	$report_merchandises = (isset($reports['merchandises'])) ? $reports['merchandises'] : array();
	$total_merchandises = (isset($report_merchandises['total_amount'])) ? $report_merchandises['total_amount'] : 0;
	$merchandises_details = (isset($report_merchandises['details']) && is_array($report_merchandises['details'])) ? $report_merchandises['details'] : array();
	$options_details = (isset($report_merchandises['qty_by_options']) && is_array($report_merchandises['qty_by_options'])) ? $report_merchandises['qty_by_options'] : array();
	
	if(!empty($merchandises_details)){
		$download_args['download_campaign_report'] = 'merchandises';

		pp_toolkit_template('reports/report-merchandises.php', array(
		    'campaign' => $campaign,
		    'total_merchandises' => $total_merchandises,
		    'merchandises_details' => $merchandises_details,
		    'options_details' => $options_details,
		    'max_table_display' => $max_table_display,
		    'report_url' => home_url(add_query_arg($download_args, $wp->request)),
		));
	}
	?>

</div>