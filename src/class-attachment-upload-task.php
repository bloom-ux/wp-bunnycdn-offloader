<?php
/**
 * Async task to upload attachment files to BunnyCDN
 *
 * @package Bloom_UX\Bunny_CDN_Offloader
 */

namespace Bloom_UX\Bunny_CDN_Offloader;

use WP_Async_Request;
use Bunny\Storage\Client;
use soulseekah\WP_Lock\WP_Lock;

/**
 * Async attachment upload
 *
 * @package Bloom_UX\Bunny_CDN_Offloader
 */
class Attachment_Upload_Task extends WP_Async_Request {

	/**
	 * Task prefix
	 *
	 * @var string
	 */
	protected $prefix = 'bloom_bunnycdn_offloader';

	/**
	 * Task action
	 *
	 * @var string
	 */
	protected $action = 'attachment_upload';

	/**
	 * BunnyCDN client
	 *
	 * @var Client
	 */
	private $client;

	/**
	 * Handle the async upload request
	 *
	 * @return void
	 */
	protected function handle() {
		$attachment_id = filter_input( INPUT_POST, 'attachment_id', FILTER_SANITIZE_NUMBER_INT );
		$updated_data  = wp_unslash( empty( $_POST['updated_data'] ) ? null : $_POST['updated_data'] ); //phpcs:ignore
		$this->client  = new Client(
			getenv( 'BLOOM_BUNNY_STORAGE_API_KEY' ),
			getenv( 'BLOOM_BUNNY_STORAGE_ZONE' ),
			getenv( 'BLOOM_BUNNY_STORAGE_REGION' )
		);
		if ( empty( $updated_data ) ) {
			$this->upload_attachment( $attachment_id );
		} else {
			$original_size = array(
				'file' => basename( $updated_data['file'] ),
			);
			$this->upload_attachment_size( $attachment_id, $original_size );
			foreach ( $updated_data['sizes'] as $updated_size ) {
				$this->upload_attachment_size( $attachment_id, $updated_size );
			}
		}
	}

	/**
	 * Upload an attachment and its sizes to BunnyCDN
	 *
	 * @param int $attachment_id The attachment ID.
	 * @return bool True if correctly uploaded, false otherwise.
	 */
	public function upload_attachment( int $attachment_id ): bool {
		global $wpdb;
		$upload_dir      = wp_upload_dir();
		$attachment_rel  = get_post_meta( $attachment_id, '_wp_attached_file', true );
		$full_path       = $upload_dir['basedir'] . '/' . $attachment_rel;
		$attachment_meta = $wpdb->get_var( $wpdb->prepare( "SELECT meta_value FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = %s", $attachment_id, '_wp_attachment_metadata' ) );
		$attachment_meta = maybe_unserialize( $attachment_meta );
		$this->upload_file( $full_path );
		if ( ! empty( $attachment_meta['sizes'] ) ) {
			foreach ( $attachment_meta['sizes'] as $size => $size_data ) {
				$this->upload_attachment_size( $attachment_id, $size_data );
			}
		}
		update_post_meta( $attachment_id, Plugin::UPLOADED_META_KEY, 1 );
		return true;
	}

	/**
	 * Upload a singular size from an attachment
	 *
	 * @param int   $attachment_id The attachment ID.
	 * @param array $size Size data (file, width, height, mime-type, filesize).
	 * @return void
	 */
	private function upload_attachment_size( int $attachment_id, array $size ) {
		$upload_dir     = wp_upload_dir();
		$attachment_rel = get_post_meta( $attachment_id, '_wp_attached_file', true );
		$full_path      = $upload_dir['basedir'] . '/' . $attachment_rel;
		$size_path      = str_replace( basename( $full_path ), $size['file'], $full_path );
		$size_name      = $size['file'];
		$upload_lock    = new WP_Lock( "$attachment_id:$size_name:upload" );
		$upload_lock->acquire( WP_Lock::WRITE );
		$uploaded_key = Plugin::UPLOADED_META_KEY . md5( "_{$size['file']}" );
		$was_uploaded = (bool) get_post_meta( $attachment_id, $uploaded_key, true );
		if ( $was_uploaded ) {
			$upload_lock->release();
			return;
		}
		$this->upload_file( $size_path );
		update_post_meta( $attachment_id, $uploaded_key, $size['file'] );
		$upload_lock->release();
	}

	/**
	 * Upload the given absolute path to BunnyCDN
	 *
	 * @param string $full_path The full path to the file on disk.
	 * @return bool True if correctly uploaded.
	 */
	private function upload_file( string $full_path ): bool {
		$content_dir = WP_CONTENT_DIR;
		$prefix_dir  = wp_parse_url( getenv( 'BLOOM_BUNNY_PUBLIC_URL' ), PHP_URL_PATH );
		$remote_path = ( $prefix_dir ? untrailingslashit( $prefix_dir ) : '' ) . str_replace( $content_dir, '', $full_path );
		$this->client->upload( $full_path, $remote_path );
		return true;
	}
}
