<?php

namespace TwoFAS\MagicPassword\Notifications;

use TwoFAS\Core\Update\Deprecation;
use TwoFAS\MagicPassword\Helpers\Flash;
use TwoFAS\MagicPassword\User\Capabilities;

class Plugin_Notifier {

	const ROLE_ADMIN     = 'is_current_user_admin';
	const PHP_DEPRECATED = 'is_php_deprecated';

	/**
	 * @var Flash
	 */
	private $flash;

	/**
	 * @var Deprecation
	 */
	private $deprecation;

	/**
	 * @var array
	 */
	private $requirements = array();

	/**
	 * @var array
	 */
	private $notifications = array();

	/**
	 * @param Flash       $flash
	 * @param Deprecation $deprecation
	 */
	public function __construct( Flash $flash, Deprecation $deprecation ) {
		$this->flash       = $flash;
		$this->deprecation = $deprecation;
	}

	public function show() {
		if ( ! is_user_logged_in() ) {
			return;
		}

		$this->set_requirements();
		$this->set_notifications();

		foreach ( $this->notifications as $notification ) {
			$this->flash->add_message_now( 'error', $notification );
		}
	}

	private function set_requirements() {
		$this->requirements = array(
			self::ROLE_ADMIN     => $this->is_current_user_admin(),
			self::PHP_DEPRECATED => $this->deprecation->is_php_deprecated(),
		);
	}

	private function set_notifications() {
		if ( $this->can_show_deprecated_php() ) {
			$this->notifications[] = "Starting from next minor version (1.6.0) the Magic Password plugin will not work with your version of PHP. <a href='https://wordpress.org/support/update-php/' target='_blank'>Click here to learn more about updating PHP</a>.";
		}

		if ( $this->is_current_user_admin() ) {
			$this->notifications[] = "Magic Password will be abandoned soon, read more information on our website <a href='https://magicpassword.io' target='_blank'>magicpassword.io</a>";
		}
	}

	/**
	 * @return bool
	 */
	private function can_show_deprecated_php() {
		return $this->requirements[ self::ROLE_ADMIN ]
			&& $this->requirements[ self::PHP_DEPRECATED ];
	}

	/**
	 * @return bool
	 */
	private function is_current_user_admin() {
		return current_user_can( Capabilities::ADMIN );
	}
}
