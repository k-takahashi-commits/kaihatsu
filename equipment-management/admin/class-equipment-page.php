<?php
/**
 * Equipment admin pages.
 *
 * @package EquipmentManagement
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders equipment list and form pages.
 */
class Equipment_Management_Equipment_Page {
	/**
	 * Handles equipment add and update requests.
	 *
	 * @return void
	 */
	public static function handle_save() {
		Equipment_Management_Permissions::require_capability( 'equipment_edit_items' );
		check_admin_referer( 'equipment_management_save_equipment' );

		$posted = wp_unslash( $_POST );
		$id     = isset( $posted['equipment_id'] ) ? absint( $posted['equipment_id'] ) : 0;

		$result = Equipment_Management_DB::save_equipment_row(
			array(
				'id'             => $id,
				'name'           => isset( $posted['name'] ) ? $posted['name'] : '',
				'location_id'    => isset( $posted['location_id'] ) ? $posted['location_id'] : 0,
				'model_number'   => isset( $posted['model_number'] ) ? $posted['model_number'] : '',
				'equipment_code' => isset( $posted['equipment_code'] ) ? $posted['equipment_code'] : '',
				'installed_date' => isset( $posted['installed_date'] ) ? $posted['installed_date'] : '',
				'usage_id'       => isset( $posted['usage_id'] ) ? $posted['usage_id'] : 0,
				'status_id'      => isset( $posted['status_id'] ) ? $posted['status_id'] : 0,
				'note'           => isset( $posted['note'] ) ? $posted['note'] : '',
			)
		);

		if ( false === $result ) {
			self::redirect_form( $id, 'error' );
		}

		$attachment_notice = Equipment_Management_Attachments::handle_uploads( 'equipment', $result, 'equipment_attachments' ) ? '' : 'attachment_error';

		$url = add_query_arg(
			array(
				'page'             => Equipment_Management_Admin_Menu::SLUG_ITEMS,
				'equipment_notice' => 'saved',
				'attachment_notice' => $attachment_notice,
			),
			admin_url( 'admin.php' )
		);

		wp_safe_redirect( $url );
		exit;
	}

	/**
	 * Renders the equipment list page.
	 *
	 * @return void
	 */
	public static function render_list() {
		Equipment_Management_Permissions::require_capability( 'equipment_view_items' );

		$filters = self::get_filters();
		$rows    = Equipment_Management_DB::get_equipment_rows( $filters );
		$masters = self::get_form_masters();
		$notice  = isset( $_GET['equipment_notice'] ) ? sanitize_key( wp_unslash( $_GET['equipment_notice'] ) ) : '';
		$attachment_notice = isset( $_GET['attachment_notice'] ) ? sanitize_key( wp_unslash( $_GET['attachment_notice'] ) ) : '';

		include equipment_management_path( 'admin/views/equipment-list.php' );
	}

	/**
	 * Exports equipment list rows as CSV.
	 *
	 * @return void
	 */
	public static function handle_export() {
		Equipment_Management_Permissions::require_capability( 'equipment_export_csv' );
		check_admin_referer( 'equipment_management_export_equipment' );

		$filters = self::get_filters();
		$rows    = Equipment_Management_DB::get_equipment_rows( $filters, 0 );

		Equipment_Management_DB::log_operation( 'export', 'equipment', null, null, array( 'filters' => $filters, 'count' => count( $rows ) ) );

		self::send_csv(
			'equipment-' . gmdate( 'Ymd-His' ) . '.csv',
			array( '内部ID', '機器名', '設置場所', '型番', '機器コード', '導入日', '導入年数', '利用用途', '状態', '最終修理日', '更新日時' ),
			array_map(
				static function ( $row ) {
					return array(
						$row->id,
						$row->name,
						$row->location_name,
						$row->model_number,
						$row->equipment_code,
						$row->installed_date,
						Equipment_Management_DB::calculate_years_since( $row->installed_date ),
						$row->usage_name,
						$row->status_name,
						$row->last_repair_date,
						$row->updated_at,
					);
				},
				$rows
			)
		);
	}

