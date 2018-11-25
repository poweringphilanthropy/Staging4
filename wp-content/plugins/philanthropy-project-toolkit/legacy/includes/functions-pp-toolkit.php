<?php
/**
 * Function collections of PP Toolkit.
 * Overrides plugin dependencies template
 *
 * @version     1.0
 * @author lafif <hello@lafif.me>
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/***************
 * EDD RELATED
 ***************/


/**
 * Get Checkout Form
 *
 * @since 1.0
 * @return string
 */
function pp_edd_checkout_form() {
    $payment_mode = edd_get_chosen_gateway();
    $form_action  = esc_url( edd_get_checkout_uri( 'payment-mode=' . $payment_mode ) );

    ob_start();
        echo '<div id="edd_checkout_wrap">';
        if ( edd_get_cart_contents() || edd_cart_has_fees() ) :

            edd_checkout_cart();
        ?>
            <div id="edd_checkout_form_wrap" class="edd_clearfix">
                <?php do_action( 'edd_before_purchase_form' ); ?>
                <form id="edd_purchase_form" class="edd_form" action="<?php echo $form_action; ?>" method="POST" enctype="multipart/form-data">
                    <?php
                    /**
                     * Hooks in at the top of the checkout form
                     *
                     * @since 1.0
                     */
                    do_action( 'edd_checkout_form_top' );

                    if ( edd_show_gateways() ) {
                        do_action( 'edd_payment_mode_select'  );
                    } else {
                        do_action( 'edd_purchase_form' );
                    }

                    /**
                     * Hooks in at the bottom of the checkout form
                     *
                     * @since 1.0
                     */
                    do_action( 'edd_checkout_form_bottom' )
                    ?>
                </form>
                <?php do_action( 'edd_after_purchase_form' ); ?>
            </div><!--end #edd_checkout_form_wrap-->
        <?php
        else:
            /**
             * Fires off when there is nothing in the cart
             *
             * @since 1.0
             */
            do_action( 'edd_cart_empty' );
        endif;
        echo '</div><!--end #edd_checkout_wrap-->';
    return ob_get_clean();
}

function pp_add_downloads_to_cart() {
    $cart_total = 0;

    if ( isset( $_POST['downloads'] ) && ! empty( $_POST['downloads'] ) ) {

        foreach ( $_POST['downloads'] as $download_id => $download_options ) {

            $quantity       = isset( $download_options['quantity'] ) ? $download_options['quantity'] : 1;
            $options        = array(
                'quantity'  => $quantity,
            );

            $line_item_amount = 0;

            if ( isset( $download_options['price_id'] ) ) {

                $options['price_id'] = $download_options['price_id'];
                $price = edd_get_price_option_amount( $download_id, $options['price_id'] );

                if ( is_array( $options['price_id'] ) ) {

                    foreach ( $options['price_id'] as $price_id ) {

                        $line_item_amount += $quantity * edd_get_price_option_amount( $download_id, $price_id );

                    }
                } else {

                    $line_item_amount += $quantity * edd_get_price_option_amount( $download_id, $options['price_id'] );
                }
            } else {

                $line_item_amount += $quantity * edd_get_download_price( $download_id );

            }

            edd_add_to_cart( $download_id, $options );

            $cart_total += $line_item_amount;

        }
    }

    return $cart_total;
}

/***************
 * CHARITABLE EDD RELATED
 ***************/

/**
 * Email template tag: download_list
 * A list of download links for each download purchased
 *
 * @param int $payment_id
 *
 * @return string download_list
 */
