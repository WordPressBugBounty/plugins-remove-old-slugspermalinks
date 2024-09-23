<?php
/**
 * Slugs Manager - Settings Class
 *
 * @version 2.7.0
 * @since   2.4.0
 *
 * @author  Algoritmika Ltd
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_Slugs_Manager_Settings' ) ) :

class Alg_Slugs_Manager_Settings {

	/**
	 * Constructor.
	 *
	 * @version 2.5.0
	 * @since   2.4.0
	 *
	 * @todo    (dev) core refactoring?
	 */
	function __construct() {
		add_action( 'admin_menu',   array( $this, 'add_plugin_options_page' ) );
		add_action( 'admin_init',   array( $this, 'save_settings' ) );
		add_action( 'admin_footer', array( $this, 'add_select_all_script' ), PHP_INT_MAX );
	}

	/**
	 * add_select_all_script.
	 *
	 * @version 2.5.0
	 * @since   2.5.0
	 *
	 * @todo    (dev) make it better, like the standard WP checkbox
	 * @todo    (dev) move it to a separate JS file?
	 */
	function add_select_all_script() {
		?><script>
			if ( jQuery ) {
				jQuery( '#alg-slugs-manager-select-all' ).change( function () {
					if ( jQuery( this ).prop( 'checked' ) ) {
						jQuery( 'input.alg-slugs-manager-post-checkbox' ).prop( 'checked', true );
					} else {
						jQuery( 'input.alg-slugs-manager-post-checkbox' ).prop( 'checked', false );
					}
				} );
				jQuery( '#alg-slugs-manager-select-all' ).trigger( 'change' );
			}
		</script><?php
	}

	/**
	 * save_settings.
	 *
	 * @version 2.7.0
	 * @since   2.4.0
	 *
	 * @todo    (fix) `alg_slugs_manager_save_settings_crons`: `cron_unschedule_the_event()`: when "current time" > "event time" the event is not unscheduled?
	 */
	function save_settings() {

		if (
			isset( $_POST['alg_remove_old_slugs_on_save_post'] ) ||
			isset( $_POST['alg_remove_old_slugs_crons'] )
		) {
			// Check user permissions
			if ( ! current_user_can( 'manage_options' ) ) {
				add_action( 'admin_notices', array( alg_slugs_manager()->core, 'admin_notice_invalid_user' ) );
				return;
			}
		}

		// Clean Up on Save Post
		if ( isset( $_POST['alg_remove_old_slugs_on_save_post'] ) ) {
			// Check nonce
			if (
				! isset( $_REQUEST['alg_sm_on_save_post_nonce'] ) ||
				! wp_verify_nonce( $_REQUEST['alg_sm_on_save_post_nonce'], 'alg-sm-on-save-post' )
			) {
				add_action( 'admin_notices', array( alg_slugs_manager()->core, 'admin_notice_invalid_nonce' ) );
				return;
			}
			update_option( 'alg_remove_old_slugs_on_save_post_enabled', sanitize_text_field( $_POST['alg_remove_old_slugs_on_save_post_enabled'] ) );
		}

		// Scheduled Clean Ups
		if ( isset( $_POST['alg_remove_old_slugs_crons'] ) ) {
			// Check nonce
			if (
				! isset( $_REQUEST['alg_sm_crons_nonce'] ) ||
				! wp_verify_nonce( $_REQUEST['alg_sm_crons_nonce'], 'alg-sm-crons' )
			) {
				add_action( 'admin_notices', array( alg_slugs_manager()->core, 'admin_notice_invalid_nonce' ) );
				return;
			}
			update_option( 'alg_remove_old_slugs_cron_interval', sanitize_text_field( $_POST['alg_remove_old_slugs_crons_interval'] ) );
			do_action( 'alg_slugs_manager_save_settings_crons' );
		}

	}

	/*
	 * add_plugin_options_page.
	 *
	 * @version 2.7.0
	 * @since   1.0.0
	 */
	function add_plugin_options_page() {
		add_submenu_page(
			'tools.php',
			esc_html__( 'Slugs Manager', 'remove-old-slugspermalinks' ),
			esc_html__( 'Slugs Manager', 'remove-old-slugspermalinks' ),
			'manage_options',
			'alg-slugs-manager',
			array( $this, 'create_admin_page' )
		);
	}

	/*
	 * create_admin_page.
	 *
	 * @version 2.7.0
	 * @since   1.0.0
	 *
	 * @todo    (dev) split it into a separate sections
	 */
	function create_admin_page() {
		$html  = '';

		// Header
		$html .= '<div class="wrap">';
		$html .= '<h1>' . esc_html__( 'Slugs Manager', 'remove-old-slugspermalinks' ) . '</h1>';

		// Old slugs
		$html .= $this->display_old_slugs_table();

		// Old slugs: Automatic clean ups
		$html .= $this->display_automatic_clean_ups_options();

		// Regenerate slugs
		$html .= $this->display_regenerate_slugs_options();

		// Extra tools
		$html .= $this->display_extra_tools_options();

		// The end
		$html .= '</div>';

		echo $html;
	}

	/**
	 * display_old_slugs_table.
	 *
	 * @version 2.7.0
	 * @since   2.4.0
	 */
	function display_old_slugs_table() {
		$html  = '';
		$html .= '<hr>' . '<h2>' . '<span class="dashicons dashicons-search" style="color:gray;"></span> ' . __( 'Old Slugs', 'remove-old-slugspermalinks' ) . '</h2>' .
			'<p><em>' . __( 'This tool removes old slugs (permalinks) from database.', 'remove-old-slugspermalinks' ) . '</em></p>';
		$old_slugs     = alg_slugs_manager()->core->get_old_slugs();
		$num_old_slugs = count( $old_slugs );
		if ( $num_old_slugs > 0 ) {
			$table_data   = array();
			$table_data[] = array(
				'<input type="checkbox" id="alg-slugs-manager-select-all">',
				'#',
				__( 'Old Slug', 'remove-old-slugspermalinks' ),
				__( 'Post ID', 'remove-old-slugspermalinks' ),
				__( 'Post Title', 'remove-old-slugspermalinks' ),
				__( 'Post Type', 'remove-old-slugspermalinks' ),
				__( 'Current Slug', 'remove-old-slugspermalinks' ),
			);
			$i = 0;
			foreach ( $old_slugs as $old_slug ) {
				$i++;
				$post_type    = get_post_type( $old_slug->post_id );
				$post_title   = get_the_title( $old_slug->post_id );
				$current_slug = get_post( $old_slug->post_id );
				$current_slug = $current_slug->post_name;
				$table_data[] = array(
					'<input type="checkbox" class="alg-slugs-manager-post-checkbox" name="alg_slugs_manager_post_ids[]" value="' . $old_slug->post_id . '">',
					$i,
					$old_slug->meta_value,
					'<a href="' . admin_url( 'post.php?post=' . $old_slug->post_id . '&action=edit' ) . '" target="_blank">' . $old_slug->post_id . '</a>',
					'<a href="' . get_the_permalink( $old_slug->post_id ) . '" target="_blank">' . $post_title . '</a>',
					$post_type,
					$current_slug,
				);
			}
			$buttons = '<p>' .
					'<input class="button-primary" type="submit" name="alg_slugs_manager_remove_selected_old_slugs" onclick="return confirm(\'' .
						__( 'Are you sure?', 'remove-old-slugspermalinks' ) . '\')" value="' . __( 'Remove selected old slugs', 'remove-old-slugspermalinks' ) . '"/>' . ' ' .
					'<input class="button-primary" type="submit" name="alg_slugs_manager_remove_old_slugs" onclick="return confirm(\'' .
						__( 'Are you sure?', 'remove-old-slugspermalinks' ) . '\')" value="' . __( 'Remove all old slugs', 'remove-old-slugspermalinks' ) . '"/>' . ' ' .
					'<a class="button" href="' . admin_url( 'tools.php?page=alg-slugs-manager' ) . '">' .
						__( 'Refresh list', 'remove-old-slugspermalinks' ) . '</a>' .
				'</p>';
			$html .= '<p>' . sprintf( __( '%s old slug(s) found.', 'remove-old-slugspermalinks' ), '<strong>' . $num_old_slugs . '</strong>' ) . '</p>' .
				'<form method="post" action="">' .
					$buttons .
					$this->get_table_html( $table_data, array( 'table_class' => 'widefat striped', 'table_heading_type' => 'none' ) ) .
					$buttons .
					'<input type="hidden" name="alg_sm_remove_old_slugs_nonce" value="' . esc_attr( wp_create_nonce( 'alg-sm-remove-old-slugs' ) ) . '">' .
				'</form>';
		} else {
			// No old slugs found
			$html .= '<p><strong>' . __( 'No old slugs found in database.', 'remove-old-slugspermalinks' ) . '</strong></p>' .
				'<p>' .
					'<a class="button" href="' . admin_url( 'tools.php?page=alg-slugs-manager' ) . '">' .
						__( 'Refresh list', 'remove-old-slugspermalinks' ) . '</a>' .
				'</p>';
		}
		return $html;
	}

	/*
	 * display_automatic_clean_ups_options.
	 *
	 * @version 2.7.0
	 * @since   2.4.0
	 */
	function display_automatic_clean_ups_options() {
		$html  = '';
		// Header
		$html .= '<h4>' . __( 'Automatic Clean Ups', 'remove-old-slugspermalinks' ) . '</h4>';
		$html .= apply_filters( 'alg_slugs_manager_core_settings', '<h4 style="padding: 10px; background-color: white;">' . sprintf(
			__( 'You will need %s plugin to enable automatic old slugs clean ups.', 'remove-old-slugspermalinks' ),
				'<a href="https://wpfactory.com/item/slugs-manager-wordpress-plugin/" target="_blank">' .
					__( 'Slugs Manager Pro', 'remove-old-slugspermalinks' ) . '</a>' ) . '</h4>' );
		if ( isset( $_GET['alg_debug'] ) && defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) {
			$html .= '<h4 style="padding: 20px; background-color: #dddddd;">' .
				sprintf( __( '%s is set to %s in your %s file - "Scheduled Clean Ups" won\'t work.', 'remove-old-slugspermalinks' ),
					'<code>DISABLE_WP_CRON</code>', '<code>true</code>', '<code>wp-config.php</code>' ) .
			'</h4>';
		}
		// Scheduled clean ups
		$form_crons  = '';
		$form_crons .= '<form method="post" action="">';
		$intervals   = array(
			'disabled'   => __( 'Disabled', 'remove-old-slugspermalinks' ),
			'minutely'   => __( 'Every minute', 'remove-old-slugspermalinks' ),
			'hourly'     => __( 'Hourly', 'remove-old-slugspermalinks' ),
			'twicedaily' => __( 'Twice daily', 'remove-old-slugspermalinks' ),
			'daily'      => __( 'Daily', 'remove-old-slugspermalinks' ),
			'weekly'     => __( 'Weekly', 'remove-old-slugspermalinks' ),
		);
		$form_crons .= '<select style="width:150px;" name="alg_remove_old_slugs_crons_interval" id="alg_remove_old_slugs_crons_interval"' .
			apply_filters( 'alg_slugs_manager_core_settings', 'disabled' ). '>';
		$selected = esc_attr( get_option( 'alg_remove_old_slugs_cron_interval', 'disabled' ) );
		foreach ( $intervals as $interval_id => $interval_desc ) {
			$form_crons .= '<option value="' . $interval_id . '" ' . selected( $selected, $interval_id, false ) . '>' . $interval_desc . '</option>';
		}
		$form_crons .= '</select>' . ' ';
		$form_crons .= '<input class="button-primary" type="submit" name="alg_remove_old_slugs_crons" value="' . __( 'Save', 'remove-old-slugspermalinks' ) . '"' .
			apply_filters( 'alg_slugs_manager_core_settings', 'disabled' ). '/>';
		$form_crons .= '<input type="hidden" name="alg_sm_crons_nonce" value="' . esc_attr( wp_create_nonce( 'alg-sm-crons' ) ) . '">';
		$form_crons .= '</form>';
		$cron_info = '';
		if ( wp_next_scheduled( 'alg_remove_old_slugs_cron' ) ) {
			$cron_info .= '<br><em>' . sprintf( __( 'Next old slugs clean up is scheduled on %s. Current time is %s.', 'remove-old-slugspermalinks' ),
				'<code>' . date_i18n( 'Y-m-d H:i:s', wp_next_scheduled( 'alg_remove_old_slugs_cron' ) ) . '</code>',
				'<code>' . date_i18n( 'Y-m-d H:i:s', time() ) . '</code>' ) . '</em>';
		}
		// Clean up on save post
		$form_on_save_post  = '';
		$form_on_save_post .= '<form method="post" action="">';
		$form_on_save_post .= '<select style="width:150px;" name="alg_remove_old_slugs_on_save_post_enabled" id="alg_remove_old_slugs_on_save_post_enabled"' .
			apply_filters( 'alg_slugs_manager_core_settings', 'disabled' ). '>';
		$selected = esc_attr( get_option( 'alg_remove_old_slugs_on_save_post_enabled', 'no' ) );
		$form_on_save_post .= '<option value="no" '  . selected( $selected, 'no',  false ) . '>' . __( 'No', 'remove-old-slugspermalinks' )  . '</option>';
		$form_on_save_post .= '<option value="yes" ' . selected( $selected, 'yes', false ) . '>' . __( 'Yes', 'remove-old-slugspermalinks' ) . '</option>';
		$form_on_save_post .= '</select>' . ' ';
		$form_on_save_post .= '<input class="button-primary" type="submit" name="alg_remove_old_slugs_on_save_post" value="' .
			__( 'Save', 'remove-old-slugspermalinks' ) . '"' . apply_filters( 'alg_slugs_manager_core_settings', 'disabled' ). '/>';
		$form_on_save_post .= '<input type="hidden" name="alg_sm_on_save_post_nonce" value="' . esc_attr( wp_create_nonce( 'alg-sm-on-save-post' ) ) . '">';
		$form_on_save_post .= '</form>';
		// Final output
		$table_data = array(
			array(
				'<strong>' . __( 'Scheduled Clean Ups', 'remove-old-slugspermalinks' ) . '</strong>',
				'<em>' . sprintf( __( 'Set old slugs to be cleared periodically (%s).', 'remove-old-slugspermalinks' ), implode( ', ', $intervals ) ) . '</em>' .
					$cron_info,
				$form_crons,
			),
			array(
				'<strong>' . __( 'Clean Up on Save Post', 'remove-old-slugspermalinks' ) . '</strong>',
				'<em>' . __( 'Set old slugs to be cleared automatically, when post is saved.', 'remove-old-slugspermalinks' ) . '</em>',
				$form_on_save_post,
			),
		);
		$html .= $this->get_table_html( $table_data, array( 'table_class' => 'widefat striped', 'table_heading_type' => 'none',
			'columns_styles' => array( 'width:20%;', 'width:40%;', 'width:20%;' ) ) );
		return $html;
	}

	/*
	 * display_regenerate_slugs_options.
	 *
	 * @version 2.7.0
	 * @since   2.4.0
	 *
	 * @todo    (desc) `$post_types`: better description, styling?
	 */
	function display_regenerate_slugs_options() {
		$html  = '';
		// Header
		$html .= '<hr>' . '<h2>' . '<span class="dashicons dashicons-image-rotate" style="color:gray;"></span> ' . __( 'Regenerate Slugs', 'remove-old-slugspermalinks' ) . '</h2>' .
			apply_filters( 'alg_slugs_manager_core_settings', '<h4 style="padding: 10px; background-color: white;">' . sprintf(
			__( 'You will need %s plugin to enable slugs regeneration.', 'remove-old-slugspermalinks' ),
				'<a href="https://wpfactory.com/item/slugs-manager-wordpress-plugin/" target="_blank">' .
					__( 'Slugs Manager Pro', 'remove-old-slugspermalinks' ) . '</a>' ) . '</h4>' );
		// Post types
		$post_types_value   = get_option( 'alg_sm_regenerate_slugs_post_types', array( 'post' ) );
		$post_types_options = '';
		foreach ( get_post_types( array( 'exclude_from_search' => false ) ) as $post_type ) {
			$post_types_options .= '<option value="' . $post_type . '"' . selected( in_array( $post_type, $post_types_value ), true, false ) . '>' . $post_type . '</option>';
		}
		$post_types = '<select style="width:150px;" multiple name="alg_sm_regenerate_slugs_post_types[]">' . $post_types_options . '</select>';
		// Form
		$form_regenerate  = '';
		$form_regenerate .= '<form method="post" action="">';
		$form_regenerate .= $post_types . ' ';
		$form_regenerate .= '<input class="button-primary" type="submit" name="alg_remove_old_slugs_regenerate_slugs"' .
			' onclick="return confirm(\'' . __( 'There is no undo for this action.', 'remove-old-slugspermalinks' ) . ' ' .
			__( 'Are you sure?', 'remove-old-slugspermalinks' ) . '\')"' . ' value="' . __( 'Regenerate', 'remove-old-slugspermalinks' ) . '"' .
			apply_filters( 'alg_slugs_manager_core_settings', 'disabled' ). '/>';
		$form_regenerate .= '<input type="hidden" name="alg_sm_regenerate_slugs_nonce" value="' . esc_attr( wp_create_nonce( 'alg-sm-regenerate-slugs' ) ) . '">';
		$form_regenerate .= '</form>';
		// Final output
		$table_data = array(
			array(
				'<strong>' . __( 'Regenerate Slugs', 'remove-old-slugspermalinks' ) . '</strong>',
				'<em>' . __( 'Regenerate slug from <strong>title</strong> for all posts.', 'remove-old-slugspermalinks' ) . '</em>',
				$form_regenerate,
			),
		);
		$html .= $this->get_table_html( $table_data, array( 'table_class' => 'widefat striped', 'table_heading_type' => 'none',
			'columns_styles' => array( 'width:20%;', 'width:40%;', 'width:20%;' ) ) );
		return $html;
	}

	/*
	 * display_extra_tools_options.
	 *
	 * @version 2.7.0
	 * @since   2.5.1
	 *
	 * @todo    (desc) Flush rewrite rules: better desc, e.g., "what is a rewrite rule?", "why do we need to flush them?", etc.
	 */
	function display_extra_tools_options() {
		$html = '<hr>' . '<h2>' . '<span class="dashicons dashicons-admin-tools" style="color:gray;"></span> ' . esc_html__( 'Extra Tools', 'remove-old-slugspermalinks' ) . '</h2>';
		$table_data = array(
			array(
				'<strong>' . esc_html__( 'Flush Rewrite Rules', 'remove-old-slugspermalinks' ) . '</strong>',
				'<em>' . esc_html__( 'Remove rewrite rules and then recreate rewrite rules.', 'remove-old-slugspermalinks' ) . '</em>',
				'<form method="post" action="">' .
					'<input class="button-primary" type="submit" name="alg_sm_flush_rewrite_rules" value="' . esc_html__( 'Flush', 'remove-old-slugspermalinks' ) . '">' .
					'<input type="hidden" name="alg_sm_flush_rewrite_rules_nonce" value="' . esc_attr( wp_create_nonce( 'alg-sm-flush-rewrite-rules' ) ) . '">' .
				'</form>',
			),
		);
		$html .= $this->get_table_html( $table_data, array( 'table_class' => 'widefat striped', 'table_heading_type' => 'none',
			'columns_styles' => array( 'width:20%;', 'width:40%;', 'width:20%;' ) ) );
		return $html;
	}

	/**
	 * get_table_html.
	 *
	 * @version 2.7.0
	 * @since   2.0.0
	 */
	function get_table_html( $data, $args = array() ) {
		$defaults = array(
			'table_class'        => '',
			'table_style'        => '',
			'table_heading_type' => 'horizontal',
			'columns_classes'    => array(),
			'columns_styles'     => array(),
		);
		$args        = array_merge( $defaults, $args );
		$table_class = ( '' == $args['table_class'] ) ? '' : ' class="' . $args['table_class'] . '"';
		$table_style = ( '' == $args['table_style'] ) ? '' : ' style="' . $args['table_style'] . '"';
		$html        = '';
		$html       .= '<table' . $table_class . $table_style . '>';
		$html       .= '<tbody>';
		foreach( $data as $row_number => $row ) {
			$html .= '<tr>';
			foreach( $row as $column_number => $value ) {
				$th_or_td     = ( ( 0 === $row_number && 'horizontal' === $args['table_heading_type'] ) ||
					( 0 === $column_number && 'vertical' === $args['table_heading_type'] ) ) ? 'th' : 'td';
				$column_class = ( ! empty( $args['columns_classes'] ) && isset( $args['columns_classes'][ $column_number ] ) ) ?
					' class="' . $args['columns_classes'][ $column_number ] . '"' : '';
				$column_style = ( ! empty( $args['columns_styles'] )  && isset( $args['columns_styles'][ $column_number ] ) )  ?
					' style="' . $args['columns_styles'][ $column_number ]  . '"' : '';
				$html        .= "<{$th_or_td}{$column_class}{$column_style}>{$value}</{$th_or_td}>";
			}
			$html .= '</tr>';
		}
		$html .= '</tbody>';
		$html .= '</table>';
		return $html;
	}

}

endif;

return new Alg_Slugs_Manager_Settings();
