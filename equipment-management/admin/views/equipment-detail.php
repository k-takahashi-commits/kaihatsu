<?php
/**
 * Equipment detail view.
 *
 * @package EquipmentManagement
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap equipment-management">
	<h1><?php esc_html_e( '機材詳細', 'equipment-management' ); ?></h1>

	<?php if ( ! $equipment ) : ?>
		<div class="notice notice-error"><p><?php esc_html_e( '機材が見つかりません。', 'equipment-management' ); ?></p></div>
	<?php else : ?>
		<p>
			<a class="button" href="<?php echo esc_url( add_query_arg( array( 'page' => Equipment_Management_Admin_Menu::SLUG_ITEM_NEW, 'equipment_id' => $equipment->id ), admin_url( 'admin.php' ) ) ); ?>"><?php esc_html_e( '編集', 'equipment-management' ); ?></a>
			<a class="button" href="<?php echo esc_url( add_query_arg( array( 'page' => Equipment_Management_Admin_Menu::SLUG_REPAIR_NEW, 'equipment_id' => $equipment->id ), admin_url( 'admin.php' ) ) ); ?>"><?php esc_html_e( 'この機材の修理を起票', 'equipment-management' ); ?></a>
			<a class="button" href="<?php echo esc_url( add_query_arg( array( 'page' => Equipment_Management_Admin_Menu::SLUG_ITEMS ), admin_url( 'admin.php' ) ) ); ?>"><?php esc_html_e( '一覧へ戻る', 'equipment-management' ); ?></a>
		</p>

		<table class="widefat striped equipment-management-detail-table">
			<tbody>
				<tr><th><?php esc_html_e( '内部ID', 'equipment-management' ); ?></th><td><?php echo esc_html( $equipment->id ); ?></td></tr>
				<tr><th><?php esc_html_e( '機器名', 'equipment-management' ); ?></th><td><?php echo esc_html( $equipment->name ); ?></td></tr>
				<tr><th><?php esc_html_e( '設置場所', 'equipment-management' ); ?></th><td><?php echo esc_html( $equipment->location_name ); ?></td></tr>
				<tr><th><?php esc_html_e( '型番', 'equipment-management' ); ?></th><td><?php echo esc_html( $equipment->model_number ); ?></td></tr>
				<tr><th><?php esc_html_e( '機器コード', 'equipment-management' ); ?></th><td><?php echo esc_html( $equipment->equipment_code ); ?></td></tr>
				<tr><th><?php esc_html_e( '導入日', 'equipment-management' ); ?></th><td><?php echo esc_html( $equipment->installed_date ); ?></td></tr>
				<tr><th><?php esc_html_e( '導入年数', 'equipment-management' ); ?></th><td><?php echo esc_html( Equipment_Management_DB::calculate_years_since( $equipment->installed_date ) ); ?></td></tr>
				<tr><th><?php esc_html_e( '利用用途', 'equipment-management' ); ?></th><td><?php echo esc_html( $equipment->usage_name ); ?></td></tr>
				<tr><th><?php esc_html_e( '状態', 'equipment-management' ); ?></th><td><?php echo esc_html( $equipment->status_name ); ?></td></tr>
				<tr><th><?php esc_html_e( '備考', 'equipment-management' ); ?></th><td><?php echo nl2br( esc_html( $equipment->note ) ); ?></td></tr>
				<tr><th><?php esc_html_e( '登録者', 'equipment-management' ); ?></th><td><?php echo esc_html( $equipment->created_by ); ?></td></tr>
				<tr><th><?php esc_html_e( '更新者', 'equipment-management' ); ?></th><td><?php echo esc_html( $equipment->updated_by ); ?></td></tr>
				<tr><th><?php esc_html_e( '登録日時', 'equipment-management' ); ?></th><td><?php echo esc_html( $equipment->created_at ); ?></td></tr>
				<tr><th><?php esc_html_e( '更新日時', 'equipment-management' ); ?></th><td><?php echo esc_html( $equipment->updated_at ); ?></td></tr>
			</tbody>
		</table>

		<h2><?php esc_html_e( '添付ファイル', 'equipment-management' ); ?></h2>
		<?php
		$attachment_redirect   = add_query_arg( array( 'page' => Equipment_Management_Admin_Menu::SLUG_ITEM_DETAIL, 'equipment_id' => $equipment->id ), admin_url( 'admin.php' ) );
		$can_delete_attachment = false;
		include equipment_management_path( 'admin/views/attachment-list.php' );
		?>

		<h2><?php esc_html_e( '修理履歴', 'equipment-management' ); ?></h2>
		<table class="widefat striped equipment-management-table">
			<thead>
				<tr>
					<th><?php esc_html_e( '修理ID', 'equipment-management' ); ?></th>
					<th><?php esc_html_e( 'ステータス', 'equipment-management' ); ?></th>
					<th><?php esc_html_e( '発生日時', 'equipment-management' ); ?></th>
					<th><?php esc_html_e( '不具合状況', 'equipment-management' ); ?></th>
					<th><?php esc_html_e( '修理依頼先', 'equipment-management' ); ?></th>
					<th><?php esc_html_e( '担当者', 'equipment-management' ); ?></th>
					<th><?php esc_html_e( '修理費用', 'equipment-management' ); ?></th>
					<th><?php esc_html_e( '完了希望日', 'equipment-management' ); ?></th>
					<th><?php esc_html_e( '完了日', 'equipment-management' ); ?></th>
					<th><?php esc_html_e( '操作', 'equipment-management' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $repairs ) ) : ?>
					<tr><td colspan="10"><?php esc_html_e( '修理履歴がありません。', 'equipment-management' ); ?></td></tr>
				<?php else : ?>
					<?php foreach ( $repairs as $repair ) : ?>
						<tr>
							<td><?php echo esc_html( $repair->id ); ?></td>
							<td><?php echo esc_html( $repair->status_name ); ?></td>
							<td><?php echo esc_html( $repair->occurred_at ); ?></td>
							<td><?php echo esc_html( wp_html_excerpt( $repair->trouble_detail, 40, '...' ) ); ?></td>
							<td><?php echo esc_html( $repair->vendor_name ? $repair->vendor_name : $repair->vendor_name_free ); ?></td>
							<td><?php echo esc_html( $repair->assignee_name ); ?></td>
							<td><?php echo esc_html( $repair->repair_cost ); ?></td>
							<td><?php echo esc_html( $repair->desired_completion_date ); ?></td>
							<td><?php echo esc_html( $repair->completed_date ); ?></td>
							<td>
								<a href="<?php echo esc_url( add_query_arg( array( 'page' => Equipment_Management_Admin_Menu::SLUG_REPAIR_DETAIL, 'repair_id' => $repair->id ), admin_url( 'admin.php' ) ) ); ?>"><?php esc_html_e( '詳細', 'equipment-management' ); ?></a>
								|
								<a href="<?php echo esc_url( add_query_arg( array( 'page' => Equipment_Management_Admin_Menu::SLUG_REPAIR_NEW, 'repair_id' => $repair->id ), admin_url( 'admin.php' ) ) ); ?>"><?php esc_html_e( '編集', 'equipment-management' ); ?></a>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
	<?php endif; ?>
</div>
