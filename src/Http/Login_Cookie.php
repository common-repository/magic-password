<?php

namespace TwoFAS\MagicPassword\Http;

class Login_Cookie {

	const LOGIN_COOKIE_NAME    = 'mpwd_login';
	const ONE_MONTH_IN_SECONDS = 2592000;

	/**
	 * @var Cookie
	 */
	private $cookie;

	/**
	 * @param Cookie $cookie
	 */
	public function __construct( Cookie $cookie ) {
		$this->cookie = $cookie;
	}

	public function set() {
		$this->cookie->set_cookie( self::LOGIN_COOKIE_NAME, '1', self::ONE_MONTH_IN_SECONDS );
	}

	public function delete() {
		$this->cookie->delete_cookie( self::LOGIN_COOKIE_NAME );
	}
}
