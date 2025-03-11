# Beardog Reviews (Repocean Integration) 🐕

[![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-blue.svg)](https://wordpress.org/)
[![License](https://img.shields.io/badge/license-GPL--2.0%2B-red.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

A powerful WordPress plugin that seamlessly integrates with the Repocean API to display and manage Google Reviews on your website.

## 🌟 Features

- **Repocean API Integration**: Fetch and sync Google Reviews automatically
- **Smart Review Management**:
  - Filter and display 5-star reviews
  - Disable/enable individual reviews
  - Custom review text editing
  - Review caching for optimal performance
- **Flexible Display Options**:
  - Multiple businesses support
  - Customizable review layouts
  - Author profile photos and initials
  - Human-readable timestamps
- **Admin Features**:
  - Easy-to-use admin interface
  - Bulk review management
  - Review synchronization scheduling
  - Comprehensive error logging

## 📋 Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- Valid Repocean API key
- Google Place IDs for your businesses

## 💻 Installation

1. Download the plugin zip file
2. Upload to `/wp-content/plugins/beardog-reviews/`
3. Activate through WordPress admin panel
4. Configure via 'Review Settings' > 'API Settings'

## ⚙️ Configuration

### API Setup
1. Navigate to 'Review Settings' > 'API Settings'
2. Enter your Repocean API key
3. Choose sync interval (hourly/daily/weekly)
4. Save changes

### Business Setup
1. Go to 'Review Settings' > 'Business'
2. Click 'Add New Business'
3. Enter business details:
   - Business name
   - Google Place ID
4. Save business

## 🚀 Usage

### Display Reviews in Templates

```php
use BeardogReviews\DisplayReviews;

// Get all reviews (including filtered)
$processed_reviews = DisplayReviews::process_reviews_for_display(true); // true = only 5-star reviews

// Access review data
foreach ($processed_reviews['reviews'] as $review) {
    echo $review['author_full_name'];     // Reviewer name
    echo $review['author_initial'];       // Single initial
    echo $review['author_initial_two'];   // Two initials
    echo $review['review_date'];          // Human-readable date
    echo $review['review_text'];          // Review content
    echo $review['business_term'];        // Business name
}
```

### Shortcode Usage

```php
[beardog_reviews business_id="123" count="5" rating="5"]
```

## 🔧 Advanced Features

### Review Customization
- Custom review text editing
- Review disable/enable toggle
- Review date formatting options
- Author display customization

### API Integration
- Automatic review syncing
- Error handling and retry logic
- Rate limiting protection
- Data validation and sanitization

## 🐛 Debugging

Enable WordPress debug mode in `wp-config.php`:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

Logs are stored in `wp-content/debug.log`

## 📝 Changelog

### 1.8.0
- Added Repocean API integration
- Implemented Place ID management
- Added configurable sync intervals
- Enhanced error handling
- Updated admin interface
- Added automatic review updates

### 1.7.4
- Legacy version changes

## 🤝 Contributing

1. Fork the repository
2. Create feature branch (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -m 'Add amazing feature'`)
4. Push to branch (`git push origin feature/amazing-feature`)
5. Open Pull Request

## 📄 License

This project is licensed under the GPL-2.0+ License - see the [LICENSE](LICENSE) file for details.

## 🌐 Support

For support and feature requests:
- Visit [Beardog Digital](https://beardog.digital/)
- Open an [issue](https://github.com/your-repo/beardog-reviews/issues)
- Email: support@beardog.digital