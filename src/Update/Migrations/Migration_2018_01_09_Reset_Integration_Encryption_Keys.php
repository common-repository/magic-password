<?php

namespace TwoFAS\MagicPassword\Update\Migrations;

use TwoFAS\Account\Exception\Exception as Account_Exception;
use TwoFAS\Account\Exception\NotFoundException;
use TwoFAS\Account\Exception\ValidationException;
use TwoFAS\Account\OAuth\TokenNotFoundException;
use TwoFAS\MagicPassword\Exceptions\Migration_Exception;
use TwoFAS\MagicPassword\Update\Migration;

class Migration_2018_01_09_Reset_Integration_Encryption_Keys extends Migration {

	/**
	 * @return string
	 */
	public function introduced() {
		return '1.3.0';
	}

	/**
	 * @throws Migration_Exception
	 */
	public function up() {
		try {
			$this->api_wrapper->reset_integration_encryption_keys();
		} catch ( TokenNotFoundException $e ) {
			throw new Migration_Exception( 'OAuth token has not been found.' );
		} catch ( NotFoundException $e ) {
			throw new Migration_Exception( 'Integration has not been found.' );
		} catch ( ValidationException $e ) {
			throw new Migration_Exception( 'Validation error occurred.' );
		} catch ( Account_Exception $e ) {
			throw new Migration_Exception( 'Something went wrong.' );
		}
	}

	public function down() {

	}
}
