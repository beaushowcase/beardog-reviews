# Awesome Google Reviews (Repocean Integration)

A WordPress plugin to display Google Reviews on your website, now with Repocean API integration.

## Features

- Fetch Google Reviews using Repocean API
- Automatic review updates (hourly/daily/weekly)
- Beautiful review display with customization options
- Review caching for improved performance
- Error handling and logging
- Easy-to-use admin interface

## Installation

1. Upload the plugin files to the `/wp-content/plugins/awesome-google-review` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to 'Review Settings' > 'API Settings' to configure your Repocean API key
4. Add businesses and their Google Place IDs through the 'Business' menu

## Configuration

### API Settings
1. Navigate to 'Review Settings' > 'API Settings'
2. Enter your Repocean API key
3. Select your preferred review refresh interval (hourly/daily/weekly)

### Adding a Business
1. Go to 'Review Settings' > 'Business'
2. Click 'Add New Business'
3. Enter the business name
4. Enter the Google Place ID for the business
5. Save the business

## Usage

### Shortcode
Use the shortcode to display reviews on any page or post:
```
[awesome_google_reviews business="business-name"]
```

Optional parameters:
- `count`: Number of reviews to display (default: 5)
- `rating`: Minimum rating to display (1-5)
- `sort`: Sort order ('recent' or 'rating')

Example:
```
[awesome_google_reviews business="my-business" count="10" rating="4" sort="recent"]
```

## Error Handling

The plugin includes comprehensive error handling:
- API connection issues
- Invalid Place IDs
- Rate limiting
- Data parsing errors

Errors are logged to WordPress debug log when WP_DEBUG is enabled.

## Changelog

### 1.8.0
- Added Repocean API integration
- Added Place ID field for businesses
- Added configurable review refresh intervals
- Added improved error handling and logging
- Updated admin interface with API settings
- Added automatic review updates via WordPress cron

### 1.7.4 (Previous version)
- Previous version changes...

## Support

For support, please visit https://beardog.digital/

## License

GPL-2.0+