<?php
/**
 * Renders the custom styles added by Charitable.
 *
 * Override this template by copying it to yourtheme/charitable/custom-styles.css.php
 *
 * @author  Studio 164a
 * @package Charitable/Templates/CSS
 * @since   1.0.0
 * @version 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

$highlight_colour = charitable_get_option( 'highlight_colour', apply_filters( 'charitable_default_highlight_colour', '#f89d35' ) );

?>
<style id="charitable-highlight-colour-styles">
.campaign-raised .amount, 
.campaign-figures .amount,
.donors-count, 
.time-left,
.charitable-form-field a:not(.button),
.charitable-form-fields .charitable-fieldset a:not(.button),
.charitable-notice,
.charitable-notice .errors a {
    color: <?php echo $highlight_colour ?>;
}

.campaign-progress-bar .bar,
.donate-button,
.charitable-donation-form .donation-amount.selected,
.charitable-donation-amount-form .donation-amount.selected {
    background-color: <?php echo $highlight_colour ?>;
}

.charitable-donation-form .donation-amount.selected,
.charitable-donation-amount-form .donation-amount.selected,
.charitable-notice, 
.charitable-drag-drop-images li:hover a.remove-image,
.supports-drag-drop .charitable-drag-drop-dropzone.drag-over {
    border-color: <?php echo $highlight_colour ?>;
}

<?php do_action( 'charitable_custom_styles', $highlight_colour ) ?>
</style>