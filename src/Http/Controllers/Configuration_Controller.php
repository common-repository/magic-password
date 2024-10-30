<?php

namespace TwoFAS\MagicPassword\Http\Controllers;

use InvalidArgumentException;
use TwoFAS\Account\OAuth\TokenNotFoundException;
use TwoFAS\Api\Exception\AuthorizationException as API_Authorization_Exception;
use TwoFAS\Api\Exception\Exception as API_Exception;
use TwoFAS\Api\Exception\ValidationException as API_Validation_Exception;
use TwoFAS\Api\IntegrationUser;
use TwoFAS\Api\MobileSecretGenerator;
use TwoFAS\Api\QrCode\QrClientFactory;
use TwoFAS\Api\QrCodeGenerator;
use TwoFAS\Api\TotpSecretGenerator;
use TwoFAS\Core\Http\JSON_Response;
use TwoFAS\Core\Http\Redirect_Response;
use TwoFAS\Core\Http\View_Response;
use TwoFAS\Encryption\Exceptions\RandomBytesGenerateException;
use TwoFAS\MagicPassword\Codes\QR_Code_Generator;
use TwoFAS\MagicPassword\Exceptions\Failed_Pairing_Exception;
use TwoFAS\MagicPassword\Exceptions\Forbidden_Action_Exception;
use TwoFAS\MagicPassword\Exceptions\User_Not_Set_Exception;
use TwoFAS\MagicPassword\Helpers\Flash;
use TwoFAS\MagicPassword\Http\Action_URL;
use TwoFAS\MagicPassword\Http\Login_Cookie;
use TwoFAS\MagicPassword\Http\Request;
use TwoFAS\MagicPassword\Integration\API_Wrapper;
use TwoFAS\MagicPassword\Integration\User_Configuration;
use TwoFAS\MagicPassword\Services\Pair_Service;
use TwoFAS\MagicPassword\Services\Pusher_Session_Service;
use TwoFAS\MagicPassword\Storage\Account_Storage;
use TwoFAS\MagicPassword\Storage\User_Storage;
use UnexpectedValueException;

class Configuration_Controller extends Controller {

	/**
	 * @var Flash
	 */
	private $flash;

	/**
	 * @var API_Wrapper
	 */
	private $api_wrapper;

	/**
	 * @var Account_Storage
	 */
	private $account_storage;

	/**
	 * @var User_Storage
	 */
	private $user_storage;

	/**
	 * @var User_Configuration
	 */
	private $user_configuration;

	/**
	 * @var Pair_Service
	 */
	private $pair_service;

	/**
	 * @var Pusher_Session_Service
	 */
	private $pusher_session_service;

	/**
	 * @var Login_Cookie
	 */
	private $login_cookie;

	/**
	 * @param Flash                  $flash
	 * @param API_Wrapper            $api_wrapper
	 * @param Account_Storage        $account_storage
	 * @param User_Storage           $user_storage
	 * @param User_Configuration     $user_configuration
	 * @param Pair_Service           $pair_service
	 * @param Pusher_Session_Service $pusher_session_service
	 * @param Login_Cookie           $login_cookie
	 */
	public function __construct(
		Flash $flash,
		API_Wrapper $api_wrapper,
		Account_Storage $account_storage,
		User_Storage $user_storage,
		User_Configuration $user_configuration,
		Pair_Service $pair_service,
		Pusher_Session_Service $pusher_session_service,
		Login_Cookie $login_cookie
	) {
		$this->flash                  = $flash;
		$this->api_wrapper            = $api_wrapper;
		$this->account_storage        = $account_storage;
		$this->user_storage           = $user_storage;
		$this->user_configuration     = $user_configuration;
		$this->pair_service           = $pair_service;
		$this->pusher_session_service = $pusher_session_service;
		$this->login_cookie           = $login_cookie;
	}

	/**
	 * @param Request $request
	 *
	 * @return View_Response
	 *
	 * @throws API_Exception
	 * @throws TokenNotFoundException
	 * @throws User_Not_Set_Exception
	 * @throws UnexpectedValueException
	 * @throws RandomBytesGenerateException
	 */
	public function show_configuration_page( Request $request ) {
		$integration_id   = $this->account_storage->retrieve_integration_id();
		$integration_user = $this->get_integration_user();
		$session_id       = $this->pusher_session_service->get_session_id();

		if ( $integration_user->getTotpSecret() ) {
			return $this->configured_view( $integration_user, $integration_id, $session_id );
		}

		return $this->not_configured_view( $integration_user, $integration_id, $session_id );
	}

	/**
	 * @param Request $request
	 *
	 * @return JSON_Response
	 *
	 * @throws Failed_Pairing_Exception
	 * @throws InvalidArgumentException
	 * @throws API_Authorization_Exception
	 * @throws API_Validation_Exception
	 * @throws API_Exception
	 */
	public function pair( Request $request ) {
		$channel_name = $request->post( 'channel_name' );
		$status_id    = intval( $request->post( 'status_id' ) );
		$totp_secret  = $request->post( 'totp_secret' );
		$totp_code    = $request->post( 'totp_code' );

		$this->pair_service->pair( $totp_secret, $totp_code, $channel_name, $status_id );
		$this->login_cookie->set();

		return $this->json( array() );
	}

