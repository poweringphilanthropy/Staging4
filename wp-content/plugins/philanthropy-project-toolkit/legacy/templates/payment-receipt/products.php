<?php 
/**
 * Displays purchased merchandise and event tickets on the EDD checkout page.
 *
 * @since       1.0.0
 * @author      Eric Daams 
 * @copyright   Copyright (c) 2014, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License  
 */

global $edd_receipt_args;

$payment_id = $edd_receipt_args['id'];
$cart       = edd_get_payment_meta_cart_details( $payment_id, true );

if ( ! $edd_receipt_args[ 'charitable_edd_products' ] || ! $cart ) : 
    return;
endif;

$meta       = edd_get_payment_meta( $payment_id );
$email      = edd_get_payment_user_email( $payment_id );
$groups     = pp_edd_organize_downloads( $cart );

foreach ( $groups as $key => $group ) : 

    if ( count( $group ) ) :
    ?>
        <h3><?php echo ucfirst( $key ) ?></h3>
        <table id="edd_purchase_receipt_products">
            <thead>
                <th><?php _e( 'Name', 'pp-toolkit' ); ?></th>
                <?php if ( edd_use_skus() ) { ?>
                    <th><?php _e( 'SKU', 'pp-toolkit' ); ?></th>
                <?php } ?>
                <?php if ( edd_item_quantities_enabled() ) : ?>
                    <th><?php _e( 'Quantity', 'pp-toolkit' ); ?></th>
                <?php endif; ?>
                <th><?php _e( 'Price', 'pp-toolkit' ); ?></th>
            </thead>
            <tbody>
            <?php foreach ( $group as $key => $item ) : ?>

                <?php if( ! apply_filters( 'edd_user_can_view_receipt_item', true, $item ) ) : ?>
                    <?php continue; // Skip this item if can't view it ?>
                <?php endif; ?>

                <?php if( empty( $item['in_bundle'] ) ) : ?>
                <tr>
                    <td>

                        <?php
                        $price_id       = edd_get_cart_item_price_id( $item );
                        $download_files = edd_get_download_files( $item['id'], $price_id );
                        ?>

                        <div class="edd_purchase_receipt_product_name">
                            <?php echo esc_html( $item['name'] ); ?>
                            <?php if( edd_has_variable_prices( $item['id'] ) && ! is_null( $price_id ) ) : ?>
                            <span class="edd_purchase_receipt_price_name">&nbsp;&ndash;&nbsp;<?php echo edd_get_price_option_name( $item['id'], $price_id, $payment_id ); ?></span>
                            <?php endif; ?>
                        </div>

                        <?php if ( $edd_receipt_args['notes'] ) : ?>
                            <div class="edd_purchase_receipt_product_notes"><?php echo wpautop( edd_get_product_notes( $item['id'] ) ); ?></div>
                        <?php endif; ?>

                        <?php
                        if( edd_is_payment_complete( $payment_id ) && edd_receipt_show_download_files( $item['id'], $edd_receipt_args ) ) : ?>
                        <ul class="edd_purchase_receipt_files">
                            <?php
                            if ( ! empty( $download_files ) && is_array( $download_files ) ) :

                                foreach ( $download_files as $filekey => $file ) :
        
                                    $download_url = edd_get_download_file_url( $meta['key'], $email, $filekey, $item['id'], $price_id );
                                    ?>
                                    <li class="edd_download_file">
                                        <a href="<?php echo esc_url( $download_url ); ?>" class="edd_download_file_link"><?php echo edd_get_file_name( $file ); ?></a>
                                    </li>
                                    <?php
                                    do_action( 'edd_receipt_files', $filekey, $file, $item['id'], $payment_id, $meta );
                                endforeach;

                            elseif( edd_is_bundled_product( $item['id'] ) ) :

                                $bundled_products = edd_get_bundled_products( $item['id'] );

                                foreach( $bundled_products as $bundle_item ) : ?>
                                    <li class="edd_bundled_product">
                                        <span class="edd_bundled_product_name"><?php echo get_the_title( $bundle_item ); ?></span>
                                        <ul class="edd_bundled_product_files">
                                            <?php
                                            $download_files = edd_get_download_files( $bundle_item );

                                            if( $download_files && is_array( $download_files ) ) :

                                                foreach ( $download_files as $filekey => $file ) :

                                                    $download_url = edd_get_download_file_url( $meta['key'], $email, $filekey, $bundle_item, $price_id ); ?>
                                                    <li class="edd_download_file">
                                                        <a href="<?php echo esc_url( $download_url ); ?>" class="edd_download_file_link"><?php echo esc_html( $file['name'] ); ?></a>
                                                    </li>
                                                    <?php
                                                    do_action( 'edd_receipt_bundle_files', $filekey, $file, $item['id'], $bundle_item, $payment_id, $meta );

                                                endforeach;
                                            else :
                                                echo '<li>' . __( 'No downloadable files found for this bundled item.', 'pp-toolkit' ) . '</li>';
                                            endif;
                                            ?>
                                        </ul>
                                    </li>
                                    <?php
                                endforeach;

                            else :
                                echo '<li>' . apply_filters( 'edd_receipt_no_files_found_text', '', $item['id'] ) . '</li>';
                            endif; ?>
                        </ul>
                        <?php endif; ?>

                    </td>
                    <?php if ( edd_use_skus() ) : ?>
                        <td><?php echo edd_get_download_sku( $item['id'] ); ?></td>
                    <?php endif; ?>
                    <?php if ( edd_item_quantities_enabled() ) { ?>
                        <td><?php echo $item['quantity']; ?></td>
                    <?php } ?>
                    <td>
                        <?php if( empty( $item['in_bundle'] ) ) : // Only show price when product is not part of a bundle ?>
                            <?php echo edd_currency_filter( edd_format_amount( $item[ 'price' ] ) ); ?>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endif; ?>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php 
    endif;

endforeach;