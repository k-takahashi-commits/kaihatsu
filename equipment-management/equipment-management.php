<?php
/**
 * Plugin Name: Equipment Management
 * Description: Equipment and repair management for WordPress admin.
 * Version: 0.1.0
 * Author: Equipment Management Team
 * Text Domain: equipment-management
 * Domain Path: /languages
 *
 * @package EquipmentManagement
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'EQUIPMENT_MANAGEMENT_VERSION', '0.1.0' );
define( 'EQUIPMENT_MANAGEMENT_FILE', __FILE__ );
define( 'EQUIPMENT_MANAGEMENT_DIR', plugin_dir_path( __FILE__ ) );
define( 'EQUIPMENT_MANAGEMENT_URL', plugin_dir_url( __FILE__ ) );

require_once EQUIPMENT_MANAGEMENT_DIR . 'includes/helpers.php';
require_once EQUIPMENT_MANAGEMENT_DIR . 'includes/class-activator.php';
require_once EQUIPMENT_MANAGEMENT_DIR . 'includes/class-deactivator.php';
require_once EQUIPMENT_MANAGEMENT_DIR . 'includes/class-plugin.php';

register_activation_hook( __FILE__, array( 'Equipment_Management_Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Equipment_Management_Deactivator', 'deactivate' ) );

/**
 * Starts the plugin.
 *
 * @return void
 */
function equipment_management_run() {
	$plugin = new Equipment_Management_Plugin();
	$plugin->run();
}

equipment_management_run();
