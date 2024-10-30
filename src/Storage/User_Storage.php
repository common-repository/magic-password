<?php

namespace TwoFAS\MagicPassword\Storage;

use TwoFAS\MagicPassword\Exceptions\User_Not_Set_Exception;
use TwoFAS\MagicPassword\User\Capabilities;
use WP_User;

class User_Storage {

	/**
	 * @since 1.1.0
	 */
	const BLOCKED_UNTIL = 'mpwd_blocked_until';

	/**
	 * @since 1.1.0
	 */
	const FAILED_LOGIN_ATTEMPT_COUNT = 'mpwd_failed_login_attempt_count';

	const MAXIMUM_LOGIN_LENGTH = 16;

	/**
	 * @var WP_User|null
	 */
	private $wp_user;

	/**
	 * @return bool
	 */
	public function is_wp_user_set() {
		return $this->wp_user instanceof WP_User;
	}

	/**
	 * @return int
	 *
	 * @throws User_Not_Set_Exception
	 */
	public function get_id() {
		return $this->get_wp_user()->ID;
	}

	/**
	 * @return WP_User
	 *
	 * @throws User_Not_Set_Exception
	 */
	public function get_wp_user() {
		if ( ! $this->is_wp_user_set() ) {
			throw new User_Not_Set_Exception('User has not been set.');
		}

		return $this->wp_user;
	}

	/**
	 * @param WP_User $user
	 */
	public function set_wp_user( WP_User $user ) {
		$this->wp_user = $user;
	}

	public function reset_wp_user() {
		$this->wp_user = null;
	}

	/**
	 * @param string $capability
	 *
	 * @return bool
	 *
	 * @throws User_Not_Set_Exception
	 */
	public function has_capability( $capability ) {
		return user_can( $this->get_id(), $capability );
	}

	/**
	 * @return bool
	 */
	public function is_admin() {
		return $this->has_capability( Capabilities::ADMIN );
	}

	/**
	 * @return string
	 *
	 * @throws User_Not_Set_Exception
	 */
	public function get_shortened_login() {
		$wp_user = $this->get_wp_user();
		$login   = $wp_user->user_login;
		$login   = substr( $login, 0, self::MAXIMUM_LOGIN_LENGTH );

		return rawurlencode( $login );
	}

	/**
	 * @return array
	 *
	 * @throws User_Not_Set_Exception
	 */
	public function get_roles() {
		$wp_user = $this->get_wp_user();

		return $wp_user->roles;
	}

	/**
	 * @return bool
	 */
	public function is_blocked() {
		$blocked_until = $this->get( self::BLOCKED_UNTIL );

		return intval( $blocked_until ) > time();
	}

	/**
	 * @param int $blocked_until
	 */
	public function block( $blocked_until ) {
		$this->set( self::BLOCKED_UNTIL, $blocked_until );
	}

	public function unblock() {
		$this->delete( self::BLOCKED_UNTIL );
	}

	/**
	 * @return int
	 */
	public function get_failed_login_attempt_count() {
		$count = $this->get( self::FAILED_LOGIN_ATTEMPT_COUNT );

		return intval( $count );
	}

	/**
	 * @param int $count
	 */
	public function set_failed_login_attempt_count( $count ) {
		$this->set( self::FAILED_LOGIN_ATTEMPT_COUNT, $count );
	}

	public function delete_failed_login_attempt_count() {
		$this->delete( self::FAILED_LOGIN_ATTEMPT_COUNT );
	}

	/**
	 * @param string $key
	 *
	 * @return array|string
	 *
	 * @throws User_Not_Set_Exception
	 */
	public function get( $key ) {
		return get_user_meta( $this->get_id(), $key, true );
	}

	/**
	 * @param string           $key
	 * @param array|int|string $value
	 *
	 * @throws User_Not_Set_Exception
	 */
	public function set( $key, $value ) {
		update_user_meta( $this->get_id(), $key, $value );
	}

	/**
	 * @param string $key
	 *
	 * @throws User_Not_Set_Exception
	 */
	public function delete( $key ) {
		delete_user_meta( $this->get_id(), $key );
	}
}
