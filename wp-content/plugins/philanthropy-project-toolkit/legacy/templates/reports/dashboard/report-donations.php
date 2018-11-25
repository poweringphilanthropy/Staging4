<?php  
/**
 * Section report for donations
 */
$report = $view_args['report'];
$leaderboard = $view_args['leaderboard'];
$donors = $report->get_donors();

$currency_helper = charitable_get_currency_helper();

$total_donation = array_sum( wp_list_pluck( $donors, 'amount' ) );

// echo "<pre>";
// print_r($donors);
// echo "</pre>";

$max_table_display = 5;
?>

<div class="report-section">
	<div class="section-title">
		<div class="uk-grid">
			<div class="uk-width-1-1 uk-width-medium-1-3">
				<div class="report-title">DONATIONS</div>
			</div>
			<div class="uk-width-1-1 uk-width-medium-2-3">
				<div class="uk-grid">
					<div class="uk-width-1-1 uk-width-medium-1-3">
						<div class="block-amount">
						  	<div class="inner icon">
								<img src="<?php echo pp_toolkit()->directory_url . 'assets/img/my-campaigns/icon-dollar.png'; ?>" alt="icon">
						  	</div>
						  	<div class="inner">
						  		<?php // echo $campaign->get_donated_amount(); ?>
								<div class="amount"><?php echo number_format($total_donation); ?></div>
								<div class="sub">Total</div>
						  	</div>
						</div>
					</div>
					<div class="uk-width-1-1 uk-width-medium-1-3">
						<div class="block-amount">
						  	<div class="inner icon">
								<img src="<?php echo pp_toolkit()->directory_url . 'assets/img/my-campaigns/icon-user.png'; ?>" alt="icon">
						  	</div>
						  	<div class="inner">
								<div class="amount"><?php echo $report->get_total_donors() ?></div>
								<div class="sub"><?php echo $report->get_total_donors() > 1 ? 'Donors' : 'Donor' ; ?></div>
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
				<div class="container-donor load-more-table-container">
			        <table class="report-table table-donor">
			        	<thead>
							<tr>
								<td class="thead-qty">AMOUNT</td>
								<td>FUNDRAISER NAME</td>
							</tr>
						</thead>
			            <tbody>
			            <?php 
			            $show_more = false;

			            $i = 0;
			            foreach ( $donors as $donor ) : 

			                $tr_classes = 'donor-'.$i;
			                if($i >= $max_table_display ){
			                    $tr_classes .= ' more';

			                    $show_more = true;
			                    // close tbody to separate
			                    echo '</tbody><tbody class="more-tbody" style="display:none;">';
			                }

			                ?>
			                <tr class="<?php echo $tr_classes; ?>">
			                    <td class="amount"><?php echo charitable_get_currency_helper()->get_monetary_amount( $donor->amount, 0 ); ?></td>
			                    <td class=""><?php echo implode(' ', array($donor->first_name, $donor->last_name) ); ?></td>
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
				  	<a href="<?php echo $report_url; ?>">
						<div class="inner icon">
							<img src="<?php echo pp_toolkit()->directory_url . 'assets/img/my-campaigns/download-report.png'; ?>" alt="icon">
					  	</div>
					  	<div class="inner">
					  		<div>
								Download list of<br>donors & amounts
					  		</div>
					  	</div>
				  	</a>
				</div>
			</div>
		</div>
	</div>
</div>
