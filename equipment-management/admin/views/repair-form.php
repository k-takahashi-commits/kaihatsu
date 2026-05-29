<?php
/**
 * Repair form view.
 *
 * @package EquipmentManagement
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$is_edit              = ! empty( $repair );
$selected_equipment_id = $is_edit ? (int) $repair->equipment_id : ( isset( $_GET['equipment_id'] ) ? absint( $_GET['equipment_id'] ) : 0 );
$occurred_value      = $is_edit && ! empty( $repair->occurred_at ) ? str_replace( ' ', 'T', substr( $repair->occurred_at, 0, 16 ) ) : '';
?>

<div class="wrap equipment-management">
	<h1><?php echo $is_edit ? esc_html__( '修理編集', 'equipment-management' ) : esc_html__( '修理起票', 'equipment-management' ); ?></h1>

	<?php if ( 'error' === $notice ) : ?>
		<div class="notice notice-error is-dismissible"><p><?php esc_html_e( '保存に失敗しました。必須項目を確認してください。', 'equipment-management' ); ?></p></div>
	<?php endif; ?>

	<?php if ( empty( $masters['equipment'] ) || empty( $masters['locations'] ) || empty( $masters['repair_statuses'] ) ) : ?>
		<div class="notice notice-warning"><p><?php esc_html_e( '修理起票には機材、場所マスター、修理ステータスマスターが必要です。', 'equipment-management' ); ?></p></div>
	<?php endif; ?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="equipment-management-form">
		<input type="hidden" name="action" value="equipment_management_save_repair">
		<input type="hidden" name="repair_id" value="<?php echo esc_attr( $is_edit ? $repair->id : 0 ); ?>">
		<input type="hidden" name="reporter_user_id" value="<?php echo esc_attr( $is_edit ? $repair->reporter_user_id : get_current_user_id() ); ?>">
		<?php wp_nonce_field( 'equipment_management_save_repair' ); ?>

		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="repair-equipment-id"><?php esc_html_e( '対象機材', 'equipment-management' ); ?> <span class="description"><?php esc_html_e( '必須', 'equipment-management' ); ?></span></label></th>
				<td>
					<select name="equipment_id" id="repair-equipment-id" required>
						<option value="0"><?php esc_html_e( '選択してください', 'equipment-management' ); ?></option>
						<?php foreach ( $masters['equipment'] as $equipment ) : ?>
							<?php
							$label = sprintf(
								'%s / %s / %s / %s',
								$equipment->name,
								$equipment->location_name,
								$equipment->model_number,
								$equipment->equipment_code
							);
							?>
							<option value="<?php echo esc_attr( $equipment->id ); ?>" <?php selected( $selected_equipment_id, $equipment->id ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="repair-trouble-location-id"><?php esc_html_e( '事故・故障場所', 'equipment-management' ); ?> <span class="description"><?php esc_html_e( '必須', 'equipment-management' ); ?></span></label></th>
				<td>
					<select name="trouble_location_id" id="repair-trouble-location-id" required>
						<option value="0"><?php esc_html_e( '選択してください', 'equipment-management' ); ?></option>
						<?php foreach ( $masters['locations'] as $location ) : ?>
							<option value="<?php echo esc_attr( $location->id ); ?>" <?php selected( $is_edit ? $repair->trouble_location_id : 0, $location->id ); ?>><?php echo esc_html( $location->name ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="repair-occurred-at"><?php esc_html_e( '発生日時', 'equipment-management' ); ?> <span class="description"><?php esc_html_e( '必須', 'equipment-management' ); ?></span></label></th>
				<td><input name="occurred_at" id="repair-occurred-at" type="datetime-local" value="<?php echo esc_attr( $occurred_value ); ?>" required></td>
			</tr>
			<tr>
				<th scope="row"><label for="repair-vendor-id"><?php esc_html_e( '修理依頼先', 'equipment-management' ); ?></label></th>
				<td>
					<select name="vendor_id" id="repair-vendor-id">
						<option value="0"><?php esc_html_e( 'マスター選択なし', 'equipment-management' ); ?></option>
						<?php foreach ( $masters['vendors'] as $vendor ) : ?>
							<option value="<?php echo esc_attr( $vendor->id ); ?>" <?php selected( $is_edit ? $repair->vendor_id : 0, $vendor->id ); ?>><?php echo esc_html( $vendor->name ); ?></option>
						<?php endforeach; ?>
					</select>
					<input name="vendor_name_free" type="text" class="regular-text" placeholder="<?php esc_attr_e( '自由入力', 'equipment-management' ); ?>" value="<?php echo esc_attr( $is_edit ? $repair->vendor_name_free : '' ); ?>">
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="repair-requested-date"><?php esc_html_e( '依頼日', 'equipment-management' ); ?></label></th>
				<td><input name="requested_date" id="repair-requested-date" type="date" value="<?php echo esc_attr( $is_edit ? $repair->requested_date : '' ); ?>"></td>
			</tr>
			<tr>
				<th scope="row"><label for="repair-desired-completion-date"><?php esc_html_e( '完了希望日', 'equipment-management' ); ?></label></th>
				<td><input name="desired_completion_date" id="repair-desired-completion-date" type="date" value="<?php echo esc_attr( $is_edit ? $repair->desired_completion_date : '' ); ?>"></td>
			</tr>
			<tr>
				<th scope="row"><label for="repair-return-location-id"><?php esc_html_e( '修理後の送り先', 'equipment-management' ); ?></label></th>
				<td>
					<select name="return_location_id" id="repair-return-location-id">
						<option value="0"><?php esc_html_e( '場所選択なし', 'equipment-management' ); ?></option>
						<?php foreach ( $masters['locations'] as $location ) : ?>
							<option value="<?php echo esc_attr( $location->id ); ?>" <?php selected( $is_edit ? $repair->return_location_id : 0, $location->id ); ?>><?php echo esc_html( $location->name ); ?></option>
						<?php endforeach; ?>
					</select>
					<input name="return_address_free" type="text" class="regular-text" placeholder="<?php esc_attr_e( '自由入力', 'equipment-management' ); ?>" value="<?php echo esc_attr( $is_edit ? $repair->return_address_free : '' ); ?>">
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="repair-trouble-detail"><?php esc_html_e( '不具合状況', 'equipment-management' ); ?> <span class="description"><?php esc_html_e( '必須', 'equipment-management' ); ?></span></label></th>
				<td><textarea name="trouble_detail" id="repair-trouble-detail" class="large-text" rows="5" required><?php echo esc_textarea( $is_edit ? $repair->trouble_detail : '' ); ?></textarea></td>
			</tr>
			<tr>
				<th scope="row"><label for="repair-assignee-name"><?php esc_html_e( '担当者', 'equipment-management' ); ?></label></th>
				<td><input name="assignee_name" id="repair-assignee-name" type="text" class="regular-text" value="<?php echo esc_attr( $is_edit ? $repair->assignee_name : '' ); ?>"></td>
			</tr>
			<tr>
				<th scope="row"><label for="repair-cost"><?php esc_html_e( '修理費用', 'equipment-management' ); ?></label></th>
				<td><input name="repair_cost" id="repair-cost" type="number" min="0" step="1" value="<?php echo esc_attr( $is_edit ? $repair->repair_cost : '' ); ?>"></td>
			</tr>
			<tr>
				<th scope="row"><label for="repair-status-id"><?php esc_html_e( 'ステータス', 'equipment-management' ); ?> <span class="description"><?php esc_html_e( '必須', 'equipment-management' ); ?></span></label></th>
				<td>
					<select name="status_id" id="repair-status-id" required>
						<option value="0"><?php esc_html_e( '選択してください', 'equipment-management' ); ?></option>
						<?php foreach ( $masters['repair_statuses'] as $status ) : ?>
							<option value="<?php echo esc_attr( $status->id ); ?>" <?php selected( $is_edit ? $repair->status_id : 0, $status->id ); ?>><?php echo esc_html( $status->name ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="repair-completed-date"><?php esc_html_e( '完了日', 'equipment-management' ); ?></label></th>
				<td><input name="completed_date" id="repair-completed-date" type="date" value="<?php echo esc_attr( $is_edit ? $repair->completed_date : '' ); ?>"></td>
			</tr>
			<tr>
				<th scope="row"><label for="repair-note"><?php esc_html_e( '備考', 'equipment-management' ); ?></label></th>
				<td><textarea name="note" id="repair-note" class="large-text" rows="4"><?php echo esc_textarea( $is_edit ? $repair->note : '' ); ?></textarea></td>
			</tr>
		</table>

		<?php submit_button( $is_edit ? __( '更新', 'equipment-management' ) : __( '登録', 'equipment-management' ) ); ?>
	</form>
</div>
