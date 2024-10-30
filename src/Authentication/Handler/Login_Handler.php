<?php

namespace TwoFAS\MagicPassword\Authentication\Handler;

use TwoFAS\Account\OAuth\TokenNotFoundException;
use TwoFAS\Api\Exception\Exception as API_Exception;
use TwoFAS\Core\Http\JSON_Response;
use TwoFAS\Core\Http\View_Response;
use TwoFAS\MagicPassword\Exceptions\Failed_Pairing_Exception;
use TwoFAS\MagicPassword\Exceptions\Invalid_Nonce_Exception;
use TwoFAS\MagicPassword\Exceptions\Login_Restriction_Exception;
use TwoFAS\MagicPassword\Exceptions\User_ID_Not_Found_Exception;
use TwoFAS\MagicPassword\Exceptions\User_Not_Found_Exception;
use TwoFAS\MagicPassword\Http\Request;
use TwoFAS\MagicPassword\Storage\User_Storage;
use UnexpectedValueException;
use WP_Error;
use WP_User;

abstract class Login_Handler {

	/**
	 * @var Request
	 */
	protected $request;

	/**
	 * @var User_Storage
	 */
	protected $user_storage;

	/**
	 * @var null|Login_Handler
	 */
	protected $successor;

	/**
	 * @param Request      $request
	 * @param User_Storage $user_storage
	 */
	public function __construct( Request $request, User_Storage $user_storage ) {
		$this->request      = $request;
		$this->user_storage = $user_storage;
	}

	/**
	 * @param WP_Error|WP_User $user
	 *
	 * @return bool
	 */
	abstract public function supports( $user );

	/**
	 * @param WP_Error|WP_User $user
	 *
	 * @return JSON_Response|View_Response|null
	 */
	abstract protected function handle( $user );

	/**
	 * @param WP_Error|WP_User $user
	 *
	 * @return JSON_Response|View_Response|null
	 *
	 * @throws API_Exception
	 * @throws Failed_Pairing_Exception
	 * @throws User_Not_Found_Exception
	 * @throws User_ID_Not_Found_Exception
	 * @throws Invalid_Nonce_Exception
	 * @throws TokenNotFoundException
	 * @throws UnexpectedValueException
	 * @throws Login_Restriction_Exception
	 */
	public function authenticate( $user ) {
		return $this->supports( $user ) ? $this->handle( $user ) : $this->fallback( $user );
	}

	/**
	 * @param Login_Handler $successor
	 *
	 * @return Login_Handler
	 */
	public function then( Login_Handler $successor ) {
		return $this->successor = $successor;
	}

	/**
	 * @param WP_Error|WP_User $user
	 *
	 * @return JSON_Response|View_Response|null
	 *
	 * @throws API_Exception
	 * @throws Failed_Pairing_Exception
	 * @throws User_ID_Not_Found_Exception
	 * @throws Invalid_Nonce_Exception
	 * @throws TokenNotFoundException
	 */
	public function fallback( $user ) {
		return $this->successor ? $this->successor->authenticate( $user ) : null;
	}

	/**
	 * @param array $body
	 * @param int   $status_code
	 *
	 * @return JSON_Response
	 */
	protected function json( array $body, $status_code ) {
		return new JSON_Response( $body, $status_code );
	}

	/**
	 * @param string $message
	 * @param int    $status_code
	 *
	 * @return JSON_Response
	 */
	protected function json_error( $message, $status_code ) {
		return $this->json( array(
			'error' => $message,
		), $status_code );
	}

	/**
	 * @param string $template
	 * @param array  $data
	 *
	 * @return View_Response
	 */
	protected function view( $template, array $data = array() ) {
		return new View_Response( $template, $data );
	}

	/**
	 * @param null|WP_Error|WP_User $user
	 *
	 * @return bool
	 */
	protected function is_wp_user( $user ) {
		return $user instanceof WP_User;
	}

	/**
	 * @param int $user_id
	 *
	 * @return WP_User
	 *
	 * @throws User_ID_Not_Found_Exception
	 * @throws User_Not_Found_Exception
	 */
	protected function get_wp_user( $user_id ) {
		if ( is_null( $user_id ) ) {
			throw new User_ID_Not_Found_Exception();
		}

		$user = get_user_by( 'id', intval( $user_id ) );

		if ( false === $user ) {
			throw new User_Not_Found_Exception( 'User has not been found.' );
		}

		return $user;
	}
}
