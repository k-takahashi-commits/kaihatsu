<?php
/**
 * Attachment list partial.
 *
 * @package EquipmentManagement
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$attachment_redirect = isset( $attachment_redirect ) ? $attachment_redirect : admin_url( 'admin.php' );
$can_delete_attachment = ! empty( $can_delete_attachment );
?>

<?php if ( empty( $attachments ) ) : ?>
	<p class="description"><?php esc_html_e( '添付ファイルはありません。', 'equipment-management' ); ?></p>
<?php else : ?>
	<ul class="equipment-management-attachments">
		<?php foreach ( $attachments as $attachment ) : ?>
			<?php
			$file_url    = wp_get_attachment_url( $attachment->attachment_id );
			$delete_url  = wp_nonce_url(
				add_query_arg(
					array(
						'action'            => 'equipment_management_delete_attachment',
						'attachment_row_id' => $attachment->id,
						'redirect'          => $attachment_redirect,
					),
					admin_url( 'admin-post.php' )
				),
				'equipment_management_delete_attachment_' . $attachment->id
			);
			?>
			<li>
				<?php if ( $file_url ) : ?>
					<a href="<?php echo esc_url( $file_url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $attachment->file_name ); ?></a>
				<?php else : ?>
					<?php echo esc_html( $attachment->file_name ); ?>
				<?php endif; ?>
				<span class="description"><?php echo esc_html( size_format( (int) $attachment->file_size ) ); ?></span>
				<?php if ( $can_delete_attachment ) : ?>
					<a class="submitdelete" href="<?php echo esc_url( $delete_url ); ?>" onclick="return window.confirm('<?php echo esc_js( __( 'この添付ファイルを削除しますか？', 'equipment-management' ) ); ?>');"><?php esc_html_e( '削除', 'equipment-management' ); ?></a>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ul>
<?php endif; ?>
