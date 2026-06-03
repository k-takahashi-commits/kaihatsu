<?php
/**
 * Attachment management.
 *
 * @package EquipmentManagement
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles uploads and attachment records for equipment and repair rows.
 */
class Equipment_Management_Attachments {
	const MAX_FILES_PER_TARGET = 3;
	const MAX_FILE_SIZE        = 5242880;

	/**
	 * Returns attachment rows for a target.
	 *
	 * @param string $target_type Target type.
	 * @param int    $target_id Target ID.
	 * @return array<int, object>
	 */
	public static function get_rows( $target_type, $target_id ) {
		global $wpdb;

		if ( ! self::is_valid_target_type( $target_type ) || absint( $target_id ) <= 0 ) {
			return array();
		}

		$table = Equipment_Management_DB::table( 'attachments' );

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE target_type = %s AND target_id = %d ORDER BY created_at DESC, id DESC",
				$target_type,
				absint( $target_id )
			)
		);
	}

	/**
	 * Handles uploaded files for a target.
	 *
	 * @param string $target_type Target type.
	 * @param int    $target_id Target ID.
	 * @param string $field_name File input name.
	 * @return bool
	 */
	public static function handle_uploads( $target_type, $target_id, $field_name ) {
		if ( empty( $_FILES[ $field_name ] ) || ! self::is_valid_target_type( $target_type ) || absint( $target_id ) <= 0 ) {
			return true;
		}

		$files = self::normalize_files_array( $_FILES[ $field_name ] );

		if ( empty( $files ) ) {
			return true;
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$has_error = false;

		foreach ( $files as $file ) {
			if ( empty( $file['name'] ) || UPLOAD_ERR_NO_FILE === (int) $file['error'] ) {
				continue;
			}

			if ( ! self::can_add_more( $target_type, $target_id ) || ! self::is_valid_upload( $file ) ) {
				$has_error = true;
				continue;
			}

			$attachment_id = media_handle_sideload( $file, 0 );

			if ( is_wp_error( $attachment_id ) ) {
				$has_error = true;
				continue;
			}

			self::insert_row( $target_type, $target_id, $attachment_id );
		}

		return ! $has_error;
	}

	/**
	 * Handles attachment deletion requests.
	 *
	 * @return void
	 */
	public static function handle_delete() {
		$attachment_row_id = isset( $_GET['attachment_row_id'] ) ? absint( $_GET['attachment_row_id'] ) : 0;
		$redirect          = isset( $_GET['redirect'] ) ? esc_url_raw( wp_unslash( $_GET['redirect'] ) ) : admin_url( 'admin.php' );

		check_admin_referer( 'equipment_management_delete_attachment_' . $attachment_row_id );

		$row = self::get_row( $attachment_row_id );

		if ( ! $row ) {
			wp_safe_redirect( add_query_arg( 'attachment_notice', 'missing', $redirect ) );
			exit;
		}

		self::require_target_edit_capability( $row->target_type );

		$deleted = self::delete_row( $row );

		wp_safe_redirect( add_query_arg( 'attachment_notice', $deleted ? 'deleted' : 'error', $redirect ) );
		exit;
	}

	/**
	 * Returns true when the target can accept more attachments.
	 *
	 * @param string $target_type Target type.
	 * @param int    $target_id Target ID.
	 * @return bool
	 */
	private static function can_add_more( $target_type, $target_id ) {
		return count( self::get_rows( $target_type, $target_id ) ) < self::MAX_FILES_PER_TARGET;
	}

	/**
	 * Deletes a row and its WordPress attachment.
	 *
	 * @param object $row Attachment row.
	 * @return bool
	 */
	private static function delete_row( $row ) {
		global $wpdb;

		$table   = Equipment_Management_DB::table( 'attachments' );
		$deleted = $wpdb->delete( $table, array( 'id' => absint( $row->id ) ), array( '%d' ) );

		if ( false === $deleted ) {
			return false;
		}

		wp_delete_attachment( absint( $row->attachment_id ), true );
		Equipment_Management_DB::log_operation( 'detach_file', 'attachment', absint( $row->id ), $row, null );

		return true;
	}

	/**
	 * Returns one attachment row.
	 *
	 * @param int $id Attachment row ID.
	 * @return object|null
	 */
	private static function get_row( $id ) {
		global $wpdb;

		$table = Equipment_Management_DB::table( 'attachments' );

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE id = %d",
				absint( $id )
			)
		);
	}

	/**
	 * Inserts an attachment relation row.
	 *
	 * @param string $target_type Target type.
	 * @param int    $target_id Target ID.
	 * @param int    $attachment_id WordPress attachment ID.
	 * @return void
	 */
	private static function insert_row( $target_type, $target_id, $attachment_id ) {
		global $wpdb;

		$table     = Equipment_Management_DB::table( 'attachments' );
		$file_path = get_attached_file( $attachment_id );
		$file_name = $file_path ? basename( $file_path ) : get_the_title( $attachment_id );
		$file_size = $file_path && file_exists( $file_path ) ? filesize( $file_path ) : 0;

		$wpdb->insert(
			$table,
			array(
				'target_type'   => sanitize_key( $target_type ),
				'target_id'     => absint( $target_id ),
				'attachment_id' => absint( $attachment_id ),
				'file_name'     => sanitize_file_name( $file_name ),
				'file_size'     => absint( $file_size ),
				'created_by'    => get_current_user_id(),
				'created_at'    => current_time( 'mysql' ),
			)
		);

		$row = self::get_row( (int) $wpdb->insert_id );
		Equipment_Management_DB::log_operation( 'attach_file', 'attachment', $row ? (int) $row->id : null, null, $row );
	}

	/**
	 * Validates one uploaded file.
	 *
	 * @param array<string,mixed> $file File array.
	 * @return bool
	 */
	private static function is_valid_upload( $file ) {
		if ( ! empty( $file['error'] ) || empty( $file['tmp_name'] ) ) {
			return false;
		}

		return isset( $file['size'] ) && (int) $file['size'] > 0 && (int) $file['size'] <= self::MAX_FILE_SIZE;
	}

	/**
	 * Checks supported target types.
	 *
	 * @param string $target_type Target type.
	 * @return bool
	 */
	private static function is_valid_target_type( $target_type ) {
		return in_array( $target_type, array( 'equipment', 'repair' ), true );
	}

	/**
	 * Normalizes PHP's multi-file upload array.
	 *
	 * @param array<string,mixed> $files Files array.
	 * @return array<int,array<string,mixed>>
	 */
	private static function normalize_files_array( $files ) {
		if ( ! is_array( $files['name'] ) ) {
			return array( $files );
		}

		$normalized = array();

		foreach ( $files['name'] as $index => $name ) {
			$normalized[] = array(
				'name'     => $name,
				'type'     => $files['type'][ $index ],
				'tmp_name' => $files['tmp_name'][ $index ],
				'error'    => $files['error'][ $index ],
				'size'     => $files['size'][ $index ],
			);
		}

		return $normalized;
	}

	/**
	 * Requires edit capability for the target type.
	 *
	 * @param string $target_type Target type.
	 * @return void
	 */
	private static function require_target_edit_capability( $target_type ) {
		if ( 'equipment' === $target_type ) {
			Equipment_Management_Permissions::require_capability( 'equipment_edit_items' );
			return;
		}

		Equipment_Management_Permissions::require_capability( 'equipment_edit_repairs' );
	}
}
