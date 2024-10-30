<?php

namespace TwoFAS\MagicPassword\Hooks;

class Enqueue_Login_Scripts_Action extends Enqueue_Scripts_Action {

	public function register_hook() {
		if ( $this->account_storage->is_account_created() && $this->account_storage->is_plugin_enabled() ) {
			add_action( 'login_enqueue_scripts', array( $this, 'enqueue_login' ) );
		}
	}

	public function enqueue_login() {
		$this->enqueue_common();
		wp_enqueue_script( 'mpwd-mobile-detect', MPWD_ASSETS_URL . 'js/mobile-detect.min.js', array( 'jquery' ), MPWD_PLUGIN_VERSION, true );
		wp_enqueue_script( 'mpwd-device-type', MPWD_ASSETS_URL . 'js/device-type.min.js', array( 'jquery' ), MPWD_PLUGIN_VERSION, true );
		wp_enqueue_script( 'mpwd-login', MPWD_ASSETS_URL . 'js/login.min.js', array( 'jquery' ), MPWD_PLUGIN_VERSION, true );
	}
}
