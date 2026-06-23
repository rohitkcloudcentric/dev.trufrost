<?php
/**
 * Blocks Initializer
 * 
 * @package WP Logo Showcase Responsive Slider
 * @since 2.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Blocks Initializer
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * 'script_handle' will be BlockName-editor-script (/ will be replaced with dash(-) in BlockName)
 * 
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
function wpls_register_guten_block() {

	$blocks = array(
						'logoshowcase' => array(
											'callback'		=> 'wpls_logo_slider',
											'script_handle'	=> 'wpls-logoshowcase-editor-script'
										)
					);

	foreach ($blocks as $block_key => $block_data) {

		register_block_type( __DIR__ . "/build/{$block_key}", array(
																'render_callback' => $block_data['callback'],
															));
		wp_set_script_translations( $block_data['script_handle'], 'wp-logo-showcase-responsive-slider-slider', WPLS_DIR . '/languages' );
	}
}
add_action( 'init', 'wpls_register_guten_block' );

/**
 * Adds a custom variable to the JS to allow a user in the block editor
 * to preview sensitive data.
 *
 * @since 1.0
 * @return void
 */
function wpls_editor_assets() {

	wp_localize_script( 'wp-block-editor', 'Wplsf_Block', array(
																'pro_demo_link' => 'https://demo.essentialplugin.com/prodemo/pro-logo-showcase-responsive-slider/',
																'free_demo_link' => 'https://demo.essentialplugin.com/logo-slider-plugin-demo/',
																'pro_link' => WPLS_PLUGIN_LINK_UNLOCK,
															));
}
add_action( 'enqueue_block_editor_assets', 'wpls_editor_assets' );

/**
 * Adds an extra category to the block inserter
 *
 *  @since 1.0
 */
function wpls_add_block_category( $categories ) {

	$guten_cats = wp_list_pluck( $categories, 'slug' );

	if( ! empty( $guten_cats ) && ! in_array( 'essp_guten_block', $guten_cats ) ) {

		$categories[] = array(
							'slug'	=> 'essp_guten_block',
							'title'	=> __('Essential Plugin Blocks', 'wp-logo-showcase-responsive-slider-slider'),
							'icon'	=> null,
						);
	}

	return $categories;
}
add_filter( 'block_categories_all', 'wpls_add_block_category' );