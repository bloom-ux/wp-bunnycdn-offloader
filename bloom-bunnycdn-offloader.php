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

$offloader_plugin = Plugin::get_instance();
$offloader_plugin->init();
