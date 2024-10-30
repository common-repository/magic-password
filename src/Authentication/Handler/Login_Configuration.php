<?php

namespace TwoFAS\MagicPassword\Authentication\Handler;

use InvalidArgumentException;
use TwoFAS\Api\Exception\AuthorizationException as API_Authorization_Exception;
use TwoFAS\Api\Exception\Exception as API_Exception;
use TwoFAS\Api\Exception\ValidationException as API_Validation_Exception;
use TwoFAS\Core\Http\JSON_Response;
use TwoFAS\Core\Http\View_Response;
use TwoFAS\MagicPassword\Exceptions\Failed_Pairing_Exception;
use TwoFAS\MagicPassword\Exceptions\User_ID_Not_Found_Exception;
use TwoFAS\MagicPassword\Exceptions\User_Not_Found_Exception;
use TwoFAS\MagicPassword\Helpers\Flash;
use TwoFAS\MagicPassword\Http\Login_Cookie;
use TwoFAS\MagicPassword\Http\Request;
use TwoFAS\MagicPassword\Services\Pair_Service;
use TwoFAS\MagicPassword\Storage\User_Storage;
use WP_Error;
use WP_User;

class Login_Configuration extends Login_Handler {

	/**
	 * @var Pair_Service
	 */
	private $pair_service;

	/**
	 * @var Flash
	 */
	private $flash;

	/**
	 * @var Login_Cookie
	 */
	private $login_cookie;

	/**
	 * @param Request      $request
	 * @param User_Storage $user_storage
	 * @param Pair_Service $pair_service
	 * @param Flash        $flash
	 * @param Login_Cookie $login_cookie
	 */
	public function __construct( Request $request, User_Storage $user_storage, Pair_Service $pair_service, Flash $flash, Login_Cookie $login_cookie ) {
		parent::__construct( $request, $user_storage );
		$this->pair_service = $pair_service;
		$this->flash        = $flash;
		$this->login_cookie = $login_cookie;
	}

	/**
	 * @param WP_Error|WP_User $user
	 *
	 * @return bool
	 */
	public function supports( $user ) {
		return $user instanceof WP_Error && 'login-configuration' === $this->request->post( 'action' );
	}

	/**
	 * @param WP_Error|WP_User $user
	 *
	 * @return JSON_Response|View_Response|null
	 *
	 * @throws Failed_Pairing_Exception
	 * @throws User_Not_Found_Exception
	 * @throws User_ID_Not_Found_Exception
	 * @throws InvalidArgumentException
	 * @throws API_Authorization_Exception
	 * @throws API_Validation_Exception
	 * @throws API_Exception
	 */
	protected function handle( $user ) {
		$channel_name = $this->request->post( 'channel_name' );
		$status_id    = intval( $this->request->post( 'status_id' ) );
		$totp_secret  = $this->request->post( 'totp_secret' );
		$totp_code    = $this->request->post( 'totp_code' );
		$user_id      = $this->request->session()->get( 'user_id' );

		if ( is_null( $user_id ) ) {
			throw new User_ID_Not_Found_Exception();
		}

		$user = $this->get_wp_user( $user_id );

		$this->user_storage->set_wp_user( $user );
		$this->pair_service->pair( $totp_secret, $totp_code, $channel_name, $status_id );
		$this->login_cookie->set();
		$this->flash->add_message( 'success', 'Magic Password has been configured successfully.' );

		return $this->json( array(
			'user_id' => $this->user_storage->get_id(),
		), 200 );
	}
}
