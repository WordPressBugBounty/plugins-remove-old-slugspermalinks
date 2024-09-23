<?php
/**
 * Slugs Manager - Core Class
 *
 * @version 2.7.0
 * @since   2.4.0
 *
 * @author  Algoritmika Ltd
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_Slugs_Manager_Core' ) ) :

class Alg_Slugs_Manager_Core {

	/**
	 * old_slugs_deleted_num.
	 *
	 * @since 2.6.5
	 */
	public $old_slugs_deleted_num;

	/**
	 * Constructor.
	 *
	 * @version 2.5.1
	 * @since   2.4.0
	 */
	function __construct() {
		add_action( 'admin_init', array( $this, 'manage_old_slugs' ) );
		add_action( 'admin_init', array( $this, 'maybe_flush_rewrite_rules' ) );
	}

	/*
	 * maybe_flush_rewrite_rules.
	 *
	 * @version 2.7.0
	 * @since   2.5.1
	 */
	function maybe_flush_rewrite_rules() {
		if ( isset( $_REQUEST['alg_sm_flush_rewrite_rules'] ) ) {
			// Check nonce
			if (
				! isset( $_REQUEST['alg_sm_flush_rewrite_rules_nonce'] ) ||
				! wp_verify_nonce( $_REQUEST['alg_sm_flush_rewrite_rules_nonce'], 'alg-sm-flush-rewrite-rules' )
			) {
				add_action( 'admin_notices', array( $this, 'admin_notice_invalid_nonce' ) );
				return;
			}
			// Check user permissions
			if ( ! current_user_can( 'manage_options' ) ) {
				add_action( 'admin_notices', array( $this, 'admin_notice_invalid_user' ) );
				return;
			}
			// Flush rewrite rules
			flush_rewrite_rules();
			add_action( 'admin_notices', array( $this, 'admin_notice_rewrite_rules_flushed' ) );
		}
	}

	/*
	 * delete_old_slugs.
	 *
	 * @version 2.7.0
	 * @since   2.2.0
	 *
	 * @todo    (fix) selected old slugs: different old slugs for single post ID
	 */
	function delete_old_slugs( $post_ids = false ) {
		global $wpdb;
		$post_ids = ( $post_ids ? ' AND post_id IN (' . implode( ',', $post_ids ) . ')' : '' );
		$query = "DELETE FROM {$wpdb->prefix}postmeta WHERE meta_key = '_wp_old_slug'{$post_ids}";
		return $wpdb->get_results( $query );
	}

	/*
	 * get_old_slugs.
	 *
	 * @version 2.5.0
	 * @since   2.4.0
	 */
	function get_old_slugs() {
		global $wpdb;
		$query = "SELECT * FROM {$wpdb->prefix}postmeta WHERE meta_key = '_wp_old_slug' ORDER BY post_id";
		return $wpdb->get_results( $query );
	}

	/*
	 * manage_old_slugs.
	 *
	 * @version 2.7.0
	 * @since   2.0.0
	 *
	 * @todo    (dev) `$_POST` to `$_REQUEST`?
	 */
	function manage_old_slugs() {

		if (
			isset( $_POST['alg_slugs_manager_remove_old_slugs'] ) ||
			isset( $_POST['alg_slugs_manager_remove_selected_old_slugs'] )
		) {
			// Check nonce
			if (
				! isset( $_REQUEST['alg_sm_remove_old_slugs_nonce'] ) ||
				! wp_verify_nonce( $_REQUEST['alg_sm_remove_old_slugs_nonce'], 'alg-sm-remove-old-slugs' )
			) {
				add_action( 'admin_notices', array( $this, 'admin_notice_invalid_nonce' ) );
				return;
			}
			// Check user permissions
			if ( ! current_user_can( 'manage_options' ) ) {
				add_action( 'admin_notices', array( $this, 'admin_notice_invalid_user' ) );
				return;
			}
		}

		if ( isset( $_POST['alg_slugs_manager_remove_old_slugs'] ) ) {

			// Remove *all* old slugs
			$old_slugs     = $this->get_old_slugs();
			$num_old_slugs = count( $old_slugs );
			if ( $num_old_slugs > 0 ) {
				// Old slugs found
				$this->delete_old_slugs();
				$old_slugs_after_deletion    = $this->get_old_slugs();
				$this->old_slugs_deleted_num = ( $num_old_slugs - count( $old_slugs_after_deletion ) );
				add_action( 'admin_notices', array( $this, 'admin_notice_old_slugs_deleted' ) );
			}

		} elseif ( isset( $_POST['alg_slugs_manager_remove_selected_old_slugs'] ) ) {

			// Remove *selected* old slugs
			if ( ! empty( $_POST['alg_slugs_manager_post_ids'] ) ) {
				$post_ids = array_map( 'sanitize_text_field', $_POST['alg_slugs_manager_post_ids'] );
				$this->delete_old_slugs( $post_ids );
				$this->old_slugs_deleted_num = count( $post_ids );
				add_action( 'admin_notices', array( $this, 'admin_notice_old_slugs_deleted' ) );
			} else {
				add_action( 'admin_notices', array( $this, 'admin_notice_old_slugs_none_selected' ) );
			}

		}

	}

	/**
	 * admin_notice_invalid_user.
	 *
	 * @version 2.7.0
	 * @since   2.7.0
	 */
	function admin_notice_invalid_user() {
		?><div class="notice notice-error is-dismissible">
			<p><?php echo esc_html__( 'Invalid user.', 'remove-old-slugspermalinks' ); ?></p>
		</div><?php
	}

	/**
	 * admin_notice_invalid_nonce.
	 *
	 * @version 2.7.0
	 * @since   2.7.0
	 */
	function admin_notice_invalid_nonce() {
		?><div class="notice notice-error is-dismissible">
			<p><?php echo esc_html__( 'Invalid nonce.', 'remove-old-slugspermalinks' ); ?></p>
		</div><?php
	}

	/**
	 * admin_notice_rewrite_rules_flushed.
	 *
	 * @version 2.7.0
	 * @since   2.5.1
	 */
	function admin_notice_rewrite_rules_flushed() {
		?><div class="notice notice-success is-dismissible">
			<p><?php echo esc_html__( 'Rewrite rules flushed.', 'remove-old-slugspermalinks' ); ?></p>
		</div><?php
	}

	/**
	 * admin_notice_old_slugs_deleted.
	 *
	 * @version 2.7.0
	 * @since   2.4.0
	 */
	function admin_notice_old_slugs_deleted() {
		?><div class="notice notice-success is-dismissible">
			<p><?php printf( esc_html__( 'Removing old slugs from database finished! %s old slug(s) deleted.', 'remove-old-slugspermalinks' ),
				'<strong>' . $this->old_slugs_deleted_num . '</strong>' ); ?></p>
		</div><?php
	}

	/**
	 * admin_notice_old_slugs_none_selected.
	 *
	 * @version 2.7.0
	 * @since   2.5.0
	 */
	function admin_notice_old_slugs_none_selected() {
		?><div class="notice notice-warning is-dismissible">
			<p><?php echo esc_html__( 'No slugs selected.', 'remove-old-slugspermalinks' ); ?></p>
		</div><?php
	}

}

endif;

return new Alg_Slugs_Manager_Core();