function pp_edd_email_tag_download_list( $payment_id ) {
    
    // $cart = Charitable_EDD_Cart::create_with_payment( $payment_id );
    $donation_id    = get_post_meta( $payment_id, 'charitable_donation_from_edd_payment', true );

    if(empty($donation_id))
        return edd_email_tag_download_list( $payment_id );
    
    /**
     * Display donation and downloads
     * @var EDD_Payment
     */
    $payment = new EDD_Payment( $payment_id );

    $payment_data  = $payment->get_meta();
    $download_list = '<ul>';
    $cart_items    = $payment->cart_details;
    $cart_fees    = $payment->fees;
    $email         = $payment->email;

    // $donation_log = get_post_meta( $donation_id, 'donation_from_edd_payment_log', true );
    // $donation = charitable_get_donation( $donation_id );
    
    // echo "<pre>";
    // print_r($cart_fees);
    // echo "</pre>";

    // echo "<pre>";
    // print_r($cart_items);
    // echo "</pre>";
    
    // echo "<pre>";
    // print_r($donation_log);
    // echo "</pre>";

    // echo "<pre>";
    // print_r($donation);
    // echo "</pre>";


    /**
     * Display Donations
     */
    if ( !empty($cart_fees) ) {

        foreach ($cart_fees as $fee) {
            if ( ! Charitable_EDD_Cart::fee_is_donation( $fee ) )
                continue;

            $download_list .= '<li>' . sprintf(__('<strong>%s</strong> (%s)', 'philanthropy'), $fee['label'], charitable_format_money($fee['amount']) ) . '</li>';
        }
    }

    /**
     * Display downloads,
     * assume all downloads with 100% campaign benefactor relationship
     */
    if ( $cart_items ) {
        $show_names = apply_filters( 'edd_email_show_names', true );
        $show_links = apply_filters( 'edd_email_show_links', true );

        foreach ( $cart_items as $item ) {

            if ( edd_use_skus() ) {
                $sku = edd_get_download_sku( $item['id'] );
            }

            if ( edd_item_quantities_enabled() ) {
                $quantity = $item['quantity'];
            }

            $price_id = edd_get_cart_item_price_id( $item );
            if ( $show_names ) {

                $title = '<strong>' . get_the_title( $item['id'] ) . '</strong>';

                // if ( ! empty( $quantity ) && $quantity > 1 ) {
                //  $title .= "&nbsp;&ndash;&nbsp;" . __( 'Quantity', 'easy-digital-downloads' ) . ': ' . $quantity;
                // }

                if ( ! empty( $sku ) ) {
                    $title .= "&nbsp;&ndash;&nbsp;" . __( 'SKU', 'easy-digital-downloads' ) . ': ' . $sku;
                }

                if ( edd_has_variable_prices( $item['id'] ) && isset( $price_id ) ) {
                    $title .= "&nbsp;&ndash;&nbsp;" . edd_get_price_option_name( $item['id'], $price_id, $payment_id );
                }

                $download_list .= '<li>' . $item['quantity'] . 'x ' . apply_filters( 'edd_email_receipt_download_title', $title, $item, $price_id, $payment_id ) . ' ('.charitable_format_money($item['price']).')<br/>';
            }

            $files = edd_get_download_files( $item['id'], $price_id );

            if ( ! empty( $files ) ) {

                foreach ( $files as $filekey => $file ) {

                    if ( $show_links ) {
                        $download_list .= '<div>';
                        $file_url = edd_get_download_file_url( $payment_data['key'], $email, $filekey, $item['id'], $price_id );
                        $download_list .= '<a href="' . esc_url_raw( $file_url ) . '">' . edd_get_file_name( $file ) . '</a>';
                        $download_list .= '</div>';
                    } else {
                        $download_list .= '<div>';
                        $download_list .= edd_get_file_name( $file );
                        $download_list .= '</div>';
                    }

                }

            } elseif ( edd_is_bundled_product( $item['id'] ) ) {

                $bundled_products = apply_filters( 'edd_email_tag_bundled_products', edd_get_bundled_products( $item['id'] ), $item, $payment_id, 'download_list' );

                foreach ( $bundled_products as $bundle_item ) {

                    $download_list .= '<div class="edd_bundled_product"><strong>' . get_the_title( $bundle_item ) . '</strong></div>';

                    $files = edd_get_download_files( $bundle_item );

                    foreach ( $files as $filekey => $file ) {
                        if ( $show_links ) {
                            $download_list .= '<div>';
                            $file_url = edd_get_download_file_url( $payment_data['key'], $email, $filekey, $bundle_item, $price_id );
                            $download_list .= '<a href="' . esc_url( $file_url ) . '">' . edd_get_file_name( $file ) . '</a>';
                            $download_list .= '</div>';
                        } else {
                            $download_list .= '<div>';
                            $download_list .= edd_get_file_name( $file );
                            $download_list .= '</div>';
                        }
                    }
                }
            }


            // if ( '' != edd_get_product_notes( $item['id'] ) ) {
            //  $download_list .= ' &mdash; <small>' . edd_get_product_notes( $item['id'] ) . '</small>';
            // }


            if ( $show_names ) {
                $download_list .= '</li><br>';
            }
        }
    }

    if ( ( $fees = edd_get_payment_fees( $payment->ID, 'fee' ) ) ){
        foreach ($fees as $fee) {
            $download_list .= '<li>';
            $download_list .= sprintf(__('<strong>%s</strong> (%s)', 'philanthropy'), $fee['label'], charitable_format_money($fee['amount']));
            $download_list .= '</li><br>';
        }
    }


    $download_list .= '</ul>';

    return $download_list;
}

/**
 * Email template tag: download_list
 * A list of download links for each download purchased in plaintext
 *
 * @since 2.1.1
 * @param int $payment_id
 *
 * @return string download_list
 */
