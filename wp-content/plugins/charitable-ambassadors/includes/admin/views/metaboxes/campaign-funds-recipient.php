<?php
/**
 * Renders the Funds Recipient metabox.
 *
 * @since       1.0.0
 * @author      Eric Daams
 * @copyright   Copyright (c) 2017, Studio 164a
 */

global $post;

$campaign               = new Charitable_Campaign( $post );
$recipient_type         = $campaign->get( 'recipient' );
$recipient_type_details = charitable_get_recipient_type( $recipient_type );
$default_funding_data   = array(
	'recipient_type' => array(
		'label' => __( 'Type of Campaign', 'charitable-ambassadors' ),
		'value' => $recipient_type_details['admin_label'],
	),
);
$funding_data           = apply_filters( 'charitable_ambassadors_campaign_funds_recipient_data', $default_funding_data, $campaign, $recipient_type );
?>
<div class="charitable-metabox">
	<table class="widefat">
		<tbody>
			<?php foreach ( $funding_data as $data ) : ?>
			<tr>
				<th><?php echo $data['label'] ?></th>
				<td><?php echo $data['value'] ?></td>
			</tr>
			<?php endforeach ?>
		</tbody>
	</table>
</div>
