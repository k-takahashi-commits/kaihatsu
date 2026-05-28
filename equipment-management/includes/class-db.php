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

		if ( $id > 0 ) {
			$updated = $wpdb->update(
				$table,
				$row,
				array( 'id' => $id )
			);

			return false === $updated ? false : $id;
		}

		$row['created_at'] = $now;
		$inserted          = $wpdb->insert( $table, $row );

		return false === $inserted ? false : (int) $wpdb->insert_id;
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
	public static function get_equipment_rows( $filters = array() ) {
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
			ORDER BY i.updated_at DESC, i.id DESC
			LIMIT 200';

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

		if ( $id > 0 ) {
			$updated = $wpdb->update(
				$table,
				$row,
				array( 'id' => $id )
			);

			return false === $updated ? false : $id;
		}

		$row['created_by'] = get_current_user_id();
		$row['created_at'] = $now;
		$inserted          = $wpdb->insert( $table, $row );

		return false === $inserted ? false : (int) $wpdb->insert_id;
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
