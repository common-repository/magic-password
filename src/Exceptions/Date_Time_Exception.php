<?php

namespace TwoFAS\MagicPassword\Exceptions;

use Exception;

class Date_Time_Exception extends Exception {

	/**
	 * @param string $message
	 */
	public function __construct( $message = 'Invalid date format' ) {
		parent::__construct( $message );
	}
}
