<?php
/**
 * Plugin Name: IUC Syd Portal Features
 * Description: Company, communication and private-request features extending BuddyPress.
 * Version: 1.0.0
 * Author: IUC Syd fafsacd för webhook3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define(
	'IUC_PORTAL_FEATURES_PATH',
	plugin_dir_path( __FILE__ )
);

define(
	'IUC_PORTAL_FEATURES_URL',
	plugin_dir_url( __FILE__ )
);

require_once IUC_PORTAL_FEATURES_PATH . 'includes/private-requests.php';

/**
 * Load the plugin's frontend styles.
 */
function iuc_syd_features_assets() {
	$path = IUC_PORTAL_FEATURES_PATH . 'assets/features.css';

	wp_enqueue_style(
		'iuc-syd-features',
		IUC_PORTAL_FEATURES_URL . 'assets/features.css',
		array(),
		file_exists( $path ) ? (string) filemtime( $path ) : '1.0.0'
	);
}
add_action( 'wp_enqueue_scripts', 'iuc_syd_features_assets' );