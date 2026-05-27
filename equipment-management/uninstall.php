<?php
/**
 * Uninstall handler.
 *
 * @package EquipmentManagement
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'equipment_management_version' );
