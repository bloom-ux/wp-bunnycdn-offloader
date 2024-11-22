<?php

namespace Bloom\Bunny_CDN_Offloader;

class Plugin {


	/**
	 * @var Attachment_Upload_Task
	 */
	private $attachment_upload_task = null;

	/**
	 * Command line interface
	 *
	 * @var mixed
	 */
	private $cli;

	private static $instance = null;

	const UPLOADED_META_KEY = '_bloom_bunny_uploaded';

	private function __construct() {
		$this->attachment_upload_task = new Attachment_Upload_Task();
	}

	public static function get_instance() {
		if ( is_null( static::$instance ) ) {
			$classname = get_called_class();
			static::$instance = new $classname();
		}
		return static::$instance;
	}

	public function init() {
		$this->attachment_upload_task = new Attachment_Upload_Task();
		add_filter( 'wp_generate_attachment_metadata', array( $this, 'upload_attachment_files' ), 10, 2 );
		add_filter( 'wp_get_attachment_url', array( $this, 'filter_attachment_url' ), 10, 2 );
		add_action( 'add_attachment', array( $this, 'upload_attachment' ), 10, 1 );
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			$this->cli = new CLI();
			\WP_CLI::add_command( 'bloom-bunny upload', array( $this->cli, 'upload' ) );
		}
	}

	/**
	 * Uploads an attachment to the CDN.
	 *
	 * @param int $attachment_id Attachment ID.
	 * @return void|true
	 */
	public function upload_attachment( int $attachment_id ) {
		$attachment = get_post( $attachment_id );
		if ( ! $attachment ) {
			return;
		}
		$this->attachment_upload_task->data( array( 'attachment_id' => $attachment_id ) )->dispatch();
		return true;
	}

	/**
	 * Filters the attachment URL to point to the CDN.
	 *
	 * @param string $url Default attachment URL "https://mysite.com/wp-content...".
	 * @param int    $attachment_id Attachment ID.
	 * @return string Filtered URL if file was already uploaded ("https://mysite.b-cdn.net/wp-content...")
	 */
	public function filter_attachment_url( string $url, $attachment_id ): string {
		$in_cdn = (bool) get_post_meta( $attachment_id, static::UPLOADED_META_KEY, true );
		$cdn_url = str_replace( content_url(), untrailingslashit( getenv( 'BLOOM_BUNNY_PUBLIC_URL' ) ), $url );
		return $in_cdn ? $cdn_url : $url;
	}

	/**
	 * Uploads the attachment files to the CDN.
	 *
	 * Shouldn't really be a filter.
	 *
	 * @param array $metadata WordPress attachment metadata.
	 * @param int   $attachment_id WordPress attachment ID.
	 * @return array WordPress attachment metadata
	 */
	public function upload_attachment_files( array $metadata, int $attachment_id ): array {
		$this->attachment_upload_task->data( array( 'attachment_id' => $attachment_id ) )->dispatch();
		return $metadata;
	}
}
