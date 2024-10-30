<?php

namespace TwoFAS\MagicPassword\Authentication\Handler;

use Exception;
use TwoFAS\Account\Exception\Exception as Account_Exception;
use TwoFAS\Account\Exception\NotFoundException;
use TwoFAS\Account\OAuth\TokenNotFoundException;
use TwoFAS\Api\AuthenticationCollection;
use TwoFAS\Api\ChannelStatuses;
use TwoFAS\Api\Exception\AuthorizationException as API_Authorization_Exception;
use TwoFAS\Api\Exception\Exception as API_Exception;
use TwoFAS\Core\Http\JSON_Response;
use TwoFAS\Core\Http\View_Response;
use TwoFAS\Encryption\Exceptions\RsaDecryptException;
use TwoFAS\Encryption\RsaCryptographer;
use TwoFAS\MagicPassword\Exceptions\Login_Restriction_Exception;
use TwoFAS\MagicPassword\Exceptions\User_Not_Found_Exception;
use TwoFAS\MagicPassword\Helpers\Flash;
use TwoFAS\MagicPassword\Http\Login_Cookie;
use TwoFAS\MagicPassword\Http\Request;
use TwoFAS\MagicPassword\Integration\API_Wrapper;
use TwoFAS\MagicPassword\Integration\User_Configuration;
use TwoFAS\MagicPassword\Services\Lockout_Service;
use TwoFAS\MagicPassword\Storage\User_Storage;
use UnexpectedValueException;
use WP_Error;
use WP_User;

class Passwordless_Login extends Login_Handler {

	/**
	 * @var User_Configuration
	 */
	private $user_configuration;

	/**
	 * @var Lockout_Service
	 */
	private $lockout_service;

	/**
	 * @var API_Wrapper
	 */
	private $api_wrapper;

	/**
	 * @var Flash
	 */
	private $flash;

	/**
	 * @var Login_Cookie
	 */
	private $login_cookie;

	/**
	 * @param Request            $request
	 * @param User_Storage       $user_storage
	 * @param User_Configuration $user_configuration
	 * @param Lockout_Service    $lockout_service
	 * @param API_Wrapper        $api_wrapper
	 * @param Flash              $flash
	 * @param Login_Cookie       $login_cookie
	 */
	public function __construct(
		Request $request,
		User_Storage $user_storage,
		User_Configuration $user_configuration,
		Lockout_Service $lockout_service,
		API_Wrapper $api_wrapper,
		Flash $flash,
		Login_Cookie $login_cookie
	) {
		parent::__construct( $request, $user_storage );
		$this->user_configuration = $user_configuration;
		$this->lockout_service    = $lockout_service;
		$this->api_wrapper        = $api_wrapper;
		$this->flash              = $flash;
		$this->login_cookie       = $login_cookie;
	}

	/**
	 * @param WP_Error|WP_User $user
	 *
	 * @return bool
	 */
	public function supports( $user ) {
		return ( $user instanceof WP_Error || $user instanceof WP_User ) && 'passwordless-login' === $this->request->post( 'action' );
	}

	/**
	 * @param WP_Error|WP_User $user
	 *
	 * @return JSON_Response|View_Response|null
	 *
	 * @throws TokenNotFoundException
	 * @throws NotFoundException
	 * @throws User_Not_Found_Exception
	 * @throws RsaDecryptException
	 * @throws API_Exception
	 * @throws Account_Exception
	 * @throws UnexpectedValueException
	 * @throws Login_Restriction_Exception
	 * @throws Exception
	 */
	protected function handle( $user ) {
		$channel_name = $this->request->post( 'channel_name' );
		$status_id    = intval( $this->request->post( 'status_id' ) );

		try {
			$response = $this->process();

			if ( 200 === $response->get_status_code() ) {
				$status = ChannelStatuses::RESOLVED;
			} else {
				$status = ChannelStatuses::REJECTED;
			}

			$this->api_wrapper->update_channel_status( $channel_name, $status_id, $status );

			return $response;
		} catch ( Exception $e ) {
			$this->api_wrapper->update_channel_status( $channel_name, $status_id, ChannelStatuses::REJECTED );
			throw $e;
		}
	}

