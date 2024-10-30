<?php

namespace TwoFAS\MagicPassword\Hooks;

use TwoFAS\Account\TwoFAS as Account;
use TwoFAS\Api\TwoFAS as API;
use TwoFAS\Core\Hooks\Hook_Interface;
use TwoFAS\MagicPassword\Helpers\Config;
use TwoFAS\MagicPassword\Helpers\Twig;
use TwoFAS\MagicPassword\Http\Action_Index;
use TwoFAS\MagicPassword\Storage\Account_Storage;

abstract class Enqueue_Scripts_Action implements Hook_Interface {

	/**
	 * @var Config
	 */
	protected $config;

	/**
	 * @var Account_Storage
	 */
	protected $account_storage;

	/**
	 * @var Twig
	 */
	protected $twig;

	/**
	 * @param Config          $config
	 * @param Account_Storage $account_storage
	 * @param Twig            $twig
	 */
	public function __construct( Config $config, Account_Storage $account_storage, Twig $twig ) {
		$this->config          = $config;
		$this->account_storage = $account_storage;
		$this->twig            = $twig;
	}

	/**
	 * @return string
	 */
	protected function get_base_url() {
		return admin_url( 'admin.php?' . Action_Index::PAGE . '=' );
	}

	protected function enqueue_common() {
		$this->enqueue_sentry();
		wp_enqueue_style( 'magic-password', MPWD_ASSETS_URL . 'css/magic-password.min.css', array(), MPWD_PLUGIN_VERSION );
		wp_enqueue_script( 'mpwd-modals', MPWD_ASSETS_URL . 'js/modals.min.js', array( 'jquery' ), MPWD_PLUGIN_VERSION, true );
		wp_enqueue_script( 'mpwd-toaster', MPWD_ASSETS_URL . 'js/toaster.min.js', array( 'jquery' ), MPWD_PLUGIN_VERSION, true );
		$this->enqueue_deactivation_script();
		$this->enqueue_pusher();
	}

	private function enqueue_sentry() {
		wp_enqueue_script( 'sentry', 'https://browser.sentry-cdn.com/5.4.3/bundle.min.js', array( 'jquery' ), '5.4.3', true );
		wp_enqueue_script( 'mpwd-sentry', MPWD_ASSETS_URL . 'js/sentry.min.js', array( 'sentry' ), MPWD_PLUGIN_VERSION, true );

		$data = array(
			'sentryDsn'           => $this->config->get_sentry_dsn(),
			'loggingAllowed'      => $this->account_storage->is_logging_allowed(),
			'whitelistUrls'       => MPWD_ASSETS_URL . 'js',
			'release'             => MPWD_PLUGIN_VERSION,
			'wp_version'          => $this->account_storage->get_wp_version(),
			'api_sdk_version'     => API::VERSION,
			'account_sdk_version' => Account::VERSION,
			'loginPageUrl'        => wp_login_url(),
			'siteUrl'             => get_bloginfo( 'wpurl' ),
		);

		wp_localize_script( 'mpwd-sentry', 'mpwdSentry', $data );
	}

	private function enqueue_pusher() {
		wp_enqueue_script( 'pusher', 'https://js.pusher.com/4.4/pusher.min.js', array( 'jquery' ), '4.4.0', true );
		wp_enqueue_script( 'mpwd-show-modal-error', MPWD_ASSETS_URL . 'js/show-modal-error.min.js', array( 'jquery' ), MPWD_PLUGIN_VERSION, true );
		wp_enqueue_script( 'mpwd-pusher-events', MPWD_ASSETS_URL . 'js/pusher-events.min.js', array(
			'pusher',
			'jquery',
		), MPWD_PLUGIN_VERSION, true );

		$baseUrl = $this->get_base_url() . Action_Index::PAGE_CONFIGURATION . '&' . Action_Index::ACTION . '=';

		$data = array(
			'pusherKey'            => $this->config->get_pusher_key(),
			'authenticateEndpoint' => $baseUrl . 'authenticate-channel',
			'pairEndpoint'         => $baseUrl . 'pair',
		);

		wp_localize_script( 'mpwd-pusher-events', 'mpwdPusher', $data );
	}

	private function enqueue_deactivation_script() {
		wp_enqueue_script( 'mpwd-deactivation', MPWD_ASSETS_URL . 'js/deactivation-form.min.js', array( 'jquery' ), MPWD_PLUGIN_VERSION, true );

		$baseDeactivationUrl = $this->get_base_url() . Action_Index::PAGE_DEACTIVATION . '&' . Action_Index::ACTION . '=';

		$data = array(
			'deactivationForm' => $this->twig->render( 'modals/deactivation-form.html.twig' ),
			'deactivationUrl'  => $baseDeactivationUrl,
		);

		wp_localize_script( 'mpwd-deactivation', 'mpwdDeactivation', $data );
	}
}
