<?php

namespace TwoFAS\MagicPassword\Core;

use Exception;
use TwoFAS\Core\Factories\Response_Factory;
use TwoFAS\Core\Hooks\Hook_Handler;
use TwoFAS\Core\Http\JSON_Response;
use TwoFAS\Core\Http\Redirect_Response;
use TwoFAS\Core\Http\Request;
use TwoFAS\Core\Http\View_Response;
use TwoFAS\MagicPassword\Notifications\Plugin_Notifier;
use TwoFAS\MagicPassword\Services\Account_Creator;
use TwoFAS\MagicPassword\Update\Updater;

class Plugin {

	/**
	 * @var Response_Factory
	 */
	private $response_factory;

	/**
	 * @var Request
	 */
	private $request;

	/**
	 * @var Account_Creator
	 */
	private $account;

	/**
	 * @var Updater
	 */
	private $updater;

	/**
	 * @var Hook_Handler
	 */
	private $hook_handler;

	/**
	 * @var Plugin_Notifier
	 */
	private $notifier;

	/**
	 * @param Response_Factory $response_factory
	 * @param Request          $request
	 * @param Account_Creator  $account
	 * @param Updater          $updater
	 * @param Hook_Handler     $hook_handler
	 * @param Plugin_Notifier  $notifier
	 */
	public function __construct(
		Response_Factory $response_factory,
		Request $request,
		Account_Creator $account,
		Updater $updater,
		Hook_Handler $hook_handler,
		Plugin_Notifier $notifier
	) {
		$this->response_factory = $response_factory;
		$this->request          = $request;
		$this->account          = $account;
		$this->updater          = $updater;
		$this->hook_handler     = $hook_handler;
		$this->notifier         = $notifier;
	}

	public function start() {
		try {
			if ( $this->account->should_account_be_created() ) {
				if ( $this->account->can_update_account() ) {
					$this->account->update_account();
				} else {
					$this->account->create_account();
				}
			}

			if ( $this->updater->should_plugin_be_updated() ) {
				$this->updater->update();
			}

			$this->notifier->show();

			$response = $this->response_factory->create_response( $this->request );
		} catch ( Exception $e ) {
			$response = $this->response_factory->create_error_response( $e );
		}

		if ( $response instanceof JSON_Response ) {
			$response->send_json();
		}

		if ( $response instanceof Redirect_Response ) {
			$response->redirect();
		}

		if ( $response instanceof View_Response ) {
			$this->hook_handler->register_hooks( $response );
		}
	}
}
