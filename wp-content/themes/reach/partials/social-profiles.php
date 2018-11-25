<?php
/**
 * Social network links
 *
 * @package Reach
 */
?>
<ul class="social">
	<?php

	foreach ( array_keys( array_reverse( reach_get_social_sites() ) ) as $site ) :

		$url = esc_url( get_theme_mod( $site ) );

		if ( strlen( $url ) ) :
		?>
			<li>
				<a class="<?php echo $site ?>" href="<?php echo $url ?>"><i class="icon-<?php echo $site ?>"></i></a>
			</li>
		<?php
		endif;

	endforeach;

	?>
</ul><!-- .social -->
