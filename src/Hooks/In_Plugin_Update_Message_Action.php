<?php

namespace TwoFAS\MagicPassword\Hooks;

use TwoFAS\Core\Exceptions\Download_Exception;
use TwoFAS\Core\Exceptions\Parse_Exception;
use TwoFAS\Core\Readme\Upgrade_Notice;
use TwoFAS\MagicPassword\Exceptions\Handler\Error_Handler;
use TwoFAS\MagicPassword\Helpers\Twig;

class In_Plugin_Update_Message_Action extends Hook {

	/**
	 * @var Upgrade_Notice
	 */
	private $upgrade_notice;

	/**
	 * @var Twig
	 */
	private $twig;

	/**
	 * @param Error_Handler  $error_handler
	 * @param Upgrade_Notice $upgrade_notice
	 * @param Twig           $twig
	 */
	public function __construct( Error_Handler $error_handler, Upgrade_Notice $upgrade_notice, Twig $twig ) {
		parent::__construct( $error_handler );

		$this->upgrade_notice = $upgrade_notice;
		$this->twig           = $twig;
	}

	public function register_hook() {
		add_action( 'in_plugin_update_message-' . MPWD_PLUGIN_BASENAME, array( $this, 'show_upgrade_notice' ) );
	}

	public function show_upgrade_notice() {
		try {
			$paragraphs = $this->get_paragraphs();

			echo $this->twig->render( 'dashboard/upgrade-notice.html.twig', array( 'paragraphs' => $paragraphs ) );
		} catch ( Download_Exception $e ) {
			echo $this->capture_exception( $e )->to_notification( $e, 'mf-upgrade-notice' );
		} catch ( Parse_Exception $e ) {
			echo $this->capture_exception( $e )->to_notification( $e, 'mf-upgrade-notice' );
		}
	}

	/**
	 * @return array
	 *
	 * @throws Download_Exception
	 * @throws Parse_Exception
	 */
	private function get_paragraphs() {
		return $this->upgrade_notice->get_paragraphs( MPWD_PLUGIN_VERSION );
	}
}
