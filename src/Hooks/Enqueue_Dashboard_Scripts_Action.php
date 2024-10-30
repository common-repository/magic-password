<?php

namespace TwoFAS\MagicPassword\Hooks;

use TwoFAS\Core\Environment_Interface;
use TwoFAS\Core\Http\Request;
use TwoFAS\Core\Update\Update_Lock;
use TwoFAS\MagicPassword\Helpers\Config;
use TwoFAS\MagicPassword\Helpers\Twig;
use TwoFAS\MagicPassword\Http\Action_Index;
use TwoFAS\MagicPassword\Storage\Account_Storage;

class Enqueue_Dashboard_Scripts_Action extends Enqueue_Scripts_Action {

	/**
	 * @var Request
	 */
	private $request;

	/**
	 * @var Environment_Interface
	 */
	private $environment;

	/**
	 * @var Update_Lock
	 */
	private $update_lock;

	/**
	 * @param Config                $config
	 * @param Account_Storage       $account_storage
	 * @param Twig                  $twig
	 * @param Request               $request
	 * @param Environment_Interface $environment
	 * @param Update_Lock           $update_lock
	 */
	public function __construct(
		Config $config,
		Account_Storage $account_storage,
		Twig $twig,
		Request $request,
		Environment_Interface $environment,
		Update_Lock $update_lock
	) {
		parent::__construct( $config, $account_storage, $twig );

		$this->request     = $request;
		$this->environment = $environment;
		$this->update_lock = $update_lock;
	}

	public function register_hook() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_dashboard' ) );
	}

	public function enqueue_dashboard() {
		wp_enqueue_style( 'poppins', 'https://fonts.googleapis.com/css?family=Poppins:300,400,500,700', array(), MPWD_PLUGIN_VERSION );
		$this->enqueue_common();
		wp_enqueue_script( 'mpwd-mobile-detect', MPWD_ASSETS_URL . 'js/mobile-detect.min.js', array( 'jquery' ), MPWD_PLUGIN_VERSION, true );
		wp_enqueue_script( 'mpwd-device-type', MPWD_ASSETS_URL . 'js/device-type.min.js', array( 'jquery' ), MPWD_PLUGIN_VERSION, true );
		wp_enqueue_script( 'mpwd-show-modal-error', MPWD_ASSETS_URL . 'js/show-modal-error.min.js', array( 'jquery' ), MPWD_PLUGIN_VERSION, true );
		wp_enqueue_script( 'mpwd-dashboard', MPWD_ASSETS_URL . 'js/dashboard.min.js', array( 'jquery' ), MPWD_PLUGIN_VERSION, true );

		$baseConfigurationUrl = $this->get_base_url() . Action_Index::PAGE_CONFIGURATION . '&' . Action_Index::ACTION . '=';
		$baseSettingsUrl      = $this->get_base_url() . Action_Index::PAGE_SETTINGS . '&' . Action_Index::ACTION . '=';

		$data = array(
			'baseConfigurationUrl' => $baseConfigurationUrl,
			'baseSettingsUrl'      => $baseSettingsUrl,
		);

		wp_localize_script( 'mpwd-dashboard', 'mpwdDashboardParams', $data );

		if ( $this->should_update_be_locked() ) {
			$this->enqueue_update_lock_scripts();
		}
	}

	private function should_update_be_locked() {
		return ( $this->request->is_plugins_page() || $this->request->is_plugin_search_page() )
			&& $this->update_lock->is_locked();
	}

	private function enqueue_update_lock_scripts() {
		$scripts = $this->get_scripts_for_current_wordpress_version();

		foreach ( $scripts as $handle => $script ) {
			wp_enqueue_script( 'mpwd-' . $handle, MPWD_ASSETS_URL . $script, array( 'jquery' ), MPWD_PLUGIN_VERSION, true );
		}
	}

	/**
	 * @return array
	 */
	public function get_scripts_for_current_wordpress_version() {
		$scripts = array(
			'4.5'                                              => array(
				'plugins'        => 'js/update-lock/plugins-42-to-44.min.js',
				'plugin-install' => 'js/update-lock/plugin-install-42-to-51.min.js',
			),
			'4.6'                                              => array(
				'plugins'        => 'js/update-lock/plugins-45.min.js',
				'plugin-install' => 'js/update-lock/plugin-install-42-to-51.min.js',
			),
			Update_Lock::WP_VERSION_WITH_NATIVE_UPDATE_LOCKING => array(
				'plugins'        => 'js/update-lock/plugins-46-to-51.min.js',
				'plugin-install' => 'js/update-lock/plugin-install-42-to-51.min.js',
				'ajax-search'    => 'js/update-lock/ajax-search.min.js',
			),
		);

		foreach ( $scripts as $version => $script ) {
			if ( version_compare( $this->environment->get_wordpress_version(), $version, '<' ) ) {
				return $script;
			}
		}

		return array();
	}
}
