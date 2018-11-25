<?php
/**
 * Display a widget with top fundraisers
 *
 * @since   1.0.0
 */

$widget_title   = apply_filters( 'widget_title', $view_args['title'] );
$fundraisers         = $view_args[ 'fundraisers' ];
$max_display = $view_args[ 'number' ];

$campaign_id = $view_args['campaign_id'];

if ( 'all' == $view_args['campaign_id'] ) {
    $campaign_id = false;
}

if ( 'current' == $view_args['campaign_id'] ) {
    $campaign_id = get_the_ID();
}

/**
 * No need to display on child campaign
 */
$parent_campaign_id = wp_get_post_parent_id( $campaign_id );
if(!empty($parent_campaign_id))
    return;

/* If there are no fundraisers and the widget is configured to hide when empty, return now. */
if ( ! $fundraisers->count() && $view_args['hide_if_no_fundraisers'] ) {
    return;
}

$team_fundraising = get_post_meta( $campaign_id, '_campaign_team_fundraising', true) === 'on';
$enable_widget = get_post_meta( $campaign_id, 'display_top_fundraiser_widget', true) === 'on';
if(!$enable_widget && !$team_fundraising){
    return;
}

echo $view_args['before_widget'];

if ( ! empty( $widget_title ) ) :
    echo $view_args['before_title'] . $widget_title . $view_args['after_title'];
endif;

if ( $fundraisers->count() ) :
    ?>
    
    <div class="container-donor load-more-table-container">
        <table class="table-donor">
            <tbody>
            <?php 
            $show_more = false;

            $i = 0;
			// echo "<pre>";
			// print_r($fundraisers);
			// echo "</pre>";
            foreach ( $fundraisers as $fundraiser ) : 

                // echo "<pre>";
                // print_r($fundraiser);
                // echo "</pre>";

                $tr_classes = 'donor-'.$i;
                if($i >= $max_display ){
                    $tr_classes .= ' more';

                    $show_more = true;
                    // close tbody to separate
                    echo '</tbody><tbody class="more-tbody" style="display:none;">';
                }
				
			$parent_campaign_id = wp_get_post_parent_id( $fundraiser->campaign_id );
			$team_fundraising = get_post_meta( $parent_campaign_id, '_campaign_team_fundraising', true) === 'on';
			if($team_fundraising){
				$referral_name = get_the_author_meta('display_name', get_post_field( 'post_author', $fundraiser->campaign_id ));
				$referral_link = get_permalink($fundraiser->campaign_id);
				
			}else{
				// $referral_name = (isset($payment_meta['referral']) && !empty($payment_meta['referral'])) ?  pp_get_referrer_name($payment_meta['referral'], false) : 'na';
				// echo $fundraiser->campaign_id;
				// $referral_name = 'test';
				$referral_name = get_the_author_meta('display_name', get_post_field( 'post_author', $fundraiser->campaign_id ));
				$referral_link = get_permalink($fundraiser->campaign_id);
			}
			
                ?>
                <tr class="<?php echo $tr_classes; ?>">
                    <td class="donor-amount"><?php echo charitable_get_currency_helper()->get_monetary_amount( $fundraiser->amount ); ?></td>
					<td class="donor-name"><?php echo pp_get_referrer_name($fundraiser->referral); ?></td>
                    <!--<td class="donor-name"><a href="<?php echo $referral_link; ?>"><?php echo $referral_name;?></a></td>-->
                </tr>

            <?php
            $i++;
            endforeach;
            ?>
            </tbody>
        </table>

        <?php if($show_more): ?>
        <div class="load-more">
            <a href="javascript:;" class="load-more-button"><?php _e('See All', 'pp-toolkit'); ?></a>
        </div>
        <?php endif; ?>
    </div>

<?php
else : 

    ?>

    <p><?php _e( 'No top fundraiser yet.', 'pp-toolkit' ) ?></p>

    <?php

endif;

echo $view_args['after_widget'];