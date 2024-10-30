<?php

namespace TwoFAS\MagicPassword\Update;

use TwoFAS\MagicPassword\Integration\API_Wrapper;
use TwoFAS\MagicPassword\Storage\DB_Wrapper;
use TwoFAS\MagicPassword\Storage\Storage;

abstract class Migration implements Migration_Interface {

	/**
	 * @var DB_Wrapper
	 */
	protected $db;

	/**
	 * @var API_Wrapper
	 */
	protected $api_wrapper;

	/**
	 * @var Storage
	 */
	protected $storage;

	/**
	 * @var array
	 */
	protected $tables = array();

	/**
	 * @param DB_Wrapper  $db
	 * @param API_Wrapper $api_wrapper
	 * @param Storage     $storage
	 */
	public function __construct( DB_Wrapper $db, API_Wrapper $api_wrapper, Storage $storage ) {
		$this->db          = $db;
		$this->api_wrapper = $api_wrapper;
		$this->storage     = $storage;

		$this->set_table_full_names();
	}

	/**
	 * @param string $version
	 *
	 * @return bool
	 */
	public function supports( $version ) {
		return version_compare( $version, '0', '>' )
			&& version_compare( $version, $this->introduced(), '<' );
	}

	/**
	 * @param string $table_name
	 *
	 * @return string
	 */
	protected function get_table_full_name( $table_name ) {
		return str_replace( '{prefix}', $this->db->get_prefix(), $table_name );
	}

	protected function set_table_full_names() {
		foreach ( $this->tables as $table_key => $table_name ) {
			$this->tables[ $table_key ] = $this->get_table_full_name( $table_name );
		}
	}
}
