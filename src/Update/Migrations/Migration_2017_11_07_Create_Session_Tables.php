<?php

namespace TwoFAS\MagicPassword\Update\Migrations;

use TwoFAS\MagicPassword\Exceptions\Migration_Exception;
use TwoFAS\MagicPassword\Update\Migration;

class Migration_2017_11_07_Create_Session_Tables extends Migration {

	const TABLE_SESSIONS          = 'sessions';
	const TABLE_SESSION_VARIABLES = 'session_variables';

	/**
	 * @var array
	 */
	protected $tables = array(
		self::TABLE_SESSIONS          => '{prefix}mpwd_sessions',
		self::TABLE_SESSION_VARIABLES => '{prefix}mpwd_session_variables',
	);

	/**
	 * @return string
	 */
	public function introduced() {
		return '1.2.0';
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

		$sql = "CREATE TABLE IF NOT EXISTS {$this->tables[ self::TABLE_SESSIONS ]} (
id varchar(32),
expiry_date bigint(20) NOT NULL,
PRIMARY KEY (id)
) ENGINE = INNODB {$charset_collate}";

		$result = $this->db->query( $sql );

		if ( false === $result ) {
			throw new Migration_Exception( $this->db->get_last_error() );
		}

		$sql = "CREATE TABLE IF NOT EXISTS {$this->tables[ self::TABLE_SESSION_VARIABLES ]} (
session_id varchar(32) NOT NULL,
session_key varchar(100) NOT NULL,
session_value text NOT NULL,
FOREIGN KEY (session_id) REFERENCES {$this->tables[ self::TABLE_SESSIONS ]}(id) ON DELETE CASCADE,
UNIQUE KEY session_key (session_id, session_key)
) ENGINE = INNODB {$charset_collate}";

		$result = $this->db->query( $sql );

		if ( false === $result ) {
			throw new Migration_Exception( $this->db->get_last_error() );
		}
	}

	/**
	 * @throws Migration_Exception
	 */
	public function down() {
		$result = $this->db->query( 'DROP TABLE IF EXISTS ' . $this->tables[ self::TABLE_SESSION_VARIABLES ] );

		if ( false === $result ) {
			throw new Migration_Exception( $this->db->get_last_error() );
		}

		$result = $this->db->query( 'DROP TABLE IF EXISTS ' . $this->tables[ self::TABLE_SESSIONS ] );

		if ( false === $result ) {
			throw new Migration_Exception( $this->db->get_last_error() );
		}
	}
}
