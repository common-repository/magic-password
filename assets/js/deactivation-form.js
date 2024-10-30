(
	function( $ ) {
		function disableElement( element ) {
			element.prop( 'disabled', true );
		}

		function enableElement( element ) {
			element.prop( 'disabled', false );
		}

		try {
			var deactivationButton = $( '#the-list tr.active' ).filter( function() {
				    return $( this ).data( 'plugin' ) === 'magic-password/magic-password.php';
			    } ).find( '.deactivate a' ),
			    deactivationUrl    = deactivationButton.attr( 'href' ),
			    isFormAdded        = false;

			deactivationButton.click( function( e ) {
				e.preventDefault();

				if ( !isFormAdded ) {
					$( 'body' ).append( mpwdDeactivation.deactivationForm );
					isFormAdded = true;
				}

				var deactivationToken       = $( '.mf-deactivation-token > input[name="_wpnonce"]' ),
				    skipButton              = $( '.mf-js-deactivation-skip' ),
				    sendButton              = $( '.mf-js-deactivation-send' ),
				    deactivationPluginModal = $( '.mf-deactivation-plugin-modal' ),
				    reasonRadio             = $( 'input[name=reason]' ),
				    textArea                = $( 'textarea[name=reason-desc]' );

				reasonRadio.on( 'change', function() {
					if ( $( this ).val() === 'other' ) {
						enableElement( textArea );

						if ( $.trim( textArea.val() ).length ) {
							enableElement( sendButton );
						} else {
							disableElement( sendButton );
						}

					} else {
						enableElement( sendButton );
						disableElement( textArea );
					}
				} );

				textArea.on( 'input change keyup paste', function() {
					if ( $.trim( $( this ).val() ).length ) {
						enableElement( sendButton );
					} else {
						disableElement( sendButton );
					}
				} );

				skipButton.off( 'click' ).on( 'click', function( e ) {
					e.preventDefault();
					window.location.href = deactivationUrl;
				} );

				sendButton.off( 'click' ).on( 'click', function( e ) {
					e.preventDefault();

					var reason  = $( 'input[name=reason]:checked' ),
					    message = '';

					if ( reason.length ) {
						if ( reason.val() !== 'other' ) {
							message = reason.next().html();
						} else {
							message = textArea.val();
						}
					}

					disableElement( sendButton );

					$.post( mpwdDeactivation.deactivationUrl + 'send-deactivation-reason', {
						_wpnonce: deactivationToken.val(),
						message: message
					} ).always( function() {
						window.location.href = deactivationUrl;
					} )
				} );

				deactivationPluginModal.trigger( 'mf-modal-open' );
			} );
		} catch ( e ) {
			Sentry.captureException( e );
		}
	}
)( jQuery );
