<?php
/**
 * WP REST API Search Permalink routes
 *
 * @package WP_API_Search_Permalink
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'WP_REST_Api_Search_Permalink' ) ) :


    /**
     * WP REST Api Search Permalink class.
     *
     * WP REST API Search Permalink support for WP API v2.
     *
     * @package WP_API_Search_Permalink
     * @since 1.2.0
     */
    class WP_REST_Api_Search_Permalink extends WP_REST_Controller {


        protected      $post_type;

	    /**
	     * Get WP API namespace.
	     *
	     * @since 1.2.0
	     * @return string
	     */
        public static function get_api_namespace() {
            return 'wp/v2';
        }


	    /**
	     * Get WP API Search_Permalink namespace.
	     *
	     * @since 1.2.1
	     * @return string
	     */
	    public static function get_plugin_namespace() {
		    return 'searchlink/v2';
	    }


        /**
         * Register routes for WP API v2.
         *
         * Get post or page by requesting link
         *
         * @since  1.2.0
         * @return array
         */
        public function register_routes() {

            register_rest_route( self::get_plugin_namespace(), '/(?P<pattern>(.*?))', array(
                array(
                    'methods'  => WP_REST_Server::READABLE,
                    'callback' => array( $this, 'get_post_or_page' ),
                    'args'     => array(
                        'context' => array(
                            'default' => 'view',
                        ),
                    ),
                )
            ) );
        }


        public function get_post_type_base ( $post_type )
        {

            if ( ! is_object ( $post_type ) ) {
                $post_type = get_post_type_object ( $post_type );
            }

            $base = ! empty( $post_type->rest_base ) ? $post_type->rest_base : $post_type->name;

            return $base;
        }


        protected function prepare_excerpt_response ( $excerpt )
        {

            if ( post_password_required () ) {
                return __ ( 'There is no excerpt because this is a protected post.' );
            }

            /** This filter is documented in wp-includes/post-template.php */
            $excerpt = apply_filters ( 'the_excerpt', apply_filters ( 'get_the_excerpt', $excerpt ) );

            if ( empty( $excerpt ) ) {
                return '';
            }

            return $excerpt;
        }

        public function get_item_schema ()
        {

            $base   = $this->get_post_type_base ( $this->post_type );
            $schema = array (
                '$schema'    => 'http://json-schema.org/draft-04/schema#',
                'title'      => $this->post_type,
                'type'       => 'object',
                /*
                 * Base properties for every Post.
                 */
                'properties' => array (
                    'date'         => array (
                        'description' => "The date the object was published, in the site's timezone.",
                        'type'        => 'string',
                        'format'      => 'date-time',
                        'context'     => array ( 'view', 'edit', 'embed' ),
                    ),
                    'date_gmt'     => array (
                        'description' => 'The date the object was published, as GMT.',
                        'type'        => 'string',
                        'format'      => 'date-time',
                        'context'     => array ( 'view', 'edit' ),
                    ),
                    'guid'         => array (
                        'description' => 'The globally unique identifier for the object.',
                        'type'        => 'object',
                        'context'     => array ( 'view', 'edit' ),
                        'readonly'    => true,
                        'properties'  => array (
                            'raw'      => array (
                                'description' => 'GUID for the object, as it exists in the database.',
                                'type'        => 'string',
                                'context'     => array ( 'edit' ),
                            ),
                            'rendered' => array (
                                'description' => 'GUID for the object, transformed for display.',
                                'type'        => 'string',
                                'context'     => array ( 'view', 'edit' ),
                            ),
                        ),
                    ),
                    'id'           => array (
                        'description' => 'Unique identifier for the object.',
                        'type'        => 'integer',
                        'context'     => array ( 'view', 'edit', 'embed' ),
                        'readonly'    => true,
                    ),
                    'link'         => array (
                        'description' => 'URL to the object.',
                        'type'        => 'string',
                        'format'      => 'uri',
                        'context'     => array ( 'view', 'edit', 'embed' ),
                        'readonly'    => true,
                    ),
                    'modified'     => array (
                        'description' => "The date the object was last modified, in the site's timezone.",
                        'type'        => 'string',
                        'format'      => 'date-time',
                        'context'     => array ( 'view', 'edit' ),
                    ),
                    'modified_gmt' => array (
                        'description' => 'The date the object was last modified, as GMT.',
                        'type'        => 'string',
                        'format'      => 'date-time',
                        'context'     => array ( 'view', 'edit' ),
                    ),
                    'password'     => array (
                        'description' => 'A password to protect access to the post.',
                        'type'        => 'string',
                        'context'     => array ( 'edit' ),
                    ),
                    'slug'         => array (
                        'description' => 'An alphanumeric identifier for the object unique to its type.',
                        'type'        => 'string',
                        'context'     => array ( 'view', 'edit', 'embed' ),
                        'arg_options' => array (
                            'sanitize_callback' => 'sanitize_title',
                        ),
                    ),
                    'status'       => array (
                        'description' => 'A named status for the object.',
                        'type'        => 'string',
                        'enum'        => array_keys ( get_post_stati ( array ( 'internal' => false ) ) ),
                        'context'     => array ( 'edit' ),
                    ),
                    'type'         => array (
                        'description' => 'Type of Post for the object.',
                        'type'        => 'string',
                        'context'     => array ( 'view', 'edit', 'embed' ),
                        'readonly'    => true,
                    ),
                ),
            );

            $post_type_obj = get_post_type_object ( $this->post_type );
            if ( $post_type_obj->hierarchical ) {
                $schema[ 'properties' ][ 'parent' ] = array (
                    'description' => 'The ID for the parent of the object.',
                    'type'        => 'integer',
                    'context'     => array ( 'view', 'edit' ),
                );
            }

            $post_type_attributes = array (
                'title',
                'editor',
                'author',
                'excerpt',
                'thumbnail',
                'comments',
                'revisions',
                'page-attributes',
                'post-formats',
            );
            $fixed_schemas        = array (
                'post'       => array (
                    'title',
                    'editor',
                    'author',
                    'excerpt',
                    'thumbnail',
                    'comments',
                    'revisions',
                    'post-formats',
                ),
                'page'       => array (
                    'title',
                    'editor',
                    'author',
                    'excerpt',
                    'thumbnail',
                    'comments',
                    'revisions',
                    'page-attributes',
                ),
                'attachment' => array (
                    'title',
                    'author',
                    'comments',
                    'revisions',
                ),
            );
            foreach ( $post_type_attributes as $attribute ) {
                if ( isset( $fixed_schemas[ $this->post_type ] ) && ! in_array ( $attribute,
                        $fixed_schemas[ $this->post_type ] )
                ) {
                    continue;
                } elseif ( ! in_array ( $this->post_type,
                        array_keys ( $fixed_schemas ) ) && ! post_type_supports ( $this->post_type, $attribute )
                ) {
                    continue;
                }

                switch ( $attribute ) {

                    case 'title':
                        $schema[ 'properties' ][ 'title' ] = array (
                            'description' => 'The title for the object.',
                            'type'        => 'object',
                            'context'     => array ( 'view', 'edit', 'embed' ),
                            'properties'  => array (
                                'raw'      => array (
                                    'description' => 'Title for the object, as it exists in the database.',
                                    'type'        => 'string',
                                    'context'     => array ( 'edit' ),
                                ),
                                'rendered' => array (
                                    'description' => 'Title for the object, transformed for display.',
                                    'type'        => 'string',
                                    'context'     => array ( 'view', 'edit', 'embed' ),
                                ),
                            ),
                        );
                        break;

                    case 'editor':
                        $schema[ 'properties' ][ 'content' ] = array (
                            'description' => 'The content for the object.',
                            'type'        => 'object',
                            'context'     => array ( 'view', 'edit' ),
                            'properties'  => array (
                                'raw'      => array (
                                    'description' => 'Content for the object, as it exists in the database.',
                                    'type'        => 'string',
                                    'context'     => array ( 'edit' ),
                                ),
                                'rendered' => array (
                                    'description' => 'Content for the object, transformed for display.',
                                    'type'        => 'string',
                                    'context'     => array ( 'view', 'edit' ),
                                ),
                            ),
                        );
                        break;

                    case 'author':
                        $schema[ 'properties' ][ 'author' ] = array (
                            'description' => 'The ID for the author of the object.',
                            'type'        => 'integer',
                            'context'     => array ( 'view', 'edit', 'embed' ),
                        );
                        break;

                    case 'excerpt':
                        $schema[ 'properties' ][ 'excerpt' ] = array (
                            'description' => 'The excerpt for the object.',
                            'type'        => 'object',
                            'context'     => array ( 'view', 'edit', 'embed' ),
                            'properties'  => array (
                                'raw'      => array (
                                    'description' => 'Excerpt for the object, as it exists in the database.',
                                    'type'        => 'string',
                                    'context'     => array ( 'edit' ),
                                ),
                                'rendered' => array (
                                    'description' => 'Excerpt for the object, transformed for display.',
                                    'type'        => 'string',
                                    'context'     => array ( 'view', 'edit', 'embed' ),
                                ),
                            ),
                        );
                        break;

                    case 'thumbnail':
                        $schema[ 'properties' ][ 'featured_image' ] = array (
                            'description' => 'ID of the featured image for the object.',
                            'type'        => 'integer',
                            'context'     => array ( 'view', 'edit' ),
                        );
                        break;

                    case 'comments':
                        $schema[ 'properties' ][ 'comment_status' ] = array (
                            'description' => 'Whether or not comments are open on the object.',
                            'type'        => 'string',
                            'enum'        => array ( 'open', 'closed' ),
                            'context'     => array ( 'view', 'edit' ),
                        );
                        $schema[ 'properties' ][ 'ping_status' ]    = array (
                            'description' => 'Whether or not the object can be pinged.',
                            'type'        => 'string',
                            'enum'        => array ( 'open', 'closed' ),
                            'context'     => array ( 'view', 'edit' ),
                        );
                        break;

                    case 'page-attributes':
                        $schema[ 'properties' ][ 'menu_order' ] = array (
                            'description' => 'The order of the object in relation to other object of its type.',
                            'type'        => 'integer',
                            'context'     => array ( 'view', 'edit' ),
                        );
                        break;

                    case 'post-formats':
                        $schema[ 'properties' ][ 'format' ] = array (
                            'description' => 'The format for the object.',
                            'type'        => 'string',
                            'enum'        => array_values ( get_post_format_slugs () ),
                            'context'     => array ( 'view', 'edit' ),
                        );
                        break;

                }
            }

            if ( 'post' === $this->post_type ) {
                $schema[ 'properties' ][ 'sticky' ] = array (
                    'description' => 'Whether or not the object should be treated as sticky.',
                    'type'        => 'boolean',
                    'context'     => array ( 'view', 'edit' ),
                );
            }

            if ( 'page' === $this->post_type ) {
                $schema[ 'properties' ][ 'template' ] = array (
                    'description' => 'The theme file to use to display the object.',
                    'type'        => 'string',
                    'enum'        => array_keys ( wp_get_theme ()->get_page_templates () ),
                    'context'     => array ( 'view', 'edit' ),
                );
            }

            return $this->add_additional_fields_schema ( $schema );
        }

        protected function prepare_date_response ( $date_gmt, $date = null )
        {

            if ( '0000-00-00 00:00:00' === $date_gmt ) {
                return null;
            }

            if ( isset( $date ) ) {
                return mysql_to_rfc3339 ( $date );
            }

            return mysql_to_rfc3339 ( $date_gmt );
        }

        public function prepare_item_for_response ( $post, $request )
        {

            $GLOBALS[ 'post' ] = $post;
            setup_postdata ( $post );

            //If fields filtering is enabled, get all the fields requested
            $allFields  = (isset($request->get_params()['fields'])) ? $request->get_params()['fields'] : null;
            $_fields    = (!is_null($allFields)) ? explode(',', $allFields) : null;

            //Inverse the array keys
            $fields     = (!is_null($_fields)) ? array_flip($_fields) : null;

            $this->post_type = $post->post_type;

            //If filtering by fields is enabled, we allow to show on the screen only specified fields
            if (!is_null($fields) && (count($fields) > 0)) {
                $data = [];

                if (isset($fields['id'])) {
                    $data['id']         = $post->ID;
                }
                if (isset($fields['date'])) {
                    $data['date']       = $this->prepare_date_response ( $post->post_date_gmt, $post->post_date );
                }
                if (isset($fields['date_gmt'])) {
                    $data['date_gmt']   = $this->prepare_date_response ( $post->post_date_gmt );
                }
                if (isset($fields['guid'])) {
                    $data['guid']       = array (
                        /** This filter is documented in wp-includes/post-template.php */
                        'rendered' => apply_filters ( 'get_the_guid', $post->guid ),
                        'raw'      => $post->guid,
                    );
                }
                if (isset($fields['modified'])) {
                    $data['modified']       = $this->prepare_date_response ( $post->post_modified_gmt, $post->post_modified );
                }
                if (isset($fields['modified_gmt'])) {
                    $data['modified_gmt']   = $this->prepare_date_response ( $post->post_modified_gmt );
                }
                if (isset($fields['password'])) {
                    $data['password']       = $post->post_password;
                }
                if (isset($fields['slug'])) {
                    $data['slug']           = $post->post_name;
                }
                if (isset($fields['status'])) {
                    $data['status']         = $post->post_status;
                }
                if (isset($fields['type'])) {
                    $data['type']           = $post->post_type;
                }
                if (isset($fields['link'])) {
                    $data['link']           = get_permalink ( $post->ID );
                }

                $schema = $this->get_item_schema();

                if ( ! empty( $schema[ 'properties' ][ 'title' ] ) && (isset($fields['title'])) ) {
                    $data[ 'title' ] = array (
                        'raw'      => $post->post_title,
                        'rendered' => get_the_title ( $post->ID ),
                    );
                }

                if ( ! empty( $schema[ 'properties' ][ 'content' ] ) && (isset($fields['content'])) ) {

                    if ( ! empty( $post->post_password ) ) {
                        $this->prepare_password_response ( $post->post_password );
                    }

                    $data[ 'content' ] = array (
                        'raw'      => $post->post_content,
                        /** This filter is documented in wp-includes/post-template.php */
                        'rendered' => apply_filters ( 'the_content', $post->post_content ),
                    );

                    // Don't leave our cookie lying around: https://github.com/WP-API/WP-API/issues/1055.
                    if ( ! empty( $post->post_password ) ) {
                        $_COOKIE[ 'wp-postpass_' . COOKIEHASH ] = '';
                    }
                }

                if ( ! empty( $schema[ 'properties' ][ 'excerpt' ] ) && (isset($fields['excerpt']))) {
                    $data[ 'excerpt' ] = array (
                        'raw'      => $post->post_excerpt,
                        'rendered' => $this->prepare_excerpt_response ( $post->post_excerpt ),
                    );
                }

                if ( ! empty( $schema[ 'properties' ][ 'author' ] ) && (isset($fields['author']))) {
                    $data[ 'author' ] = (int) $post->post_author;
                }

                if ( ! empty( $schema[ 'properties' ][ 'featured_image' ] ) && (isset($fields['featured_image']))) {
                    $data[ 'featured_image' ] = (int) get_post_thumbnail_id ( $post->ID );
                }

                if ( ! empty( $schema[ 'properties' ][ 'parent' ] ) && (isset($fields['parent']))) {
                    $data[ 'parent' ] = (int) $post->post_parent;
                }

                if ( ! empty( $schema[ 'properties' ][ 'menu_order' ] ) && (isset($fields['menu_order']))) {
                    $data[ 'menu_order' ] = (int) $post->menu_order;
                }

                if ( ! empty( $schema[ 'properties' ][ 'comment_status' ] ) && (isset($fields['comment_status']))) {
                    $data[ 'comment_status' ] = $post->comment_status;
                }

                if ( ! empty( $schema[ 'properties' ][ 'ping_status' ] ) && (isset($fields['ping_status']))) {
                    $data[ 'ping_status' ] = $post->ping_status;
                }

                if ( ! empty( $schema[ 'properties' ][ 'sticky' ] ) && (isset($fields['sticky']))) {
                    $data[ 'sticky' ] = is_sticky ( $post->ID );
                }

                if ( ! empty( $schema[ 'properties' ][ 'template' ] ) && (isset($fields['template']))) {
                    if ( $template = get_page_template_slug ( $post->ID ) ) {
                        $data[ 'template' ] = $template;
                    } else {
                        $data[ 'template' ] = '';
                    }
                }

                if ( ! empty( $schema[ 'properties' ][ 'format' ] ) && (isset($fields['format']))) {
                    $data[ 'format' ] = get_post_format ( $post->ID );
                    // Fill in blank post format.
                    if ( empty( $data[ 'format' ] ) ) {
                        $data[ 'format' ] = 'standard';
                    }
                }

            } else {

                // Base fields for every post.
                $data = array(
                    'id'         => $post->ID,
                    'date'       => $this->prepare_date_response ( $post->post_date_gmt, $post->post_date ),
                    'date_gmt'   => $this->prepare_date_response ( $post->post_date_gmt ),
                    'guid'       => array (
                        /** This filter is documented in wp-includes/post-template.php */
                        'rendered' => apply_filters ( 'get_the_guid', $post->guid ),
                        'raw'      => $post->guid,
                    ),
                    'modified'       => $this->prepare_date_response ( $post->post_modified_gmt, $post->post_modified ),
                    'modified_gmt'   => $this->prepare_date_response ( $post->post_modified_gmt ),
                    'password'       => $post->post_password,
                    'slug'           => $post->post_name,
                    'status'         => $post->post_status,
                    'type'           => $post->post_type,
                    'link'           => get_permalink ( $post->ID )
                );

                $schema = $this->get_item_schema();

                if ( ! empty( $schema[ 'properties' ][ 'title' ] ) ) {
                    $data[ 'title' ] = array (
                        'raw'      => $post->post_title,
                        'rendered' => get_the_title ( $post->ID ),
                    );
                }

                if ( ! empty( $schema[ 'properties' ][ 'content' ] ) ) {

                    if ( ! empty( $post->post_password ) ) {
                        $this->prepare_password_response ( $post->post_password );
                    }

                    $data[ 'content' ] = array (
                        'raw'      => $post->post_content,
                        /** This filter is documented in wp-includes/post-template.php */
                        'rendered' => apply_filters ( 'the_content', $post->post_content ),
                    );

                    // Don't leave our cookie lying around: https://github.com/WP-API/WP-API/issues/1055.
                    if ( ! empty( $post->post_password ) ) {
                        $_COOKIE[ 'wp-postpass_' . COOKIEHASH ] = '';
                    }
                }

                if ( ! empty( $schema[ 'properties' ][ 'excerpt' ] ) ) {
                    $data[ 'excerpt' ] = array (
                        'raw'      => $post->post_excerpt,
                        'rendered' => $this->prepare_excerpt_response ( $post->post_excerpt ),
                    );
                }

                if ( ! empty( $schema[ 'properties' ][ 'author' ] ) ) {
                    $data[ 'author' ] = (int) $post->post_author;
                }

                if ( ! empty( $schema[ 'properties' ][ 'featured_image' ] ) ) {
                    $data[ 'featured_image' ] = (int) get_post_thumbnail_id ( $post->ID );
                }

                if ( ! empty( $schema[ 'properties' ][ 'parent' ] ) ) {
                    $data[ 'parent' ] = (int) $post->post_parent;
                }

                if ( ! empty( $schema[ 'properties' ][ 'menu_order' ] ) ) {
                    $data[ 'menu_order' ] = (int) $post->menu_order;
                }

                if ( ! empty( $schema[ 'properties' ][ 'comment_status' ] ) ) {
                    $data[ 'comment_status' ] = $post->comment_status;
                }

                if ( ! empty( $schema[ 'properties' ][ 'ping_status' ] ) ) {
                    $data[ 'ping_status' ] = $post->ping_status;
                }

                if ( ! empty( $schema[ 'properties' ][ 'sticky' ] ) ) {
                    $data[ 'sticky' ] = is_sticky ( $post->ID );
                }

                if ( ! empty( $schema[ 'properties' ][ 'template' ] ) ) {
                    if ( $template = get_page_template_slug ( $post->ID ) ) {
                        $data[ 'template' ] = $template;
                    } else {
                        $data[ 'template' ] = '';
                    }
                }

                if ( ! empty( $schema[ 'properties' ][ 'format' ] ) ) {
                    $data[ 'format' ] = get_post_format ( $post->ID );
                    // Fill in blank post format.
                    if ( empty( $data[ 'format' ] ) ) {
                        $data[ 'format' ] = 'standard';
                    }
                }
            }










            $context = ! empty( $request[ 'context' ] ) ? $request[ 'context' ] : 'view';
            $data    = $this->filter_response_by_context ( $data, $context );

            global $wp_rest_additional_fields;

            $data = $this->add_additional_fields_to_object ( $data, $request );

            // Wrap the data in a response object.
            $data = rest_ensure_response ( $data );


            /**
             * Filter the post data for a response.
             *
             * The dynamic portion of the hook name, $this->post_type, refers to post_type of the post being
             * prepared for the response.
             *
             * @param array           $data    An array of post data, prepared for response.
             * @param WP_Post         $post    Post object.
             * @param WP_REST_Request $request Request object.
             */
            return apply_filters ( 'rest_prepare_' . $this->post_type, $data, $post, $request );

        }


        /**
         * Get post or page by link.
         *
         * @since  1.2.0
         * @return array All permanent Search_Permalink scripts or styles
         */
        public function get_post_or_page( $request ) {

            $params                 = $request->get_params();
            $requestedPattern       = $params['pattern'];

            $post                   = new stdClass(); //Variable to return

            //If pattern ends by a slash, remove it
            if (substr($requestedPattern, -1) === '/') {
                $requestedPattern = substr($requestedPattern, 0, -1);
            }

            //If we have multiple patterns, we build and array
            $patterns = (strpos($requestedPattern, '/')) ? explode('/', $requestedPattern) : $requestedPattern;

            //If patterns is an array, we take the last value as the database pattern
            $pattern = (is_array($patterns)) ? max($patterns) : $patterns;

            //Request in WP database for Posts or pages at the same time
            if ($pattern === "*") {
                $posts_query = new WP_Query( array(
                    'post_type' => array('any')
                ) );
            } else {
                $posts_query = new WP_Query( array(
                    'post_type' => array('any'),
                    'name' => $pattern
                ) );
            }

            //If something is found
            if ( $posts_query->have_posts() ) {

                $data = [];

                //If we're requesting all the pages, loop inside
                if (count($posts_query->posts) > 1) {

                    foreach ($posts_query->posts as $key => $post) {

                        $itemdata = $this->prepare_item_for_response ( $post, $request );
                        $data[]   = $this->prepare_response_for_collection ( $itemdata );
                    }


                } else {
                    $post   = $posts_query->posts[0];

                    $itemdata = $this->prepare_item_for_response ( $post, $request );
                    $data[]   = $this->prepare_response_for_collection ( $itemdata );
                }


                return new WP_REST_Response( $data, 200 );

            } else {
                return apply_filters( 'rest_prepare_', [] );
            }
        }
    }

endif;
