<?php

use EventEspresso\CalendarPlus\CalendarPlusPostMeta;

$post_id   = get_the_ID();
$post_meta = CalendarPlusPostMeta::forPostContent($post_id);
[
	'address'       => $address,
	'all_day_event' => $all_day_event,
	'city'          => $city,
	'country'       => $country,
	'end_date'      => $end_date,
	'end_time'      => $end_time,
	'same_day'      => $same_day,
	'start_date'    => $start_date,
	'start_time'    => $start_time,
	'state'         => $state,
	'venue'         => $venue
] = $post_meta;

// add commas after address, city, & state if trailing values exist
$address    = $city || $state || $country ? "$address, " : $address;
$city   = $state || $country ? "$city, " : $city;
$state = $country ? "$state, " : $state;

/**
 * @package    CalendarPlus
 * @subpackage CalendarPlus/frontend/templates
 * @link       https://www.eventespresso.com
 * @since      1.0.0
 * @see        https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 *
 * @var bool $all_day_event
 * @var bool $same_day
 * @var string $address
 * @var string $city
 * @var string $country
 * @var string $end_date
 * @var string $end_time
 * @var string $start_date
 * @var string $start_time
 * @var string $state
 * @var string $venue
 */

?>
<div <?php echo get_block_wrapper_attributes(); ?>>
	<div class="calendar-plus-event-date-time">
		<?php if ($all_day_event) :
			if ($start_date) : ?>
				<span class="calendar-plus-date-wrapper">
			<span class="dashicons dashicons-clock"></span>
			<span class="calendar-plus-event-start-date"><?php echo esc_html($start_date) ?></span>
		</span>
			<?php endif; ?>
			<span class="calendar-plus-event-all-day">
			<?php esc_html_e('All Day Event', 'events-calendar-plus') ?>
		</span>
			<?php if ($start_time) : ?>
			<span class="calendar-plus-event-start-time">
		<?php
			/* translators: time of day, ex: starts at: 11:00 am */
			echo esc_html(sprintf(__('starts at: %1$s', 'events-calendar-plus'), $start_time));
		?>
		</span>
		<?php endif; ?>
		<?php else : // not all day event ?>
			<?php if ($start_date) : ?>
				<span class="calendar-plus-date-wrapper">
			<span class="dashicons dashicons-clock"></span>
			<span class="calendar-plus-event-start-date"><?php echo esc_html($start_date); ?></span>
			<span class="calendar-plus-event-start-time"><?php echo esc_html($start_time); ?></span>
		</span>
			<?php endif; ?>
			<?php if ($end_date) : ?>
				<span class="calendar-plus-event-separator"> - </span>
				<?php if (! $same_day) : ?>
					<span class="calendar-plus-event-end-date"><?php echo esc_html($end_date); ?></span>
				<?php endif; ?>
				<span class="calendar-plus-event-end-time"><?php echo esc_html($end_time); ?></span>
			<?php endif; ?>
		<?php endif; ?>
	</div>
	<?php if ($venue) : ?>
		<div class="calendar-plus-event-venue">
		<span class="calendar-plus-event-venue-name">
			<span class="dashicons dashicons-location"></span>
			<?php echo esc_html($venue); ?>
		</span>
			<?php if ($address) : ?>
				<span class="calendar-plus-event-venue-address">
			<span class="calendar-plus-event-address"><?php echo esc_html($address); ?></span>
			<span class="calendar-plus-event-city"><?php echo esc_html($city); ?></span>
			<span class="calendar-plus-event-state"><?php echo esc_html($state); ?></span>
			<span class="calendar-plus-event-country"><?php echo esc_html($country); ?></span>
		</span>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</div>
