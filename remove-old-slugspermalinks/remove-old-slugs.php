<?php
/*
Plugin Name: Slugs Manager: Delete Old Permalinks from WordPress Database
Plugin URI: https://wpfactory.com/item/slugs-manager-wordpress-plugin/
Description: Plugin helps you manage slugs (permalinks) in WordPress, for example, remove old slugs from database.
Version: 2.8.0
Author: WPFactory
Author URI: https://wpfactory.com
Requires at least: 3.5.1
Text Domain: remove-old-slugspermalinks
Domain Path: /langs
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

defined( 'ABSPATH' ) || exit;

if ( 'remove-old-slugs.php' === basename( __FILE__ ) ) {
	/**
	 * Check if Pro plugin version is activated.
	 *
	 * @version 2.6.0
	 * @since   2.6.0
	 */
	$plugin = 'remove-old-slugs-pro/remove-old-slugs-pro.php';
	if (
		in_array( $plugin, (array) get_option( 'active_plugins', array() ), true ) ||
		(
			is_multisite() &&
			array_key_exists( $plugin, (array) get_site_option( 'active_sitewide_plugins', array() ) )
		)
	) {
		return;
	}
}

defined( 'ALG_SLUGS_MANAGER_VERSION' ) || define( 'ALG_SLUGS_MANAGER_VERSION', '2.8.0' );

defined( 'ALG_SLUGS_MANAGER_FILE' ) || define( 'ALG_SLUGS_MANAGER_FILE', __FILE__ );

require_once plugin_dir_path( __FILE__ ) . 'includes/class-alg-slugs-manager.php';

if ( ! function_exists( 'alg_slugs_manager' ) ) {
	/**
	 * Returns the main instance of Alg_Slugs_Manager to prevent the need to use globals.
	 *
	 * @version 2.4.0
	 * @since   2.4.0
	 */
	function alg_slugs_manager() {
		return Alg_Slugs_Manager::instance();
	}
}

add_action( 'plugins_loaded', 'alg_slugs_manager' );
