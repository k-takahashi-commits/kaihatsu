<?php
/**
 * Repair list view.
 *
 * @package EquipmentManagement
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$export_url = wp_nonce_url(
	add_query_arg(
		array_merge(
			$_GET,
			array(
				'action' => 'equipment_management_export_repairs',
			)
		),
		admin_url( 'admin-post.php' )
	),
	'equipment_management_export_repairs'
);
?>

<div class="wrap equipment-management">
	<h1 class="wp-heading-inline"><?php esc_html_e( '修理一覧', 'equipment-management' ); ?></h1>
	<a href="<?php echo esc_url( add_query_arg( array( 'page' => Equipment_Management_Admin_Menu::SLUG_REPAIR_NEW ), admin_url( 'admin.php' ) ) ); ?>" class="page-title-action"><?php esc_html_e( '修理起票', 'equipment-management' ); ?></a>
	<a href="<?php echo esc_url( $export_url ); ?>" class="page-title-action"><?php esc_html_e( 'CSVエクスポート', 'equipment-management' ); ?></a>
	<hr class="wp-header-end">

	<?php if ( 'saved' === $notice ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php esc_html_e( '修理情報を保存しました。', 'equipment-management' ); ?></p></div>
	<?php endif; ?>

	<?php if ( 'attachment_error' === $attachment_notice ) : ?>
		<div class="notice notice-error is-dismissible"><p><?php esc_html_e( '添付ファイルの保存に失敗しました。ファイル数は最大3件、1ファイル5MBまでです。', 'equipment-management' ); ?></p></div>
	<?php endif; ?>

	<form method="get" class="equipment-management-search">
		<input type="hidden" name="page" value="<?php echo esc_attr( Equipment_Management_Admin_Menu::SLUG_REPAIRS ); ?>">
		<div class="equipment-management-search-grid">
			<label><span><?php esc_html_e( '発生日 From', 'equipment-management' ); ?></span><input type="date" name="occurred_from" value="<?php echo esc_attr( $filters['occurred_from'] ); ?>"></label>
			<label><span><?php esc_html_e( '発生日 To', 'equipment-management' ); ?></span><input type="date" name="occurred_to" value="<?php echo esc_attr( $filters['occurred_to'] ); ?>"></label>
			<label><span><?php esc_html_e( '依頼日 From', 'equipment-management' ); ?></span><input type="date" name="requested_from" value="<?php echo esc_attr( $filters['requested_from'] ); ?>"></label>
			<label><span><?php esc_html_e( '依頼日 To', 'equipment-management' ); ?></span><input type="date" name="requested_to" value="<?php echo esc_attr( $filters['requested_to'] ); ?>"></label>
			<label><span><?php esc_html_e( '完了希望日 From', 'equipment-management' ); ?></span><input type="date" name="desired_from" value="<?php echo esc_attr( $filters['desired_from'] ); ?>"></label>
			<label><span><?php esc_html_e( '完了希望日 To', 'equipment-management' ); ?></span><input type="date" name="desired_to" value="<?php echo esc_attr( $filters['desired_to'] ); ?>"></label>
			<label><span><?php esc_html_e( '完了日 From', 'equipment-management' ); ?></span><input type="date" name="completed_from" value="<?php echo esc_attr( $filters['completed_from'] ); ?>"></label>
			<label><span><?php esc_html_e( '完了日 To', 'equipment-management' ); ?></span><input type="date" name="completed_to" value="<?php echo esc_attr( $filters['completed_to'] ); ?>"></label>
			<label>
				<span><?php esc_html_e( '場所', 'equipment-management' ); ?></span>
				<select name="trouble_location_id">
					<option value="0"><?php esc_html_e( 'すべて', 'equipment-management' ); ?></option>
					<?php foreach ( $masters['locations'] as $location ) : ?>
						<option value="<?php echo esc_attr( $location->id ); ?>" <?php selected( $filters['trouble_location_id'], $location->id ); ?>><?php echo esc_html( $location->name ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
			<label><span><?php esc_html_e( '機器名', 'equipment-management' ); ?></span><input type="search" name="equipment_name" value="<?php echo esc_attr( $filters['equipment_name'] ); ?>"></label>
			<label><span><?php esc_html_e( '型番', 'equipment-management' ); ?></span><input type="search" name="model_number" value="<?php echo esc_attr( $filters['model_number'] ); ?>"></label>
			<label><span><?php esc_html_e( '機器コード', 'equipment-management' ); ?></span><input type="search" name="equipment_code" value="<?php echo esc_attr( $filters['equipment_code'] ); ?>"></label>
			<label>
				<span><?php esc_html_e( 'ステータス', 'equipment-management' ); ?></span>
				<select name="status_id">
					<option value="0"><?php esc_html_e( 'すべて', 'equipment-management' ); ?></option>
					<?php foreach ( $masters['repair_statuses'] as $status ) : ?>
						<option value="<?php echo esc_attr( $status->id ); ?>" <?php selected( $filters['status_id'], $status->id ); ?>><?php echo esc_html( $status->name ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
			<label><span><?php esc_html_e( '担当者', 'equipment-management' ); ?></span><input type="search" name="assignee" value="<?php echo esc_attr( $filters['assignee'] ); ?>"></label>
			<label><span><?php esc_html_e( '修理依頼先', 'equipment-management' ); ?></span><input type="search" name="vendor" value="<?php echo esc_attr( $filters['vendor'] ); ?>"></label>
			<label><span><?php esc_html_e( '修理費用 From', 'equipment-management' ); ?></span><input type="number" name="cost_from" value="<?php echo esc_attr( $filters['cost_from'] ); ?>"></label>
			<label><span><?php esc_html_e( '修理費用 To', 'equipment-management' ); ?></span><input type="number" name="cost_to" value="<?php echo esc_attr( $filters['cost_to'] ); ?>"></label>
		</div>
		<?php submit_button( __( '検索', 'equipment-management' ), 'secondary', '', false ); ?>
		<a class="button" href="<?php echo esc_url( add_query_arg( array( 'page' => Equipment_Management_Admin_Menu::SLUG_REPAIRS ), admin_url( 'admin.php' ) ) ); ?>"><?php esc_html_e( 'クリア', 'equipment-management' ); ?></a>
	</form>

	<table class="widefat striped equipment-management-table">
		<thead>
			<tr>
				<th><?php esc_html_e( '修理ID', 'equipment-management' ); ?></th>
				<th><?php esc_html_e( 'ステータス', 'equipment-management' ); ?></th>
				<th><?php esc_html_e( '発生日時', 'equipment-management' ); ?></th>
				<th><?php esc_html_e( '機器名', 'equipment-management' ); ?></th>
				<th><?php esc_html_e( '設置場所', 'equipment-management' ); ?></th>
				<th><?php esc_html_e( '型番', 'equipment-management' ); ?></th>
				<th><?php esc_html_e( '機器コード', 'equipment-management' ); ?></th>
				<th><?php esc_html_e( '修理依頼先', 'equipment-management' ); ?></th>
				<th><?php esc_html_e( '担当者', 'equipment-management' ); ?></th>
				<th><?php esc_html_e( '完了希望日', 'equipment-management' ); ?></th>
				<th><?php esc_html_e( '修理費用', 'equipment-management' ); ?></th>
				<th><?php esc_html_e( '操作', 'equipment-management' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( empty( $rows ) ) : ?>
				<tr><td colspan="12"><?php esc_html_e( '修理データがありません。', 'equipment-management' ); ?></td></tr>
			<?php else : ?>
				<?php foreach ( $rows as $row ) : ?>
					<tr>
						<td><?php echo esc_html( $row->id ); ?></td>
						<td><?php echo esc_html( $row->status_name ); ?></td>
						<td><?php echo esc_html( $row->occurred_at ); ?></td>
						<td><a href="<?php echo esc_url( add_query_arg( array( 'page' => Equipment_Management_Admin_Menu::SLUG_ITEM_DETAIL, 'equipment_id' => $row->equipment_id ), admin_url( 'admin.php' ) ) ); ?>"><?php echo esc_html( $row->equipment_name ); ?></a></td>
						<td><?php echo esc_html( $row->equipment_location_name ); ?></td>
						<td><?php echo esc_html( $row->model_number ); ?></td>
						<td><?php echo esc_html( $row->equipment_code ); ?></td>
						<td><?php echo esc_html( $row->vendor_name ? $row->vendor_name : $row->vendor_name_free ); ?></td>
						<td><?php echo esc_html( $row->assignee_name ); ?></td>
						<td><?php echo esc_html( $row->desired_completion_date ); ?></td>
						<td><?php echo esc_html( $row->repair_cost ); ?></td>
						<td>
							<a href="<?php echo esc_url( add_query_arg( array( 'page' => Equipment_Management_Admin_Menu::SLUG_REPAIR_DETAIL, 'repair_id' => $row->id ), admin_url( 'admin.php' ) ) ); ?>"><?php esc_html_e( '詳細', 'equipment-management' ); ?></a>
							|
							<a href="<?php echo esc_url( add_query_arg( array( 'page' => Equipment_Management_Admin_Menu::SLUG_REPAIR_NEW, 'repair_id' => $row->id ), admin_url( 'admin.php' ) ) ); ?>"><?php esc_html_e( '編集', 'equipment-management' ); ?></a>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>
</div>
