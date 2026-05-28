<?php
/**
 * Core plugin bootstrap.
 *
 * @package EquipmentManagement
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers WordPress hooks used by the plugin.
 */
class Equipment_Management_Plugin {
	/**
	 * Registers all plugin hooks.
	 *
	 * @return void
	 */
	public function run() {
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );
		add_action( 'admin_post_equipment_management_save_master', array( 'Equipment_Management_Master_Page', 'handle_save' ) );
		add_action( 'admin_post_equipment_management_save_equipment', array( 'Equipment_Management_Equipment_Page', 'handle_save' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
	}

	/**
	 * Loads translation files.
	 *
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'equipment-management',
			false,
			dirname( plugin_basename( EQUIPMENT_MANAGEMENT_FILE ) ) . '/languages'
		);
	}

	/**
	 * Registers admin menu pages.
	 *
	 * @return void
	 */
	public function register_admin_menu() {
		$menu = new Equipment_Management_Admin_Menu();
		$menu->register();
	}

	/**
	 * Enqueues shared admin assets.
	 *
	 * @return void
	 */
	public function enqueue_admin_assets() {
		wp_enqueue_style(
			'equipment-management-admin',
			EQUIPMENT_MANAGEMENT_URL . 'assets/css/admin.css',
			array(),
			EQUIPMENT_MANAGEMENT_VERSION
		);

		wp_enqueue_script(
			'equipment-management-admin',
			EQUIPMENT_MANAGEMENT_URL . 'assets/js/admin.js',
			array(),
			EQUIPMENT_MANAGEMENT_VERSION,
			true
		);
	}
}
