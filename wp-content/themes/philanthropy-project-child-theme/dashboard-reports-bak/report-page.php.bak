<?php
/**
 * DASHBOARD REPORT
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

get_header('dashboard');
	
	if ( have_posts() ) :
		while ( have_posts() ) :
			the_post();

			if ( post_password_required( $post ) ):
				get_template_part( 'partials/content', 'page' );
			else: 

			$leaderboard = new PP_Charitable_Leaderboard( $post );

			$style_bg_color = '';
			$style_color = '';
			$color = $leaderboard->get_color_accent();

			// if(!empty($color)){
			// 	$style_bg_color = ' style="background:'.$color.'"';
			// 	$style_color = ' style="color:'.$color.'"';
			// }

			$campaign_ids = $leaderboard->get_campaign_ids();

			$currency_helper = charitable_get_currency_helper();

			?>
			<div id="dashboard-report" class="layout-wrapper">
				<div class="charitable-user-campaigns pp-campaign-report">
					<div class="report-summary campaign-summary report-section">
						<div class="report-title">
							<?php echo get_the_title(); ?>
						</div>
						<div class="section-content ld-content">
							<div class="uk-grid ld-stats">
								<div class="uk-width-small-5-10 ld-icon uk-text-center uk-vertical-align">
									<div class="uk-vertical-align-middle dashboard-report-stat-image">
										<img class="ld-icon" src="<?php echo get_stylesheet_directory_uri(); ?>/media/black_Impact.png" alt="">
									</div>
								</div>
								<div class="uk-width-small-5-10">
									<ul class="stats">
										<li><span class="count" <?php echo $style_color; ?>><?php echo $leaderboard->get_total_campaigns(); ?></span> Campaigns</li>
										<li><span class="count" <?php echo $style_color; ?>><?php echo $leaderboard->get_total_supporters(); ?></span> Supporters</li>
										<li><span class="count" <?php echo $style_color; ?>><?php echo $currency_helper->get_monetary_amount( $leaderboard->get_donation_amount(), 0 ); ?></span> Raised</li>
										<?php if($leaderboard->is_track_service_hours_enable()): ?>
										<li><span class="count" <?php echo $style_color; ?>><?php echo $leaderboard->get_total_service_hours(true); ?></span> Service Hours</li>
										<?php endif; ?>
									</ul>
								</div>
							</div>
						</div>
					</div>

					<div class="report-section">
						<div class="section-title">
							<div class="uk-grid">
								<div class="uk-width-1-1 uk-width-medium-1-3">
									<div class="report-title">
										CAMPAIGNS
									</div>
								</div>
								<div class="uk-width-1-1 uk-width-medium-2-3">
									<div class="uk-grid">
										<div class="uk-width-1-1 uk-width-medium-1-3">
											<div class="block-amount">
												<div class="inner icon">
													<img alt="icon" src="<?php echo pp_toolkit()->directory_url . 'assets/img/my-campaigns/icon-dollar.png'; ?>">
												</div>
												<div class="inner">
													<div class="amount">
														<?php echo $currency_helper->get_monetary_amount( $leaderboard->get_donation_amount(), 0 ); ?>
													</div>
													<div class="sub">
														Total
													</div>
												</div>
											</div>
										</div>
										<div class="uk-width-1-1 uk-width-medium-1-3">
											<div class="block-amount">
												<div class="inner icon">
													<img alt="icon" src="<?php echo pp_toolkit()->directory_url . 'assets/img/my-campaigns/icon-campaign.png'; ?>">
												</div>
												<div class="inner">
													<div class="amount">
														<?php echo $leaderboard->get_total_campaigns(); ?>
													</div>
													<div class="sub">
														Campaigns
													</div>
												</div>
											</div>
										</div>
										<div class="uk-width-1-1 uk-width-medium-1-3">
											<div class="block-amount">
												<div class="inner icon">
													<img alt="icon" src="<?php echo pp_toolkit()->directory_url . 'assets/img/my-campaigns/icon-user.png'; ?>">
												</div>
												<div class="inner">
													<div class="amount">
														<?php echo $leaderboard->get_total_supporters(); ?>
													</div>
													<div class="sub">
														Supporters
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="section-content">
							<div class="uk-grid">
								<div class="uk-wid uk-width-1-1 uk-width-medium-3-5">
									<div class="container-fundraiser load-more-table-container">
										<table class="report-table table-fundraiser">
								        	<thead>
												<tr>
													<td class="thead-qty">AMOUNT</td>
													<td>CAMPAIGN NAME</td>
												</tr>
											</thead>
								            <tbody>
								            <?php 
								            $max_table_display = 5;
								            $show_more = false;

								            $i = 0;
								            foreach ( $leaderboard->get_campaign_ids() as $campaign_id ) : 

								                $tr_classes = 'fundraiser-'.$i;
								                if($i >= $max_table_display ){
								                    $tr_classes .= ' more';

								                    $show_more = true;
								                    // close tbody to separate
								                    echo '</tbody><tbody class="more-tbody" style="display:none;">';
								                }

								                ?>
								                <tr class="<?php echo $tr_classes; ?>">
								                	<?php $donation_amount = charitable_get_table( 'campaign_donations' )->get_campaign_donated_amount( $campaign_id ); ?>
								                    <td class="amount"><?php echo charitable_get_currency_helper()->get_monetary_amount( $donation_amount, 0 ); ?></td>
								                    <td class="link"><a href="<?php echo get_permalink( $campaign_id ); ?>"><?php echo get_the_title( $campaign_id ); ?></a></td>
								                </tr>

								            <?php
								            $i++;
								            endforeach;
								            ?>
								            </tbody>
								            <tfoot>
								        		<?php if($show_more): ?>
												<tr>
													<td class="load-more" colspan="2">
														<a href="javascript:;" class="load-more-button"><?php _e('See All', 'pp-toolkit'); ?></a>
													</td>
												</tr>
								        		<?php endif; ?>
								            </tfoot>
								        </table>
									</div>
								</div>
								<div class="uk-width-1-1 uk-width-medium-2-5">
									<div class="download-report-button">
										<a href="#">
										<div class="inner icon"><img alt="icon" src="<?php echo pp_toolkit()->directory_url . 'assets/img/my-campaigns/download-report.png'; ?>"></div>
										<div class="inner">
											<div>
												Download list of<br>
												campaigns &amp; amounts
											</div>
										</div></a>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div><!-- .layout-wrapper -->
			<?php 
			endif; // password required
		endwhile;
	endif;

get_footer('dashboard');