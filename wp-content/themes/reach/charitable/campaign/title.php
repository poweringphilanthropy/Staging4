<?php
/**
 * The template for displaying the campaign title.
 *
 * Override this template by copying it to yourtheme/charitable/campaign/title.php
 *
 * @author  Studio 164a
 * @package Reach
 * @since   1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

?>
<h1 class="campaign-title"><?php echo get_the_title( $view_args['campaign']->ID ) ?></h1>
