(
    function( $ ) {
        try {
            var mobileDetect = new MobileDetect( window.navigator.userAgent );

            function isMobileDevice() {
                return mobileDetect.mobile() !== null || mobileDetect.tablet() !== null;
            }

            function setDeviceType() {
                var bodyClass = isMobileDevice() ? 'mpwd-mobile' : 'mpwd-desktop';
                $( 'body' ).addClass( bodyClass );
            }

            setDeviceType();
        } catch ( e ) {
            Sentry.captureException( e );
        }
    }
)( jQuery );