<?php
/**
 * The template used to display the variable prices field.
 *
 * @author  Studio 164a
 * @since   1.0.0
 * @version 1.0.0
 */

if ( ! isset( $view_args[ 'form' ] ) || ! isset( $view_args[ 'field' ] ) ) {
    return;
}

$form       = $view_args[ 'form' ];
$field      = $view_args[ 'field' ];
$key        = $form->get_form_identifier();
$value      = isset( $field[ 'value' ] ) && is_array( $field[ 'value' ] ) ? $field[ 'value' ] : array();

/**
 * Add one empty value to show form
 */
if(empty($value)){
    $value = array(
        array(
            'name' => '',
            'amount' => '',
            'purchase_limit' => '',
        )
    );
}

// echo "<pre>";
// print_r($value);
// echo "</pre>";
?>
<table class="pp-edd-variable-prices charitable-campaign-form-table charitable-repeatable-form-field-table variable-pricing hidden"> 
    <tbody>
        <?php 
        foreach ( $value as $i => $price ) : 
            $download_id = $form->get_product_value( 'ID' );
            $max_purchase = (!empty($download_id)) ? $max_purchases = edd_pl_get_file_purchase_limit( $download_id, null, $i ) : $price['purchase_limit'];
           
            ?>          
            <tr class="variable-price repeatable-fieldx" data-index="<?php echo $i ?>">
                <td>
                    <div class="repeatable-field-wrapper">
                        <div class="charitable-form-field three-columns">
                            <label for="<?php printf( 'merchandise_%s_variable_prices_%d_name', $key, $i ) ?>"><?php _e( 'Size/Style Options', 'pp-toolkit' ) ?></label>
                            <input 
                                type="text" 
                                id="<?php printf( 'merchandise_%s_variable_prices_%d_name', $key, $i ) ?>" 
                                name="<?php printf( 'merchandise[%s][variable_prices][%d][name]', $key, $i ) ?>" 
                                value="<?php echo $price['name'] ?>"
                                placeholder="Small, Red or One-Size-Fits-All" />
                        </div>
                        <div class="charitable-form-field three-columns">
                            <label for="<?php printf( 'merchandise_%s_variable_prices_%d_amount', $key, $i ) ?>"><?php _e( 'Price', 'pp-toolkit' ) ?></label>
                            <input 
                                type="number" 
                                id="<?php printf( 'merchandise_%s_variable_prices_%d_amount', $key, $i ) ?>" 
                                name="<?php printf( 'merchandise[%s][variable_prices][%d][amount]', $key, $i ) ?>"
                                value="<?php echo $price['amount'] ?>", 
                                min="0"
                                step="0.01" />                       
                        </div>
                        <div class="charitable-form-field three-columns">
                            <label for="<?php printf( 'merchandise_%s_variable_prices_%d_purchase_limit', $key, $i ) ?>"><?php _e( 'Quantity Available', 'pp-toolkit' ) ?></label>
                            <input 
                                type="number" 
                                id="<?php printf( 'merchandise_%s_variable_prices_%d_purchase_limit', $key, $i ) ?>" 
                                name="<?php printf( 'merchandise[%s][variable_prices][%d][purchase_limit]', $key, $i ) ?>"
                                value="<?php echo $max_purchase; ?>", 
                                min="-1" />                       
                        </div>
                        <button class="remove" data-pp-charitable-remove-row="<?php echo $i ?>">x</button>
                    </div>
                </td>
            </tr>       
            <?php 

        endforeach;
        ?>
    </tbody>
    <tfoot class="add-more">
        <tr>
            <td colspan="3" class="add-row-pricing"><a class="add-row" href="#" data-charitable-add-row="variable-price" data-form-identifier="<?php echo $key ?>"><?php _e( 'Add more size/style options', 'pp-toolkit' ) ?></a></td>
        </tr>
    </tfoot>
</table>