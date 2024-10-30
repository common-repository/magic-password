<?php

use TwoFAS\Core\Factories\Middleware_Factory;
use TwoFAS\MagicPassword\Http\Cookie;
use TwoFAS\MagicPassword\Http\Login_Cookie;
use TwoFAS\MagicPassword\Http\Request;
use TwoFAS\Core\Http\Route;
use TwoFAS\MagicPassword\Http\Session\Session;
use TwoFAS\MagicPassword\Http\Middleware\Check_User_Is_Logged_In;
use TwoFAS\MagicPassword\Http\Middleware\Check_User_Is_Admin;
use TwoFAS\MagicPassword\Http\Middleware\Check_Nonce;
use TwoFAS\MagicPassword\Http\Middleware\Check_Account;
use TwoFAS\MagicPassword\Http\Middleware\Check_Passwordless_Login_Is_Configured;
use TwoFAS\MagicPassword\Http\Middleware\Check_Passwordless_Role;
use TwoFAS\MagicPassword\Http\Middleware\Show_Review_Notice;
use TwoFAS\MagicPassword\Http\Middleware\Check_If_Plugin_Is_Enabled;
use TwoFAS\Core\Http\Middleware\Middleware_Bag;
use TwoFAS\MagicPassword\Http\Controllers\Settings_Controller;
use TwoFAS\MagicPassword\Http\Controllers\Configuration_Controller;
use TwoFAS\MagicPassword\Http\Controllers\Channel_Authentication_Controller;
use TwoFAS\MagicPassword\Http\Controllers\Deactivation_Controller;
use TwoFAS\Core\Factories\Response_Factory;

/**
 * --------------------------------------------------------------------------------------------------------------------
 * Http
 * --------------------------------------------------------------------------------------------------------------------
 */
$mpwd_container['cookie'] = function ( $c ) use ( $mpwd_cookie ) {
	return new Cookie( $mpwd_cookie );
};

$mpwd_container['login_cookie'] = function ( $c ) {
	return new Login_Cookie( $c['cookie'] );
};

$mpwd_container['session'] = function ( $c ) {
	return new Session( $c['session_storage'] );
};

$mpwd_container['request'] = function ( $c ) use ( $mpwd_get, $mpwd_post, $mpwd_server ) {
	return new Request(
		$mpwd_get,
		$mpwd_post,
		$mpwd_server,
		$c['cookie'],
		$c['session']
	);
};

$mpwd_container['route'] = function ( $c ) use ( $mpwd_routes ) {
	return new Route(
		$c['request'],
		$mpwd_routes
	);
};

$mpwd_container['response_factory'] = function ( $c ) {
	return new Response_Factory(
		$c['route'],
		$c['controller_factory'],
		$c['middleware_factory'],
		$c['request'],
		$c['error_handler']
	);
};

$mpwd_container['middleware_factory'] = function ( $c ) {
	return new Middleware_Factory( $c['middleware_bag'] );
};

/**
 * --------------------------------------------------------------------------------------------------------------------
 * Controllers
 * --------------------------------------------------------------------------------------------------------------------
 */
$mpwd_container['channel_authentication_controller'] = function ( $c ) {
	return new Channel_Authentication_Controller( $c['api_wrapper'], $c['account_storage'] );
};

$mpwd_container['configuration_controller'] = function ( $c ) {
	return new Configuration_Controller(
		$c['flash'],
		$c['api_wrapper'],
		$c['account_storage'],
		$c['user_storage'],
		$c['user_configuration'],
		$c['pair_service'],
		$c['pusher_session_service'],
		$c['login_cookie']
	);
};

$mpwd_container['settings_controller'] = function ( $c ) {
	return new Settings_Controller( $c['account_storage'] );
};

$mpwd_container['deactivation_controller'] = function ( $c ) {
	return new Deactivation_Controller( $c['curl_client'] );
};

/**
 * --------------------------------------------------------------------------------------------------------------------
 * Middleware
 * --------------------------------------------------------------------------------------------------------------------
 */
$mpwd_container['check_user_is_logged_in'] = function ( $c ) {
	return new Check_User_Is_Logged_In( $c['request'] );
};

$mpwd_container['check_user_is_admin'] = function ( $c ) {
	return new Check_User_Is_Admin( $c['request'], $c['user_storage'] );
};

$mpwd_container['check_nonce'] = function ( $c ) {
	return new Check_Nonce( $c['request'], $c['flash'] );
};

$mpwd_container['check_account'] = function ( $c ) {
	return new Check_Account( $c['request'], $c['api_wrapper'], $c['flash'], $c['url'] );
};

$mpwd_container['check_passwordless_login_is_configured'] = function ( $c ) {
	return new Check_Passwordless_Login_Is_Configured(
		$c['request'],
		$c['flash'],
		$c['user_configuration']
	);
};

$mpwd_container['check_passwordless_role'] = function ( $c ) {
	return new Check_Passwordless_Role( $c['user_configuration'], $c['flash'] );
};

$mpwd_container['show_review_notice'] = function ( $c ) {
	return new Show_Review_Notice( $c['review_notice_action'], $c['account_storage'], $c['user_storage'] );
};

$mpwd_container['check_if_plugin_is_enabled'] = function ( $c ) {
	return new Check_If_Plugin_Is_Enabled( $c['request'], $c['account_storage'] );
};

$mpwd_container['middleware_bag'] = function ( $c ) {
	return new Middleware_Bag();
};

$mpwd_container->extend( 'middleware_bag', function ( Middleware_Bag $middleware_bag, $c ) {
	$middleware_bag->add_middleware( 'auth', $c['check_user_is_logged_in'] );
	$middleware_bag->add_middleware( 'admin', $c['check_user_is_admin'] );
	$middleware_bag->add_middleware( 'nonce', $c['check_nonce'] );
	$middleware_bag->add_middleware( 'account', $c['check_account'] );
	$middleware_bag->add_middleware( 'configured', $c['check_passwordless_login_is_configured'] );
	$middleware_bag->add_middleware( 'obligatoriness', $c['check_passwordless_role'] );
	$middleware_bag->add_middleware( 'review_notice', $c['show_review_notice'] );
	$middleware_bag->add_middleware( 'enabled', $c['check_if_plugin_is_enabled'] );

	return $middleware_bag;
} );
