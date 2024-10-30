<?php

namespace TwoFAS\MagicPassword\Integration;

use TwoFAS\Api\Exception\Exception as API_Exception;
use TwoFAS\MagicPassword\Exceptions\User_Not_Set_Exception;
use TwoFAS\MagicPassword\Storage\Account_Storage;
use TwoFAS\MagicPassword\Storage\User_Storage;
use UnexpectedValueException;

class User_Configuration {

	/**
	 * @since 1.0.0
	 */
	const PASSWORDLESS_LOGIN_STATUS_KEY = 'mpwd_enabled';

	/**
	 * @since 1.0.0
	 */
	const OBLIGATORINESS_STATUS_KEY = 'mpwd_only_auth_option';

	/**
	 * @var User_Storage
	 */
	private $user_storage;

	/**
	 * @var Account_Storage
	 */
	private $account_storage;

	/**
	 * @var API_Wrapper
	 */
	private $api_wrapper;

	/**
	 * @param User_Storage    $user_storage
	 * @param Account_Storage $account_storage
	 * @param API_Wrapper     $api_wrapper
	 */
	public function __construct( User_Storage $user_storage, Account_Storage $account_storage, API_Wrapper $api_wrapper ) {
		$this->user_storage    = $user_storage;
		$this->account_storage = $account_storage;
		$this->api_wrapper     = $api_wrapper;
	}

	/**
	 * @return bool
	 */
	public function is_user_set() {
		return $this->user_storage->is_wp_user_set();
	}

	/**
	 * @return int
	 *
	 * @throws User_Not_Set_Exception
	 */
	public function get_user_id() {
		return $this->user_storage->get_id();
	}

	/**
	 * @return bool
	 *
	 * @throws User_Not_Set_Exception
	 * @throws API_Exception
	 */
	public function is_passwordless_login_configured() {
		$integration_user = $this->api_wrapper->get_integration_user_by_external_id( $this->user_storage->get_id() );

		if ( is_null( $integration_user ) ) {
			return false;
		}

		return ! is_null( $integration_user->getTotpSecret() );
	}

	/**
	 * @return bool
	 */
	public function is_passwordless_login_enabled() {
		return '1' === $this->user_storage->get( self::PASSWORDLESS_LOGIN_STATUS_KEY );
	}

	/**
	 * @return bool
	 */
	public function is_passwordless_login_disabled() {
		return ! $this->is_passwordless_login_enabled();
	}

	/**
	 * @return bool
	 */
	public function is_passwordless_login_obligatory() {
		return '1' === $this->user_storage->get( self::OBLIGATORINESS_STATUS_KEY );
	}

	/**
	 * @return bool
	 */
	public function is_passwordless_login_optional() {
		return ! $this->is_passwordless_login_obligatory();
	}

	/**
	 * @return bool
	 *
	 * @throws User_Not_Set_Exception
	 * @throws UnexpectedValueException
	 */
	public function is_passwordless_role_assigned() {
		$user_roles         = $this->user_storage->get_roles();
		$passwordless_roles = $this->account_storage->get_passwordless_roles();
		$intersection       = array_intersect( $user_roles, $passwordless_roles );

		return ! empty( $intersection );
	}

	public function enable_passwordless_login() {
		$this->user_storage->set( self::PASSWORDLESS_LOGIN_STATUS_KEY, '1' );
	}

	public function disable_passwordless_login() {
		$this->user_storage->set( self::PASSWORDLESS_LOGIN_STATUS_KEY, '0' );
	}

	public function set_obligatory_passwordless_login() {
		$this->user_storage->set( self::OBLIGATORINESS_STATUS_KEY, '1' );
	}

	public function set_optional_passwordless_login() {
		$this->user_storage->set( self::OBLIGATORINESS_STATUS_KEY, '0' );
	}

	public function delete() {
		$this->user_storage->delete( self::PASSWORDLESS_LOGIN_STATUS_KEY );
		$this->user_storage->delete( self::OBLIGATORINESS_STATUS_KEY );
	}
}
