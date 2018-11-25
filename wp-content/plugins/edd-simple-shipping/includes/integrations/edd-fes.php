<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class EDD_Simple_Shipping_FES {

	public function edd_fes_simple_shipping() {
		if ( version_compare( fes_plugin_version, '2.3', '>=' ) ) {
			require_once edd_simple_shipping()->plugin_path . '/includes/integrations/edd-fes-shipping-field.php';
			add_filter(  'fes_load_fields_array', 'edd_fes_simple_shipping_add_field', 10, 1 );
			function edd_fes_simple_shipping_add_field( $fields ) {
				$fields['edd_simple_shipping'] = 'FES_Simple_Shipping_Field';
				return $fields;
			}
		}
	}


	/**
	 * Register a custom FES submission form button
	 *
	 * @since 2.0
	 *
	 * @return void
	 */
	public function edd_fes_simple_shipping_field_button( $title ) {
		if ( version_compare( fes_plugin_version, '2.2', '>=' ) ) {
			echo  '<button class="fes-button button" data-name="edd_simple_shipping" data-type="action" title="' . esc_attr( $title ) . '">'. __( 'Shipping', 'edd-simple-shipping' ) . '</button>';
		}
	}

	/**
	 * Setup the custom FES form field
	 *
	 * @since 2.0
	 *
	 * @return void
	 */
	public function edd_fes_simple_shipping_admin_field( $field_id, $label = "", $values = array() ) {
		if( ! isset( $values['label'] ) ) {
			$values['label'] = __( 'Shipping', 'edd-simple-shipping' );
		}

		$values['no_css']  = true;
		$values['is_meta'] = true;
		$values['name']    = 'edd_simple_shipping';
		?>
		<li class="edd_simple_shipping">
			<?php FES_Formbuilder_Templates::legend( $values['label'] ); ?>
			<?php FES_Formbuilder_Templates::hidden_field( "[$field_id][input_type]", 'edd_simple_shipping' ); ?>
			<?php FES_Formbuilder_Templates::hidden_field( "[$field_id][template]", 'edd_simple_shipping' ); ?>
			<div class="fes-form-holder">
				<?php FES_Formbuilder_Templates::common( $field_id, 'edd_simple_shipping', false, $values, false, '' ); ?>
			</div> <!-- .fes-form-holder -->
		</li>
	<?php
	}

	/**
	 * Indicate that this is a custom field
	 *
	 * @since 2.0
	 *
	 * @return bool
	 */
	public function edd_fes_simple_shipping_formbuilder_is_custom_field( $bool, $template_field ) {
		if ( $bool ) {
			return $bool;
		} else if ( isset( $template_field['template'] ) && $template_field['template'] == 'edd_simple_shipping' ) {
			return true;
		} else {
			return $bool;
		}
	}

	/**
	 * save the input values when the submission form is submitted
	 *
	 * @since 2.0
	 *
	 * @return void
	 */
	public function edd_fes_simple_shipping_save_custom_fields( $post_id ) {
		if ( isset( $_POST ['edd_simple_shipping'] ) && isset( $_POST ['edd_simple_shipping']['enabled'] ) ) {
			$domestic      = ! empty( $_POST ['edd_simple_shipping']['domestic'] ) ? edd_sanitize_amount( $_POST ['edd_simple_shipping']['domestic'] ) : 0;
			$international = ! empty( $_POST ['edd_simple_shipping']['international'] ) ? edd_sanitize_amount( $_POST ['edd_simple_shipping']['international'] ) : 0;
			update_post_meta( $post_id, '_edd_enable_shipping', '1' );
			update_post_meta( $post_id, '_edd_shipping_domestic', $domestic );
			update_post_meta( $post_id, '_edd_shipping_international', $international );

			$prices = edd_get_variable_prices( $post_id );
			if( ! empty( $prices ) ) {
				foreach( $prices as $price_id => $price ) {
					$prices[ $price_id ]['shipping'] = '1';
				}
				update_post_meta( $post_id, 'edd_variable_prices', $prices );
			}
		} else {
			delete_post_meta( $post_id, '_edd_enable_shipping' );
		}
	}

	/**
	 * Render our shipping fields in the submission form
	 *
	 * @since 2.0
	 *
	 * @return void
	 */
	public function edd_fes_simple_shipping_field( $attr, $post_id, $type ) {

		$required = '';
		if ( isset( $attr['required'] ) && $attr['required'] == 'yes' ) {
			$required = apply_filters( 'fes_required_class', ' edd-required-indicator', $attr );
		}

		$enabled       = get_post_meta( $post_id, '_edd_enable_shipping', true );
		$domestic      = get_post_meta( $post_id, '_edd_shipping_domestic', true );
		$international = get_post_meta( $post_id, '_edd_shipping_international', true );

		?>
		<style>
			div.fes-form fieldset .fes-fields.edd_simple_shipping label { width: 100%; display:block; }
			div.fes-form fieldset .fes-fields.edd_simple_shipping .edd-fes-shipping-fields label { width: 45%; display:inline-block; }
			div.fes-form fieldset .fes-fields .edd-shipping-field { width: 45%; display:inline-block; }
		</style>
		<div class="fes-fields <?php echo sanitize_key( $attr['name']); ?>">
			<label for="edd_simple_shipping[enabled]">
				<input type="checkbox" name="edd_simple_shipping[enabled]" id="edd_simple_shipping[enabled]" value="1"<?php checked( '1', $enabled ); ?>/>
				<?php _e( 'Enable Shipping', 'edd-simple-shipping' ); ?>
			</label>
			<div class="edd-fes-shipping-fields">
				<label for="edd_simple_shipping[domestic]"><?php _e( 'Domestic', 'edd-simple-shipping' ); ?></label>
				<label for="edd_simple_shipping[international]"><?php _e( 'International', 'edd-simple-shipping' ); ?></label>
				<input class="edd-shipping-field textfield<?php echo esc_attr( $required ); ?>" id="edd_simple_shipping[domestic]" type="text" data-required="<?php echo $attr['required'] ?>" data-type="text" name="<?php echo esc_attr( $attr['name'] ); ?>[domestic]" placeholder="<?php echo __( 'Enter the domestic shipping charge amount', 'edd-simple-shipping' ); ?>" value="<?php echo esc_attr( $domestic ) ?>" size="10" />
				<input class="edd-shipping-field textfield<?php echo esc_attr( $required ); ?>" id="edd_simple_shipping[international]" type="text" data-required="<?php echo $attr['required'] ?>" data-type="text" name="<?php echo esc_attr( $attr['name'] ); ?>[international]" placeholder="<?php echo __( 'Enter the international shipping charge amount', 'edd-simple-shipping' ); ?>" value="<?php echo esc_attr( $international ) ?>" size="10" />
			</div>
		</div> <!-- .fes-fields -->
	<?php
	}
}