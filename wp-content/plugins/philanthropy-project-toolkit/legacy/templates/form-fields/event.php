<?php
/**
 * The template used to display the merchandise fields.
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
$nonce      = wp_create_nonce( 'pp-event-form' );
$index      = 0;
?>
<table id="pp-event" class="pp-fundraising-table charitable-campaign-form-table charitable-repeatable-form-field-table"> 
    <thead>
        <tr>
            <td class="icon"><?php echo (isset($view_args['field']['icon_url'])) ? '<img src="'.$view_args['field']['icon_url'].'">' : ''; ?></td>
            <td class="desc">
                <h3><?php _e('Event Tickets + Registration', 'pp-toolkit'); ?></h3>
                <p><?php _e('You can create events and sell tickets to raise money for your campaign goal.', 'pp-toolkit'); ?></p>
            </td>
            <td class="button-add"><a class="add-row" href="#" data-charitable-add-row="event-form" data-nonce="<?php echo $nonce ?>"><?php _e( '+ ADD TO CAMPAIGN', 'pp-toolkit' ); ?></a></td>
        </tr>
    </thead>
    <tbody>
        <?php
        // echo "<pre>";
        // print_r($field[ 'value' ]);
        // echo "</pre>";
        foreach ( $field[ 'value' ] as $event ) :

            $submitted = array();

            if ( is_array( $event ) && isset( $event[ 'POST' ] ) ) {
                $submitted = $event[ 'POST' ];
                $event = null;
            }

            if ( empty( $event ) && empty( $submitted ) ) :
                continue;
            endif;
        
            $template = new PP_Toolkit_Template( 'form-fields/event-form.php', false );
            $template->set_view_args( array(
                'form'      => new PP_Event_Form( $event, $submitted ),
                'index'     => $index
            ) );
            $template->render();

            $index += 1;
        
        endforeach ?> 
        <tr class="loading-row"><td colspan="3"></td></tr>
    </tbody>
    <tfoot class="add-more <?php echo ($index <= 0) ? 'hide' : ''; ?>">
        <tr>
            <td colspan="3"><a class="add-row" href="#" data-charitable-add-row="event-form" data-nonce="<?php echo $nonce ?>"><?php _e( 'Add another event', 'pp-toolkit' ) ?></a></td>
        </tr>
    </tfoot>
</table>