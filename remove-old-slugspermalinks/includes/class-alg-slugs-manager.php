<?php
/**
 * Slugs Manager - Main Class
 *
 * @version 2.9.0
 * @since   1.0.0
 *
 * @author WPFactory
 *
 * @package WPFactory\Slugs_Manager
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_Slugs_Manager' ) ) :

	/**
	 * Alg_Slugs_Manager class.
	 *
	 * @version 2.9.0
	 * @since   1.0.0
	 */
	final class Alg_Slugs_Manager {

		/**
		 * Plugin version.
		 *
		 * @version 2.3.0
		 * @since   2.3.0
		 *
		 * @var string
		 */
		public $version = ALG_SLUGS_MANAGER_VERSION;

		/**
		 * Core.
		 *
		 * @version 2.6.5
		 * @since   2.6.5
		 *
		 * @var Alg_Slugs_Manager_Core
		 */
		public $core;

		/**
		 * The single instance of the class.
		 *
		 * @version 2.9.0
		 * @since   2.4.0
		 *
		 * @var Alg_Slugs_Manager
		 */
		protected static $instance = null;

		/**
		 * Main Alg_Slugs_Manager Instance.
		 *
		 * Ensures only one instance of Alg_Slugs_Manager is loaded or can be loaded.
		 *
		 * @version 2.9.0
		 * @since   2.4.0
		 *
		 * @static
		 *
		 * @return Alg_Slugs_Manager
		 */
		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Alg_Slugs_Manager Constructor.
		 *
		 * @version 2.9.0
		 * @since   1.0.0
		 *
		 * @access public
		 *
		 * @todo (dev) Move *all* to `is_admin()`?
		 */
		public function __construct() {
			// Load libs.
			if ( is_admin() ) {
				require_once plugin_dir_path( ALG_SLUGS_MANAGER_FILE ) . 'vendor/autoload.php';
			}

			// Pro.
			if ( 'remove-old-slugs-pro.php' === basename( ALG_SLUGS_MANAGER_FILE ) ) {
				require_once plugin_dir_path( __FILE__ ) . 'pro/class-alg-slugs-manager-pro.php';
			}

			// Include required files.
			$this->includes();

			// Admin.
			if ( is_admin() ) {
				$this->admin();
			}

			// Action.
			do_action( 'alg_slugs_manager_plugin_loaded', ALG_SLUGS_MANAGER_FILE );
		}

		/**
		 * Includes.
		 *
		 * @version 2.8.0
		 * @since   2.4.0
		 */
		public function includes() {
			$this->core = require_once plugin_dir_path( __FILE__ ) . 'class-alg-slugs-manager-core.php';
		}

		/**
		 * Admin.
		 *
		 * @version 2.8.0
		 * @since   2.4.0
		 */
		public function admin() {
			// Action links.
			add_filter(
				'plugin_action_links_' . plugin_basename( ALG_SLUGS_MANAGER_FILE ),
				array( $this, 'action_links' )
			);

			// "Recommendations" page.
			add_action( 'init', array( $this, 'add_cross_selling_library' ) );

			// Settings.
			require_once plugin_dir_path( __FILE__ ) . 'settings/class-alg-slugs-manager-settings.php';

			// Version update.
			if ( get_option( 'alg_slugs_manager_plugin_version', '' ) !== $this->version ) {
				add_action( 'admin_init', array( $this, 'version_updated' ) );
			}
		}

		/**
		 * Action links.
		 *
		 * @version 2.8.0
		 * @since   2.0.0
		 *
		 * @param mixed $links Plugin action links.
		 *
		 * @return array
		 */
		public function action_links( $links ) {
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
		 * Add cross selling library.
		 *
		 * @version 2.8.0
		 * @since   2.8.0
		 */
		public function add_cross_selling_library() {
			if ( ! class_exists( '\WPFactory\WPFactory_Cross_Selling\WPFactory_Cross_Selling' ) ) {
				return;
			}

			$cross_selling = new \WPFactory\WPFactory_Cross_Selling\WPFactory_Cross_Selling();
			$cross_selling->setup( array( 'plugin_file_path' => ALG_SLUGS_MANAGER_FILE ) );
			$cross_selling->init();
		}

		/**
		 * Version updated.
		 *
		 * @version 2.4.0
		 * @since   2.4.0
		 */
		public function version_updated() {
			update_option( 'alg_slugs_manager_plugin_version', $this->version );
		}

		/**
		 * Plugin URL.
		 *
		 * @version 2.6.0
		 * @since   2.4.0
		 *
		 * @return string
		 */
		public function plugin_url() {
			return untrailingslashit( plugin_dir_url( ALG_SLUGS_MANAGER_FILE ) );
		}

		/**
		 * Plugin path.
		 *
		 * @version 2.6.0
		 * @since   2.4.0
		 *
		 * @return string
		 */
		public function plugin_path() {
			return untrailingslashit( plugin_dir_path( ALG_SLUGS_MANAGER_FILE ) );
		}
	}

endif;