function pp_edd_email_tag_download_list_plain( $payment_id ) {
    // $cart = Charitable_EDD_Cart::create_with_payment( $payment_id );
    $donation_id    = get_post_meta( $payment_id, 'charitable_donation_from_edd_payment', true );

    if(empty($donation_id))
        return edd_email_tag_download_list( $payment_id );
    
    /**
     * Display donation and downloads
     * @var EDD_Payment
     */
    $payment = new EDD_Payment( $payment_id );

    $payment_data  = $payment->get_meta();
    $download_list = '<ul>';
    $cart_items    = $payment->cart_details;
    $cart_fees    = $payment->fees;
    $email         = $payment->email;

    // $donation_log = get_post_meta( $donation_id, 'donation_from_edd_payment_log', true );
    // $donation = charitable_get_donation( $donation_id );

    /**
     * Display Donations
     */
    if ( !empty($cart_fees) ) {

        foreach ($cart_fees as $fee) {
            if ( ! Charitable_EDD_Cart::fee_is_donation( $fee ) )
                continue;

            $download_list .= '<li>' . sprintf(__('<strong>%s</strong> (%s)', 'philanthropy'), $fee['label'], charitable_format_money($fee['amount']) ) . '</li>';
        }
    }

    /**
     * Display downloads
     */
    if ( $cart_items ) {
        $show_names = apply_filters( 'edd_email_show_names', true );
        $show_links = apply_filters( 'edd_email_show_links', true );

        foreach ( $cart_items as $item ) {

            if ( edd_use_skus() ) {
                $sku = edd_get_download_sku( $item['id'] );
            }

            if ( edd_item_quantities_enabled() ) {
                $quantity = $item['quantity'];
            }

            $price_id = edd_get_cart_item_price_id( $item );
            if ( $show_names ) {

                $title = '<strong>' . get_the_title( $item['id'] ) . '</strong>';

                if ( ! empty( $quantity ) && $quantity > 1 ) {
                    $title .= "&nbsp;&ndash;&nbsp;" . __( 'Quantity', 'easy-digital-downloads' ) . ': ' . $quantity;
                }

                if ( ! empty( $sku ) ) {
                    $title .= "&nbsp;&ndash;&nbsp;" . __( 'SKU', 'easy-digital-downloads' ) . ': ' . $sku;
                }

                if ( edd_has_variable_prices( $item['id'] ) && isset( $price_id ) ) {
                    $title .= "&nbsp;&ndash;&nbsp;" . edd_get_price_option_name( $item['id'], $price_id, $payment_id );
                }

                $download_list .= '<li>' . $item['quantity'] . 'x ' . apply_filters( 'edd_email_receipt_download_title', $title, $item, $price_id, $payment_id ) . ' ('.charitable_format_money($item['price']).')<br/>';
            }

            // display shipping
            if(!empty($item['fees'])){
                foreach ($item['fees'] as $key => $item_fee) {
                    $download_list .= '<div>';
                    $download_list .= sprintf(__('%s (%s)', 'philanthropy'), $item_fee['label'], charitable_format_money($item_fee['amount']));
                    $download_list .= '</div>';
                }
            }
            

            $files = edd_get_download_files( $item['id'], $price_id );

            if ( ! empty( $files ) ) {

                foreach ( $files as $filekey => $file ) {

                    if ( $show_links ) {
                        $download_list .= '<div>';
                            $file_url = edd_get_download_file_url( $payment_data['key'], $email, $filekey, $item['id'], $price_id );
                            $download_list .= '<a href="' . esc_url_raw( $file_url ) . '">' . edd_get_file_name( $file ) . '</a>';
                            $download_list .= '</div>';
                    } else {
                        $download_list .= '<div>';
                            $download_list .= edd_get_file_name( $file );
                        $download_list .= '</div>';
                    }

                }

            } elseif ( edd_is_bundled_product( $item['id'] ) ) {

                $bundled_products = apply_filters( 'edd_email_tag_bundled_products', edd_get_bundled_products( $item['id'] ), $item, $payment_id, 'download_list' );

                foreach ( $bundled_products as $bundle_item ) {

                    $download_list .= '<div class="edd_bundled_product"><strong>' . get_the_title( $bundle_item ) . '</strong></div>';

                    $files = edd_get_download_files( $bundle_item );

                    foreach ( $files as $filekey => $file ) {
                        if ( $show_links ) {
                            $download_list .= '<div>';
                            $file_url = edd_get_download_file_url( $payment_data['key'], $email, $filekey, $bundle_item, $price_id );
                            $download_list .= '<a href="' . esc_url( $file_url ) . '">' . edd_get_file_name( $file ) . '</a>';
                            $download_list .= '</div>';
                        } else {
                            $download_list .= '<div>';
                            $download_list .= edd_get_file_name( $file );
                            $download_list .= '</div>';
                        }
                    }
                }
            }


            if ( '' != edd_get_product_notes( $item['id'] ) ) {
                $download_list .= ' &mdash; <small>' . edd_get_product_notes( $item['id'] ) . '</small>';
            }


            if ( $show_names ) {
                $download_list .= '</li>';
            }
        }
    }


    $download_list .= '</ul>';

    return $download_list;
}

