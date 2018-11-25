<?php
/**
 * The template used to display the ticket fields.
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
$nonce      = wp_create_nonce( 'pp-ticket-form' );
$index      = 0;
?>
<table class="pp-tickets charitable-campaign-form-table charitable-repeatable-form-field-table">
    <tbody>    
        <?php 
        foreach ( $field[ 'value' ] as $ticket_id ) :

            $submitted = array();

            if ( is_array( $ticket_id ) && isset( $ticket_id[ 'POST' ] ) ) {                
                $submitted = $ticket_id[ 'POST' ];
                $ticket_id = null;
            }
        
            $template = new PP_Toolkit_Template( 'form-fields/ticket-form.php', false );
            $template->set_view_args( array(
                'form'      => new PP_Ticket_Form( $form->get_form_identifier(), $form->get_event_id(), $ticket_id, $submitted ),
                'index'     => $index
            ) );
            $template->render();

            $index += 1;
        
        endforeach ?> 
        <tr class="loading-row"><td></td></tr>
    </tbody>
    <tfoot>
        <tr>
            <td><a class="add-row" href="#" data-namespace="<?php echo $form->get_form_identifier() ?>" data-event-id="<?php echo $form->get_event_id() ?>" data-charitable-add-row="ticket-form" data-nonce="<?php echo $nonce ?>"><?php _e( 'Add a ticket', 'pp-toolkit' ) ?></a></td>
        </tr>
    </tfoot>
</table>