<?php
/**
 * Plugin deactivation tasks.
 *
 * @package EquipmentManagement
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles plugin deactivation.
 */
class Equipment_Management_Deactivator {
	/**
	 * Runs on plugin deactivation.
	 *
	 * Data, roles, and capabilities are intentionally left in place.
	 *
	 * @return void
	 */
	public static function deactivate() {
		flush_rewrite_rules();
	}
}
