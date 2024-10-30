<?php

namespace TwoFAS\MagicPassword\Hooks;

use TwoFAS\Core\Hooks\Hook_Interface;
use TwoFAS\MagicPassword\Helpers\Twig;

class Admin_Notices_Action implements Hook_Interface {

	/**
	 * @var Twig
	 */
	private $twig;

	/**
	 * @param Twig $twig
	 */
	public function __construct( Twig $twig ) {
		$this->twig = $twig;
	}

	public function register_hook() {
		add_action( 'admin_notices', array( $this, 'render_notices' ), 20 );
	}

	public function render_notices() {
		echo $this->twig->render( 'dashboard/notices.html.twig' );
	}
}
