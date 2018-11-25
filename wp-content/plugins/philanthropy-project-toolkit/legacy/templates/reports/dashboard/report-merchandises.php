<?php  
/**
 * Section report for merchandises
 */
$report = $view_args['report'];
$leaderboard = $view_args['leaderboard'];
$merchandises_details = $report->get_merchandises();

$currency_helper = charitable_get_currency_helper();

$total_donation = array_sum( wp_list_pluck( $merchandises_details, 'amount' ) );

// echo "<pre>";
// print_r($merchandises_details);
// echo "</pre>";

$max_table_display = 5;
?>
<div class="report-section">
	<div class="section-title">
		<div class="uk-grid">
			<div class="uk-width-1-1 uk-width-medium-1-3">
				<div class="report-title">MERCHANDISES</div>
			</div>
			<div class="uk-width-1-1 uk-width-medium-2-3">
				<div class="uk-grid">
					<div class="uk-width-1-1 uk-width-medium-1-3">
						<div class="block-amount">
						  	<div class="inner icon">
								<img src="<?php echo pp_toolkit()->directory_url . 'assets/img/my-campaigns/icon-dollar.png'; ?>" alt="icon">
						  	</div>
						  	<div class="inner">
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
								<div class="amount"><?php echo count($merchandises_details); ?></div>
								<div class="sub"><?php echo (count($merchandises_details) > 1) ? 'Supporters' : 'Supporter' ; ?></div>
						  	</div>
						</div>
					</div>
					<div class="uk-width-1-1 uk-width-medium-1-3">
						<div class="block-amount">
						  	<div class="inner icon">
								<img src="<?php echo pp_toolkit()->directory_url . 'assets/img/my-campaigns/icon-merchandise.png'; ?>" alt="icon">
						  	</div>
						  	<div class="inner">
						  		<?php $sum = array_sum(wp_list_pluck( $merchandises_details, 'quantity' )); ?>
								<div class="amount"><?php echo $sum; ?></div>
								<div class="sub"><?php echo ($sum > 1) ? 'Tickets' : 'Ticket'; ?></div>
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
				<div class="container-merchandises load-more-table-container">
			        <table class="report-table table-merchandises">
			        	<thead>
							<tr>
								<td class="thead-qty">QTY</td>
								<td>DESCRIPTION</td>
							</tr>
						</thead>
			            <tbody>
			            <?php 

			            $show_more = false;

			            $i = 0;
			            foreach ( $merchandises_details as $merchandise ) : 

			                $tr_classes = 'merchandise-'.$i;
			                if($i >= $max_table_display ){
			                    $tr_classes .= ' more';

			                    $show_more = true;
			                    // close tbody to separate
			                    echo '</tbody><tbody class="more-tbody" style="display:none;">';
			                }

			                ?>
			                <tr class="<?php echo $tr_classes; ?>">
			                    <td class="qty"><?php echo $merchandise->quantity; ?></td>
			                    <td class=""><?php echo $merchandise->name; ?></td>
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
					  		<div>Download list of<br>merchandise holders</div>
					  	</div>
				  	</a>
				</div>
			</div>
		</div>
	</div>
</div>