<?php

namespace TwoFAS\MagicPassword\Hooks;

use Exception;
use TwoFAS\MagicPassword\Exceptions\Handler\Error_Handler;
use TwoFAS\MagicPassword\Helpers\Flash;
use TwoFAS\MagicPassword\Integration\API_Wrapper;
use TwoFAS\MagicPassword\Integration\Integration_Name;
use TwoFAS\MagicPassword\Storage\Account_Storage;

class Update_Option_Blog_Name_Action extends Hook {

	/**
	 * @var API_Wrapper
	 */
	private $api_wrapper;

	/**
	 * @var Flash
	 */
	private $flash;

	/**
	 * @var Integration_Name
	 */
	private $integration_name;

	/**
	 * @var Account_Storage
	 */
	private $account_storage;

	/**
	 * @param Error_Handler    $error_handler
	 * @param API_Wrapper      $api_wrapper
	 * @param Flash            $flash
	 * @param Integration_Name $integration_name
	 * @param Account_Storage  $account_storage
	 */
	public function __construct(
		Error_Handler $error_handler,
		API_Wrapper $api_wrapper,
		Flash $flash,
		Integration_Name $integration_name,
		Account_Storage $account_storage
	) {
		parent::__construct( $error_handler );

		$this->api_wrapper      = $api_wrapper;
		$this->flash            = $flash;
		$this->integration_name = $integration_name;
		$this->account_storage  = $account_storage;
	}

	public function register_hook() {
		if ( $this->account_storage->is_account_created() ) {
			add_action( 'update_option_blogname', array( $this, 'update_integration_name' ), 10, 2 );
		}
	}

	/**
	 * @param string $old_name
	 * @param string $new_name
	 */
	public function update_integration_name( $old_name, $new_name ) {
		try {
			$integration = $this->api_wrapper->get_integration();

			if ( is_null( $integration ) ) {
				return;
			}

			$integration->setName( $this->integration_name->create( $new_name ) );
			$this->api_wrapper->update_integration( $integration );
		} catch ( Exception $e ) {
			$this->capture_exception( $e );
			$this->flash->add_message( 'error', 'Magic Password could not update integration name.' );
		}
	}
}
