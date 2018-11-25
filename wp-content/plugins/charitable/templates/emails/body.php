<?php
/**
 * Email Body
 *
 * Override this template by copying it to yourtheme/charitable/emails/body.php
 *
 * @author  Studio 164a
 * @package Charitable/Templates/Emails
 * @version 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! isset( $view_args['email'] ) ) {
    return;
}

echo $view_args['email']->get_body();
