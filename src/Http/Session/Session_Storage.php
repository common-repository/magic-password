<?php

namespace TwoFAS\MagicPassword\Http\Session;

use TwoFAS\Encryption\Random\NonCryptographicalRandomIntGenerator;
use TwoFAS\Encryption\Random\RandomStringGenerator;

abstract class Session_Storage implements Session_Storage_Interface {

	/**
	 * @return string
	 */
	protected function generate_id() {
		$generator = new RandomStringGenerator( new NonCryptographicalRandomIntGenerator() );
		$str       = $generator->string( Session::SESSION_KEY_LENGTH );

		return $str->toBase64()->__toString();
	}
}
