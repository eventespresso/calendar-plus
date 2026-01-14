<?php

/**
 * Provide an admin area view for the plugin settings
 *
 * @package    CalendarPlus
 * @subpackage CalendarPlus/admin/templates
 * @link       https://www.eventespresso.com
 * @since      1.0.0
 */

?>
<div class="wrap">
    <div class="calendar-plus-support">
        <div class="header">
            <h1><?php esc_html_e('Calendar+ Help & How To', 'events-calendar-plus'); ?></h1>
            <span>
                <span class="dashicons dashicons-sos"></span>
                <span class="dashicons dashicons-editor-help"></span>
            </span>
        </div>

        <div class="intro">
            <h2><?php esc_html_e('Thank You for Using Events Calendar+', 'events-calendar-plus'); ?></h2>
            <p>
                <?php esc_html_e(
                    'Calendar+ makes it easy to display a beautiful event calendar on your website, regardless of where you manage your events.',
                    'events-calendar-plus'
                ); ?>
            </p>
        </div>

        <div class="installation">
            <h3><?php esc_html_e('Installation & Setup', 'events-calendar-plus'); ?></h3>
            <p>
                <?php printf(
                    esc_html__(
                        'After installing Calendar+, simply add the %1$s shortcode to any page or post to display your event calendar.',
                        'events-calendar-plus'
                    ),
                    '<code>[EVENTS_CALENDAR_PLUS]</code>'
                ); ?>
            </p>
        </div>

        <h3 class="faqs-hdr"><?php esc_html_e('Frequently Asked Questions', 'events-calendar-plus'); ?></h3>
        <div class="faqs-wrapper">
            <div class="faqs gc">
                <h4><?php esc_html_e('General Questions', 'events-calendar-plus'); ?></h4>
                <h5>
                    <span class="question-abbr"><?php esc_html_e('Q:', 'events-calendar-plus'); ?></span>
                    <?php esc_html_e('What does Calendar+ do?', 'events-calendar-plus'); ?>
                </h5>
                <p class="ecp-answer">
                    <span class="answer-abbr"><?php esc_html_e('A:', 'events-calendar-plus'); ?></span>
                    <?php esc_html_e(
                        'Calendar+ allows you to display your events in a visually appealing calendar format directly on your website. Event data can be sourced from Calendar+ itself or integrated with other event management applications.',
                        'events-calendar-plus'
                    ); ?>
                </p>
                <h5>
                    <span class="question-abbr"><?php esc_html_e('Q:', 'events-calendar-plus'); ?></span>
                    <?php esc_html_e('What calendar platforms can I use with Calendar+?', 'events-calendar-plus'); ?>
                </h5>
                <p class="ecp-answer">
                    <span class="answer-abbr"><?php esc_html_e('A:', 'events-calendar-plus'); ?></span>
                    <?php printf(
                        esc_html__(
                        'Currently, Calendar+ supports WordPress, %1$sEvent Espresso%3$s, and %2$sEvent Smart%3$s. We plan to expand integrations over time.',
                        'events-calendar-plus'
                        ),
                        '<a href="https://eventespresso.com" target="_blank">',
                        '<a href="https://eventsmart.com" target="_blank">',
                        '</a>'
                    ); ?>
                </p>
                <h5>
                    <span class="question-abbr"><?php esc_html_e('Q:', 'events-calendar-plus'); ?></span>
                    <?php esc_html_e('Is Calendar+ free to use?', 'events-calendar-plus'); ?>
                </h5>
                <p class="ecp-answer">
                    <span class="answer-abbr"><?php esc_html_e('A:', 'events-calendar-plus'); ?></span>
                    <?php esc_html_e(
                        'Yes! Calendar+ is a free WordPress plugin designed to showcase event calendars. Some advanced features may require additional paid add-ons or integrations.',
                        'events-calendar-plus'
                    ); ?>
                </p>
                <h5>
                    <span class="question-abbr"><?php esc_html_e('Q:', 'events-calendar-plus'); ?></span>
                    <?php esc_html_e('Does Calendar+ work with any WordPress theme?', 'events-calendar-plus'); ?>
                </h5>
                <p class="ecp-answer">
                    <span class="answer-abbr"><?php esc_html_e('A:', 'events-calendar-plus'); ?></span>
                    <?php esc_html_e(
                        'Yes, Calendar+ is designed to work with virtually all WordPress themes.',
                        'events-calendar-plus'
                    ); ?>
                </p>
                <h5>
                    <span class="question-abbr"><?php esc_html_e('Q:', 'events-calendar-plus'); ?></span>
                    <?php esc_html_e('Can I customize the appearance of the calendar?', 'events-calendar-plus'); ?>
                </h5>
                <p class="ecp-answer">
                    <span class="answer-abbr"><?php esc_html_e('A:', 'events-calendar-plus'); ?></span>
                    <?php esc_html_e(
                        'Absolutely! You can customize colors, text, layouts, and display settings within the plugin settings to match your website’s branding.',
                        'events-calendar-plus'
                    ); ?>
                </p>
                <h5>
                    <span class="question-abbr"><?php esc_html_e('Q:', 'events-calendar-plus'); ?></span>
                    <?php esc_html_e('Is Calendar+ mobile-friendly?', 'events-calendar-plus'); ?>
                </h5>
                <p class="ecp-answer">
                    <span class="answer-abbr"><?php esc_html_e('A:', 'events-calendar-plus'); ?></span>
                    <?php esc_html_e(
                        'Yes, Calendar+ is fully responsive and works on desktops, tablets, and mobile devices.',
                        'events-calendar-plus'
                    ); ?>
                </p>
            </div>
            <div class="faqs em">
                <h4><?php esc_html_e('Event Management & Display', 'events-calendar-plus'); ?></h4>
                <h5>
                    <span class="question-abbr"><?php esc_html_e('Q:', 'events-calendar-plus'); ?></span>
                    <?php esc_html_e('What is Event Espresso?', 'events-calendar-plus'); ?>
                </h5>
                <p class="ecp-answer">
                    <span>A:</span>
                    <?php printf(
                        esc_html__(
                        '%1$sEvent Espresso%2$s is a powerful WordPress plugin for event registration and ticket sales.',
                        'events-calendar-plus'
                        ),
                        '<a href="https://eventespresso.com" target="_blank">',
                        '</a>'
                    ); ?>
                </p>
                <h5>
                    <span class="question-abbr"><?php esc_html_e('Q:', 'events-calendar-plus'); ?></span>
                    <?php esc_html_e('What is Event Smart?', 'events-calendar-plus'); ?>
                </h5>
                <p class="ecp-answer">
                    <span>A:</span>
                    <?php printf(
                        esc_html__(
                        '%1$sEvent Smart%2$s is a cloud-based event management platform that allows you to create an event website, manage events, sell tickets, and accept registrations without needing to host a website yourself.',
                        'events-calendar-plus'
                        ),
                        '<a href="https://eventsmart.com" target="_blank">',
                        '</a>'
                    ); ?>
                </p>
                <h5>
                    <span class="question-abbr"><?php esc_html_e('Q:', 'events-calendar-plus'); ?></span>
                    <?php esc_html_e('How do I add events to my calendar?', 'events-calendar-plus'); ?>
                </h5>
                <p class="ecp-answer">
                    <span class="answer-abbr"><?php esc_html_e('A:', 'events-calendar-plus'); ?></span>
                    <?php printf(
                        esc_html__(
                        'You can manually enter events within WordPress or pull event data from %1$sEvent Espresso%2$s or %1$sEvent Smart%2$s for automated event syncing.',
                        'events-calendar-plus'
                        ),
                        '<strong>',
                        '</strong>'
                    ); ?>
                </p>
                <h5>
                    <span class="question-abbr"><?php esc_html_e('Q:', 'events-calendar-plus'); ?></span>
                    <?php esc_html_e(
                        'Can I display different calendar views (month, week, day, list, agenda)?',
                        'events-calendar-plus'
                    ); ?>
                </h5>
                <p class="ecp-answer">
                    <span class="answer-abbr"><?php esc_html_e('A:', 'events-calendar-plus'); ?></span>
                    <?php esc_html_e(
                        'Yes! Calendar+ supports multiple views so visitors can choose how they want to browse events.',
                        'events-calendar-plus'
                    ); ?>
                </p>
                <h5>
                    <span class="question-abbr"><?php esc_html_e('Q:', 'events-calendar-plus'); ?></span>
                    <?php esc_html_e('Can I categorize my events?', 'events-calendar-plus'); ?>
                </h5>
                <p class="ecp-answer">
                    <span class="answer-abbr"><?php esc_html_e('A:', 'events-calendar-plus'); ?></span>
                    <?php esc_html_e(
                        'Yes, you can organize events by categories and tags, making it easier for visitors to filter and find relevant events.',
                        'events-calendar-plus'
                    ); ?>
                </p>
                <h5>
                    <span class="question-abbr"><?php esc_html_e('Q:', 'events-calendar-plus'); ?></span>
                    <?php esc_html_e(
                        'Can I import events from Google Calendar, Eventbrite, or other platforms?',
                        'events-calendar-plus'
                    ); ?>
                </h5>
                <p class="ecp-answer">
                    <span class="answer-abbr"><?php esc_html_e('A:', 'events-calendar-plus'); ?></span>
                    <?php esc_html_e(
                        'Currently, Calendar+ supports Event Espresso and Event Smart. We plan to introduce integrations for Google Calendar, Eventbrite, and other platforms in future updates.',
                        'events-calendar-plus'
                    ); ?>
                </p>
            </div>
            <div class="faqs tc">
                <h4><?php esc_html_e('Technical & Customization', 'events-calendar-plus'); ?></h4>
                <h5>
                    <span class="question-abbr"><?php esc_html_e('Q:', 'events-calendar-plus'); ?></span>
                    <?php esc_html_e('Does Calendar+ support shortcodes?', 'events-calendar-plus'); ?>
                </h5>
                <p class="ecp-answer">
                    <span class="answer-abbr"><?php esc_html_e('A:', 'events-calendar-plus'); ?></span>
                    <?php printf(
                        esc_html__(
                        'Yes! You can embed the calendar anywhere on your site using the %1$s shortcode.',
                        'events-calendar-plus'
                        ),
                        '<code>[EVENTS_CALENDAR_PLUS]</code>'
                    ); ?>
                </p>
                <h5>
                    <span class="question-abbr"><?php esc_html_e('Q:', 'events-calendar-plus'); ?></span>
                    <?php esc_html_e(
                        'How do I add Calendar+ to my homepage or another page?',
                        'events-calendar-plus'
                    ); ?>
                </h5>
                <p class="ecp-answer">
                    <span class="answer-abbr"><?php esc_html_e('A:', 'events-calendar-plus'); ?></span>
                    <?php printf(
                        esc_html__(
                        'Simply insert the %1$s shortcode into any post or page where you want the calendar to appear.',
                        'events-calendar-plus'
                        ),
                        '<code>[EVENTS_CALENDAR_PLUS]</code>'
                    ); ?>
                </p>
                <h5>
                    <span class="question-abbr"><?php esc_html_e('Q:', 'events-calendar-plus'); ?></span>
                    <?php esc_html_e('Can I change the date/time format to match my region?', 'events-calendar-plus'); ?>
                </h5>
                <p class="ecp-answer">
                    <span class="answer-abbr"><?php esc_html_e('A:', 'events-calendar-plus'); ?></span>
                    <?php esc_html_e(
                        'Yes, Calendar+ allows you to set your preferred date and time format in the plugin settings.',
                        'events-calendar-plus'
                    ); ?>
                </p>
                <h5>
                    <span class="question-abbr"><?php esc_html_e('Q:', 'events-calendar-plus'); ?></span>
                    <?php esc_html_e('Can I customize event tooltips or pop-ups?', 'events-calendar-plus'); ?>
                </h5>
                <p class="ecp-answer">
                    <span class="answer-abbr"><?php esc_html_e('A:', 'events-calendar-plus'); ?></span>
                    <?php esc_html_e(
                        'Yes! You can enable/disable tooltips and customize their appearance within the settings.',
                        'events-calendar-plus'
                    ); ?>
                </p>
                <h5>
                    <span class="question-abbr"><?php esc_html_e('Q:', 'events-calendar-plus'); ?></span>
                    <?php esc_html_e('Can I display multiple calendars on different pages?', 'events-calendar-plus'); ?>
                </h5>
                <p class="ecp-answer">
                    <span class="answer-abbr"><?php esc_html_e('A:', 'events-calendar-plus'); ?></span>
                    <?php esc_html_e(
                        'Yes, you can create multiple calendar views using event categories or tags.',
                        'events-calendar-plus'
                    ); ?>
                </p>
                <h5>
                    <span class="question-abbr"><?php esc_html_e('Q:', 'events-calendar-plus'); ?></span>
                    <?php esc_html_e('Can I add more features?', 'events-calendar-plus'); ?>
                </h5>
                <p class="ecp-answer">
                    <span class="answer-abbr"><?php esc_html_e('A:', 'events-calendar-plus'); ?></span>
                    <?php esc_html_e(
                        'Yes! By integrating with Event Espresso, you can unlock additional features such
                                    as event registration, ticketing, payment processing, and custom attendee
                                    registration forms. We will also release add-on extensions with new
                                    capabilities, including category-specific calendars, additional templates, and
                                    integrations with platforms like Gmail and Eventbrite to expand your event
                                    sources and display options.',
                        'events-calendar-plus'
                    ); ?>
                </p>
            </div>
            <div class="faqs tr">
                <h4><?php esc_html_e('Ticketing & Registration', 'events-calendar-plus'); ?></h4>
                <h5>
                    <span class="question-abbr"><?php esc_html_e('Q:', 'events-calendar-plus'); ?></span>
                    <?php esc_html_e('Can I sell tickets or accept registrations for events?', 'events-calendar-plus'); ?>
                </h5>
                <p class="ecp-answer">
                    <span class="answer-abbr"><?php esc_html_e('A:', 'events-calendar-plus'); ?></span>
                    <?php printf(
                        esc_html__(
                        'Calendar+ itself does not handle ticket sales, but by integrating with %1$sEvent Espresso%2$s, you can enable event registration, ticketing, and payment processing.',
                        'events-calendar-plus'
                        ),
                        '<a href="https://eventespresso.com" target="_blank">',
                        '</a>'
                    ); ?>
                </p>
                <h5>
                    <span class="question-abbr"><?php esc_html_e('Q:', 'events-calendar-plus'); ?></span>
                    <?php esc_html_e(
                        'What payment gateways are supported if I want to sell tickets?',
                        'events-calendar-plus'
                    ); ?>
                </h5>
                <p class="ecp-answer">
                    <span class="answer-abbr"><?php esc_html_e('A:', 'events-calendar-plus'); ?></span>
                    <?php printf(
                        esc_html__(
                        'If using Event Espresso, you can accept payments via %1$sPayPal, Stripe, Square%2$s, and other gateways.',
                        'events-calendar-plus'
                        ),
                        '<strong>',
                        '</strong>'
                    ); ?>
                </p>
            </div>
            <div class="faqs st">
                <h4><?php esc_html_e('Support & Troubleshooting', 'events-calendar-plus'); ?></h4>
                <h5>
                    <span class="question-abbr"><?php esc_html_e('Q:', 'events-calendar-plus'); ?></span>
                    <?php esc_html_e(
                        'What should I do if my calendar isn’t displaying correctly?',
                        'events-calendar-plus'
                    ); ?>
                </h5>
                <p class="ecp-answer">
                    <span class="answer-abbr"><?php esc_html_e('A:', 'events-calendar-plus'); ?></span>
                    <?php esc_html_e(
                        'First, ensure your shortcode is placed correctly. If issues persist, check for plugin conflicts by deactivating other plugins one by one. If needed, reach out to our support team for help.',
                        'events-calendar-plus'
                    ); ?>
                </p>
                <h5>
                    <span class="question-abbr"><?php esc_html_e('Q:', 'events-calendar-plus'); ?></span>
                    <?php esc_html_e('Where can I get support if I need help?', 'events-calendar-plus'); ?>
                </h5>
                <p class="ecp-answer">
                    <span class="answer-abbr"><?php esc_html_e('A:', 'events-calendar-plus'); ?></span>
                    <?php printf(
                        esc_html__(
                        'You can reach our support team through our %1$ssupport page%2$s or visit the community forums for assistance.',
                        'events-calendar-plus'
                        ),
                        '<a href="https://wordpress.org/support/plugin/events-calendar-plus/" target="_blank">',
                        '</a>'
                    ); ?>
                </p>
                <h5>
                    <span class="question-abbr"><?php esc_html_e('Q:', 'events-calendar-plus'); ?></span>
                    <?php esc_html_e('How do I suggest a new feature?', 'events-calendar-plus'); ?>
                </h5>
                <p class="ecp-answer">
                    <span class="answer-abbr"><?php esc_html_e('A:', 'events-calendar-plus'); ?></span>
                    <?php esc_html_e(
                        'Absolutely! We welcome suggestions. Feel free to reach out to us anytime with your ideas.',
                        'events-calendar-plus'
                    ); ?>
                </p>
            </div>
        </div>

    </div>
</div>
