<?php
/**
 * Repair detail view.
 *
 * @package EquipmentManagement
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap equipment-management">
	<h1><?php esc_html_e( '修理詳細', 'equipment-management' ); ?></h1>

	<?php if ( ! $repair ) : ?>
		<div class="notice notice-error"><p><?php esc_html_e( '修理情報が見つかりません。', 'equipment-management' ); ?></p></div>
	<?php else : ?>
		<p>
			<a class="button" href="<?php echo esc_url( add_query_arg( array( 'page' => Equipment_Management_Admin_Menu::SLUG_REPAIR_NEW, 'repair_id' => $repair->id ), admin_url( 'admin.php' ) ) ); ?>"><?php esc_html_e( '編集', 'equipment-management' ); ?></a>
			<a class="button" href="<?php echo esc_url( add_query_arg( array( 'page' => Equipment_Management_Admin_Menu::SLUG_ITEM_DETAIL, 'equipment_id' => $repair->equipment_id ), admin_url( 'admin.php' ) ) ); ?>"><?php esc_html_e( '対象機材詳細', 'equipment-management' ); ?></a>
			<a class="button" href="<?php echo esc_url( add_query_arg( array( 'page' => Equipment_Management_Admin_Menu::SLUG_REPAIRS ), admin_url( 'admin.php' ) ) ); ?>"><?php esc_html_e( '一覧へ戻る', 'equipment-management' ); ?></a>
		</p>

		<h2><?php esc_html_e( '修理情報', 'equipment-management' ); ?></h2>
		<table class="widefat striped equipment-management-detail-table">
			<tbody>
				<tr><th><?php esc_html_e( '修理ID', 'equipment-management' ); ?></th><td><?php echo esc_html( $repair->id ); ?></td></tr>
				<tr><th><?php esc_html_e( 'ステータス', 'equipment-management' ); ?></th><td><?php echo esc_html( $repair->status_name ); ?></td></tr>
				<tr><th><?php esc_html_e( '発生日時', 'equipment-management' ); ?></th><td><?php echo esc_html( $repair->occurred_at ); ?></td></tr>
				<tr><th><?php esc_html_e( '事故・故障場所', 'equipment-management' ); ?></th><td><?php echo esc_html( $repair->trouble_location_name ); ?></td></tr>
				<tr><th><?php esc_html_e( '記入者', 'equipment-management' ); ?></th><td><?php echo esc_html( $repair->reporter_user_id ); ?></td></tr>
				<tr><th><?php esc_html_e( '修理依頼先', 'equipment-management' ); ?></th><td><?php echo esc_html( $repair->vendor_name ? $repair->vendor_name : $repair->vendor_name_free ); ?></td></tr>
				<tr><th><?php esc_html_e( '依頼日', 'equipment-management' ); ?></th><td><?php echo esc_html( $repair->requested_date ); ?></td></tr>
				<tr><th><?php esc_html_e( '完了希望日', 'equipment-management' ); ?></th><td><?php echo esc_html( $repair->desired_completion_date ); ?></td></tr>
				<tr><th><?php esc_html_e( '修理後の送り先', 'equipment-management' ); ?></th><td><?php echo esc_html( $repair->return_location_name ? $repair->return_location_name : $repair->return_address_free ); ?></td></tr>
				<tr><th><?php esc_html_e( '不具合状況', 'equipment-management' ); ?></th><td><?php echo nl2br( esc_html( $repair->trouble_detail ) ); ?></td></tr>
				<tr><th><?php esc_html_e( '担当者', 'equipment-management' ); ?></th><td><?php echo esc_html( $repair->assignee_name ); ?></td></tr>
				<tr><th><?php esc_html_e( '修理費用', 'equipment-management' ); ?></th><td><?php echo esc_html( $repair->repair_cost ); ?></td></tr>
				<tr><th><?php esc_html_e( '完了日', 'equipment-management' ); ?></th><td><?php echo esc_html( $repair->completed_date ); ?></td></tr>
				<tr><th><?php esc_html_e( '備考', 'equipment-management' ); ?></th><td><?php echo nl2br( esc_html( $repair->note ) ); ?></td></tr>
				<tr><th><?php esc_html_e( '登録者', 'equipment-management' ); ?></th><td><?php echo esc_html( $repair->created_by ); ?></td></tr>
				<tr><th><?php esc_html_e( '更新者', 'equipment-management' ); ?></th><td><?php echo esc_html( $repair->updated_by ); ?></td></tr>
				<tr><th><?php esc_html_e( '登録日時', 'equipment-management' ); ?></th><td><?php echo esc_html( $repair->created_at ); ?></td></tr>
				<tr><th><?php esc_html_e( '更新日時', 'equipment-management' ); ?></th><td><?php echo esc_html( $repair->updated_at ); ?></td></tr>
			</tbody>
		</table>

		<h2><?php esc_html_e( '対象機材', 'equipment-management' ); ?></h2>
		<table class="widefat striped equipment-management-detail-table">
			<tbody>
				<tr><th><?php esc_html_e( '機器名', 'equipment-management' ); ?></th><td><?php echo esc_html( $repair->equipment_name ); ?></td></tr>
				<tr><th><?php esc_html_e( '設置場所', 'equipment-management' ); ?></th><td><?php echo esc_html( $repair->equipment_location_name ); ?></td></tr>
				<tr><th><?php esc_html_e( '型番', 'equipment-management' ); ?></th><td><?php echo esc_html( $repair->model_number ); ?></td></tr>
				<tr><th><?php esc_html_e( '機器コード', 'equipment-management' ); ?></th><td><?php echo esc_html( $repair->equipment_code ); ?></td></tr>
				<tr><th><?php esc_html_e( '導入日', 'equipment-management' ); ?></th><td><?php echo esc_html( $repair->installed_date ); ?></td></tr>
			</tbody>
		</table>

		<h2><?php esc_html_e( '添付ファイル', 'equipment-management' ); ?></h2>
		<?php
		$attachment_redirect   = add_query_arg( array( 'page' => Equipment_Management_Admin_Menu::SLUG_REPAIR_DETAIL, 'repair_id' => $repair->id ), admin_url( 'admin.php' ) );
		$can_delete_attachment = false;
		include equipment_management_path( 'admin/views/attachment-list.php' );
		?>
	<?php endif; ?>
</div>
