window.addEventListener('load', function() {
	
	if ( document.querySelector('.bw_portfolio_container') !== null ) {
		
		// helper function that closes/hides/resets the full portfolio item modal
		function closeModal(bwbh_modal_selector = bwbh_modal_el) {
			bwbh_modal_selector.style.display = "none";
			bwbh_modal_selector.innerHTML = "<img class='loader' src='" + bwbh_portfolio_js_vars.loader_gif_url + "'>";
		}

		// create modal
		const bwbh_modal_el = document.createElement("div");
		bwbh_modal_el.classList.add("bw_portfolio_modal");

		// add loader gif to modal
		const bwbh_loader_gif = document.createElement('img');
		bwbh_loader_gif.setAttribute('class', 'loader');
		bwbh_loader_gif.setAttribute('src', bwbh_portfolio_js_vars.loader_gif_url);
		bwbh_modal_el.appendChild(bwbh_loader_gif);
		
		// append modal to body
		document.body.appendChild(bwbh_modal_el);
	
		// use let for flag variable used to prevent multiple clicks of relevant links and buttons
		let clicked;
		
		// detecting all clicks on document because often dealing with dynamically added elements 
		document.addEventListener('click', function(e){
		
			// try to prevent multiple clicks, set clicked to false at end of click functionality sections below
			if (clicked) {
				return false;
			}
			clicked = true;
			// reset clicked after timeout so click doesn't get disabled
			setTimeout(function(){
				clicked = false;
			}, 750);
		
			// if clicking on portfolio item, or anyting that has portfolio item as parent, and modal_off attribute is NOT set(adds .modal_off class to .bw_portfolio_content_area)
			if ( (e.target.classList.contains('bw_portfolio_item') || e.target.closest('.bw_portfolio_item') !== null) && 
			!e.target.closest('.bw_portfolio_content_area').classList.contains('modal_off') ) {
				e.preventDefault();
			
				// set portfolio item, even if clicking on child element of portfolio item
				if ( e.target.matches('.bw_portfolio_item') ) {
					var portfolio_item = e.target;
				}
				else {
					var portfolio_item = e.target.closest('.bw_portfolio_item');
				}
				
				// boolean used to handle showing tags on cards of filtered and full modal view portfolio items
				let bwbh_show_tags = portfolio_item.closest('.bw_portfolio_content_area').classList.contains('show_tags');
						
				// display modal on portfolio item click
				bwbh_modal_el.style.display = "grid";
			
				const bwbh_form_data = new FormData();
			    bwbh_form_data.append( 'bw_portfolio_item_id', portfolio_item.dataset.post_id);
				bwbh_form_data.append( 'bw_show_tags', bwbh_show_tags);

				// format form data as plain, then into JSON so fetch can send data as JSON
				const form_data_json = JSON.stringify( Object.fromEntries( bwbh_form_data.entries() ) );
				
				fetch(wpApiSettings.root + 'bw-portfolio/v1/view-portfolio-item', {
					method: 'POST',
					credentials: 'same-origin',
					headers: {
						"Content-Type": "application/json",
						"Accept": "application/json",
						"X-WP-Nonce": bwbh_portfolio_js_vars.nonce,
					},
					body: form_data_json,
			    })
			    .then( response => response.json() )
			    .then( data => {
					
					// remove loader gif from our modal
					if( bwbh_modal_el.querySelector('.loader') !== null ) {
						bwbh_modal_el.querySelector('.loader').remove();
					}
					
					// dynamically create full view of portfolio item and then insert into modal
					const bwbh_portfolio_item_full = document.createElement("article");
					bwbh_portfolio_item_full.setAttribute('class', 'bw_portfolio_item_full');

					// add close modal link
					const bwbh_close_link = document.createElement('a');
					bwbh_close_link.setAttribute('href', '');
					bwbh_close_link.setAttribute('class', 'bw_portfolio_modal_close dashicons dashicons-dismiss');
					bwbh_portfolio_item_full.appendChild(bwbh_close_link);

					// portfolio item featured image
					const bwbh_feat_image = document.createElement('img');
					bwbh_feat_image.classList.add('bw_portfolio_item_full_image');
					bwbh_feat_image.setAttribute('src', data.feat_image_url);
					bwbh_portfolio_item_full.appendChild(bwbh_feat_image);
						
					// add portfolio item text div for holding title, content, and tags
					const bwbh_portfolio_text = document.createElement('div');
					bwbh_portfolio_text.setAttribute('class', 'bw_portfolio_item_full_text');

					// add heading for title to text area
					const bwbh_portfolio_heading = document.createElement('h3');
					bwbh_portfolio_heading.setAttribute('class', 'bw_portfolio_item_full_title');
					bwbh_portfolio_heading.textContent = data.title;
					bwbh_portfolio_text.appendChild(bwbh_portfolio_heading);

					// add content div to text area
					const bwbh_portfolio_item_content = document.createElement('div');
					bwbh_portfolio_item_content.setAttribute('class', 'bw_portfolio_item_full_content');
					bwbh_portfolio_item_content.innerHTML = data.content;
					bwbh_portfolio_text.appendChild(bwbh_portfolio_item_content);

					// add portfolio item tags if shortcode attribute show_tags is present or true, and a tag(s) have been sent in json response
					if( data.bw_show_tags === true && Object.keys(data.bw_portfolio_tags).length > 0 ) {

						const bwbh_portfolio_tags = document.createElement('section');
						bwbh_portfolio_tags.setAttribute('class', 'bw_portfolio_tags');
						
						const bwbh_span = document.createElement('span');
						bwbh_span.innerHTML = "Tags:";
						bwbh_portfolio_tags.appendChild(bwbh_span);

						for (let tag in data.bw_portfolio_tags) {
							const bwbh_portfolio_tag = document.createElement('span');
							bwbh_portfolio_tag.innerHTML = data.bw_portfolio_tags[tag];
							bwbh_portfolio_tags.append( bwbh_portfolio_tag );
						}
						bwbh_portfolio_text.appendChild(bwbh_portfolio_tags);
					}

					// add text div with title, content, and tags to full portfolio item
					bwbh_portfolio_item_full.appendChild(bwbh_portfolio_text);

					// add full portfolio item to modal
					bwbh_modal_el.appendChild(bwbh_portfolio_item_full);

					// reset clicked flag variable
					clicked = false;

			    })
			    .catch((error) => {
					console.error(error);
					clicked = false;
			    });
			}
		
			// if clicking on portfolio tag filter or sort label, display portfolio items with that portfolio tag and sort order
			if (e.target.classList.contains('bw_portfolio_form_label')) {

				// initialize vars for handling portfolio filter tag click
				let bwbh_clicked_tag = e.target;
				let bwbh_portfolio_container = bwbh_clicked_tag.closest('.bw_portfolio_container');
				let bwbh_portfolio_container_height = bwbh_portfolio_container.clientHeight;
				let bwbh_portfolio_content_area = bwbh_portfolio_container.querySelector('.bw_portfolio_content_area');
				let bwbh_num_of_words = bwbh_portfolio_content_area.dataset.num_of_words;
				
				// boolean used to handle showing tags on cards of filtered portfolio items
				let bwbh_show_tags = bwbh_portfolio_content_area.classList.contains('show_tags');
				// boolean used to handle whether modal is on or off for filtered portfolio items
				let bwbh_modal_off = bwbh_portfolio_content_area.classList.contains('modal_off');
				
				// clear the old set of portfolio items
				if (bwbh_portfolio_content_area !== null) {
					bwbh_portfolio_content_area.innerHTML = "";
				}
				
				// create loader gif element
				const bwbh_loader_gif = document.createElement('img');
				bwbh_loader_gif.classList.add('loader');
				bwbh_loader_gif.setAttribute('src', bwbh_portfolio_js_vars.loader_gif_url);
			
				if (bwbh_portfolio_container !== null && bwbh_portfolio_container_height !== null) {
					// append loader gif to portfolio header area
					bwbh_portfolio_container.querySelector('header').appendChild(bwbh_loader_gif);
					
					// set height of container so content doesn't jump from current height, to 0 height, then to new height
					// when portfolio items get filtered to be less rows than are originally shown, the below line was keeping height of portfolio container to height of original rows
					//bwbh_portfolio_container.style.height = bwbh_portfolio_container_height + "px";
				}

				// formData was not getting most recent form input values until second click(maybe because checking radio button takes some milliseconds?) unless I added a slight delay before getting form data???
				setTimeout(function() {
					
					// setup form data to send with fetch request
					const bwbh_data1 = new FormData( e.target.closest('form') );
					bwbh_data1.append( 'bwbh_show_tags', bwbh_show_tags);
					bwbh_data1.append( 'bwbh_modal_off', bwbh_modal_off);
					bwbh_data1.append( 'bwbh_num_of_words', bwbh_num_of_words);

					// format form data as plain, then into JSON so fetch can send data as JSON
					const form1_data_json = JSON.stringify( Object.fromEntries( bwbh_data1.entries() ) );

					//Make fetch request
					fetch( wpApiSettings.root + 'bw-portfolio/v1/filter-sort-portfolio-items', {
						method: 'POST',
						credentials: 'same-origin',
						headers: {
							"Content-Type": "application/json",
							"Accept": "application/json",
							"X-WP-Nonce": bwbh_portfolio_js_vars.nonce,
						},
						body: form1_data_json
					})
					.then( response => response.json() )
					.then( data => {
												
						bwbh_loader_gif.remove();
											
						for ( let bwbh_key in data.portfolio_items ) {
							
							// dynamically create card view of each portfolio item and then insert into portfolio container content area .bw_portfolio_content_area
							const bwbh_portfolio_item_card = document.createElement("article");
							bwbh_portfolio_item_card.setAttribute('class', 'bw_portfolio_item');
							bwbh_portfolio_item_card.setAttribute('data-post_id', data.portfolio_items[bwbh_key].bw_portfolio_id);
							bwbh_portfolio_item_card.setAttribute('data-permalink', data.portfolio_items[bwbh_key].permalink);
	
							// portfolio item featured image
							const bwbh_feat_image = document.createElement('img');
							bwbh_feat_image.classList.add('bw_portfolio_item_image');
							bwbh_feat_image.setAttribute('src', data.portfolio_items[bwbh_key].feat_image_url);						
							
							// add portfolio item text div for holding title, content, and tags
							const bwbh_portfolio_text = document.createElement('div');
							bwbh_portfolio_text.setAttribute('class', 'bw_portfolio_item_text');
							
							// add heading for title
							const bwbh_portfolio_heading = document.createElement('h3');
							bwbh_portfolio_heading.setAttribute('class', 'bw_portfolio_item_title');
							bwbh_portfolio_heading.textContent = data.portfolio_items[bwbh_key].title;
							bwbh_portfolio_text.appendChild(bwbh_portfolio_heading);
							
							// add content div
							const bwbh_portfolio_content = document.createElement('div');
							bwbh_portfolio_content.setAttribute('class', 'bw_portfolio_item_content');
							bwbh_portfolio_content.innerHTML = data.portfolio_items[bwbh_key].content;
							bwbh_portfolio_text.appendChild(bwbh_portfolio_content);
	
							
							// add portfolio item tags if shortcode attribute show_tags is present or true, and a tag(s) have been sent in json response
							if( data.show_tags === true && Object.keys(data.portfolio_items[bwbh_key].bw_portfolio_tags).length > 0 ) {
	
								const bwbh_portfolio_tags = document.createElement('section');
								bwbh_portfolio_tags.setAttribute('class', 'bw_portfolio_tags');
								
								const bwbh_default_span = document.createElement('span');
								bwbh_default_span.innerHTML = "Tags:";
								bwbh_portfolio_tags.appendChild(bwbh_default_span);
	
								for (let tag in data.portfolio_items[bwbh_key].bw_portfolio_tags) {
									const bwbh_portfolio_tag = document.createElement('span');
									bwbh_portfolio_tag.innerHTML = data.portfolio_items[bwbh_key].bw_portfolio_tags[tag];
									bwbh_portfolio_tags.append( bwbh_portfolio_tag );
								}
								bwbh_portfolio_text.appendChild(bwbh_portfolio_tags);
							}
	
							// add text div with title, content, and portfolio feaured image to the portfolio item link, or to the portfolio item
							if( data.modal_off === true ) {
								const bwbh_regular_link = document.createElement('a');
								bwbh_regular_link.setAttribute('href', data.portfolio_items[bwbh_key].permalink);
								bwbh_portfolio_item_card.appendChild(bwbh_regular_link);
								
								bwbh_regular_link.appendChild(bwbh_feat_image);
								bwbh_regular_link.appendChild(bwbh_portfolio_text);
							}
							else {
								bwbh_portfolio_item_card.appendChild(bwbh_feat_image);
								bwbh_portfolio_item_card.appendChild(bwbh_portfolio_text);
							}
							
							// add portfolio item card to portfolio content area
							bwbh_portfolio_content_area.appendChild(bwbh_portfolio_item_card);
						}
	
						// reset clicked flag variable
						clicked = false;
	 
					})
					.catch((error) => {
						console.error(error);
						// reset clicked flag variable
						clicked = false;
					});

				}, 25);				
			}
		
			// if clicking on modal close button, then close modal
			if ( e.target.matches('.bw_portfolio_modal_close') || e.target.matches('.bw_portfolio_modal') ) {
				e.preventDefault();
				closeModal();
				clicked = false;
			}
		
		});
	
		// close modal if esc key pressed
		document.addEventListener('keydown', function(e){
		    if (e.keyCode == 27) {
				closeModal();
		    }
		});
		
	}
	
});