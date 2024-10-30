(
	function( $ ) {
		var notificationWrapper = $( '#magic-password' ).next().find( '.update-message' );

		notificationWrapper.empty();
		notificationWrapper.append( "There is a new version of Magic Password available, but it doesn't work with your version of PHP. <a href='https://wordpress.org/support/update-php/' target='_blank'>Learn more about updating PHP</a>." );
	}
)( jQuery );
