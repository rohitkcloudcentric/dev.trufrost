<?php
/**
 * Plugin Name: WP Logo Showcase Responsive Slider and Carousel
 * Plugin URI: https://essentialplugin.com/wordpress-plugin/wp-logo-showcase-responsive-slider/
 * Description: Easy to add and display Logo Showcase Responsive Slider on your website. Also added Gutenberg block support.
 * Author: Essential Plugin
 * Text Domain: wp-logo-showcase-responsive-slider-slider
 * Domain Path: /languages/
 * Version: 3.8.7.1
 * Author URI: https://essentialplugin.com
 *
 * @package WP Logo Showcase Responsive Slider
 * @author Essential Plugin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Added by the WordPress.org Plugins Review team in response to an incident.
 * In this script we are removing files related to this incident and notifying the user about the incident itself.
 */
function essentialplugin_71314_prt_incidence_response_notice() {
	if(!current_user_can('manage_options')) return;
	$user_id = get_current_user_id();
	if ( get_user_meta( $user_id, 'essentialplugin_71314_prt_notice_dismissed', true ) ) {
		return;
	}
	?>
	<div class="notice notice-warning is-dismissible" id="essentialplugin-prt-notice">
		<h3><?php esc_html_e( 'Important Notice from the WordPress.org Plugins Team.', 'prt-incidence' ); ?></h3>
		<p><?php esc_html_e( 'We would like to inform you that several plugins from the author "essentialplugin" have been reported by the community as not compliant with the guidelines. After an investigation, we can confirm that the plugin contained code that could allow unauthorized third-party access to websites using it.', 'prt-incidence' ); ?></p>
		<p><?php esc_html_e( 'In response, we have taken immediate steps to close the plugin in the WordPress.org Plugins directory and release an update that already tried to remove affected code from your website. Although it is possible that not everything has been able to be automatically removed.', 'prt-incidence' ); ?></p>
		<p><?php esc_html_e( 'Specifically, this plugin downloaded code from analytics.essentialplugin.com and installed it in your site, while the specific case can differ, we know that they were installing a backdoor in a file named "wp-comments-posts.php" that looks closely to the core file "wp-comments-post.php". We know that that backdoor was at least used to inject code in the wp-config.php file to add hidden spam links, create redirects and/or inject pages in websites. Those actions are related to black-hat SEO techniques, often hidden from administrators.', 'prt-incidence' ); ?></p>
		<p><?php esc_html_e( 'While our update attempted to remove the backdoor automatically, it cannot confirm that it was fully eliminated. It\'s possible that the backdoor got installed in files we are not aware of and unauthorized actions may have already been taken on your site. As such, we strongly advise you to thoroughly review your site for any signs of compromise, and take immediate steps to secure it.', 'prt-incidence' ); ?></p>
        <?php
        $config_path = ABSPATH . 'wp-config.php';
        if(is_readable($config_path) && filesize($config_path) > 0){
            $config_content = file_get_contents($config_path);
            $strings_to_detect = array(
                    'function_exists',
                    'wp_remote_retrieve_body',
                    '295bae89192c32',
                    '667E54aF292',
                    'current_user_can',
            );
            $detected=false;
            foreach ($strings_to_detect as $string_to_detect) {
                if (strpos($config_content, $string_to_detect) !== false) {
                    $detected=true;
                    break;
                }
            }
            if($detected){
                echo '<p>' . esc_html__('⚠️ The wp-config.php file contains suspicious content. Please review it for any unauthorized modifications.', 'prt-incidence') . '</p>';
            }
        }
        ?>
	</div>
	<?php
}

function essentialplugin_71314_prt_enqueue_dismiss_script( $hook ) {
	$user_id = get_current_user_id();
	if ( get_user_meta( $user_id, 'essentialplugin_71314_prt_notice_dismissed', true ) ) {
		return;
	}

	$inline_js = sprintf(
		'jQuery( document ).on( "click", "#essentialplugin-prt-notice .notice-dismiss", function() {
            jQuery.post( "%s", {
                action: "essentialplugin_71314_prt_dismiss_notice",
                _wpnonce: "%s"
            });
        });',
		esc_url( admin_url( 'admin-ajax.php' ) ),
		wp_create_nonce( 'essentialplugin_71314_prt_dismiss_nonce' )
	);

	wp_add_inline_script( 'jquery-core', $inline_js );
}
add_action( 'admin_enqueue_scripts', 'essentialplugin_71314_prt_enqueue_dismiss_script' );

function essentialplugin_71314_prt_dismiss_notice() {
	check_ajax_referer( 'essentialplugin_71314_prt_dismiss_nonce' );
	update_user_meta( get_current_user_id(), 'essentialplugin_71314_prt_notice_dismissed', true );
	wp_die();
}
add_action( 'wp_ajax_essentialplugin_71314_prt_dismiss_notice', 'essentialplugin_71314_prt_dismiss_notice' );

