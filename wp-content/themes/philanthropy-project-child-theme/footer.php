<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #main div and all content after
 *
 * @package reach
 */
?>
        </div><!-- #main -->
    </div><!-- .body-wrapper -->
</div><!-- #page -->
    <footer id="site-footer" class="wrapper" role="contentinfo">

        <?php get_template_part( 'partials/footer', 'social' ); ?>

        

        <div class="layout-wrapper wrapper-bottom-content">
            <div class="uk-grid">
                <div class="uk-width-1-1 uk-width-medium-1-2 footer-menu 3-col left-content">
                    <?php dynamic_sidebar( 'footer_left' ) ?>
                </div>
                <div class="uk-width-1-1 uk-width-medium-1-2 right-content">
                    <?php 
                    $image = reach_get_customizer_image_data( 'footer_image' );
                    if(isset($image['image']) && !empty($image['image'])):
                    ?>
                    <img src="<?php echo $image['image']; ?>" alt="">
                    <?php endif; ?>
                </div>
            </div>
            <div class="uk-grid">
                <div id="colophon" class="uk-width-1-1">
                    <?php 
                    if ( function_exists('wpml_languages_list') ) :
                        echo wpml_languages_list(0, 'language-list');
                    endif;
                    ?>
                    <p class="footer-notice aligncenter">
                    <?php 
                        if ( get_theme_mod( 'footer_tagline', false ) ) : 
                            echo str_replace('{year}', date('Y'), get_theme_mod( 'footer_tagline' ) );                    
                        endif;
                    ?>
                    </p>            
                </div><!-- #rockbottom -->  
            </div>
        </div><!-- .layout-wrapper -->
    </footer><!-- #site-footer -->
<?php wp_footer() ?>

</body>
</html>