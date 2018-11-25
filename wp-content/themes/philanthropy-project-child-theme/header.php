<?php
/**
 * The header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="main">
 *
 * @package Reach
 */
?><!DOCTYPE html>
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]> <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]> <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" <?php language_attributes() ?>> <!--<![endif]-->
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php wp_title( '|', true, 'right' ); ?></title>
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">

	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

<!-- 
<div class="layout-wrapper">
	<a class="skip-link screen-reader-text" href="#main"><?php // _e( 'Skip to content', 'reach' ); ?></a>
	<?php // get_template_part( 'partials/social-profiles' ) ?>
	<?php // get_template_part( 'partials/account-links' ) ?>
</div>
 -->
<div style='clear:both;'></div>

<header id="header" class="cf wrapper site-header" role="banner">
	<div class="layout-wrapper">
		<div class="site-branding site-identity">
			<?php reach_site_identity() ?>					
		</div><!-- .site-branding -->
		<div class="site-navigation">		
			<nav role="navigation">
				<a class="menu-toggle menu-button toggle-button" aria-controls="menu" aria-expanded="false"></a>
				<?php wp_nav_menu( array(   
					'theme_location' 	=> 'primary_navigation',
					'container' 		=> false,
					'menu_class' 		=> 'menu menu-site responsive_menu'
				) ) ?>
			</nav>
	    </div><!-- .site-navigation -->
	    <?php get_template_part( 'partials/account-links' ) ?>
	</div><!-- .layout-wrapper -->
</header><!-- #header -->

<?php 
if( is_front_page() ) {
   echo reach_get_media( array( 'split_media' => true ) );
} 
?>
<div class='header_image'>
<?php 
if (is_page_template('page-template-fullwidth.php')||is_page_template('page-featured-campaigns.php')) {
	if ( has_post_thumbnail() ) {
		the_post_thumbnail('full');
	} 
}
?>
</div>
<h1 class="page-title custom-title"><?php the_title() ?></h1>
	<div id="page" class="hfeed site-container">
		<!-- .layout-wrapper -->
		<div class="body-wrapper">
			
			<div id="main" class="site-content cf custom_content">