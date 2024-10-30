<?php
$bw_args = [];
/**
 * Here we setup a new WP_Query to pull in portfolio items. Query can be modified using shortcode attributes.
 */
$bw_args = [
	'post_type' => 'bw_portfolio',
];

// sort portfolio items when user clicks on sort order button
if( !empty($bwbh_portfolio_sort) ) {
	$bw_args['order'] = $bwbh_portfolio_sort;
}

// if shortcode tags attribute was set like: tags="sometag1, someothertag", add taxonomy query for portfolio_tag to wp_query
if( !empty($bw_atts['filter_by_tags']) || (!empty($bwbh_portfolio_filter_tag) && $bwbh_portfolio_filter_tag != "show_all") ) {
	
	// strip any html tags but not the html tag contents
	$bw_tags_str = strip_tags( $bw_atts['filter_by_tags'] );
	// explode the expected string of words separated by comma into an array of values
	$bw_tags_array = explode(',', $bw_tags_str);
	// for each array value make spaces between words into dashes, and make words lowercase
	$bw_tags_array = array_map('sanitize_title_with_dashes', $bw_tags_array);
	// trim whitespace off ends of tags
	$bw_tags_array = array_map('trim', $bw_tags_array);
	
	// if user clicked on portfolio tag filter link, empty array of filter terms from shortcode, and insert only the term user clicked on
	if(!empty($bwbh_portfolio_filter_tag)) {
		$bw_tags_array = [];
		$bw_tags_array[] = strip_tags(trim($bwbh_portfolio_filter_tag));
	}
	
	// add taxonomy query arguments
	$bw_args['tax_query'] = [
		[
			'taxonomy' => 'portfolio_tag',
			'field'    => 'slug',
			'terms'    => $bw_tags_array,
		]
	];
}

// if shortcode portfolio_items attribute is set
if( !empty($bw_atts['portfolio_items']) ) {
	// strip any html tags but not the html tag contents
	$bw_portfolio_items = strip_tags( $bw_atts['portfolio_items'] );
	// explode the expected string of words separated by comma into an array of values
	$bw_portfolio_item_ids = explode(',', $bw_portfolio_items);
	// trim whitespace off ends of portfolio item ids
	array_map('trim', $bw_portfolio_item_ids);
	
	$bw_args['post__in'] = $bw_portfolio_item_ids;
}

// if user sets specific post ids, or the ignore sticky posts attribute
if( !empty($bw_atts['portfolio_items']) || $bw_atts['ignore_sticky_posts'] ) {
	// most likely does not want sticky posts included
	$bw_args['ignore_sticky_posts'] = true;
}

$bw_portfolio_query = new WP_Query($bw_args);