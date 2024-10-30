(
	function( $ ) {
		$( 'a:contains("Magic Password")' ).parent().parent().next().find( 'a' ).remove();
	}
)( jQuery );
