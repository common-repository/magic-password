<?php

namespace TwoFAS\MagicPassword\Http;

use TwoFAS\Core\Http\Cookie as Base_Cookie;

class Cookie extends Base_Cookie {

	const FLASH_MESSAGE_COOKIE_NAME_BASE = 'mpwd_flash';
	const ONE_MINUTE_IN_SECONDS          = 60;

	/**
	 * @return array
	 */
	protected function get_plugin_cookies() {
		$cookies       = array( Login_Cookie::LOGIN_COOKIE_NAME );
		$flash_cookies = $this->get_flash_cookies();

		return array_merge( $cookies, $flash_cookies );
	}

	/**
	 * @return array
	 */
	private function get_flash_cookies() {
		$cookies       = $this->get_cookies();
		$flash_cookies = array();

		foreach ( $cookies as $cookie_name => $cookie_value ) {
			if ( $this->is_flash_cookie( $cookie_name ) ) {
				$flash_cookies[] = $cookie_name;
			}
		}

		return $flash_cookies;
	}

	/**
	 * @param string $cookie_name
	 *
	 * @return bool
	 */
	private function is_flash_cookie( $cookie_name ) {
		return false !== strpos( $cookie_name, self::FLASH_MESSAGE_COOKIE_NAME_BASE );
	}
}