/***************
 * CHARITABLE
 ***************/
function pp_get_override_charitable_form_field_templates(){
    
    $templates = array( 
        'edd-downloads', 
        'variable-prices', 
        'merchandise', 
        'event', 
        'datepicker', 
        'datetime', 
        'ticket', 
        'team-fundraising', 
        'sponsors', 
        'volunteers', 
        'referrer', 
        'image-crop',
        'picture',
        'fieldset',
        'donation-levels',
        'payout-options',
        'finished-button',
        'login-signup-button',
        'select-non-profit',
    );

    return apply_filters( 'pp_get_override_charitable_form_field_templates', $templates );
}

function pp_get_override_charitable_templates(){

    $templates = array(
        'shortcodes/submit-campaign.php',
        'widgets/donors.php'
    );

    return apply_filters( 'pp_get_override_charitable_templates', $templates );
}

function order_by_post_id_and_campaign_id($query_group, $Charitable_Query){

    if ( ! $Charitable_Query->get( 'distinct_donors', false ) ) {
        global $wpdb;

        $query_group = "GROUP BY {$wpdb->posts}.ID, cd.campaign_id";
    }

    return $query_group;
}

/**
 * Get donors from multiple campaign ids, 
 * used on leaderboard
 * @param  [type]  $campaign_ids    [description]
 * @param  boolean $distinct_donors [description]
 * @return [type]                   [description]
 */
function philanthropy_get_multiple_donors($campaign_ids, $distinct_donors = false ){

    add_filter( 'charitable_query_groupby', 'order_by_post_id_and_campaign_id', 100, 2 );

    $query_args = array(
        'number' => -1,
        'output' => 'donors',
        'campaign' => $campaign_ids,
        'distinct_donors' => $distinct_donors,
        'distinct' => false,
    );
    $donors = new Charitable_Donor_Query( $query_args );

    remove_filter( 'charitable_query_groupby', 'order_by_post_id_and_campaign_id', 100 );

    return $donors;
}



function philanthropy_get_connected_downloads($edd_campaign){
    $campaign_downloads = $edd_campaign->get_connected_downloads();
    if( (false === $campaign_downloads) || empty($campaign_downloads) )
        return false;

    // echo "<pre>";
    // print_r($campaign_downloads);
    // echo "</pre>";

    $downloads = array();
    foreach ($campaign_downloads as $download) {
        $post_id = is_a( $download, 'WP_Post' ) ? $download->ID : $download[ 'id' ];
        $download_category = get_the_terms( $post_id, 'download_category' );
        $download_category = (isset($download_category[0])) ? $download_category[0] : false;
        if($download_category){
            $downloads[$download_category->slug]['term'] = $download_category;
            // for ticket we need to group with event id
            if($download_category->slug == 'ticket'){
                $event_id = get_post_meta( $post_id, '_tribe_eddticket_for_event', true );
                $downloads[$download_category->slug]['posts'][$event_id][] = $download;
            } else {
                $downloads[$download_category->slug]['posts'][] = $download;
            }
        } else {
            $downloads[] = $download;
        }
    }

    // echo "<pre>";
    // print_r($downloads);
    // echo "</pre>";

    return $downloads;
}

function philanthropy_get_campaign_event_ids($campaign_id){

    // maybe it is child campaign
    $parent_id = wp_get_post_parent_id( $campaign_id );
    if(!empty($parent_id)){
        $parent_campaign = new Charitable_Campaign( $parent_id ); 
        if($parent_campaign->get('team_fundraising') == 'on'){
            $campaign_id = $parent_id;
        }
    }

    $events = get_post_meta($campaign_id, '_campaign_events', true);

    return $events;
}

function philanthropy_get_volunteers($campaign){

    if(is_int($campaign)){
        $campaign = new Charitable_Campaign( $campaign );
    }

    // maybe it is child campaign
    $parent_id = wp_get_post_parent_id( $campaign->ID );
    if(!empty($parent_id)){
        $parent_campaign = new Charitable_Campaign( $parent_id ); 
        if($parent_campaign->get('team_fundraising') == 'on'){
            $campaign = $parent_campaign;
        }
    }

    $return = array();

    $needs  = $campaign->volunteers;
    if(!empty($needs)){
        
        $volunteers = wp_list_pluck( $needs, 'need' );
        if(is_array($volunteers) && !empty($volunteers)) 
            $return = array_filter($volunteers);
    }
    
    
    return $return;
}

