<?php
/**
 * Repair admin pages.
 *
 * @package EquipmentManagement
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders repair list and form pages.
 */
class Equipment_Management_Repair_Page {
	/**
	 * Handles repair add and update requests.
	 *
	 * @return void
	 */
	public static function handle_save() {
		Equipment_Management_Permissions::require_capability( 'equipment_edit_repairs' );
		check_admin_referer( 'equipment_management_save_repair' );

		$posted = wp_unslash( $_POST );
		$id     = isset( $posted['repair_id'] ) ? absint( $posted['repair_id'] ) : 0;

		$result = Equipment_Management_DB::save_repair_row(
			array(
				'id'                       => $id,
				'equipment_id'             => isset( $posted['equipment_id'] ) ? $posted['equipment_id'] : 0,
				'reporter_user_id'         => isset( $posted['reporter_user_id'] ) ? $posted['reporter_user_id'] : get_current_user_id(),
				'trouble_location_id'      => isset( $posted['trouble_location_id'] ) ? $posted['trouble_location_id'] : 0,
				'occurred_at'              => isset( $posted['occurred_at'] ) ? str_replace( 'T', ' ', $posted['occurred_at'] ) : '',
				'vendor_id'                => isset( $posted['vendor_id'] ) ? $posted['vendor_id'] : 0,
				'vendor_name_free'         => isset( $posted['vendor_name_free'] ) ? $posted['vendor_name_free'] : '',
				'requested_date'           => isset( $posted['requested_date'] ) ? $posted['requested_date'] : '',
				'desired_completion_date'  => isset( $posted['desired_completion_date'] ) ? $posted['desired_completion_date'] : '',
				'return_location_id'       => isset( $posted['return_location_id'] ) ? $posted['return_location_id'] : 0,
				'return_address_free'      => isset( $posted['return_address_free'] ) ? $posted['return_address_free'] : '',
				'trouble_detail'           => isset( $posted['trouble_detail'] ) ? $posted['trouble_detail'] : '',
				'assignee_user_id'         => isset( $posted['assignee_user_id'] ) ? $posted['assignee_user_id'] : 0,
				'assignee_name'            => isset( $posted['assignee_name'] ) ? $posted['assignee_name'] : '',
				'repair_cost'              => isset( $posted['repair_cost'] ) ? $posted['repair_cost'] : '',
				'status_id'                => isset( $posted['status_id'] ) ? $posted['status_id'] : 0,
				'completed_date'           => isset( $posted['completed_date'] ) ? $posted['completed_date'] : '',
				'note'                     => isset( $posted['note'] ) ? $posted['note'] : '',
			)
		);

		if ( false === $result ) {
			self::redirect_form( $id, 'error' );
		}

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'             => Equipment_Management_Admin_Menu::SLUG_REPAIRS,
					'equipment_notice' => 'saved',
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

	/**
	 * Renders the repair list page.
	 *
	 * @return void
	 */
	public static function render_list() {
		Equipment_Management_Permissions::require_capability( 'equipment_view_repairs' );

		$filters = self::get_filters();
		$rows    = Equipment_Management_DB::get_repair_rows( $filters );
		$masters = self::get_form_masters();
		$notice  = isset( $_GET['equipment_notice'] ) ? sanitize_key( wp_unslash( $_GET['equipment_notice'] ) ) : '';

		include equipment_management_path( 'admin/views/repair-list.php' );
	}

	/**
	 * Exports repair list rows as CSV.
	 *
	 * @return void
	 */
	public static function handle_export() {
		Equipment_Management_Permissions::require_capability( 'equipment_export_csv' );
		check_admin_referer( 'equipment_management_export_repairs' );

		$filters = self::get_filters();
		$rows    = Equipment_Management_DB::get_repair_rows( $filters, 0 );

		Equipment_Management_DB::log_operation( 'export', 'repair', null, null, array( 'filters' => $filters, 'count' => count( $rows ) ) );

		self::send_csv(
			'repairs-' . gmdate( 'Ymd-His' ) . '.csv',
			array( '修理ID', 'ステータス', '発生日時', '機器名', '設置場所', '型番', '機器コード', '修理依頼先', '担当者', '完了希望日', '完了日', '修理費用' ),
			array_map(
				static function ( $row ) {
					return array(
						$row->id,
						$row->status_name,
						$row->occurred_at,
						$row->equipment_name,
						$row->equipment_location_name,
						$row->model_number,
						$row->equipment_code,
						$row->vendor_name ? $row->vendor_name : $row->vendor_name_free,
						$row->assignee_name,
						$row->desired_completion_date,
						$row->completed_date,
						$row->repair_cost,
					);
				},
				$rows
			)
		);
	}

	/**
	 * Renders the repair registration form.
	 *
	 * @return void
	 */
	public static function render_form() {
		Equipment_Management_Permissions::require_capability( 'equipment_edit_repairs' );

		$repair_id = isset( $_GET['repair_id'] ) ? absint( $_GET['repair_id'] ) : 0;
		$repair    = Equipment_Management_DB::get_repair_row( $repair_id );
		$masters   = self::get_form_masters();
		$notice    = isset( $_GET['equipment_notice'] ) ? sanitize_key( wp_unslash( $_GET['equipment_notice'] ) ) : '';

		include equipment_management_path( 'admin/views/repair-form.php' );
	}

	/**
	 * Returns sanitized search filters.
	 *
	 * @return array<string, mixed>
	 */
	private static function get_filters() {
		$query = wp_unslash( $_GET );

		return array(
			'occurred_from'        => isset( $query['occurred_from'] ) ? sanitize_text_field( $query['occurred_from'] ) : '',
			'occurred_to'          => isset( $query['occurred_to'] ) ? sanitize_text_field( $query['occurred_to'] ) : '',
			'requested_from'       => isset( $query['requested_from'] ) ? sanitize_text_field( $query['requested_from'] ) : '',
			'requested_to'         => isset( $query['requested_to'] ) ? sanitize_text_field( $query['requested_to'] ) : '',
			'desired_from'         => isset( $query['desired_from'] ) ? sanitize_text_field( $query['desired_from'] ) : '',
			'desired_to'           => isset( $query['desired_to'] ) ? sanitize_text_field( $query['desired_to'] ) : '',
			'completed_from'       => isset( $query['completed_from'] ) ? sanitize_text_field( $query['completed_from'] ) : '',
			'completed_to'         => isset( $query['completed_to'] ) ? sanitize_text_field( $query['completed_to'] ) : '',
			'trouble_location_id'  => isset( $query['trouble_location_id'] ) ? absint( $query['trouble_location_id'] ) : 0,
			'equipment_name'       => isset( $query['equipment_name'] ) ? sanitize_text_field( $query['equipment_name'] ) : '',
			'model_number'         => isset( $query['model_number'] ) ? sanitize_text_field( $query['model_number'] ) : '',
			'equipment_code'       => isset( $query['equipment_code'] ) ? sanitize_text_field( $query['equipment_code'] ) : '',
			'status_id'            => isset( $query['status_id'] ) ? absint( $query['status_id'] ) : 0,
			'assignee'             => isset( $query['assignee'] ) ? sanitize_text_field( $query['assignee'] ) : '',
			'vendor'               => isset( $query['vendor'] ) ? sanitize_text_field( $query['vendor'] ) : '',
			'cost_from'            => isset( $query['cost_from'] ) ? sanitize_text_field( $query['cost_from'] ) : '',
			'cost_to'              => isset( $query['cost_to'] ) ? sanitize_text_field( $query['cost_to'] ) : '',
		);
	}

	/**
	 * Returns master rows needed by repair screens.
	 *
	 * @return array<string, array<int, object>>
	 */
	private static function get_form_masters() {
		return array(
			'equipment'       => Equipment_Management_DB::get_equipment_options(),
			'locations'       => Equipment_Management_DB::get_active_master_rows( 'locations' ),
			'repair_statuses' => Equipment_Management_DB::get_active_master_rows( 'repair_statuses' ),
			'vendors'         => Equipment_Management_DB::get_active_master_rows( 'vendors' ),
		);
	}

	/**
	 * Redirects back to the repair form.
	 *
	 * @param int    $id Repair ID.
	 * @param string $notice Notice key.
	 * @return void
	 */
	private static function redirect_form( $id, $notice ) {
		$args = array(
			'page'             => Equipment_Management_Admin_Menu::SLUG_REPAIR_NEW,
			'equipment_notice' => $notice,
		);

		if ( $id > 0 ) {
			$args['repair_id'] = $id;
		}

		wp_safe_redirect( add_query_arg( $args, admin_url( 'admin.php' ) ) );
		exit;
	}

	/**
	 * Sends CSV output encoded for Excel on Japanese Windows.
	 *
	 * @param string            $filename Download filename.
	 * @param array<int,string> $headers Header row.
	 * @param array<int,array>  $rows Data rows.
	 * @return void
	 */
	private static function send_csv( $filename, $headers, $rows ) {
		nocache_headers();
		header( 'Content-Type: text/csv; charset=Shift_JIS' );
		header( 'Content-Disposition: attachment; filename="' . sanitize_file_name( $filename ) . '"' );

		$output = fopen( 'php://output', 'w' );
		fputcsv( $output, array_map( array( __CLASS__, 'encode_csv_value' ), $headers ) );

		foreach ( $rows as $row ) {
			fputcsv( $output, array_map( array( __CLASS__, 'encode_csv_value' ), $row ) );
		}

		fclose( $output );
		exit;
	}

	/**
	 * Encodes one CSV value to CP932.
	 *
	 * @param mixed $value Value.
	 * @return string
	 */
	private static function encode_csv_value( $value ) {
		return mb_convert_encoding( (string) $value, 'CP932', 'UTF-8' );
	}
}
