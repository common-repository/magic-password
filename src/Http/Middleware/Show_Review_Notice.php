<?php

namespace TwoFAS\MagicPassword\Http\Middleware;

use DateTime;
use Exception;
use TwoFAS\Core\Http\JSON_Response;
use TwoFAS\Core\Http\Middleware\Middleware;
use TwoFAS\Core\Http\Redirect_Response;
use TwoFAS\Core\Http\View_Response;
use TwoFAS\MagicPassword\Exceptions\Date_Time_Exception;
use TwoFAS\MagicPassword\Hooks\Review_Notice_Action;
use TwoFAS\MagicPassword\Storage\Account_Storage;
use TwoFAS\MagicPassword\Storage\User_Storage;

class Show_Review_Notice extends Middleware {

	const WAITING_TIME_IN_DAYS = 14;

	/**
	 * @var Review_Notice_Action
	 */
	private $review_notice_action;

	/**
	 * @var Account_Storage
	 */
	private $account_storage;

	/**
	 * @var User_Storage
	 */
	private $user_storage;

	/**
	 * @param Review_Notice_Action $review_notice_action
	 * @param Account_Storage      $account_storage
	 * @param User_Storage         $user_storage
	 */
	public function __construct(
		Review_Notice_Action $review_notice_action,
		Account_Storage $account_storage,
		User_Storage $user_storage
	) {
		$this->review_notice_action = $review_notice_action;
		$this->account_storage      = $account_storage;
		$this->user_storage         = $user_storage;
	}

	/**
	 * @return View_Response|JSON_Response|Redirect_Response
	 *
	 * @throws Date_Time_Exception
	 */
	public function handle() {
		if ( $this->check() ) {
			$this->review_notice_action->register_hook();
		}

		return $this->next->handle();
	}

	/**
	 * @return bool
	 *
	 * @throws Date_Time_Exception
	 */
	private function check() {
		$data = $this->account_storage->get_review_notice_data();

		return $this->account_storage->is_plugin_enabled()
			&& $this->check_time( $data )
			&& ! $this->was_closed( $data )
			&& $this->user_storage->is_admin();
	}

	/**
	 * @param array $data
	 *
	 * @return bool
	 *
	 * @throws Date_Time_Exception
	 */
	private function check_time( array $data ) {
		if ( ! array_key_exists( 'created_at', $data ) ) {
			return false;
		}

		$now = new DateTime();

		try {
			$created_at = new DateTime( '@' . $data['created_at'] );
		} catch ( Exception $e ) {
			throw new Date_Time_Exception();
		}

		$diff = $now->diff( $created_at );

		return intval( $diff->days ) >= self::WAITING_TIME_IN_DAYS;
	}

	/**
	 * @param array $data
	 *
	 * @return bool
	 */
	private function was_closed( array $data ) {
		if ( ! array_key_exists( 'closed', $data ) ) {
			return false;
		}

		return false !== $data['closed'];
	}
}
