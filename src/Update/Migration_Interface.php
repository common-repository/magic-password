<?php

namespace TwoFAS\MagicPassword\Update;

use TwoFAS\MagicPassword\Exceptions\Migration_Exception;

interface Migration_Interface {

	/**
	 * @return string
	 */
	public function introduced();

	/**
	 * @param string $version
	 *
	 * @return bool
	 */
	public function supports( $version );

	/**
	 * @throws Migration_Exception
	 */
	public function up();

	/**
	 * @throws Migration_Exception
	 */
	public function down();
}
