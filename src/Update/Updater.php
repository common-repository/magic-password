<?php

namespace TwoFAS\MagicPassword\Update;

use RuntimeException;
use TwoFAS\MagicPassword\Exceptions\DB_Exception;
use TwoFAS\MagicPassword\Exceptions\Migration_Exception;
use TwoFAS\MagicPassword\Http\Request;
use TwoFAS\MagicPassword\Storage\Account_Storage;
use UnexpectedValueException;

class Updater {

	/**
	 * @var Request
	 */
	private $request;

	/**
	 * @var Migrator
	 */
	private $migrator;

	/**
	 * @var Account_Storage
	 */
	private $account_storage;

	/**
	 * @param Request         $request
	 * @param Migrator        $migrator
	 * @param Account_Storage $account_storage
	 */
	public function __construct( Request $request, Migrator $migrator, Account_Storage $account_storage ) {
		$this->request         = $request;
		$this->migrator        = $migrator;
		$this->account_storage = $account_storage;
	}

	/**
	 * @return bool
	 */
	public function should_plugin_be_updated() {
		return ! $this->request->has( 'doing_wp_cron' );
	}

	/**
	 * @throws UnexpectedValueException
	 * @throws RuntimeException
	 * @throws Migration_Exception
	 * @throws DB_Exception
	 */
	public function update() {
		$db_version = $this->account_storage->get_db_version();

		$this->migrator->migrate( $db_version );
		$this->update_plugin_version( $db_version );
	}

	/**
	 * @param string $db_version
	 */
	private function update_plugin_version( $db_version ) {
		if ( version_compare( $db_version, MPWD_PLUGIN_VERSION, '<' ) ) {
			$this->account_storage->set_db_version( MPWD_PLUGIN_VERSION );
		}
	}
}
