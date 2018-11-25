<?php 

/**
 * Charitable Ambassadors Template Functions. 
 *
 * @author 		Studio164a
 * @category 	Functions
 * @package 	Charitable Ambassadors/Functions/Template
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

if ( ! function_exists( 'charitable_ambassadors_template_edit_campaign_link' ) ) :
    /**
     * Add campaign editing link to the top of a campaign when viewing on the frontend. 
     *
     * @return  boolean True if the template was displayed. False otherwise.    
     * @since   1.0.0
     */
    function charitable_ambassadors_template_edit_campaign_link() {
        /* Display the edit link if the campaign creator is looking at their own campaign. */
        if ( is_single() && 'campaign' == get_post_type() && charitable_is_current_campaign_creator( get_the_ID() ) ) {

            charitable_ambassadors_template( 'edit-campaign-link.php' );

            return true;
            
        }

        return false;
    }
endif;
