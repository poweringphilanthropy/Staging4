<?php
/**
 * The template used to display the recipient type search field.
 *
 * Override this template by copying it to yourtheme/charitable/charitable-ambassadors/form-fields/recipient-type-search.php
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

if ( ! isset( $field[ 'recipient_type' ] ) ) {
    return;
}

wp_enqueue_style( 'select2' );
wp_enqueue_script( 'charitable-ambassadors-recipient-search' );

$recipient_type = $field[ 'recipient_type' ];
$has_options = ! empty( $field[ 'options' ] );
$required = isset( $field[ 'required' ] ) && $field[ 'required' ] ? '1' : '0';
?>
<div class="charitable-campaign-recipient-search">
    <select class="search-field select2" name="<?php echo esc_attr( $field[ 'key' ] ) ?>" data-placeholder="<?php echo esc_attr( $recipient_type[ 'search_placeholder' ] ) ?>" data-recipient-type="<?php echo esc_attr( $recipient_type[ 'recipient_type_key' ] ) ?>" data-required=<?php echo $required ?>>
        <?php if ( $has_options ) : 
            foreach ( $recipient_type[ 'options' ] as $value => $label ) : ?>
            <option value="<?php echo esc_attr( $value ) ?>"><?php echo $label ?></option>
            <?php 
            endforeach;
        endif ?>
    </select>
</div>
