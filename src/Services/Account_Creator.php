<?php

namespace TwoFAS\MagicPassword\Services;

use TwoFAS\Account\Exception\Exception as Account_Exception;
use TwoFAS\Account\Exception\NotFoundException;
use TwoFAS\Account\OAuth\TokenNotFoundException;
use TwoFAS\Encryption\Exceptions\RandomBytesGenerateException;
use TwoFAS\MagicPassword\Helpers\Email;
use TwoFAS\MagicPassword\Helpers\Hash;
use TwoFAS\MagicPassword\Http\Action_Index;
use TwoFAS\MagicPassword\Http\Request;
use TwoFAS\MagicPassword\Integration\API_Wrapper;
use TwoFAS\MagicPassword\Storage\Account_Storage;

class Account_Creator {

	/**
	 * @var Request
	 */
	private $request;

	/**
	 * @var Account_Storage
	 */
	private $account_storage;

	/**
	 * @var API_Wrapper
	 */
	private $api_wrapper;

	/**
	 * @param Request         $request
	 * @param Account_Storage $account_storage
	 * @param API_Wrapper     $api_wrapper
	 */
	public function __construct( Request $request, Account_Storage $account_storage, API_Wrapper $api_wrapper ) {
		$this->request         = $request;
		$this->account_storage = $account_storage;
		$this->api_wrapper     = $api_wrapper;
	}

	/**
	 * @return bool
	 */
	public function should_account_be_created() {
		$page = $this->request->page();

		return ! $this->account_storage->is_account_created()
			&& ( Action_Index::PAGE_CONFIGURATION === $page || Action_Index::PAGE_SETTINGS === $page )
			&& Action_Index::ACTION_DEFAULT === $this->request->action();
	}

	/**
	 * @return bool
	 */
	public function can_update_account() {
		if ( ! $this->account_storage->encryption_key_exists() ) {
			return false;
		}

		if ( ! $this->account_storage->oauth_token_exists() ) {
			return false;
		}

		if ( is_null( $this->account_storage->retrieve_key_token() ) ) {
			return false;
		}

		return true;
	}

	/**
	 * @throws RandomBytesGenerateException
	 * @throws Account_Exception
	 */
	public function create_account() {
		$this->api_wrapper->create_account( Email::generate(), Hash::generate() );
	}

	/**
	 * @throws Account_Exception
	 * @throws NotFoundException
	 * @throws TokenNotFoundException
	 */
	public function update_account() {
		if ( is_null( $this->account_storage->retrieve_integration_login() ) ) {
			$this->account_storage->store_integration_login( $this->api_wrapper->get_integration()->getLogin() );
		}

		if ( is_null( $this->account_storage->retrieve_email() ) ) {
			$this->account_storage->store_email( $this->api_wrapper->get_client()->getEmail() );
		}
	}
}
