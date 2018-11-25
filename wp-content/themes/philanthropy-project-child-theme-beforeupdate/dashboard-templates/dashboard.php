<?php
/**
 * Single campaign template.
 *
 * This template is only used if Charitable is active.
 *
 * @package Reach
 */

global $post;

get_header('dashboard');

// Enqueue script
wp_enqueue_style( 'philanthropy-modal' );
wp_enqueue_script( 'jquery-ui-datepicker' );
wp_enqueue_script( 'philanthropy-modal' );
wp_localize_script( 'philanthropy-modal', 'PHILANTHROPY_MODAL', array(
    'ajax_url' => admin_url( 'admin-ajax.php', 'relative' ),
    'default_error_message' => __('Unable to process request.', 'pp-toolkit')
) );

?>

<main class="site-main site-content cf leaderboard" id="main">    

    <?php

    $currency_helper = charitable_get_currency_helper();

    /**
     * @var     Charitable_Campaign
     */
    $dashboard = new PP_Dashboard_Data( $post->ID );

    $style_bg_color = '';
    $style_color = '';
    $color = $dashboard->get_color();

    if(!empty($color)){
        $style_bg_color = ' style="background:'.$color.'"';
        $style_color = ' style="color:'.$color.'"';
    }

    $campaign_ids = $dashboard->get_campaign_ids();
    ?>
    <section class="leaderboard-heading">
        <div class="uk-width-1-1 ld-title">
            <h1 <?php echo $style_bg_color; ?>><?php echo get_the_title($post); ?></h1>
        </div>
        <?php  
        if ( has_post_thumbnail( $post->ID ) ) :
            $thumb_url = get_the_post_thumbnail_url( $post->ID, 'full' );
            $thumb_url = aq_resize( $thumb_url, 1900, 600, true, true, true );
            ?>
            <div class="uk-width-1-1 ld-image">
                <img src="<?php echo $thumb_url; ?>" alt="">
            </div>
            <?php  
        endif;
        ?>
    </section>

    <section class="section-heading-button">
        <div class="heading-button-container">

            <?php if(!empty($campaign_ids)): ?>
            
            <div class="heading-button-col">
                <a class="heading-button" href="#campaign-list" <?php echo $style_bg_color; ?>>DONATE TO A CAMPAIGN</a>
            </div>

            <?php endif; ?>
            
            <div class="heading-button-col">
                <a class="heading-button" href="<?php echo home_url( 'create-campaign' ); ?>" <?php echo $style_bg_color; ?>>CREATE A CAMPAIGN</a>
            </div>
            <?php if( $dashboard->is_track_service_hours_enable() ): ?>
            <div class="heading-button-col">
                <a class="heading-button log-service-hours-button" href="#" <?php echo $style_bg_color; ?> data-p_popup-open="log-service-hours">LOG SERVICE HOURS</a>
            </div>
        <?php endif; ?>
        </div>
    </section>

    <div class="layout-wrapper">

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
                <?php 
                the_content($post->ID); 
                ?>
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
                            <li><span class="count" <?php echo $style_color; ?>><?php echo count($campaign_ids); ?></span> Campaigns</li>
                            <li><span class="count" <?php echo $style_color; ?>><?php echo $dashboard->get_total_donors(); ?></span> Supporters</li>
                            <li><span class="count" <?php echo $style_color; ?>><?php echo $currency_helper->get_monetary_amount( $dashboard->get_total_donations(), 0 ); ?></span> Raised</li>
                            <?php if($dashboard->is_track_service_hours_enable()): ?>
                            <li><span class="count" <?php echo $style_color; ?>><?php echo $dashboard->get_total_service_hours(true); ?></span> Service Hours</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>

                <?php do_action( 'philanthropy_after_dashboard_content', $post ); ?>

                <?php if($dashboard->display_top_campaigns()): ?>
        
                <div class="ld-donors-stat uk-grid">
                    <div class="uk-width-1-1 ld-subtitle">
                        <h2 <?php echo $style_bg_color; ?>>TOP CAMPAIGNS:</h2>
                        <?php if(!empty($campaign_ids)): ?>
                        <div class="container-donor">
                            <?php if ( !empty($top_campaigns_by_donor = $dashboard->get_top_campaigns_by_donor()) ) : ?>
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


                            <?php endif; // !empty($dashboard->get_top_campaigns_by_donor() ?>
                        </div>

                        <?php endif; // if(!empty($campaign_ids)) ?>
                    </div>
                </div>

                <?php endif; //$dashboard->display_top_campaigns() ?>
                

                <?php 
                /**
                 * TOP FUNDRAISERS
                 */
                if($dashboard->display_top_fundraisers()): ?>
        
                <div class="ld-donors-stat uk-grid">
                    <div class="uk-width-1-1 ld-subtitle">
                        <h2 <?php echo $style_bg_color; ?>>TOP FUNDRAISERS:</h2>
                        <?php 
                        $fundraisers = $dashboard->get_top_fundraisers();
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
                                <a href="javascript:;" class="load-more-button"><?php _e('See All', 'pp-toolkit'); ?></a>
                            </div>
                            <?php endif; ?>
                        </div>

                        <?php endif; // if($fundraisers->count()) ?>
                    </div>
                </div>

                <?php endif; //$dashboard->display_top_campaigns() ?>
            </div>
        </div>

        <?php if ( !empty($campaign_ids) ): ?>
        <div id="campaign-list" class="ld-campaigns">
            <div class="uk-width-1-1 ld-subtitle">
                <h2 <?php echo $style_bg_color; ?>>Browse Campaigns:</h2>
            </div>

            <div class="campaigns-grid-wrapper">
                <?php

                charitable_template( 'campaign-loop.php', array( 'campaigns' => $dashboard->get_campaign_query() , 'columns' => 3, 'color' => $color ) );

                wp_reset_postdata();

                // reach_paging_nav( __( 'Older Campaigns', 'reach' ), __( 'Newer Campaigns', 'reach' ) );

                ?>
            </div>
        </div>
        <?php endif; ?>

    </div><!-- .layout-wrapper -->
</main><!-- #main -->


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
                $dashboard_id = get_queried_object_id();
                wp_nonce_field( 'save_service_hours', '_save_service_hours_nonce' ); ?>
                <input type="hidden" name="chapter_form_action" value="save_service_hours">
                <input type="hidden" name="dashboard_id" value="<?php echo $dashboard_id; ?>">

                <div class="charitable-form-fields">
                    <div id="" class="charitable-form-field fullwidth">
                        <?php if( $dashboard->is_prepopulate_chapters_enable() ){ ?>
                        <select name="chapter_id" id="select-chapter">
                            <?php foreach (pp_get_dashboard_chapters($dashboard_id) as $id => $name) {
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

get_footer('dashboard');