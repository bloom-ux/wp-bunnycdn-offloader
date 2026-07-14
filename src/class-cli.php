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
	 * [<attachment-id>...]
	 * : One or more attachment IDs to upload.
	 *
	 * [--all]
	 * : Upload all attachments.
	 *
	 * ## EXAMPLES
	 *
	 *     # Upload specific attachments
	 *     $ wp bloom-bunny upload 123 456
	 *     Processing attachment ID: 123...
	 *     Attachment ID: 123 finished.
	 *     Processing attachment ID: 456...
	 *     Attachment ID: 456 finished.
	 *
	 *     # Upload all attachments with progress bar
	 *     $ wp bloom-bunny upload --all
	 *     Uploading all attachments: 100% [======================] 0:05 / 0:05
	 *     Success: Uploaded 150 attachments.
	 *
	 * @param array $args       Command arguments.
	 * @param array $assoc_args Command flags.
	 * @return void
	 */
	public function upload( $args, $assoc_args ) {
		$upload_task = new Attachment_Upload_Task();

		if ( ! empty( $assoc_args['all'] ) ) {
			$attachment_ids = get_posts(
				array(
					'post_type'      => 'attachment',
					'posts_per_page' => -1,
					'post_status'    => 'inherit',
					'fields'         => 'ids',
				)
			);

			if ( empty( $attachment_ids ) ) {
				WP_CLI::warning( 'No attachments found.' );
				return;
			}

			$progress = \WP_CLI\Utils\make_progress_bar( 'Uploading all attachments', count( $attachment_ids ) );

			foreach ( $attachment_ids as $attachment_id ) {
				$upload_task->upload_attachment( $attachment_id );
				$progress->tick();
			}

			$progress->finish();
			WP_CLI::success( sprintf( 'Uploaded %d attachments.', count( $attachment_ids ) ) );
		} else {
			if ( empty( $args ) ) {
				WP_CLI::error( 'Please provide attachment IDs or use --all flag.' );
			}

			foreach ( $args as $attachment_id ) {
				WP_CLI::line( sprintf( 'Processing attachment ID: %d...', $attachment_id ) );

				$upload_task->upload_attachment( $attachment_id );

				WP_CLI::line( sprintf( 'Attachment ID: %d finished.', $attachment_id ) );
			}
		}
	}
}