	/**
	 * @param Request $request
	 *
	 * @return Redirect_Response
	 *
	 * @throws API_Exception
	 */
	public function unpair( Request $request ) {
		$integration_user = $this->get_integration_user()
			->setTotpSecret( null )
			->setMobileSecret( null );

		$this->api_wrapper->update_integration_user( $integration_user );
		$this->user_configuration->delete();
		$this->login_cookie->delete();
		$this->flash->add_message( 'success', 'Configuration has been removed successfully.' );

		return $this->redirect( new Action_URL( $request->page() ) );
	}

	/**
	 * @param Request $request
	 *
	 * @return JSON_Response
	 */
	public function enable_passwordless_login( Request $request ) {
		$this->user_configuration->enable_passwordless_login();
		$this->login_cookie->set();

		return $this->json( array( 'message' => 'Passwordless login has been enabled.' ) );
	}

	/**
	 * @param Request $request
	 *
	 * @return JSON_Response
	 *
	 * @throws User_Not_Set_Exception
	 * @throws UnexpectedValueException
	 * @throws Forbidden_Action_Exception
	 */
	public function disable_passwordless_login( Request $request ) {
		if ( $this->user_configuration->is_passwordless_role_assigned() ) {
			throw new Forbidden_Action_Exception( 'You cannot perform this action because you have a role with obligatory login with Magic Password.' );
		}

		$this->user_configuration->disable_passwordless_login();
		$this->login_cookie->delete();

		return $this->json( array( 'message' => 'Passwordless login has been disabled.' ) );
	}

	/**
	 * @param IntegrationUser $integration_user
	 * @param int             $integration_id
	 * @param string          $session_id
	 *
	 * @return View_Response
	 *
	 * @throws User_Not_Set_Exception
	 * @throws UnexpectedValueException
	 */
	private function configured_view( IntegrationUser $integration_user, $integration_id, $session_id ) {
		$template_name = 'dashboard/configuration/configured.html.twig';
		$totp_secret   = $integration_user->getTotpSecret();

		return $this->qr_code_view( $integration_user, $integration_id, $session_id, $totp_secret, $template_name );
	}

	/**
	 * @param IntegrationUser $integration_user
	 * @param int             $integration_id
	 * @param string          $session_id
	 *
	 * @return View_Response
	 *
	 * @throws User_Not_Set_Exception
	 * @throws UnexpectedValueException
	 */
	private function not_configured_view( IntegrationUser $integration_user, $integration_id, $session_id ) {
		$template_name = 'dashboard/configuration/not-configured.html.twig';
		$totp_secret   = TotpSecretGenerator::generate();

		return $this->qr_code_view( $integration_user, $integration_id, $session_id, $totp_secret, $template_name );
	}

	/**
	 * @param IntegrationUser $integration_user
	 * @param int             $integration_id
	 * @param string          $session_id
	 * @param string          $totp_secret
	 * @param string          $template_name
	 *
	 * @return View_Response
	 *
	 * @throws User_Not_Set_Exception
	 * @throws UnexpectedValueException
	 */
	private function qr_code_view( IntegrationUser $integration_user, $integration_id, $session_id, $totp_secret, $template_name ) {
		$qr_code_generator = new QR_Code_Generator( new QrCodeGenerator( QrClientFactory::getInstance() ) );
		$mobile_secret     = $integration_user->getMobileSecret();
		$configured        = ! is_null( $integration_user->getTotpSecret() );
		$login             = $this->user_storage->get_shortened_login();

		$qr_code = $qr_code_generator->generate_config_code(
			$integration_id,
			$session_id,
			$totp_secret,
			$mobile_secret,
			$login
		);

		return $this->view( $template_name, array(
			'qr_code'                          => $qr_code,
			'session_id'                       => $session_id,
			'totp_secret'                      => $totp_secret,
			'mobile_secret'                    => $mobile_secret,
			'integration_id'                   => $integration_id,
			'is_passwordless_login_configured' => $configured,
			'is_passwordless_login_enabled'    => $this->user_configuration->is_passwordless_login_enabled(),
			'is_passwordless_login_obligatory' => $this->user_configuration->is_passwordless_login_obligatory(),
			'login'                            => $login,
			'has_passwordless_role'            => $this->user_configuration->is_passwordless_role_assigned(),
			'is_plugin_enabled'                => $this->account_storage->is_plugin_enabled(),
		) );
	}

	/**
	 * @return IntegrationUser
	 *
	 * @throws API_Exception
	 * @throws User_Not_Set_Exception
	 */
	private function get_integration_user() {
		if ( ! $this->api_wrapper->is_account_created() ) {
			return new IntegrationUser();
		}

		$integration_user = $this->api_wrapper->get_integration_user_by_external_id( $this->user_storage->get_id() );

		if ( is_null( $integration_user ) ) {
			$integration_user = $this->api_wrapper->create_integration_user( $this->user_storage->get_id() );
		}

		if ( is_null( $integration_user->getMobileSecret() ) ) {
			$integration_user->setMobileSecret( MobileSecretGenerator::generate() );
			$this->api_wrapper->update_integration_user( $integration_user );
		}

		return $integration_user;
	}
}
