(
	function( $, toaster, modal ) {
		try {
			var optionsRadio       = $( 'input[name="mf-only"]' ),
			    loggingOptions     = $( 'input[name="mf-logging"]' ),
			    statusBar          = $( '.mf-status-bar' ),
			    disableOptionNo    = $( '.mf-js-disable-option-no' ),
			    optionNo           = $( '#mf-only-no' ),
			    reviewNotice       = $( '.mf-review-notice' ),
			    rolesSaveButton    = $( '.mf-js-save-roles' ),
			    roleForm           = $( '.mf-js-roles-form' ),
			    obligatorinessForm = $( '.mf-js-bottom-options' ),
			    loggingForm        = $( '.mf-js-form-logging' ),
			    roleState          = {
				    initialized: false,
				    penultimate: null,
				    current: null,
				    update: function( roles ) {
					    this.penultimate = this.current;
					    this.current = roles;
				    },
				    undo: function() {
					    this.current = this.penultimate;
					    this.penultimate = null;
				    }
			    };

			if ( roleForm.length && !roleState.initialized ) {
				roleState.current = getMarkedRoles();
				roleState.initialized = true;
			}

			function configurationActionUrl( action ) {
				return mpwdDashboardParams.baseConfigurationUrl + action;
			}

			function settingsActionUrl( action ) {
				return mpwdDashboardParams.baseSettingsUrl + action;
			}

			function handleError( response ) {
				var error = JSON.parse( response.responseText ).error;

				Sentry.captureMessage( 'Dashboard error', {
					extra: {
						error: error,
						status: response.status
					}
				} );

				modal.showErrorModal( error );
			}

			function reverseRadioButtons( name ) {
				var button1   = $( 'input[name="' + name + '"][value="no"]' ),
				    button2   = $( 'input[name="' + name + '"][value="yes"]' ),
				    isChecked = button1.prop( 'checked' );

				button1.prop( 'checked', button2.prop( 'checked' ) );
				button2.prop( 'checked', isChecked );
			}

			function unmarkRoles() {
				$( '.mf-js-roles-form input:checkbox[name="role"]' ).each( function() {
					$( this ).attr( 'checked', false );
				} );
			}

			function markRoles( roles ) {
				unmarkRoles();

				roles.forEach( function( roleKey ) {
					$( '.mf-js-roles-form input:checkbox[value="' + roleKey + '"]' ).attr( 'checked', true );
				} );
			}

			function getMarkedRoles() {
				return $( '.mf-js-roles-form input:checkbox[name="role"]:checked' ).map( function() {
					return this.value;
				} ).get();
			}

			function enablePlugin( event ) {
				event.preventDefault();
				statusBar.removeClass( 'mf-disabled' ).addClass( 'mf-enabled mf-wait' );
				$( '.mf-switch-btn' ).removeClass( 'mf-switch-btn-disabled mf-js-enable-plugin' ).addClass( 'mf-js-disable-plugin' );
				showContent( 'mf-js-role-container' );
				showContent( 'mf-js-bottom-options' );

				$.post( settingsActionUrl( 'enable-plugin' ), {
					_wpnonce: $( '.mf-js-csrf-enable' ).val()
				} ).done( function( response ) {
					statusBar.removeClass( 'mf-wait' );
					toaster.showToast( response.message );
				} ).fail( function( response ) {
					statusBar.removeClass( 'mf-enabled mf-wait' ).addClass( 'mf-disabled' );
					$( '.mf-switch-btn' ).removeClass( 'mf-js-disable-plugin' ).addClass( 'mf-switch-btn-disabled mf-js-enable-plugin' );
					hideContent( 'mf-js-role-container' );
					hideContent( 'mf-js-bottom-options' );
					handleError( response );
				} );
			}

			function disablePlugin( event ) {
				event.preventDefault();
				statusBar.removeClass( 'mf-enabled' ).addClass( 'mf-disabled mf-wait' );
				$( '.mf-switch-btn' ).removeClass( 'mf-js-disable-plugin' ).addClass( 'mf-switch-btn-disabled mf-js-enable-plugin' );
				hideContent( 'mf-js-role-container' );
				hideContent( 'mf-js-bottom-options' );

				$.post( settingsActionUrl( 'disable-plugin' ), {
					_wpnonce: $( '.mf-js-csrf-disable' ).val()
				} ).done( function( response ) {
					statusBar.removeClass( 'mf-wait' );
					toaster.showToast( response.message );
				} ).fail( function( response ) {
					statusBar.removeClass( 'mf-disabled mf-wait' ).addClass( 'mf-enabled' );
					$( '.mf-switch-btn' ).removeClass( 'mf-switch-btn-disabled mf-js-enable-plugin' ).addClass( 'mf-js-disable-plugin' );
					showContent( 'mf-js-role-container' );
					showContent( 'mf-js-bottom-options' );
					handleError( response );
				} );
			}

			function hideContent( className ) {
				$( '.' + className ).addClass( 'mf-hidden' );
			}

			function showContent( className ) {
				$( '.' + className ).removeClass( 'mf-hidden' );
			}

			disableOptionNo.click( function() {
				optionNo.attr( 'disabled', true );
				optionNo.addClass( 'mf-option-disabled' );
				$( '.mf-only-yes-label' ).removeClass( 'mf-js-disable-option-no' );
			} );

			loggingOptions.on( 'change', function() {
				var targetValue = $( 'input[name="mf-logging"]:checked' ).val();

				loggingForm.addClass( 'mf-wait' );

				$.post( settingsActionUrl( 'save-logging' ), {
					_wpnonce: $( '.mf-js-form-logging #_wpnonce' ).val(),
					'mf-logging': targetValue
				} ).done( function( response ) {
					toaster.showToast( response.message );
					loggingForm.removeClass( 'mf-wait' );

					if ( 'yes' == targetValue ) {
						mpwdSentry.loggingAllowed = true;
					} else {
						mpwdSentry.loggingAllowed = false;
					}
				} ).fail( function( response ) {
					handleError( response );
					reverseRadioButtons( 'mf-logging' );
					loggingForm.removeClass( 'mf-wait' );
				} );
			} );

			$( document ).on( 'click', '.mf-js-enable-passwordless-login', function( event ) {
				event.preventDefault();

				var targetBarClass = 'mf-enabled';
				var targetSwitchClass = 'mf-js-disable-passwordless-login';

				if ( $( '.mf-status-bar-container' ).hasClass( 'has_passwordless_role' ) ) {
					targetBarClass = 'mf-blocked';
					targetSwitchClass = 'mf-switch-btn-blocked';
				}

				statusBar.removeClass( 'mf-disabled' ).addClass( targetBarClass + ' mf-wait' );
				$( '.mf-switch-btn' ).removeClass( 'mf-switch-btn-disabled mf-js-enable-passwordless-login' ).addClass( targetSwitchClass );
				showContent( 'mf-js-bottom-options' );

				$.post( configurationActionUrl( 'enable-passwordless-login' ), {
					_wpnonce: $( '.mf-js-csrf-token-for-enable-action' ).val()
				} ).done( function( response ) {
					statusBar.removeClass( 'mf-wait' );
					toaster.showToast( response.message )
				} ).fail( function( response ) {
					statusBar.removeClass( 'mf-wait ' + targetBarClass ).addClass( 'mf-disabled' );
					$( '.mf-switch-btn' ).removeClass( targetSwitchClass ).addClass( 'mf-switch-btn-disabled mf-js-enable-passwordless-login' );
					hideContent( 'mf-js-bottom-options' );
					handleError( response );
				} );
			} );

			$( document ).on( 'click', '.mf-js-disable-passwordless-login', function( event ) {
				event.preventDefault();

				statusBar.removeClass( 'mf-enabled' ).addClass( 'mf-disabled mf-wait' );
				$( '.mf-switch-btn' ).removeClass( 'mf-js-disable-passwordless-login' ).addClass( 'mf-switch-btn-disabled mf-js-enable-passwordless-login' );
				hideContent( 'mf-js-bottom-options' );

				$.post( configurationActionUrl( 'disable-passwordless-login' ), {
					_wpnonce: $( '.mf-js-csrf-token-for-disable-action' ).val()
				} ).done( function( response ) {
					statusBar.removeClass( 'mf-wait' );
					toaster.showToast( response.message );
				} ).fail( function( response ) {
					statusBar.removeClass( 'mf-wait mf-disabled' ).addClass( 'mf-enabled' );
					$( '.mf-switch-btn' ).removeClass( 'mf-switch-btn-disabled mf-js-enable-passwordless-login' ).addClass( 'mf-js-disable-passwordless-login' );
					showContent( 'mf-js-bottom-options' );
					handleError( response );
				} );
			} );

			reviewNotice.on( 'click', '.notice-dismiss', function() {
				$.post( settingsActionUrl( 'close-review-notice' ), {
					_wpnonce: $( '.mf-review-notice input' ).val()
				} ).fail( function( response ) {
					handleError( response );
				} );
			} );

			optionsRadio.on( 'change', function() {
				var targetValue = $( 'input[name="mf-only"]:checked' ).val();

				obligatorinessForm.addClass( 'mf-wait' );

				$.post( configurationActionUrl( 'set-obligatoriness' ), {
					_wpnonce: $( '.mf-option #_wpnonce' ).val(),
					'mf-only': targetValue
				} ).done( function( response ) {
					toaster.showToast( response.message );
					obligatorinessForm.removeClass( 'mf-wait' );
				} ).fail( function( response ) {
					handleError( response );
					reverseRadioButtons( 'mf-only' );
					obligatorinessForm.removeClass( 'mf-wait' );
				} );
			} );

			rolesSaveButton.on( 'click', function( event ) {
				event.preventDefault();
				rolesSaveButton.addClass( 'mf-btn-disabled' );
				$( '.mf-js-roles-form' ).addClass( 'mf-wait' );

				var markedRoles = getMarkedRoles();

				$.post( settingsActionUrl( 'save-roles' ), {
					_wpnonce: $( '.mf-js-roles-form #_wpnonce' ).val(),
					roles: markedRoles
				} ).done( function( response ) {
					toaster.showToastWithUndoOption( response.message );
					rolesSaveButton.removeClass( 'mf-btn-disabled' );
					$( '.mf-js-roles-form' ).removeClass( 'mf-wait' );
					roleState.update( markedRoles );
				} ).fail( function( response ) {
					handleError( response );
					markRoles( roleState.current );
					rolesSaveButton.removeClass( 'mf-btn-disabled' );
					$( '.mf-js-roles-form' ).removeClass( 'mf-wait' );
				} );
			} );

			$( '.mf-undo' ).on( 'click', function( event ) {
				event.preventDefault();
				toaster.closeToast();

				$.post( settingsActionUrl( 'save-roles' ), {
					_wpnonce: $( '.mf-js-roles-form #_wpnonce' ).val(),
					roles: roleState.penultimate
				} ).done( function() {
					toaster.showToast( 'Changes have been undone.' );
					markRoles( roleState.penultimate );
					roleState.undo();
				} ).fail( function( response ) {
					handleError( response );
				} );
			} );

			$( document ).on( 'click', '.mf-js-enable-plugin', enablePlugin );
			$( document ).on( 'click', '.mf-js-disable-plugin', disablePlugin );
		} catch ( e ) {
			Sentry.captureException( e );
			modal.showErrorModal( e.message );
		}
	}
)( jQuery, mpwdDashboard.toaster, mpwdDashboard.modal );
