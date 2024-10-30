<?php

namespace TwoFAS\MagicPassword\Hooks;

use TwoFAS\Core\Hooks\Admin_Menu_Action as Base_Admin_Menu_Action;
use TwoFAS\MagicPassword\Helpers\Twig;
use TwoFAS\MagicPassword\User\Capabilities;

class Admin_Menu_Action extends Base_Admin_Menu_Action {

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

	public function add_menu() {
		add_menu_page(
			'Magic Password &#8212; Admin Configuration',
			'Magic Password',
			Capabilities::ADMIN,
			'mpwd-settings',
			array( $this, 'render' )
		);

		add_submenu_page(
			'mpwd-settings',
			'Magic Password &#8212; Admin Configuration',
			'Admin Configuration',
			Capabilities::ADMIN,
			'mpwd-settings',
			array( $this, 'render' )
		);

		add_submenu_page(
			'mpwd-settings',
			'Magic Password &#8212; User Configuration',
			'User Configuration',
			Capabilities::USER,
			'mpwd-configuration',
			array( $this, 'render' )
		);
	}

	public function render() {
		echo $this->twig->render( $this->response->get_template(), $this->response->get_data() );
	}
}
