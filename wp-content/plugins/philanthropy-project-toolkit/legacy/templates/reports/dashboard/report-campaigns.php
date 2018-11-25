<?php  
/**
 * Section report for donations
 */
$report = $view_args['report'];
$leaderboard = $view_args['leaderboard'];
$campaigns = $report->get_campaigns();

$currency_helper = charitable_get_currency_helper();

$max_table_display = 5;
?>

<div class="report-section">
	<div class="section-title">
		<div class="uk-grid">
			<div class="uk-width-1-1 uk-width-medium-1-3">
				<div class="report-title">CAMPAIGNS</div>
			</div>
			<div class="uk-width-1-1 uk-width-medium-2-3">
				<div class="uk-grid">
					<div class="uk-width-1-1 uk-width-medium-1-3">
						<div class="block-amount">
						  	<div class="inner icon">
								<img src="<?php echo pp_toolkit()->directory_url . 'assets/img/my-campaigns/icon-dollar.png'; ?>" alt="icon">
						  	</div>
						  	<div class="inner">
								<div class="amount"><?php echo number_format( $report->get_total_donation_amount() ); ?></div>
								<div class="sub">Total</div>
						  	</div>
						</div>
					</div>
					<div class="uk-width-1-1 uk-width-medium-1-3">
						<div class="block-amount">
						  	<div class="inner icon">
								<img src="<?php echo pp_toolkit()->directory_url . 'assets/img/my-campaigns/icon-campaign.png'; ?>" alt="icon">
						  	</div>
						  	<div class="inner">
						  		<div class="amount"><?php echo count($campaigns); ?></div>
								<div class="sub"><?php echo (count($campaigns) > 1) ? 'Campaigns' : 'Campaign' ; ?></div>
						  	</div>
						</div>
					</div>
					<div class="uk-width-1-1 uk-width-medium-1-3">
						<div class="block-amount">
						  	<div class="inner icon">
								<img src="<?php echo pp_toolkit()->directory_url . 'assets/img/my-campaigns/icon-user.png'; ?>" alt="icon">
						  	</div>
						  	<div class="inner">
						  		<div class="amount"><?php echo $report->get_total_donors(); ?></div>
								<div class="sub"><?php echo ( $report->get_total_donors() > 1) ? 'Supporters' : 'Supporter' ; ?></div>
						  	</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="section-content">
		<div class="uk-grid">
			<div class="uk-width-1-1 uk-width-medium-3-5">
				<div class="container-donation load-more-table-container">
			        <table class="report-table table-donation">
			        	<thead>
							<tr>
								<td class="thead-qty">AMOUNT</td>
								<td>CAMPAIGN NAME</td>
							</tr>
						</thead>
			            <tbody>
			            <?php

			            // echo "<pre>";
			            // print_r($report->get_campaigns());
			            // echo "</pre>";

			            $show_more = false;

			            $i = 0;
			            foreach ( $report->get_campaigns() as $campaign ) : 

			                $tr_classes = 'donor-'.$i;
			                if($i >= $max_table_display ){
			                    $tr_classes .= ' more';

			                    $show_more = true;
			                    // close tbody to separate
			                    echo '</tbody><tbody class="more-tbody" style="display:none;">';
			                }

			                ?>
			                <tr class="<?php echo $tr_classes; ?>">
			                    <td class="amount"><?php echo charitable_get_currency_helper()->get_monetary_amount( $campaign->total_donation, 0 ); ?></td>
			                    <td class="link"><a href="<?php echo get_permalink( $campaign->ID ); ?>"><?php echo $campaign->post_title; ?></a></td>
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
						<div class="inner icon">
							<img src="<?php echo pp_toolkit()->directory_url . 'assets/img/my-campaigns/download-report.png'; ?>" alt="icon">
					  	</div>
					  	<div class="inner">
					  		<div>Download list of<br>campaigns & amounts</div>
					  	</div>
				  	</a>
				</div>
			</div>
		</div>
	</div>
</div>
