<?php

namespace TwoFAS\MagicPassword\Hooks;

use Exception;
use TwoFAS\Core\Hooks\Hook_Interface;
use TwoFAS\MagicPassword\Exceptions\Handler\Error_Handler;

abstract class Hook implements Hook_Interface {

	/**
	 * @var Error_Handler
	 */
	private $error_handler;

	/**
	 * @param Error_Handler $error_handler
	 */
	public function __construct( Error_Handler $error_handler ) {
		$this->error_handler = $error_handler;
	}

	/**
	 * @param Exception $e
	 *
	 * @return Error_Handler
	 */
	protected function capture_exception( Exception $e) {
		return $this->error_handler->capture_exception( $e );
	}
}