	/**
	 * @return JSON_Response
	 *
	 * @throws API_Exception
	 * @throws Account_Exception
	 * @throws NotFoundException
	 * @throws TokenNotFoundException
	 * @throws API_Authorization_Exception
	 * @throws RsaDecryptException
	 * @throws User_Not_Found_Exception
	 * @throws UnexpectedValueException
	 * @throws Login_Restriction_Exception
	 */
	private function process() {
		$cryptographer       = $this->api_wrapper->get_cryptographer();
		$totp_code           = $this->get_totp_code( $cryptographer );
		$integration_user_id = $this->get_integration_user_id( $cryptographer );
		$integration_user    = $this->api_wrapper->get_integration_user( $integration_user_id );

		if ( is_null( $integration_user ) ) {
			return $this->json_error( 'Something went wrong.', 404 );
		}

		$user_id = intval( $integration_user->getExternalId() );
		$user    = $this->get_wp_user( $user_id );
		$this->user_storage->set_wp_user( $user );

		if ( ! $this->is_user_id_valid( $user_id ) ) {
			throw new Login_Restriction_Exception( 'You cannot log in to this account. Please make sure you choose a proper one in a mobile application.' );
		}

		if ( ! $this->user_configuration->is_passwordless_role_assigned() && ! $this->user_configuration->is_passwordless_login_enabled() ) {
			return $this->json_error( 'Magic Password is disabled for this account.', 403 );
		}

		if ( $this->user_storage->is_blocked() ) {
			$time = Lockout_Service::DEFAULT_LOCKOUT_TIME_IN_MINUTES;

			return $this->json_error( 'Logging in with Magic Password has been blocked for ' . $time . ' minutes. Please try again later.', 403 );
		}

		if ( is_null( $integration_user->getTotpSecret() ) ) {
			return $this->json_error( 'TOTP secret is missing.', 400 );
		}

		$authentication  = $this->api_wrapper->request_auth( $integration_user->getTotpSecret() );
		$authentications = new AuthenticationCollection();

		$authentications->add( $authentication );

		$check = $this->api_wrapper->check_code( $authentications, $totp_code );

		if ( ! $check->accepted() ) {
			$this->lockout_service->register_failed_login_attempt();

			return $this->json_error( 'Something went wrong.', 400 );
		}

		if ( $this->user_configuration->is_passwordless_role_assigned() ) {
			$message = 'You have been obligated to log in with Magic Password by the administrator.';

			if ( ! $this->user_configuration->is_passwordless_login_obligatory() ) {
				$this->user_configuration->set_obligatory_passwordless_login();
				$this->flash->add_message( 'warning', $message );
			}

			if ( ! $this->user_configuration->is_passwordless_login_enabled() ) {
				$this->user_configuration->enable_passwordless_login();
				$this->flash->add_message( 'warning', $message );
			}
		}

		$this->login_cookie->set();
		$this->lockout_service->reset();

		return $this->json( array(
			'user_id' => $user_id,
		), 200 );
	}

	/**
	 * @param RsaCryptographer $cryptographer
	 *
	 * @return string
	 *
	 * @throws RsaDecryptException
	 */
	private function get_totp_code( RsaCryptographer $cryptographer ) {
		$totp_code = $this->request->post( 'totp_code' );

		return $cryptographer->decryptBase64( $totp_code );
	}

	/**
	 * @param RsaCryptographer $cryptographer
	 *
	 * @return string
	 *
	 * @throws RsaDecryptException
	 */
	private function get_integration_user_id( RsaCryptographer $cryptographer ) {
		$integration_user_id = $this->request->post( 'integration_user_id' );

		return $cryptographer->decryptBase64( $integration_user_id );
	}

	/**
	 * @param int $user_id
	 *
	 * @return bool
	 */
	private function is_user_id_valid( $user_id ) {
		if ( ! $this->request->session()->exists( 'user_id' ) ) {
			return true;
		}

		return intval( $this->request->session()->get( 'user_id' ) ) === $user_id;
	}
}
