<?php
/**
 * A stripped back footer used by the Stripped page template.
 *
 * @package Reach
 */
?>
		</div><!-- #main -->
		<footer id="site-footer" role="contentinfo">
			<div class="layout-wrapper">
				<div id="colophon">
					<?php
					if ( function_exists( 'wpml_languages_list' ) ) :
						echo wpml_languages_list( 0, 'language-list' );
					endif;
					?>						
				</div><!-- #rockbottom -->	
			</div><!-- .layout-wrapper -->
		</footer><!-- #site-footer -->
	</div><!-- .body-wrapper -->
</div><!-- #page -->

<?php wp_footer() ?>

</body>
</html>
