<?php

namespace TwoFAS\MagicPassword\Update;

use DirectoryIterator;
use RuntimeException;
use TwoFAS\MagicPassword\Exceptions\DB_Exception;
use TwoFAS\MagicPassword\Exceptions\Migration_Exception;
use TwoFAS\MagicPassword\Integration\API_Wrapper;
use TwoFAS\MagicPassword\Storage\DB_Wrapper;
use TwoFAS\MagicPassword\Storage\Storage;
use UnexpectedValueException;

class Migrator {

	const TABLE_MIGRATION = 'mpwd_migrations';

	/**
	 * @var DB_Wrapper
	 */
	private $db;

	/**
	 * @var API_Wrapper
	 */
	private $api_wrapper;

	/**
	 * @var Storage
	 */
	private $storage;

	/**
	 * @var string
	 */
	private $migrations_path;

	/**
	 * @param DB_Wrapper  $db
	 * @param API_Wrapper $api_wrapper
	 * @param Storage     $storage
	 */
	public function __construct( DB_Wrapper $db, API_Wrapper $api_wrapper, Storage $storage ) {
		$this->db              = $db;
		$this->api_wrapper     = $api_wrapper;
		$this->storage         = $storage;
		$this->migrations_path = __DIR__ . '/Migrations';
	}

	/**
	 * @param string $migrations_path
	 */
	public function set_migrations_path( $migrations_path ) {
		$this->migrations_path = $migrations_path;
	}

	/**
	 * @param string $db_version
	 *
	 * @throws UnexpectedValueException
	 * @throws RuntimeException
	 * @throws Migration_Exception
	 * @throws DB_Exception
	 */
	public function migrate( $db_version ) {
		$migrations = array_diff( $this->get_migrations(), $this->get_executed_migrations() );

		sort( $migrations );

		foreach ( $migrations as $name ) {
			$migration_name = $this->get_fully_qualified_name( $name );

			/** @var Migration_Interface $migration */
			$migration = new $migration_name( $this->db, $this->api_wrapper, $this->storage );

			if ( $migration->supports( $db_version ) ) {
				$migration->up();
			}

			$this->add_migration( $name );
		}
	}

	/**
	 * @throws Migration_Exception
	 */
	public function rollback_all() {
		$migrations = $this->get_executed_migrations();

		rsort( $migrations );

		foreach ( $migrations as $name ) {
			$migration_name = $this->get_fully_qualified_name( $name );

			/** @var Migration_Interface $migration */
			$migration = new $migration_name( $this->db, $this->api_wrapper, $this->storage );
			$migration->down();
		}
	}

	/**
	 * @return bool
	 */
	private function check_migration_table() {
		$table_exist = $this->db->get_var( "SHOW TABLES LIKE '" . $this->get_migration_table_name() . "' " );

		return ! is_null( $table_exist );
	}

	/**
	 * @return array
	 *
	 * @throws UnexpectedValueException
	 * @throws RuntimeException
	 */
	private function get_migrations() {
		$migrations = array();

		foreach ( new DirectoryIterator( $this->migrations_path ) as $migration ) {
			if ( $migration->isDot() ) {
				continue;
			}

			$filename = $migration->getFilename();

			if ( ! preg_match( '/^Migration_\d{4}_\d{2}_\d{2}(_[a-zA-Z]+)+\.php$/', $filename ) ) {
				continue;
			}

			$migrations[] = str_replace( '.php', '', $filename );
		}

		return $migrations;
	}

	/**
	 * @return array
	 */
	private function get_executed_migrations() {
		if ( ! $this->check_migration_table() ) {
			return array();
		}

		return $this->db->get_col( "SELECT migration FROM " . $this->get_migration_table_name() . " " );
	}

	/**
	 * @param string $migration_name
	 *
	 * @throws DB_Exception
	 */
	private function add_migration( $migration_name ) {
		$result = $this->db->insert( $this->get_migration_table_name(), array(
			'migration' => $migration_name,
		) );

		if ( $result === false ) {
			throw new DB_Exception( $this->db->get_last_error() );
		}
	}

	/**
	 * @param string $migration_name
	 *
	 * @return string
	 */
	private function get_fully_qualified_name( $migration_name ) {
		return 'TwoFAS\\MagicPassword\\Update\\Migrations\\' . $migration_name;
	}

	/**
	 * @return string
	 */
	private function get_migration_table_name() {
		return $this->db->get_prefix() . self::TABLE_MIGRATION;
	}
}
