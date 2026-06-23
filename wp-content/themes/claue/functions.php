<?php
/**
 * Theme constants definition and functions.
 *
 * @since   1.0.0
 * @package Claue
 */

// Constants definition
define( 'JAS_CLAUE_PATH', get_template_directory()     );
define( 'JAS_CLAUE_URL',  get_template_directory_uri() );
define( 'JAS_CLAUE_VERSION', '1.6.1' );

require JAS_CLAUE_PATH . '/core/init.php';

// Enqueue custom CSS
function trufrost_enqueue_custom_styles() {
	wp_enqueue_style( 'trufrost-custom-css', JAS_CLAUE_URL . '/custom.css', array(), filemtime( JAS_CLAUE_PATH . '/custom.css' ) );
}
add_action( 'wp_enqueue_scripts', 'trufrost_enqueue_custom_styles', 99 );