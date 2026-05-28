<?php
/**
 * Equipment form view.
 *
 * @package EquipmentManagement
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$is_edit = ! empty( $equipment );
?>

<div class="wrap equipment-management">
	<h1><?php echo $is_edit ? esc_html__( '機材編集', 'equipment-management' ) : esc_html__( '機材登録', 'equipment-management' ); ?></h1>

	<?php if ( 'error' === $notice ) : ?>
		<div class="notice notice-error is-dismissible"><p><?php esc_html_e( '保存に失敗しました。必須項目を確認してください。', 'equipment-management' ); ?></p></div>
	<?php endif; ?>

	<?php if ( empty( $masters['locations'] ) || empty( $masters['statuses'] ) ) : ?>
		<div class="notice notice-warning"><p><?php esc_html_e( '登録には、場所マスターと状態マスターが必要です。先にマスター管理で登録してください。', 'equipment-management' ); ?></p></div>
	<?php endif; ?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="equipment-management-form">
		<input type="hidden" name="action" value="equipment_management_save_equipment">
		<input type="hidden" name="equipment_id" value="<?php echo esc_attr( $is_edit ? $equipment->id : 0 ); ?>">
		<?php wp_nonce_field( 'equipment_management_save_equipment' ); ?>

		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="equipment-name"><?php esc_html_e( '機器名', 'equipment-management' ); ?> <span class="description"><?php esc_html_e( '必須', 'equipment-management' ); ?></span></label></th>
				<td><input name="name" id="equipment-name" type="text" class="regular-text" value="<?php echo esc_attr( $is_edit ? $equipment->name : '' ); ?>" required></td>
			</tr>
			<tr>
				<th scope="row"><label for="equipment-location-id"><?php esc_html_e( '設置場所', 'equipment-management' ); ?> <span class="description"><?php esc_html_e( '必須', 'equipment-management' ); ?></span></label></th>
				<td>
					<select name="location_id" id="equipment-location-id" required>
						<option value="0"><?php esc_html_e( '選択してください', 'equipment-management' ); ?></option>
						<?php foreach ( $masters['locations'] as $location ) : ?>
							<option value="<?php echo esc_attr( $location->id ); ?>" <?php selected( $is_edit ? $equipment->location_id : 0, $location->id ); ?>><?php echo esc_html( $location->name ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="equipment-model-number"><?php esc_html_e( '型番', 'equipment-management' ); ?></label></th>
				<td><input name="model_number" id="equipment-model-number" type="text" class="regular-text" value="<?php echo esc_attr( $is_edit ? $equipment->model_number : '' ); ?>"></td>
			</tr>
			<tr>
				<th scope="row"><label for="equipment-code"><?php esc_html_e( '機器コード', 'equipment-management' ); ?></label></th>
				<td><input name="equipment_code" id="equipment-code" type="text" class="regular-text" value="<?php echo esc_attr( $is_edit ? $equipment->equipment_code : '' ); ?>"></td>
			</tr>
			<tr>
				<th scope="row"><label for="equipment-installed-date"><?php esc_html_e( '導入日', 'equipment-management' ); ?> <span class="description"><?php esc_html_e( '必須', 'equipment-management' ); ?></span></label></th>
				<td><input name="installed_date" id="equipment-installed-date" type="date" value="<?php echo esc_attr( $is_edit ? $equipment->installed_date : '' ); ?>" required></td>
			</tr>
			<tr>
				<th scope="row"><label for="equipment-usage-id"><?php esc_html_e( '利用用途', 'equipment-management' ); ?></label></th>
				<td>
					<select name="usage_id" id="equipment-usage-id">
						<option value="0"><?php esc_html_e( '選択なし', 'equipment-management' ); ?></option>
						<?php foreach ( $masters['usages'] as $usage ) : ?>
							<option value="<?php echo esc_attr( $usage->id ); ?>" <?php selected( $is_edit ? $equipment->usage_id : 0, $usage->id ); ?>><?php echo esc_html( $usage->name ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="equipment-status-id"><?php esc_html_e( '状態', 'equipment-management' ); ?> <span class="description"><?php esc_html_e( '必須', 'equipment-management' ); ?></span></label></th>
				<td>
					<select name="status_id" id="equipment-status-id" required>
						<option value="0"><?php esc_html_e( '選択してください', 'equipment-management' ); ?></option>
						<?php foreach ( $masters['statuses'] as $status ) : ?>
							<option value="<?php echo esc_attr( $status->id ); ?>" <?php selected( $is_edit ? $equipment->status_id : 0, $status->id ); ?>><?php echo esc_html( $status->name ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="equipment-note"><?php esc_html_e( '備考', 'equipment-management' ); ?></label></th>
				<td><textarea name="note" id="equipment-note" class="large-text" rows="5"><?php echo esc_textarea( $is_edit ? $equipment->note : '' ); ?></textarea></td>
			</tr>
		</table>

		<?php submit_button( $is_edit ? __( '更新', 'equipment-management' ) : __( '登録', 'equipment-management' ) ); ?>
	</form>
</div>
