=== Parish Core ===
Contributors: greenberry
Tags: parish, catholic, church, mass times, events, liturgical
Requires at least: 6.5
Tested up to: 6.7
Requires PHP: 8.0
Stable tag: 2.2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A comprehensive parish management system for Catholic parishes.

== Description ==

Parish Core provides a complete solution for managing your Catholic parish website.

**Dashboard Features:**

* Welcome banner with parish logo
* Today's Mass Times widget
* This Week's Mass Schedule (7-day grid with today highlighted)
* Today's Mass Readings (First Reading, Psalm, Gospel)
* Liturgical calendar panel (season, feast day, color)
* Content Overview with clickable stats and "Add" links
* Recent Newsletters sidebar widget
* Upcoming Events list
* Quick Actions (customizable)
* Resources links
* Developed by Greenberry footer

**About Parish Editor:**

* Parish identity (name, description, logo, banner)
* Contact information (address, phone, email, office hours)
* Diocese information with link
* Social media links (Facebook, YouTube, Livestream)
* Clergy & Staff management
* Dashboard Resources management
* Quick Actions customization

**Mass Times Scheduler:**

* Visual 7-day grid scheduler
* Support for multiple churches
* Recurring patterns (weekly, first/last of month)
* Livestream integration with URL
* Notes per mass
* Active/inactive toggle

**Events Calendar:**

* Month calendar view with navigation
* Click day to add event
* Click event to edit
* Event types (parish, sacrament, feast)
* Color-coded events
* Location and description fields

**Settings (Admin Only):**

* Feature toggles for all 15 modules
* Admin Color Scheme customization (8 color options)
* Reset colors to defaults
* Shortcode Reference with attributes

**Readings API Page:**

* API key configuration
* All 7 endpoints listed
* Individual Fetch buttons per endpoint
* Fetch All Readings button
* Shortcode reference

**Custom Post Types (12 total):**

* Death Notices, Baptism Notices, Wedding Notices
* Churches, Schools, Cemeteries
* Parish Groups, Newsletters, News
* Gallery, Reflections, Prayers

**Shortcodes with Attributes:**

* [parish_mass_times] - day, church_id, show_livestream, format (daily/weekly/simple)
* [parish_events] - limit, type, month, year, past
* [parish_reflection] - Latest reflection
* [parish_churches] - List all churches
* [parish_clergy] - Staff/clergy list
* [parish_contact] - Contact information
* [parish_prayers] - limit, orderby
* [daily_readings] - Today's readings
* [mass_reading_details] - Detailed readings
* [feast_day_details] - Feast day info
* [sunday_homily] - Sunday homily
* [saint_of_the_day] - Today's saint
* [next_sunday_reading] - Next Sunday

**Role-Based Access:**

* Editors: Dashboard, About Parish, Mass Times, Events, CPTs
* Admins: All above plus Settings, Readings API, Feature Toggles

== Installation ==

1. Upload `parish-core` folder to `/wp-content/plugins/`
2. Activate the plugin
3. Go to Parish > Settings to configure features and colors
4. Go to Parish > About Parish to set up parish information
5. Add churches first (needed for mass times/events)
6. Configure mass times and events
7. Use shortcodes in your pages

== Changelog ==

= 2.2.0 =
* Added Today's Mass Times widget to dashboard
* Added This Week's Mass Schedule grid to dashboard
* Added Mass Readings card (First Reading, Psalm, Gospel) to dashboard
* Moved Liturgical panel to sidebar
* Added Recent Newsletters widget to sidebar
* Made Content Overview stats clickable with Add links
* Added Admin Color Scheme customization (8 colors)
* Added dedicated Readings API page with Fetch buttons
* Added Shortcode Reference panel with all attributes
* Added Prayers custom post type
* Added shortcode filters (day, church_id, format, type, month, year)
* Added Greenberry footer branding
* Fixed quick action default icons
* Improved responsive design

= 2.0.0 =
* Complete rewrite with React admin interface
* New dashboard with widgets
* About Parish editor
* Mass Times scheduler
* Events Calendar
* Feature toggles
* Liturgical calendar integration

= 1.0.0 =
* Initial release
