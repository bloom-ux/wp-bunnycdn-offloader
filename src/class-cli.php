<?php
/**
 * Command line interface for BunnyCDN offloader
 *
 * @package Bloom_UX\Bunny_CDN_Offloader
 */

namespace Bloom_UX\Bunny_CDN_Offloader;

/**
 * Command line interface
 */
class CLI {

	/**
	 * Upload an attachment to BunnyCDN
	 *
	 * @param array $args {
	 *     Command arguments.
	 *     @type int $attachment_id The attachment ID.
	 * }
	 * @return void
	 */
	public function upload( $args ) {
		list( $attachment_id ) = $args;
		$upload_task = new Attachment_Upload_Task();
		$upload_task->upload_attachment( $attachment_id );
	}
}
