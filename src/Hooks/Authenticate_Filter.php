<?php

namespace TwoFAS\MagicPassword\Hooks;

use Exception;
use TwoFAS\Core\Http\JSON_Response;
use TwoFAS\Core\Http\View_Response;
use TwoFAS\MagicPassword\Authentication\Login_Process;
use TwoFAS\MagicPassword\Exceptions\Handler\Error_Handler;
use TwoFAS\MagicPassword\Exceptions\User_Not_Found_Exception;
use TwoFAS\MagicPassword\Helpers\Twig;
use TwoFAS\MagicPassword\Http\Session\Session;
use TwoFAS\MagicPassword\Storage\Account_Storage;
use TwoFAS\MagicPassword\Storage\User_Storage;
use WP_Error;
use WP_User;

class Authenticate_Filter extends Hook {

	/**
	 * @var Account_Storage
	 */
	private $account_storage;

	/**
	 * @var User_Storage
	 */
	private $user_storage;

	/**
	 * @var Session
	 */
	private $session;

	/**
	 * @var Login_Process
	 */
	private $login_process;

	/**
	 * @var Twig
	 */
	private $twig;

	/**
	 * @param Error_Handler   $error_handler
	 * @param Account_Storage $account_storage
	 * @param User_Storage    $user_storage
	 * @param Session         $session
	 * @param Login_Process   $login_process
	 * @param Twig            $twig
	 */
	public function __construct(
		Error_Handler $error_handler,
		Account_Storage $account_storage,
		User_Storage $user_storage,
		Session $session,
		Login_Process $login_process,
		Twig $twig
	) {
		parent::__construct( $error_handler );

		$this->account_storage = $account_storage;
		$this->user_storage    = $user_storage;
		$this->session         = $session;
		$this->login_process   = $login_process;
		$this->twig            = $twig;
	}

	public function register_hook() {
		if ( $this->account_storage->is_account_created() && $this->account_storage->is_plugin_enabled() ) {
			add_filter( 'authenticate', array( $this, 'authenticate' ), 99, 1 );
		}
	}

	/**
	 * @param null|WP_Error|WP_User $user
	 *
	 * @return null|WP_Error|WP_User
	 */
	public function authenticate( $user ) {
		if ( is_null( $user ) ) {
			return $user;
		}

		$this->set_wp_user( $user );

		$response = $this->login_process->authenticate( $user );

		if ( is_null( $response ) ) {
			return $user;
		}

		if ( $response instanceof JSON_Response ) {
			$status_code = $response->get_status_code();
			$body        = $response->get_body();

			if ( 200 === $status_code ) {
				return new WP_User( $body['user_id'] );
			}

			return new WP_Error( 'mpwd_error', $body['error'] );
		}

		if ( $response instanceof View_Response ) {
			try {
				$this->render( $response );
				exit;
			} catch ( Exception $e ) {
				return $this->capture_exception( $e )->to_wp_error( $e );
			}
		}
	}

	/**
	 * @param WP_Error|WP_User $user
	 */
	private function set_wp_user( $user ) {
		try {
			if ( $this->user_storage->is_wp_user_set() ) {
				$this->user_storage->reset_wp_user();
			}

			if ( $this->is_wp_user( $user ) ) {
				$this->user_storage->set_wp_user( $user );
				$this->session->set( 'user_id', $user->ID );
			}
		} catch ( User_Not_Found_Exception $e ) {
		}
	}

	/**
	 * @param WP_Error|WP_User $user
	 *
	 * @return bool
	 */
	private function is_wp_user( $user ) {
		return $user instanceof WP_User;
	}

	/**
	 * @param View_Response $response
	 */
	private function render( View_Response $response ) {
		echo $this->twig->render( $response->get_template(), $response->get_data() );
	}
}
