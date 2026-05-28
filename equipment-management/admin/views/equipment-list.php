<?php
/**
 * Equipment list view.
 *
 * @package EquipmentManagement
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap equipment-management">
	<h1 class="wp-heading-inline"><?php esc_html_e( '機材一覧', 'equipment-management' ); ?></h1>
	<a href="<?php echo esc_url( add_query_arg( array( 'page' => Equipment_Management_Admin_Menu::SLUG_ITEM_NEW ), admin_url( 'admin.php' ) ) ); ?>" class="page-title-action">
		<?php esc_html_e( '新規登録', 'equipment-management' ); ?>
	</a>
	<hr class="wp-header-end">

	<?php if ( 'saved' === $notice ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php esc_html_e( '機材を保存しました。', 'equipment-management' ); ?></p></div>
	<?php endif; ?>

	<form method="get" class="equipment-management-search">
		<input type="hidden" name="page" value="<?php echo esc_attr( Equipment_Management_Admin_Menu::SLUG_ITEMS ); ?>">
		<div class="equipment-management-search-grid">
			<label>
				<span><?php esc_html_e( '機器名', 'equipment-management' ); ?></span>
				<input type="search" name="equipment_name" value="<?php echo esc_attr( $filters['name'] ); ?>">
			</label>
			<label>
				<span><?php esc_html_e( '設置場所', 'equipment-management' ); ?></span>
				<select name="location_id">
					<option value="0"><?php esc_html_e( 'すべて', 'equipment-management' ); ?></option>
					<?php foreach ( $masters['locations'] as $location ) : ?>
						<option value="<?php echo esc_attr( $location->id ); ?>" <?php selected( $filters['location_id'], $location->id ); ?>><?php echo esc_html( $location->name ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
			<label>
				<span><?php esc_html_e( '型番', 'equipment-management' ); ?></span>
				<input type="search" name="model_number" value="<?php echo esc_attr( $filters['model_number'] ); ?>">
			</label>
			<label>
				<span><?php esc_html_e( '機器コード', 'equipment-management' ); ?></span>
				<input type="search" name="equipment_code" value="<?php echo esc_attr( $filters['equipment_code'] ); ?>">
			</label>
			<label>
				<span><?php esc_html_e( '導入日 From', 'equipment-management' ); ?></span>
				<input type="date" name="installed_from" value="<?php echo esc_attr( $filters['installed_from'] ); ?>">
			</label>
			<label>
				<span><?php esc_html_e( '導入日 To', 'equipment-management' ); ?></span>
				<input type="date" name="installed_to" value="<?php echo esc_attr( $filters['installed_to'] ); ?>">
			</label>
			<label>
				<span><?php esc_html_e( '利用用途', 'equipment-management' ); ?></span>
				<select name="usage_id">
					<option value="0"><?php esc_html_e( 'すべて', 'equipment-management' ); ?></option>
					<?php foreach ( $masters['usages'] as $usage ) : ?>
						<option value="<?php echo esc_attr( $usage->id ); ?>" <?php selected( $filters['usage_id'], $usage->id ); ?>><?php echo esc_html( $usage->name ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
			<label>
				<span><?php esc_html_e( '状態', 'equipment-management' ); ?></span>
				<select name="status_id">
					<option value="0"><?php esc_html_e( 'すべて', 'equipment-management' ); ?></option>
					<?php foreach ( $masters['statuses'] as $status ) : ?>
						<option value="<?php echo esc_attr( $status->id ); ?>" <?php selected( $filters['status_id'], $status->id ); ?>><?php echo esc_html( $status->name ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
		</div>
		<?php submit_button( __( '検索', 'equipment-management' ), 'secondary', '', false ); ?>
		<a class="button" href="<?php echo esc_url( add_query_arg( array( 'page' => Equipment_Management_Admin_Menu::SLUG_ITEMS ), admin_url( 'admin.php' ) ) ); ?>"><?php esc_html_e( 'クリア', 'equipment-management' ); ?></a>
	</form>

	<table class="widefat striped equipment-management-table">
		<thead>
			<tr>
				<th scope="col"><?php esc_html_e( '内部ID', 'equipment-management' ); ?></th>
				<th scope="col"><?php esc_html_e( '機器名', 'equipment-management' ); ?></th>
				<th scope="col"><?php esc_html_e( '設置場所', 'equipment-management' ); ?></th>
				<th scope="col"><?php esc_html_e( '型番', 'equipment-management' ); ?></th>
				<th scope="col"><?php esc_html_e( '機器コード', 'equipment-management' ); ?></th>
				<th scope="col"><?php esc_html_e( '導入日', 'equipment-management' ); ?></th>
				<th scope="col"><?php esc_html_e( '導入年数', 'equipment-management' ); ?></th>
				<th scope="col"><?php esc_html_e( '利用用途', 'equipment-management' ); ?></th>
				<th scope="col"><?php esc_html_e( '状態', 'equipment-management' ); ?></th>
				<th scope="col"><?php esc_html_e( '最終修理日', 'equipment-management' ); ?></th>
				<th scope="col"><?php esc_html_e( '更新日時', 'equipment-management' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( empty( $rows ) ) : ?>
				<tr>
					<td colspan="11"><?php esc_html_e( '機材データがありません。', 'equipment-management' ); ?></td>
				</tr>
			<?php else : ?>
				<?php foreach ( $rows as $row ) : ?>
					<tr>
						<td><?php echo esc_html( $row->id ); ?></td>
						<td>
							<a href="<?php echo esc_url( add_query_arg( array( 'page' => Equipment_Management_Admin_Menu::SLUG_ITEM_NEW, 'equipment_id' => $row->id ), admin_url( 'admin.php' ) ) ); ?>">
								<?php echo esc_html( $row->name ); ?>
							</a>
						</td>
						<td><?php echo esc_html( $row->location_name ); ?></td>
						<td><?php echo esc_html( $row->model_number ); ?></td>
						<td><?php echo esc_html( $row->equipment_code ); ?></td>
						<td><?php echo esc_html( $row->installed_date ); ?></td>
						<td><?php echo esc_html( Equipment_Management_DB::calculate_years_since( $row->installed_date ) ); ?></td>
						<td><?php echo esc_html( $row->usage_name ); ?></td>
						<td><?php echo esc_html( $row->status_name ); ?></td>
						<td><?php echo esc_html( $row->last_repair_date ); ?></td>
						<td><?php echo esc_html( $row->updated_at ); ?></td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>
</div>
