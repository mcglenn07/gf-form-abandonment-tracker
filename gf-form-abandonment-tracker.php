<?php
/**
 * Plugin Name: GF Form Abandonment Tracker
 * Description: Tracks visitors who start filling out a Gravity Forms form but never submit, and shows which fields cause the most drop-off.
 * Version: 1.0.0
 * Author: Glenn Tangalin
 * Text Domain: gf-form-abandonment-tracker
 * Requires Plugins: gravityforms
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'GFAT_VERSION', '1.0.0' );
define( 'GFAT_FILE', __FILE__ );
define( 'GFAT_DIR', plugin_dir_path( __FILE__ ) );
define( 'GFAT_URL', plugin_dir_url( __FILE__ ) );

require_once GFAT_DIR . 'includes/class-gfat-db.php';
require_once GFAT_DIR . 'includes/class-gfat-tracker.php';
require_once GFAT_DIR . 'includes/class-gfat-admin.php';

register_activation_hook( __FILE__, array( 'GFAT_DB', 'install' ) );

add_action( 'plugins_loaded', 'gfat_init' );

function gfat_init() {
	if ( ! class_exists( 'GFForms' ) ) {
		add_action( 'admin_notices', 'gfat_missing_gf_notice' );
		return;
	}

	new GFAT_Tracker();
	new GFAT_Admin();
}

function gfat_missing_gf_notice() {
	echo '<div class="notice notice-warning"><p>' .
		esc_html__( 'GF Form Abandonment Tracker requires Gravity Forms to be installed and active.', 'gf-form-abandonment-tracker' ) .
		'</p></div>';
}
