<?php
/**
 * Display the footer notice.
 *
 * @package Reach
 */

?>
<p class="footer-notice aligncenter">
	<?php

	$default = sprintf( '<a href="https://www.wpcharitable.com">%s</a>',
		__( 'Reach: A WordPress theme by WP Charitable', 'reach' )
	);

	echo html_entity_decode( get_theme_mod( 'footer_tagline', $default ) );

	?>
</p>
