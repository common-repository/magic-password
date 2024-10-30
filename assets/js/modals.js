(
	function( $ ) {
		try {
			var openTipsModal           = $( '.mf-js-open-tips' ),
			    closeModalButton        = $( '.mf-js-close-modal' ),
			    scanningTipsModal       = $( '.mf-scanning-tips-modal' ),
			    DeleteConfigButton      = $( '.mf-js-delete-config' ),
			    DeleteConfigModal       = $( '.mf-deletion-confirmation-modal' ),
				DeleteConfigForm		= $( '.mf-deletion-confirmation-modal-form' ),
			    configurationResetBtn   = $( '.mf-js-open-reset-confirmation' ),
			    configurationResetModal = $( '.mf-reset-confirmation-modal' ),
			    successModalBtn         = $( '.mf-js-success-modal-continue' ),
			    isModalOpened           = false;

			$( document ).on( 'mf-modal-open', '.mf-modal-backdrop', function() {
				$( this ).addClass( 'mf-modal-show' ).animate( { opacity: 1 }, 500 );
				isModalOpened = true;
			} );

			$( document ).on( 'mf-modal-close', '.mf-modal-backdrop', function() {
				$( this ).animate( { opacity: 0 }, 250, function() {
					$( this ).removeClass( 'mf-modal-show' );
					isModalOpened = false;
				} );
			} );

			// open modals
			openTipsModal.click( function( event ) {
				event.preventDefault();
				scanningTipsModal.trigger( 'mf-modal-open' );
			} );

			// close modals with backdrop click
			$( document ).mouseup( function( e ) {
				if ( isModalOpened ) {
					var container = $( '.mf-modal' );

					if ( !container.is( e.target ) && container.has( e.target ).length === 0 ) {
						container.trigger( 'mf-modal-close' );
					}
				}
			} );

			DeleteConfigButton.click( function( event ) {
				event.preventDefault();
				DeleteConfigModal.trigger( 'mf-modal-open' );
			} );

			configurationResetBtn.click( function( event ) {
				event.preventDefault();
				configurationResetModal.trigger( 'mf-modal-open' );
			} );

			// success modal button
			successModalBtn.click( function() {
				$( this ).addClass( 'mf-reloading mf-link-disabled' );
				location.reload();
			} );

			DeleteConfigForm.on( 'submit', function() {
				$( this ).find( '*[type="submit"]' ).addClass( 'mf-reloading mf-link-disabled' );
			} );

			// close modals
			closeModalButton.click( findModalParentAndClose );

			function findModalParentAndClose( event ) {
				event.preventDefault();
				$( this ).trigger( 'mf-modal-close' );
			}
		} catch ( e ) {
			Sentry.captureException( e );
		}
	}
)( jQuery );
