<?php

namespace TwoFAS\MagicPassword\Http\Session;

use DateInterval;
use DateTime;

class In_Memory_Session_Storage extends Session_Storage {

	/**
	 * @var string
	 */
	private $session_id;

	/**
	 * @var array
	 */
	private $sessions = array();

	/**
	 * @var array
	 */
	private $variables = array();

	public function __construct() {
		$this->session_id = $this->generate_id();
	}

	/**
	 * @return string
	 */
	public function get_session_id() {
		return $this->session_id;
	}

	/**
	 * @return bool
	 */
	public function exists() {
		return array_key_exists( $this->session_id, $this->sessions );
	}

	public function refresh() {
		$this->sessions[ $this->session_id ]['expiry_date'] = $this->get_expiry_date();
	}

	/**
	 * @return array|null
	 */
	public function get_session() {
		return $this->sessions[ $this->session_id ];
	}

	public function add_session() {
		$expire = $this->get_expiry_date();

		$this->sessions[ $this->session_id ] = array(
			'id'          => $this->session_id,
			'expiry_date' => $expire,
		);
	}

	public function delete_session() {
		unset( $this->sessions[ $this->session_id ] );
		unset( $this->variables[ $this->session_id ] );
	}

	/**
	 * @param string $key
	 *
	 * @return bool
	 */
	public function variable_exists( $key ) {
		if ( ! array_key_exists( $this->session_id, $this->variables ) ) {
			return false;
		}

		if ( ! array_key_exists( $key, $this->variables[ $this->session_id ] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * @param string $key
	 *
	 * @return string|null
	 */
	public function get_variable( $key ) {
		if ( ! $this->variable_exists( $key ) ) {
			return null;
		}

		return $this->variables[ $this->session_id ][ $key ];
	}

	/**
	 * @param string $key
	 * @param string $value
	 */
	public function add_variable( $key, $value ) {
		$this->variables[ $this->session_id ] [ $key ] = $value;
	}

	/**
	 * @param string $key
	 * @param string $value
	 */
	public function update_variable( $key, $value ) {
		$this->add_variable( $key, $value );
	}

	/**
	 * @param string $key
	 */
	public function delete_variable( $key ) {
		unset( $this->variables[ $this->session_id ][ $key ] );
	}

	/**
	 * @return DateTime
	 */
	private function get_expiry_date() {
		$now = new DateTime();

		return $now->add( new DateInterval( 'PT' . Session::ONE_HOUR_IN_SECONDS . 'S' ) );
	}
}
