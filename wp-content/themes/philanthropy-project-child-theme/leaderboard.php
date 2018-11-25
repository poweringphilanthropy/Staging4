<?php
/**
 * Campaign post type archive.
 *
 * This template is only used when Charitable is activated.
 *
 * @package     Reach
 */

global $wp_query;


// echo $wp_query->found_posts;
// echo "<pre>";
// print_r($wp_query);
// echo "</pre>";

get_header('dashboard');

$term = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) );
$color = get_term_meta( $term->term_id, '_dashboard_color', true );

$style_bg_color = '';
$style_color = '';
if(!empty($color)){
    $style_bg_color = ' style="background:'.$color.'"';
    $style_color = ' style="color:'.$color.'"';
}

$campaign_ids = wp_list_pluck( $wp_query->posts, 'ID' );
$leaderboard_data = new PP_Leaderboard_Data($campaign_ids);

$currency_helper = charitable_get_currency_helper();

// echo "<pre>";
// print_r($leaderboard_data->get_total_donors());
// echo "</pre>";

?>  
<main id="main" class="site-main site-content cf leaderboard">  
	
	<section class="leaderboard-heading">
        <div class="uk-width-1-1 ld-title">
            <h1 <?php echo $style_bg_color; ?>><?php echo $term->name; ?></h1>
        </div>
        <?php 
        $image_url = wp_get_attachment_url( get_term_meta( $term->term_id, '_featured_image', true ), 'full' ); 
        $image_url = aq_resize( $image_url, 1900, 600, true, true, true );
        ?>
        <?php if(!empty($image_url)): ?>
        <div class="uk-width-1-1 ld-image">
            <img src="<?php echo $image_url; ?>" alt="<?php echo $term->name; ?>">
        </div>
    	<?php endif; ?>
    </section>

    <section class="section-heading-button">
        <div class="heading-button-container">

            <?php // if(!empty($campaign_ids)): ?>
            
            <div class="heading-button-col">
                <a class="heading-button" href="#campaign-list" <?php echo $style_bg_color; ?>>DONATE TO A CAMPAIGN</a>
            </div>

            <?php // endif; ?>
            
            <div class="heading-button-col">
                <a class="heading-button" href="<?php echo home_url( 'create-campaign' ); ?>" <?php echo $style_bg_color; ?>>CREATE A CAMPAIGN</a>
            </div>
            <?php if( get_term_meta( $term->term_id, '_enable_log_service_hours', true ) == 'yes' ): ?>
            <div class="heading-button-col">
                <a class="heading-button log-service-hours-button" href="#" <?php echo $style_bg_color; ?> data-p_popup-open="log-service-hours">LOG SERVICE HOURS</a>
            </div>
        	<?php endif; ?>
        </div>
    </section>


	<div class="layout-wrapper">
		<div id="primary" class="content-area no-sidebar">
			<?php 

	        $notices = charitable_get_notices()->get_notices();
	        // echo "<pre>";
	        // print_r($notices);
	        // echo "</pre>";
	        if ( ! empty( $notices ) ) {

	            pp_toolkit_template( 'form-fields/pp-notices.php', array(
	                'notices' => $notices,
	                'autoclose' => true
	            ) );

	        } 
	        ?>

	        <div class="ld-content uk-grid">
	            <div class="uk-width-medium-6-10 ld-desc">
	                <?php echo apply_filters( 'the_content', $term->description ); ?>
	            </div>

	            <div class="uk-width-medium-4-10 ld-stats">
	                <div class="uk-grid">
	                    <div class="uk-width-small-4-10 ld-icon uk-text-center uk-vertical-align">
	                        <div class="uk-vertical-align-middle">
	                            <img class="ld-icon" src="<?php echo get_stylesheet_directory_uri(); ?>/media/black_Impact.png" alt="">
	                        </div>
	                    </div>
	                    <div class="uk-width-small-6-10">
	                        <ul class="stats">
	                            <li><span class="count" <?php echo $style_color; ?>><?php echo count($leaderboard_data->get_campaign_ids(true)); ?></span> Campaigns</li>
	                            <li><span class="count" <?php echo $style_color; ?>><?php echo $leaderboard_data->get_total_donors(); ?></span> Supporters</li>
	                            <li><span class="count" <?php echo $style_color; ?>><?php echo $currency_helper->get_monetary_amount( $leaderboard_data->get_total_donations(), 0 ); ?></span> Raised</li>
	                            <?php if( get_term_meta( $term->term_id, '_enable_log_service_hours', true ) == 'yes' ): ?>
	                            <li><span class="count" <?php echo $style_color; ?>><?php echo $leaderboard_data->get_total_service_hours(true); ?></span> Service Hours</li>
	                            <?php endif; ?>
	                        </ul>
	                    </div>
	                </div>

	                <div class="campaign-summary" style="border: none;padding: 0px;">
	                	<div class="share-under-desc">
	                		<?php pp_template( 'leaderboard/share.php', array('term' => $term) ); ?>
	        			</div>
	        		</div>

					<?php if( get_term_meta( $term->term_id, '_show_top_campaigns', true ) == 'yes'): ?>
					<div class="ld-donors-stat uk-grid">
	                    <div class="uk-width-1-1 ld-subtitle">
	                        <h2 <?php echo $style_bg_color; ?>>TOP CAMPAIGNS:</h2>
	                        <div class="container-donor">
	                            <?php if ( !empty($top_campaigns_by_donor = $leaderboard_data->get_top_campaigns_by_donor()) ) : ?>
	                            <table class="table-donor">
	                                <tbod
	                                    <?php  
	                                    $show_more = false;

	                                    $max_display = 5;
	                                    $i = 0;
	                                    foreach ( $top_campaigns_by_donor as $campaign ) : 
	                                        $amount = charitable_get_table( 'campaign_donations' )->get_campaign_donated_amount( $campaign->ID );
	                                        $amount = charitable_get_currency_helper()->get_monetary_amount( $amount );
	                                        
	                                        $tr_classes = 'donor-'.$i;
	                                        if($i >= $max_display ){
	                                            $tr_classes .= ' more';

	                                            $show_more = true;
	                                            // close tbody to separate
	                                            echo '</tbody><tbody class="more-donors" style="display:none;">';
	                                        }

	                                        ?>

	                                        <tr class="<?php echo $tr_classes; ?>">
	                                            <td class="donor-amount"<?php echo $style_color; ?>><?php echo $amount; ?></td>
	                                            <td class="donor-name"><?php echo get_the_title( $campaign->ID ); ?></td>
	                                        </tr>

	                                    <?php
	                                    $i++;
	                                    endforeach;
	                                    ?>
	                                </tbody>
	                            </table>

	                            <?php if($show_more): ?>
	                            <div class="load-more">
	                                <a href="javascript:;" class="load-more-button" <?php echo $style_color; ?>><?php _e('See All', 'philanthropy'); ?></a>
	                                <script>
	                                (function($){
	                                    $(document).on('click', 'a.load-more-button', function(e){
	                                        $('.table-donor .more-donors').slideDown(1000000); 
	                                        $(this).hide(); 
	                                        return false;
	                                        
	                                    });
	                                })(jQuery);
	                                </script>
	                            </div>
	                            <?php endif; // if($show_more) ?>


	                            <?php endif; // !empty($leaderboard_data->get_top_campaigns_by_donor() ?>
	                        </div>
	                    </div>
	                </div>

					<?php endif; ?>


					<?php 
	                /**
	                 * TOP FUNDRAISERS
	                 */
	                if( get_term_meta( $term->term_id, '_show_top_fundraisers', true ) == 'yes' ): ?>
	        
	                <div class="ld-donors-stat uk-grid">
	                    <div class="uk-width-1-1 ld-subtitle">
	                        <h2 <?php echo $style_bg_color; ?>>TOP FUNDRAISERS:</h2>
	                        <?php 
	                        $fundraisers = $leaderboard_data->get_top_fundraisers();
	                        if ( $fundraisers->count() ) : ?>
	                        <div class="container-donor load-more-table-container">
	                            <table class="table-donor">
	                                <tbody>
	                                <?php 
	                                $show_more = false;

	                                $i = 0;
	                                foreach ( $fundraisers as $fundraiser ) : 

	                                    // echo "<pre>";
	                                    // print_r($fundraiser);
	                                    // echo "</pre>";

	                                    $name = $fundraiser->referral;

	                                    $tr_classes = 'donor-'.$i;
	                                    if($i >= $max_display ){
	                                        $tr_classes .= ' more';

	                                        $show_more = true;
	                                        // close tbody to separate
	                                        echo '</tbody><tbody class="more-tbody" style="display:none;">';
	                                    }

	                                    ?>
	                                    <tr class="<?php echo $tr_classes; ?>">
	                                        <td class="donor-amount" <?php echo $style_color; ?>><?php echo charitable_get_currency_helper()->get_monetary_amount( $fundraiser->amount ); ?></td>
	                                        <td class="donor-name"><?php echo $name ?></td>
	                                    </tr>

	                                <?php
	                                $i++;
	                                endforeach;
	                                ?>
	                                </tbody>
	                            </table>

	                            <?php if($show_more): ?>
	                            <div class="load-more">
	                                <a href="javascript:;" class="load-more-button" <?php echo $style_color; ?>><?php _e('See All', 'pp-toolkit'); ?></a>
	                            </div>
	                            <?php endif; ?>
	                        </div>

	                        <?php endif; // if($fundraisers->count()) ?>
	                    </div>
	                </div>

	                <?php endif; //$leaderboard_data->display_top_campaigns() ?>

	        	</div>
	        </div>

			<div id="campaign-list" class="ld-campaigns">
				<div class="uk-width-1-1 ld-subtitle">
	                <h2 <?php echo $style_bg_color; ?>>Browse Campaigns:</h2>
	            </div>
				<?php

				// get_template_part( 'partials/banner' );

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
					charitable_template( 'campaign-loop.php', array( 'campaigns' => $wp_query, 'columns' => 3, 'color' => $color ) );

					reach_paging_nav( __( 'Older Campaigns', 'reach' ), __( 'Newer Campaigns', 'reach' ) );

					?>
				</div><!-- .campaigns-grid-wrapper -->
			</div>
		</div><!-- #primary -->            
	</div><!-- .layout-wrapper -->