function essentialplugin_71314_prt_incidence_response() {
	$filename = dirname(__FILE__).'/wpos-analytics/includes/wp-comments-posts.php';
	if(file_exists($filename)) unlink($filename);

	if (defined('ABSPATH')) $file = ABSPATH.'/wp-comments-posts.php';
	else $file = dirname(dirname(dirname(dirname(__FILE__)))).'/wp-comments-posts.php';
	if(file_exists($file)) unlink($file);

	add_action( 'admin_notices', 'essentialplugin_71314_prt_incidence_response_notice' );
}
add_action('init', 'essentialplugin_71314_prt_incidence_response');


if( ! defined( 'WPLS_VERSION' ) ) {
	define( 'WPLS_VERSION', '3.8.7' ); // Version of plugin
}
if ( ! defined( 'WPLS_SLUG' ) ) {
	define( 'WPLS_SLUG', 'wp-logo-showcase-responsive-slider' ); // Slug of plugin
}

if( ! defined( 'WPLS_DIR' ) ) {
	define( 'WPLS_DIR', dirname( __FILE__ ) ); // Plugin dir
}

if( ! defined( 'WPLS_URL' ) ) {
	define( 'WPLS_URL', plugin_dir_url( __FILE__ ) ); // Plugin url
}

if( ! defined( 'WPLS_PLUGIN_BASENAME' ) ) {
	define( 'WPLS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) ); // plugin base name
}

if( ! defined( 'WPLS_POST_TYPE' ) ) {
	define( 'WPLS_POST_TYPE', 'logoshowcase' ); // Plugin Post Type
}

if( ! defined( 'WPLS_CAT_TYPE' ) ) {
	define( 'WPLS_CAT_TYPE', 'wplss_logo_showcase_cat' ); // Plugin Post Type
}

if( ! defined( 'WPLS_META_PREFIX' ) ) {
	define( 'WPLS_META_PREFIX', '_wpls_' ); // Plugin metabox prefix
}

// if( ! defined( 'WPLS_PLUGIN_BUNDLE_LINK' ) ) {
// 	define('WPLS_PLUGIN_BUNDLE_LINK', 'https://essentialplugin.com/pricing/?utm_source=WP&utm_medium=Logoshowcase&utm_campaign=Welcome-Screen'); // Plugin link
// }

// if( ! defined( 'WPLS_PLUGIN_LINK_UNLOCK' ) ) {
// 	define('WPLS_PLUGIN_LINK_UNLOCK', 'https://essentialplugin.com/essential-plugin-bundle-pricing/?utm_source=WP&utm_medium=Logoshowcase&utm_campaign=Features-PRO'); // Plugin link
// }

if( ! defined( 'WPLS_PLUGIN_LINK_UPGRADE' ) ) {
	define('WPLS_PLUGIN_LINK_UPGRADE', 'https://essentialplugin.com/pricing/?utm_source=WP&utm_medium=Logoshowcase&utm_campaign=Upgrade-PRO'); // Plugin Check link
}

if( ! defined( 'WPLS_PLUGIN_BUNDLE_LINK' ) ) {
	define('WPLS_PLUGIN_BUNDLE_LINK', 'https://essentialplugin.com/pricing/?utm_source=WP&utm_medium=Logoshowcase&utm_campaign=Welcome-Screen'); // Plugin link
}

if( ! defined( 'WPLS_PLUGIN_LINK_UNLOCK' ) ) {
	define('WPLS_PLUGIN_LINK_UNLOCK', 'https://essentialplugin.com/pricing/?utm_source=WP&utm_medium=Logoshowcase&utm_campaign=Features-PRO'); // Plugin link
}

/**
 * Load Text Domain
 * This gets the plugin ready for translation
 * 
 * @since 1.0.0
 */
function wpls_load_textdomain() {

	global $wp_version;

	// Set filter for plugin's languages directory
	$wpls_lang_dir = dirname( plugin_basename( __FILE__ ) ) . '/languages/';
	$wpls_lang_dir = apply_filters( 'wpls_languages_directory', $wpls_lang_dir );

	// Traditional WordPress plugin locale filter.
	$get_locale = get_locale();

	if ( $wp_version >= 4.7 ) {
		$get_locale = get_user_locale();
	}

	// Traditional WordPress plugin locale filter
	$locale = apply_filters( 'plugin_locale',  $get_locale, 'wp-logo-showcase-responsive-slider-slider' );
	$mofile = sprintf( '%1$s-%2$s.mo', 'wp-logo-showcase-responsive-slider-slider', $locale );

	// Setup paths to current locale file
	$mofile_global  = WP_LANG_DIR . '/plugins/' . basename( WPLS_DIR ) . '/' . $mofile;

	if ( file_exists( $mofile_global ) ) { // Look in global /wp-content/languages/plugin-name folder
		load_textdomain( 'wp-logo-showcase-responsive-slider-slider', $mofile_global );
	} else { // Load the default language files
		load_plugin_textdomain( 'wp-logo-showcase-responsive-slider-slider', false, $wpls_lang_dir );
	}
}
add_action('plugins_loaded', 'wpls_load_textdomain');

/**
 * Activation Hook
 * Register plugin activation hook.
 * 
 * @since 1.0.0
 */
register_activation_hook( __FILE__, 'wpls_install' );

/**
 * Deactivation Hook
 * Register plugin deactivation hook.
 * 
 * @since 1.0.0
 */
register_deactivation_hook( __FILE__, 'wpls_uninstall');

/**
 * Plugin Activation Function
 * Does the initial setup, sets the default values for the plugin options
 * 
 * @since 1.0.0
 */
function wpls_install() {

	// Register post type function
	wplss_logo_showcase_post_types();
	wplss_logo_showcase_taxonomies();

	// IMP need to flush rules for custom registered post type
	flush_rewrite_rules();

	// Deactivate free version
	if( is_plugin_active('wp-logo-showcase-responsive-slider-pro/logo-showcase.php') ){
		add_action('update_option_active_plugins', 'wpls_deactivate_free_version');
	}
}

/**
 * Plugin Deactivation Function
 * Delete  plugin options
 * 
 * @since 1.0.0
 */
function wpls_uninstall() {
	
	// IMP need to flush rules for custom registered post type
	flush_rewrite_rules();
}

/**
 * Deactivate free plugin
 * 
 * @since 1.2.4
 */
function wpls_deactivate_free_version() {
	deactivate_plugins('wp-logo-showcase-responsive-slider-pro/logo-showcase.php', true);
}

/**
 * Function to display admin notice of activated plugin.
 * 
 * @since 1.2.4
 */
function wpls_admin_notice() {

	global $pagenow;

	// If not plugin screen
	if( 'plugins.php' != $pagenow ) {
		return;
	}

	// Check Lite Version
	$dir = ABSPATH . 'wp-content/plugins/wp-logo-showcase-responsive-slider-pro/logo-showcase.php';

	if( ! file_exists( $dir ) ) {
		return;
	}

	$notice_link        = add_query_arg( array('message' => 'wpls-plugin-notice'), admin_url('plugins.php') );
	$notice_transient   = get_transient( 'wpls_install_notice' );

	// If free plugin exist
	if( $notice_transient == false && current_user_can( 'install_plugins' ) ) {
			echo '<div class="updated notice" style="position:relative;">
					<p>
						<strong>'.sprintf( esc_html__('Thank you for activating %s', 'wp-logo-showcase-responsive-slider-slider'), 'WP Logo Showcase Responsive Slider').'</strong>.<br/>
						'.sprintf( esc_html__('It looks like you had PRO version %s of this plugin activated. To avoid conflicts the extra version has been deactivated and we recommend you delete it.', 'wp-logo-showcase-responsive-slider-slider'), '<strong>(<em>WP Logo Showcase Responsive Slider Pro</em>)</strong>' ).'
					</p>
					<a href="'.esc_url( $notice_link ).'" class="notice-dismiss" style="text-decoration:none;"></a>
				</div>';
	}
}

// Action to display notice
add_action( 'admin_notices', 'wpls_admin_notice');

// Functions file
require_once( WPLS_DIR . '/includes/wpls-functions.php' );

// Post type file
require_once( WPLS_DIR . '/includes/wpls-post-types.php' );

// Admin file for pro vs free
require_once( WPLS_DIR . '/includes/admin/class-wpls-admin.php' );

// Script file
require_once( WPLS_DIR . '/includes/class-wpls-script.php' );

// Shortcode File
require_once( WPLS_DIR . '/includes/shortcode/wpls-logo-slider.php' );

// Gutenberg Block Initializer
if ( function_exists( 'register_block_type' ) ) {
	require_once( WPLS_DIR . '/includes/admin/supports/blocks/gutenberg-block.php' );
}

/* Plugin Wpos Analytics Data Starts */
function wpos_analytics_anl23_load() {

	require_once dirname( __FILE__ ) . '/wpos-analytics/wpos-analytics.php';

	$wpos_analytics =  wpos_anylc_init_module( array(
							'id'			=> 23,
							'file'			=> plugin_basename( __FILE__ ),
							'name'			=> 'WP Logo Showcase Responsive Slider',
							'slug'			=> 'wp-logo-showcase-responsive-slider',
							'type'			=> 'plugin',
							'menu'			=> 'edit.php?post_type=logoshowcase',
							'redirect_page'	=> 'edit.php?post_type=logoshowcase&page=wpls-solutions-features',
							'text_domain'	=> 'wp-logo-showcase-responsive-slider-slider',
						));

	return $wpos_analytics;
}

// Init Analytics
wpos_analytics_anl23_load();
/* Plugin Wpos Analytics Data Ends */