(
	function( $ ) {
		if ( typeof window.mpwdDashboard === 'undefined' ) {
			window.mpwdDashboard = {};
		}

		window.mpwdDashboard.modal = function() {
			var errorModal        = $( '.mf-error-modal' ),
			    errorModalMessage = $( '.mf-error' );

			function showErrorModal( error ) {
				if ( error === 'General error.' ) {
					error = '';
				}
				errorModalMessage.html( '<span class="mf-text-line">Something went wrong.</span> <span class="mf-text-line">Please try to refresh this page.</span><small>' + error + '</small>' );
				errorModal.trigger( 'mf-modal-open' );
			}

			return {
				showErrorModal: showErrorModal
			};
		}();
	}
)( jQuery );
