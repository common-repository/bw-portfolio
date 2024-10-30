<?php

// start ouput buffer
ob_start();

if ( $bw_portfolio_query->have_posts() ) :

	$bw_portfolio_id = uniqid();
	
	$portfolio_output .= "<section id='bw_portfolio_" . $bw_portfolio_id . "' class='bw_portfolio_container alignwide'>";

	$portfolio_output .= "<header class='bw_portfolio_header'>";
	
	if( !empty($bw_atts['portfolio_title']) ) {
		$portfolio_output .= "<h2 class='bw_portfolio_heading'>" .  esc_html__( $bw_atts['portfolio_title'] ) . "</h2>";
	}
	/**
	 * portfolio tag filter links
	 */
	$bw_portfolio_tags = get_terms([
		'taxonomy' => 'portfolio_tag',
		'orderby' => 'id'
	]);

	$portfolio_output .= "<form id='portfolio_filter_sort_form_" . $bw_portfolio_id . "'>";

	$portfolio_output .= "<section class='portfolio_filter_sort_inputs'>";

	if( !is_wp_error($bw_portfolio_tags) && count($bw_portfolio_tags) > 0 ) {
		
		
				
		$portfolio_output .= "<input id='pt_show_all_" . $bw_portfolio_id . "' type='radio' name='bwbh_portfolio_filter_tag' value='show_all' checked><label class='bw_portfolio_form_label' for='pt_show_all_" . $bw_portfolio_id . "' title='Show all portfolio items'>Show All</label>";

		foreach($bw_portfolio_tags as $bw_tag) {
			if( isset($bw_tags_array) ) {
				if( in_array($bw_tag->slug, $bw_tags_array) ) {
					$portfolio_output .= "<input id='pt_" . $bw_tag->slug . "_" . $bw_portfolio_id . "' type='radio' name='bwbh_portfolio_filter_tag' value='" . $bw_tag->slug . "'><label class='bw_portfolio_form_label' for='pt_" . $bw_tag->slug . "_" . $bw_portfolio_id . "' title='Show portfolio items tagged with " . $bw_tag->name . "'>" . $bw_tag->name . "</label>";
				}
			}
			else {
				$portfolio_output .= "<input id='pt_" . $bw_tag->slug . "_" . $bw_portfolio_id . "' type='radio' name='bwbh_portfolio_filter_tag' value='" . $bw_tag->slug . "'><label class='bw_portfolio_form_label' for='pt_" . $bw_tag->slug . "_" . $bw_portfolio_id . "' title='Show portfolio items tagged with " . $bw_tag->name . "'>" . $bw_tag->name . "</label>";
			}
			
 		}
		 $portfolio_output .= " <span class='bwbh_separator'>|</span> "; 
		//$portfolio_output .= "</section>";
	}

	// sort buttons
	//$portfolio_output .= "<section class='portfolio_sort'>";

	
	$portfolio_output .= "<input id='ps_desc_" . $bw_portfolio_id . "' type='radio' name='bwbh_portfolio_sort' value='DESC' checked><label class='bw_portfolio_form_label' for='ps_desc_" . $bw_portfolio_id . "' title='Show newest first'>Newest</label>";
	$portfolio_output .= "<input id='ps_asc_" . $bw_portfolio_id . "' type='radio' name='bwbh_portfolio_sort' value='ASC'><label class='bw_portfolio_form_label' for='ps_asc_" . $bw_portfolio_id . "' title='Show oldest first'>Oldest</label>";

	$portfolio_output .= "</section>";

	$portfolio_output .= "</form>";
		
	$portfolio_output .= "</header>";
	
	// if user sets columns attribute, add a custom css class to the css grid container that overrides grid-template-columns value
	$bw_content_classes = "";
	switch($bw_atts['columns']) {
		
		case 1:
			$bw_content_classes = " col-1-layout";
			break;
		case 2:
			$bw_content_classes = " col-2-layout";
			break;
		case 3:
			$bw_content_classes = " col-3-layout";
			break;
		case 4:
			$bw_content_classes = " col-4-layout";
			break;
		
		default:
			$bw_content_classes = "";
	}
	
	// set data attributes based on shortcode attributes, and the data atts can be used by js to persist user setting when portfolio items are filtered, or otherwise reloaded into the screen with js 
	$bwbh_data_atts = "";
	
	// if user sets modal_off attribute, then add .modal_off css class so javascript knows to not handle click functionality
	if( $bw_atts['modal_off'] ) {
		$bw_content_classes .= " modal_off";
	}
	
	// going to add classes to .bw_portfolio_content for storing shortcode attribute settings
	if( $bw_atts['show_tags'] ) {
		$bw_content_classes .= " show_tags";
	}
	
	// if someone sets the num_of_words_on_cards shortcode attribute, use it to shorten card content, otherwise default to showing 15 words on smaller card view of portfolio item
	if( is_int((int)$bw_atts['num_of_words_on_cards']) && (int)$bw_atts['num_of_words_on_cards']>0 ) {
		$num_of_words = (int)$bw_atts['num_of_words_on_cards'];
	}
	else {
		$num_of_words = 15;
	}
	$data_num_of_words = "data-num_of_words='" . $num_of_words . "'";
	
	$portfolio_output .= "<div class='bw_portfolio_content_area" . $bw_content_classes . "' " . $data_num_of_words . ">";

	while ( $bw_portfolio_query->have_posts() ) : $bw_portfolio_query->the_post();
		// add portfolio item html to output variable
		$portfolio_output .= "<article class='bw_portfolio_item' data-post_id='" . get_the_ID() . "' data-permalink='" . get_the_permalink() . "' >";
		
		// if user adds modal_off attribute, then add html anchor link to handle normal anchor link click to the full portfolio item being displayed by theme template
		if( $bw_atts['modal_off'] ) {
			$portfolio_output .= "<a href='" . get_the_permalink() . "'>";		
		}
		
		$portfolio_output .= "<img src='" . esc_url( get_the_post_thumbnail_url(get_the_ID(), 'large') ) . "' class='bw_portfolio_item_image'>"; 
		
		$portfolio_output .= "<div class='bw_portfolio_item_text'>";
	
		$portfolio_output .= "<h3 class='bw_portfolio_item_title'>" . esc_html( get_the_title() ) . "</h3>";
				
		$portfolio_output .= "<section class='bw_portfolio_item_content'>" . wp_kses_post( bwbh_limit_words( strip_tags(get_the_content()), $num_of_words ) ) . "...</section>";
		
		// check if shortcode attribute show_tags is present
		if( $bw_atts['show_tags'] ) {
			// then display portfolio_tag's if they've been added to the current portfolio item
			$bw_portfolio_tags = get_the_terms(get_the_ID(), 'portfolio_tag');
			if( !is_wp_error($bw_portfolio_tags) && $bw_portfolio_tags !== false ) {
				$portfolio_output .= "<section class='bw_portfolio_tags'>";
				$portfolio_output .= "<span>Tags: </span>";
				$t = 0;
				foreach($bw_portfolio_tags as $bw_tag) {
					$t++;
					
					$portfolio_output .= "<span>" . $bw_tag->name . "</span>";
					
					// add spaces after all but the last tag
					if( $t != count($bw_portfolio_tags) ) {
						$portfolio_output .= " ";
					}
				
				}
				$portfolio_output .= "</section>";
			}
		}
		
		$portfolio_output .= "</div>"; // end div.bw_portfolio_item_content
			
		if( $bw_atts['modal_off'] ) {
			$portfolio_output .= "</a>";
		}
			
		$portfolio_output .= "</article>";
		
	endwhile;

	$portfolio_output .= "</div>";

	$portfolio_output .= "</section>";

else :
	// no portfolio items were found so display message for admins and editors to check on portfolio items
	if( current_user_can('editor') || current_user_can('administrator') ) {
		$portfolio_output .= "<p class='admin-notice'>There were no portfolio items found. You can see your <a href='/wp-admin/edit.php?post_type=bw_portfolio'>portfolio items here</a></p>";
	}
	else {
		// or no output for other users who wouldn't be able to do anything, and error messages may cause them to get distressed, confused, or mad even.
		$portfolio_output = "";
	}

endif;

echo ($portfolio_output);

wp_reset_postdata();
