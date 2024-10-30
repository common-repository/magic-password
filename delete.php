<?php

use TwoFAS\MagicPassword\Core\Uninstaller;
use TwoFAS\MagicPassword\Exceptions\Handler\Error_Handler;

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

require_once 'vendor/autoload.php';
require_once 'constants.php';
require_once 'dependencies.php';

/** @var Error_Handler $error_handler */
$error_handler = $mpwd_container['error_handler'];

/** @var Uninstaller $uninstaller */
$uninstaller = $mpwd_container['uninstaller'];

$uninstaller->uninstall();
