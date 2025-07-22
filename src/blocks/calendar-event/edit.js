import {__} from '@wordpress/i18n';
import {
	Card,
	CardBody,
	CheckboxControl,
	__experimentalHStack as HStack,
	__experimentalInputControl as InputControl,
	__experimentalVStack as VStack
} from '@wordpress/components';

import './editor.scss';

export default function Edit() {
	const { currentPost, isCleanNewPost, isPostSavingLocked, isPublishSidebarOpened } = wp.data.useSelect( (select) => {
		return {
			currentPost: select('core/editor').getCurrentPost(),
			isCleanNewPost: select( 'core/editor' ).isCleanNewPost(),
			isPostSavingLocked: select( 'core/editor' ).isPostSavingLocked(),
			isPublishSidebarOpened: select( 'core/editor' ).isPublishSidebarOpened(),
		}
	} );

	// generate keys for controlling post locks and notices
	const postLockKey = `calendar-event-save-lock-notice-${currentPost.id}`;
	const requiredKey = `calendar-event-start-date-required-${currentPost.id}`;

	// Fetch the meta as an object and the setMeta function
	const [meta, setMeta] = wp.coreData.useEntityProp('postType', currentPost.type, 'meta');

	const defaults = {
		calendar_event_address: '',
		calendar_event_all_day: false,
		calendar_event_city: '',
		calendar_event_country: '',
		calendar_event_end_datetime: '',
		calendar_event_start_datetime: '',
		calendar_event_state: '',
		calendar_event_timezone_offset: '',
		calendar_event_venue: '',
	}

	const metaData = {...defaults, ...meta};

	const address = metaData?.calendar_event_address || '';
	const all_day = metaData?.calendar_event_all_day || false;
	const city = metaData?.calendar_event_city || '';
	const country = metaData?.calendar_event_country || '';
	const end_datetime = metaData?.calendar_event_end_datetime || '';
	const start_datetime = metaData?.calendar_event_start_datetime || '';
	const state = metaData?.calendar_event_state || '';
	const timezone_offset = metaData?.calendar_event_timezone_offset || '';
	const venue = metaData?.calendar_event_venue || '';

	wp.element.useEffect(() => {
		if ( ! start_datetime ) {
			if ( ! isPostSavingLocked && ! isCleanNewPost && isPublishSidebarOpened) {
				wp.data.dispatch('core/editor').lockPostSaving(requiredKey);
				wp.data.dispatch('core/notices').createErrorNotice(
					'Please enter a start date to continue.',
					{id: postLockKey, isDismissible: false}
				);
			}
		} else if (isPostSavingLocked) {
			wp.data.dispatch( 'core/editor' ).unlockPostSaving(requiredKey);
			wp.data.dispatch('core/notices').removeNotice(postLockKey);
		}
	}, [isCleanNewPost, isPostSavingLocked, isPublishSidebarOpened, start_datetime, postLockKey, requiredKey]);


	return (
		<Card className={'calendar-event-details'}>
			<CardBody isBorderless isShady size={'large'}>
				<input type="hidden" name="timezone_offset" value={timezone_offset}/>
			<VStack spacing={8}>
				<HStack className="datetimes" spacing={4}>
					<InputControl type="datetime-local"
								  label={__('Start Date & Time', 'events-calendar-plus')}
								  value={start_datetime}
								  onChange={(nextValue) => setMeta({"calendar_event_start_datetime": nextValue})}
								  isPressEnterToChange
								  required
								  __next40pxDefaultSize
					/>
					<InputControl type="datetime-local"
								  label={__('End Date & Time', 'events-calendar-plus')}
								  value={end_datetime}
								  onChange={(nextValue) => setMeta({"calendar_event_end_datetime": nextValue})}
								  isPressEnterToChange
								  __next40pxDefaultSize
					/>
					<CheckboxControl label={__('All Day Event', 'events-calendar-plus')}
									 checked={all_day}
									 className="all-day-checkbox"
									 onChange={(nextValue) => setMeta({"calendar_event_all_day": nextValue})}
									 __nextHasNoMarginBottom
					/>
				</HStack>
				<HStack className="venue-address" spacing={4}>
					<InputControl type="text"
								  label={__('Venue Name', 'events-calendar-plus')}
								  value={venue}
								  onChange={(nextValue) => setMeta({"calendar_event_venue": nextValue})}
								  isPressEnterToChange
								  __next40pxDefaultSize
					/>
					<InputControl type="text"
								  label={__('Address', 'events-calendar-plus')}
								  value={address}
								  onChange={(nextValue) => setMeta({"calendar_event_address": nextValue})}
								  isPressEnterToChange
								  __next40pxDefaultSize
					/>
				</HStack>
				<HStack className="city-state-country" spacing={4}>
					<InputControl type="text"
								  label={__('City', 'events-calendar-plus')}
								  value={city}
								  onChange={(nextValue) => setMeta({"calendar_event_city": nextValue})}
								  isPressEnterToChange
								  __next40pxDefaultSize
					/>
					<InputControl type="text"
								  label={__('State/Province', 'events-calendar-plus')}
								  value={state}
								  onChange={(nextValue) => setMeta({"calendar_event_state": nextValue})}
								  isPressEnterToChange
								  __next40pxDefaultSize
					/>
					<InputControl type="text"
								 label={__('Country', 'events-calendar-plus')}
								 value={country}
								 onChange={(nextValue) => setMeta({"calendar_event_country": nextValue})}
								  isPressEnterToChange
								 __next40pxDefaultSize
					/>
				</HStack>
			</VStack>
			</CardBody>
		</Card>
	);
}
