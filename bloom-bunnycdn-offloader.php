<?php
/**
 * Plugin Name: Bloom BunnyCDN Offloader
 * Description: Offload media files to BunnyCDN
 * Version: 0.1.0
 * Author: bloom.lat
 * Author URI: https://bloom.lat
 *
 * @package Bloom_UX\Bunny_CDN_Offloader
 */

namespace Bloom_UX\Bunny_CDN_Offloader;

if ( is_readable( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

if ( ! class_exists( Plugin::class ) ) {
	add_action(
		'admin_notices',
		function () {
			echo '<div class="notice notice-error"><p>';
			echo '<strong>Bloom BunnyCDN Offloader:</strong> ';
			echo 'Clases necesarias no encontradas. Ejecuta <code>composer install</code> en el directorio del plugin o asegúrate de que las dependencias estén instaladas en el proyecto principal.';
			echo '</p></div>';
		}
	);
	return;
}

$offloader_plugin = Plugin::get_instance();
$offloader_plugin->init();
