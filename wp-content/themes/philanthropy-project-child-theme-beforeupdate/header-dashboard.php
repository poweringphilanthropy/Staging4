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