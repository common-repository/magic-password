<?php

namespace TwoFAS\MagicPassword\Update\Migrations;

use TwoFAS\MagicPassword\Exceptions\Migration_Exception;
use TwoFAS\MagicPassword\Update\Migration;

class Migration_0000_00_00_Create_Migrations_Table extends Migration {

	const TABLE_MIGRATIONS = 'migrations';

	/**
	 * @var array
	 */
	protected $tables = array(
		self::TABLE_MIGRATIONS => '{prefix}mpwd_migrations',
	);

	/**
	 * @return string
	 */
	public function introduced() {
		return '1.1.1';
	}

	/**
	 * @param string $version
	 *
	 * @return bool
	 */
	public function supports( $version ) {
		return true;
	}

	/**
	 * @throws Migration_Exception
	 */
	public function up() {
		$charset_collate = $this->db->get_charset_collate();

		$query = "CREATE TABLE IF NOT EXISTS {$this->tables[ self::TABLE_MIGRATIONS ]} (
migration VARCHAR(100),
PRIMARY KEY (migration)
) ENGINE = INNODB {$charset_collate}";

		$result = $this->db->query( $query );

		if ( false === $result ) {
			throw new Migration_Exception( $this->db->get_last_error() );
		}
	}

	/**
	 * @throws Migration_Exception
	 */
	public function down() {
		$result = $this->db->query( 'DROP TABLE IF EXISTS ' . $this->tables[ self::TABLE_MIGRATIONS ] );

		if ( false === $result ) {
			throw new Migration_Exception( $this->db->get_last_error() );
		}
	}
}
