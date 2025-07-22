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
            <h1>Calendar+ Help & How To</h1>
            <span>
                <span class="dashicons dashicons-sos"></span>
                <span class="dashicons dashicons-editor-help"></span>
            </span>
        </div>

        <div class="intro">
            <h2>Thank You for Using Events Calendar+</h2>
            <p>Calendar+ makes it easy to display a beautiful event calendar on your website, regardless of where you
               manage your events.
            </p>
        </div>

        <div class="installation">
            <h3>Installation & Setup</h3>
            <p>After installing Calendar+, simply add the <code>[EVENTS_CALENDAR_PLUS]</code> shortcode to any page or post
               to display your event calendar.
            </p>
        </div>

        <h3 class="faqs-hdr">Frequently Asked Questions</h3>
        <div class="faqs-wrapper">
            <div class="faqs gc">
                <h4>General Questions</h4>
                <h5>Q: What does Calendar+ do?</h5>
                <p class="ecp-answer">
                    <span>A:</span> Calendar+ allows you to display your events in a visually appealing calendar
                                    format directly on your website. Event data can be sourced from Calendar+ itself
                                    or integrated with other event management applications.
                </p>
                <h5>Q: What calendar platforms can I use with Calendar+?</h5>
                <p class="ecp-answer">
                    <span>A:</span> Currently, Calendar+ supports WordPress,
                    <a href="https://eventespresso.com">Event Espresso</a>, and
                    <a href="https://eventsmart.com">Event Smart</a>. We plan to expand integrations over time.
                </p>
                <h5>Q: Is Calendar+ free to use?</h5>
                <p class="ecp-answer">
                    <span>A:</span> Yes! Calendar+ is a free WordPress plugin designed to showcase event calendars.
                                    Some advanced features may require additional paid add-ons or integrations.
                </p>
                <h5>Q: Does Calendar+ work with any WordPress theme?</h5>
                <p class="ecp-answer">
                    <span>A:</span> Yes, Calendar+ is designed to work with virtually all WordPress themes.
                </p>
                <h5>Q: Can I customize the appearance of the calendar?</h5>
                <p class="ecp-answer">
                    <span>A:</span> Absolutely! You can customize colors, text, layouts, and display settings within
                                    the plugin settings to match your website’s branding.
                </p>
                <h5>Q: Is Calendar+ mobile-friendly?</h5>
                <p class="ecp-answer">
                    <span>A:</span> Yes, Calendar+ is fully responsive and works on desktops, tablets, and mobile
                                    devices.
                </p>
            </div>
            <div class="faqs em">
                <h4>Event Management & Display</h4>
                <h5>Q: What is Event Espresso?</h5>
                <p class="ecp-answer">
                    <span>A:</span>
                    <a href="https://eventespresso.com">Event Espresso</a>
                    is a powerful WordPress plugin for event registration and ticket sales.
                </p>
                <h5>Q: What is Event Smart?</h5>
                <p class="ecp-answer">
                    <span>A:</span>
                    <a href="https://eventsmart.com">Event Smart</a>
                    is a cloud-based event management platform that allows you to create an event website, manage
                    events, sell tickets, and accept registrations without needing to host a website yourself.
                </p>
                <h5>Q: How do I add events to my calendar?</h5>
                <p class="ecp-answer">
                    <span>A:</span> You can manually enter events within WordPress or pull event data from
                    <strong>Event Espresso</strong> or <strong>Event Smart</strong> for automated event syncing.
                </p>
                <h5>Q: Can I display different calendar views (month, week, day, list, agenda)?</h5>
                <p class="ecp-answer">
                    <span>A:</span> Yes! Calendar+ supports multiple views so visitors can choose how they want to
                                    browse events.
                </p>
                <h5>Q: Can I categorize my events?</h5>
                <p class="ecp-answer">
                    <span>A:</span> Yes, you can organize events by categories and tags, making it easier for
                                    visitors to filter and find relevant events.
                </p>
                <h5>Q: Can I import events from Google Calendar, Eventbrite, or other platforms?</h5>
                <p class="ecp-answer">
                    <span>A:</span> Currently, Calendar+ supports Event Espresso and Event Smart. We plan to
                                    introduce integrations for Google Calendar, Eventbrite, and other platforms in
                                    future updates.
                </p>
            </div>
            <div class="faqs tc">
                <h4>Technical & Customization</h4>
                <h5>Q: Does Calendar+ support shortcodes?</h5>
                <p class="ecp-answer">
                    <span>A:</span> Yes! You can embed the calendar anywhere on your site using the
                    <code>[EVENTS_CALENDAR_PLUS]</code> shortcode.
                </p>
                <h5>Q: How do I add Calendar+ to my homepage or another page?</h5>
                <p class="ecp-answer">
                    <span>A:</span> Simply insert the <code>[EVENTS_CALENDAR_PLUS]</code> shortcode into any post or page
                                    where you want the calendar to appear.
                </p>
                <h5>Q: Can I change the date/time format to match my region?</h5>
                <p class="ecp-answer">
                    <span>A:</span> Yes, Calendar+ allows you to set your preferred date and time format in the
                                    plugin settings.
                </p>
                <h5>Q: Can I customize event tooltips or pop-ups?</h5>
                <p class="ecp-answer">
                    <span>A:</span> Yes! You can enable/disable tooltips and customize their appearance within the
                                    settings.
                </p>
                <h5>Q: Can I display multiple calendars on different pages?</h5>
                <p class="ecp-answer">
                    <span>A:</span> Yes, you can create multiple calendar views using event categories or tags.
                </p>
                <h5>Q: Can I add more features?</h5>
                <p class="ecp-answer">
                    <span>A:</span> Yes! By integrating with Event Espresso, you can unlock additional features such
                                    as event registration, ticketing, payment processing, and custom attendee
                                    registration forms. We will also release add-on extensions with new
                                    capabilities, including category-specific calendars, additional templates, and
                                    integrations with platforms like Gmail and Eventbrite to expand your event
                                    sources and display options.
                </p>
            </div>
            <div class="faqs tr">
                <h4>Ticketing & Registration</h4>
                <h5>Q: Can I sell tickets or accept registrations for events?</h5>
                <p class="ecp-answer">
                    <span>A:</span> Calendar+ itself does not handle ticket sales, but by integrating with
                    <a href="https://eventespresso.com">Event Espresso</a>, you can enable event registration,
                                    ticketing, and payment processing.
                </p>
                <h5>Q: What payment gateways are supported if I want to sell tickets?</h5>
                <p class="ecp-answer">
                    <span>A:</span> If using Event Espresso, you can accept payments via
                    <strong>PayPal, Stripe, Square</strong>, and other gateways.
                </p>
            </div>
            <div class="faqs st">
                <h4>Support & Troubleshooting</h4>
                <h5>Q: What should I do if my calendar isn’t displaying correctly?</h5>
                <p class="ecp-answer">
                    <span>A:</span> First, ensure your shortcode is placed correctly. If issues persist, check for
                                    plugin conflicts by deactivating other plugins one by one. If needed, reach out
                                    to our support team for help.
                </p>
                <h5>Q: Where can I get support if I need help?</h5>
                <p class="ecp-answer">
                    <span>A:</span> You can reach our support team through our
                    <a href="https://wordpress.org/support/plugin/events-calendar-plus/">support page</a>
                                    or visit the community forums for assistance.
                </p>
                <h5>Q: How do I suggest a new feature?</h5>
                <p class="ecp-answer">
                    <span>A:</span> Absolutely! We welcome suggestions.
                                    Feel free to reach out to us anytime with your ideas.
                </p>
            </div>
        </div>

    </div>
</div>
