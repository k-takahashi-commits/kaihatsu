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
	 * DB tables and roles will be created in later implementation steps.
	 *
	 * @return void
	 */
	public static function activate() {
		update_option( 'equipment_management_version', EQUIPMENT_MANAGEMENT_VERSION );
	}
}
