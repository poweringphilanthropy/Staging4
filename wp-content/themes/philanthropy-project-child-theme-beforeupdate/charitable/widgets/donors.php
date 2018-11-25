<?php
/**
 * Display a widget with donors, either for a specific campaign or sitewide.
 *
 * @author  Studio 164a
 * @since   1.0.0
 */

$widget_title   = apply_filters( 'widget_title', $view_args['title'] );
$donors         = $view_args[ 'donors' ];

$campaign_id = $view_args['campaign_id'];

if ( 'all' == $view_args['campaign_id'] ) {
    $campaign_id = false;
}

if ( 'current' == $view_args['campaign_id'] ) {
    $campaign_id = get_the_ID();
}

/* If there are no donors and the widget is configured to hide when empty, return now. */
if ( ! $donors->count() && $view_args['hide_if_no_donors'] ) {
    return;
}

echo $view_args['before_widget'];

if ( ! empty( $widget_title ) ) :
    echo $view_args['before_title'] . $widget_title . $view_args['after_title'];
endif;

if ( $donors->count() ) :
    ?>
    
    <div class="container-donor">
        <table class="table-donor">
            <tbody>
            <?php 
            $show_more = false;

            $max_display = 10;
            $i = 0;
            foreach ( $donors as $donor ) : 

                $payment_id = Charitable_EDD_Payment::get_payment_for_donation( $donor->donation_id );
                $user_info = edd_get_payment_meta_user_info( $payment_id );

                $name = sprintf( '%s %s', 
                    isset( $user_info[ 'first_name' ] ) ? $user_info[ 'first_name' ] : '', 
                    isset( $user_info[ 'last_name' ] ) ? $user_info[ 'last_name' ] : '' 
                );

                if ( empty( trim( $name ) ) ) {
                    $name = $donor->get_name();
                }

                $tr_classes = 'donor-'.$i;
                if($i >= $max_display ){
                    $tr_classes .= ' more';

                $show_more = true;
                // close tbody to separate
                echo '</tbody><tbody class="more-donors" style="display:none;">';
                }

                ?>

                <tr class="<?php echo $tr_classes; ?>">
                    <td class="donor-amount"><?php echo charitable_get_currency_helper()->get_monetary_amount( $donor->get_amount($campaign_id) ); ?></td>
                    <td class="donor-name"><?php echo $name ?></td>
                </tr>

            <?php
            $i++;
            endforeach;
            ?>
            </tbody>
        </table>

        <?php if($show_more): ?>
        <div class="load-more">
            <a href="javascript:;" class="load-more-button"><?php _e('See All', 'philanthropy'); ?></a>
            <script>
            (function($){
                $(document).on('click', 'a.load-more-button', function(e){
                    $('.table-donor .more-donors').slideDown(1000000); 
                    $(this).hide(); 
                    return false;
                    
                });
            })(jQuery);
            </script>
        </div>
        <?php endif; ?>
    </div>

<?php
else : 

    ?>

    <p><?php _e( 'No donors yet. Be the first!', 'charitable' ) ?></p>

    <?php

endif;

echo $view_args['after_widget'];