<?php

namespace TwoFAS\MagicPassword\Http\Controllers;

use TwoFAS\Core\Http\JSON_Response;
use TwoFAS\Core\Http\View_Response;
use TwoFAS\MagicPassword\Exceptions\Validation_Exception;
use TwoFAS\MagicPassword\Http\Request;
use TwoFAS\MagicPassword\Storage\Account_Storage;
use UnexpectedValueException;
use WP_Roles;

class Settings_Controller extends Controller {

	/**
	 * @var Account_Storage
	 */
	private $account_storage;

	/**
	 * @param Account_Storage $account_storage
	 */
	public function __construct( Account_Storage $account_storage ) {
		$this->account_storage = $account_storage;
	}

	/**
	 * @param Request $request
	 *
	 * @return View_Response
	 *
	 * @throws UnexpectedValueException
	 */
	public function show_settings_page( Request $request ) {
		return $this->view( 'dashboard/settings/settings.html.twig', array(
			'roles'              => $this->get_role_settings(),
			'is_logging_allowed' => $this->account_storage->is_logging_allowed(),
			'is_plugin_enabled'  => $this->account_storage->is_plugin_enabled(),
		) );
	}

	/**
	 * @param Request $request
	 *
	 * @return JSON_Response
	 */
	public function enable_plugin( Request $request ) {
		$this->account_storage->enable_plugin();

		return $this->json( array( 'message' => 'Plugin has been enabled.' ) );
	}

	/**
	 * @param Request $request
	 *
	 * @return JSON_Response
	 */
	public function disable_plugin( Request $request ) {
		$this->account_storage->disable_plugin();

		return $this->json( array( 'message' => 'Plugin has been disabled.' ) );
	}

	/**
	 * @param Request $request
	 *
	 * @return JSON_Response
	 */
	public function save_logging( Request $request ) {
		$logging = $request->post( 'mf-logging' );

		if ( 'yes' === $logging ) {
			$this->account_storage->enable_logging();
			$message = 'Error logging has been enabled.';
		} else {
			$this->account_storage->disable_logging();
			$message = 'Error logging has been disabled.';
		}

		return $this->json( array( 'message' => $message ) );
	}

	/**
	 * @param Request $request
	 *
	 * @return JSON_Response
	 */
	public function close_review_notice( Request $request ) {
		$review_notice_data           = $this->account_storage->get_review_notice_data();
		$review_notice_data['closed'] = true;
		$this->account_storage->set_review_notice_data( $review_notice_data );

		return $this->json( array() );
	}

	/**
	 * @return array
	 *
	 * @throws UnexpectedValueException
	 */
	private function get_role_settings() {
		$wp_roles           = $this->get_wp_roles();
		$passwordless_roles = $this->account_storage->get_passwordless_roles();
		$roles              = array();

		foreach ( $wp_roles as $role_key => $role_name ) {
			$roles[] = array(
				'key'        => $role_key,
				'name'       => $role_name,
				'obligatory' => in_array( $role_key, $passwordless_roles, true ),
			);
		}

		return $roles;
	}

	/**
	 * @return array
	 */
	private function get_wp_roles() {
		$wp_roles = new WP_Roles();

		return $wp_roles->role_names;
	}

	/**
	 * @param array $roles
	 *
	 * @return bool
	 */
	private function validate_roles( array $roles ) {
		$wp_roles = array_keys( $this->get_wp_roles() );
		$diff     = array_diff( $roles, $wp_roles );

		return empty( $diff );
	}
}
