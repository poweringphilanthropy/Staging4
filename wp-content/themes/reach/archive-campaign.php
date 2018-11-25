<?php
/**
 * Campaign post type archive.
 *
 * This template is only used when Charitable is activated.
 *
 * @package     Reach
 */

get_header();

?>  
<main id="main" class="site-main site-content cf">  
	<div class="layout-wrapper">
		<div id="primary" class="content-area no-sidebar">
			<?php

			get_template_part( 'partials/banner' );

			?>
			<div class="campaigns-grid-wrapper">                                
				<nav class="campaigns-navigation" role="navigation">
					<a class="menu-toggle menu-button toggle-button" aria-controls="menu" aria-expanded="false"></a>
					<?php reach_crowdfunding_campaign_nav() ?>              
				</nav>
				<?php

				/**
				 * This renders a loop of campaigns that are displayed with the
				 * `reach/charitable/campaign-loop.php` template file.
				 *
				 * @see 	charitable_template_campaign_loop()
				 */
				charitable_template_campaign_loop( false, 3 );

				reach_paging_nav( __( 'Older Campaigns', 'reach' ), __( 'Newer Campaigns', 'reach' ) );

				?>
			</div><!-- .campaigns-grid-wrapper -->
		</div><!-- #primary -->            
	</div><!-- .layout-wrapper -->
</main><!-- #main -->   
<?php

get_footer();
