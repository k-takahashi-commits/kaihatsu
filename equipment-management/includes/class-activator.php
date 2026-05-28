<?php
/**
 * Plugin activation tasks.
 *
 * @package EquipmentManagement
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles plugin activation.
 */
class Equipment_Management_Activator {
	/**
	 * Runs on plugin activation.
	 *
	 * @return void
	 */
	public static function activate() {
		Equipment_Management_DB::create_tables();
		Equipment_Management_DB::seed_defaults();
		Equipment_Management_Roles::create_roles();

		update_option( 'equipment_management_version', EQUIPMENT_MANAGEMENT_VERSION );
	}
}
