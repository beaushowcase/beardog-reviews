# Beardog Google Reviews Tool 🐕

[![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-blue.svg)](https://wordpress.org/)
[![License](https://img.shields.io/badge/license-GPL--2.0%2B-red.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

```php
use BeardogReviews\DisplayReviews;
$processed_reviews = DisplayReviews::process_reviews_for_display(true); // true = only 5-star reviews
echo "<pre>";print_r($processed_reviews);exit;
```

A powerful WordPress plugin that helps you display and manage Google Reviews on your website.

## 🌟 Features

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

## 📋 Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- Google Place IDs for your businesses

## 💻 Installation

1. Download the plugin zip file
2. Upload to `/wp-content/plugins/beardog-reviews/`
3. Activate through WordPress admin panel
4. Configure via 'Review Settings'

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

## 📝 Changelog

### 1.8.0
- Added API integration
- Implemented Place ID management
- Added configurable sync intervals
- Enhanced error handling
- Updated admin interface
- Added automatic review updates

### 1.7.4
- Legacy version changes

## 📄 License

This project is licensed under the GPL-2.0+ License