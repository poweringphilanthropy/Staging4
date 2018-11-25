<?php
/**
 * The template used to display the suggested amounts field.
 *
 * Override this template by copying it to yourtheme/charitable/charitable-ambassadors/form-fields/recipient-types.php
 *
 * @author  Studio 164a
 * @since   1.0.0
 * @version 1.0.0
 */

if ( ! isset( $view_args['form'] ) || ! isset( $view_args['field'] ) ) {
    return;
}

$form            = $view_args['form'];
$field           = $view_args['field'];
$options         = charitable_get_option( 'campaign_recipients', array() );
$recipient_types = charitable_get_recipient_types();

charitable_ambassadors_enqueue_styles();

?>
<div id="charitable_field_<?php echo $field['key'] ?>">
    <ul class="charitable-recipient-type-options charitable-recipient-type-options-<?php echo count( $options ) ?>">
    <?php foreach ( $options as $key => $option ) : 

        if ( ! isset( $recipient_types[ $option ] ) ) :
            unset( $options[ $key ] );
            continue;
        endif;

        $recipient_type = $recipient_types[ $option ];
        ?>
        <li class="charitable-recipient-type" data-recipient-type="<?php echo esc_attr( $option ) ?>">
            <input type="radio" name="<?php echo $field['key'] ?>" value="<?php echo $option ?>" />
            
            <?php printf( '<h4>%s</h4><p>%s</p>', $recipient_type['label'], $recipient_type['description'] ) ?>
        </li>      
    <?php endforeach ?>
    </ul>
    <div class="charitable-recipient-type-fields">
    <?php foreach ( $options as $option ) :         

        $recipient_type_fields = Charitable_Ambassadors_Campaign_Recipient_Form::get_instance()->get_individual_recipient_type_fields( $option, $recipient_types ); 

        if ( ! empty( $recipient_type_fields ) ) : 
        ?>
            <div class="hidden charitable-recipient-type-<?php echo $option ?>-fields" data-recipient-type="<?php echo esc_attr( $option ) ?>">
                <?php
                $i = 1;

                foreach ( $recipient_type_fields as $key => $field ) :
                                            
                    do_action( 'charitable_form_field', $field, $key, $form, $i );

                    $i += apply_filters( 'charitable_form_field_increment', 1, $field, $key, $form );

                endforeach;
                ?>
            </div><!-- .charitable-recipient-type-<?php echo $option ?>-fields -->
        <?php endif;
    endforeach ?>
    </div>
</div>