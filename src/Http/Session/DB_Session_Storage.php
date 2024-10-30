<?php

namespace TwoFAS\MagicPassword\Http\Session;

use DateInterval;
use DateTime;
use TwoFAS\MagicPassword\Http\Cookie;
use TwoFAS\MagicPassword\Storage\DB_Wrapper;

class DB_Session_Storage extends Session_Storage {

	const TABLE_SESSIONS          = 'mpwd_sessions';
	const TABLE_SESSION_VARIABLES = 'mpwd_session_variables';

	/**
	 * @var string
	 */
	private $session_id;

	/**
	 * @var Cookie
	 */
	private $cookie;

	/**
	 * @var DB_Wrapper
	 */
	private $db;

	/**
	 * @var array
	 */
	private $cached_variables = array();

	/**
	 * @param Cookie     $cookie
	 * @param DB_Wrapper $db
	 */
	public function __construct( Cookie $cookie, DB_Wrapper $db ) {
		$this->cookie     = $cookie;
		$this->db         = $db;
		$this->session_id = $this->cookie->get_cookie( Session::SESSION_COOKIE_NAME );
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
		$table = $this->get_table_full_name( self::TABLE_SESSIONS );
		$sql   = $this->db->prepare(
			"SELECT COUNT(1) FROM {$table} WHERE id = %s",
			array( $this->get_hash( $this->session_id ) )
		);

		return (bool) $this->db->get_var( $sql );
	}

	public function refresh() {
		$table       = $this->get_table_full_name( self::TABLE_SESSIONS );
		$expiry_date = $this->get_expiry_date();

		$this->cookie->set_cookie(
			Session::SESSION_COOKIE_NAME,
			$this->session_id,
			Session::ONE_HOUR_IN_SECONDS,
			true
		);

		$this->db->update(
			$table,
			array(
				'expiry_date' => $expiry_date->getTimestamp(),
			),
			array(
				'id' => $this->get_hash( $this->session_id ),
			),
			array( '%s' ),
			array( '%s' )
		);
	}

	/**
	 * @return array|null
	 */
	public function get_session() {
		$table  = $this->get_table_full_name( self::TABLE_SESSIONS );
		$sql    = "SELECT id, expiry_date FROM {$table} WHERE id = %s";
		$sql    = $this->db->prepare( $sql, array( $this->get_hash( $this->session_id ) ) );
		$result = $this->db->get_row( $sql, ARRAY_A );

		if ( is_null( $result ) ) {
			return null;
		}

		return array(
			'id'          => $result['id'],
			'expiry_date' => new DateTime( '@' . intval( $result['expiry_date'] ) ),
		);
	}

	public function add_session() {
		$table            = $this->get_table_full_name( self::TABLE_SESSIONS );
		$this->session_id = $this->generate_id();
		$expiry_date      = $this->get_expiry_date();

		$this->cookie->set_cookie(
			Session::SESSION_COOKIE_NAME,
			$this->session_id,
			Session::ONE_HOUR_IN_SECONDS,
			true
		);

		$this->db->insert(
			$table,
			array(
				'id'          => $this->get_hash( $this->session_id ),
				'expiry_date' => $expiry_date->getTimestamp(),
			),
			array( '%s', '%d' )
		);
	}

	public function delete_session() {
		$table = $this->get_table_full_name( self::TABLE_SESSIONS );

		$this->db->delete(
			$table,
			array( 'id' => $this->get_hash( $this->session_id ) ),
			array( '%s' )
		);

		$this->cookie->delete_cookie( Session::SESSION_COOKIE_NAME );
	}

	/**
	 * @param string $key
	 *
	 * @return bool
	 */
	public function variable_exists( $key ) {
		if ( array_key_exists( $key, $this->cached_variables ) ) {
			return true;
		}

		$table = $this->get_table_full_name( self::TABLE_SESSION_VARIABLES );
		$sql   = $this->db->prepare(
			"SELECT COUNT(1) FROM {$table} WHERE session_id = %s AND session_key = %s",
			array( $this->get_hash( $this->session_id ), $key )
		);

		return (bool) $this->db->get_var( $sql );
	}

	/**
	 * @param string $key
	 *
	 * @return null|string
	 */
	public function get_variable( $key ) {
		if ( array_key_exists( $key, $this->cached_variables ) ) {
			return $this->cached_variables[ $key ];
		}

		$table = $this->get_table_full_name( self::TABLE_SESSION_VARIABLES );
		$sql   = $this->db->prepare(
			"SELECT session_value FROM {$table} WHERE session_id = %s AND session_key = %s",
			array( $this->get_hash( $this->session_id ), $key )
		);

		$variable = $this->db->get_var( $sql );

		if ( is_null( $variable ) ) {
			return null;
		}

		$this->cached_variables[ $key ] = $variable;

		return $variable;
	}

	/**
	 * @param string $key
	 * @param string $value
	 */
	public function add_variable( $key, $value ) {
		$table = $this->get_table_full_name( self::TABLE_SESSION_VARIABLES );

		$this->db->insert(
			$table,
			array(
				'session_id'    => $this->get_hash( $this->session_id ),
				'session_key'   => $key,
				'session_value' => $value,
			),
			array( '%s', '%s', '%s' )
		);

		$this->cached_variables[ $key ] = $value;
	}

	/**
	 * @param string $key
	 * @param string $value
	 */
	public function update_variable( $key, $value ) {
		$table = $this->get_table_full_name( self::TABLE_SESSION_VARIABLES );

		$this->db->update(
			$table,
			array(
				'session_value' => $value,
			),
			array(
				'session_id'  => $this->get_hash( $this->session_id ),
				'session_key' => $key,
			),
			array( '%s' ),
			array( '%s', '%s' )
		);

		$this->cached_variables[ $key ] = $value;
	}

	/**
	 * @param string $key
	 */
	public function delete_variable( $key ) {
		$table = $this->get_table_full_name( self::TABLE_SESSION_VARIABLES );

		$this->db->delete(
			$table,
			array(
				'session_id'  => $this->get_hash( $this->session_id ),
				'session_key' => $key,
			),
			array( '%s', '%s' )
		);

		unset( $this->cached_variables[ $key ] );
	}

	/**
	 * @param string $table_name
	 *
	 * @return string
	 */
	private function get_table_full_name( $table_name ) {
		return $this->db->get_prefix() . $table_name;
	}

	/**
	 * @return DateTime
	 */
	private function get_expiry_date() {
		$now = new DateTime();

		return $now->add( new DateInterval( 'PT' . Session::ONE_HOUR_IN_SECONDS . 'S' ) );
	}

	/**
	 * @param string $session_id
	 *
	 * @return string
	 */
	private function get_hash( $session_id ) {
		return md5( $session_id );
	}
}
