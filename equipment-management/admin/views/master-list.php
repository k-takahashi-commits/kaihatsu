<?php
/**
 * Master list view.
 *
 * @package EquipmentManagement
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$definition = $master_definitions[ $current_type ];
?>

<div class="wrap equipment-management">
	<h1><?php esc_html_e( 'マスター管理', 'equipment-management' ); ?></h1>

	<?php if ( 'saved' === $notice ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'マスターを保存しました。', 'equipment-management' ); ?></p></div>
	<?php elseif ( 'error' === $notice ) : ?>
		<div class="notice notice-error is-dismissible"><p><?php esc_html_e( '保存に失敗しました。入力内容を確認してください。', 'equipment-management' ); ?></p></div>
	<?php elseif ( 'invalid' === $notice ) : ?>
		<div class="notice notice-error is-dismissible"><p><?php esc_html_e( '不正なマスター種別です。', 'equipment-management' ); ?></p></div>
	<?php endif; ?>

	<nav class="nav-tab-wrapper equipment-management-tabs" aria-label="<?php esc_attr_e( 'マスター種別', 'equipment-management' ); ?>">
		<?php foreach ( $master_definitions as $type => $master_definition ) : ?>
			<a
				class="nav-tab <?php echo $type === $current_type ? 'nav-tab-active' : ''; ?>"
				href="<?php echo esc_url( add_query_arg( array( 'page' => Equipment_Management_Admin_Menu::SLUG_MASTERS, 'master_type' => $type ), admin_url( 'admin.php' ) ) ); ?>"
			>
				<?php echo esc_html( $master_definition['label'] ); ?>
			</a>
		<?php endforeach; ?>
	</nav>

	<div class="equipment-management-master-grid">
		<section class="equipment-management-master-list" aria-labelledby="equipment-management-master-list-title">
			<h2 id="equipment-management-master-list-title">
				<?php
				printf(
					/* translators: %s: master label. */
					esc_html__( '%s 一覧', 'equipment-management' ),
					esc_html( $definition['label'] )
				);
				?>
			</h2>

			<table class="widefat striped">
				<thead>
					<tr>
						<th scope="col"><?php esc_html_e( 'ID', 'equipment-management' ); ?></th>
						<th scope="col"><?php esc_html_e( '名称', 'equipment-management' ); ?></th>
						<?php if ( 'vendors' === $current_type ) : ?>
							<th scope="col"><?php esc_html_e( '電話番号', 'equipment-management' ); ?></th>
							<th scope="col"><?php esc_html_e( 'メールアドレス', 'equipment-management' ); ?></th>
						<?php endif; ?>
						<?php if ( 'repair_statuses' === $current_type ) : ?>
							<th scope="col"><?php esc_html_e( '完了扱い', 'equipment-management' ); ?></th>
						<?php endif; ?>
						<th scope="col"><?php esc_html_e( '表示順', 'equipment-management' ); ?></th>
						<th scope="col"><?php esc_html_e( '有効', 'equipment-management' ); ?></th>
						<th scope="col"><?php esc_html_e( '操作', 'equipment-management' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( empty( $rows ) ) : ?>
						<tr>
							<td colspan="8"><?php esc_html_e( 'データがありません。', 'equipment-management' ); ?></td>
						</tr>
					<?php else : ?>
						<?php foreach ( $rows as $row ) : ?>
							<tr>
								<td><?php echo esc_html( $row->id ); ?></td>
								<td><?php echo esc_html( $row->name ); ?></td>
								<?php if ( 'vendors' === $current_type ) : ?>
									<td><?php echo esc_html( $row->phone ); ?></td>
									<td><?php echo esc_html( $row->email ); ?></td>
								<?php endif; ?>
								<?php if ( 'repair_statuses' === $current_type ) : ?>
									<td><?php echo ! empty( $row->is_closed ) ? esc_html__( 'はい', 'equipment-management' ) : esc_html__( 'いいえ', 'equipment-management' ); ?></td>
								<?php endif; ?>
								<td><?php echo esc_html( $row->display_order ); ?></td>
								<td><?php echo ! empty( $row->is_active ) ? esc_html__( '有効', 'equipment-management' ) : esc_html__( '無効', 'equipment-management' ); ?></td>
								<td>
									<a href="<?php echo esc_url( add_query_arg( array( 'page' => Equipment_Management_Admin_Menu::SLUG_MASTERS, 'master_type' => $current_type, 'edit_id' => $row->id ), admin_url( 'admin.php' ) ) ); ?>">
										<?php esc_html_e( '編集', 'equipment-management' ); ?>
									</a>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
		</section>

		<section class="equipment-management-master-form" aria-labelledby="equipment-management-master-form-title">
			<h2 id="equipment-management-master-form-title">
				<?php echo $edit_row ? esc_html__( 'マスター編集', 'equipment-management' ) : esc_html__( 'マスター追加', 'equipment-management' ); ?>
			</h2>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="equipment_management_save_master">
				<input type="hidden" name="master_type" value="<?php echo esc_attr( $current_type ); ?>">
				<input type="hidden" name="master_id" value="<?php echo esc_attr( $edit_row ? $edit_row->id : 0 ); ?>">
				<?php wp_nonce_field( 'equipment_management_save_master' ); ?>

				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="equipment-master-name"><?php esc_html_e( '名称', 'equipment-management' ); ?></label></th>
						<td><input name="name" id="equipment-master-name" type="text" class="regular-text" value="<?php echo esc_attr( $edit_row ? $edit_row->name : '' ); ?>" required></td>
					</tr>
					<?php if ( 'vendors' === $current_type ) : ?>
						<tr>
							<th scope="row"><label for="equipment-master-phone"><?php esc_html_e( '電話番号', 'equipment-management' ); ?></label></th>
							<td><input name="phone" id="equipment-master-phone" type="text" class="regular-text" value="<?php echo esc_attr( $edit_row ? $edit_row->phone : '' ); ?>"></td>
						</tr>
						<tr>
							<th scope="row"><label for="equipment-master-email"><?php esc_html_e( 'メールアドレス', 'equipment-management' ); ?></label></th>
							<td><input name="email" id="equipment-master-email" type="email" class="regular-text" value="<?php echo esc_attr( $edit_row ? $edit_row->email : '' ); ?>"></td>
						</tr>
						<tr>
							<th scope="row"><label for="equipment-master-address"><?php esc_html_e( '住所', 'equipment-management' ); ?></label></th>
							<td><textarea name="address" id="equipment-master-address" class="large-text" rows="4"><?php echo esc_textarea( $edit_row ? $edit_row->address : '' ); ?></textarea></td>
						</tr>
					<?php endif; ?>
					<?php if ( 'repair_statuses' === $current_type ) : ?>
						<tr>
							<th scope="row"><?php esc_html_e( '完了扱い', 'equipment-management' ); ?></th>
							<td>
								<label>
									<input name="is_closed" type="checkbox" value="1" <?php checked( $edit_row ? $edit_row->is_closed : 0, 1 ); ?>>
									<?php esc_html_e( '完了扱いにする', 'equipment-management' ); ?>
								</label>
							</td>
						</tr>
					<?php endif; ?>
					<tr>
						<th scope="row"><label for="equipment-master-display-order"><?php esc_html_e( '表示順', 'equipment-management' ); ?></label></th>
						<td><input name="display_order" id="equipment-master-display-order" type="number" class="small-text" value="<?php echo esc_attr( $edit_row ? $edit_row->display_order : 0 ); ?>"></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( '有効', 'equipment-management' ); ?></th>
						<td>
							<label>
								<input name="is_active" type="checkbox" value="1" <?php checked( $edit_row ? $edit_row->is_active : 1, 1 ); ?>>
								<?php esc_html_e( '有効にする', 'equipment-management' ); ?>
							</label>
						</td>
					</tr>
				</table>

				<?php submit_button( $edit_row ? __( '更新', 'equipment-management' ) : __( '追加', 'equipment-management' ) ); ?>
			</form>
		</section>
	</div>
</div>
