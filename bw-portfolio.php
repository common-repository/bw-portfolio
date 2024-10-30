<?php
/**
* Plugin Name: BW Portfolio
* Description: The BW Portfolio plugin is powerful yet lightweight and fast. It allows you to easily add portfolio items in your WordPress Dashboard, and organize them with portfolio tags as well. Then by using a handy shortcode you can display your portfolio items just about anywhere in a nice, responsive css grid that is compatible on many different devices. Also has tag filtering and sorting of portfolio items built in.
* Version: 1.2.3
* Requires at least: 5.2
* Requires PHP: 7.0
* Author: Ben HartLenn
* Author URI: https://bountifulweb.com
* License: GPLv3
* License URI: https://www.gnu.org/licenses/gpl-3.0.html
* Text Domain: bw_portfolio
*/

/*
BW Portfolio is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation version 3 of the License, or
any later version.
 
BW Portfolio is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with BW Portfolio. If not, see https://www.gnu.org/licenses/gpl-3.0.html.
*/

/** 
 * Enqueue Scripts and Stylesheets
 */
add_action( 'wp_enqueue_scripts', function() {
    
    if( !is_admin() ) {
		wp_enqueue_style( 'bw-portfolio-style', plugins_url('/assets/css/bw-portfolio-style.css', __FILE__), ['dashicons'], filemtime( plugin_dir_path(__FILE__).'assets/css/bw-portfolio-style.css' ), 'screen' );
		wp_enqueue_script( 'bw-portfolio-script', plugins_url('/assets/js/bw-portfolio-script.js', __FILE__), ['wp-api-request'], '1.2.0', true );

		wp_localize_script( 'bw-portfolio-script', 'bwbh_portfolio_js_vars', [
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'wp_rest' ),
			'loader_gif_url' => esc_url( plugins_url( 'bw-portfolio/assets/images/loading.gif', dirname(__FILE__) ) ),
		]);
	}
	
});


// register custom rest api routes/endpoints
add_action('rest_api_init', function () {

	// custom route to view full portfolio item
    register_rest_route('bw-portfolio/v1', '/view-portfolio-item', [
        'methods' => 'POST',
        'callback' => 'bw_view_portfolio_item',
        'permission_callback' => '__return_true',
    ]);

	// custom route to filter and sort portfolio items
	register_rest_route('bw-portfolio/v1', '/filter-sort-portfolio-items', [
        'methods' => 'POST',
        'callback' => 'bw_filter_sort_portfolio_items',
        'permission_callback' => '__return_true',
    ]);

});

/**
 * handle fetch requests to load full view of portfolio item in modal via rest api
 */
function bw_view_portfolio_item( $request ) {	
	// if $request includes the portfolio item id...
    if( $request['bw_portfolio_item_id'] ) {

		$bw_portfolio_item_id = (int)$request['bw_portfolio_item_id'];
    	$bw_post = get_post($bw_portfolio_item_id, OBJECT, 'display');
		
		// if portfolio item was found...
		if($bw_post !== null) {

			// store the needed post data to send back in rest response
			$bw_response_data = [
				'title' => html_entity_decode($bw_post->post_title),
				'content' => apply_filters('the_content', $bw_post->post_content ),
			];
			
			// get and add tags to $bw_post_data
			if( $bw_show_tags !== false ) {
				$bw_portfolio_tags = get_the_terms( $bw_post->ID, 'portfolio_tag' );
				if( !is_wp_error($bw_portfolio_tags) && $bw_portfolio_tags !== false ) {
					foreach( $bw_portfolio_tags as $bw_tag ) {
						$bw_response_data['bw_portfolio_tags'][$bw_tag->term_id] = $bw_tag->name;
					}
				}
			}

			// add featured image url to array
			$post_thumbnail_id = get_post_thumbnail_id( $bw_post->ID );
			if(!empty($post_thumbnail_id)) {
				$feat_image_urls = wp_get_attachment_image_src( $post_thumbnail_id, 'full' );
				$bw_response_data['feat_image_url'] = $feat_image_urls[0];
			}

			// store the shortcode attribute settings that affect full portfolio item view here
			$bw_show_tags = filter_var($request['bw_show_tags'], FILTER_VALIDATE_BOOLEAN);
			$bw_response_data['bw_show_tags'] = $bw_show_tags;

			return new WP_REST_Response( $bw_response_data, 200 );
		}
		else {
			// Return a WP_Error because the request product was not found. In this case we return a 404 because the main resource was not found.
			return new WP_Error( 'rest_portfolio_item_invalid', esc_html__( 'The portfolio item does not exist.', 'bw_portfolio' ), array( 'status' => 404 ) );
		}
    }
} 


/**
 * handle fetch requests to filter portfolio items by portfolio tag
 */
