<?php

namespace TwoFAS\MagicPassword\Authentication\Middleware;

use TwoFAS\Account\OAuth\TokenNotFoundException;
use TwoFAS\Api\Exception\Exception as API_Exception;
use TwoFAS\Core\Http\JSON_Response;
use TwoFAS\Core\Http\View_Response;
use TwoFAS\Encryption\Exceptions\RandomBytesGenerateException;
use TwoFAS\MagicPassword\Codes\QR_Code_Generator;
use TwoFAS\MagicPassword\Exceptions\User_Not_Found_Exception;
use TwoFAS\MagicPassword\Http\Request;
use TwoFAS\MagicPassword\Integration\User_Configuration;
use TwoFAS\MagicPassword\Services\Pusher_Session_Service;
use TwoFAS\MagicPassword\Storage\Account_Storage;
use UnexpectedValueException;
use WP_Error;
use WP_User;

class Confirmation_View extends Middleware {

	/**
	 * @var Request
	 */
	private $request;

	/**
	 * @var User_Configuration
	 */
	private $user_configuration;

	/**
	 * @var Account_Storage
	 */
	private $account_storage;

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
	 * @param User_Configuration     $user_configuration
	 * @param Account_Storage        $account_storage
	 * @param QR_Code_Generator      $qr
	 * @param Pusher_Session_Service $pusher_session_service
	 */
	public function __construct(
		Request $request,
		User_Configuration $user_configuration,
		Account_Storage $account_storage,
		QR_Code_Generator $qr,
		Pusher_Session_Service $pusher_session_service
	) {
		$this->request                = $request;
		$this->user_configuration     = $user_configuration;
		$this->account_storage        = $account_storage;
		$this->qr                     = $qr;
		$this->pusher_session_service = $pusher_session_service;
	}

	/**
	 * @param null|WP_Error|WP_User            $user
	 * @param JSON_Response|View_Response|null $response
	 *
	 * @return JSON_Response|View_Response|null
	 *
	 * @throws TokenNotFoundException
	 * @throws RandomBytesGenerateException
	 * @throws API_Exception
	 * @throws User_Not_Found_Exception
	 * @throws UnexpectedValueException
	 */
	public function handle( $user, $response = null ) {
		if ( $this->supports( $user ) ) {
			$session_id     = $this->pusher_session_service->get_session_id();
			$integration_id = (string) $this->account_storage->retrieve_integration_id();

			$response = $this->view( 'login/secondary.html.twig', $this->get_view_data( $session_id, $integration_id ) );
		}

		return $this->run_next( $user, $response );
	}

	/**
	 * @param null|WP_Error|WP_User $user
	 *
	 * @return bool
	 *
	 * @throws API_Exception
	 * @throws User_Not_Found_Exception
	 * @throws UnexpectedValueException
	 */
	private function supports( $user ) {
		if ( ! $this->is_wp_user( $user ) ) {
			return false;
		}

		if ( 'passwordless-login' === $this->request->post( 'action' ) ) {
			return false;
		}

		return $this->user_configuration->is_passwordless_role_assigned()
			&& $this->user_configuration->is_passwordless_login_configured();
	}

	/**
	 * @param string $session_id
	 * @param string $integration_id
	 *
	 * @return array
	 */
	private function get_view_data( $session_id, $integration_id ) {
		$data = array(
			'session_id'     => $session_id,
			'integration_id' => $integration_id,
			'qr_code'        => $this->qr->generate_login_code( $integration_id, $session_id ),
		);

		if ( $this->request->has( 'interim-login' ) ) {
			$data['interim_login'] = true;
		}

		return $data;
	}
}