	/**
	 * Renders the equipment registration form.
	 *
	 * @return void
	 */
	public static function render_form() {
		Equipment_Management_Permissions::require_capability( 'equipment_edit_items' );

		$equipment_id = isset( $_GET['equipment_id'] ) ? absint( $_GET['equipment_id'] ) : 0;
		$equipment    = Equipment_Management_DB::get_equipment_row( $equipment_id );
		$masters      = self::get_form_masters();
		$attachments  = $equipment ? Equipment_Management_Attachments::get_rows( 'equipment', $equipment->id ) : array();
		$notice       = isset( $_GET['equipment_notice'] ) ? sanitize_key( wp_unslash( $_GET['equipment_notice'] ) ) : '';
		$attachment_notice = isset( $_GET['attachment_notice'] ) ? sanitize_key( wp_unslash( $_GET['attachment_notice'] ) ) : '';

		include equipment_management_path( 'admin/views/equipment-form.php' );
	}

	/**
	 * Renders the equipment detail page.
	 *
	 * @return void
	 */
	public static function render_detail() {
		Equipment_Management_Permissions::require_capability( 'equipment_view_items' );

		$equipment_id = isset( $_GET['equipment_id'] ) ? absint( $_GET['equipment_id'] ) : 0;
		$equipment    = Equipment_Management_DB::get_equipment_detail_row( $equipment_id );
		$repairs      = $equipment ? Equipment_Management_DB::get_repair_rows( array( 'equipment_id' => $equipment->id ), 0 ) : array();
		$attachments  = $equipment ? Equipment_Management_Attachments::get_rows( 'equipment', $equipment->id ) : array();

		include equipment_management_path( 'admin/views/equipment-detail.php' );
	}

	/**
	 * Returns sanitized search filters.
	 *
	 * @return array<string, mixed>
	 */
	private static function get_filters() {
		$query = wp_unslash( $_GET );

		return array(
			'name'           => isset( $query['equipment_name'] ) ? sanitize_text_field( $query['equipment_name'] ) : '',
			'location_id'    => isset( $query['location_id'] ) ? absint( $query['location_id'] ) : 0,
			'model_number'   => isset( $query['model_number'] ) ? sanitize_text_field( $query['model_number'] ) : '',
			'equipment_code' => isset( $query['equipment_code'] ) ? sanitize_text_field( $query['equipment_code'] ) : '',
			'usage_id'       => isset( $query['usage_id'] ) ? absint( $query['usage_id'] ) : 0,
			'status_id'      => isset( $query['status_id'] ) ? absint( $query['status_id'] ) : 0,
			'installed_from' => isset( $query['installed_from'] ) ? sanitize_text_field( $query['installed_from'] ) : '',
			'installed_to'   => isset( $query['installed_to'] ) ? sanitize_text_field( $query['installed_to'] ) : '',
		);
	}

	/**
	 * Returns master rows needed by equipment screens.
	 *
	 * @return array<string, array<int, object>>
	 */
	private static function get_form_masters() {
		return array(
			'locations' => Equipment_Management_DB::get_active_master_rows( 'locations' ),
			'statuses'  => Equipment_Management_DB::get_active_master_rows( 'statuses' ),
			'usages'    => Equipment_Management_DB::get_active_master_rows( 'usages' ),
		);
	}

	/**
	 * Redirects back to the equipment form.
	 *
	 * @param int    $id Equipment ID.
	 * @param string $notice Notice key.
	 * @return void
	 */
	private static function redirect_form( $id, $notice ) {
		$args = array(
			'page'             => Equipment_Management_Admin_Menu::SLUG_ITEM_NEW,
			'equipment_notice' => $notice,
		);

		if ( $id > 0 ) {
			$args['equipment_id'] = $id;
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
		fputcsv( $output, array_map( array( __CLASS__, 'encode_csv_value' ), $headers ), ',', '"', '' );

		foreach ( $rows as $row ) {
			fputcsv( $output, array_map( array( __CLASS__, 'encode_csv_value' ), $row ), ',', '"', '' );
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
