<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

require_once 'Magic_Password_Requirements_Checker.php';

$requirements = new Magic_Password_Requirements_Checker();

if ( ! $requirements->check_php_version() ) {
	return;
}

require_once 'delete.php';
