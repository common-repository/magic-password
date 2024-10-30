<?php

use TwoFAS\MagicPassword\Http\Action_Index;

$mpwd_routes = array(
	Action_Index::PAGE_CONFIGURATION => array(
		Action_Index::ACTION_DEFAULT                    => array(
			'controller' => 'Configuration_Controller',
			'action'     => 'show_configuration_page',
			'method'     => array( 'GET' ),
			'middleware' => array( 'auth', 'account', 'obligatoriness', 'review_notice' ),
		),
		Action_Index::ACTION_PAIR                       => array(
			'controller' => 'Configuration_Controller',
			'action'     => 'pair',
			'method'     => array( 'POST' ),
			'middleware' => array( 'auth', 'nonce', 'account', 'enabled' ),
		),
		Action_Index::ACTION_UNPAIR                     => array(
			'controller' => 'Configuration_Controller',
			'action'     => 'unpair',
			'method'     => array( 'POST' ),
			'middleware' => array( 'auth', 'nonce', 'account', 'enabled' ),
		),
		Action_Index::ACTION_AUTHENTICATE_CHANNEL       => array(
			'controller' => 'Channel_Authentication_Controller',
			'action'     => 'authenticate_channel',
			'method'     => array( 'POST' ),
			'middleware' => array( 'account', 'enabled' ),
		),
		Action_Index::ACTION_ENABLE_PASSWORDLESS_LOGIN  => array(
			'controller' => 'Configuration_Controller',
			'action'     => 'enable_passwordless_login',
			'method'     => array( 'POST' ),
			'middleware' => array( 'auth', 'nonce', 'account', 'enabled', 'configured' ),
		),
		Action_Index::ACTION_DISABLE_PASSWORDLESS_LOGIN => array(
			'controller' => 'Configuration_Controller',
			'action'     => 'disable_passwordless_login',
			'method'     => array( 'POST' ),
			'middleware' => array( 'auth', 'nonce', 'account', 'enabled', 'configured' ),
		),
	),
	Action_Index::PAGE_SETTINGS      => array(
		Action_Index::ACTION_DEFAULT             => array(
			'controller' => 'Settings_Controller',
			'action'     => 'show_settings_page',
			'method'     => array( 'GET' ),
			'middleware' => array( 'auth', 'admin', 'account', 'review_notice' ),
		),
		Action_Index::ACTION_SAVE_LOGGING        => array(
			'controller' => 'Settings_Controller',
			'action'     => 'save_logging',
			'method'     => array( 'POST' ),
			'middleware' => array( 'auth', 'admin', 'nonce', 'account', 'enabled' ),
		),
		Action_Index::ACTION_CLOSE_REVIEW_NOTICE => array(
			'controller' => 'Settings_Controller',
			'action'     => 'close_review_notice',
			'method'     => array( 'POST' ),
			'middleware' => array( 'auth', 'admin', 'nonce', 'account', 'enabled' ),
		),
		Action_Index::ACTION_ENABLE_PLUGIN       => array(
			'controller' => 'Settings_Controller',
			'action'     => 'enable_plugin',
			'method'     => array( 'POST' ),
			'middleware' => array( 'auth', 'admin', 'nonce', 'account' ),
		),
		Action_Index::ACTION_DISABLE_PLUGIN      => array(
			'controller' => 'Settings_Controller',
			'action'     => 'disable_plugin',
			'method'     => array( 'POST' ),
			'middleware' => array( 'auth', 'admin', 'nonce', 'account' ),
		),
	),
	Action_Index::PAGE_DEACTIVATION  => array(
		Action_Index::ACTION_SEND_DEACTIVATION_REASON => array(
			'controller' => 'Deactivation_Controller',
			'action'     => 'send_deactivation_reason',
			'method'     => array( 'POST' ),
			'middleware' => array( 'auth', 'admin', 'nonce' ),
		),
	),
);
