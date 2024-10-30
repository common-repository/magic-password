<?php

namespace TwoFAS\MagicPassword\Http;

class Action_Index {

	const PAGE   = 'page';
	const ACTION = 'mpwd-action';

	const PAGE_CONFIGURATION                = 'mpwd-configuration';
	const PAGE_SETTINGS                     = 'mpwd-settings';
	const PAGE_DEACTIVATION                 = 'mpwd-deactivation';
	const ACTION_DEFAULT                    = '';
	const ACTION_PAIR                       = 'pair';
	const ACTION_UNPAIR                     = 'unpair';
	const ACTION_AUTHENTICATE_CHANNEL       = 'authenticate-channel';
	const ACTION_ENABLE_PASSWORDLESS_LOGIN  = 'enable-passwordless-login';
	const ACTION_DISABLE_PASSWORDLESS_LOGIN = 'disable-passwordless-login';
	const ACTION_SET_OBLIGATORINESS         = 'set-obligatoriness';
	const ACTION_SAVE_ROLES                 = 'save-roles';
	const ACTION_SAVE_LOGGING               = 'save-logging';
	const ACTION_CLOSE_REVIEW_NOTICE        = 'close-review-notice';
	const ACTION_ENABLE_PLUGIN              = 'enable-plugin';
	const ACTION_DISABLE_PLUGIN             = 'disable-plugin';
	const ACTION_SEND_DEACTIVATION_REASON   = 'send-deactivation-reason';
}
