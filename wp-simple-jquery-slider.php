<?php
/*
Plugin Name: WP Simple JQuery Slider
Plugin URI: https://github.com/qriouslad/wp-simple-jquery-slider
Description: Simple slider using JQuery Slides https://github.com/nathansearles/Slides
Version: 1.0
Author: Bowo
Author URI: https://bowo.io
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

WP Simple JQuery Slider is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
WP Simple JQuery Slider is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with WP Simple JQuery Slider. If not, see https://www.gnu.org/licenses/gpl-2.0.html
*/

function wpsjs_slider_activation() {
}
register_activation_hook(__FILE__, 'wpsjs_slider_activation');

function wpsjs_slider_deactivation() {
}
register_deactivation_hook(__FILE__, 'wpsjs_slider_deactivation');


add_action('wp_enqueue_scripts','wpsjs_scripts');
function wpsjs_scripts() {

	wp_register_script('slidesjs_core',plugins_url('js/jquery.slides.min.js', __FILE__),array('jquery'));
	wp_enqueue_script('slidesjs_core');

	wp_register_script('slidesjs_init', plugins_url('js/slidesjs.initialize.js', __FILE__));
	wp_enqueue_script('slidesjs_init');

}


add_action('wp_enqueue_scripts', 'wpsjs_styles');
function wpsjs_styles() {

	wp_register_style('slidesjs_example', plugins_url('css/example.css', __FILE__));
	wp_enqueue_style('slidesjs_example');

	wp_register_style('slidesjs_styles', plugins_url('css/styles.css', __FILE__));
	wp_enqueue_style('slidesjs_styles');

	wp_register_style('slidesjs_fonts', plugins_url('css/font-awesome.min.css', __FILE__));
	wp_enqueue_style('slidesjs_fonts');

}


add_shortcode('wpsjs_slider_example', 'wpsjs_display_slider_example');
function wpsjs_display_slider_example() {

$plugins_url = plugins_url();

return '<div class="container">
		<div id="slides">
			<img src="' . plugins_url('img/example-slide-1.jpg', __FILE__) .  '" />
			<img src="' . plugins_url('img/example-slide-2.jpg', __FILE__) .  '" />
			<img src="' . plugins_url('img/example-slide-3.jpg', __FILE__) .  '" />
			<img src="' . plugins_url('img/example-slide-4.jpg', __FILE__) .  '" />
			<a href="#" class="slidesjs-previous slidesjs-navigation"><i class="icon-chevron-left icon-large"></i></a>
			<a href="#" class="slidesjs-next slidesjs-navigation"><i class="icon-chevron-right icon-large"></i></a>
		</div>
	</div>';

}


add_action('init', 'wpsjs_register_slider_cpt');
function wpsjs_register_slider_cpt() {

	$labels = array(

		'menu_name' => _x('Sliders', 'slidersjs_slider'),

	);

	$args = array(

		'labels' => $labels,
		'name' => 'Sliders',
		'singular_name' => 'Slider',
		'edit_item' => 'Edit Slider',
		'hierarchical' => true,
		'description' => 'Slideshows',
		'supports' => array('title','editor'),
		'public' => true,
		'show_ui' => true,
		'show_in_menu' => true,
		'show_in_nav_menus' => true,
		'publicly_queryable' => true,
		'exclude_from_search' => false,
		'has_archive' => true,
		'query_var' => true,
		'can_export' => true,
		'rewrite' => true,
		'capability_type' => 'post'

	);

	register_post_type('slidesjs_slider', $args);

}


add_action('add_meta_boxes', 'wpsjs_slider_meta_box');

function wpsjs_slider_meta_box() {

	add_meta_box('wpsjs-slider-images', 'Slider Images', 'wpsjs_view_slider_images_box', 'slidesjs_slider', 'normal');

}

function wpsjs_view_slider_images_box() {
	global $post;

	$gallery_images = get_post_meta($post->ID, '_wpsjs_gallery_images', true);

	$gallery_images = ($gallery_images != '') ? json_decode($gallery_images) : array();

	$html = '<input type="hidden" name="wpsjs_slider_box_nonce" value="' . wp_create_nonce(basename(__FILE__)) . '" />';

	$html .= '';

	$html .= '

	<table class="form-table">
		<tbody>
			<tr>
				<th><label for="Upload Images">Image 1</label></th>
				<td><input id="wpsjs_slider_upload" type="text" name="gallery_img[]" value="' . $gallery_images[0] . '" /></td>
			</tr>
			<tr>
				<th><label for="Upload Images">Image 2</label></th>
				<td><input id="wpsjs_slider_upload" type="text" name="gallery_img[]" value="' . $gallery_images[1] . '" /></td>
			</tr>
			<tr>
				<th><label for="Upload Images">Image 3</label></th>
				<td><input id="wpsjs_slider_upload" type="text" name="gallery_img[]" value="' . $gallery_images[2] . '" /></td>
			</tr>
			<tr>
				<th><label for="Upload Images">Image 4</label></th>
				<td><input id="wpsjs_slider_upload" type="text" name="gallery_img[]" value="' . $gallery_images[3] . '" /></td>
			</tr>
			<tr>
				<th><label for="Upload Images">Image 5</label></th>
				<td><input id="wpsjs_slider_upload" type="text" name="gallery_img[]" value="' . $gallery_images[4] . '" /></td>
			</tr>
		</tbody>
	</table>

	';

	echo $html;


}

add_action('save_post', 'wpsjs_save_slider_info');
function wpsjs_save_slider_info($post_id) {

	// verify nonce

	if (!wp_verify_nonce($_POST['wpsjs_slider_box_nonce'], basename(__FILE__))) {

		return $post_id;

	}

	// check autosave

	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {

		return $post_id;

	}

	// check permissions

	if ( 'slidesjs_slider' == $_POST['post_type'] && current_user_can('edit_post', $post_id)) {

		/* Save Slider Images */

		// print_r($_POST['gallery_img']);

		// exit;

		$gallery_images = $_POST['gallery_img'];

		$gallery_images = strip_tags(json_encode($gallery_images));

		update_post_meta($post_id, '_wpsjs_gallery_images', $gallery_images);

	} else {

		return $post_id;

	}


}


add_shortcode('wpsjs_slider', 'wpsjs_display_slider');
function wpsjs_display_slider($attr,$content) {

	extract(shortcode_atts(array(

		'id' => ''

	), $attr));

	$gallery_images = get_post_meta($id, '_wpsjs_gallery_images', true);

	$gallery_images = ($gallery_images != '') ? json_decode($gallery_images) : array();

	$plugins_url = plugins_url();

	$html = '
		<div class="container">
		<div id="slides">
	';

	foreach ($gallery_images as $gal_img) {

		if ($gal_img != '') {

			$html .= '<img alt="" src="' . $gal_img . '" />';

		}

	}

	$html .= '
		<a href="#" class="slidesjs-previous slidesjs-navigation"><i class="icon-chevron-left icon-large"></i></a>
		<a href="#" class="slidesjs-next slidesjs-navigation"><i class="icon-chevron-right icon-large"></i></a>
		</div>
		</div>
	';

	return $html;

}