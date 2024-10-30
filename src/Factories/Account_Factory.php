<?php

namespace TwoFAS\MagicPassword\Factories;

use TwoFAS\Account\OAuth\TokenType;
use TwoFAS\Account\TwoFAS as Account;

class Account_Factory extends SDK_Factory {

	/**
	 * @return Account
	 */
	public function create() {
		$account = new Account( $this->account_storage, TokenType::passwordlessWordpress(), $this->get_headers() );

		$account->setBaseUrl( $this->config->get_account_url() );

		return $account;
	}
}
