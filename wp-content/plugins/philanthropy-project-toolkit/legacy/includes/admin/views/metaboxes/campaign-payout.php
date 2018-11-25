<?php
global $post;


$payout_option = get_post_meta( $post->ID, '_campaign_payout_options', true );
$connected_stripe_id = get_post_meta( $post->ID, '_campaign_connected_stripe_id', true );
$platform_fee = get_post_meta( $post->ID, '_campaign_platform_fee', true );
if(empty($platform_fee)){
	$platform_fee = edd_get_option('platform_fee');
}

$payable_name = get_post_meta( $post->ID, '_campaign_payout_payable_name', true );
$organization_name = get_post_meta( $post->ID, '_campaign_payout_organization_name', true );
$email = get_post_meta( $post->ID, '_campaign_payout_email', true );
$phone = get_post_meta( $post->ID, '_campaign_payout_venmo_phone', true );
$first_name = get_post_meta( $post->ID, '_campaign_payout_first_name', true );
$last_name = get_post_meta( $post->ID, '_campaign_payout_last_name', true );
$address = get_post_meta( $post->ID, '_campaign_payout_address', true );
$address2 = get_post_meta( $post->ID, '_campaign_payout_address2', true );
$city = get_post_meta( $post->ID, '_campaign_payout_city', true );
$state = get_post_meta( $post->ID, '_campaign_payout_state', true );
$zipcode = get_post_meta( $post->ID, '_campaign_payout_zipcode', true );
$country = get_post_meta( $post->ID, '_campaign_payout_country', true );
?>
<div class="charitable-metabox wrapper-hide-show">

	<table class="widefat">
		<tbody>
			<tr>
				<th><?php _e('Payout Option', 'pp-toolkit'); ?></th>
				<td>
					<select name="_campaign_payout_options" class="hide-show-trigger">
						<option value="check" <?php selected($payout_option, 'check'); ?>><?php _e('Check', 'pp-toolkit'); ?></option>
						<option value="venmo" <?php selected($payout_option, 'venmo'); ?>><?php _e('Venmo', 'pp-toolkit'); ?></option>
						<option value="direct" <?php selected($payout_option, 'direct'); ?>><?php _e('Direct to Non Profit', 'pp-toolkit'); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th><?php _e('Platform Fee', 'pp-toolkit'); ?></th>
				<td>
					<input name="_campaign_platform_fee" type="number" min="0" max="100" value="<?php echo $platform_fee; ?>">
				</td>
			</tr>
		</tbody>
	</table>
	<hr>

	<table class="widefat">
		<tbody>
			<tr class="hide-show show-if-direct">
				<th><?php _e('Non-Profit Organization', 'pp-toolkit'); ?></th>
				<td>
					<select name="_campaign_connected_stripe_id" id="select-non-profit" style="width: 100%;">
						<option value=""><?php _e('Select Non Profit Organization', 'pp-toolkit'); ?></option>
						<?php 
						if($orgs = pp_get_connected_organization_options()): 
						foreach ($orgs as $stripe_id => $name) : ?>
						<option value="<?php echo $stripe_id; ?>" <?php selected($connected_stripe_id, $stripe_id); ?>><?php echo $name; ?></option>
						<?php 
						endforeach;
						endif;
						?>
					</select>
				</td>
			</tr>
			<tr class="hide-show show-if-direct">
				<th><?php _e('Unregistered Organization Name:', 'pp-toolkit'); ?></th>
				<td><input type="text" id="payout_organization_name" name="_campaign_payout_organization_name" value="<?php echo $organization_name; ?>" ></td>
			</tr>
			<tr class="hide-show show-if-check show-if-venmo">
				<th><?php _e('Payable to:', 'pp-toolkit'); ?></th>
				<td><input type="text" id="payout_payable_name" name="_campaign_payout_payable_name" value="<?php echo $payable_name; ?>" ></td>
			</tr>
			<tr class="hide-show show-if-check show-if-venmo">
				<th><?php _e('First Name:', 'pp-toolkit'); ?></th>
				<td><input type="text" id="payout_first_name" name="_campaign_payout_first_name" value="<?php echo $first_name; ?>" ></td>
			</tr>
			<tr class="hide-show show-if-check show-if-venmo">
				<th><?php _e('Last Name:', 'pp-toolkit'); ?></th>
				<td><input type="text" id="payout_last_name" name="_campaign_payout_last_name" value="<?php echo $last_name; ?>" ></td>
			</tr>
			<tr class="hide-show show-if-venmo">
				<th><?php _e('Email:', 'pp-toolkit'); ?></th>
				<td><input type="text" id="payout_email" name="_campaign_payout_email" value="<?php echo $email; ?>" ></td>
			</tr>
			<tr class="hide-show show-if-venmo">
				<th><?php _e('Phone:', 'pp-toolkit'); ?></th>
				<td><input type="text" id="payout_venmo_phone" name="_campaign_payout_venmo_phone" value="<?php echo $phone; ?>" ></td>
			</tr>
			<tr class="hide-show show-if-check show-if-direct">
				<th><?php _e('Address:', 'pp-toolkit'); ?></th>
				<td><input type="text" id="payout_address" name="_campaign_payout_address" value="<?php echo $address; ?>" ></td>
			</tr>
			<tr class="hide-show ">
				<th><?php _e('Address2:', 'pp-toolkit'); ?></th>
				<td><input type="text" id="payout_address2" name="_campaign_payout_address2" value="<?php echo $address2; ?>" ></td>
			</tr>
			<tr class="hide-show show-if-check show-if-direct">
				<th><?php _e('City:', 'pp-toolkit'); ?></th>
				<td><input type="text" id="payout_city" name="_campaign_payout_city" value="<?php echo $city; ?>" ></td>
			</tr>
			<tr class="hide-show show-if-check show-if-direct">
				<th><?php _e('State:', 'pp-toolkit'); ?></th>
				<td><input type="text" id="payout_state" name="_campaign_payout_state" value="<?php echo $state; ?>" ></td>
			</tr>
			<tr class="hide-show show-if-check show-if-direct">
				<th><?php _e('Zip Code:', 'pp-toolkit'); ?></th>
				<td><input type="text" id="payout_zipcode" name="_campaign_payout_zipcode" value="<?php echo $zipcode; ?>" ></td>
			</tr>
			<tr class="hide-show ">
				<th><?php _e('Country:', 'pp-toolkit'); ?></th>
				<td><input type="text" id="payout_country" name="_campaign_payout_country" value="<?php echo $country; ?>" ></td>
			</tr>
		</tbody>
	</table>
</div>