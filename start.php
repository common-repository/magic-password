<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
require_once 'Magic_Password_Requirements_Checker.php';

$requirements = new Magic_Password_Requirements_Checker();

if ( $requirements->are_satisfied() ) {
	require_once 'vendor/autoload.php';
	require_once 'dependencies.php';

	$plugin = new TwoFAS\MagicPassword\Core\Plugin(
		$mpwd_container['response_factory'],
		$mpwd_container['request'],
		$mpwd_container['account_creator'],
		$mpwd_container['updater'],
		$mpwd_container['hook_handler'],
		$mpwd_container['status_notifier']
	);

	$plugin->start();
} else {
	$is_admin = current_user_can( 'manage_options' );

	if ( ! $is_admin ) {
		return;
	}

	foreach ( $requirements->get_not_satisfied() as $message ) {
		add_action( 'admin_notices', function () use ( $message ) {
			echo '<div class="notice notice-error error"><p>' . $message . '</p></div>';
		} );
	}
}
