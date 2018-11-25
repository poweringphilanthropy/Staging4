<?php  

$user_id = $view_args['user_id'];
$service_hours = $view_args['service_hours'];
$download_url = $view_args['download_url'];
$max_table_display = 5;

// echo "<pre>";
// print_r($service_hours);
// echo "</pre>";
?>

<div class="charitable-user-campaigns pp-campaign-report">
	<div class="report-section">
		<div class="section-title">
			<div class="uk-grid">
				<div class="uk-width-1-1 uk-width-medium-1-3">
					<div class="report-title">
						SERVICE HOURS
					</div>
				</div>
				<div class="uk-width-1-1 uk-width-medium-2-3">
					<div class="uk-grid">
						<div class="uk-width-1-1 uk-width-medium-1-3">
							<div class="block-amount">
								<div class="inner icon"><img alt="icon" src="<?php echo PP()->get_image_url('icons/icon-clock.png'); ?>"></div>
								<div class="inner">
									<div class="amount">
										<?php echo $service_hours['amount']; ?>
									</div>
									<div class="sub">
										Hours
									</div>
								</div>
							</div>
						</div>
						<div class="uk-width-1-1 uk-width-medium-1-3">
							<div class="block-amount">
								<div class="inner icon"><img alt="icon" src="<?php echo PP()->get_image_url('icons/icon-user.png'); ?>"></div>
								<div class="inner">
									<div class="amount">
										<?php echo $service_hours['count']; ?>
									</div>
									<div class="sub">
										Members
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
									<td class="thead-qty"># OF HOURS</td>
									<td>MEMBER NAME</td>
								</tr>
							</thead>
							<tbody>
								<?php 

					            $show_more = false;

					            $i = 0;
					            foreach ( $service_hours['data'] as $service_hour ) : 

					                $tr_classes = 'service_hour-'.$i;
					                if($i >= $max_table_display ){
					                    $tr_classes .= ' more';

					                    $show_more = true;
					                    // close tbody to separate
					                    echo '</tbody><tbody class="more-tbody" style="display:none;">';
					                }

					                ?>
					                <tr class="<?php echo $tr_classes; ?>">
					                    <td class="qty"><?php echo $service_hour['amount']; ?></td>
					                    <td class=""><?php echo (isset($service_hour['member_name'])) ? $service_hour['member_name'] : ''; ?></td>
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
						<a href="#" class="log-service-hours_open text-default">
						<div class="inner icon"><img alt="icon" src="<?php echo PP()->get_image_url('icons/icon-log-hours-blue.png'); ?>"></div>
						<div class="inner">
							<div>
								Log Chapter<br>
								Service Hours
							</div>
						</div></a>
					</div>
					<div class="download-report-button">
						<a href="<?php echo $download_url; ?>">
						<div class="inner icon"><img alt="icon" src="<?php echo PP()->get_image_url('icons/icon-download-report.png'); ?>"></div>
						<div class="inner">
							<div>
								Download list of<br>
								Service Hours
							</div>
						</div></a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div id="log-service-hours" class="pp-popup" data-blur="true">

	<div class="p_popup-inner">
        <a class="p_popup-close log-service-hours_close" href="#">x</a>
    	<form id="save-user-service-hours" class="charitable-form" method="post" style="padding: 0px;">
	        <div class="p_popup-header">
				<h2 class="p_popup-title"><span>Log Service Hours</span></h2>
				<div class="p_popup-notices uk-width-1-1"></div>
	        </div>
	        <div class="p_popup-content">
		        <div class="hidden" style="display: none;">
					<input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
		        	<?php wp_nonce_field( 'save-user-service-hours' ); ?>
		        </div>
	        	<div class="charitable-form-fields">
					<div id="" class="charitable-form-field fullwidth">
						<select name="campaign_id" id="" placeholder="Select campaign"></select>
					</div>
	        	</div>
	        	<div class="charitable-form-fields">
		        	<div id="" class="charitable-form-field fullwidth">
						<input type="text" data-update="title" name="title" placeholder="Title">
					</div>
				</div>
	        	<div class="charitable-form-fields">
		        	<div id="" class="charitable-form-field fullwidth">
						<textarea data-update="description" name="description" id="" cols="" rows="4" placeholder="Description"></textarea>
					</div>
				</div>
	        	<div class="charitable-form-fields">
		        	
		        	<div id="" class="charitable-form-field odd">
						<input type="text" class="datepicker" name="service_date" placeholder="Date of Service Hours" value="">
					</div>
		        	
		        	<div id="" class="charitable-form-field even">
						<input type="number" class="" name="service_hours" placeholder="Number of Service Hours" value="">
					</div>
				</div>
	        	<div class="charitable-form-fields">
		        	<div id="" class="charitable-form-field fullwidth">
						<textarea name="members" id="" cols="" rows="4" placeholder="Add Member Names: First name, Last name, Email address (optional)"></textarea>
					</div>
				</div>
	        </div>
	        <div class="p_popup-footer">
				<div class="p_popup-submit-field">
					<button type="submit" class="button submit-service-hours"><?php _e('Submit', 'philanthropy'); ?></button>
				</div>
	        </div>
	    </form>
    </div>
</div>