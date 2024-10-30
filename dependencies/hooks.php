<?php

use TwoFAS\MagicPassword\Hooks\Admin_Notices_Action;
use TwoFAS\MagicPassword\Hooks\Enqueue_Login_Scripts_Action;
use TwoFAS\MagicPassword\Hooks\Enqueue_Dashboard_Scripts_Action;
use TwoFAS\MagicPassword\Hooks\Script_Attribute_Filter;
use TwoFAS\MagicPassword\Hooks\Action_Links_Filter;
use TwoFAS\MagicPassword\Hooks\Delete_Expired_Sessions_Action;
use TwoFAS\MagicPassword\Hooks\Login_Form_Action;
use TwoFAS\MagicPassword\Hooks\Update_Option_Blog_Name_Action;
use TwoFAS\MagicPassword\Hooks\Update_Option_User_Roles_Action;
use TwoFAS\MagicPassword\Hooks\In_Plugin_Update_Message_Action;
use TwoFAS\MagicPassword\Hooks\Admin_Menu_Action;
use TwoFAS\MagicPassword\Hooks\Destroy_Session_Action;
use TwoFAS\MagicPassword\Hooks\Regenerate_Session_Action;
use TwoFAS\Core\Hooks\Hook_Handler;
use TwoFAS\MagicPassword\Hooks\Review_Notice_Action;
use TwoFAS\MagicPassword\Hooks\Authenticate_Filter;

/**
 * --------------------------------------------------------------------------------------------------------------------
 * Hooks
 * --------------------------------------------------------------------------------------------------------------------
 */
$mpwd_container['hook_handler'] = function ( $c ) {
	return new Hook_Handler();
};

$mpwd_container->extend( 'hook_handler', function ( Hook_Handler $handler, $c ) {
	$handler->add_hook( $c['admin_menu_action'] );
	$handler->add_hook( $c['authenticate_filter'] );
	$handler->add_hook( $c['admin_notices_action'] );
	$handler->add_hook( $c['enqueue_login_scripts_action'] );
	$handler->add_hook( $c['enqueue_dashboard_scripts_action'] );
	$handler->add_hook( $c['script_attribute_filter'] );
	$handler->add_hook( $c['action_links_filter'] );
	$handler->add_hook( $c['delete_expired_sessions_action'] );
	$handler->add_hook( $c['destroy_session_action'] );
	$handler->add_hook( $c['regenerate_session_action'] );
	$handler->add_hook( $c['login_form_action'] );
	$handler->add_hook( $c['update_option_blog_name_action'] );
	$handler->add_hook( $c['update_option_user_roles_action'] );
	$handler->add_hook( $c['in_plugin_update_message_action'] );

	return $handler;
} );

$mpwd_container['review_notice_action'] = function ( $c ) {
	return new Review_Notice_Action( $c['twig'] );
};

$mpwd_container['admin_notices_action'] = function ( $c ) {
	return new Admin_Notices_Action( $c['twig'] );
};

$mpwd_container['enqueue_login_scripts_action'] = function ( $c ) {
	return new Enqueue_Login_Scripts_Action( $c['config'], $c['account_storage'], $c['twig'] );
};

$mpwd_container['enqueue_dashboard_scripts_action'] = function ( $c ) {
	return new Enqueue_Dashboard_Scripts_Action(
		$c['config'],
		$c['account_storage'],
		$c['twig'],
		$c['request'],
		$c['environment'],
		$c['update_lock']
	);
};

$mpwd_container['script_attribute_filter'] = function ( $c ) {
	return new Script_Attribute_Filter();
};

$mpwd_container['action_links_filter'] = function ( $c ) {
	return new Action_Links_Filter( $c['url'] );
};

$mpwd_container['delete_expired_sessions_action'] = function ( $c ) {
	return new Delete_Expired_Sessions_Action( $c['error_handler'], $c['db'] );
};

$mpwd_container['login_form_action'] = function ( $c ) {
	return new Login_Form_Action(
		$c['error_handler'],
		$c['account_storage'],
		$c['twig'],
		$c['qr_code_generator'],
		$c['pusher_session_service']
	);
};

$mpwd_container['update_option_blog_name_action'] = function ( $c ) {
	return new Update_Option_Blog_Name_Action(
		$c['error_handler'],
		$c['api_wrapper'],
		$c['flash'],
		$c['integration_name'],
		$c['account_storage']
	);
};

$mpwd_container['update_option_user_roles_action'] = function ( $c ) {
	return new Update_Option_User_Roles_Action( $c['db'], $c['account_storage'] );
};

$mpwd_container['in_plugin_update_message_action'] = function ( $c ) {
	return new In_Plugin_Update_Message_Action(
		$c['error_handler'],
		$c['upgrade_notice'],
		$c['twig']
	);
};

$mpwd_container['admin_menu_action']   = function ( $c ) {
	return new Admin_Menu_Action( $c['twig'] );
};
$mpwd_container['authenticate_filter'] = function ( $c ) {
	return new Authenticate_Filter(
		$c['error_handler'],
		$c['account_storage'],
		$c['user_storage'],
		$c['session'],
		$c['login_process'],
		$c['twig']
	);
};

$mpwd_container['destroy_session_action'] = function ( $c ) {
	return new Destroy_Session_Action( $c['session'] );
};

$mpwd_container['regenerate_session_action'] = function ( $c ) {
	return new Regenerate_Session_Action( $c['session'] );
};
