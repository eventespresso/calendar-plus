import { unregisterBlockType } from '@wordpress/blocks';
import domReady from '@wordpress/dom-ready';

// Unregister our block on all other post types that do not use the metadata
domReady( function () {
	if(typeof postData !== "undefined" && postData?.postType !== 'calendar-event'){
		unregisterBlockType( 'events-calendar-plus/calendar-event' );
	}
});
