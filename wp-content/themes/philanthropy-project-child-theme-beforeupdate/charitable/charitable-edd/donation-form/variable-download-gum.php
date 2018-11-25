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
        
                        
//GUM EDIT
if ( $prices ) :
foreach ( $prices as $key => $price ) :
?>
<label for="edd_download_quantity">Qty</label>
<input style="width:6em;"
type="number" min="0" step="1" 
name="downloads[<?php echo $download->ID ?>][price_id][]"
id="<?php esc_attr_e( 'edd_price_option_' . $download->ID . '_' . $key ) ?>"
class="edd-input edd-item-quantity <?php esc_attr_e( 'edd_price_option_' . $download->ID ) ?>" 
value=""
data-price="<?php echo edd_get_price_option_amount( $download->ID, $key ) ?>" 
data-price-mode="single" 
/>
<!--
data-action="edd_add_to_cart" 
data-download-id="<?php esc_attr_e( 'edd_price_option_' . $download->ID . '_' . $key ) ?>" 
data-variable-price="yes" 
data-price-mode="single" 
-->
<?php
echo '<br>';
echo get_post_field('post_content', $download->ID);

endforeach;
endif;


//echo do_shortcode('[purchase_link id="' . esc_attr( $download->ID ) . '" text="Add to Cart" style="button" color="blue"]');
//echo '<br>';
//echo get_post_field('post_content', $download->ID);
		
echo '</hr>';



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
