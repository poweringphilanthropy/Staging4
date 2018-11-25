<?php
/**
 * The template used to display the suggested amounts field.
 *
 * @author  Studio 164a
 * @package Charitable/Templates/Form Fields
 * @since   1.4.14
 * @version 1.4.14
 */

if ( ! array_key_exists( 'content', $view_args['field'] ) ) {
	return;
}

echo $view_args['field']['content'];
