=== BW Portfolio ===
Contributors: bhartlenn
Donate Link: https://www.paypal.com/donate/?business=GT66FP68TM9AE&amount=5&no_recurring=0&item_name=Hello%21+If+you+enjoyed+some+of+my+web+development+work+you+can+help+fuel+me+with+coffee+and+tea+by+donating+here.+Thanks%21+%3A%29&currency_code=CAD
Tags: portfolio, shortcode, grid, modal
Requires at least: 5.2
Tested up to: 5.9.3
Requires PHP: 7.0
Stable tag: 1.2.3
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

The BW Portfolio plugin allows you to easily add portfolio items in your WordPress Dashboard, and you can use a handy shortcode to display your portfolio items in a modern responsive css grid that is compatible on many different devices.

== Description ==

The BW Portfolio plugin is powerful yet lightweight and fast. It allows you to easily add portfolio items in your WordPress Dashboard, and organize them with portfolio tags as well. Then by using a handy shortcode you can display your portfolio items just about anywhere in a nice, responsive css grid that is compatible on many different devices. Has tag filtering built in.

Here is a breakdown of the shortcode options currently available:

* [bw_portfolio modal_off] >> By default clicking on a portfolio item card loads the full version of the portfolio item in a pop-up modal. Add the modal_off attribute to load portfolio items in your normal theme templates instead.
* [bw_portfolio portfolio_title="Ben's Portfolio"] >> The portfolio_title attribute allows you to automatically insert a title before your portfolio items grid
* [bw_portfolio columns=3] >> By default the bw_portfolio shortcode displays your items in a grid that will load as many items as it can per column, and reduce the number of items per column on smaller screen sizes. You can force the number of columns to be 1, 2, 3, or 4 by using the columns attribute.
* [bw_portfolio show_tags] >> Display portfolio item tags on each of the portfolio item cards
* [bw_portfolio num_of_words_on_cards=20] >> By default the bw_portfolio shortcode shows the portfolio item content clipped at 15 words on the cards, but you can over ride that number using the num_of_words_on_cards attribute
* [bw_portfolio filter_by_tags="yourtag1, yourothertag"] >> The filter_by_tags attribute allows you to only show portfolio items that have the tags you set
* [bw_portfolio portfolio_items="42, 69, 1111"] >> The portfolio_items attribute accepts a list of portfolio item ID's, and will only display those portfolio items that you set. Ignores sticky posts automatically.
* [bw_portfolio ignore_sticky_posts] >> There might be other times you want to ignore sticky portfolio items in your grid of portfolio items, do so by adding the ignore_sticky_posts attribute

If you want to see a new feature please let me know [in the plugins Wordpress support forum.](https://wordpress.org/support/plugin/bw-portfolio/)

== Screenshots ==

1. Portfolio items plugin display with different sizes of images

== Changelog ==

= 1.2.3 =
* Fixed full portfolio item display being spuished on mobile
* Fixed color of portfolio text in twentytwentytwo theme and blocks
* Fixed filter and sort tags layout
* Fixed portfolio items with apostrophe's in title showing &#039; instead of an apostrophe
* Removed and erroneous console.log that was used for testing and logged portfolio item attributes

= 1.2.2 =
* Upgraded the filter by tags bar with new sort buttons that allows users to show newest or oldest portfolio items first
* Modified styling for filter and sort buttons, and turned into html form for easier data submission

= 1.2.1 =
* added a missing function name being called on plugin activation to register post type

= 1.2.0 =
* Major update where I stopped using admin-ajax and switched over to using the WordPress REST API for viewing a portfolio item, and filtering portfolio items by portfolio tag
* added .alignwide class to portfolio container so wordpress themes(like twentytwentone, etc) default styling allows it to be full width of its parent
* fixed some minor display bugs

= 1.1.7 =
* fixed bug where portfolio item card content wouldn't show unless num_of_words_on_cards shortcode att was added

= 1.1.6 =
* added missing /inc/portfolio-filtered-loop.php file

= 1.1.5 =
* This was a large update that added filter links for each portfolio tag at the top of the portfolio. When you click on a portfolio tag filter link, the portfolio items with that portfolio tag are displayed
* Can now force layout to be 1 column, in addition to 2, 3, or 4 column layouts that already existed
* Fixed portfolio_title shortcode attribute bug
* Made a number of small improvements, and squashed some other minor bugs along the way

= 1.1.4 =
* Fixed uncoloured text on modal full version display of portfolio item
* Improved data handling on filter_by_tags shortcode attribute so it handles typos better

= 1.1.3 =
* Squashed bug that was sometimes preventing custom post type initialization on plugin activation

= 1.1.2 =
* Added color declaration for portfolio item text, so it always shows on light background colour
* Removed box-shadow declaration for portfolio items displayed in css grid

= 1.1.1 =
* Removed errant text from readme.txt

= 1.0.0 =
* Initial Public Release Date - January 1, 2022
