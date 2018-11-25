<?php 
global $post;

$payout_destination    = get_post_meta( $post->ID, '_campaign_payout_destination', true );
$connected_stripe_id = get_post_meta( $post->ID, '_campaign_connected_stripe_id', true );
$platform_fee = get_post_meta( $post->ID, '_campaign_platform_fee', true );
if(empty($platform_fee)){
	$platform_fee = edd_get_option('platform_fee');
}

wp_enqueue_script( 'pp-edd-admin' ); 
?>

<div class="charitable-metabox">
	<table class="widefat">
		<tbody class="wrapper-hide-show">
			<tr>
				<th><?php _e('Payout Destination', 'edds'); ?></th>
				<td>
					<select name="_campaign_payout_destination" class="hide-show-trigger">
						<option value="creator" <?php selected($payout_destination, 'creator'); ?>><?php _e('Direct to Campaign Creator', 'edds'); ?></option>
						<option value="non_profit" <?php selected($payout_destination, 'non_profit'); ?>><?php _e('Direct to Non-Profit', 'edds'); ?></option>
					</select>
				</td>
			</tr>
			<tr class="hide-show show-if-non_profit">
				<th><?php _e('Non-Profit Organization', 'edds'); ?></th>
				<td>
					<select name="_campaign_connected_stripe_id" id="">
						<option value=""><?php _e('Select Non Profit Organization', 'edds'); ?></option>
						<?php 
						if($orgs = pp_edds_get_connected_organizations()): 
						foreach ($orgs as $stripe_id => $name) : ?>
						<option value="<?php echo $stripe_id; ?>" <?php selected($connected_stripe_id, $stripe_id); ?>><?php echo $name; ?></option>
						<?php 
						endforeach;
						endif;
						?>
					</select>
				</td>
			</tr>
			<tr>
				<th><?php _e('Platform Fee', 'edds'); ?></th>
				<td>
					<input name="_campaign_platform_fee" type="number" min="0" max="100" value="<?php echo $platform_fee; ?>">
				</td>
			</tr>
		</tbody>
	</table>
</div>