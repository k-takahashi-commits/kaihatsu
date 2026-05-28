<?php
/**
 * Role and capability management.
 *
 * @package EquipmentManagement
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Creates and updates plugin roles.
 */
class Equipment_Management_Roles {
	const ADMIN_ROLE    = 'equipment_admin';
	const OPERATOR_ROLE = 'equipment_operator';

	/**
	 * Capabilities granted to plugin administrators.
	 *
	 * @return array<int, string>
	 */
	public static function admin_capabilities() {
		return array(
			'equipment_view_dashboard',
			'equipment_view_items',
			'equipment_edit_items',
			'equipment_view_repairs',
			'equipment_edit_repairs',
			'equipment_export_csv',
			'equipment_import_csv',
			'equipment_manage_masters',
			'equipment_view_logs',
			'equipment_manage_users',
			'equipment_view_backup',
		);
	}

	/**
	 * Capabilities granted to equipment operators.
	 *
	 * @return array<int, string>
	 */
	public static function operator_capabilities() {
		return array(
			'equipment_view_dashboard',
			'equipment_view_items',
			'equipment_edit_items',
			'equipment_view_repairs',
			'equipment_edit_repairs',
			'equipment_export_csv',
		);
	}

	/**
	 * Registers plugin roles and capabilities.
	 *
	 * @return void
	 */
	public static function create_roles() {
		self::create_or_update_role(
			self::ADMIN_ROLE,
			'機材管理者',
			self::admin_capabilities()
		);

		self::create_or_update_role(
			self::OPERATOR_ROLE,
			'機材登録者',
			self::operator_capabilities()
		);

		self::grant_admin_caps_to_wp_administrators();
	}

	/**
	 * Adds role or updates existing role capabilities.
	 *
	 * @param string            $role_key Role key.
	 * @param string            $label Role label.
	 * @param array<int,string> $capabilities Capabilities.
	 * @return void
	 */
	private static function create_or_update_role( $role_key, $label, $capabilities ) {
		$role_caps = array( 'read' => true );

		foreach ( $capabilities as $capability ) {
			$role_caps[ $capability ] = true;
		}

		$role = get_role( $role_key );

		if ( ! $role ) {
			add_role( $role_key, $label, $role_caps );
			return;
		}

		foreach ( $role_caps as $capability => $grant ) {
			$role->add_cap( $capability, $grant );
		}
	}

	/**
	 * Gives WordPress administrators all plugin capabilities.
	 *
	 * @return void
	 */
	private static function grant_admin_caps_to_wp_administrators() {
		$role = get_role( 'administrator' );

		if ( ! $role ) {
			return;
		}

		foreach ( self::admin_capabilities() as $capability ) {
			$role->add_cap( $capability, true );
		}
	}
}
