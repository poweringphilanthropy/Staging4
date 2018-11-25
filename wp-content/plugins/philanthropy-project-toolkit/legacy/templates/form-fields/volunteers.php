<?php
/**
 * The template used to display the suggested amounts field.
 *
 * @author 	Studio 164a
 * @since 	1.0.0
 * @version 1.0.0
 */

if ( ! isset( $view_args[ 'form' ] ) || ! isset( $view_args[ 'field' ] ) ) {
	return;
}

$form 		= $view_args[ 'form' ];
$field 		= $view_args[ 'field' ];
$key 		= $field[ 'key' ];
$value 		= isset( $field[ 'value' ] ) && is_array( $field[ 'value' ] ) ? $field[ 'value' ] : array();
?>
<table id="charitable-campaign-volunteers-need" class="pp-fundraising-table charitable-campaign-form-table charitable-repeatable-form-field-table">	
	<thead>
        <tr>
         	<td class="icon"><?php echo (isset($view_args['field']['icon_url'])) ? '<img src="'.$view_args['field']['icon_url'].'">' : ''; ?></td>
            <td class="desc">
                <h3><?php _e('Volunteers', 'pp-toolkit'); ?></h3>
                <p><?php _e('You can recruit and organize participants to help with specific tasks through out your campaign. You receive emails from interested participants.', 'pp-toolkit'); ?></p>
            </td>
            <td class="button-add"><a class="add-row" href="#" data-charitable-add-row="volunteers-need"><?php _e( '+ ADD TO CAMPAIGN', 'pp-toolkit' ); ?></a></td>
        </tr>
    </thead>
	<tbody>
		<?php 
		foreach ( $value as $i => $need ) : 
			?>			
			<tr class="volunteers-need repeatable-fieldx" data-index="<?php echo $i ?>">
				<td colspan="3">
					<div class="repeatable-field-wrapper">
						<div class="charitable-form-field odd" style="width: 100%;">
							<label for="<?php printf( '%s_%d_need', $key, $i ) ?>"><?php _e( 'Task Description', 'charitable-ambassadors' ) ?></label>
							<input 
								type="text" 
								id="<?php printf( '%s_%d_need', $key, $i ) ?>" 
								name="<?php printf( '%s[%d][need]', $key, $i ) ?>" 
								value="<?php echo $need['need'] ?>"
								/>
						</div>
						
						<button class="remove" data-pp-charitable-remove-row="<?php echo $i ?>">x</button>
					</div>
				</td>
			</tr>		
			<?php 

		endforeach;
		?>
	</tbody>
    <tfoot class="add-more <?php echo (empty($value)) ? 'hide' : ''; ?>">
        <tr>
            <td colspan="3"><a class="add-row" href="#" data-charitable-add-row="volunteers-need"><?php _e( 'Add another task', 'pp-toolkit' ) ?></a></td>
        </tr>
    </tfoot>
</table>