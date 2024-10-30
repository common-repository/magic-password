<?php

namespace TwoFAS\MagicPassword\Core;

use Exception;
use TwoFAS\Account\OAuth\TokenType;
use TwoFAS\MagicPassword\Exceptions\Handler\Error_Handler;
use TwoFAS\MagicPassword\Integration\API_Wrapper;
use TwoFAS\MagicPassword\Integration\User_Configuration;
use TwoFAS\MagicPassword\Storage\Account_Storage;
use TwoFAS\MagicPassword\Storage\User_Storage;
use TwoFAS\MagicPassword\Update\Migrator;

class Uninstaller {

	/**
	 * @var Migrator
	 */
	private $migrator;

	/**
	 * @var API_Wrapper
	 */
	private $api_wrapper;

	/**
	 * @var Error_Handler
	 */
	private $error_handler;

	/**
	 * @param Migrator      $migrator
	 * @param API_Wrapper   $api_wrapper
	 * @param Error_Handler $error_handler
	 */
	public function __construct( Migrator $migrator, API_Wrapper $api_wrapper, Error_Handler $error_handler ) {
		$this->migrator      = $migrator;
		$this->api_wrapper   = $api_wrapper;
		$this->error_handler = $error_handler;
	}

	public function uninstall() {
		try {
			$this->migrator->rollback_all();
		} catch ( Exception $e ) {
			$this->error_handler->capture_exception( $e );
		}

		try {
			$this->api_wrapper->delete_integration();
		} catch ( Exception $e ) {
			$this->error_handler->capture_exception( $e );
		}

		$this->delete_options();
		$this->delete_user_data();
	}

	private function delete_options() {
		delete_option( Account_Storage::VERSION );
		delete_option( Account_Storage::EMAIL );
		delete_option( Account_Storage::INTEGRATION_LOGIN );
		delete_option( Account_Storage::KEY_TOKEN );
		delete_option( Account_Storage::ENCRYPTION_KEY );
		delete_option( Account_Storage::OAUTH_TOKEN_BASE . TokenType::SETUP );
		delete_option( Account_Storage::OAUTH_TOKEN_BASE . TokenType::PASSWORDLESS_WORDPRESS );
		delete_option( Account_Storage::PASSWORDLESS_ROLES );
		delete_option( Account_Storage::LOGGING_ALLOWED );
		delete_option( Account_Storage::PLUGIN_DISABLED );
	}

	private function delete_user_data() {
		$users = get_users();

		foreach ( $users as $user ) {
			delete_user_meta( $user->ID, User_Configuration::PASSWORDLESS_LOGIN_STATUS_KEY );
			delete_user_meta( $user->ID, User_Configuration::OBLIGATORINESS_STATUS_KEY );
			delete_user_meta( $user->ID, User_Storage::BLOCKED_UNTIL );
			delete_user_meta( $user->ID, User_Storage::FAILED_LOGIN_ATTEMPT_COUNT );
		}
	}
}
