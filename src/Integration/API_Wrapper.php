<?php

namespace TwoFAS\MagicPassword\Integration;

use InvalidArgumentException;
use TwoFAS\Account\Client;
use TwoFAS\Account\Exception\AuthorizationException as Account_Authorization_Exception;
use TwoFAS\Account\Exception\Exception as Account_Exception;
use TwoFAS\Account\Exception\NotFoundException;
use TwoFAS\Account\Exception\TokenRefreshException;
use TwoFAS\Account\Exception\ValidationException as Account_Validation_Exception;
use TwoFAS\Account\Integration;
use TwoFAS\Account\Key;
use TwoFAS\Account\OAuth\Token;
use TwoFAS\Account\OAuth\TokenNotFoundException;
use TwoFAS\Account\TwoFAS as Account;
use TwoFAS\Api\Authentication;
use TwoFAS\Api\AuthenticationCollection;
use TwoFAS\Api\Code\Code;
use TwoFAS\Api\Exception\AuthorizationException as API_Authorization_Exception;
use TwoFAS\Api\Exception\ChannelNotActiveException;
use TwoFAS\Api\Exception\Exception as API_Exception;
use TwoFAS\Api\Exception\IntegrationUserNotFoundException;
use TwoFAS\Api\Exception\InvalidDateException;
use TwoFAS\Api\Exception\ValidationException as API_Validation_Exception;
use TwoFAS\Api\IntegrationUser;
use TwoFAS\Api\MobileSecretGenerator;
use TwoFAS\Api\TwoFAS as API;
use TwoFAS\Encryption\AESGeneratedKey;
use TwoFAS\Encryption\RsaCryptographer;
use TwoFAS\MagicPassword\Factories\Account_Factory;
use TwoFAS\MagicPassword\Factories\API_Factory;
use TwoFAS\MagicPassword\Storage\Account_Storage;

class API_Wrapper {

	/**
	 * @var Account_Storage
	 */
	private $account_storage;

	/**
	 * @var API_Factory
	 */
	private $api_factory;

	/**
	 * @var Account_Factory
	 */
	private $account_factory;

	/**
	 * @var API
	 */
	private $api;

	/**
	 * @var Account
	 */
	private $account;

	/**
	 * @var Integration_Name
	 */
	private $integration_name;

	/**
	 * @param Account_Storage  $account_storage
	 * @param API_Factory      $api_factory
	 * @param Account_Factory  $account_factory
	 * @param Integration_Name $integration_name
	 */
	public function __construct(
		Account_Storage $account_storage,
		API_Factory $api_factory,
		Account_Factory $account_factory,
		Integration_Name $integration_name
	) {
		$this->account_storage  = $account_storage;
		$this->api_factory      = $api_factory;
		$this->account_factory  = $account_factory;
		$this->integration_name = $integration_name;
	}

	/**
	 * @return API
	 */
	private function api() {
		if ( ! $this->api ) {
			$this->api = $this->api_factory->create();
		}

		return $this->api;
	}

	/**
	 * @return Account
	 */
	private function account() {
		if ( ! $this->account ) {
			$this->account = $this->account_factory->create();
		}

		return $this->account;
	}

	/**
	 * @return Client
	 *
	 * @throws Account_Exception
	 */
	public function get_client() {
		return $this->account()->getClient();
	}

	/**
	 * @param string $email
	 * @param string $password
	 *
	 * @throws Account_Validation_Exception
	 * @throws Account_Authorization_Exception
	 * @throws Account_Exception
	 */
	public function create_account( $email, $password ) {
		$client      = $this->account()->createClient( $email, $password, $password, 'wordpress' );
		$integration = $this->create_integration( $email, $password );
		$key         = $this->create_key( $email, $password, $integration->getId() );

		$this->account_storage->store_integration( new AESGeneratedKey(), $integration->getLogin(), $key->getToken(), $client->getEmail() );
	}

	/**
	 * @param string $email
	 * @param string $password
	 *
	 * @return Integration
	 *
	 * @throws Account_Validation_Exception
	 * @throws Account_Authorization_Exception
	 * @throws Account_Exception
	 */
	private function create_integration( $email, $password ) {
		$this->account()->generateOAuthSetupToken( $email, $password );

		$name = $this->integration_name->create( $this->account_storage->get_bare_wp_name() );

		return $this->account()->createIntegration( $name );
	}

	/**
	 * @param string $email
	 * @param string $password
	 * @param int    $integration_id
	 *
	 * @return Key
	 *
	 * @throws Account_Validation_Exception
	 * @throws Account_Authorization_Exception
	 * @throws Account_Exception
	 */
	private function create_key( $email, $password, $integration_id ) {
		$this->account()->generateIntegrationSpecificToken( $email, $password, $integration_id );

		return $this->account()->createKey( $integration_id, 'WordPress' );
	}

	/**
	 * @return bool
	 */
	public function is_account_created() {
		return $this->account_storage->is_account_created();
	}

