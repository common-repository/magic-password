<?php

namespace TwoFAS\MagicPassword\Exceptions\Handler;

use Exception;
use Raven_Client;
use TwoFAS\Account\TwoFAS as Account;
use TwoFAS\Api\TwoFAS as API;
use TwoFAS\Core\Exceptions\Handler\Logger_Interface;
use TwoFAS\MagicPassword\Helpers\Config;
use TwoFAS\MagicPassword\Integration\User_Configuration;
use TwoFAS\MagicPassword\Storage\Account_Storage;

class Sentry_Logger implements Logger_Interface {

	/**
	 * @var Raven_Client
	 */
	private $client;

	/**
	 * @param Config             $config
	 * @param User_Configuration $user_configuration
	 * @param Account_Storage    $account_storage
	 */
	public function __construct( Config $config, User_Configuration $user_configuration, Account_Storage $account_storage ) {
		$options = array(
			'processors'    => array(
				'Raven_Processor_RemoveCookiesProcessor',
			),
			'send_callback' => function ( &$event ) {
				if ( wp_login_url() === $event['request']['url'] ) {
					$site_url = get_bloginfo( 'wpurl' );
					$event['request']['url'] = '[Filtered: ' . $site_url . ']';
				}
			},
		);

		$this->client = new Raven_Client( $config->get_sentry_dsn(), $options );

		$this->client->tags_context(
			array(
				'php_version'         => phpversion(),
				'wp_version'          => $account_storage->get_wp_version(),
				'api_sdk_version'     => API::VERSION,
				'account_sdk_version' => Account::VERSION,
				'db_version'          => $account_storage->get_db_version(),
			)
		);

		if ( $user_configuration->is_user_set() ) {
			$this->client->extra_context( array(
				'is_passwordless_login_enabled'    => $user_configuration->is_passwordless_login_enabled(),
				'is_passwordless_login_obligatory' => $user_configuration->is_passwordless_login_obligatory(),
			) );
		}

		$this->client->setRelease( MPWD_PLUGIN_VERSION );
	}

	/**
	 * @param Exception $e
	 * @param array     $options
	 */
	public function capture_exception( Exception $e, array $options = array() ) {
		$this->client->captureException( $e, $options );
	}
}
