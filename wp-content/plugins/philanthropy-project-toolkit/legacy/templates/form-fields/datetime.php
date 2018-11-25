<?php
/**
 * The template used to display date & time form fields.
 *
 * @author 	Studio 164a
 * @since 	1.0.0
 * @version 1.0.0
 */

if ( ! isset( $view_args[ 'form' ] ) || ! isset( $view_args[ 'field' ] ) ) {
	return;
}

$form 			= $view_args[ 'form' ];
$field 			= $view_args[ 'field' ];
$classes 		= esc_attr( $view_args[ 'classes' ] );
$is_required 	= isset( $field[ 'required' ] ) ? $field[ 'required' ] : false;
$value			= isset( $field[ 'value' ] ) && is_array( $field[ 'value' ] ) ? $field[ 'value' ] : array( 'date' => '', 'hour' => '12', 'minute' => '00', 'meridian' => 'am' );
$placeholder 	= isset( $field[ 'placeholder' ] ) ? esc_attr( $field[ 'placeholder' ] ) : '';

// echo "<pre>";
// print_r($value);
// echo "</pre>";

?>
<div id="charitable_field_<?php echo $field['key'] ?>" class="<?php echo $classes ?>">
	<?php if ( isset( $field['label'] ) ) : ?>
		<label for="charitable_field_<?php echo $field['key'] ?>">
			<?php echo $field['label'] ?>
			<?php if ( $is_required ) : ?>
				<abbr class="required" title="required">*</abbr>
			<?php endif ?>
		</label>
	<?php endif ?>
	<div class="uk-grid">
		<div class="uk-width-1-1 input-date-container">
			<input type="text" class="datepicker datepicker_<?php echo $field[ 'key' ] ?>" name="<?php echo $field[ 'key' ] ?>[date]" value="<?php echo $value[ 'date' ] ?>" placeholder="<?php echo $placeholder ?>" />
			<!-- <span class="at">@</span> -->
		</div>
		<div class="uk-width-1-1">
			<div class="uk-grid input-time-container">
				<div class="uk-width-1-3 select-container">
					<select name="<?php echo $field[ 'key' ] ?>[hour]">
						<option value="12" <?php selected( $value[ 'hour' ], '12' ) ?>>12</option>
						<option value="01" <?php selected( $value[ 'hour' ], '01' ) ?>>01</option>
						<option value="02" <?php selected( $value[ 'hour' ], '02' ) ?>>02</option>
						<option value="03" <?php selected( $value[ 'hour' ], '03' ) ?>>03</option>
						<option value="04" <?php selected( $value[ 'hour' ], '04' ) ?>>04</option>
						<option value="05" <?php selected( $value[ 'hour' ], '05' ) ?>>05</option>
						<option value="06" <?php selected( $value[ 'hour' ], '06' ) ?>>06</option>
						<option value="07" <?php selected( $value[ 'hour' ], '07' ) ?>>07</option>
						<option value="08" <?php selected( $value[ 'hour' ], '08' ) ?>>08</option>
						<option value="09" <?php selected( $value[ 'hour' ], '09' ) ?>>09</option>
						<option value="10" <?php selected( $value[ 'hour' ], '10' ) ?>>10</option>
						<option value="11" <?php selected( $value[ 'hour' ], '11' ) ?>>11</option>
					</select>
				</div>
				<div class="uk-width-1-3 select-container">
					<select name="<?php echo $field[ 'key' ] ?>[minute]">
						<option value="00" <?php selected( $value[ 'minute' ], '00' ) ?>>00</option>
						<option value="05" <?php selected( $value[ 'minute' ], '05' ) ?>>05</option>
						<option value="10" <?php selected( $value[ 'minute' ], '10' ) ?>>10</option>
						<option value="15" <?php selected( $value[ 'minute' ], '15' ) ?>>15</option>
						<option value="20" <?php selected( $value[ 'minute' ], '20' ) ?>>20</option>
						<option value="25" <?php selected( $value[ 'minute' ], '25' ) ?>>25</option>
						<option value="30" <?php selected( $value[ 'minute' ], '30' ) ?>>30</option>
						<option value="35" <?php selected( $value[ 'minute' ], '35' ) ?>>35</option>
						<option value="40" <?php selected( $value[ 'minute' ], '40' ) ?>>40</option>
						<option value="45" <?php selected( $value[ 'minute' ], '45' ) ?>>45</option>
						<option value="50" <?php selected( $value[ 'minute' ], '50' ) ?>>50</option>
						<option value="55" <?php selected( $value[ 'minute' ], '55' ) ?>>55</option>
					</select>
				</div>
				<div class="uk-width-1-3 select-container">
					<select name="<?php echo $field[ 'key' ] ?>[meridian]">
						<option value="am" <?php selected( $value[ 'meridian' ], 'am' ) ?>>AM</option>
						<option value="pm" <?php selected( $value[ 'meridian' ], 'pm' ) ?>>PM</option>
					</select>
				</div>
			</div>
		</div>
	</div>
</div>