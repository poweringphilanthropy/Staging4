<?php
/**
 * Display a list of campaign categories or tags.
 *
 * Override this template by copying it to yourtheme/charitable/widgets/campaign-terms.php
 *
 * @package Charitable/Templates/Widgets
 * @author  Studio 164a
 * @since   1.0.0
 * @version 1.0.0
 */

$taxonomy   = isset( $view_args['taxonomy'] ) ? $view_args['taxonomy'] : 'campaign_category';
$show_count = isset( $view_args['show_count'] ) && $view_args['show_count'];
$hide_empty = isset( $view_args['hide_empty'] ) && $view_args['hide_empty'];

echo $view_args['before_widget'];

if ( ! empty( $view_args['title'] ) ) :

	echo $view_args['before_title'] . $view_args['title'] . $view_args['after_title'];

endif;
?>
<ul class="charitable-terms-widget">
	<?php wp_list_categories( array(
		'title_li'   => '',
		'taxonomy'   => $taxonomy,
		'show_count' => $show_count,
		'hide_empty' => $hide_empty,
	) ) ?>
</ul><!-- .charitable-terms-widget -->
<?php

echo $view_args['after_widget'];
