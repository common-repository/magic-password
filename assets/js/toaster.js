(
	function( $ ) {
		if (typeof window.mpwdDashboard === "undefined") {
			window.mpwdDashboard = {};
		}

		window.mpwdDashboard.toaster = function() {
			var timeout       = null,
			    toast         = $( '.mf-toast' ),
			    toastMessage  = $( '.mf-toast-message' ),
			    toastCloseBtn = $( '.mf-js-close-toast' );

			function showToast( message ) {
				var timeout = toast.hasClass( 'mf-toast-visible' ) ? 500 : 1;

				closeToast();

				setTimeout( function() {
					openToast( message );
				}, timeout );
			}

			function showToastWithUndoOption( message ) {
				var timeout = toast.hasClass( 'mf-toast-visible' ) ? 500 : 1;

				closeToast();

				setTimeout( function() {
					openToastWithUndoOption( message );
				}, timeout );
			}

			function openToast( message ) {
				toastMessage.text( message );
				toast.addClass( 'mf-toast-visible' );

				setToastTimeout();
			}

			function openToastWithUndoOption( message ) {
				toastMessage.text( message );
				toast.addClass( 'mf-toast-visible mf-toast-undo' );

				setToastTimeout();
			}

			function closeToast() {
				clearTimeout( timeout );
				toast.removeClass( 'mf-toast-visible mf-toast-undo' );
			}

			function setToastTimeout() {
				timeout = setTimeout( function() {
					closeToast();
				}, 7000 );
			}

			toastCloseBtn.on( 'click', function() {
				closeToast();
			} );

			return {
				showToast: showToast,
				showToastWithUndoOption: showToastWithUndoOption,
				closeToast: closeToast
			}
		}();
	}
)( jQuery );