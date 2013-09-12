<?php
/*
Plugin Name: Post Google Map Category
Plugin URI: http://webdevstudios.com/support/wordpress-plugins/
Description: Extends the original Plugin "Post Google Map" with the exception that it adds a new shortcode that accepts a category and a title argument. Just use the shortcode [google-map-category category="todo" title="To explore"]. You must install the original plugin for this to work. You can find here http://webdevstudios.com/support/wordpress-plugins/. Plugin allows posts to be linked to specific addresses and coordinates and display plotted on a Google Map.  Use shortcode [google-map] to display map directly in your post/page.  Map shows plots for each address added to the post you are viewing.
Version: 1.0.0
Author: http://www.morenafiore.com extending "Post Google Map Category" plugin by WebDevStudios.com
Author https://github.com/morena/wordpress-post-google-map-category-plugin
License: GPLv2
*/

//register the [google-map-category] shortcode. It takes the arguments category and title
add_shortcode( 'google-map-category', 'gmp_register_shortcode_category' );


//shortcode function
function gmp_register_shortcode_category($atts) {
	extract(shortcode_atts(array(
			'category' => '',
			'title' =>'Category',
		),$atts));

	global $gmp_display;

	$gmp_display = 'return';

	//generate map for shortcode
	$map = gmp_generate_map_category( '400', 'map_canvas_shortcode_'.$category, $category, $title);

	echo $map;

}


function gmp_generate_map_category( $height='650', $id='map_canvas', $category = 0, $title = "Category" ) {

	//include the Google Map Class
	include_once( plugin_dir_path( __FILE__ ) .'map-lib/Google-Map-Class-category.php' );

	?>
	<h2 class="title"><span><?php echo $title; ?></span></h2>
	<div id="google_map_<?php echo $category;?>" class="paper shadow"><?php
	$gmp_google_map = new GMP_Google_Map_Category();
	$gmp_google_map->run( absint( $height ), $id, $category );
	?></div><?php


}