<?php

namespace TwoFAS\MagicPassword\Authentication\Handler;

use TwoFAS\Account\OAuth\TokenNotFoundException;
use TwoFAS\Api\Exception\Exception as API_Exception;
use TwoFAS\Api\MobileSecretGenerator;
use TwoFAS\Api\TotpSecretGenerator;
use TwoFAS\Core\Http\JSON_Response;
use TwoFAS\Core\Http\View_Response;
use TwoFAS\Encryption\Exceptions\RandomBytesGenerateException;
use TwoFAS\MagicPassword\Codes\QR_Code_Generator;
use TwoFAS\MagicPassword\Exceptions\Invalid_Nonce_Exception;
use TwoFAS\MagicPassword\Exceptions\User_ID_Not_Found_Exception;
use TwoFAS\MagicPassword\Exceptions\User_Not_Set_Exception;
use TwoFAS\MagicPassword\Http\Request;
use TwoFAS\MagicPassword\Integration\API_Wrapper;
use TwoFAS\MagicPassword\Integration\User_Configuration;
use TwoFAS\MagicPassword\Services\Pusher_Session_Service;
use TwoFAS\MagicPassword\Storage\Account_Storage;
use TwoFAS\MagicPassword\Storage\User_Storage;
use UnexpectedValueException;
use WP_Error;
use WP_User;

class Configuration_Reset extends Login_Handler {

	/**
	 * @var User_Configuration
	 */
	private $user_configuration;

	/**
	 * @var Account_Storage
	 */
	private $account_storage;

	/**
	 * @var API_Wrapper
	 */
	private $api_wrapper;

	/**
	 * @var QR_Code_Generator
	 */
	private $qr;

	/**
	 * @var Pusher_Session_Service
	 */
	private $pusher_session_service;

	/**
	 * @param Request                $request
	 * @param User_Storage           $user_storage
	 * @param User_Configuration     $user_configuration
	 * @param Account_Storage        $account_storage
	 * @param API_Wrapper            $api_wrapper
	 * @param QR_Code_Generator      $qr
	 * @param Pusher_Session_Service $pusher_session_service
	 */
	public function __construct(
		Request $request,
		User_Storage $user_storage,
		User_Configuration $user_configuration,
		Account_Storage $account_storage,
		API_Wrapper $api_wrapper,
		QR_Code_Generator $qr,
		Pusher_Session_Service $pusher_session_service
	) {
		parent::__construct( $request, $user_storage );

		$this->user_configuration     = $user_configuration;
		$this->account_storage        = $account_storage;
		$this->api_wrapper            = $api_wrapper;
		$this->qr                     = $qr;
		$this->pusher_session_service = $pusher_session_service;
	}

	/**
	 * @param WP_Error|WP_User $user
	 *
	 * @return bool
	 *
	 * @throws User_Not_Set_Exception
	 * @throws UnexpectedValueException
	 */
	public function supports( $user ) {
		if ( $this->is_wp_user( $user ) ) {
			return $this->user_configuration->is_passwordless_role_assigned()
				&& $this->user_configuration->is_passwordless_login_disabled();
		}

		return $user instanceof WP_Error && 'configuration-reset' === $this->request->post( 'action' );
	}

	/**
	 * @param WP_Error|WP_User $user
	 *
	 * @return JSON_Response|View_Response|null
	 *
	 * @throws API_Exception
	 * @throws User_ID_Not_Found_Exception
	 * @throws User_Not_Set_Exception
	 * @throws Invalid_Nonce_Exception
	 * @throws TokenNotFoundException
	 * @throws RandomBytesGenerateException
	 */
	protected function handle( $user ) {
		if ( 'configuration-reset' === $this->request->post( 'action' ) ) {
			if ( ! $this->request->validate_nonce( 'configuration-reset' ) ) {
				throw new Invalid_Nonce_Exception();
			}
		}

		$user_id = $this->request->session()->get( 'user_id' );

		if ( is_null( $user_id ) ) {
			throw new User_ID_Not_Found_Exception();
		}

		$user = $this->get_wp_user( $user_id );
		$this->user_storage->set_wp_user( $user );

		$integration_id   = $this->account_storage->retrieve_integration_id();
		$session_id       = $this->pusher_session_service->get_session_id();
		$integration_user = $this->api_wrapper->get_integration_user_by_external_id( $this->user_storage->get_id() );

		if ( is_null( $integration_user ) ) {
			$integration_user = $this->api_wrapper->create_integration_user( $this->user_storage->get_id() );
		} else {
			$integration_user->setTotpSecret( null );
			$integration_user->setMobileSecret( MobileSecretGenerator::generate() );
			$integration_user = $this->api_wrapper->update_integration_user( $integration_user );
		}

		$this->user_configuration->delete();

		$mobile_secret = $integration_user->getMobileSecret();
		$totp_secret   = TotpSecretGenerator::generate();
		$login         = $this->user_storage->get_shortened_login();

		$data = array(
			'qr_code'        => $this->qr->generate_config_code( $integration_id, $session_id, $totp_secret, $mobile_secret, $login ),
			'integration_id' => $integration_id,
			'session_id'     => $session_id,
			'totp_secret'    => $totp_secret,
			'mobile_secret'  => $mobile_secret,
			'login'          => $login,
		);

		if ( $this->request->has( 'interim-login' ) ) {
			$data['interim_login'] = true;
		}

		return $this->view( 'login/configuration.html.twig', $data );
	}
}
