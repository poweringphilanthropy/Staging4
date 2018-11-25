<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #main div and all content after
 *
 * @package reach
 */
global $post;

$support_email = '';
$get_started_page = '';

if( $term = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) ) ){
    $support_email = get_post_meta( $term->term_id, '_support_email', true );
    $get_started_page = get_term_meta( $term->term_id, '_get_started_page', true );
}

// echo "<pre>";
// print_r($term);
// echo "</pre>";

?>
        </div><!-- #main -->
    </div><!-- .body-wrapper -->
</div><!-- #page -->

<footer id="site-footer" class="wrapper" role="contentinfo" style="background: #000; color:#fff;">
    <div class="layout-wrapper" style="padding-top: 20px;">
        <div class="footer-left">
            <div class="footer-left">
                <aside class="widget footer-widget widget_text" id="text-2">
                    <!-- <div>
                        <div class="textwidget">
                            <?php // echo strtoupper( get_the_title( $post ) ); ?>  &copy; <?php echo date('Y', current_time( 'timestamp' ) ); ?>
                        </div>
                    </div>
                    <br> -->
                    <?php if(!empty($support_email)): ?>
                    <div>
                        <div class="textwidget">
                           CONTACT US:
                        </div>
                        <div class="textwidget">
                           <a href="mailto:<?php echo $support_email; ?>" style="color:#fff;"><?php echo $support_email; ?></a>
                        </div>
                    </div>
                    <?php endif; ?>
                </aside>
                <aside class="widget footer-widget widget_text" id="text-3">
                    <div class="textwidget"></div>
                </aside>
                <aside class="widget footer-widget widget_text" id="text-4">
                    <div class="textwidget"></div>
                </aside>
                <aside class="widget footer-widget widget_nav_menu" id="nav_menu-2">
                    <div class="menu-footer-container">
                        <ul class="menu" id="menu-footer" style="text-transform: uppercase;">
                            <?php if(!empty($get_started_page)): ?>
                            <li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-7438" id="menu-item-7438">
                                <a href="<?php echo trailingslashit( get_term_link( $term->term_id, $term->taxonomy ) ) . 'getting-started'; ?>"><?php _e('Get Started', 'pp-toolkit'); ?></a>
                            </li>
                            <?php endif; ?>
                            <li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-7438" id="menu-item-7438">
                                <a href="<?php echo home_url( 'create-campaign' ); ?>" style="color:#fff;">Create Campaign</a>
                            </li>
                            <li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-7438" id="menu-item-7438">
                                <a href="<?php echo trailingslashit( get_term_link( $term->term_id, $term->taxonomy ) ) . 'report'; ?>"><?php _e('Reports', 'pp-toolkit'); ?></a>
                            </li>
                            <?php if(!is_user_logged_in()): ?>
                            <li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-7438" id="menu-item-7438">
                                <a href="<?php echo home_url( 'register' ); ?>" style="color:#fff;">Login</a>
                            </li>
                            <?php endif; ?>
                            <li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-7438" id="menu-item-7438">
                                <a href="<?php echo home_url( 'faq' ); ?>" style="color:#fff;">FAQ</a>
                            </li>
                            <li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-7263" id="menu-item-7263">
                                <a href="<?php echo home_url( 'terms-of-service' ); ?>" style="color:#fff;">Terms & Conditions</a>
                            </li>
                            <li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-7262" id="menu-item-7262">
                                <a href="<?php echo home_url( 'privacy-policy' ); ?>" style="color:#fff;">Privacy Policy</a>
                            </li>
                        </ul>
                    </div>
                </aside>
            </div>
        </div>
        <div class="footer-right">
            <?php 
            dynamic_sidebar( 'footer_right' ) 
            ?>
        </div>
        <div id="colophon" style="border-top-style: none; margin-top: 0px; ">
            <?php 
            if ( function_exists('wpml_languages_list') ) :
                echo wpml_languages_list(0, 'language-list');
            endif;
            ?>
            <p class="footer-notice aligncenter">
                <!-- <a href="<?php // echo home_url(); ?>" style="color:#fff;"><?php // echo get_bloginfo( 'name' ); ?>.com</a>   -->
                <?php 
                    if ( get_theme_mod( 'footer_tagline', false ) ) : 
                        echo str_replace('{year}', date('Y'), get_theme_mod( 'footer_tagline' ) );                    
                    endif;
                ?>
            </p>            
        </div><!-- #rockbottom -->  
    </div><!-- .layout-wrapper -->
</footer><!-- #site-footer -->
<?php wp_footer() ?>

</body>
</html>