<?php
/**
 * The template for displaying the title banner at the top of a page.
 *
 * @package Reach
 */

$banner_title = reach_get_banner_title();

if ( ! empty( $banner_title ) ) : ?>

	<header class="banner">
		<div class="shadow-wrapper">
			<h1 class="banner-title"><?php echo $banner_title ?></h1>
			<?php get_template_part( 'partials/author', 'activity-summary' ) ?>
		</div>
	</header><!-- .banner -->

<?php endif ?>
