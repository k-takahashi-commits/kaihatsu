<?php
/**
 * Master admin page.
 *
 * @package EquipmentManagement
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders master management page.
 */
class Equipment_Management_Master_Page {
	/**
	 * Handles add and update requests.
	 *
	 * @return void
	 */
	public static function handle_save() {
		Equipment_Management_Permissions::require_capability( 'equipment_manage_masters' );
		check_admin_referer( 'equipment_management_save_master' );

		$posted = wp_unslash( $_POST );
		$type   = isset( $posted['master_type'] ) ? sanitize_key( $posted['master_type'] ) : '';

		if ( ! Equipment_Management_DB::is_master_type( $type ) ) {
			self::redirect( $type, 'invalid' );
		}

		$result = Equipment_Management_DB::save_master_row(
			$type,
			array(
				'id'            => isset( $posted['master_id'] ) ? absint( $posted['master_id'] ) : 0,
				'name'          => isset( $posted['name'] ) ? $posted['name'] : '',
				'display_order' => isset( $posted['display_order'] ) ? $posted['display_order'] : 0,
				'is_active'     => isset( $posted['is_active'] ) ? 1 : 0,
				'phone'         => isset( $posted['phone'] ) ? $posted['phone'] : '',
				'email'         => isset( $posted['email'] ) ? $posted['email'] : '',
				'address'       => isset( $posted['address'] ) ? $posted['address'] : '',
				'is_closed'     => isset( $posted['is_closed'] ) ? 1 : 0,
			)
		);

		self::redirect( $type, false === $result ? 'error' : 'saved' );
	}

	/**
	 * Renders the page.
	 *
	 * @return void
	 */
	public static function render() {
		Equipment_Management_Permissions::require_capability( 'equipment_manage_masters' );

		$master_definitions = Equipment_Management_DB::master_definitions();
		$current_type       = isset( $_GET['master_type'] ) ? sanitize_key( wp_unslash( $_GET['master_type'] ) ) : 'locations';

		if ( ! isset( $master_definitions[ $current_type ] ) ) {
			$current_type = 'locations';
		}

		$edit_id  = isset( $_GET['edit_id'] ) ? absint( $_GET['edit_id'] ) : 0;
		$rows     = Equipment_Management_DB::get_master_rows( $current_type );
		$edit_row = Equipment_Management_DB::get_master_row( $current_type, $edit_id );
		$notice   = isset( $_GET['equipment_notice'] ) ? sanitize_key( wp_unslash( $_GET['equipment_notice'] ) ) : '';

		include equipment_management_path( 'admin/views/master-list.php' );
	}

	/**
	 * Redirects back to the current master screen.
	 *
	 * @param string $type Master type.
	 * @param string $notice Notice key.
	 * @return void
	 */
	private static function redirect( $type, $notice ) {
		$url = add_query_arg(
			array(
				'page'             => Equipment_Management_Admin_Menu::SLUG_MASTERS,
				'master_type'      => $type,
				'equipment_notice' => $notice,
			),
			admin_url( 'admin.php' )
		);

		wp_safe_redirect( $url );
		exit;
	}
}
