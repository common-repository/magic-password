<?php

namespace TwoFAS\MagicPassword\Services;

use DateTime;
use Exception;
use InvalidArgumentException;
use TwoFAS\Account\Exception\Exception as Account_Exception;
use TwoFAS\Account\Exception\NotFoundException;
use TwoFAS\Account\OAuth\TokenNotFoundException;
use TwoFAS\Api\Authentication;
use TwoFAS\Api\AuthenticationCollection;
use TwoFAS\Api\ChannelStatuses;
use TwoFAS\Api\Exception\AuthorizationException as API_Authorization_Exception;
use TwoFAS\Api\Exception\Exception as API_Exception;
use TwoFAS\Api\Exception\ValidationException as API_Validation_Exception;
use TwoFAS\Encryption\Exceptions\RsaDecryptException;
use TwoFAS\MagicPassword\Exceptions\Failed_Pairing_Exception;
use TwoFAS\MagicPassword\Exceptions\User_Not_Set_Exception;
use TwoFAS\MagicPassword\Helpers\Flash;
use TwoFAS\MagicPassword\Integration\API_Wrapper;
use TwoFAS\MagicPassword\Integration\User_Configuration;
use UnexpectedValueException;

class Pair_Service {

	/**
	 * @var API_Wrapper
	 */
	private $api_wrapper;

	/**
	 * @var User_Configuration
	 */
	private $user_configuration;

	/**
	 * @var Flash
	 */
	private $flash;

	/**
	 * @param API_Wrapper        $api_wrapper
	 * @param User_Configuration $user_configuration
	 * @param Flash              $flash
	 */
	public function __construct( API_Wrapper $api_wrapper, User_Configuration $user_configuration, Flash $flash ) {
		$this->api_wrapper        = $api_wrapper;
		$this->user_configuration = $user_configuration;
		$this->flash              = $flash;
	}

	/**
	 * @param string $totp_secret
	 * @param string $totp_code
	 * @param string $channel_name
	 * @param int    $status_id
	 *
	 * @throws Failed_Pairing_Exception
	 * @throws InvalidArgumentException
	 * @throws API_Authorization_Exception
	 * @throws API_Validation_Exception
	 * @throws API_Exception
	 */
	public function pair( $totp_secret, $totp_code, $channel_name, $status_id ) {
		try {
			$this->process_pairing( $totp_secret, $totp_code, $channel_name, $status_id );
		} catch ( Failed_Pairing_Exception $e ) {
			$this->api_wrapper->update_channel_status( $channel_name, $status_id, ChannelStatuses::REJECTED );
			throw $e;
		}
	}

	/**
	 * @param string $totp_secret
	 * @param string $totp_code
	 * @param string $channel_name
	 * @param int    $status_id
	 *
	 * @throws Failed_Pairing_Exception
	 */
	private function process_pairing( $totp_secret, $totp_code, $channel_name, $status_id ) {
		try {
			$cryptographer   = $this->api_wrapper->get_cryptographer();
			$authentication  = $this->api_wrapper->request_auth( $totp_secret );
			$authentications = new AuthenticationCollection();
			$authentications->add( new Authentication( $authentication->id(), new DateTime(), new DateTime() ) );

			$totp_code = $cryptographer->decryptBase64( $totp_code );
			$code      = $this->api_wrapper->check_code( $authentications, $totp_code );

			if ( ! $code->accepted() ) {
				throw new Failed_Pairing_Exception( 'TOTP token is invalid.', 400 );
			}

			$integration_user = $this->api_wrapper->get_integration_user_by_external_id( $this->user_configuration->get_user_id() );

			if ( is_null( $integration_user ) ) {
				throw new Failed_Pairing_Exception( 'No integration user found.', 404 );
			}

			$should_passwordless_login_be_obligatory = $this->should_passwordless_login_be_obligatory();

			if ( $totp_secret !== $integration_user->getTotpSecret() ) {
				$integration_user->setTotpSecret( $totp_secret );
				$this->api_wrapper->update_integration_user( $integration_user );
				$this->user_configuration->enable_passwordless_login();
				$this->user_configuration->set_optional_passwordless_login();
			}

			$this->api_wrapper->update_channel_status( $channel_name, $status_id, ChannelStatuses::RESOLVED );

			if ( $should_passwordless_login_be_obligatory ) {
				$this->user_configuration->set_obligatory_passwordless_login();
				$this->flash->add_message( 'warning', 'You have been obligated to log in with Magic Password by the administrator.' );
			}
		} catch ( API_Validation_Exception $e ) {
			throw new Failed_Pairing_Exception( 'Something went wrong.', 400 );
		} catch ( TokenNotFoundException $e ) {
			throw new Failed_Pairing_Exception( 'OAuth token not found.', 404 );
		} catch ( NotFoundException $e ) {
			throw new Failed_Pairing_Exception( 'Integration has not been found.', 404 );
		} catch ( RsaDecryptException $e ) {
			throw new Failed_Pairing_Exception( 'Token could not be decrypted.', 400 );
		} catch ( API_Exception $e ) {
			throw new Failed_Pairing_Exception( 'Something went wrong.', 500 );
		} catch ( Account_Exception $e ) {
			throw new Failed_Pairing_Exception( 'Something went wrong.', 500 );
		} catch ( User_Not_Set_Exception $e ) {
			throw new Failed_Pairing_Exception( 'User not found.', 404 );
		} catch ( UnexpectedValueException $e ) {
			throw new Failed_Pairing_Exception( $e->getMessage(), 500 );
		} catch( Exception $e ) {
			throw new Failed_Pairing_Exception( 'Something went wrong.', 500 );
		}
	}

	/**
	 * @return bool
	 *
	 * @throws User_Not_Set_Exception
	 * @throws UnexpectedValueException
	 */
	private function should_passwordless_login_be_obligatory() {
		return $this->user_configuration->is_passwordless_role_assigned()
			&& $this->user_configuration->is_passwordless_login_optional();
	}
}
