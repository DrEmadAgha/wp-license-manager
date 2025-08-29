# WP License Manager - Architecture Documentation

**Version**: 2.0.0  
**Last Updated**: 2025-08-29

## Overview

WP License Manager (WPLM) is a comprehensive WordPress plugin for managing software license keys with WooCommerce integration, subscription support, and CRM functionality.

## Core Architecture

### Plugin Entry Point
- **File**: `wp-license-manager.php`
- **Pattern**: Singleton
- **Responsibilities**: Plugin initialization, dependency loading, activation/deactivation

### Data Layer

#### Custom Post Types (CPT)
- `wplm_license` - Individual license records
- `wplm_product` - Licensed products/software
- `wplm_subscription` - Subscription records
- `wplm_activity_log` - Activity logging
- `wplm_customer` - Customer management

#### Database Tables
- WordPress post meta for license data
- Custom tables for subscriptions (optional)
- Activity logs stored as custom post type

### Core Modules

#### 1. License Management (`class-admin-manager.php`)
```php
class WPLM_Admin_Manager {
    // Responsibilities:
    // - License CRUD operations
    // - Meta box rendering
    // - Basic AJAX handlers
    // - License key generation
}
```

#### 2. API Layer (`class-api-manager.php`, `class-enhanced-api-manager.php`)
```php
class WPLM_API_Manager {
    // Core API endpoints:
    // - License validation
    // - Activation/deactivation
    // - Update checks
    // - Rate limiting
}

class WPLM_Enhanced_API_Manager {
    // Advanced features:
    // - REST API endpoints
    // - Enhanced security
    // - Comprehensive validation
}
```

#### 3. E-commerce Integration
```php
class WPLM_WooCommerce_Integration {
    // WooCommerce bridge:
    // - Order completion hooks
    // - Product synchronization
    // - Customer account integration
}

class WPLM_Enhanced_Digital_Downloads {
    // EDD-style functionality:
    // - Standalone checkout
    // - Download delivery
    // - Customer accounts
}
```

#### 4. Subscription System
```php
class WPLM_Subscription_Manager {
    // Core subscription logic:
    // - Renewal processing
    // - Expiry handling
    // - Payment integration
}

class WPLM_Built_In_Subscription_System {
    // Internal subscription handling:
    // - Cron jobs
    // - Billing cycles
    // - Dunning management
}
```

#### 5. CRM/ERM System
```php
class WPLM_Customer_Management_System {
    // Customer relationship management:
    // - Customer profiles
    // - Communication tracking
    // - License associations
}

class WPLM_Activity_Logger {
    // Activity tracking:
    // - License events
    // - System activities
    // - Audit trails
}
```

### Public API Surface

#### REST Endpoints
- `GET|POST /wp-json/wplm/v1/validate` - License validation
- `POST /wp-json/wplm/v1/activate` - License activation
- `POST /wp-json/wplm/v1/deactivate` - License deactivation
- `GET /wp-json/wplm/v1/info` - License information
- `GET /wp-json/wplm/v1/update-check` - Update availability

#### AJAX Endpoints
- `wplm_validate` - License validation
- `wplm_activate` - License activation
- `wplm_generate_key` - Generate license key
- `wplm_bulk_operations` - Bulk license operations

#### WordPress Hooks

##### Actions
- `wplm_license_activated` - Fired when license is activated
- `wplm_license_deactivated` - Fired when license is deactivated  
- `wplm_license_expired` - Fired when license expires
- `wplm_subscription_renewed` - Fired on subscription renewal

##### Filters
- `wplm_license_validation_rules` - Modify validation logic
- `wplm_license_key_format` - Customize key generation
- `wplm_api_response` - Modify API responses
- `wplm_email_templates` - Customize email templates

### Security Architecture

#### Authentication
- API key-based authentication for external requests
- WordPress nonces for admin actions
- Capability-based access control

#### Input Validation
- Sanitization using WordPress functions
- Custom validation for license keys
- Rate limiting on API endpoints

#### Output Escaping
- Consistent use of `esc_html()`, `esc_attr()`
- SQL injection prevention with `$wpdb->prepare()`

### Integration Points

#### WooCommerce Hooks
- `woocommerce_order_status_completed` - Generate licenses
- `woocommerce_subscription_status_*` - Handle subscription changes
- `woocommerce_product_*` - Product synchronization

#### WordPress Core Hooks
- `init` - Plugin initialization
- `admin_menu` - Admin interface setup
- `rest_api_init` - REST endpoint registration

## Design Patterns

### Singleton Pattern
- Main plugin class uses singleton for single instance
- Prevents multiple initialization

### Factory Pattern
- License key generation with customizable formats
- Email template creation

### Observer Pattern
- Hook system for extending functionality
- Event-driven architecture for license state changes

## Performance Considerations

### Caching
- Transients for expensive operations
- Option caching for frequently accessed data

### Database Optimization
- Proper indexing on custom fields
- Efficient queries with WP_Query
- Batch processing for bulk operations

### Asset Loading
- Conditional script/style loading
- Minification and concatenation

## Extension Points

### Custom License Validators
```php
add_filter('wplm_license_validation_rules', function($rules) {
    $rules['custom_check'] = 'my_custom_validation';
    return $rules;
});
```

### Custom Email Templates
```php
add_filter('wplm_email_templates', function($templates) {
    $templates['custom_notification'] = 'path/to/template.php';
    return $templates;
});
```

### Custom API Endpoints
```php
add_action('rest_api_init', function() {
    register_rest_route('wplm/v1', '/custom-endpoint', [
        'methods' => 'POST',
        'callback' => 'my_custom_handler',
        'permission_callback' => 'my_permission_check'
    ]);
});
```

## Deployment Architecture

### File Structure
```
wp-license-manager/
├── wp-license-manager.php          # Main plugin file
├── includes/                       # Core classes
│   ├── class-*.php                # Individual class files
│   └── admin/                     # Admin-specific classes
├── assets/                        # Frontend assets
│   ├── js/                       # JavaScript files
│   ├── css/                      # Stylesheets
│   └── images/                   # Images and icons
├── templates/                     # Template files
├── languages/                     # Internationalization files
└── tests/                        # Unit tests
```

### Dependencies
- **WordPress**: 5.0+
- **PHP**: 7.4+
- **Optional**: WooCommerce 4.0+
- **Testing**: PHPUnit 9.5+

## Future Enhancements

### Planned Improvements
1. GraphQL API support
2. Multi-site network support
3. Advanced analytics dashboard
4. Mobile app API
5. Blockchain-based license verification

### Scalability Considerations
1. Database sharding for large installations
2. CDN integration for license validation
3. Redis caching for high-traffic sites
4. Microservices architecture for enterprise deployments