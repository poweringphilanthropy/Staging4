<?php
/**
 * The template used to display the user's campaigns.
 *
 * @author  Studio 164a
 * @since   1.0.0
 * @version 1.0.0
 */

$campaign = $view_args[ 'campaign' ];
$user = $view_args[ 'user' ];
$currency_helper = charitable_get_currency_helper();
?>
<ul class="campaign-stats user-post-stats">
    <?php if ( $campaign->has_goal() ) : ?>
        <li class="campaign-raised summary-item">
            <?php printf( 
                _x( '%s Raised', 'percentage raised', 'charitable-fes' ), 
                '<span class="amount">' . $campaign->get_percent_donated() . '</span>' 
            ) ?>
        </li>
        <li class="campaign-figures summary-item">
            <?php printf(
                _x( '%s donated of %s goal', 'amount donated of goal', 'charitable-fes' ), 
                '<span class="amount">' . $currency_helper->get_monetary_amount( $campaign->get_donated_amount() ) . '</span>', 
                '<span class="goal-amount">' . $currency_helper->get_monetary_amount( $campaign->get_goal() ) . '</span>'
            ) ?>
        </li>
    <?php else : ?>
        <li class="campaign-figures summary-item">
            <?php printf(
                _x( '%s Donated', 'amount donated', 'charitable-fes' ), 
                '<span class="amount">' . $currency_helper->get_monetary_amount( $campaign->get_donated_amount() ) . '</span>'
            ) ?>
        </li>
    <?php endif ?>
    <li class="campaign-donors summary-item">
        <?php printf( 
            _x( '%s Donors', 'number of donors', 'charitable-fes' ), 
            '<span class="donors-count">' . $campaign->get_donor_count() . '</span>'
        ) ?>
    </li>
    <?php if ( ! $campaign->is_endless() ) : ?>
        <li class="campaign-time-left summary-item">
            <?php echo $campaign->get_time_left() ?>
        </li>
    <?php 
    endif;
    ?>
</ul><!-- .campaign-stats -->