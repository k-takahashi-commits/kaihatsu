<?php
/**
 * Database schema and helpers.
 *
 * @package EquipmentManagement
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manages plugin table names and schema creation.
 */
class Equipment_Management_DB {
	/**
	 * Table suffixes used by the plugin.
	 *
	 * @return array<string, string>
	 */
	public static function table_suffixes() {
		return array(
			'items'             => 'equipment_items',
			'repairs'           => 'equipment_repairs',
			'locations'         => 'equipment_locations',
			'statuses'          => 'equipment_statuses',
			'repair_statuses'   => 'equipment_repair_statuses',
			'usages'            => 'equipment_usages',
			'vendors'           => 'equipment_vendors',
			'attachments'       => 'equipment_attachments',
			'logs'              => 'equipment_logs',
			'csv_imports'       => 'equipment_csv_imports',
			'backup_logs'       => 'equipment_backup_logs',
		);
	}

	/**
	 * Returns a fully-prefixed table name.
	 *
	 * @param string $key Table key.
	 * @return string
	 */
	public static function table( $key ) {
		global $wpdb;

		$suffixes = self::table_suffixes();

		return isset( $suffixes[ $key ] ) ? $wpdb->prefix . $suffixes[ $key ] : '';
	}

	/**
	 * Creates or updates plugin database tables.
	 *
	 * @return void
	 */
	public static function create_tables() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = $wpdb->get_charset_collate();
		$tables          = self::tables();

		foreach ( $tables as $sql ) {
			$sql = str_replace( ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4', " {$charset_collate}", $sql );
			dbDelta( $sql . ';' );
		}
	}

	/**
	 * Inserts default master rows.
	 *
	 * @return void
	 */
	public static function seed_defaults() {
		self::seed_simple_master(
			self::table( 'statuses' ),
			array(
				'使用中',
				'保管中',
				'修理中',
				'故障中',
				'廃棄',
				'無効',
			)
		);

		self::seed_repair_statuses();
	}

	/**
	 * Returns master table definitions used by the admin screen.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public static function master_definitions() {
		return array(
			'locations'       => array(
				'label'      => __( '場所', 'equipment-management' ),
				'table_key'  => 'locations',
				'extra_cols' => array(),
			),
			'statuses'        => array(
				'label'      => __( '機材状態', 'equipment-management' ),
				'table_key'  => 'statuses',
				'extra_cols' => array(),
			),
			'usages'          => array(
				'label'      => __( '利用用途', 'equipment-management' ),
				'table_key'  => 'usages',
				'extra_cols' => array(),
			),
			'vendors'         => array(
				'label'      => __( '修理依頼先', 'equipment-management' ),
				'table_key'  => 'vendors',
				'extra_cols' => array( 'phone', 'email', 'address' ),
			),
			'repair_statuses' => array(
				'label'      => __( '修理ステータス', 'equipment-management' ),
				'table_key'  => 'repair_statuses',
				'extra_cols' => array( 'is_closed' ),
			),
		);
	}

	/**
	 * Checks whether a master type is supported.
	 *
	 * @param string $type Master type.
	 * @return bool
	 */
	public static function is_master_type( $type ) {
		$definitions = self::master_definitions();

		return isset( $definitions[ $type ] );
	}

	/**
	 * Returns rows for a master table.
	 *
	 * @param string $type Master type.
	 * @return array<int, object>
	 */
	public static function get_master_rows( $type ) {
		global $wpdb;

		if ( ! self::is_master_type( $type ) ) {
			return array();
		}

		$definition = self::master_definitions()[ $type ];
		$table      = self::table( $definition['table_key'] );

		return $wpdb->get_results( "SELECT * FROM {$table} ORDER BY display_order ASC, id ASC" );
	}

