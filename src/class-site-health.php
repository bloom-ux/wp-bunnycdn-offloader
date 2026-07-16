<?php
/**
 * Site Health integration for BunnyCDN offloader
 *
 * @package Bloom_UX\Bunny_CDN_Offloader
 */

namespace Bloom_UX\Bunny_CDN_Offloader;

/**
 * Site Health diagnostic info
 */
class Site_Health {

	/**
	 * Initialize hooks
	 *
	 * @return void
	 */
	public function init() {
		add_filter( 'debug_information', array( $this, 'add_debug_info' ) );
		add_action( 'wp_ajax_bloom_bunnycdn_offloader_loopback_test', array( $this, 'handle_loopback_test' ) );
		add_action( 'wp_ajax_nopriv_bloom_bunnycdn_offloader_loopback_test', array( $this, 'handle_loopback_test' ) );
	}

	/**
	 * Handle loopback test AJAX request
	 *
	 * @return void
	 */
	public function handle_loopback_test() {
		wp_send_json_success( 'Loopback test OK' );
	}

	/**
	 * Add diagnostic info to Site Health debug tab
	 *
	 * @param array $info Existing debug info.
	 * @return array Modified debug info.
	 */
	public function add_debug_info( $info ) {
		$info['bloom-bunnycdn-offloader'] = array(
			'label'       => 'Bloom BunnyCDN Offloader',
			'description' => 'Diagnostic information for the BunnyCDN offloader.',
			'fields'      => array(
				'vendor_autoload' => array(
					'label' => 'Composer Dependencies',
					'value' => $this->check_vendor_autoload(),
				),
				'env_variables'   => array(
					'label' => 'Environment Variables',
					'value' => $this->check_env_variables(),
				),
				'loopback_test'   => array(
					'label' => 'Loopback Test',
					'value' => $this->check_loopback(),
				),
				'content_url'     => array(
					'label' => 'Content URL (content_url)',
					'value' => content_url(),
				),
				'site_url'        => array(
					'label' => 'Site URL (site_url)',
					'value' => site_url(),
				),
				'admin_ajax_url'  => array(
					'label' => 'Admin AJAX URL',
					'value' => admin_url( 'admin-ajax.php' ),
				),
			),
		);
		return $info;
	}

	/**
	 * Check if Composer vendor directory is available
	 *
	 * @return string Status message.
	 */
	private function check_vendor_autoload() {
		$autoload_path = dirname( __DIR__ ) . '/vendor/autoload.php';
		$has_local     = is_readable( $autoload_path );
		$has_classes   = class_exists( Plugin::class );

		if ( $has_classes ) {
			return 'OK - Classes available';
		}

		if ( $has_local && ! $has_classes ) {
			return 'ERROR - vendor/autoload.php exists but classes were not loaded correctly';
		}

		return 'ERROR - vendor/autoload.php not found. Run composer install.';
	}

	/**
	 * Check if required environment variables are set
	 *
	 * @return string Status message.
	 */
	private function check_env_variables() {
		$required = array(
			'BLOOM_BUNNY_STORAGE_API_KEY',
			'BLOOM_BUNNY_STORAGE_ZONE',
			'BLOOM_BUNNY_STORAGE_REGION',
			'BLOOM_BUNNY_PUBLIC_URL',
		);

		$missing = array();
		foreach ( $required as $var ) {
			if ( ! getenv( $var ) ) {
				$missing[] = $var;
			}
		}

		if ( empty( $missing ) ) {
			return 'OK - All environment variables configured';
		}

		return 'ERROR - Missing variables: ' . implode( ', ', $missing );
	}

	/**
	 * Test if loopback requests work correctly
	 *
	 * @return string Status message.
	 */
	private function check_loopback() {
		$ajax_url = admin_url( 'admin-ajax.php' );

		$response = wp_remote_post(
			$ajax_url,
			array(
				'body'      => array(
					'action' => 'bloom_bunnycdn_offloader_loopback_test',
				),
				'timeout'   => 5,
				'sslverify' => false,
			)
		);

		if ( is_wp_error( $response ) ) {
			return 'ERROR - ' . $response->get_error_message();
		}

		$code = wp_remote_retrieve_response_code( $response );
		if ( 200 === $code ) {
			return "OK - Loopback works (HTTP $code)";
		}

		return "ERROR - Unexpected HTTP response: $code";
	}
}
