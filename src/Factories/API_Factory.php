<?php

namespace TwoFAS\MagicPassword\Factories;

use TwoFAS\Api\TwoFAS as API;

class API_Factory extends SDK_Factory {

	/**
	 * @return API
	 */
	public function create() {
		$login = $this->account_storage->retrieve_integration_login();
		$key   = $this->account_storage->retrieve_key_token();
		$api   = new API( $login, $key, $this->get_headers() );

		$api->setBaseUrl( $this->config->get_api_url() );

		return $api;
	}
}
