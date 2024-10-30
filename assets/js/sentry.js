Sentry.init( {
	dsn: mpwdSentry.sentryDsn,
	release: mpwdSentry.release,
	whitelistUrls: [
		mpwdSentry.whitelistUrls
	],
	beforeSend: function( event ) {
		if ( false === Boolean( mpwdSentry.loggingAllowed ) ) {
			return null;
		}

		if ( mpwdSentry.loginPageUrl === event.request.url ) {
			event.request.url = '[Filtered: ' +  mpwdSentry.siteUrl + ']';
		}

		return event;
	}
} );

Sentry.configureScope( function( scope ) {
	scope.setTag( 'jquery_version', jQuery.fn.jquery );
	scope.setTag( 'wp_version', mpwdSentry.wp_version );
	scope.setTag( 'api_sdk_version', mpwdSentry.api_sdk_version );
	scope.setTag( 'account_sdk_version', mpwdSentry.account_sdk_version );
} );
