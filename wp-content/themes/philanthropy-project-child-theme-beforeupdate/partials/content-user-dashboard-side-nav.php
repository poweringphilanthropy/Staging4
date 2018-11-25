<?php
/**
 * The template used for displaying page content in page-templates/user-dashboard.php
 *
 * @package Reach
 */

// echo "<pre>";
// print_r(pp_get_account_menu());
// echo "</pre>";

?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<div class="user-dashboard-menu">
		<ul class="menu" id="menu-user-dashboard">
	

			<?php foreach ( pp_get_account_menu() as $id => $menu) { ?>
			<li class="menu-item">
				<a href="<?php echo $menu['url']; ?>">
					<div class="icon profile">
				        <img src="<?php echo $menu['icon']; ?>" alt="<?php echo $menu['label']; ?>">
				    </div>
				    <div class="text">
				        <?php echo $menu['label']; ?>
				    </div>
				    <div class="clear"></div>
				</a>
			</li>
			<?php } ?>
			
		</ul>
	</div>

	<?php

	// get_template_part( 'partials/banner' );

	// if ( function_exists( 'charitable_get_user_dashboard' ) ) :
	// 	charitable_get_user_dashboard()->nav( array(
	// 		'container_class' => 'user-dashboard-menu',
	// 	) );
	// endif;

	?>
	<div class="block entry-block">
		<div class="entry cf">
			<?php the_content(); ?>
			<?php
				wp_link_pages( array(
					'before' => '<div class="page-links">' . __( 'Pages:', 'reach' ),
					'after'  => '</div>',
				) );
			?>
		</div><!-- .entry -->    
	</div><!-- .entry-block -->
</article><!-- post-<?php the_ID() ?> -->
