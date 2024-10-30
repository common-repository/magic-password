<?php

namespace TwoFAS\MagicPassword\Hooks;

use TwoFAS\Core\Hooks\Hook_Interface;
use TwoFAS\MagicPassword\Helpers\Twig;

class Review_Notice_Action implements Hook_Interface {

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
		add_action( 'admin_notices', array( $this, 'show_notice' ) );
	}

	public function show_notice() {
		echo $this->twig->render( 'dashboard/review-notice.html.twig' );
	}
}
