<?php
/**
 * Admin menu registration.
 *
 * @package EquipmentManagement
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers equipment management admin pages.
 */
class Equipment_Management_Admin_Menu {
	const SLUG_DASHBOARD     = 'equipment-management';
	const SLUG_ITEMS         = 'equipment-management-items';
	const SLUG_ITEM_NEW      = 'equipment-management-item-new';
	const SLUG_ITEM_DETAIL   = 'equipment-management-item-detail';
	const SLUG_REPAIRS       = 'equipment-management-repairs';
	const SLUG_REPAIR_NEW    = 'equipment-management-repair-new';
	const SLUG_REPAIR_DETAIL = 'equipment-management-repair-detail';
	const SLUG_MASTERS       = 'equipment-management-masters';

	/**
	 * Registers top-level and submenu pages.
	 *
	 * @return void
	 */
	public function register() {
		add_menu_page(
			__( '機材管理', 'equipment-management' ),
			__( '機材管理', 'equipment-management' ),
			'equipment_view_dashboard',
			self::SLUG_DASHBOARD,
			array( 'Equipment_Management_Dashboard_Page', 'render' ),
			'dashicons-archive',
			26
		);

		add_submenu_page(
			self::SLUG_DASHBOARD,
			__( 'ダッシュボード', 'equipment-management' ),
			__( 'ダッシュボード', 'equipment-management' ),
			'equipment_view_dashboard',
			self::SLUG_DASHBOARD,
			array( 'Equipment_Management_Dashboard_Page', 'render' )
		);

		add_submenu_page(
			self::SLUG_DASHBOARD,
			__( '機材一覧', 'equipment-management' ),
			__( '機材一覧', 'equipment-management' ),
			'equipment_view_items',
			self::SLUG_ITEMS,
			array( 'Equipment_Management_Equipment_Page', 'render_list' )
		);

		add_submenu_page(
			self::SLUG_DASHBOARD,
			__( '機材登録', 'equipment-management' ),
			__( '機材登録', 'equipment-management' ),
			'equipment_edit_items',
			self::SLUG_ITEM_NEW,
			array( 'Equipment_Management_Equipment_Page', 'render_form' )
		);

		add_submenu_page(
			null,
			__( '機材詳細', 'equipment-management' ),
			__( '機材詳細', 'equipment-management' ),
			'equipment_view_items',
			self::SLUG_ITEM_DETAIL,
			array( 'Equipment_Management_Equipment_Page', 'render_detail' )
		);

		add_submenu_page(
			self::SLUG_DASHBOARD,
			__( '修理一覧', 'equipment-management' ),
			__( '修理一覧', 'equipment-management' ),
			'equipment_view_repairs',
			self::SLUG_REPAIRS,
			array( 'Equipment_Management_Repair_Page', 'render_list' )
		);

		add_submenu_page(
			self::SLUG_DASHBOARD,
			__( '修理起票', 'equipment-management' ),
			__( '修理起票', 'equipment-management' ),
			'equipment_edit_repairs',
			self::SLUG_REPAIR_NEW,
			array( 'Equipment_Management_Repair_Page', 'render_form' )
		);

		add_submenu_page(
			null,
			__( '修理詳細', 'equipment-management' ),
			__( '修理詳細', 'equipment-management' ),
			'equipment_view_repairs',
			self::SLUG_REPAIR_DETAIL,
			array( 'Equipment_Management_Repair_Page', 'render_detail' )
		);

		add_submenu_page(
			self::SLUG_DASHBOARD,
			__( 'マスター管理', 'equipment-management' ),
			__( 'マスター管理', 'equipment-management' ),
			'equipment_manage_masters',
			self::SLUG_MASTERS,
			array( 'Equipment_Management_Master_Page', 'render' )
		);
	}
}
