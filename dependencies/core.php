<?php

use TwoFAS\Api\QrCode\QrClientFactory;
use TwoFAS\Api\QrCodeGenerator;
use TwoFAS\Core\Readme\Container;
use TwoFAS\Core\Readme\Upgrade_Notice;
use TwoFAS\MagicPassword\Codes\QR_Code_Generator;
use TwoFAS\MagicPassword\Core\Uninstaller;
use TwoFAS\MagicPassword\Exceptions\Handler\Error_Handler;
use TwoFAS\MagicPassword\Exceptions\Handler\Sentry_Logger;
use TwoFAS\MagicPassword\Factories\Account_Factory;
use TwoFAS\MagicPassword\Factories\API_Factory;
use TwoFAS\Core\Factories\Controller_Factory;
use TwoFAS\MagicPassword\Factories\Session_Storage_Factory;
use TwoFAS\MagicPassword\Helpers\Config;
use TwoFAS\MagicPassword\Helpers\Flash;
use TwoFAS\MagicPassword\Helpers\Twig;
use TwoFAS\MagicPassword\Helpers\URL;
use TwoFAS\MagicPassword\Integration\API_Wrapper;
use TwoFAS\MagicPassword\Integration\Blog_Name_Decoder;
use TwoFAS\MagicPassword\Integration\Integration_Name;
use TwoFAS\MagicPassword\Integration\User_Configuration;
use TwoFAS\Core\Readme\Downloader;
use TwoFAS\Core\Readme\Parser;
use TwoFAS\MagicPassword\Services\Account_Creator;
use TwoFAS\MagicPassword\Services\Lockout_Service;
use TwoFAS\MagicPassword\Services\Pair_Service;
use TwoFAS\MagicPassword\Services\Pusher_Session_Service;
use TwoFAS\MagicPassword\Storage\Account_Storage;
use TwoFAS\MagicPassword\Storage\DB_Wrapper;
use TwoFAS\MagicPassword\Storage\User_Storage;
use TwoFAS\MagicPassword\Storage\Storage;
use TwoFAS\MagicPassword\Update\Migrator;
use TwoFAS\MagicPassword\Update\Updater;
use TwoFAS\Account\HttpClient\CurlClient;
use TwoFAS\MagicPassword\Notifications\Plugin_Notifier;
use TwoFAS\MagicPassword\Helpers\Environment;
use TwoFAS\Core\Update\Deprecation;
use TwoFAS\Core\Update\Update_Lock;
use TwoFAS\Core\Readme\Header;

/**
 * --------------------------------------------------------------------------------------------------------------------
 * Core
 * --------------------------------------------------------------------------------------------------------------------
 */
$mpwd_container['db'] = function ( $c ) use ( $wpdb ) {
	return new DB_Wrapper( $wpdb );
};

$mpwd_container['flash'] = function ( $c ) {
	return new Flash( $c['cookie'] );
};

$mpwd_container['url'] = function ( $c ) {
	return new URL();
};

$mpwd_container['twig'] = function ( $c ) {
	return new Twig(
		$c['flash'],
		$c['url'],
		$c['error_handler']
	);
};

$mpwd_container['config'] = function ( $c ) {
	return new Config();
};

$mpwd_container['error_handler'] = function ( $c ) {
	/** @var Account_Storage $account_storage */
	$account_storage = $c['account_storage'];
	$sentry_logger   = new Sentry_Logger(
		$c['config'],
		$c['user_configuration'],
		$account_storage
	);

	return new Error_Handler(
		$sentry_logger,
		$account_storage->is_logging_allowed()
	);
};

$mpwd_container['account_creator'] = function ( $c ) {
	return new Account_Creator(
		$c['request'],
		$c['account_storage'],
		$c['api_wrapper']
	);
};

$mpwd_container['migrator'] = function ( $c ) {
	return new Migrator(
		$c['db'],
		$c['api_wrapper'],
		$c['storage']
	);
};

$mpwd_container['updater'] = function ( $c ) {
	return new Updater(
		$c['request'],
		$c['migrator'],
		$c['account_storage']
	);
};

$mpwd_container['uninstaller'] = function ( $c ) {
	return new Uninstaller( $c['migrator'], $c['api_wrapper'], $c['error_handler'] );
};

$mpwd_container['blog_name_decoder'] = function ( $c ) {
	return new Blog_Name_Decoder();
};

