<?php
/**
 * The template for displaying the title banner at the top of a page.
 *
 * @package Reach
 */

$banner_title    = reach_get_banner_title();
$banner_subtitle = reach_get_banner_subtitle();

if ( ! empty( $banner_title ) ) : ?>

	<header class="banner">
		<div class="shadow-wrapper">
			<h1 class="banner-title"><?php echo $banner_title ?></h1>
			<?php if ( $banner_subtitle ) : ?>
				<h2 class="banner-subtitle"><?php echo $banner_subtitle ?></h2>
			<?php endif ?>
		</div>
	</header><!-- .banner -->

<?php endif ?>
