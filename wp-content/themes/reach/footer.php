<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #main div and all content after
 *
 * @package Reach
 */
?>
		<!--</div>--><!-- #main -->
		<footer id="site-footer" role="contentinfo">
			<div class="layout-wrapper">
				<?php dynamic_sidebar( 'footer_left' ) ?>
				<div id="colophon" <?php if ( ! is_active_sidebar( 'footer_left' ) ) : ?>class="no-widgets"<?php endif ?>>
					<?php
					if ( function_exists( 'wpml_languages_list' ) ) :
						echo wpml_languages_list( 0, 'language-list' );
					endif;

					get_template_part( 'partials/footer', 'notice' );
					?>					
				</div><!-- #rockbottom -->	
			</div><!-- .layout-wrapper -->
		</footer><!-- #site-footer -->
	</div><!-- .body-wrapper -->
</div><!-- #page -->

<?php wp_footer() ?>

</body>
</html>