function bw_filter_sort_portfolio_items( $request ) {
	
    if( $request['bwbh_portfolio_filter_tag'] && $request['bwbh_portfolio_sort'] ) {
		
		$bwbh_portfolio_filter_tag = $request['bwbh_portfolio_filter_tag'];
		$bwbh_portfolio_sort = $request['bwbh_portfolio_sort'];

		// need to get the shortcode attribute settings that might affect filtered results here
		$bwbh_show_tags = filter_var($request['bwbh_show_tags'], FILTER_VALIDATE_BOOLEAN);
		$bwbh_modal_off = filter_var($request['bwbh_modal_off'], FILTER_VALIDATE_BOOLEAN);
		$bwbh_num_of_words = absint((int)$request['bwbh_num_of_words']);

		$bw_response_data = [
			'term' => sanitize_text_field($bwbh_portfolio_filter_tag),
			'sort' => sanitize_text_field($bwbh_portfolio_sort),
			'show_tags' => $bwbh_show_tags,
			'modal_off' => $bwbh_modal_off,
			'num_of_words' => $bwbh_num_of_words,
		];

		
		// include file that builds custom WP_Query for filtered portfolio items
		include plugin_dir_path( __FILE__ ) . 'inc/portfolio-query.php';

		// loop through query results, and only add relevant pieces of portfolio item data to the $bw_response_data array
		if ( $bw_portfolio_query->have_posts() ) :
			$n = 0;
			while ( $bw_portfolio_query->have_posts() ) : $bw_portfolio_query->the_post();
				$bw_response_data['portfolio_items'][$n] = [
					'bw_portfolio_id' => get_the_ID(),
					'title' => html_entity_decode(get_the_title()),
					'content' => wp_kses_post( bwbh_limit_words( strip_tags( get_the_content() ), $bwbh_num_of_words ) ) . '...',
				];
				
				// if modal_off is true, then send permalink for opening portfolio item manually
				if( $bwbh_modal_off ) {
					$bw_response_data['portfolio_items'][$n]['permalink'] = get_permalink( get_the_ID() );
				}

				// get and add portfolio item tags to $bw_response_data if show_tags shortcode attribute was true
				if( $bwbh_show_tags !== false ) {
					$bw_portfolio_tags = get_the_terms( get_the_ID(), 'portfolio_tag' );
					if( !is_wp_error($bw_portfolio_tags) && $bw_portfolio_tags !== false ) {
						foreach($bw_portfolio_tags as $bw_tag) {
							$bw_response_data['portfolio_items'][$n]['bw_portfolio_tags'][$bw_tag->term_id] = $bw_tag->name;
						}
					}
				}

				// add featured image url to array if it exists
				$post_thumbnail_id = get_post_thumbnail_id( $bw_post->ID );
				if(!empty($post_thumbnail_id)) {
					$feat_image_urls = wp_get_attachment_image_src( $post_thumbnail_id, 'full' );
					$bw_response_data['portfolio_items'][$n]['feat_image_url'] = $feat_image_urls[0];
				}
				$n++;

			endwhile;
			
			wp_reset_postdata();

		endif; // else if no portfolio items in tag are found then return error?(don't think this can happen though?)

		return new WP_REST_Response( $bw_response_data, 200 );	
    }		
} 

/**
* Register bw_portfolio post type
* Register portfolio_tag taxonomy
*/
add_action( 'init', 'bwbh_portfolio_cpt_init');
function bwbh_portfolio_cpt_init() {
	// 
	register_post_type('bw_portfolio',
        array(
            'labels'      => array(
                'name'          => __('Portfolio Items', 'bw_portfolio'),
                'singular_name' => __('Portfolio Item', 'bw_portfolio'),
		        'edit_item'         => __( 'Edit Portfolio Item', 'bw_portfolio' ),
		        'update_item'       => __( 'Update Portfolio Item', 'bw_portfolio' ),
            ),
	            'public'        => true,
	            'has_archive'   => true,
				'rewrite'       => array( 'slug' => 'portfolio' ),
				'supports'      => array( 'title', 'editor', 'author', 'thumbnail' ),
				'show_in_rest'  => true,
				'show_ui'       => true,
				'show_in_menu'  => true,
				'menu_position' => 20,
    		)
    );
	
    // Add new taxonomy, make it non-hierarchical (like tags)
    $bw_tax_labels = array(
        'name'              => _x( 'Portfolio Tags', 'taxonomy general name', 'bw_portfolio' ),
        'singular_name'     => _x( 'Portfolio Tag', 'taxonomy singular name', 'bw_portfolio' ),
        'search_items'      => __( 'Search Portfolio Tags', 'bw_portfolio' ),
        'all_items'         => __( 'All Portfolio Tags', 'bw_portfolio' ),
        'parent_item'       => null,
        'parent_item_colon' => null,
        'edit_item'         => __( 'Edit Portfolio Tag', 'bw_portfolio' ),
        'update_item'       => __( 'Update Portfolio Tag', 'bw_portfolio' ),
        'add_new_item'      => __( 'Add New Portfolio Tag', 'bw_portfolio' ),
        'new_item_name'     => __( 'New Portfolio Tag Name', 'bw_portfolio' ),
        'menu_name'         => __( 'Portfolio Tags', 'bw_portfolio' ),
    );
    $bw_tax_args = array(
        'hierarchical'      => false,
        'labels'            => $bw_tax_labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'portfolio-tag' ),
		'show_in_rest'      => true,
    );
    register_taxonomy( 'portfolio_tag', array( 'bw_portfolio' ), $bw_tax_args );
	// In case we use these variables later...
	unset( $bw_tax_labels );
	unset( $bw_tax_args );
}