$mpwd_container['qr_code_generator'] = function ( $c ) {
	return new QR_Code_Generator(
		new QrCodeGenerator( QrClientFactory::getInstance() )
	);
};

$mpwd_container['integration_name'] = function ( $c ) {
	return new Integration_Name( $c['blog_name_decoder'] );
};

$mpwd_container['user_configuration'] = function ( $c ) {
	return new User_Configuration( $c['user_storage'], $c['account_storage'], $c['api_wrapper'] );
};

$mpwd_container['pusher_session_service'] = function ( $c ) {
	return new Pusher_Session_Service( $c['session'] );
};

$mpwd_container['pair_service'] = function ( $c ) {
	return new Pair_Service( $c['api_wrapper'], $c['user_configuration'], $c['flash'] );
};

$mpwd_container['lockout_service'] = function ( $c ) {
	return new Lockout_Service( $c['user_storage'] );
};

$mpwd_container['parser'] = function ( $c ) {
	return new Parser();
};

$mpwd_container['downloader'] = function ( $c ) {
	return new Downloader( $c['parser'] );
};

$mpwd_container['readme_container'] = function ( $c ) {
	/** @var Config $config */
	$config = $c['config'];

	return new Container( $c['request'], $c['downloader'], $config->get_readme_url() );
};

$mpwd_container['curl_client'] = function ( $c ) {
	return new CurlClient();
};

$mpwd_container['environment'] = function ( $c ) {
	return new Environment();
};

$mpwd_container['upgrade_notice'] = function ( $c ) {
	return new Upgrade_Notice( $c['readme_container'] );
};

$mpwd_container['status_notifier'] = function ( $c ) {
	return new Plugin_Notifier( $c['flash'], $c['deprecation'] );
};

$mpwd_container['deprecation'] = function ( $c ) {
	$deprecation = new Deprecation( $c['environment'] );

	return $deprecation;
};

$mpwd_container->extend('deprecation', function(Deprecation $deprecation, $c) {
	$deprecation->deprecate_php_older_than( MPWD_DEPRECATE_PHP_OLDER_THAN );

	return $deprecation;
});

$mpwd_container['readme_container'] = function ( $c ) {
	/** @var Config $config */
	$config = $c['config'];
	return new Container( $c['request'], $c['downloader'], $config->get_readme_url() );
};
$mpwd_container['readme_header'] = function ( $c ) {
	return new Header( $c['readme_container'] );
};
$mpwd_container['update_lock'] = function ( $c ) {
	return new Update_Lock( $c['environment'], $c['readme_header'] );
};

/**
 * --------------------------------------------------------------------------------------------------------------------
 * Storages
 * --------------------------------------------------------------------------------------------------------------------
 */

$mpwd_container['user_storage'] = function ( $c ) {
	$storage = new User_Storage();

	if ( function_exists( 'wp_get_current_user' ) ) {
		$wp_user = wp_get_current_user();

		if ( $wp_user->exists() ) {
			$storage->set_wp_user( $wp_user );
		}
	}

	return $storage;
};

$mpwd_container['account_storage'] = function ( $c ) {
	return new Account_Storage( $c['blog_name_decoder'] );
};

$mpwd_container['session_storage'] = function ( $c ) use ( $mpwd_get ) {
	$session_storage_factory = new Session_Storage_Factory(
		$c['db'],
		$c['cookie'],
		$mpwd_get
	);

	return $session_storage_factory->create();
};

$mpwd_container['storage'] = function ( $c ) {
	return new Storage( $c['account_storage'], $c['user_storage'], $c['session_storage'] );
};

/**
 * --------------------------------------------------------------------------------------------------------------------
 * SDK
 * --------------------------------------------------------------------------------------------------------------------
 */

$mpwd_container['api_wrapper'] = function ( $c ) {
	return new API_Wrapper(
		$c['account_storage'],
		$c['api_factory'],
		$c['account_factory'],
		$c['integration_name']
	);
};

/**
 * --------------------------------------------------------------------------------------------------------------------
 * Factories
 * --------------------------------------------------------------------------------------------------------------------
 */
$mpwd_container['api_factory'] = function ( $c ) {
	return new API_Factory(
		$c['account_storage'],
		$c['config']
	);
};

$mpwd_container['account_factory'] = function ( $c ) {
	return new Account_Factory(
		$c['account_storage'],
		$c['config']
	);
};

$mpwd_container['controller_factory'] = function ( $c ) {
	return new Controller_Factory( $c );
};
