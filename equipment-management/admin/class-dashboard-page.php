<?php
/**
 * Dashboard admin page.
 *
 * @package EquipmentManagement
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders the equipment dashboard page.
 */
class Equipment_Management_Dashboard_Page {
	/**
	 * Renders the page.
	 *
	 * @return void
	 */
	public static function render() {
		Equipment_Management_Permissions::require_capability( 'equipment_view_dashboard' );

		include equipment_management_path( 'admin/views/dashboard.php' );
	}
}
