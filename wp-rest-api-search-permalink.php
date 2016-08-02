<?php
/**
 * Plugin Name: WP REST API Search Permalink
 * Plugin URI:  https://github.com/edouardkombo/wp-rest-api-search-permalink
 * Description: Extends Wp Rest APi and allows you to search for pages and posts by link.
 *
 * Version:     1.3.0
 *
 * Author:      Edouard Kombo
 * Author URI:  https://github.com/edouardkombo
 *
 * Text Domain: wp-rest-api-search-permalink
 *
 * @package WP_Rest_Api_Search_Permalink
 */

/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// WP API v2.
include_once 'includes/wp-rest-api-search-permalink-v2.php';

if ( ! function_exists ( 'wp_rest_api_search_permalink_init' ) ) :

	/**
	 * Init JSON REST API search_permalink routes.
	 *
	 * @since 1.0.0
	 */
    function wraa_wp_rest_api_search_permalink_init() {

        if ( ! defined( 'JSON_API_VERSION' ) &&
             ! in_array( 'json-rest-api/plugin.php', get_option( 'active_plugins' ) ) ) {
			     $class = new WP_REST_Api_Search_Permalink();
			     add_filter( 'rest_api_init', array( $class, 'register_routes' ) );
        }
    }

	add_action( 'init', 'wraa_wp_rest_api_search_permalink_init' );

endif;
