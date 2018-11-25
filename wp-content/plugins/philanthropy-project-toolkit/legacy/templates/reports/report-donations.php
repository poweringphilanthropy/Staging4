<?php  
/**
 * Section report for donations
 */
$campaign = $view_args['campaign'];
$total_donations = $view_args['total_donations'];
$donation_details = $view_args['donation_details'];
$max_table_display = $view_args['max_table_display'];
$report_url = $view_args['report_url'];

// echo "<pre>";
// print_r($donation_details);
// echo "</pre>";

if(empty($donation_details))
	return;
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
								<div class="amount"><?php echo number_format($total_donations); ?></div>
								<div class="sub">Total</div>
						  	</div>
						</div>
					</div>
					<div class="uk-width-1-1 uk-width-medium-2-3">
						<div class="block-amount">
						  	<div class="inner icon">
								<img src="<?php echo pp_toolkit()->directory_url . 'assets/img/my-campaigns/icon-user.png'; ?>" alt="icon">
						  	</div>
						  	<div class="inner">
						  		<div class="amount"><?php echo count($donation_details); ?></div>
								<div class="sub"><?php echo (count($donation_details) > 1) ? 'Donors' : 'Donor' ; ?></div>
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
								<td>DONOR NAME</td>
							</tr>
						</thead>
			            <tbody>
			            <?php 

			            $show_more = false;

			            $i = 0;
			            foreach ( $donation_details as $donation ) : 

			            	$name = array();
			            	if(isset($donation['first_name']) && !empty($donation['first_name']))
			            		$name[] = $donation['first_name'];

			            	if(isset($donation['last_name']) && !empty($donation['last_name']))
			            		$name[] = $donation['last_name'];

			                $tr_classes = 'donor-'.$i;
			                if($i >= $max_table_display ){
			                    $tr_classes .= ' more';

			                    $show_more = true;
			                    // close tbody to separate
			                    echo '</tbody><tbody class="more-tbody" style="display:none;">';
			                }

			                ?>
			                <tr class="<?php echo $tr_classes; ?>">
			                    <td class="amount"><?php echo charitable_get_currency_helper()->get_monetary_amount( $donation['amount'], 0 ); ?></td>
			                    <td class=""><?php echo implode(' ', $name); ?></td>
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
					  		<div>Download list of<br>donors & amounts</div>
					  	</div>
				  	</a>
				</div>
			</div>
		</div>
	</div>
</div>
