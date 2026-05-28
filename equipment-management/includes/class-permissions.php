<?php
/**
 * Permission checks.
 *
 * @package EquipmentManagement
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Centralizes current-user capability checks.
 */
class Equipment_Management_Permissions {
	/**
	 * Checks whether current user has a capability.
	 *
	 * @param string $capability Capability name.
	 * @return bool
	 */
	public static function current_user_can( $capability ) {
		return current_user_can( $capability );
	}

	/**
	 * Stops request execution when the current user lacks a capability.
	 *
	 * @param string $capability Capability name.
	 * @return void
	 */
	public static function require_capability( $capability ) {
		if ( self::current_user_can( $capability ) ) {
			return;
		}

		wp_die(
			esc_html__( 'この機能を利用する権限がありません。', 'equipment-management' ),
			esc_html__( '権限エラー', 'equipment-management' ),
			array( 'response' => 403 )
		);
	}
}
