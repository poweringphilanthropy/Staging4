<?php
/**
 * The template used to display the ticket fields.
 *
 * @author  Studio 164a
 * @since   1.0.0
 * @version 1.0.0
 */


if ( ! isset( $view_args[ 'form' ] ) || ! isset( $view_args[ 'index' ] ) ) {
    return;
}

$form       = $view_args[ 'form' ];
$index      = $view_args[ 'index' ];
$ticket_object = Tribe__Tickets__Tickets::load_ticket_object( $form->get_ticket_id() );
$is_ticket_sold = ( method_exists($ticket_object, 'qty_sold') && !empty( $ticket_object->qty_sold() ) && ($ticket_object->qty_sold() > 0) );
?> 
<tr class="ticket-form repeatable-fieldx <?php echo ($is_ticket_sold) ? 'sold' : ''; ?>" data-index="<?php echo $index ?>">
    <td>    
        <div class="repeatable-field-wrapper">
            
            <?php if($is_ticket_sold): ?>
            <p>This ticket is already sold, if you want to remove the ticket from campaign page, you can adjust the ticket sale end date.</p>
            <?php endif; ?>

            <div class="charitable-form-fields cf">
            <?php
			// $form->view()->render_notices();
			// $form->view()->render_honeypot();
			// $form->view()->render_hidden_fields();			
            $i = 1;

            foreach ( $form->get_fields() as $key => $field ) :
					$form->view()->render_field( $field, $key, array(
					'index' => $i,
				) );

				$i += apply_filters( 'charitable_form_field_increment', 1, $field, $key, $form , $i);

                // do_action( 'charitable_form_field', $field, $key, $form, $i );

                // $i += apply_filters( 'charitable_form_field_increment', 1, $field, $key, $form );

            endforeach;

            ?>    
            </div>

            <?php if( !$is_ticket_sold ): ?>
            <button class="remove" data-pp-charitable-remove-child-row="<?php echo $index ?>">x</button>
            <?php endif; ?>
        </div>
    </td>
</tr>