/**
* Helper functions for the [bw_portfolio] Shortcode attributes
*/

// num_of_words_on_cards shortcode attribute
if (!function_exists('bwbh_limit_words')) {
	function bwbh_limit_words($string, $word_limit) {
	    $words = explode(" ", trim($string));
	    return implode(" ", array_splice($words, 0, $word_limit));
	}
}

// shortcode attributes with no value set are normally sent through as "indexed key => attribute name" pairs, where normal shortcode attributes with a value set are sent as "attribute name => att value"
// a number of the shortcode attributes I have added are simply flag variables, so to make it easier for users they can simply enter the attribute name, instead of attribute name=true
if (!function_exists('bwbh_normalize_empty_atts')) {
    function bwbh_normalize_empty_atts($atts) {
        foreach ($atts as $attribute => $value) {
			// if $attribute/key is an integer then that was an attribute set with no value, and we want to unset that, and re-add the attr name into the $atts array with boolean value true
            if (is_int($attribute)) {
                $atts[strtolower($value)] = true;
                unset($atts[$attribute]);
            }
        }
        return $atts;
    }
}

/**
 * The bw_portfolio shortcode
 */
if (!function_exists('bwbh_portfolio_shortcode')) {

	function bwbh_portfolio_shortcode( $atts ) {
		// normalize attribute keys, make lowercase
		$atts = array_change_key_case( (array) $atts, CASE_LOWER );
	
		// store empty attributes with value true set automatically
		$atts = bwbh_normalize_empty_atts($atts);
	
		// Shortcode attributes default values
		$bw_atts = shortcode_atts(
			[
				"modal_off" => "", /* add this shortcode attribute to make portfolio items open via normal request in theme template */
				"show_tags" => "", /* add this shortcode attribute to make portfolio items display the portfolio tags they have */
				"num_of_words_on_cards" => "", /* control how many words of content display on the smaller card portfolio items */
				"columns" => "", /* force number of columns to be 1, 2, 3, or 4 */
				"portfolio_title" => "", /* Add a title that displays above the portfolio */
				"filter_by_tags" => "", /* filter the initial display of portfolio items by portfolio tags separated by a comma */
				"portfolio_items" => "", /* enter specific ID's of portfolio items you want to show */
				"ignore_sticky_posts" => "", /* sometimes sticky posts are annoying, so add this attribute to your shortcode to ignore them */
			],
			$atts,
			"bw_portfolio"
		);
	
		$portfolio_output = "";

		// include file that builds custom WP_Query for shortcode output
		include plugin_dir_path( __FILE__ ) . 'inc/portfolio-query.php';
		
		// include main shortcode output file 
		$portfolio_output = include plugin_dir_path( __FILE__ ) . 'inc/portfolio-loop.php';
	
		// return shortcode output from output buffer here.
		return ob_get_clean();

	}
}

/**
 * Central location to create shortcode(s).
 */
add_action( 'init', 'bwbh_portfolio_shortcode_init' );
function bwbh_portfolio_shortcode_init() {
	if( !shortcode_exists('bw_portfolio') ) {
		add_shortcode( 'bw_portfolio', 'bwbh_portfolio_shortcode' );
	}
}

/**
* On plugin activation...
*/
register_activation_hook( __FILE__, 'bwbh_rewrite_flush' );
function bwbh_rewrite_flush() {
    // ...Call the function that registers the portfolio custom post type first...
    bwbh_portfolio_cpt_init();
 
    // ...Then flush the rewrite rules to ensure custom post type works on plugin activation
    flush_rewrite_rules();
}

/**
 * On plugin Deactivation...
 */
register_deactivation_hook( __FILE__, 'bwbh_deactivate' );
function bwbh_deactivate() {
    // Unregister the post type, so the rules are no longer in memory.
    unregister_post_type( 'bw_portfolio' );
    // Clear the permalinks to remove our post type's rules from the database.
    flush_rewrite_rules();
}