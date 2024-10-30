(
	function( $ ) {
		try {
			var loginWithMPButton = $( '.mf-login-button' ),
			    normalLogin       = $( '.mf-normal-login' ),
			    actionInput       = $( '.mf-action-name' );

			function isLoginCookieSet() {
				return -1 < document.cookie.search( 'mpwd_login=1' );
			}

			function setQRView() {
				$( '#login' ).addClass( 'mf-with-qr' );
			}

			function isLoginConfigurationView() {
				return actionInput.length && 'login-configuration' === actionInput.val();
			}

			function isPasswordlessLoginView() {
				return actionInput.length && 'passwordless-login' === actionInput.val();
			}

			function isSecondStepView() {
				return isLoginConfigurationView() || isPasswordlessLoginView();
			}

			normalLogin.click( function() {
				$('input[type=password]').prop( 'disabled', false );
				$( '#login' ).removeClass( 'mf-with-qr' );
				$( '#user_login' ).focus();
			} );

			loginWithMPButton.click( function() {
				setQRView();
			} );

			if ( isLoginCookieSet() || isSecondStepView() ) {
				setQRView();
			}

			if ( $( '.mf-js-step-2' ).length ) {
				$( '#backtoblog' ).hide();
			}
		} catch ( e ) {
			Sentry.captureException( e );
		}
	}
)( jQuery );
