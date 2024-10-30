<?php

namespace TwoFAS\MagicPassword\Factories;

use TwoFAS\MagicPassword\Http\Cookie;
use TwoFAS\MagicPassword\Http\Session\DB_Session_Storage;
use TwoFAS\MagicPassword\Http\Session\In_Memory_Session_Storage;
use TwoFAS\MagicPassword\Storage\DB_Wrapper;

class Session_Storage_Factory {

	/**
	 * @var DB_Wrapper
	 */
	private $db;

	/**
	 * @var Cookie
	 */
	private $cookie;

	/**
	 * @var array
	 */
	private $get;

	/**
	 * @param DB_Wrapper $db
	 * @param Cookie     $cookie
	 * @param array      $get
	 */
	public function __construct( DB_Wrapper $db, Cookie $cookie, array $get ) {
		$this->db     = $db;
		$this->cookie = $cookie;
		$this->get    = $get;
	}

	/**
	 * @return DB_Session_Storage|In_Memory_Session_Storage
	 */
	public function create() {
		if ( $this->can_use_db_session_storage() ) {
			return new DB_Session_Storage( $this->cookie, $this->db );
		}

		return new In_Memory_Session_Storage();
	}

	/**
	 * @return bool
	 */
	private function can_use_db_session_storage() {
		$tables_exist = ! is_null( $this->db->get_var( "SHOW TABLES LIKE '" . $this->db->get_prefix() . DB_Session_Storage::TABLE_SESSIONS . "'" ) );

		return $tables_exist && ! $this->is_cron_working();
	}

	/**
	 * @return bool
	 */
	private function is_cron_working() {
		return array_key_exists( 'doing_wp_cron', $this->get );
	}
}
