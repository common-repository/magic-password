<?php

namespace TwoFAS\MagicPassword\Authentication\Middleware;

use TwoFAS\Core\Http\JSON_Response;
use TwoFAS\Core\Http\View_Response;
use TwoFAS\MagicPassword\Http\Login_Cookie;
use WP_Error;
use WP_User;

class Login_Cookie_Deletion extends Middleware {

	/**
	 * @var Login_Cookie
	 */
	private $login_cookie;

	/**
	 * @param Login_Cookie $login_cookie
	 */
	public function __construct( Login_Cookie $login_cookie ) {
		$this->login_cookie = $login_cookie;
	}

	/**
	 * @param WP_Error|WP_User                 $user
	 * @param JSON_Response|View_Response|null $response
	 *
	 * @return JSON_Response|View_Response|null
	 */
	public function handle( $user, $response = null ) {
		if ( $this->supports( $user ) ) {
			$this->login_cookie->delete();
		}

		return $this->run_next( $user, $response );
	}

	/**
	 * @param WP_Error|WP_User $user
	 *
	 * @return bool
	 */
	private function supports( $user ) {
		return $user instanceof WP_User || $this->are_fields_filled( $user->get_error_codes() );
	}

	/**
	 * @param array $codes
	 *
	 * @return bool
	 */
	private function are_fields_filled( array $codes ) {
		return ! ( in_array( 'empty_username', $codes ) && in_array( 'empty_password', $codes ) );
	}
}
