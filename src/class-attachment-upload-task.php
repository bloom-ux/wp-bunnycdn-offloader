<?php

namespace Bloom\Bunny_CDN_Offloader;

use Bunny\Storage\Client;
use WP_Async_Request;


class Attachment_Upload_Task extends WP_Async_Request {

	protected $prefix = 'bloom_bunnycdn_offloader';

	protected $action = 'attachment_upload';

	protected function handle() {
		$attachment_id = filter_input( INPUT_POST, 'attachment_id', FILTER_SANITIZE_NUMBER_INT );
		$this->upload_attachment( $attachment_id );
	}

	public function upload_attachment( int $attachment_id ): bool {
		$upload_dir      = wp_upload_dir();
		$attachment_rel  = get_post_meta( $attachment_id, '_wp_attached_file', true );
		$full_path       = $upload_dir['basedir'] . '/' . $attachment_rel;
		$attachment_meta = get_post_meta( $attachment_id, '_wp_attachment_metadata', true );
		$this->upload_file( $full_path );
		if ( ! empty( $attachment_meta['sizes'] ) ) {
			foreach ( $attachment_meta['sizes'] as $size => $size_data ) {
				$size_path = str_replace( basename( $full_path ), $size_data['file'], $full_path );
				$this->upload_file( $size_path );
			}
		}
		update_post_meta( $attachment_id, Plugin::UPLOADED_META_KEY, 1 );
		return true;
	}

	private function upload_file( string $full_path ): bool {
		$content_dir     = WP_CONTENT_DIR;
		$prefix_dir      = wp_parse_url( getenv( 'BLOOM_BUNNY_PUBLIC_URL' ), PHP_URL_PATH );
		$remote_path     = ( $prefix_dir ? untrailingslashit( $prefix_dir ) : '' ) . str_replace( $content_dir, '', $full_path );
		$client = new Client(
			getenv( 'BLOOM_BUNNY_STORAGE_API_KEY' ),
			getenv( 'BLOOM_BUNNY_STORAGE_ZONE' ),
			getenv( 'BLOOM_BUNNY_STORAGE_REGION' )
		);
		$client->upload(
			$full_path,
			$remote_path
		);
		return true;
	}
}
