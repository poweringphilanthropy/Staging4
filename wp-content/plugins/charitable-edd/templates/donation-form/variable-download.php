<?php
/**
 * Display a download that the donor can purchase which will
 * make a donation to the campaign.
 *
 * @author  Studio 164a
 * @since   1.0.0
 */

$download = $view_args[ 'download' ];

$prices = apply_filters( 'edd_purchase_variable_prices', edd_get_variable_prices( $download->ID ), $download->ID );
$type = edd_single_price_option_mode( $download->ID ) ? 'checkbox' : 'radio';
$mode = edd_single_price_option_mode( $download->ID ) ? 'multi' : 'single';
$schema = edd_add_schema_microdata() ? ' itemprop="offers" itemscope itemtype="http://schema.org/Offer"' : '';
?>
<div class="charitable-edd-connected-download variable-download">
    <?php if ( apply_filters( 'charitable_edd_donation_form_show_thumbnail', true ) ) : ?>

        <div class="charitable-edd-download-media">
            <?php echo get_the_post_thumbnail( $download->ID, array( 150, 150 ) ) ?>
        </div><!--.download-media-->
            
    <?php
    endif;
    ?>

    <div class="charitable-edd-download-details">
        <?php 
        if ( apply_filters( 'charitable_edd_donation_form_show_title', true ) ) : ?>

            <h5 class="download-title"><?php echo get_the_title( $download->ID ) ?></h5>

        <?php 
        endif;

        if ( apply_filters( 'charitable_edd_donation_form_show_excerpt', false ) ) :
            
            echo apply_filters( 'the_excerpt', get_post_field( 'post_content', $download->ID ) );

        endif;

        ?>
        <?php 

        do_action( 'edd_before_price_options', $download->ID ); 

        ?>
        <div class="charitable-edd-price-options edd_price_options edd_<?php echo esc_attr( $mode ); ?>_mode">
            <ul>
                <?php
                if ( $prices ) :

                    foreach ( $prices as $key => $price ) :
                        
                        ?>
                        <li id="edd_price_option_<?php echo $download->ID ?>_<?php echo sanitize_key( $price['name'] ) ?>" class="edd-price-option" <?php echo $schema ?>>
                            <label for="<?php esc_attr_e( 'edd_price_option_' . $download->ID . '_' . $key ) ?>">
                                <input type="<?php echo $type ?>"
                                    name="downloads[<?php echo $download->ID ?>][price_id][]" 
                                    id="<?php esc_attr_e( 'edd_price_option_' . $download->ID . '_' . $key ) ?>" 
                                    class="charitable-edd-download-select <?php esc_attr_e( 'edd_price_option_' . $download->ID ) ?>" 
                                    value="<?php esc_attr_e( $key ) ?>" 
                                    data-price="<?php echo edd_get_price_option_amount( $download->ID, $key ) ?>"
                                    <?php checked( edd_item_in_cart( $download->ID, array( 'price_id' => $key ) ) ) ?>
                                />
                                <span class="edd-price-option-price" itemprop="price">
                                    <?php echo edd_currency_filter( edd_format_amount( $price['amount'] ) ) ?>
                                    <span class="currency"><?php echo edd_get_currency() ?></span>
                                </span>
                                <span class="edd-price-option-name" itemprop="description">(<?php echo esc_html( $price['name'] ) ?>)</span>
                            </label>
                            <?php 
                            do_action( 'edd_after_price_option', $key, $price, $download->ID );
                            ?>
                        </li>
                        <?php 

                    endforeach;
                endif;
                do_action( 'edd_after_price_options_list', $download->ID, $prices, $type );
                ?>
            </ul>
        </div><!--.edd_price_options-->
        <?php

        do_action( 'edd_after_price_options', $download->ID );

        ?>
    </div><!--.download-details-->
    <?php 
    if ( apply_filters( 'charitable_edd_donation_form_show_contribution_note', true ) ) : ?>
    
        <p class="charitable-edd-contribution-note">
            <?php foreach ( $download->benefactors as $benefactor ) : ?>
                <?php echo new Charitable_EDD_Benefactor( $benefactor ) ?>
            <?php endforeach ?>
        </p><!--.charitable-edd-contribution-note-->

    <?php endif ?>
    
</div><!--.charitable-edd-connected-download-->
