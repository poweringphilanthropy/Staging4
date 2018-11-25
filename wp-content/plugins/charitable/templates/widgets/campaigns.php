<?php
/**
 * Display a list of campaigns.
 *
 * Override this template by copying it to yourtheme/charitable/widgets/campaigns.php
 *
 * @package Charitable/Templates/Widgets
 * @author  Studio 164a
 * @since   1.0.0
 * @version 1.4.18
 */

$campaigns = $view_args['campaigns'];
$show_thumbnail = isset( $view_args['show_thumbnail'] ) ? $view_args['show_thumbnail'] : true;
$thumbnail_size = apply_filters( 'charitable_campaign_widget_thumbnail_size', 'medium' );

if ( ! $campaigns->have_posts() ) :
	return;
endif;

echo $view_args['before_widget'];

if ( ! empty( $view_args['title'] ) ) :

	echo $view_args['before_title'] . $view_args['title'] . $view_args['after_title'];

endif;
?>
<ol class="campaigns">
<?php
while ( $campaigns->have_posts() ) :

	$campaigns->the_post();

	$campaign = new Charitable_Campaign( get_the_ID() );
	?>
	<li class="campaign">
		<?php
		if ( $show_thumbnail && has_post_thumbnail() ) :

			the_post_thumbnail( $thumbnail_size );

		endif;
		?>
		<h6 class="campaign-title"><a href="<?php the_permalink() ?>"><?php the_title() ?></a></h6>
		<?php if ( ! $campaign->is_endless() ) : ?>		
			<div class="campaign-time-left"><?php echo $campaign->get_time_left() ?></div>
		<?php endif ?>
	</li>
<?php
endwhile;
?>
</ol>
<?php

echo $view_args['after_widget'];

wp_reset_postdata();
