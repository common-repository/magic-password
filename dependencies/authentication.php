<?php

use TwoFAS\MagicPassword\Authentication\Login_Process;
use TwoFAS\MagicPassword\Authentication\Handler\Passwordless_Login;
use TwoFAS\MagicPassword\Authentication\Handler\Configuration_Reset;
use TwoFAS\MagicPassword\Authentication\Handler\Login_Configuration;
use TwoFAS\MagicPassword\Authentication\Handler\Handler_Builder;
use TwoFAS\MagicPassword\Authentication\Middleware\Middleware_Builder;
use TwoFAS\MagicPassword\Authentication\Middleware\Login_Availability_Check;
use TwoFAS\MagicPassword\Authentication\Middleware\Confirmation_View;
use TwoFAS\MagicPassword\Authentication\Middleware\Login_Cookie_Deletion;

/**
 * --------------------------------------------------------------------------------------------------------------------
 * Login Chain
 * --------------------------------------------------------------------------------------------------------------------
 */
$mpwd_container['login_availability_check'] = function ( $c ) {
	return new Login_Availability_Check( $c['request'], $c['user_configuration'] );
};

$mpwd_container['confirmation_view'] = function ( $c ) {
	return new Confirmation_View(
		$c['request'],
		$c['user_configuration'],
		$c['account_storage'],
		$c['qr_code_generator'],
		$c['pusher_session_service']
	);
};

$mpwd_container['passwordless_login'] = function ( $c ) {
	return new Passwordless_Login(
		$c['request'],
		$c['user_storage'],
		$c['user_configuration'],
		$c['lockout_service'],
		$c['api_wrapper'],
		$c['flash'],
		$c['login_cookie']
	);
};

$mpwd_container['configuration_reset'] = function ( $c ) {
	return new Configuration_Reset(
		$c['request'],
		$c['user_storage'],
		$c['user_configuration'],
		$c['account_storage'],
		$c['api_wrapper'],
		$c['qr_code_generator'],
		$c['pusher_session_service']
	);
};

$mpwd_container['login_configuration'] = function ( $c ) {
	return new Login_Configuration(
		$c['request'],
		$c['user_storage'],
		$c['pair_service'],
		$c['flash'],
		$c['login_cookie']
	);
};

$mpwd_container['login_cookie_deletion'] = function ( $c ) {
	return new Login_Cookie_Deletion( $c['login_cookie'] );
};

$mpwd_container['login_handler'] = function ( $c ) {
	$builder = new Handler_Builder();

	$builder
		->add_handler( $c['passwordless_login'] )
		->add_handler( $c['configuration_reset'] )
		->add_handler( $c['login_configuration'] );

	return $builder->build();
};

$mpwd_container['before_middleware'] = function ( $c ) {
	$builder = new Middleware_Builder();

	$builder
		->add_middleware( $c['login_availability_check'] )
		->add_middleware( $c['confirmation_view'] );

	return $builder->build();
};

$mpwd_container['after_middleware'] = function ( $c ) {
	$builder = new Middleware_Builder();

	$builder
		->add_middleware( $c['login_cookie_deletion'] );

	return $builder->build();
};

$mpwd_container['login_process'] = function ( $c ) {
	return new Login_Process( $c['before_middleware'], $c['after_middleware'], $c['login_handler'], $c['error_handler'] );
};