function philanthropy_get_donors($campaign_id = false){

    $defaults = array (
        'number' => -1,
        'output' => "donors",
        'orderby' => "amount",
        'campaign' => charitable_get_current_campaign_id(),
        'distinct_donors' => 'on',
    );
    
    // Parse incoming $args into an array and merge it with $defaults
    $query_args = wp_parse_args( $args, $defaults );

    if($campaign_id){
        $query_args['campaign'] = $campaign_id;
    }

    return new Charitable_Donor_Query( $query_args );
}


/***************
 * EVENTS RELATED
 ***************/

/**
 * Retrieve the ID numbers of all tickets of an event
 *
 * @param mixed $event_id
 *
 * @return array
 */
function pp_get_event_tickets_ids($event_id) {
    if (is_object($event_id))
        $event_id = $event_id->ID;

    $query = new WP_Query([
        'post_type'      => 'download',
        // 'meta_key'       => Tribe__Tickets_Plus__Commerce__EDD__Main::$event_key,
        'meta_key'       => tribe( 'tickets-plus.commerce.edd' )->event_key,
        'meta_value'     => $event_id,
        'meta_compare'   => '=',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'post_status'    => 'publish',
        'orderby'        => 'date',
        'order'          => 'ASC'
    ]);

    return $query->posts;
}

/**
 * Display the campaign featured image. 
 *
 * @param   Charitable_Campaign $campaign
 * @return  void
 * @since   1.0.0
 */
if ( ! function_exists( 'reach_template_campaign_impact_summary' ) ) : 
    /**
     * Display the campaign featured image. 
     *
     * @param   Charitable_Campaign $campaign
     * @return  void
     * @since   1.0.0
     */
    function reach_template_campaign_impact_summary( Charitable_Campaign $campaign ) {    
        ?>
        <div class="campaign-image impact-goal-field" style="overflow: visible; line-height: 20px; font-size: 18px; margin:0 0 24px;">
            <div style="font-size: 24px" class="compaign-target-head">Impact Target</div><br>
            <?php echo $campaign->impact_goal ?>
        </div>
        <?php
    }
endif;

/**
 * Organize downloads into "merchandise" and "tickets" arrays.
 *
 * @param   array[] $downloads
 * @return  array[]
 * @since   1.0.0
 */
function pp_edd_organize_downloads( $downloads ) {
    $groups = array( 'merchandise' => array(), 'tickets' => array() );

    /* Unless a download is a ticket, we will classify it as "merchandise". */
    foreach ( $downloads as $download ) {
        $d = is_a( $download, 'WP_Post' ) ? $download : $download[ 'id' ];
        $group = has_term( 'ticket', 'download_category', $d ) ? 'tickets' : 'merchandise';
        $groups[ $group ][] = $download;
    }

    return $groups;
}

/**
 * Move array menu position
 * @param  [type] $a [description]
 * @param  [type] $b [description]
 * @return [type]    [description]
 */
function move_menu($a, $b) {
    global $menu;

    $out = array_splice($menu, $a, 1);
    array_splice($menu, $b, 0, $out);
}

/**
 * Load a view from the admin/views folder.
 *
 * If the view is not found, an Exception will be thrown.
 *
 * Example usage: charitable_admin_view('metaboxes/cause-metabox');
 *
 * @param   string      $view           The view to display.
 * @param   array       $view_args      Optional. Arguments to pass through to the view itself
 * @return  void
 * @since   1.0.0
 */
function pp_toolkit_admin_view( $view, $view_args = array() ) {

    $filename = pp_toolkit()->directory_path . 'includes/admin/views/' . $view . '.php';

    if ( ! is_readable( $filename ) ) {
        _doing_it_wrong( __FUNCTION__, __( 'Passed view (' . $filename . ') not found or is not readable.', 'charitable' ), '1.0.0' );
    }

    ob_start();

    include( $filename );

    ob_end_flush();
}

/**
 * Pretty same with charitable_get_table( 'edd_benefactors' )->get_campaign_benefactors( $campaign_id, false );
 * but with download category slug
 * @param  [type]  $camaign_id      [description]
 * @param  boolean $download_slug   [description]
 * @param  boolean $exclude_expired [description]
 * @return [type]                   [description]
 */
