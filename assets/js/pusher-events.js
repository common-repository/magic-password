(
	function( $, toaster, modal ) {
		try {
			var sessionIdInput         = $( '.mf-js-session-id' ),
			    integrationIdInput     = $( '.mf-js-integration-id' ),
			    integrationUserIdInput = $( '.mf-js-integration-user-id' ),
			    totpCodeInput          = $( '.mf-js-totp-code' ),
			    channelNameInput       = $( '.mf-js-channel-name' ),
			    statusIdInput          = $( '.mf-js-status-id' ),
			    loginForm              = $( '#loginform' ),
			    pairNonce              = $( '.mf-js-configuration-token > input[name="_wpnonce"]' ),
			    totpSecretInput        = $( '.mf-js-totp-secret' ),
			    successModal           = $( '.mf-success-modal' ),
			    channelSubscribed      = false,
			    waitingForCode         = true,
			    jsHandleFocus          = $( '.mf-js-handle-focus' ),
			    loginConfigQrCodeForm  = $( '.mf-js-login-config-qr-code-form' ),
			    actionInput            = $( '.mf-action-name' ),
			    spinnerWrapper         = $( '.mf-qr-code-wrapper' ),
			    showQrCodeButton       = $( '.mf-js-show-qr' ),
			    QRPlaceholderImage     = $( '.mf-qr-code-placeholder' ),
			    showingQrCode          = false;

			function enableSpinner() {
				spinnerWrapper.addClass( 'mf-loading' );
			}

			function disableSpinner() {
				spinnerWrapper.removeClass( 'mf-loading' );
			}

			function pair( pusher, totpToken, channelName, statusId ) {
				var pairData = {
					totp_secret: totpSecretInput.val(),
					totp_code: totpToken,
					channel_name: channelName,
					status_id: statusId,
					_wpnonce: pairNonce.val()
				};

				$.ajax( {
						type: 'POST',
						url: mpwdPusher.pairEndpoint,
						data: pairData
					}
				).done( function() {
						successModal.trigger( 'mf-modal-open' );
					}
				).fail( function( response ) {
						var error = JSON.parse( response.responseText ).error;

						Sentry.captureMessage( 'Pair error', {
							extra: {
								channel_name: channelName,
								error: error,
								status: response.status
							}
						} );

						modal.showErrorModal( error );
					}
				).always( function() {
					pusher.disconnect();
				} );
			}

			function handleLoginRequest( pusher, data, channelName ) {
				enableSpinner();
				$( document ).trigger( 'receivedCode' );

				channelNameInput.val( channelName );
				statusIdInput.val( data.statusId );
				integrationUserIdInput.val( data.integrationUserId );
				totpCodeInput.val( data.totpToken );
				actionInput.val( 'passwordless-login' );

				pusher.disconnect();

				loginForm.submit();
			}

			function handleConfigurationRequest( pusher, data, channelName ) {
				$( document ).trigger( 'receivedCode' );

				channelNameInput.val( channelName );
				statusIdInput.val( data.statusId );
				totpCodeInput.val( data.totpToken );

				if ( loginConfigQrCodeForm.length ) {
					enableSpinner();
					pusher.disconnect();
					loginConfigQrCodeForm.submit();
				} else {
					pair( pusher, data.totpToken, channelName, data.statusId );
				}
			}

			function handleSubscriptionError( status, pusher, channelName, attemptCount ) {
				if ( isRequestAborted( status ) ) {
					return;
				}

				if ( attemptCount < 3 ) {
					subscribeChannel( pusher, channelName, ++attemptCount );
				} else {
					Sentry.captureMessage( 'Subscription error', {
						extra: {
							channel_name: channelName,
							status: status
						}
					} );

					modal.showErrorModal( 'Subscription error (' + status + ')' );
				}
			}

			function subscribeChannel( pusher, channelName, attemptCount ) {
				var channel = pusher.subscribe( channelName );

				channel.bind( 'login-request', function( data ) {
					handleLoginRequest( pusher, data, channelName );
				} );

				channel.bind( 'configuration-request', function( data ) {
					handleConfigurationRequest( pusher, data, channelName );
				} );

				channel.bind( 'pusher:subscription_error', function( status ) {
					handleSubscriptionError( status, pusher, channelName, attemptCount )
				} );

				channel.bind( 'pusher:subscription_succeeded', function() {
					channelSubscribed = true;
					disableSpinner();

					if ( showingQrCode ) {
						showQrCode();
					}
				} );
			}

			function startPusher() {
				if ( channelSubscribed ) {
					return;
				}

				var pusher       = new Pusher( mpwdPusher.pusherKey, {
					    forceTLS: true,
					    authEndpoint: mpwdPusher.authenticateEndpoint,
					    auth: {
						    params: {
							    'session_id': sessionIdInput.val()
						    },
						    headers: {
							    'X-Requested-With': 'XMLHttpRequest'
						    }
					    }
				    } ),
				    attemptCount = 1,
				    channelName  = 'private-wp_' + integrationIdInput.val() + '_' + sessionIdInput.val();

				subscribeChannel( pusher, channelName, attemptCount );
			}

			function isNotConfiguredView() {
				return $( '.mf-not-configured' ).length;
			}

			function isLoginCookieSet() {
				return -1 < document.cookie.search( 'mpwd_login=1' );
			}

			function isLoginConfigurationView() {
				return actionInput.length && 'login-configuration' === actionInput.val();
			}

			function isPasswordlessLoginView() {
				return actionInput.length && 'passwordless-login' === actionInput.val();
			}

			function isLoginSecondStepView() {
				return $( '.mf-login' ).length && (
					isLoginConfigurationView() || isPasswordlessLoginView()
				);
			}

			function isLoginPrimaryView() {
				return $( '.mf-login' ).length && isLoginCookieSet();
			}

			function isLoginQrCodeView() {
				return isLoginPrimaryView() || isLoginSecondStepView();
			}

			function isMagicButtonVisible() {
				return $( '.magic-button' ).is( ':visible' );
			}

			function isRequestAborted( status ) {
				return 0 === status;
			}

			function showQrCode() {
				QRPlaceholderImage.hide();
				$( '.mf-qr-code' ).removeClass( 'mf-qr-code-hide' );
				showQrCodeButton.text( 'MAGIC CODE READY' );
				showQrCodeButton.prop( 'disabled', true );
			}

			$( '.mf-js-subscribe-pusher-channel' ).on( 'click', startPusher );

			showQrCodeButton.click( function( event ) {
				event.preventDefault();

				if ( channelSubscribed ) {
					showQrCode();
				} else {
					showingQrCode = true;
					QRPlaceholderImage.hide();
					$( this ).text( 'LOADING' ).prop( 'disabled', true );
					$( '.mf-qr-code-wrapper' ).addClass( 'mf-loading' );
					startPusher();
				}
			} );

			if ( isNotConfiguredView() || isLoginQrCodeView() || isMagicButtonVisible() ) {
				startPusher();
			}

			jsHandleFocus.on( 'click', function() {
				jsHandleFocus.addClass( 'mf-link-disabled' );

				$( window ).on( 'focus', function() {
					$( document ).trigger( 'waitingForCode' );
				} );
			} );

			$( document ).on( 'waitingForCode', function() {
				setTimeout( function() {
					jsHandleFocus.removeClass( 'mf-link-disabled' );

					if ( waitingForCode ) {
						toaster.showToast( 'Request not received.' );
					}
				}, 2000 );
			} );

			$( document ).on( 'receivedCode', function() {
				waitingForCode = false;
			} );
		} catch ( e ) {
			Sentry.captureException( e );
			modal.showErrorModal( e.message );
		}
	}
)( jQuery, mpwdDashboard.toaster, mpwdDashboard.modal );
