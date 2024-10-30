<?php

namespace TwoFAS\MagicPassword\Update\Migrations;

use TwoFAS\Account\Exception\Exception as Account_Exception;
use TwoFAS\Account\Exception\TokenRefreshException;
use TwoFAS\Account\OAuth\TokenNotFoundException;
use TwoFAS\Account\OAuth\TokenType;
use TwoFAS\MagicPassword\Exceptions\Migration_Exception;
use TwoFAS\MagicPassword\Update\Migration;

class Migration_2019_07_24_Refresh_OAuth_Setup_Token extends Migration {

	/**
	 * @return string
	 */
	public function introduced() {
		return '1.5.0';
	}

	/**
	 * @throws Migration_Exception
	 */
	public function up() {
		try {
			$account_storage = $this->storage->get_account_storage();
			$token           = $account_storage->retrieveToken( TokenType::SETUP );

			$this->api_wrapper->refresh_token( $token );
		} catch ( TokenRefreshException $e ) {
		} catch ( TokenNotFoundException $e ) {
			throw new Migration_Exception( 'OAuth token has not been found.' );
		} catch ( Account_Exception $e ) {
			throw new Migration_Exception( 'Something went wrong.' );
		}
	}

	public function down() {
	}
}