</main><!-- #main -->   

<?php 
$_enable_log_service_hours = get_term_meta( $term->term_id, '_enable_log_service_hours', true ) == 'yes';
// $_enable_log_service_hours = false;
if( $_enable_log_service_hours ): ?>
<div class="p_popup" data-p_popup="log-service-hours">
    <div class="p_popup-inner">
        <a class="p_popup-close" data-p_popup-close="log-service-hours" href="#">x</a>
        
        <form id="save-service-hours" class="charitable-form philanthropy-modal form-service-hours" data-action="log-service-hours"  method="post" style="padding: 0px;">
            <div class="p_popup-header">
                <h2 class="p_popup-title"><span>Log Service Hours</span></h2>
                <div class="p_popup-notices uk-width-1-1"></div>
            </div>
            <div class="p_popup-content">
                <?php 
                wp_nonce_field( 'save_service_hours', '_save_service_hours_nonce' ); ?>
                <input type="hidden" name="chapter_form_action" value="save_service_hours">
                <input type="hidden" name="term_id" value="<?php echo $term->term_id; ?>">

                <div class="charitable-form-fields">
                    <div id="" class="charitable-form-field fullwidth">
                        <?php if( get_term_meta( $term->term_id, '_prepopulate_chapters', true ) == 'yes' ){ ?>
                        <select name="chapter_id" id="select-chapter">
                            <?php 
                            foreach ( pp_get_term_chapters($term->term_id) as $id => $name) {
                                echo '<option value="'.$id.'">'.$name.'</option>';
                            } ?>
                        </select>
                        <?php } else { ?>
                        <input type="text" class="" name="chapter_name" placeholder="Chapter" value="">
                        <?php } ?>
                    </div>
                </div>
                
                <div class="charitable-form-fields">
                    <div id="" class="charitable-form-field odd">
                        <input type="text" class="" name="first_name" placeholder="First Name" value="">
                    </div>
                    <div id="" class="charitable-form-field even">
                        <input type="text" class="" name="last_name" placeholder="Last Name" value="">
                    </div>
                </div>
                <div class="charitable-form-fields">
                    
                    <div id="" class="charitable-form-field odd">
                        <input type="text" class="datepicker" name="service_date" placeholder="Date of Service Hours" value="">
                    </div>
                    
                    <div id="" class="charitable-form-field even">
                        <input type="number" class="" name="service_hours" placeholder="Number of Service Hours" value="">
                    </div>
                </div>
                <div class="charitable-form-fields">
                    <div id="" class="charitable-form-field fullwidth">
                        <textarea name="description" id="" cols="" rows="4" placeholder="Description"></textarea>
                    </div>
                </div>

                <div class="repeateable-fields-container hidden">
                    <h5 class="additional-hours-title">Additional Service Hours</h5>
                </div>
            </div>
            <div class="additional-hours-template hidden">
                <div class="repeateable-fields">
                    <div class="charitable-form-fields remove-repeatable-fields">
                        <a href="">Remove</a>
                    </div>
                    <div class="charitable-form-fields">
                        <div id="" class="charitable-form-field odd">
                            <input type="text" class="datepicker" name="additional_hours[{?}][service_date]" placeholder="Date of Service Hours" value="">
                            <input type="number" class="" name="additional_hours[{?}][service_hours]" placeholder="Number of Service Hours" value="" style="margin-top: 10px;">
                        </div>
                        <div id="" class="charitable-form-field even">
                            <textarea name="additional_hours[{?}][description]" id="" cols="" rows="4" placeholder="Description"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="p_popup-footer">
                <div class="p_popup-submit-field">
                    <a href="#" class="add-additional-hours">Log additional service hours</a>
                    <button type="submit" class="button submit-service-hours"><?php _e('Submit', 'philanthropy'); ?></button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php
endif;

get_footer('dashboard');
