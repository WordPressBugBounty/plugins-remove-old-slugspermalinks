<?php
/**
 * Slugs Manager - Main Class
 *
 * @version 2.8.0
 * @since   1.0.0
 *
 * @author  Algoritmika Ltd
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_Slugs_Manager' ) ) :

final class Alg_Slugs_Manager {

	/**
	 * Plugin version.
	 *
	 * @var   string
	 * @since 2.3.0
	 */
	public $version = ALG_SLUGS_MANAGER_VERSION;

	/**
	 * core.
	 *
	 * @since 2.6.5
	 */
	public $core;

	/**
	 * @var   Alg_Slugs_Manager The single instance of the class
	 * @since 2.4.0
	 */
	protected static $_instance = null;

	/**
	 * Main Alg_Slugs_Manager Instance.
	 *
	 * Ensures only one instance of Alg_Slugs_Manager is loaded or can be loaded.
	 *
	 * @version 2.4.0
	 * @since   2.4.0
	 *
	 * @static
	 * @return  Alg_Slugs_Manager - Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/*
	 * Alg_Slugs_Manager Constructor.
	 *
	 * @version 2.8.0
	 * @since   1.0.0
	 *
	 * @access  public
	 *
	 * @todo    (v2.8.0) Plugin Check
	 * @todo    (v2.8.0) code formatting
	 * @todo    (dev) move *all* to `is_admin()`?
	 */
	function __construct() {

		// Load libs
		if ( is_admin() ) {
			require_once plugin_dir_path( ALG_SLUGS_MANAGER_FILE ) . 'vendor/autoload.php';
		}

		// Set up localisation
		add_action( 'init', array( $this, 'localize' ) );

		// Pro
		if ( 'remove-old-slugs-pro.php' === basename( ALG_SLUGS_MANAGER_FILE ) ) {
			require_once plugin_dir_path( __FILE__ ) . 'pro/class-alg-slugs-manager-pro.php';
		}

		// Include required files
		$this->includes();

		// Admin
		if ( is_admin() ) {
			$this->admin();
		}

		// Action
		do_action( 'alg_slugs_manager_plugin_loaded', ALG_SLUGS_MANAGER_FILE );

	}

	/**
	 * localize.
	 *
	 * @version 2.6.0
	 * @since   2.5.0
	 */
	function localize() {
		load_plugin_textdomain(
			'remove-old-slugspermalinks',
			false,
			dirname( plugin_basename( ALG_SLUGS_MANAGER_FILE ) ) . '/langs/'
		);
	}

	/**
	 * includes.
	 *
	 * @version 2.8.0
	 * @since   2.4.0
	 */
	function includes() {
		$this->core = require_once plugin_dir_path( __FILE__ ) . 'class-alg-slugs-manager-core.php';
	}

	/**
	 * admin.
	 *
	 * @version 2.8.0
	 * @since   2.4.0
	 */
	function admin() {

		// Action links
		add_filter(
			'plugin_action_links_' . plugin_basename( ALG_SLUGS_MANAGER_FILE ),
			array( $this, 'action_links' )
		);

		// "Recommendations" page
		add_action( 'init', array( $this, 'add_cross_selling_library' ) );

		// Settings
		require_once plugin_dir_path( __FILE__ ) . 'settings/class-alg-slugs-manager-settings.php';

		// Version update
		if ( get_option( 'alg_slugs_manager_plugin_version', '' ) !== $this->version ) {
			add_action( 'admin_init', array( $this, 'version_updated' ) );
		}

	}

	/**
	 * action_links.
	 *
	 * @version 2.8.0
	 * @since   2.0.0
	 *
	 * @param   mixed $links
	 * @return  array
	 */
	function action_links( $links ) {
		$custom_links = array();

		$custom_links[] = '<a href="' . admin_url( 'admin.php?page=alg-slugs-manager' ) . '">' .
			esc_html__( 'Settings', 'remove-old-slugspermalinks' ) .
		'</a>';

		if ( 'remove-old-slugs.php' === basename( ALG_SLUGS_MANAGER_FILE ) ) {
			$custom_links[] = '<a target="_blank" style="font-weight: bold; color: green;" href="https://wpfactory.com/item/slugs-manager-wordpress-plugin/">' .
				esc_html__( 'Go Pro', 'remove-old-slugspermalinks' ) .
			'</a>';
		}

		return array_merge( $custom_links, $links );
	}

	/**
	 * add_cross_selling_library.
	 *
	 * @version 2.8.0
	 * @since   2.8.0
	 */
	function add_cross_selling_library() {

		if ( ! class_exists( '\WPFactory\WPFactory_Cross_Selling\WPFactory_Cross_Selling' ) ) {
			return;
		}

		$cross_selling = new \WPFactory\WPFactory_Cross_Selling\WPFactory_Cross_Selling();
		$cross_selling->setup( array( 'plugin_file_path' => ALG_SLUGS_MANAGER_FILE ) );
		$cross_selling->init();

	}

	/**
	 * version_updated.
	 *
	 * @version 2.4.0
	 * @since   2.4.0
	 */
	function version_updated() {
		update_option( 'alg_slugs_manager_plugin_version', $this->version );
	}

	/**
	 * plugin_url.
	 *
	 * @version 2.6.0
	 * @since   2.4.0
	 *
	 * @return  string
	 */
	function plugin_url() {
		return untrailingslashit( plugin_dir_url( ALG_SLUGS_MANAGER_FILE ) );
	}

	/**
	 * plugin_path.
	 *
	 * @version 2.6.0
	 * @since   2.4.0
	 *
	 * @return  string
	 */
	function plugin_path() {
		return untrailingslashit( plugin_dir_path( ALG_SLUGS_MANAGER_FILE ) );
	}

}

endif;
