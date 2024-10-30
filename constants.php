<?php

$main_plugin_file_path = __DIR__ . '/magic-password.php';
$plugin_path           = plugin_dir_path( $main_plugin_file_path );
$plugin_url            = plugin_dir_url( $main_plugin_file_path );
$plugin_basename       = plugin_basename( $main_plugin_file_path );
$assets_url            = $plugin_url . 'assets/';
$templates_path        = $plugin_path . 'templates/';

define( 'MPWD_PLUGIN_PATH',     $plugin_path     );
define( 'MPWD_PLUGIN_URL',      $plugin_url      );
define( 'MPWD_PLUGIN_BASENAME', $plugin_basename );
define( 'MPWD_ASSETS_URL',      $assets_url      );
define( 'MPWD_TEMPLATES_PATH',  $templates_path  );
define( 'MPWD_PLUGIN_VERSION',  '2.0.0'          );
define( 'MPWD_DEPRECATE_PHP_OLDER_THAN', '5.4' );
