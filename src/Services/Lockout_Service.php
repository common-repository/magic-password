<?php

namespace TwoFAS\MagicPassword\Services;

use TwoFAS\MagicPassword\Storage\User_Storage;

class Lockout_Service {

	const DEFAULT_LOCKOUT_TIME_IN_MINUTES = 5;
	const ALLOWED_LOGIN_ATTEMPT_COUNT     = 5;

	/**
	 * @var User_Storage
	 */
	private $user_storage;

	/**
	 * @param User_Storage $user_storage
	 */
	public function __construct( User_Storage $user_storage ) {
		$this->user_storage = $user_storage;
	}

	public function register_failed_login_attempt() {
		$count = $this->increment_failed_login_attempt_count();

		if ( $count >= self::ALLOWED_LOGIN_ATTEMPT_COUNT ) {
			$this->block_user();
		}
	}

	public function reset() {
		$this->user_storage->delete_failed_login_attempt_count();
		$this->user_storage->unblock();
	}

	/**
	 * @return int
	 */
	private function increment_failed_login_attempt_count() {
		$count = $this->user_storage->get_failed_login_attempt_count();

		$this->user_storage->set_failed_login_attempt_count( ++$count );

		return $count;
	}

	private function block_user() {
		$this->reset();

		$blocked_until = time() + self::DEFAULT_LOCKOUT_TIME_IN_MINUTES * 60;

		$this->user_storage->block( $blocked_until );
	}
}