function pp_get_campaign_download_benefactor($camaign_id, $download_slug = false, $exclude_expired = true){
    global $wpdb;

    if($download_slug){
        $where_slug = "AND term.slug = '{$download_slug}'";
    } else {
        $where_slug = '';
    }

    if ( $exclude_expired ) {
        $exclude_expired_clause = "AND (
            ch.date_created <= UTC_TIMESTAMP()
            AND ( ch.date_deactivated = '0000-00-00 00:00:00' OR ch.date_deactivated > UTC_TIMESTAMP() )                    
        )";
    } else {
        $exclude_expired_clause = '';
    }

    $sql = "SELECT 
        edd.campaign_benefactor_id, 
        edd.edd_is_global_contribution, 
        edd.edd_download_id, 
        edd.edd_download_category_id, 
        ch.campaign_id, 
        ch.contribution_amount, 
        ch.contribution_amount_is_percentage, 
        ch.contribution_amount_is_per_item, 
        ch.date_created, 
        ch.date_deactivated,
        ( SELECT ch.date_deactivated = '0000-00-00 00:00:00' OR ch.date_deactivated > UTC_TIMESTAMP() ) as is_active
    FROM {$wpdb->prefix}charitable_edd_benefactors edd 
    INNER JOIN {$wpdb->prefix}charitable_benefactors ch ON ch.campaign_benefactor_id = edd.campaign_benefactor_id 
    INNER JOIN {$wpdb->prefix}posts download ON download.ID = edd.edd_download_id
    INNER JOIN {$wpdb->prefix}term_relationships as rel ON download.ID = rel.object_ID
    INNER JOIN {$wpdb->prefix}term_taxonomy as tax ON (rel.term_taxonomy_id = tax.term_taxonomy_id) AND (tax.taxonomy = 'download_category')
    INNER JOIN {$wpdb->prefix}terms as term ON (tax.term_id = term.term_id)
    WHERE download.post_status = 'publish'
    AND ch.campaign_id = '{$camaign_id}'
    {$where_slug}
    {$exclude_expired_clause}
    ORDER BY is_active DESC";

    return $wpdb->get_results( $sql, OBJECT_K );
}

function pp_unique_filename_callback($dir, $name, $ext){
    return $name;
}

/**
 * MERCHANDISE RELATED
 */
function pp_get_remaining_merchandise_stock($download_id, $price_id = false){

    if(!function_exists('edd_pl_get_file_purchase_limit'))
        return new WP_Error('function_not_exists', sprintf(__('%s function not exists', 'pp-toolkit'), 'edd_pl_get_file_purchase_limit'));

    $remaining = 'unlimited';

    $max_purchases = edd_pl_get_file_purchase_limit( $download_id, null, $price_id );

    if(empty($max_purchases)){
        // if 0 or meta not set, it is unlimited
        $remaining = 'unlimited';
    } elseif($max_purchases < 0) {
        // if -1, it is sold out / not available to purchase
        $remaining = 0;
    } else {
        $purchases     = edd_pl_get_file_purchases( $download_id, $price_id );
        $remaining = $max_purchases - $purchases;
    }

    return ($remaining >= 0) ? $remaining : 0;

}


function pp_is_merchandise_on_sale($download_id){
    $on_sale = true;

    $now = current_time( 'timestamp' );

    $start_date = get_post_meta( $download_id, 'merchandise_start_date', true );
    if(!empty($start_date) && (strtotime($start_date) > $now))
        $on_sale = false;

    $end_date = get_post_meta( $download_id, 'merchandise_end_date', true );
    if($on_sale && !empty($end_date) && (strtotime($end_date) < $now))
        $on_sale = false;

    return $on_sale;
}

function philanthropy_get_campaign_merchandise_ids($campaign_id){

    // maybe it is child campaign
    $parent_id = wp_get_post_parent_id( $campaign_id );
    if(!empty($parent_id)){
        $parent_campaign = new Charitable_Campaign( $parent_id ); 
        if($parent_campaign->get('team_fundraising') == 'on'){
            $campaign_id = $parent_id;
        }
    }

    // $downloads = charitable()->get_db_table('edd_benefactors')->get_single_download_campaign_benefactors($campaign_id);
    $downloads = charitable_get_table( 'benefactors' )->get_campaign_benefactors_by_extension( $campaign_id, 'charitable-edd' );
    $download_ids = wp_list_pluck( $downloads, 'edd_download_id' );

    // $debug = array();

    $merchandise = array();
    if(!empty($download_ids)):
    foreach ($download_ids as $id) {
        if( !has_term( 'merchandise', 'download_category', $id ) || in_array($id, $merchandise) )
            continue;

        // check if merchandise on sale
        if(!pp_is_merchandise_on_sale($id))
            continue;

        $merchandise[] = $id;


        // $start_date = get_post_meta( $id, 'merchandise_start_date', true ); 
        // $end_date = get_post_meta( $id, 'merchandise_end_date', true ); 
        // $debug = array(
        //     'product_id' => $id,
        //     'start' => $start_date,
        //     'end' => $end_date
        // );
    }
    endif;

    // echo "<pre>";
    // print_r($debug);
    // echo "</pre>";

    return $merchandise;
}

function addhttp($url) {
    if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
        $url = "http://" . $url;
    }
    return $url;
}

function pp_dashboard_insert_chapter($dashboard_id, $name){
    global $wpdb;

    $table_name = $wpdb->prefix . 'chapters';
    $wpdb->insert( 
        $table_name, 
        array( 
            'dashboard_id' => $dashboard_id, 
            'name' => $name 
        ), 
        array( 
            '%d', 
            '%s' 
        ) 
    );

    return $wpdb->insert_id;
}

