<?php
/**
 * Shared helper functions.
 *
 * @package EquipmentManagement
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns a plugin-relative path.
 *
 * @param string $path Relative path.
 * @return string
 */
function equipment_management_path( $path = '' ) {
	return EQUIPMENT_MANAGEMENT_DIR . ltrim( $path, '/\\' );
}

/**
 * Returns a plugin-relative URL.
 *
 * @param string $path Relative path.
 * @return string
 */
function equipment_management_url( $path = '' ) {
	return EQUIPMENT_MANAGEMENT_URL . ltrim( $path, '/\\' );
}
