<?php

namespace TwoFAS\MagicPassword\Http\Session;

use DateTime;

class Session {

	const SESSION_COOKIE_NAME = 'mpwd_session_id';
	const SESSION_KEY_LENGTH  = 16;
	const ONE_HOUR_IN_SECONDS = 3600;

	/**
	 * @var string
	 */
	private $id;

	/**
	 * @var DateTime
	 */
	private $expiry_date;

	/**
	 * @var Session_Storage_Interface
	 */
	private $storage;

	/**
	 * @param Session_Storage_Interface $storage
	 */
	public function __construct( Session_Storage_Interface $storage ) {
		$this->storage = $storage;
		$this->start();
	}

	/**
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * @return DateTime
	 */
	public function get_expiry_date() {
		return $this->expiry_date;
	}

	/**
	 * @param string $key
	 *
	 * @return bool
	 */
	public function exists( $key ) {
		return $this->storage->variable_exists( $key );
	}

	/**
	 * @param string $key
	 *
	 * @return string|null
	 */
	public function get( $key ) {
		return $this->storage->get_variable( $key );
	}

	/**
	 * @param string $key
	 * @param string $value
	 */
	public function set( $key, $value ) {
		if ( $this->storage->variable_exists( $key ) ) {
			$this->storage->update_variable( $key, $value );
		} else {
			$this->storage->add_variable( $key, $value );
		}
	}

	/**
	 * @param string $key
	 */
	public function delete( $key ) {
		$this->storage->delete_variable( $key );
	}

	public function regenerate() {
		$this->destroy();
		$this->start();
	}

	public function destroy() {
		$this->storage->delete_session();
		$this->id          = null;
		$this->expiry_date = null;
	}

	private function start() {
		if ( $this->storage->exists() ) {
			$this->storage->refresh();
		} else {
			$this->storage->add_session();
		}

		$session           = $this->storage->get_session();
		$this->id          = $this->storage->get_session_id();
		$this->expiry_date = $session['expiry_date'];
	}
}