function pp_insert_term_chapter($term_id, $name){
    global $wpdb;

    $table_name = $wpdb->prefix . 'chapters';
    $wpdb->insert( 
        $table_name, 
        array( 
            'term_id' => $term_id, 
            'name' => $name 
        ), 
        array( 
            '%d', 
            '%s' 
        ) 
    );

    return $wpdb->insert_id;
}

function pp_remove_chapter($chapter_id){
    global $wpdb;

    $table_name = $wpdb->prefix . 'chapters';
    return $wpdb->delete( 
        $table_name, 
        array( 'id' => $chapter_id ), 
        array( '%d' )
    );
}

function pp_get_dashboard_chapters($dashboard_id){
    global $wpdb;

    $chapters = array();

    $_chapters = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}chapters WHERE dashboard_id = '{$dashboard_id}'");
    if(!empty($_chapters)):
    foreach ($_chapters as $chapter) {
        $chapters[$chapter->id] = $chapter->name;
    }
    endif;

    return $chapters;
}

function pp_get_term_chapters($term_id){
    global $wpdb;

    $chapters = array();

    $_chapters = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}chapters WHERE term_id = '{$term_id}'");
    if(!empty($_chapters)):
    foreach ($_chapters as $chapter) {
        $chapters[$chapter->id] = $chapter->name;
    }
    endif;

    return $chapters;
}

function pp_save_chapter_service_hours($data){
    global $wpdb;

    // echo "<pre>";
    // print_r($data);
    // echo "</pre>";
    // exit();

    $success = false;

    $chapter_id = (isset($data['chapter_id'])) ? sanitize_text_field( $data['chapter_id'] ) : 0;
    $chapter_name = (isset($data['chapter_name'])) ? sanitize_text_field( $data['chapter_name'] ) : '';
    $dashboard_id = (isset($data['dashboard_id'])) ? sanitize_text_field( $data['dashboard_id'] ) : '';
    $term_id = (isset($data['term_id'])) ? sanitize_text_field( $data['term_id'] ) : '';
    $first_name = (isset($data['first_name'])) ? sanitize_text_field( $data['first_name'] ) : '';
    $last_name = (isset($data['last_name'])) ? sanitize_text_field( $data['last_name'] ) : '';
    $service_date = (isset($data['service_date'])) ? sanitize_text_field( $data['service_date'] ) : null;
    $service_hours = (isset($data['service_hours'])) ? sanitize_text_field( $data['service_hours'] ) : 0;
    $description = (isset($data['description'])) ? stripslashes( sanitize_text_field($data['description']) ) : 0;
    $additional_hours = (isset($data['additional_hours'])) ? $data['additional_hours'] : array();

    if( empty($chapter_id) && !empty($chapter_name) ){

        if(!empty($dashboard_id)){
            $chapter_id = pp_dashboard_insert_chapter( absint( $dashboard_id ), $chapter_name);
        }

        if(!empty($term_id)){
            $chapter_id = pp_insert_term_chapter( absint( $term_id ), $chapter_name);
        }
        
    }

    if(empty($chapter_id)){
        return false;
    }

    // reformat date
    $formatted_service_date = null;
    if (($timestamp = strtotime($service_date)) !== false) {
        $formatted_service_date = date("Y-m-d", $timestamp);
    }

    $pp_chapters = PP_Chapters::init();

    $db = $pp_chapters->db_chapter_service_hours;

    // insert main hours
    $parent = 0;

    $insert_main = $db->insert(
        array( 
            'chapter_id' => $chapter_id, 
            'first_name' => $first_name, 
            'last_name' => $last_name, 
            'service_hours' => $service_hours, 
            'service_date' => $formatted_service_date, 
            'parent' => $parent, 
            'description' => $description, 
        ),
        'chapter_service_hours'
    );

    if($insert_main){
        $success = true;
        $parent = $insert_main;
    }

    if(!empty($additional_hours) && is_array($additional_hours)):
    foreach ($additional_hours as $key => $additional) {

        if(empty($additional['service_hours']))
            continue;

        $additional_service_date = null;
        if (($timestamp = strtotime($additional['service_date'])) !== false) {
            $additional_service_date = date("Y-m-d", $timestamp);
        }

        $insert_additional = $db->insert(
            array( 
                'chapter_id' => $chapter_id, 
                'first_name' => $first_name, 
                'last_name' => $last_name, 
                'service_hours' => $additional['service_hours'], 
                'service_date' => $additional_service_date, 
                'parent' => $parent, 
                'description' => $additional['description'], 
            ), 
            'chapter_service_hours'
        );
    }
    endif;

    return $success;
}

