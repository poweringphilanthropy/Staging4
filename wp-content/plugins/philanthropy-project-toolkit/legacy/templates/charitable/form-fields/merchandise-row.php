<?php
/**
 * The template used to display the merchandise fields.
 *
 * @author  Studio 164a
 * @since   1.0.0
 * @version 1.0.0 
 */

if ( ! isset( $view_args[ 'index' ] ) ) {
    return;
}

$form       = (isset($view_args[ 'form' ])) ? $view_args[ 'form' ] : new Charitable_Ambassadors_Campaign_Form();
$fields     = (isset($view_args[ 'fields' ])) ? $view_args[ 'fields' ] : PP_Merchandise::init()->get_fields();
$index      = $view_args[ 'index' ];
?>
<tr class="merchandise-form repeatable-field" data-index="<?php echo $index ?>">
    <td>    
        <div class="repeatable-field-wrapper">
            <div class="charitable-form-fields cf">
            <?php 
			// $form->view()->render_notices();
			// $form->view()->render_honeypot();
			// $form->view()->render_hidden_fields();
            $i = 1;

            foreach ( $fields as $key => $field ) :
			
			 $form->view()->render_field( $field, $key, array(
            'index' => $i,
        ) );

        $i += apply_filters( 'charitable_form_field_increment', 1, $field, $key, $form , $i);
		

                // do_action( 'charitable_form_field', $field, $key, $form, $i, 'merchandise[' . $index . ']' );

                // $i += apply_filters( 'charitable_form_field_increment', 1, $field, $key, $form );

            endforeach;

            ?>    
            </div>
            <button class="remove" data-pp-toolkit-remove-row="<?php echo $index ?>">x</button>
        </div>
    </td>
</tr>