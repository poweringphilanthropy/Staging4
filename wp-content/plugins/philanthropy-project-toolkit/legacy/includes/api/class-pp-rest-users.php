<?php
/**
 * REST API: PP_Rest_Users class
 *
 * @package PP_Toolkit
 * @subpackage REST_API
 * @since 1.0
 */

/**
 * Core class used to manage users via the REST API.
 *
 * @since 1.0
 *
 * @see WP_REST_Controller
 */
class PP_Rest_Users extends WP_REST_Controller {

	/**
	 * Instance of a user meta fields object.
	 */
	protected $meta;

	/**
	 * Constructor.
	 * @access public
	 */
	public function __construct() {

        $this->namespace = 'philanthropy';
        $this->rest_base = 'users';

		$this->meta = new WP_REST_User_Meta_Fields();
	}

	/**
	 * Registers the routes for the objects of the controller.
	 * @access public
	 *
	 * @see register_rest_route()
	 */
	public function register_routes() {

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/me', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_current_item' ),
				'args'                => array(
					'context' => $this->get_context_param( array( 'default' => 'view' ) ),
				),
			),
			'schema' => array( $this, 'get_public_item_schema' ),
		));



		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', array(
			'args' => array(
				'id' => array(
					'description' => __( 'Unique identifier for the user.' ),
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

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/register', array(
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create_item' ),
				'permission_callback' => array( $this, 'create_item_permissions_check' ),
				'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
			),
			'schema' => array( $this, 'get_public_item_schema' ),
		) );
	}

	/**
	 * Retrieves the current user.
	 * @access public
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_current_item( $request ) {
		$current_user_id = get_current_user_id();

		if ( empty( $current_user_id ) ) {
			return new WP_Error( 'rest_not_logged_in', __( 'You are not currently logged in.' ), array( 'status' => 401 ) );
		}

		$user     = wp_get_current_user();
		$response = $this->prepare_item_for_response( $user, $request );
		$response = rest_ensure_response( $response );


		return $response;
	}

	/**
	 * Checks if a given request has access to read a user.
	 * @access public
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return true|WP_Error True if the request has read access for the item, otherwise WP_Error object.
	 */
	public function get_item_permissions_check( $request ) {
		$user = $this->get_user( $request['id'] );
		if ( is_wp_error( $user ) ) {
			return $user;
		}

		if ( get_current_user_id() === $user->ID ) {
			return true;
		}

		if ( 'edit' === $request['context'] && ! current_user_can( 'list_users' ) ) {
			return new WP_Error( 'rest_user_cannot_view', __( 'Sorry, you are not allowed to list users.' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * Retrieves a single user.
	 *
	 * @since 4.7.0
	 * @access public
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_item( $request ) {
		$user = $this->get_user( $request['id'] );
		if ( is_wp_error( $user ) ) {
			return $user;
		}

		$user = $this->prepare_item_for_response( $user, $request );
		$response = rest_ensure_response( $user );

		return $response;
	}

	/**
	 * Get the user, if the ID is valid.
	 *
	 * @param int $id Supplied ID.
	 * @return WP_User|WP_Error True if ID is valid, WP_Error otherwise.
	 */
	protected function get_user( $id ) {
		$error = new WP_Error( 'rest_user_invalid_id', __( 'Invalid user ID.' ), array( 'status' => 404 ) );
		if ( (int) $id <= 0 ) {
			return $error;
		}

		$user = get_userdata( (int) $id );
		if ( empty( $user ) || ! $user->exists() ) {
			return $error;
		}

		return $user;
	}

	/**
	 * Checks if a given request has access create users.
	 * @access public
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return true|WP_Error True if the request has access to create items, WP_Error object otherwise.
	 */
	public function create_item_permissions_check( $request ) {

		// if ( ! current_user_can( 'create_users' ) ) {
		// 	return new WP_Error( 'rest_cannot_create_user', __( 'Sorry, you are not allowed to create new users.' ), array( 'status' => rest_authorization_required_code() ) );
		// }

		return true;
	}

	/**
	 * Creates a single user.
	 * @access public
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function create_item( $request ) {
		if ( ! empty( $request['id'] ) ) {
			return new WP_Error( 'rest_user_exists', __( 'Cannot create existing user.' ), array( 'status' => 400 ) );
		}

		$schema = $this->get_item_schema();

		$user = $this->prepare_item_for_database( $request );

		$user_id = wp_insert_user( wp_slash( (array) $user ) );

		// fires charitable hook
		do_action( 'charitable_after_save_user', $user_id, wp_slash( (array) $user ) );

		/**
		 * User Charitable_User create user instead of wp_insert_user
		 * @var Charitable_User
		 */
		// $charitable_user    = new Charitable_User();
		// $user_id = $charitable_user->update_profile( wp_slash( (array) $user ) );

		// if($user_id === false){
		// 	$charitable_notices = charitable_get_notices()->get_notices();
		// 	$errors = (isset($charitable_notices['error'])) ? current($charitable_notices['error']) : '';
		// 	return new WP_Error( 'couldnt_create_user', $errors , array( 'status' => 400 ) );
		// }

		if ( is_wp_error( $user_id ) ) {
			return $user_id;
		}

		$user = get_user_by( 'id', $user_id );

		/**
		 * Fires immediately after a user is created or updated via the REST API.
		 *
		 * @param WP_User         $user     Inserted or updated user object.
		 * @param WP_REST_Request $request  Request object.
		 * @param bool            $creating True when creating a user, false when updating.
		 */
		do_action( 'rest_insert_user', $user, $request, true );

		if ( ! empty( $request['roles'] ) && ! empty( $schema['properties']['roles'] ) ) {
			array_map( array( $user, 'add_role' ), $request['roles'] );
		}

		if ( ! empty( $schema['properties']['meta'] ) && isset( $request['meta'] ) ) {
			$meta_update = $this->meta->update_value( $request['meta'], $user_id );

			if ( is_wp_error( $meta_update ) ) {
				return $meta_update;
			}
		}

		$fields_update = $this->update_additional_fields_for_object( $user, $request );

		if ( is_wp_error( $fields_update ) ) {
			return $fields_update;
		}

		$request->set_param( 'context', 'edit' );

		$response = $this->prepare_item_for_response( $user, $request );
		$response = rest_ensure_response( $response );

		$response->set_status( 201 );
		$response->header( 'Location', rest_url( sprintf( '%s/%s/%d', $this->namespace, $this->rest_base, $user_id ) ) );

		return $response;
	}

	/**
	 * Prepares a single user output for response.
	 * @access public
	 *
	 * @param WP_User         $user    User object.
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response object.
	 */
	public function prepare_item_for_response( $user, $request ) {

		$data   = array();
		$schema = $this->get_item_schema();

		if ( ! empty( $schema['properties']['id'] ) ) {
			$data['id'] = $user->ID;
		}

		if ( ! empty( $schema['properties']['username'] ) ) {
			$data['username'] = $user->user_login;
		}

		if ( ! empty( $schema['properties']['name'] ) ) {
			$data['name'] = $user->display_name;
		}

		if ( ! empty( $schema['properties']['first_name'] ) ) {
			$data['first_name'] = $user->first_name;
		}

		if ( ! empty( $schema['properties']['last_name'] ) ) {
			$data['last_name'] = $user->last_name;
		}

		if ( ! empty( $schema['properties']['email'] ) ) {
			$data['email'] = $user->user_email;
		}

		if ( ! empty( $schema['properties']['url'] ) ) {
			$data['url'] = $user->user_url;
		}

		if ( ! empty( $schema['properties']['description'] ) ) {
			$data['description'] = $user->description;
		}

		if ( ! empty( $schema['properties']['link'] ) ) {
			$data['link'] = get_author_posts_url( $user->ID, $user->user_nicename );
		}

		if ( ! empty( $schema['properties']['locale'] ) ) {
			$data['locale'] = get_user_locale( $user );
		}

		if ( ! empty( $schema['properties']['nickname'] ) ) {
			$data['nickname'] = $user->nickname;
		}

		if ( ! empty( $schema['properties']['slug'] ) ) {
			$data['slug'] = $user->user_nicename;
		}

		if ( ! empty( $schema['properties']['roles'] ) ) {
			// Defensively call array_values() to ensure an array is returned.
			$data['roles'] = array_values( $user->roles );
		}

		if ( ! empty( $schema['properties']['registered_date'] ) ) {
			$data['registered_date'] = date( 'c', strtotime( $user->user_registered ) );
		}

		if ( ! empty( $schema['properties']['capabilities'] ) ) {
			$data['capabilities'] = (object) $user->allcaps;
		}

		if ( ! empty( $schema['properties']['extra_capabilities'] ) ) {
			$data['extra_capabilities'] = (object) $user->caps;
		}

		if ( ! empty( $schema['properties']['avatar_urls'] ) ) {
			$data['avatar_urls'] = rest_get_avatar_urls( $user->user_email );
		}

		if ( ! empty( $schema['properties']['meta'] ) ) {
			$data['meta'] = $this->meta->get_value( $user->ID, $request );
		}

		$context = ! empty( $request['context'] ) ? $request['context'] : 'embed';

		$data = $this->add_additional_fields_to_object( $data, $request );
		$data = $this->filter_response_by_context( $data, $context );

		// Wrap the data in a response object.
		$response = rest_ensure_response( $data );

		$response->add_links( $this->prepare_links( $user ) );

		/**
		 * Filters user data returned from the REST API.
		 *
		 * @since 4.7.0
		 *
		 * @param WP_REST_Response $response The response object.
		 * @param object           $user     User object used to create response.
		 * @param WP_REST_Request  $request  Request object.
		 */
		return apply_filters( 'rest_prepare_user', $response, $user, $request );
	}

	/**
	 * Prepares links for the user request.
	 * @access protected
	 *
	 * @param WP_Post $user User object.
	 * @return array Links for the given user.
	 */
	protected function prepare_links( $user ) {
		$links = array(
			'self' => array(
				'href' => rest_url( sprintf( '%s/%s/%d', $this->namespace, $this->rest_base, $user->ID ) ),
			),
			'collection' => array(
				'href' => rest_url( sprintf( '%s/%s', $this->namespace, $this->rest_base ) ),
			),
		);

		return $links;
	}

	/**
	 * Prepares a single user for creation or update.
	 * @access protected
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return object $prepared_user User object.
	 */
	protected function prepare_item_for_database( $request ) {
		$prepared_user = new stdClass;

		$schema = $this->get_item_schema();

		// required arguments.
		if ( isset( $request['email'] ) && ! empty( $schema['properties']['email'] ) ) {
			$prepared_user->user_email = $request['email'];
		}

		if ( isset( $request['username'] ) && ! empty( $schema['properties']['username'] ) ) {
			$prepared_user->user_login = $request['username'];
		}

		if ( isset( $request['password'] ) && ! empty( $schema['properties']['password'] ) ) {
			$prepared_user->user_pass = $request['password'];
		}

		// optional arguments.
		if ( isset( $request['id'] ) ) {
			$prepared_user->ID = absint( $request['id'] );
		}

		if ( isset( $request['name'] ) && ! empty( $schema['properties']['name'] ) ) {
			$prepared_user->display_name = $request['name'];
		}

		if ( isset( $request['first_name'] ) && ! empty( $schema['properties']['first_name'] ) ) {
			$prepared_user->first_name = $request['first_name'];
		}

		if ( isset( $request['last_name'] ) && ! empty( $schema['properties']['last_name'] ) ) {
			$prepared_user->last_name = $request['last_name'];
		}

		if ( isset( $request['nickname'] ) && ! empty( $schema['properties']['nickname'] ) ) {
			$prepared_user->nickname = $request['nickname'];
		}

		if ( isset( $request['slug'] ) && ! empty( $schema['properties']['slug'] ) ) {
			$prepared_user->user_nicename = $request['slug'];
		}

		if ( isset( $request['description'] ) && ! empty( $schema['properties']['description'] ) ) {
			$prepared_user->description = $request['description'];
		}

		if ( isset( $request['url'] ) && ! empty( $schema['properties']['url'] ) ) {
			$prepared_user->user_url = $request['url'];
		}

		if ( isset( $request['locale'] ) && ! empty( $schema['properties']['locale'] ) ) {
			$prepared_user->locale = $request['locale'];
		}

		// setting roles will be handled outside of this function.
		if ( isset( $request['roles'] ) ) {
			$prepared_user->role = false;
		}

		/**
		 * Filters user data before insertion via the REST API.
		 *
		 * @param object          $prepared_user User object.
		 * @param WP_REST_Request $request       Request object.
		 */
		return apply_filters( 'rest_pre_insert_user', $prepared_user, $request );
	}

	/**
	 * Check a username for the REST API.
	 *
	 * Performs a couple of checks like edit_user() in wp-admin/includes/user.php.
	 *
	 * @param  mixed            $value   The username submitted in the request.
	 * @param  WP_REST_Request  $request Full details about the request.
	 * @param  string           $param   The parameter name.
	 * @return WP_Error|string The sanitized username, if valid, otherwise an error.
	 */
	public function check_username( $value, $request, $param ) {
		$username = (string) $value;

		if ( ! validate_username( $username ) ) {
			return new WP_Error( 'rest_user_invalid_username', __( 'Username contains invalid characters.' ), array( 'status' => 400 ) );
		}

		/** This filter is documented in wp-includes/user.php */
		$illegal_logins = (array) apply_filters( 'illegal_user_logins', array() );

		if ( in_array( strtolower( $username ), array_map( 'strtolower', $illegal_logins ) ) ) {
			return new WP_Error( 'rest_user_invalid_username', __( 'Sorry, that username is not allowed.' ), array( 'status' => 400 ) );
		}

		return $username;
	}

	/**
	 * Check a user password for the REST API.
	 *
	 * Performs a couple of checks like edit_user() in wp-admin/includes/user.php.
	 *
	 * @param  mixed            $value   The password submitted in the request.
	 * @param  WP_REST_Request  $request Full details about the request.
	 * @param  string           $param   The parameter name.
	 * @return WP_Error|string The sanitized password, if valid, otherwise an error.
	 */
	public function check_user_password( $value, $request, $param ) {
		$password = (string) $value;

		if ( empty( $password ) ) {
			return new WP_Error( 'rest_user_invalid_password', __( 'Passwords cannot be empty.' ), array( 'status' => 400 ) );
		}

		if ( false !== strpos( $password, "\\" ) ) {
			return new WP_Error( 'rest_user_invalid_password', __( 'Passwords cannot contain the "\\" character.' ), array( 'status' => 400 ) );
		}

		return $password;
	}

	/**
	 * Retrieves the user's schema, conforming to JSON Schema.
	 * @access public
	 *
	 * @return array Item schema data.
	 */
	public function get_item_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/schema#',
			'title'      => 'user',
			'type'       => 'object',
			'properties' => array(
				'id'          => array(
					'description' => __( 'Unique identifier for the user.' ),
					'type'        => 'integer',
					'context'     => array( 'embed', 'view', 'edit' ),
					'readonly'    => true,
				),
				'username'    => array(
					'description' => __( 'Login name for the user.' ),
					'type'        => 'string',
					'context'     => array( 'edit' ),
					'required'    => true,
					'arg_options' => array(
						'sanitize_callback' => array( $this, 'check_username' ),
					),
				),
				'name'        => array(
					'description' => __( 'Display name for the user.' ),
					'type'        => 'string',
					'context'     => array( 'embed', 'view', 'edit' ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
				'first_name'  => array(
					'description' => __( 'First name for the user.' ),
					'type'        => 'string',
					'context'     => array( 'edit' ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
				'last_name'   => array(
					'description' => __( 'Last name for the user.' ),
					'type'        => 'string',
					'context'     => array( 'edit' ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
				'email'       => array(
					'description' => __( 'The email address for the user.' ),
					'type'        => 'string',
					'format'      => 'email',
					'context'     => array( 'edit' ),
					'required'    => true,
				),
				'url'         => array(
					'description' => __( 'URL of the user.' ),
					'type'        => 'string',
					'format'      => 'uri',
					'context'     => array( 'embed', 'view', 'edit' ),
				),
				'description' => array(
					'description' => __( 'Description of the user.' ),
					'type'        => 'string',
					'context'     => array( 'embed', 'view', 'edit' ),
				),
				'link'        => array(
					'description' => __( 'Author URL of the user.' ),
					'type'        => 'string',
					'format'      => 'uri',
					'context'     => array( 'embed', 'view', 'edit' ),
					'readonly'    => true,
				),
				'locale'    => array(
					'description' => __( 'Locale for the user.' ),
					'type'        => 'string',
					'enum'        => array_merge( array( '', 'en_US' ), get_available_languages() ),
					'context'     => array( 'edit' ),
				),
				'nickname'    => array(
					'description' => __( 'The nickname for the user.' ),
					'type'        => 'string',
					'context'     => array( 'edit' ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
				'slug'        => array(
					'description' => __( 'An alphanumeric identifier for the user.' ),
					'type'        => 'string',
					'context'     => array( 'embed', 'view', 'edit' ),
					'arg_options' => array(
						'sanitize_callback' => array( $this, 'sanitize_slug' ),
					),
				),
				'registered_date' => array(
					'description' => __( 'Registration date for the user.' ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'edit' ),
					'readonly'    => true,
				),
				'roles'           => array(
					'description' => __( 'Roles assigned to the user.' ),
					'type'        => 'array',
					'items'       => array(
						'type'    => 'string',
					),
					'context'     => array( 'edit' ),
				),
				'password'        => array(
					'description' => __( 'Password for the user (never included).' ),
					'type'        => 'string',
					'context'     => array(), // Password is never displayed.
					'required'    => true,
					'arg_options' => array(
						'sanitize_callback' => array( $this, 'check_user_password' ),
					),
				),
				'capabilities'    => array(
					'description' => __( 'All capabilities assigned to the user.' ),
					'type'        => 'object',
					'context'     => array( 'edit' ),
					'readonly'    => true,
				),
				'extra_capabilities' => array(
					'description' => __( 'Any extra capabilities assigned to the user.' ),
					'type'        => 'object',
					'context'     => array( 'edit' ),
					'readonly'    => true,
				),
			),
		);

		if ( get_option( 'show_avatars' ) ) {
			$avatar_properties = array();

			$avatar_sizes = rest_get_avatar_sizes();

			foreach ( $avatar_sizes as $size ) {
				$avatar_properties[ $size ] = array(
					/* translators: %d: avatar image size in pixels */
					'description' => sprintf( __( 'Avatar URL with image size of %d pixels.' ), $size ),
					'type'        => 'string',
					'format'      => 'uri',
					'context'     => array( 'embed', 'view', 'edit' ),
				);
			}

			$schema['properties']['avatar_urls']  = array(
				'description' => __( 'Avatar URLs for the user.' ),
				'type'        => 'object',
				'context'     => array( 'embed', 'view', 'edit' ),
				'readonly'    => true,
				'properties'  => $avatar_properties,
			);
		}

		$schema['properties']['meta'] = $this->meta->get_field_schema();

		return $this->add_additional_fields_schema( $schema );
	}

	/**
	 * Retrieves the query params for collections.
	 * @access public
	 *
	 * @return array Collection parameters.
	 */
	public function get_collection_params() {
		$query_params = parent::get_collection_params();

		$query_params['context']['default'] = 'view';

		$query_params['exclude'] = array(
			'description'        => __( 'Ensure result set excludes specific IDs.' ),
			'type'               => 'array',
			'items'              => array(
				'type'           => 'integer',
			),
			'default'            => array(),
		);

		$query_params['include'] = array(
			'description'        => __( 'Limit result set to specific IDs.' ),
			'type'               => 'array',
			'items'              => array(
				'type'           => 'integer',
			),
			'default'            => array(),
		);

		$query_params['offset'] = array(
			'description'        => __( 'Offset the result set by a specific number of items.' ),
			'type'               => 'integer',
		);

		$query_params['order'] = array(
			'default'            => 'asc',
			'description'        => __( 'Order sort attribute ascending or descending.' ),
			'enum'               => array( 'asc', 'desc' ),
			'type'               => 'string',
		);

		$query_params['orderby'] = array(
			'default'            => 'name',
			'description'        => __( 'Sort collection by object attribute.' ),
			'enum'               => array(
				'id',
				'include',
				'name',
				'registered_date',
				'slug',
				'email',
				'url',
			),
			'type'               => 'string',
		);

		$query_params['slug']    = array(
			'description'        => __( 'Limit result set to users with a specific slug.' ),
			'type'               => 'array',
			'items'              => array(
				'type'               => 'string',
			),
		);

		$query_params['roles']   = array(
			'description'        => __( 'Limit result set to users matching at least one specific role provided. Accepts csv list or single role.' ),
			'type'               => 'array',
			'items'              => array(
				'type'           => 'string',
			),
		);

		/**
		 * Filter collection parameters for the users controller.
		 *
		 * This filter registers the collection parameter, but does not map the
		 * collection parameter to an internal WP_User_Query parameter.  Use the
		 * `rest_user_query` filter to set WP_User_Query arguments.
		 *
		 * @param array $query_params JSON Schema-formatted collection parameters.
		 */
		return apply_filters( 'rest_user_collection_params', $query_params );
	}
}
