<?php

namespace TwoFAS\MagicPassword\Hooks;

use TwoFAS\Core\Hooks\Hook_Interface;
use TwoFAS\MagicPassword\Storage\Account_Storage;
use TwoFAS\MagicPassword\Storage\DB_Wrapper;
use UnexpectedValueException;

class Update_Option_User_Roles_Action implements Hook_Interface {

	/**
	 * @var DB_Wrapper
	 */
	private $db;

	/**
	 * @var Account_Storage
	 */
	private $account_storage;

	/**
	 * @param DB_Wrapper      $db
	 * @param Account_Storage $account_storage
	 */
	public function __construct( DB_Wrapper $db, Account_Storage $account_storage ) {
		$this->db              = $db;
		$this->account_storage = $account_storage;
	}

	public function register_hook() {
		if ( $this->account_storage->is_account_created() ) {
			$update_option_user_roles_hook_name = 'update_option_' . $this->db->get_prefix() . 'user_roles';
			add_action( $update_option_user_roles_hook_name, array( $this, 'update' ), 10, 2 );
		}
	}

	/**
	 * @param mixed $old_value
	 * @param mixed $new_value
	 */
	public function update( $old_value, $new_value ) {
		if ( ! is_array( $old_value ) || ! is_array( $new_value ) ) {
			return;
		}

		$wp_roles = array_keys( $new_value );

		try {
			$this->update_passwordless_roles( $wp_roles );
		} catch ( UnexpectedValueException $e ) {
			return;
		}
	}

	/**
	 * @param array $wp_roles
	 *
	 * @throws UnexpectedValueException
	 */
	private function update_passwordless_roles( array $wp_roles ) {
		$passwordless_roles = $this->account_storage->get_passwordless_roles();
		$outdated_roles     = $this->get_outdated_roles( $passwordless_roles, $wp_roles );

		if ( ! empty( $outdated_roles ) ) {
			$this->delete_outdated_roles( $passwordless_roles, $outdated_roles );
		}
	}

	/**
	 * @param array $passwordless_roles
	 * @param array $wp_roles
	 *
	 * @return array
	 */
	private function get_outdated_roles( array $passwordless_roles, array $wp_roles ) {
		return array_diff( $passwordless_roles, $wp_roles );
	}

	/**
	 * @param array $passwordless_roles
	 * @param array $outdated_roles
	 */
	private function delete_outdated_roles( array $passwordless_roles, array $outdated_roles ) {
		$valid_passwordless_roles = array_diff( $passwordless_roles, $outdated_roles );
		$this->account_storage->set_passwordless_roles( array_values( $valid_passwordless_roles ) );
	}
}
