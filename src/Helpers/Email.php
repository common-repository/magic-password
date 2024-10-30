<?php

namespace TwoFAS\MagicPassword\Helpers;

use TwoFAS\Encryption\Exceptions\RandomBytesGenerateException;
use TwoFAS\Encryption\Random\NonCryptographicalRandomIntGenerator;
use TwoFAS\Encryption\Random\RandomStringGenerator;

class Email {

	/**
	 * @return string
	 *
	 * @throws RandomBytesGenerateException
	 */
	public static function generate() {
		$generator = new RandomStringGenerator( new NonCryptographicalRandomIntGenerator() );
		$id        = $generator->alphaNum( 23 );

		return "autocreated+{$id}@magicpassword.io";
	}
}