function pp_get_dashbaord_embed_code($dashboard_id){
    $dashboard_url = get_permalink( $dashboard_id );
    $dashboard_url = untrailingslashit( $dashboard_url ) . '/widget';
    
    $js_embed = '
    <iframe id="g4g-dashboard-'.$dashboard_id.'" class="pp-embed-iframe" src="'.$dashboard_url.'" width="100%"></iframe>
    <script type="text/javascript" src="'.pp_toolkit()->directory_url . 'assets/js/dashboard-embed.js"></script>';
    return apply_filters( 'pp_dashboard_embed_code', $js_embed, $dashboard_id );
}

if(!function_exists('filter_array_by_value')):
function filter_array_by_value($array, $index, $value){ 
    $newarray = array();
    if(is_array($array) && count($array)>0){ 
        foreach(array_keys($array) as $key){ 
            $temp[$key] = $array[$key][$index]; 
             
            if ($temp[$key] == $value){ 
                $newarray[$key] = $array[$key]; 
            } 
        } 
      } 
  return $newarray; 
} 
endif;

function pp_get_merged_team_campaign_ids($campaign){

    if(!is_a($campaign, 'Charitable_Campaign')){
        $campaign = new Charitable_Campaign( $campaign ); 
    }

    $campaign_id = $campaign->ID;
    $campaign_ids = array($campaign_id);

    if($campaign->get('team_fundraising') == 'on'){
        $ca = Charitable_Ambassadors_Campaign::get_instance();
        $childrens = $ca->get_child_campaigns( $campaign_id );
        if ( !empty( $childrens ) ) {
            $campaign_ids = array_merge( $campaign_ids, $childrens );
        }
    }

    return $campaign_ids;
}

function pp_get_referrer_name($name, $link = true){
    if(substr( $name, 0, 15 ) === "child_campaign_"){
        $child_campaign_id = str_replace('child_campaign_', '', $name);
        $author_id = get_post_field( 'post_author', $child_campaign_id );
        $user = get_userdata( $author_id );
        $name = implode(' ', array_filter(array($user->first_name, $user->last_name)));

        if($link){
            $name = '<a href="'. get_permalink( $child_campaign_id ) .'">' . $name . '</a>';
        } else {
            $name;
        }
    }

    return $name;
}

function delete_connected_organizations_transient(){
    return delete_transient( 'pp_get_connected_organizations' );
}

function pp_get_connected_organizations(){

    // maybe later we need to use transient for better performance
    // $transient_key = 'pp_get_connected_organizations';

    // $non_profit_data = get_transient( $transient_key );
    // if ( false === $non_profit_data ) {

        $non_profit_data = array();

        try {

            $args = array(
                'timeout'     => 120,
                // 'redirection' => 5,
                // 'httpversion' => '1.0',
                // 'user-agent'  => 'WordPress/' . $wp_version . '; ' . home_url(),
                // 'blocking'    => true,
                // 'headers'     => array(),
                // 'cookies'     => array(),
                // 'body'        => null,
                // 'compress'    => false,
                // 'decompress'  => true,
                // 'sslverify'   => true,
                // 'stream'      => false,
                // 'filename'    => null
            );

            $request = wp_remote_get( esc_url( charitable_get_option( 'nonprofit_endpoint' ) ), $args );
            if( is_wp_error( $request ) ) {
                throw new Exception( $request->get_error_message() );
            }

            $body = wp_remote_retrieve_body( $request );
            if(empty($body)){
                throw new Exception('Empty response');
            }

            $data = json_decode( $body );
            if(empty($data)){
                throw new Exception('Empty data.');
                
            }

            foreach ($data as $non_profit) {
               $non_profit_data[$non_profit->stripe_id] = $non_profit;
            }

            // Put the results in a transient. Expire after 1 week.
            // set_transient( $transient_key, $non_profit_data, 1 * WEEK_IN_SECONDS );

        } catch (Exception $e) {
            return false;
        }
    // }

    return $non_profit_data;
}

function pp_get_connected_organization_options(){
    $data = pp_get_connected_organizations();

    $options = array();
    foreach ($data as $non_profit) {
       $options[$non_profit->stripe_id] = $non_profit->name;
    }

    return $options;
}

function pp_get_dashboard_term_id($post_id){
    global $wpdb;

    $term_id = $wpdb->get_var( $wpdb->prepare( 
        "SELECT term_id FROM $wpdb->termmeta WHERE meta_key = '_dashboard_page' AND meta_value = %d", 
        $post_id
    ) );

    return $term_id;
}

// ANOTHER WAY TO GET merchandise of campaign
// $downloads      = $campaign->get_connected_downloads( array( 
//     'post_status' => 'publish',
//     'tax_query' => array(
//         array(
//             'taxonomy' => 'download_category',
//             'field'    => 'slug',
//             'terms'    => 'merchandise'
//         )       
//     ) ) 
// );