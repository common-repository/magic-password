<?php

namespace TwoFAS\MagicPassword\Update\Migrations;

use TwoFAS\MagicPassword\Storage\Account_Storage;
use TwoFAS\MagicPassword\Update\Migration;

class Migration_2018_02_26_Add_Review_Notice_Data extends Migration {

	/**
	 * @return string
	 */
	public function introduced() {
		return '1.3.3';
	}

	/**
	 * @param string $version
	 *
	 * @return bool
	 */
	public function supports( $version ) {
		return true;
	}

	public function up() {
		add_option( Account_Storage::REVIEW_NOTICE, array(
			'created_at' => time(),
			'closed'     => false
		) );
	}

	public function down() {
		delete_option( Account_Storage::REVIEW_NOTICE );
	}
}
