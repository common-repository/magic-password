<?php

namespace TwoFAS\Core\Http;

abstract class Cookie {

	/**
	 * @var array
	 */
	private $cookies;

	/**
	 * @param array $cookies
	 */
	public function __construct( array $cookies ) {
		$this->cookies = $cookies;
	}

	/**
	 * @return array
	 */
	abstract protected function get_plugin_cookies();

	public function delete_plugin_cookies() {
		foreach ( $this->get_plugin_cookies() as $plugin_cookie ) {
			$this->delete_cookie( $plugin_cookie );
		}
	}

	/**
	 * @return array
	 */
	public function get_cookies() {
		return $this->cookies;
	}

	/**
	 * @return bool
	 */
	public function is_empty() {
		return count( $this->cookies ) === 0;
	}

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public function has_cookie( $name ) {
		return isset( $this->cookies[ $name ] );
	}

	/**
	 * @param string $name
	 * @param string $value
	 * @param int    $lifespan Time in seconds the cookie exists.
	 * @param bool   $http_only
	 */
	public function set_cookie( $name, $value, $lifespan, $http_only = false ) {
		$expire = time() + $lifespan;
		setcookie( $name, $value, $expire, '/', '', false, $http_only );
	}

	/**
	 * @param string $name
	 *
	 * @return array|string
	 */
	public function get_cookie( $name ) {
		if ( $this->has_cookie( $name ) ) {
			return $this->cookies[ $name ];
		}

		return '';
	}

	/**
	 * @param string $name
	 */
	public function delete_cookie( $name ) {
		$this->set_cookie( $name, '', -3600 );

		if ( $this->has_cookie( $name ) ) {
			unset( $_COOKIE[ $name ] );
		}
	}
}
