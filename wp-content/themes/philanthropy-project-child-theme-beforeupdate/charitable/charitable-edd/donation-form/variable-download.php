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

//* GUM EDIT
//$mode = edd_single_price_option_mode( $download->ID ) ? 'multi' : 'single';
$mode = 'multi';
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


        do_action( 'edd_before_price_options', $download->ID ); 
		
		?>
        <!--<div class="charitable-edd-price-options edd_price_options edd_<?php echo esc_attr( $mode ); ?>_mode">
	        
            <ul>-->
                <?php
                if ( $prices ) :

//* GUM EDIT BEGIN
/*
echo '<p> Shortcode';
echo do_shortcode('[purchase_link id="' . esc_attr( $download->ID ) . '" text="Add to Cart" style="button" color="blue"]');
echo '</p>';
echo '<hr>';
*/
/*
	<input 
		type="checkbox" 
		checked="checked" 
		name="edd_options[price_id][]"
		id="edd_price_option_8977_0-2"
		class="edd_price_option_8977"
		value="0" 
		data-price="15.00">
		
		&nbsp;<span class="edd_price_option_name" itemprop="description">S</span>
		<span class="edd_price_option_sep">&nbsp;–&nbsp;</span>
		<span class="edd_price_option_price">$15.00</span>
		
		<meta itemprop="price" content="15.00">
		<meta itemprop="priceCurrency" content="USD"></label>
		
		<div class="edd_download_quantity_wrapper edd_download_quantity_price_option_s">
		<span class="edd_price_option_sep">&nbsp;x&nbsp;</span>
		<input type="number" min="1" step="1" name="edd_download_quantity_0" class="edd-input edd-item-quantity" value="1">
		</div>
*/			
                
?>               
<div class="edd_price_options edd_multi_mode">
	<ul>
		<?php 
		foreach ( $prices as $key => $price ) : 
		
		// get default option
		// $default = edd_get_default_variable_price( $download->ID );
		$value = 0; // if default option value will be 1 as default
		?>
		
		<li id="edd_price_option_<?php echo $download->ID ?>_<?php echo sanitize_key( $price['name'] ) ?> "itemprop="offers" itemscope="" itemtype="http://schema.org/Offer">
			<label for="<?php esc_attr_e( 'edd_price_option_' . $download->ID . '_' . $key ) ?>">
			<?php //do_action( 'edd_after_price_option', $key, $price, $download->ID ); ?>
			
			<input 
				type="checkbox"
				<?php //checked( edd_item_in_cart( $download->ID, array( 'price_id' => $key ) ) ) ?>
				name="edd_options[price_id][]"
				id="<?php esc_attr_e( 'edd_price_option_' . $download->ID . '_' . $key ) ?>" 
                class="hidden-checkbox edd_price_option_<?php echo $download->ID ?>"
                value="<?php esc_attr_e( $key ) ;?>"
                data-price="<?php echo edd_get_price_option_amount( $download->ID, $key ) ?>" >
                
				&nbsp;<span class="edd_price_option_name" itemprop="description"><?php echo esc_html( $price['name'] ) ?></span>
				<span class="edd_price_option_sep">&nbsp;–&nbsp;</span>
				<span class="edd_price_option_price"><?php echo edd_currency_filter( edd_format_amount( $price['amount'] ) ) ?></span>
				
				<meta itemprop="price" content="<?php echo edd_get_price_option_amount( $download->ID, $key ) ?>">
				<meta itemprop="priceCurrency" content="USD"></label>
				
				<div class="edd_download_quantity_wrapper edd_download_quantity_price_option_<?php echo sanitize_key( $price['name'] ) ?>">
					<span class="edd_price_option_sep">&nbsp;x&nbsp;</span>
					<input type="number" min="0" step="1" name="edd_download_quantity_<?php echo $key ;?>" class="edd-input edd-item-quantity" value="<?php echo $value; ?>">
				</div>
				
		</li>
		
		
		<?php endforeach; ?>
	</ul>
</div>

<div class="edd_purchase_submit_wrapper">
	<a href="#" 
		class="button blue edd-submit edd-has-js edd-add-to-cart" 
		data-action="edd_add_to_cart" 
		data-download-id="<?php echo $download->ID ;?>" 
		data-variable-price="yes" 
		data-price-mode="multi" 
		data-price="0">
			
		<span class="edd-add-to-cart-label" style="opacity: 1;">Add to Cart</span> 
		<span class="edd-loading" style="margin-left: 5px; margin-top: -7px; opacity: 0;"><i class="edd-icon-spinner edd-icon-spin"></i></span>
	</a>
	
	<input type="submit" 
		class="edd-add-to-cart edd-no-js button blue edd-submit" 
		name="edd_purchase_download" 
		value="Add to Cart" 
		data-action="edd_add_to_cart" 
		data-download-id="<?php echo $download->ID ;?>" 
		data-variable-price="yes" 
		data-price-mode="multi" style="display: none;">
		
	<a href="<?php edd_get_checkout_uri() ;?>" class="edd_go_to_checkout button blue edd-submit" style="display:none;">Checkout</a>
	<span class="edd-cart-ajax-alert" aria-live="assertive">
		<span class="edd-cart-added-alert" style="display: none;">
			<i class="edd-icon-ok" aria-hidden="true"></i> Added to cart
		</span>
	</span>
</div><!--end .edd_purchase_submit_wrapper-->
               
<?php                
                
// GUM EDIT END
				/*
	
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
				*/
                    
                endif;
                do_action( 'edd_after_price_options_list', $download->ID, $prices, $type );
                ?>
        <!--</ul>
        </div>--><!--.edd_price_options-->
        
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
