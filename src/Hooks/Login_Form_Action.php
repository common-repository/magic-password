<?php

namespace TwoFAS\MagicPassword\Hooks;

use Exception;
use TwoFAS\Account\OAuth\TokenNotFoundException;
use TwoFAS\Encryption\Exceptions\RandomBytesGenerateException;
use TwoFAS\MagicPassword\Codes\QR_Code_Generator;
use TwoFAS\MagicPassword\Exceptions\Handler\Error_Handler;
use TwoFAS\MagicPassword\Helpers\Twig;
use TwoFAS\MagicPassword\Services\Pusher_Session_Service;
use TwoFAS\MagicPassword\Storage\Account_Storage;

class Login_Form_Action extends Hook {

	/**
	 * @var Account_Storage
	 */
	private $account_storage;

	/**
	 * @var Twig
	 */
	private $twig;

	/**
	 * @var QR_Code_Generator
	 */
	private $qr;

	/**
	 * @var Pusher_Session_Service
	 */
	private $pusher_session_service;

	/**
	 * @param Error_Handler          $error_handler
	 * @param Account_Storage        $account_storage
	 * @param Twig                   $twig
	 * @param QR_Code_Generator      $qr
	 * @param Pusher_Session_Service $pusher_session_service
	 */
	public function __construct(
		Error_Handler $error_handler,
		Account_Storage $account_storage,
		Twig $twig,
		QR_Code_Generator $qr,
		Pusher_Session_Service $pusher_session_service
	) {
		parent::__construct( $error_handler );
		$this->account_storage    = $account_storage;
		$this->twig                   = $twig;
		$this->qr                     = $qr;
		$this->pusher_session_service = $pusher_session_service;
	}

	public function register_hook() {
		if ( $this->account_storage->is_account_created() && $this->account_storage->is_plugin_enabled() ) {
			add_action( 'login_form', array( $this, 'customize_login_page' ) );
		}
	}

	public function customize_login_page() {
		try {
			$this->render_login_template();
		} catch ( Exception $e ) {
			echo $this->capture_exception( $e )->to_notification( $e );
		}
	}

	/**
	 * @throws TokenNotFoundException
	 * @throws RandomBytesGenerateException
	 */
	private function render_login_template() {
		$integration_id = (string) $this->account_storage->retrieve_integration_id();
		$session_id     = $this->pusher_session_service->get_session_id();
		$qr_code        = $this->qr->generate_login_code( $integration_id, $session_id );

		echo $this->twig->render( 'login/primary.html.twig', array(
			'session_id'     => $session_id,
			'integration_id' => $integration_id,
			'qr_code'        => $qr_code,
		) );
	}
}
