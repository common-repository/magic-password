<?php

namespace TwoFAS\MagicPassword\Helpers;

use TwoFAS\Encryption\Exceptions\RandomBytesGenerateException;
use TwoFAS\Encryption\Random\NonCryptographicalRandomIntGenerator;
use TwoFAS\Encryption\Random\RandomStringGenerator;

class Hash {

	/**
	 * @return string
	 *
	 * @throws RandomBytesGenerateException
	 */
	public static function generate() {
		$generator = new RandomStringGenerator( new NonCryptographicalRandomIntGenerator() );
		$str       = $generator->string( 23 );

		return sha1( $str->__toString() );
	}
}
