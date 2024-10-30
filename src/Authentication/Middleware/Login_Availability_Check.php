<?php

namespace TwoFAS\MagicPassword\Authentication\Middleware;

use TwoFAS\Core\Http\JSON_Response;
use TwoFAS\Core\Http\View_Response;
use TwoFAS\MagicPassword\Http\Request;
use TwoFAS\MagicPassword\Integration\User_Configuration;
use WP_Error;
use WP_User;

class Login_Availability_Check extends Middleware {

	/**
	 * @var Request
	 */
	private $request;

	/**
	 * @var User_Configuration
	 */
	private $user_configuration;

	/**
	 * @param Request            $request
	 * @param User_Configuration $user_configuration
	 */
	public function __construct( Request $request, User_Configuration $user_configuration ) {
		$this->request            = $request;
		$this->user_configuration = $user_configuration;
	}

	/**
	 * @param WP_Error|WP_User                 $user
	 * @param JSON_Response|View_Response|null $response
	 *
	 * @return JSON_Response|View_Response|null
	 */
	public function handle( $user, $response = null ) {
		if ( $this->supports( $user ) ) {
			return $this->json_error( 'Logging in with login and password is disabled for this account.', 403 );
		}

		return $this->run_next( $user, $response );
	}

	/**
	 * @param null|WP_Error|WP_User $user
	 *
	 * @return bool
	 */
	private function supports( $user ) {
		if ( ! $this->is_wp_user( $user ) ) {
			return false;
		}

		if ( 'passwordless-login' === $this->request->post( 'action' ) ) {
			return false;
		}

		return $this->user_configuration->is_passwordless_login_enabled()
			&& $this->user_configuration->is_passwordless_login_obligatory();
	}
}
