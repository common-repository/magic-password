<?php

namespace TwoFAS\MagicPassword\Update\Migrations;

use TwoFAS\MagicPassword\Exceptions\Migration_Exception;
use TwoFAS\MagicPassword\Integration\User_Configuration;
use TwoFAS\MagicPassword\Update\Migration;

class Migration_2020_01_30_Turn_Off_Magic_Password_Obligatory extends Migration {

	/**
	 * @return string
	 */
	public function introduced() {
		return '2.0.0';
	}

	/**
	 * @throws Migration_Exception
	 */
	public function up() {
		$account_storage = $this->storage->get_account_storage();
		$users           = get_users();

		$account_storage->set_passwordless_roles( array() );

		foreach ( $users as $user ) {
			update_user_meta( $user->ID, User_Configuration::OBLIGATORINESS_STATUS_KEY, '0' );
		}
	}

	public function down() {
	}
}
