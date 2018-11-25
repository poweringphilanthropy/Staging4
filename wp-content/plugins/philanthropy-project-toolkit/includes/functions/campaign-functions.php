<?php

function pp_is_campaign_tax_deductible($campaign_id){

	$payout_option = get_post_meta( $campaign_id, '_campaign_payout_options', true );

	return $payout_option == 'direct';
}