	/**
	 * @return Integration|null
	 *
	 * @throws TokenNotFoundException
	 * @throws NotFoundException
	 * @throws Account_Exception
	 */
	public function get_integration() {
		return $this->account()->getIntegration( $this->account_storage->retrieve_integration_id() );
	}

	/**
	 * @param Integration $integration
	 *
	 * @throws Account_Validation_Exception
	 * @throws Account_Exception
	 */
	public function update_integration( Integration $integration ) {
		$this->account()->updateIntegration( $integration );
	}

	/**
	 * @param int $user_id
	 *
	 * @return IntegrationUser
	 *
	 * @throws API_Exception
	 */
	public function create_integration_user( $user_id ) {
		$integration_user = new IntegrationUser();
		$integration_user->setExternalId( (string) $user_id );
		$integration_user->setMobileSecret( MobileSecretGenerator::generate() );

		$this->api()->addIntegrationUser( $this->account_storage, $integration_user );

		return $integration_user;
	}

	/**
	 * @param string $user_id
	 *
	 * @return IntegrationUser|null
	 *
	 * @throws API_Authorization_Exception
	 * @throws API_Exception
	 */
	public function get_integration_user( $user_id ) {
		try {
			return $this->api()->getIntegrationUser( $this->account_storage, $user_id );
		} catch ( IntegrationUserNotFoundException $e ) {
			return null;
		}
	}

	/**
	 * @param int $user_id
	 *
	 * @return IntegrationUser|null
	 *
	 * @throws API_Exception
	 */
	public function get_integration_user_by_external_id( $user_id ) {
		try {
			return $this->api()->getIntegrationUserByExternalId( $this->account_storage, (string) $user_id );
		} catch ( IntegrationUserNotFoundException $e ) {
			return null;
		}
	}

	/**
	 * @param IntegrationUser $integration_user
	 *
	 * @return IntegrationUser
	 *
	 * @throws API_Exception
	 */
	public function update_integration_user( IntegrationUser $integration_user ) {
		return $this->api()->updateIntegrationUser( $this->account_storage, $integration_user );
	}

	/**
	 * @param string $totp_secret
	 *
	 * @return Authentication
	 *
	 * @throws API_Authorization_Exception
	 * @throws ChannelNotActiveException
	 * @throws InvalidDateException
	 * @throws API_Validation_Exception
	 * @throws API_Exception
	 */
	public function request_auth( $totp_secret ) {
		return $this->api()->requestAuthViaTotp( $totp_secret );
	}

	/**
	 * @param AuthenticationCollection $authentications
	 * @param string                   $code
	 *
	 * @return Code
	 *
	 * @throws API_Authorization_Exception
	 * @throws API_Exception
	 */
	public function check_code( AuthenticationCollection $authentications, $code ) {
		return $this->api()->checkCode( $authentications, $code );
	}

	/**
	 * @param string $integration_id
	 * @param string $session_id
	 * @param string $socket_id
	 *
	 * @return array
	 *
	 * @throws API_Authorization_Exception
	 * @throws API_Validation_Exception
	 * @throws API_Exception
	 */
	public function authenticate_channel( $integration_id, $session_id, $socket_id ) {
		return $this->api()->authenticateChannel( $integration_id, $session_id, $socket_id );
	}

	/**
	 * @param string $channel_name
	 * @param int    $status_id
	 * @param string $status
	 *
	 * @throws API_Authorization_Exception
	 * @throws API_Validation_Exception
	 * @throws InvalidArgumentException
	 * @throws API_Exception
	 */
	public function update_channel_status( $channel_name, $status_id, $status ) {
		$this->api()->updateChannelStatus( $channel_name, $status_id, $status );
	}

	/**
	 * @return RsaCryptographer
	 *
	 * @throws TokenNotFoundException
	 * @throws NotFoundException
	 * @throws Account_Exception
	 */
	public function get_cryptographer() {
		$integration = $this->get_integration();

		return new RsaCryptographer( $integration->getPublicKey(), $integration->getPrivateKey() );
	}

	/**
	 * @return Integration
	 *
	 * @throws Account_Exception
	 * @throws NotFoundException
	 * @throws TokenNotFoundException
	 * @throws Account_Validation_Exception
	 */
	public function reset_integration_encryption_keys() {
		return $this->account()->resetIntegrationEncryptionKeys( $this->get_integration() );
	}

	/**
	 * @throws Account_Exception
	 * @throws NotFoundException
	 * @throws TokenNotFoundException
	 */
	public function delete_integration() {
		$this->account()->deleteIntegration( $this->get_integration() );
	}

	/**
	 * @param Token $token
	 *
	 * @throws Account_Exception
	 * @throws TokenNotFoundException
	 * @throws TokenRefreshException
	 */
	public function refresh_token( Token $token ) {
		$this->account()->refreshToken( $token );
	}
}
