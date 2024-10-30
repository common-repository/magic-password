<?php

namespace TwoFAS\MagicPassword\Storage;

use TwoFAS\MagicPassword\Http\Session\Session_Storage;

class Storage {

	/**
	 * @var Account_Storage
	 */
	private $account_storage;

	/**
	 * @var User_Storage
	 */
	private $user_storage;

	/**
	 * @var Session_Storage
	 */
	private $session_storage;

	/**
	 * @param Account_Storage $account_storage
	 * @param User_Storage    $user_storage
	 * @param Session_Storage $session_storage
	 */
	public function __construct(
		Account_Storage $account_storage,
		User_Storage $user_storage,
		Session_Storage $session_storage
	) {
		$this->account_storage = $account_storage;
		$this->user_storage    = $user_storage;
		$this->session_storage = $session_storage;
	}

	/**
	 * @return Account_Storage
	 */
	public function get_account_storage() {
		return $this->account_storage;
	}

	/**
	 * @return User_Storage
	 */
	public function get_user_storage() {
		return $this->user_storage;
	}

	/**
	 * @return Session_Storage
	 */
	public function get_session_storage() {
		return $this->session_storage;
	}
}
