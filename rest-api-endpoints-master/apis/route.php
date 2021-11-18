<?php
//Register post type
//
if( !function_exists('pricode_movies_post_type_callback') ){
	function pricode_movies_post_type_callback() {
		$args = array(
			'public'    => true,
			'label'     => __( 'Movies', 'textdomain' ),
			'supports' => [ 'custom-fields',  'title', 'editor' ]
		);
		register_post_type( 'movie', $args );
	}
	add_action( 'init', 'pricode_movies_post_type_callback' );	
}




add_action('rest_api_init', function(){
    register_rest_route( 'pricode/v1', '/login/', array(
        'methods' => 'POST',
        'callback' => 'pricode_rest_user_login_endpoint_handler'

    ));
});

//GET routes
add_action('rest_api_init', function(){
    register_rest_route( 'pricode/v1', '/movies/', array(
        'methods' => 'GET',
        'callback' => 'pricode_api_get_callback'

    ));
});
add_action('rest_api_init', function(){
    register_rest_route( 'pricode/v1', '/movies/(?P<movie_id>\d+)', array(
        'methods' => 'GET',
        'callback' => 'pricode_api_get_callback'

    ));
});

//POST
add_action('rest_api_init', function(){
    register_rest_route( 'pricode/v1', '/movies/create/', array(
        'methods' => 'POST',
        'callback' => 'pricode_api_post_callback'

    ));
});

//PUT
add_action('rest_api_init', function(){
    register_rest_route( 'pricode/v1', '/movies/update/(?P<movie_id>\d+)', array(
        'methods' => 'PUT',
        'callback' => 'pricode_api_put_callback'

    ));
});

//DELETE
add_action('rest_api_init', function(){
    register_rest_route( 'pricode/v1', '/movies/(?P<movie_id>\d+)', array(
        'methods' => 'DELETE',
        'callback' => 'pricode_api_delete_callback'

    ));
});

/**
 * Get all movies from our WordPress Installation
 * @param  object $request WP_Request with data
 * @return obeject         WP_REST_Response
 */
function pricode_api_get_callback( $request ){

    $movie_id = $request->get_param('movie_id');
    if( empty( $movie_id ) ){
    	$posts = get_posts( [ 'post_type' => 'movie', 'post_status' => 'publish' ] );
	    if( count($posts) > 0 ){
	    	$response['status'] =  200;	
	    	$response['success'] = true;
	    	$response['data'] = $posts;
	    }else{
	    	$response['status'] =  200;
	    	$response['success'] = false;	
	    	$response['message'] = 'NO posts!';
	    }
    }else{
    	if( $movie_id > 0 ){
    		$post = get_post( $movie_id );	
    		if( !empty( $post ) ){
    			$response['status'] =  200;	
		    	$response['success'] = true;
		    	$response['data'] = $post;	
    		}else{
    			$response['status'] =  200;	
	    		$response['success'] = false;
	    		$response['message'] = 'No post found!';	
    		}
    		
    	}
    }
    
    wp_reset_postdata();
    return new WP_REST_Response( $response );
}

/**
 * Create a movie post by rest api
 * @param  object $request WP_Request with data
 * @return obeject         WP_REST_Response
 */
function pricode_api_post_callback( $request ){

	
	$post['post_title'] = sanitize_text_field( $request->get_param( 'title' ) );
	$post['post_content'] = sanitize_text_field( $request->get_param( 'content' ) );
	$post['meta_input'] = [
		'genre' => sanitize_text_field( $request->get_param( 'meta_genre' ) )
	];
	$post['post_status'] = 'publish';
	$post['post_type'] = 'movie';
	$new_post_id = wp_insert_post( $post );

	if( !is_wp_error( $new_post_id ) ){
		$response['status'] =  200;	
		$response['success'] = true;
		$response['data'] = get_post( $new_post_id ) ;	
	}else{
		$response['status'] =  200;	
	   	$response['success'] = false;
	    $response['message'] = 'No post found!';	
	}

	return new WP_REST_Response( $response );

}


/**
 * Update a movie post
 * @param  object $request WP_Request with data
 * @return obeject         WP_REST_Response
 */
function pricode_api_put_callback( $request ){
	$movie_id = $request->get_param('movie_id');
	if( $movie_id > 0 ){
		$post['ID'] = $movie_id;
		$post['post_title'] = sanitize_text_field( $request->get_param( 'title' ) );
		$post['post_content'] = sanitize_text_field( $request->get_param( 'content' ) );
		$post['meta_input'] = [
			'genre' => sanitize_text_field( $request->get_param( 'meta_genre' ) )
		];
		$post['post_status'] = 'publish';
		$post['post_type'] = 'movie';
		$new_post_id = wp_update_post( $post, true );

		if( !is_wp_error( $new_post_id ) ){
			$response['status'] =  200;	
			$response['success'] = true;
			$response['data'] = $new_post_id;	
		}else{
			$response['status'] =  200;	
		   	$response['success'] = false;
		    $response['message'] = 'No post found!';	
		}

		
	}else{
		$response['status'] =  200;	
		$response['success'] = false;
		$response['message'] = 'Movie id is no set!';	
	}
	return new WP_REST_Response( $response );
}

function pricode_api_delete_callback( $request ){
	$movie_id = $request->get_param('movie_id');
	if( $movie_id > 0 ){
		$deleted_post = wp_delete_post( $movie_id );
		if( !empty( $deleted_post ) ){
			$response['status'] =  200;	
			$response['success'] = true;
			$response['data'] = $deleted_post;	
		}else{
			$response['status'] =  200;	
		   	$response['success'] = false;
		    $response['message'] = 'No post found!';	
		}
	}else{
		$response['status'] =  200;	
		$response['success'] = false;
		$response['message'] = 'Movie id is no set!';	
	}
	return new WP_REST_Response( $response );	
}

/**
	 * User Login call back.
	 *
	 * @param WP_REST_Request $request
	 */
	function pricode_rest_user_login_endpoint_handler( WP_REST_Request $request ) {
		$response = array();
		$parameters = $request->get_params();

		$username = sanitize_text_field( $parameters['username'] );
		$password = sanitize_text_field( $parameters['password'] );

		// Error Handling.
		$error = new WP_Error();

		if ( empty( $username ) ) {
			$error->add(
				400,
				__( "Username field is required", 'rest-api-endpoints' ),
				array( 'status' => 400 )
				);

			return $error;
		}

		if ( empty( $password ) ) {
			$error->add(
				400,
				__( "Password field is required", 'rest-api-endpoints' ),
				array( 'status' => 400 )
			);

			return $error;
		}

		$user = wp_authenticate( $username, $password  );

		// If user found
		if ( ! is_wp_error( $user ) ) {
			$response['status'] = 200;
			$response['user'] = $user;
		} else {
			// If user not found
			$error->add( 406, __( 'User not found. Check credentials', 'rest-api-endpoints' ) );
			return $error;
		}

		return new WP_REST_Response( $response );
	}