<?php
/**
 * Command line interface for BunnyCDN offloader
 *
 * @package Bloom_UX\Bunny_CDN_Offloader
 */

namespace Bloom_UX\Bunny_CDN_Offloader;

use WP_CLI;

/**
 * Command line interface
 */
class CLI {

	/**
	 * Uploads one or more attachments to BunnyCDN.
	 *
	 * <attachment-id>...
	 * : One or more attachment IDs to upload.
	 *
	 * ## EXAMPLES
	 *
	 *     wp bunnycdn upload 123 456
	 *
	 * @param array $args Command arguments.
	 * @return void
	 */
	public function upload( $args ) {
		$upload_task = new Attachment_Upload_Task();

		foreach ( $args as $attachment_id ) {
			WP_CLI::line( sprintf( 'Processing attachment ID: %d...', $attachment_id ) );

			$upload_task->upload_attachment( $attachment_id );

			WP_CLI::line( sprintf( 'Attachment ID: %d finished.', $attachment_id ) );
		}
	}
}
