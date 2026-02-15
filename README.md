# Parish Core

A comprehensive parish management system for Catholic parishes built for WordPress.

**Version:** 8.9.0
**Requires WordPress:** 6.6+
**Requires PHP:** 8.2+
**License:** GPL v2 or later

## Description

Parish Core is a feature-rich WordPress plugin designed specifically for Catholic parishes. It provides a complete content management system for managing church schedules, clergy, events, sacraments, and more.

## Features

### Custom Post Types

The plugin registers the following custom post types for managing parish content:

| Post Type | Description |
| --------- | ----------- |
| **Churches** | Manage multiple churches within a parish |
| **Mass Times** | Configure recurring Mass schedules with flexible recurrence rules |
| **Clergy** | Staff profiles for priests, deacons, and parish staff |
| **Events** | Parish events with date/time management |
| **Baptisms** | Baptism scheduling and records |
| **Weddings** | Wedding scheduling and management |
| **Death Notices** | Memorial notices and obituaries |
| **Groups** | Parish groups and ministries |
| **Galleries** | Photo galleries for parish events |
| **History** | Parish history entries |
| **Newsletters** | Parish newsletter management |
| **Prayers** | Prayer resources |
| **Reflections** | Spiritual reflections and meditations |
| **Schools** | Associated school information |
| **Services** | Parish services information |
| **Cemetery** | Cemetery plot and records management |

### Schedule System

- Intelligent Mass schedule generator with recurrence rules
- Support for weekly, monthly, and custom schedules
- Exception handling for holidays and special occasions
- Automatic schedule caching for optimal performance
- Shortcode integration for frontend display

### Block Editor Integration

- Full Block Editor (Gutenberg) support
- Block Bindings for dynamic content
- Custom meta field support
- REST API integration

### Liturgical Features

- Daily readings integration
- Feast day calculations
- Rosary scheduling and content
- Liturgical calendar support

### Admin Features

- Intuitive admin interface
- Taxonomy filters for easy content organization
- Custom admin color schemes
- Bulk editing support

### Shortcodes

The plugin provides shortcodes for embedding content:

- `[church_schedule]` - Display church Mass schedules
- `[parish_events]` - Show upcoming events
- `[parish_clergy]` - Display clergy directory
- Rosary-related shortcodes

### REST API

Full REST API support for:

- All custom post types
- Schedule generation
- Meta field access
- Third-party integrations

## Installation

1. Upload the `parish-core` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to the Parish Core settings to configure features
4. Start creating content using the new post types

## Requirements

- WordPress 6.6 or higher
- PHP 8.2 or higher
- MySQL 5.7+ or MariaDB 10.3+

## Performance

Parish Core is optimized for production use:

- **Query Optimization:** Uses `update_post_meta_cache()` to prevent N+1 database queries
- **Transient Caching:** Expensive shortcode operations are cached for 5 minutes
- **Conditional Asset Loading:** CSS/JS only loads on pages that need it
- **Efficient Database Queries:** Paginated queries with sensible limits

## Security

The plugin follows WordPress security best practices:

- All user inputs are sanitized using `sanitize_text_field()`, `sanitize_textarea_field()`, etc.
- All outputs are escaped using `esc_html()`, `esc_attr()`, `esc_url()`, etc.
- REST API endpoints use proper permission callbacks
- Nonce verification for all form submissions
- Capability checks before sensitive operations
- Direct file access prevention

## Development

### Directory Structure

```text
parish-core/
├── assets/
│   ├── css/           # Frontend and admin stylesheets
│   └── js/            # Frontend and admin scripts
├── includes/
│   ├── cpt/
│   │   ├── modules/   # CPT definitions (one folder per type)
│   │   ├── class-parish-cpt-registry.php
│   │   ├── class-parish-meta-registry.php
│   │   └── class-parish-cpt-templates.php
│   ├── schedule/      # Schedule generation system
│   └── *.php          # Core plugin classes
├── languages/         # Translation files
└── parish-core.php    # Main plugin file
```

### Adding a New Post Type

1. Create a new folder in `includes/cpt/modules/your-type/`
2. Add `post-type.php` returning an array with `post_type`, `args`, and optional `feature`
3. Optionally add `tax.php` for custom taxonomies
4. The CPT Registry will auto-discover and register your post type

### Hooks & Filters

The plugin provides various hooks for customization:

```php
// Modify schedule generation arguments
add_filter( 'parish_schedule_args', 'your_function' );

// Hook into cache clearing
add_action( 'parish_schedule_cache_cleared', 'your_function' );
```

## Changelog

### 8.9.0

- Performance optimizations: N+1 query fixes and transient caching
- Enhanced shortcode caching with automatic invalidation
- Code cleanup and removal of deprecated properties
- Security audit and hardening
- Production-ready release

## Support

For support and feature requests, please visit [https://greenberry.ie](https://greenberry.ie).

## License

Parish Core is licensed under the GPL v2 or later.

```text
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
```