	/**
	 * Returns one master row.
	 *
	 * @param string $type Master type.
	 * @param int    $id Row ID.
	 * @return object|null
	 */
	public static function get_master_row( $type, $id ) {
		global $wpdb;

		if ( ! self::is_master_type( $type ) || $id <= 0 ) {
			return null;
		}

		$definition = self::master_definitions()[ $type ];
		$table      = self::table( $definition['table_key'] );

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE id = %d",
				$id
			)
		);
	}

	/**
	 * Inserts or updates a master row.
	 *
	 * @param string $type Master type.
	 * @param array  $data Sanitized row data.
	 * @return int|false
	 */
	public static function save_master_row( $type, $data ) {
		global $wpdb;

		if ( ! self::is_master_type( $type ) ) {
			return false;
		}

		$definition = self::master_definitions()[ $type ];
		$table      = self::table( $definition['table_key'] );
		$now        = current_time( 'mysql' );
		$id         = isset( $data['id'] ) ? absint( $data['id'] ) : 0;

		$row = array(
			'name'          => isset( $data['name'] ) ? sanitize_text_field( $data['name'] ) : '',
			'display_order' => isset( $data['display_order'] ) ? (int) $data['display_order'] : 0,
			'is_active'     => empty( $data['is_active'] ) ? 0 : 1,
			'updated_at'    => $now,
		);

		if ( in_array( 'phone', $definition['extra_cols'], true ) ) {
			$row['phone'] = isset( $data['phone'] ) ? sanitize_text_field( $data['phone'] ) : '';
		}

		if ( in_array( 'email', $definition['extra_cols'], true ) ) {
			$row['email'] = isset( $data['email'] ) ? sanitize_email( $data['email'] ) : '';
		}

		if ( in_array( 'address', $definition['extra_cols'], true ) ) {
			$row['address'] = isset( $data['address'] ) ? sanitize_textarea_field( $data['address'] ) : '';
		}

		if ( in_array( 'is_closed', $definition['extra_cols'], true ) ) {
			$row['is_closed'] = empty( $data['is_closed'] ) ? 0 : 1;
		}

		if ( '' === $row['name'] ) {
			return false;
		}

		$before = $id > 0 ? self::get_master_row( $type, $id ) : null;

		if ( $id > 0 ) {
			$updated = $wpdb->update(
				$table,
				$row,
				array( 'id' => $id )
			);

			if ( false === $updated ) {
				return false;
			}

			self::log_operation( 'update', self::master_log_target_type( $type ), $id, $before, self::get_master_row( $type, $id ) );

			return $id;
		}

		$row['created_at'] = $now;
		$inserted          = $wpdb->insert( $table, $row );

		if ( false === $inserted ) {
			return false;
		}

		$new_id = (int) $wpdb->insert_id;
		self::log_operation( 'create', self::master_log_target_type( $type ), $new_id, null, self::get_master_row( $type, $new_id ) );

		return $new_id;
	}

	/**
	 * Returns active master rows for select fields.
	 *
	 * @param string $type Master type.
	 * @return array<int, object>
	 */
	public static function get_active_master_rows( $type ) {
		global $wpdb;

		if ( ! self::is_master_type( $type ) ) {
			return array();
		}

		$definition = self::master_definitions()[ $type ];
		$table      = self::table( $definition['table_key'] );

		return $wpdb->get_results( "SELECT * FROM {$table} WHERE is_active = 1 ORDER BY display_order ASC, id ASC" );
	}

	/**
	 * Returns equipment list rows.
	 *
	 * @param array<string, mixed> $filters Search filters.
	 * @return array<int, object>
	 */
	public static function get_equipment_rows( $filters = array(), $limit = 200 ) {
		global $wpdb;

		$items     = self::table( 'items' );
		$locations = self::table( 'locations' );
		$statuses  = self::table( 'statuses' );
		$usages    = self::table( 'usages' );
		$repairs   = self::table( 'repairs' );
		$where     = array( '1 = 1' );
		$params    = array();

		if ( ! empty( $filters['name'] ) ) {
			$where[]  = 'i.name LIKE %s';
			$params[] = '%' . $wpdb->esc_like( $filters['name'] ) . '%';
		}

		if ( ! empty( $filters['location_id'] ) ) {
			$where[]  = 'i.location_id = %d';
			$params[] = absint( $filters['location_id'] );
		}

		if ( ! empty( $filters['model_number'] ) ) {
			$where[]  = 'i.model_number LIKE %s';
			$params[] = '%' . $wpdb->esc_like( $filters['model_number'] ) . '%';
		}

		if ( ! empty( $filters['equipment_code'] ) ) {
			$where[]  = 'i.equipment_code LIKE %s';
			$params[] = '%' . $wpdb->esc_like( $filters['equipment_code'] ) . '%';
		}

		if ( ! empty( $filters['usage_id'] ) ) {
			$where[]  = 'i.usage_id = %d';
			$params[] = absint( $filters['usage_id'] );
		}

		if ( ! empty( $filters['status_id'] ) ) {
			$where[]  = 'i.status_id = %d';
			$params[] = absint( $filters['status_id'] );
		}

		if ( ! empty( $filters['installed_from'] ) ) {
			$where[]  = 'i.installed_date >= %s';
			$params[] = $filters['installed_from'];
		}

		if ( ! empty( $filters['installed_to'] ) ) {
			$where[]  = 'i.installed_date <= %s';
			$params[] = $filters['installed_to'];
		}

		$sql = "SELECT
				i.*,
				l.name AS location_name,
				s.name AS status_name,
				u.name AS usage_name,
				(
					SELECT MAX(r.completed_date)
					FROM {$repairs} r
					WHERE r.equipment_id = i.id
				) AS last_repair_date
			FROM {$items} i
			LEFT JOIN {$locations} l ON l.id = i.location_id
			LEFT JOIN {$statuses} s ON s.id = i.status_id
			LEFT JOIN {$usages} u ON u.id = i.usage_id
			WHERE " . implode( ' AND ', $where ) . '
			ORDER BY i.updated_at DESC, i.id DESC';

		if ( $limit > 0 ) {
			$sql     .= ' LIMIT %d';
			$params[] = absint( $limit );
		}

		if ( empty( $params ) ) {
			return $wpdb->get_results( $sql );
		}

		return $wpdb->get_results( $wpdb->prepare( $sql, $params ) );
	}

	/**
	 * Returns one equipment row.
	 *
	 * @param int $id Equipment ID.
	 * @return object|null
	 */
	public static function get_equipment_row( $id ) {
		global $wpdb;

		$id = absint( $id );

		if ( $id <= 0 ) {
			return null;
		}

		$table = self::table( 'items' );

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE id = %d",
				$id
			)
		);
	}

	/**
	 * Returns one equipment row with master names.
	 *
	 * @param int $id Equipment ID.
	 * @return object|null
	 */
	public static function get_equipment_detail_row( $id ) {
		global $wpdb;

		$id = absint( $id );

		if ( $id <= 0 ) {
			return null;
		}

		$items     = self::table( 'items' );
		$locations = self::table( 'locations' );
		$statuses  = self::table( 'statuses' );
		$usages    = self::table( 'usages' );

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT
					i.*,
					l.name AS location_name,
					s.name AS status_name,
					u.name AS usage_name
				FROM {$items} i
				LEFT JOIN {$locations} l ON l.id = i.location_id
				LEFT JOIN {$statuses} s ON s.id = i.status_id
				LEFT JOIN {$usages} u ON u.id = i.usage_id
				WHERE i.id = %d",
				$id
			)
		);
	}

	/**
	 * Inserts or updates an equipment row.
	 *
	 * @param array<string, mixed> $data Equipment data.
	 * @return int|false
	 */
	public static function save_equipment_row( $data ) {
		global $wpdb;

		$table = self::table( 'items' );
		$now   = current_time( 'mysql' );
		$id    = isset( $data['id'] ) ? absint( $data['id'] ) : 0;

		$row = array(
			'name'           => isset( $data['name'] ) ? sanitize_text_field( $data['name'] ) : '',
			'location_id'    => isset( $data['location_id'] ) ? absint( $data['location_id'] ) : 0,
			'model_number'   => isset( $data['model_number'] ) ? sanitize_text_field( $data['model_number'] ) : '',
			'equipment_code' => isset( $data['equipment_code'] ) ? sanitize_text_field( $data['equipment_code'] ) : '',
			'installed_date' => isset( $data['installed_date'] ) ? sanitize_text_field( $data['installed_date'] ) : '',
			'usage_id'       => isset( $data['usage_id'] ) ? absint( $data['usage_id'] ) : 0,
			'status_id'      => isset( $data['status_id'] ) ? absint( $data['status_id'] ) : 0,
			'note'           => isset( $data['note'] ) ? sanitize_textarea_field( $data['note'] ) : '',
			'updated_by'     => get_current_user_id(),
			'updated_at'     => $now,
		);

		if ( '' === $row['name'] || $row['location_id'] <= 0 || '' === $row['installed_date'] || $row['status_id'] <= 0 ) {
			return false;
		}

		if ( $row['usage_id'] <= 0 ) {
			$row['usage_id'] = null;
		}

		$before = $id > 0 ? self::get_equipment_row( $id ) : null;

		if ( $id > 0 ) {
			$updated = $wpdb->update(
				$table,
				$row,
				array( 'id' => $id )
			);

			if ( false === $updated ) {
				return false;
			}

			self::log_operation( 'update', 'equipment', $id, $before, self::get_equipment_row( $id ) );

			return $id;
		}

		$row['created_by'] = get_current_user_id();
		$row['created_at'] = $now;
		$inserted          = $wpdb->insert( $table, $row );

		if ( false === $inserted ) {
			return false;
		}

		$new_id = (int) $wpdb->insert_id;
		self::log_operation( 'create', 'equipment', $new_id, null, self::get_equipment_row( $new_id ) );

		return $new_id;
	}

	/**
	 * Returns equipment rows for repair selection.
	 *
	 * @return array<int, object>
	 */
	public static function get_equipment_options() {
		global $wpdb;

		$items     = self::table( 'items' );
		$locations = self::table( 'locations' );

		return $wpdb->get_results(
			"SELECT
				i.id,
				i.name,
				i.model_number,
				i.equipment_code,
				i.location_id,
				l.name AS location_name
			FROM {$items} i
			LEFT JOIN {$locations} l ON l.id = i.location_id
			ORDER BY i.name ASC, i.id ASC
			LIMIT 5000"
		);
	}

	/**
	 * Returns repair list rows.
	 *
	 * @param array<string, mixed> $filters Search filters.
	 * @param int                 $limit Row limit. Zero means no limit.
	 * @return array<int, object>
	 */
	public static function get_repair_rows( $filters = array(), $limit = 200 ) {
		global $wpdb;

		$repairs         = self::table( 'repairs' );
		$items           = self::table( 'items' );
		$locations       = self::table( 'locations' );
		$repair_statuses = self::table( 'repair_statuses' );
		$vendors         = self::table( 'vendors' );
		$where           = array( '1 = 1' );
		$params          = array();

		if ( ! empty( $filters['equipment_id'] ) ) {
			$where[]  = 'r.equipment_id = %d';
			$params[] = absint( $filters['equipment_id'] );
		}

		if ( ! empty( $filters['occurred_from'] ) ) {
			$where[]  = 'r.occurred_at >= %s';
			$params[] = $filters['occurred_from'] . ' 00:00:00';
		}

		if ( ! empty( $filters['occurred_to'] ) ) {
			$where[]  = 'r.occurred_at <= %s';
			$params[] = $filters['occurred_to'] . ' 23:59:59';
		}

		if ( ! empty( $filters['requested_from'] ) ) {
			$where[]  = 'r.requested_date >= %s';
			$params[] = $filters['requested_from'];
		}

		if ( ! empty( $filters['requested_to'] ) ) {
			$where[]  = 'r.requested_date <= %s';
			$params[] = $filters['requested_to'];
		}

		if ( ! empty( $filters['desired_from'] ) ) {
			$where[]  = 'r.desired_completion_date >= %s';
			$params[] = $filters['desired_from'];
		}

		if ( ! empty( $filters['desired_to'] ) ) {
			$where[]  = 'r.desired_completion_date <= %s';
			$params[] = $filters['desired_to'];
		}

		if ( ! empty( $filters['completed_from'] ) ) {
			$where[]  = 'r.completed_date >= %s';
			$params[] = $filters['completed_from'];
		}

		if ( ! empty( $filters['completed_to'] ) ) {
			$where[]  = 'r.completed_date <= %s';
			$params[] = $filters['completed_to'];
		}

		if ( ! empty( $filters['trouble_location_id'] ) ) {
			$where[]  = 'r.trouble_location_id = %d';
			$params[] = absint( $filters['trouble_location_id'] );
		}

		if ( ! empty( $filters['equipment_name'] ) ) {
			$where[]  = 'i.name LIKE %s';
			$params[] = '%' . $wpdb->esc_like( $filters['equipment_name'] ) . '%';
		}

		if ( ! empty( $filters['model_number'] ) ) {
			$where[]  = 'i.model_number LIKE %s';
			$params[] = '%' . $wpdb->esc_like( $filters['model_number'] ) . '%';
		}

		if ( ! empty( $filters['equipment_code'] ) ) {
			$where[]  = 'i.equipment_code LIKE %s';
			$params[] = '%' . $wpdb->esc_like( $filters['equipment_code'] ) . '%';
		}

		if ( ! empty( $filters['status_id'] ) ) {
			$where[]  = 'r.status_id = %d';
			$params[] = absint( $filters['status_id'] );
		}

		if ( ! empty( $filters['assignee'] ) ) {
			$where[]  = 'r.assignee_name LIKE %s';
			$params[] = '%' . $wpdb->esc_like( $filters['assignee'] ) . '%';
		}

		if ( ! empty( $filters['vendor'] ) ) {
			$where[]  = '(v.name LIKE %s OR r.vendor_name_free LIKE %s)';
			$params[] = '%' . $wpdb->esc_like( $filters['vendor'] ) . '%';
			$params[] = '%' . $wpdb->esc_like( $filters['vendor'] ) . '%';
		}

		$cost_from = isset( $filters['cost_from'] ) ? (string) $filters['cost_from'] : '';
		$cost_to   = isset( $filters['cost_to'] ) ? (string) $filters['cost_to'] : '';

		if ( '' !== $cost_from ) {
			$where[]  = 'r.repair_cost >= %d';
			$params[] = absint( $filters['cost_from'] );
		}

		if ( '' !== $cost_to ) {
			$where[]  = 'r.repair_cost <= %d';
			$params[] = absint( $filters['cost_to'] );
		}

		$sql = "SELECT
				r.*,
				i.name AS equipment_name,
				i.model_number,
				i.equipment_code,
				i.location_id AS equipment_location_id,
				eq_l.name AS equipment_location_name,
				tr_l.name AS trouble_location_name,
				rs.name AS status_name,
				v.name AS vendor_name
			FROM {$repairs} r
			LEFT JOIN {$items} i ON i.id = r.equipment_id
			LEFT JOIN {$locations} eq_l ON eq_l.id = i.location_id
			LEFT JOIN {$locations} tr_l ON tr_l.id = r.trouble_location_id
			LEFT JOIN {$repair_statuses} rs ON rs.id = r.status_id
			LEFT JOIN {$vendors} v ON v.id = r.vendor_id
			WHERE " . implode( ' AND ', $where ) . '
			ORDER BY r.updated_at DESC, r.id DESC';

		if ( $limit > 0 ) {
			$sql     .= ' LIMIT %d';
			$params[] = absint( $limit );
		}

		if ( empty( $params ) ) {
			return $wpdb->get_results( $sql );
		}

		return $wpdb->get_results( $wpdb->prepare( $sql, $params ) );
	}

	/**
	 * Returns one repair row.
	 *
	 * @param int $id Repair ID.
	 * @return object|null
	 */
	public static function get_repair_row( $id ) {
		global $wpdb;

		$id = absint( $id );

		if ( $id <= 0 ) {
			return null;
		}

		$table = self::table( 'repairs' );

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE id = %d",
				$id
			)
		);
	}

	/**
	 * Returns one repair row with related names.
	 *
	 * @param int $id Repair ID.
	 * @return object|null
	 */
	public static function get_repair_detail_row( $id ) {
		global $wpdb;

		$id = absint( $id );

		if ( $id <= 0 ) {
			return null;
		}

		$repairs         = self::table( 'repairs' );
		$items           = self::table( 'items' );
		$locations       = self::table( 'locations' );
		$repair_statuses = self::table( 'repair_statuses' );
		$vendors         = self::table( 'vendors' );

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT
					r.*,
					i.name AS equipment_name,
					i.model_number,
					i.equipment_code,
					i.installed_date,
					eq_l.name AS equipment_location_name,
					tr_l.name AS trouble_location_name,
					ret_l.name AS return_location_name,
					rs.name AS status_name,
					v.name AS vendor_name
				FROM {$repairs} r
				LEFT JOIN {$items} i ON i.id = r.equipment_id
				LEFT JOIN {$locations} eq_l ON eq_l.id = i.location_id
				LEFT JOIN {$locations} tr_l ON tr_l.id = r.trouble_location_id
				LEFT JOIN {$locations} ret_l ON ret_l.id = r.return_location_id
				LEFT JOIN {$repair_statuses} rs ON rs.id = r.status_id
				LEFT JOIN {$vendors} v ON v.id = r.vendor_id
				WHERE r.id = %d",
				$id
			)
		);
	}

	/**
	 * Inserts or updates a repair row.
	 *
	 * @param array<string, mixed> $data Repair data.
	 * @return int|false
	 */
	public static function save_repair_row( $data ) {
		global $wpdb;

		$table = self::table( 'repairs' );
		$now   = current_time( 'mysql' );
		$id    = isset( $data['id'] ) ? absint( $data['id'] ) : 0;

		$row = array(
			'equipment_id'             => isset( $data['equipment_id'] ) ? absint( $data['equipment_id'] ) : 0,
			'reporter_user_id'         => isset( $data['reporter_user_id'] ) ? absint( $data['reporter_user_id'] ) : get_current_user_id(),
			'trouble_location_id'      => isset( $data['trouble_location_id'] ) ? absint( $data['trouble_location_id'] ) : 0,
			'occurred_at'              => isset( $data['occurred_at'] ) ? sanitize_text_field( $data['occurred_at'] ) : '',
			'vendor_id'                => isset( $data['vendor_id'] ) ? absint( $data['vendor_id'] ) : 0,
			'vendor_name_free'         => isset( $data['vendor_name_free'] ) ? sanitize_text_field( $data['vendor_name_free'] ) : '',
			'requested_date'           => isset( $data['requested_date'] ) ? sanitize_text_field( $data['requested_date'] ) : '',
			'desired_completion_date'  => isset( $data['desired_completion_date'] ) ? sanitize_text_field( $data['desired_completion_date'] ) : '',
			'return_location_id'       => isset( $data['return_location_id'] ) ? absint( $data['return_location_id'] ) : 0,
			'return_address_free'      => isset( $data['return_address_free'] ) ? sanitize_text_field( $data['return_address_free'] ) : '',
			'trouble_detail'           => isset( $data['trouble_detail'] ) ? sanitize_textarea_field( $data['trouble_detail'] ) : '',
			'assignee_user_id'         => isset( $data['assignee_user_id'] ) ? absint( $data['assignee_user_id'] ) : 0,
			'assignee_name'            => isset( $data['assignee_name'] ) ? sanitize_text_field( $data['assignee_name'] ) : '',
			'repair_cost'              => ! isset( $data['repair_cost'] ) || '' === (string) $data['repair_cost'] ? null : absint( $data['repair_cost'] ),
			'status_id'                => isset( $data['status_id'] ) ? absint( $data['status_id'] ) : 0,
			'completed_date'           => isset( $data['completed_date'] ) ? sanitize_text_field( $data['completed_date'] ) : '',
			'note'                     => isset( $data['note'] ) ? sanitize_textarea_field( $data['note'] ) : '',
			'updated_by'               => get_current_user_id(),
			'updated_at'               => $now,
		);

		if ( $row['equipment_id'] <= 0 || $row['reporter_user_id'] <= 0 || $row['trouble_location_id'] <= 0 || '' === $row['occurred_at'] || '' === $row['trouble_detail'] || $row['status_id'] <= 0 ) {
			return false;
		}

		if ( 16 === strlen( $row['occurred_at'] ) ) {
			$row['occurred_at'] .= ':00';
		}

		foreach ( array( 'vendor_id', 'return_location_id', 'assignee_user_id' ) as $nullable_int ) {
			if ( $row[ $nullable_int ] <= 0 ) {
				$row[ $nullable_int ] = null;
			}
		}

		foreach ( array( 'vendor_name_free', 'requested_date', 'desired_completion_date', 'return_address_free', 'completed_date' ) as $nullable_string ) {
			if ( '' === $row[ $nullable_string ] ) {
				$row[ $nullable_string ] = null;
			}
		}

		$before = $id > 0 ? self::get_repair_row( $id ) : null;

		if ( $id > 0 ) {
			$updated = $wpdb->update(
				$table,
				$row,
				array( 'id' => $id )
			);

			if ( false === $updated ) {
				return false;
			}

			self::log_operation( 'update', 'repair', $id, $before, self::get_repair_row( $id ) );

			return $id;
		}

		$row['created_by'] = get_current_user_id();
		$row['created_at'] = $now;
		$inserted          = $wpdb->insert( $table, $row );

		if ( false === $inserted ) {
			return false;
		}

		$new_id = (int) $wpdb->insert_id;
		self::log_operation( 'create', 'repair', $new_id, null, self::get_repair_row( $new_id ) );

		return $new_id;
	}

	/**
	 * Logs an operation.
	 *
	 * @param string     $action Operation action.
	 * @param string     $target_type Target type.
	 * @param int|null   $target_id Target ID.
	 * @param mixed|null $before_data Previous data.
	 * @param mixed|null $after_data New data.
	 * @return void
	 */
	public static function log_operation( $action, $target_type, $target_id = null, $before_data = null, $after_data = null ) {
		global $wpdb;

		$table = self::table( 'logs' );

		$wpdb->insert(
			$table,
			array(
				'user_id'     => get_current_user_id() ?: null,
				'action'      => sanitize_key( $action ),
				'target_type' => sanitize_key( $target_type ),
				'target_id'   => null === $target_id ? null : absint( $target_id ),
				'before_data' => null === $before_data ? null : wp_json_encode( $before_data, JSON_UNESCAPED_UNICODE ),
				'after_data'  => null === $after_data ? null : wp_json_encode( $after_data, JSON_UNESCAPED_UNICODE ),
				'ip_address'  => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : null,
				'user_agent'  => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_textarea_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : null,
				'created_at'  => current_time( 'mysql' ),
			)
		);
	}

	/**
	 * Returns log target type for a master key.
	 *
	 * @param string $type Master type.
	 * @return string
	 */
	private static function master_log_target_type( $type ) {
		$map = array(
			'locations'       => 'location_master',
			'statuses'        => 'equipment_status_master',
			'repair_statuses' => 'repair_status_master',
			'usages'          => 'usage_master',
			'vendors'         => 'vendor_master',
		);

		return isset( $map[ $type ] ) ? $map[ $type ] : 'master';
	}

	/**
	 * Calculates full years elapsed since a date.
	 *
	 * @param string $date Date in Y-m-d format.
	 * @return string
	 */
	public static function calculate_years_since( $date ) {
		if ( empty( $date ) || '0000-00-00' === $date ) {
			return '';
		}

		try {
			$start = new DateTime( $date );
			$today = new DateTime( current_time( 'Y-m-d' ) );
		} catch ( Exception $exception ) {
			return '';
		}

		return (string) $start->diff( $today )->y;
	}

	/**
	 * Returns CREATE TABLE statements.
	 *
	 * @return array<int, string>
	 */
	private static function tables() {
		$items           = self::table( 'items' );
		$repairs         = self::table( 'repairs' );
		$locations       = self::table( 'locations' );
		$statuses        = self::table( 'statuses' );
		$repair_statuses = self::table( 'repair_statuses' );
		$usages          = self::table( 'usages' );
		$vendors         = self::table( 'vendors' );
		$attachments     = self::table( 'attachments' );
		$logs            = self::table( 'logs' );
		$csv_imports     = self::table( 'csv_imports' );
		$backup_logs     = self::table( 'backup_logs' );

		return array(
			"CREATE TABLE {$items} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				name varchar(255) NOT NULL,
				location_id bigint(20) unsigned NOT NULL,
				model_number varchar(255) DEFAULT NULL,
				equipment_code varchar(255) DEFAULT NULL,
				installed_date date NOT NULL,
				usage_id bigint(20) unsigned DEFAULT NULL,
				status_id bigint(20) unsigned NOT NULL,
				note text,
				created_by bigint(20) unsigned NOT NULL,
				updated_by bigint(20) unsigned NOT NULL,
				created_at datetime NOT NULL,
				updated_at datetime NOT NULL,
				PRIMARY KEY  (id),
				KEY idx_location_id (location_id),
				KEY idx_status_id (status_id),
				KEY idx_usage_id (usage_id),
				KEY idx_name (name(191)),
				KEY idx_model_number (model_number(191)),
				KEY idx_equipment_code (equipment_code(191)),
				KEY idx_installed_date (installed_date),
				KEY idx_updated_at (updated_at)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
			"CREATE TABLE {$repairs} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				equipment_id bigint(20) unsigned NOT NULL,
				reporter_user_id bigint(20) unsigned NOT NULL,
				trouble_location_id bigint(20) unsigned NOT NULL,
				occurred_at datetime NOT NULL,
				vendor_id bigint(20) unsigned DEFAULT NULL,
				vendor_name_free varchar(255) DEFAULT NULL,
				requested_date date DEFAULT NULL,
				desired_completion_date date DEFAULT NULL,
				return_location_id bigint(20) unsigned DEFAULT NULL,
				return_address_free varchar(255) DEFAULT NULL,
				trouble_detail text NOT NULL,
				assignee_user_id bigint(20) unsigned DEFAULT NULL,
				assignee_name varchar(255) DEFAULT NULL,
				repair_cost int(10) unsigned DEFAULT NULL,
				status_id bigint(20) unsigned NOT NULL,
				completed_date date DEFAULT NULL,
				note text,
				created_by bigint(20) unsigned NOT NULL,
				updated_by bigint(20) unsigned NOT NULL,
				created_at datetime NOT NULL,
				updated_at datetime NOT NULL,
				PRIMARY KEY  (id),
				KEY idx_equipment_id (equipment_id),
				KEY idx_trouble_location_id (trouble_location_id),
				KEY idx_status_id (status_id),
				KEY idx_occurred_at (occurred_at),
				KEY idx_requested_date (requested_date),
				KEY idx_desired_completion_date (desired_completion_date),
				KEY idx_completed_date (completed_date),
				KEY idx_repair_cost (repair_cost),
				KEY idx_updated_at (updated_at)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
			"CREATE TABLE {$locations} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				name varchar(255) NOT NULL,
				display_order int(11) NOT NULL DEFAULT 0,
				is_active tinyint(1) NOT NULL DEFAULT 1,
				created_at datetime NOT NULL,
				updated_at datetime NOT NULL,
				PRIMARY KEY  (id),
				KEY idx_name (name(191)),
				KEY idx_is_active (is_active),
				KEY idx_display_order (display_order)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
			"CREATE TABLE {$statuses} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				name varchar(100) NOT NULL,
				display_order int(11) NOT NULL DEFAULT 0,
				is_active tinyint(1) NOT NULL DEFAULT 1,
				created_at datetime NOT NULL,
				updated_at datetime NOT NULL,
				PRIMARY KEY  (id)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
			"CREATE TABLE {$repair_statuses} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				name varchar(100) NOT NULL,
				display_order int(11) NOT NULL DEFAULT 0,
				is_closed tinyint(1) NOT NULL DEFAULT 0,
				is_active tinyint(1) NOT NULL DEFAULT 1,
				created_at datetime NOT NULL,
				updated_at datetime NOT NULL,
				PRIMARY KEY  (id)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
			"CREATE TABLE {$usages} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				name varchar(255) NOT NULL,
				display_order int(11) NOT NULL DEFAULT 0,
				is_active tinyint(1) NOT NULL DEFAULT 1,
				created_at datetime NOT NULL,
				updated_at datetime NOT NULL,
				PRIMARY KEY  (id)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
			"CREATE TABLE {$vendors} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				name varchar(255) NOT NULL,
				phone varchar(50) DEFAULT NULL,
				email varchar(255) DEFAULT NULL,
				address text,
				display_order int(11) NOT NULL DEFAULT 0,
				is_active tinyint(1) NOT NULL DEFAULT 1,
				created_at datetime NOT NULL,
				updated_at datetime NOT NULL,
				PRIMARY KEY  (id)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
			"CREATE TABLE {$attachments} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				target_type varchar(50) NOT NULL,
				target_id bigint(20) unsigned NOT NULL,
				attachment_id bigint(20) unsigned NOT NULL,
				file_name varchar(255) NOT NULL,
				file_size int(10) unsigned NOT NULL,
				created_by bigint(20) unsigned NOT NULL,
				created_at datetime NOT NULL,
				PRIMARY KEY  (id),
				KEY idx_target (target_type, target_id),
				KEY idx_attachment_id (attachment_id)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
			"CREATE TABLE {$logs} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				user_id bigint(20) unsigned DEFAULT NULL,
				action varchar(100) NOT NULL,
				target_type varchar(100) NOT NULL,
				target_id bigint(20) unsigned DEFAULT NULL,
				before_data longtext,
				after_data longtext,
				ip_address varchar(45) DEFAULT NULL,
				user_agent text,
				created_at datetime NOT NULL,
				PRIMARY KEY  (id),
				KEY idx_user_id (user_id),
				KEY idx_action (action),
				KEY idx_target (target_type, target_id),
				KEY idx_created_at (created_at)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
			"CREATE TABLE {$csv_imports} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				import_type varchar(50) NOT NULL,
				file_name varchar(255) NOT NULL,
				total_count int(11) NOT NULL DEFAULT 0,
				success_count int(11) NOT NULL DEFAULT 0,
				error_count int(11) NOT NULL DEFAULT 0,
				error_detail longtext,
				created_by bigint(20) unsigned NOT NULL,
				created_at datetime NOT NULL,
				PRIMARY KEY  (id)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
			"CREATE TABLE {$backup_logs} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				backup_type varchar(50) NOT NULL,
				file_path text NOT NULL,
				file_size bigint(20) unsigned DEFAULT NULL,
				status varchar(50) NOT NULL,
				message text,
				created_at datetime NOT NULL,
				PRIMARY KEY  (id)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
		);
	}

	/**
	 * Seeds a simple active master table with name/display_order columns.
	 *
	 * @param string            $table Table name.
	 * @param array<int,string> $names Master names.
	 * @return void
	 */
	private static function seed_simple_master( $table, $names ) {
		global $wpdb;

		$now = current_time( 'mysql' );

		foreach ( array_values( $names ) as $index => $name ) {
			$exists = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT id FROM {$table} WHERE name = %s LIMIT 1",
					$name
				)
			);

			if ( $exists ) {
				continue;
			}

			$wpdb->insert(
				$table,
				array(
					'name'          => $name,
					'display_order' => $index + 1,
					'is_active'     => 1,
					'created_at'    => $now,
					'updated_at'    => $now,
				),
				array( '%s', '%d', '%d', '%s', '%s' )
			);
		}
	}

	/**
	 * Seeds repair status defaults.
	 *
	 * @return void
	 */
	private static function seed_repair_statuses() {
		global $wpdb;

		$table = self::table( 'repair_statuses' );
		$now   = current_time( 'mysql' );
		$rows  = array(
			array( '未対応', 0 ),
			array( '確認中', 0 ),
			array( '修理依頼済み', 0 ),
			array( '修理中', 0 ),
			array( '完了', 1 ),
			array( 'キャンセル', 1 ),
		);

		foreach ( $rows as $index => $row ) {
			$exists = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT id FROM {$table} WHERE name = %s LIMIT 1",
					$row[0]
				)
			);

			if ( $exists ) {
				continue;
			}

			$wpdb->insert(
				$table,
				array(
					'name'          => $row[0],
					'display_order' => $index + 1,
					'is_closed'     => $row[1],
					'is_active'     => 1,
					'created_at'    => $now,
					'updated_at'    => $now,
				),
				array( '%s', '%d', '%d', '%d', '%s', '%s' )
			);
		}
	}
}
