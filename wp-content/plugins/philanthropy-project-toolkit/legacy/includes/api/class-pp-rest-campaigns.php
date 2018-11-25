<?php
/**
 * REST API: PP_Rest_Campaigns class
 *
 * @package PP_Toolkit
 * @subpackage REST_API
 */

/**
 * Core class to access posts via the REST API.
 *
 * @see WP_REST_Controller
 */
class PP_Rest_Campaigns extends WP_REST_Controller {

    /**
     * Post type.
     * @access protected
     * @var string
     */
    protected $post_type;

    /**
     * Instance of a post meta fields object.
     * @access protected
     * @var WP_REST_Post_Meta_Fields
     */
    protected $meta;

    /**
     * Constructor.
     * @access public
     *
     * @param string $post_type Post type.
     */
    public function __construct() {
        $this->post_type = 'campaign';
        $this->namespace = 'philanthropy';
        $this->rest_base = 'campaigns';

        $this->meta = new WP_REST_Post_Meta_Fields( $this->post_type );
    }

    /**
     * Registers the routes for the objects of the controller.
     * @access public
     *
     * @see register_rest_route()
     */
    public function register_routes() {

        register_rest_route( $this->namespace, '/' . $this->rest_base, array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_items' ),
                'permission_callback' => array( $this, 'get_items_permissions_check' ),
                'args'                => $this->get_collection_params(),
            ),
            'schema' => array( $this, 'get_public_item_schema' ),
        ) );
        
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', array(
            'args' => array(
                'id' => array(
                    'description' => __( 'Unique identifier for the object.' ),
                    'type'        => 'integer',
                ),
            ),
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_item' ),
                'permission_callback' => array( $this, 'get_item_permissions_check' ),
                'args'                => array(
                    'context' => $this->get_context_param( array( 'default' => 'view' ) ),
                ),
            ),
            'schema' => array( $this, 'get_public_item_schema' ),
        ) );
    }

    /**
     * Checks if a given request has access to read campaigns.
     * @access public
     *
     * @param  WP_REST_Request $request Full details about the request.
     * @return true|WP_Error True if the request has read access, WP_Error object otherwise.
     */
    public function get_items_permissions_check( $request ) {

        // $post_type = get_post_type_object( $this->post_type );

        // if ( 'edit' === $request['context'] && ! current_user_can( $post_type->cap->edit_posts ) ) {
        //     return new WP_Error( 'rest_forbidden_context', __( 'Sorry, you are not allowed to edit posts in this post type.' ), array( 'status' => rest_authorization_required_code() ) );
        // }

        return true;
    }

    /**
     * Retrieves a collection of posts.
     * @access public
     *
     * @param WP_REST_Request $request Full details about the request.
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     */
    public function get_items( $request ) {

        // Ensure a search string is set in case the orderby is set to 'relevance'.
        // if ( ! empty( $request['orderby'] ) && 'relevance' === $request['orderby'] && empty( $request['search'] ) ) {
        //     return new WP_Error( 'rest_no_search_term_defined', __( 'You need to define a search term to order by relevance.' ), array( 'status' => 400 ) );
        // }

        // // Ensure an include parameter is set in case the orderby is set to 'include'.
        // if ( ! empty( $request['orderby'] ) && 'include' === $request['orderby'] && empty( $request['include'] ) ) {
        //     return new WP_Error( 'rest_orderby_include_missing_include', sprintf( __( 'Missing parameter(s): %s' ), 'include' ), array( 'status' => 400 ) );
        // }

        // Retrieve the list of registered collection query parameters.
        $registered = $this->get_collection_params();
        $args = array();

        /*
         * This array defines mappings between public API query parameters whose
         * values are accepted as-passed, and their internal Charitable_Campaigns::query parameter
         * name equivalents (some are the same). Only values which are also
         * present in $registered will be set.
         */
        $parameter_mappings = array(
            'page'      => 'paged',
            'per_page'  => 'posts_per_page',
            'search'    => 's',
            'category'  => 'category',
            'tag'       => 'tag',
            'creator'   => 'author',
            'include_inactive'         => 'include_inactive',
            'status'         => 'post_status',
            'order'          => 'order',
            'orderby'        => 'orderby',
        );

        /*
         * For each known parameter which is both registered and present in the request,
         * set the parameter's value on the query $args.
         */
        foreach ( $parameter_mappings as $api_param => $wp_param ) {
            if ( isset( $registered[ $api_param ], $request[ $api_param ] ) ) {
                $args[ $wp_param ] = $request[ $api_param ];
            }
        }

        /* Set category constraint */
        if ( ! empty( $args['category'] ) ) {

            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'campaign_category',
                    'field'    => 'slug',
                    'terms'    => $args['category'],
                ),
            );
        }

        if(isset($args['category'])){
            unset($args['category']);
        }

        /* Set tag constraint */
        if ( ! empty( $args['tag'] ) ) {

            if ( ! array_key_exists( 'tax_query', $args ) ) {
                $args['tax_query'] = array();
            }

            $args['tax_query'][] = array(
                'taxonomy' => 'campaign_tag',
                'field'    => 'slug',
                'terms'    => $args['tag'],
            );
        }

        if(isset($args['tag'])){
            unset($args['tag']);
        }

        /* Only include active campaigns if flag is set */
        if ( ! $args['include_inactive'] ) {
            $args['meta_query'] = array(
                'relation' => 'OR',
                array(
                    'key'       => '_campaign_end_date',
                    'value'     => date( 'Y-m-d H:i:s' ),
                    'compare'   => '>=',
                    'type'      => 'datetime',
                ),
                array(
                    'key'       => '_campaign_end_date',
                    'value'     => 0,
                    'compare'   => '=',
                ),
            );
        }

        if(isset($args['include_inactive'])){
            unset($args['include_inactive']);
        }

        if ( ! empty( $args['order'] ) && in_array( strtoupper( $args['order'] ), array( 'DESC', 'ASC' ), true ) ) {
            $args['order'] = strtoupper($args['order']);
        }

        /* Return campaigns, ordered by how much money has been raised. */
        if ( 'popular' === $args['orderby'] ) {
            return Charitable_Campaigns::ordered_by_amount( $args );
        }

        /* Return campaigns, ordered by how soon they are ending. */
        if ( 'ending' === $args['orderby'] ) {
            return Charitable_Campaigns::ordered_by_ending_soon( $args );
        }

        /* Return campaigns, ordered by date of creation. */
        if ( 'post_date' === $args['orderby'] ) {
            $args['orderby'] = 'date';

            if ( ! isset( $args['order'] ) ) {
                $args['order'] = 'DESC';
            }
        } else {
            $args['orderby'] = $args['orderby'];
        }

        $results = Charitable_Campaigns::query( $args );

        $campaigns = array();

        foreach ( $results->posts as $post ) {

            $data    = $this->prepare_item_for_response( $post, $request );
            $campaigns[] = $this->prepare_response_for_collection( $data );
        }

        $page = (int) $args['paged'];
        $total_posts = $results->found_posts;

        if ( $total_posts < 1 ) {
            // Out-of-bounds, run the query again without LIMIT for total count.
            unset( $args['paged'] );

            $r = Charitable_Campaigns::query( $args );
            $total_posts = $r->found_posts;
        }

        $max_pages = ceil( $total_posts / (int) $results->query_vars['posts_per_page'] );
        $response  = rest_ensure_response( $campaigns );

        $response->header( 'X-WP-Total', (int) $total_posts );
        $response->header( 'X-WP-TotalPages', (int) $max_pages );

        $request_params = $request->get_query_params();
        $base = add_query_arg( $request_params, rest_url( sprintf( '%s/%s', $this->namespace, $this->rest_base ) ) );

        if ( $page > 1 ) {
            $prev_page = $page - 1;

            if ( $prev_page > $max_pages ) {
                $prev_page = $max_pages;
            }

            $prev_link = add_query_arg( 'page', $prev_page, $base );
            $response->link_header( 'prev', $prev_link );
        }
        if ( $max_pages > $page ) {
            $next_page = $page + 1;
            $next_link = add_query_arg( 'page', $next_page, $base );

            $response->link_header( 'next', $next_link );
        }

        return $response;
    }


    /**
     * Checks if a given request has access to read a campaign.
     * @access public
     *
     * @param WP_REST_Request $request Full details about the request.
     * @return bool|WP_Error True if the request has read access for the item, WP_Error object otherwise.
     */
    public function get_item_permissions_check( $request ) {
        // $post = $this->get_post( $request['id'] );
        // if ( is_wp_error( $post ) ) {
        //     return $post;
        // }

        // if ( 'edit' === $request['context'] && $post && ! $this->check_update_permission( $post ) ) {
        //     return new WP_Error( 'rest_forbidden_context', __( 'Sorry, you are not allowed to edit this post.' ), array( 'status' => rest_authorization_required_code() ) );
        // }

        // if ( $post && ! empty( $request['password'] ) ) {
        //     // Check post password, and return error if invalid.
        //     if ( ! hash_equals( $post->post_password, $request['password'] ) ) {
        //         return new WP_Error( 'rest_post_incorrect_password', __( 'Incorrect post password.' ), array( 'status' => 403 ) );
        //     }
        // }

        // // Allow access to all password protected posts if the context is edit.
        // if ( 'edit' === $request['context'] ) {
        //     add_filter( 'post_password_required', '__return_false' );
        // }

        // if ( $post ) {
        //     return $this->check_read_permission( $post );
        // }

        return true;
    }

    /**
     * Retrieves a single campaign.
     * @access public
     *
     * @param WP_REST_Request $request Full details about the request.
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     */
    public function get_item( $request ) {
        $post = get_post( (int) $request['id'] );
        if ( is_wp_error( $post ) ) {
            return $post;
        }

        $data     = $this->prepare_item_for_response( $post, $request );
        $response = rest_ensure_response( $data );

        // if ( is_post_type_viewable( get_post_type_object( $post->post_type ) ) ) {
        //     $response->link_header( 'alternate',  get_permalink( $post->ID ), array( 'type' => 'text/html' ) );
        // }

        return $response;
    }

    /**
     * Prepares a single campaign output for response.
     * @access public
     *
     * @param Charitable_Campaign         $campaign    Campaign object.
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response Response object.
     */
    public function prepare_item_for_response( $post, $request ) {

        $campaign = new Charitable_Campaign( $post ); 

        // Base fields for every campaign.
        $data = array(
            'id' => $campaign->ID,
            'title' => $campaign->post_title,
            'description' => $campaign->get('description'),
            // 'post_content' => $campaign->post_content,
            'goal' => $this->prepare_amount_response( $campaign->get_goal() ),
            'suggested_donations' => (!empty($campaign->get('suggested_donations'))) ? $campaign->get('suggested_donations') : array(),
            'status' => $campaign->get_status(),
            'date' => array(
                'start_date' => $this->prepare_date_response( $campaign->post_date_gmt, $campaign->post_date ),
                'end_date' => $this->prepare_date_response( date( 'Y-m-d H:i:s', $campaign->get_end_time() - ( get_option( 'gmt_offset' ) * 3600 ) ),  date( 'Y-m-d H:i:s', $campaign->get_end_time() ) ),
                // 'seconds_left' => $campaign->get_seconds_left(),
            ),
            'donation' => array(
                'amount_donated' => $this->prepare_amount_response( $campaign->get_donated_amount() ),
                'percent_donated' => $campaign->get_percent_donated_raw(),
            )
        );

        $context = ! empty( $request['context'] ) ? $request['context'] : 'view';
        $data    = $this->add_additional_fields_to_object( $data, $request );
        $data    = $this->filter_response_by_context( $data, $context );

        // Wrap the data in a response object.
        $response = rest_ensure_response( $data );

        $response->add_links( $this->prepare_links( $campaign ) );

        /**
         * Filters the campaign data for a response.
         *
         * @param WP_REST_Response $response The response object.
         * @param Charitable_Campaign          $campaign     Campaign object.
         * @param WP_REST_Request  $request  Request object.
         */
        return apply_filters( "pp_rest_prepare_campaign", $response, $campaign, $request );
    }

    /**
     * Prepares links for the request.
     * @access protected
     *
     * @param Charitable_Campaign $campaign Campaign object.
     * @return array Links for the given campaign.
     */
    protected function prepare_links( $campaign ) {
        $base = sprintf( '%s/%s', $this->namespace, $this->rest_base );

        // Entity meta.
        $links = array(
            'self' => array(
                'href'   => rest_url( trailingslashit( $base ) . $campaign->ID ),
            ),
            'collection' => array(
                'href'   => rest_url( $base ),
            ),
        );

        if ( ! empty( $campaign->get_campaign_creator() ) ) {
            $links['author'] = array(
                'href'       => rest_url( 'philanthropy/users/' . $campaign->get_campaign_creator() ),
                'embeddable' => true,
            );
        }

        return $links;
    }

    /**
     * Checks the post_date_gmt or modified_gmt and prepare any post or
     * modified date for single post output.
     * @access protected
     *
     * @param string      $date_gmt GMT publication time.
     * @param string|null $date     Optional. Local publication time. Default null.
     * @return string|null ISO8601/RFC3339 formatted datetime.
     */
    protected function prepare_date_response( $date_gmt, $date = null ) {
        // Use the date if passed.
        if ( isset( $date ) ) {
            return mysql_to_rfc3339( $date );
        }

        // Return null if $date_gmt is empty/zeros.
        if ( '0000-00-00 00:00:00' === $date_gmt ) {
            return null;
        }

        // Return the formatted datetime.
        return mysql_to_rfc3339( $date_gmt );
    }

    protected function prepare_amount_response($amount, $decimal_count = false, $with_symbol = false){

        $currency_helper = charitable_get_currency_helper();

        if ( false === $decimal_count ) {
            $decimal_count = charitable_get_option( 'decimal_count', 2 );
        }

        $amount = $currency_helper->sanitize_monetary_amount( strval( $amount ) );

        $amount = number_format(
            $amount,
            $decimal_count,
            charitable_get_option( 'decimal_separator', '.' ),
            charitable_get_option( 'thousands_separator', ',' )
        );

        return ($with_symbol) ? sprintf( $this->get_currency_format(), $this->get_currency_symbol(), $amount ) : $amount;
    }

    /**
     * Retrieves the query params for the campaigns collection.
     * @access public
     *
     * @return array Collection parameters.
     */
    public function get_collection_params() {
        $query_params = parent::get_collection_params();

        $query_params['context']['default'] = 'view';

        $query_params['search'] = array(
            'description'   => __( 'Limit result set to specific title.' ),
            'type'          => 'string',
        );

        $query_params['category'] = array(
            'description'   => __( 'Limit result set to specific category.' ),
            'type'          => 'string',
        );

        $query_params['tag'] = array(
            'description'   => __( 'Limit result set to specific tag.' ),
            'type'          => 'string',
        );

        $query_params['creator'] = array(
            'description'   => __( 'Limit result set to specific creator.' ),
            'type'          => 'integer',
        );

        $query_params['include_inactive'] = array(
            'description'   => __( 'include inactive campaign.' ),
            'type'          => 'boolean',
        );

        $query_params['status'] = array(
            'description'   => __( 'Limit by post status.' ),
            'type'          => 'string',
        );

        $query_params['order_by'] = array(
            'description'   => __( 'Order result by.' ),
            'type'          => 'string',
        );

        $query_params['order'] = array(
            'description'   => __( 'Order.' ),
            'type'          => 'string',
            'default'       => 'DESC',
        );

        /**
         * Filter collection parameters for the posts controller.
         */
        return apply_filters( "pp_rest_campaign_collection_params", $query_params );
    }
}
