<?php

namespace TwoFAS\MagicPassword\Http;

class Nonce {

	/**
	 * @var string
	 */
	private $token;

	/**
	 * @param string $token
	 */
	public function __construct( $token ) {
		$this->token = $token;
	}

	/**
	 * @param string $action
	 *
	 * @return bool
	 */
	public function validate( $action ) {
		return 1 === wp_verify_nonce( $this->token, $action );
	}
}
