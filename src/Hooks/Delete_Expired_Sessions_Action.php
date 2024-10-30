<?php

namespace TwoFAS\MagicPassword\Hooks;

use DateTime;
use TwoFAS\MagicPassword\Exceptions\DB_Exception;
use TwoFAS\MagicPassword\Exceptions\Handler\Error_Handler;
use TwoFAS\MagicPassword\Http\Session\DB_Session_Storage;
use TwoFAS\MagicPassword\Storage\DB_Wrapper;

class Delete_Expired_Sessions_Action extends Hook {

	/**
	 * @var DB_Wrapper
	 */
	private $db;

	/**
	 * @param Error_Handler $error_handler
	 * @param DB_Wrapper    $db
	 */
	public function __construct( Error_Handler $error_handler, DB_Wrapper $db ) {
		parent::__construct( $error_handler );
		$this->db = $db;
	}

	public function register_hook() {
		$delete_expired_session_hook = 'mpwd_delete_expired_sessions';

		if ( ! wp_next_scheduled( $delete_expired_session_hook ) ) {
			wp_schedule_event( time(), 'daily', $delete_expired_session_hook );
		}

		add_action( $delete_expired_session_hook, array( $this, 'delete_expired_sessions' ) );
	}

	public function delete_expired_sessions() {
		try {
			$now   = new DateTime();
			$table = $this->get_table_full_name( DB_Session_Storage::TABLE_SESSIONS );
			$sql   = $this->db->prepare( "DELETE FROM {$table} WHERE expiry_date < %d", array( $now->getTimestamp() ) );

			if ( false === $this->db->query( $sql ) ) {
				throw new DB_Exception( $this->db->get_last_error() );
			}
		} catch ( DB_Exception $e ) {
			$this->capture_exception( $e );
		}
	}

	/**
	 * @param string $table_name
	 *
	 * @return string
	 */
	private function get_table_full_name( $table_name ) {
		return $this->db->get_prefix() . $table_name;
	}
}
