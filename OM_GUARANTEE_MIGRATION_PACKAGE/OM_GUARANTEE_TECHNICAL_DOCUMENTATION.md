# OM Guarantee for WooCommerce - Technical Documentation

**Version:** 3.0.8  
**Last Updated:** August 2025  
**Developer Documentation for Migration to Cursor**

---

## Architecture Overview

### Plugin Structure
```
omg-woocommerce-plugin/
├── omg-woocommerce-plugin.php          # Main plugin file
├── README.md                           # Plugin documentation
├── assets/                             # Static assets
│   ├── css/                           # Stylesheets
│   │   ├── admin.css                  # Admin interface styles
│   │   ├── frontend.css               # Frontend styles
│   │   └── logo-padding-final-fixes.css # Layout fixes
│   ├── js/                            # JavaScript files
│   │   ├── admin.js                   # Admin functionality
│   │   └── frontend.js                # Frontend interactions
│   └── images/                        # Plugin images
│       ├── OMGcertificate2022.png     # Certification badge
│       └── newOMGincLogo2022HorizontalWEB.jpg # OM Guarantee logo
├── includes/                          # PHP class files
│   ├── class-omg-woo-admin.php        # Admin interface
│   ├── class-omg-woo-frontend.php     # Frontend display
│   ├── class-omg-woo-woocommerce.php  # WooCommerce integration
│   ├── class-omg-woo-every-org.php    # Every.org API integration
│   └── class-omg-woo-blockchain.php   # Blockchain functionality
├── languages/                         # Translation files (ready for i18n)
└── templates/                         # Template files (future use)
```

### Core Classes & Responsibilities

#### 1. Main Plugin Class (`omg-woocommerce-plugin.php`)
**Purpose:** Plugin initialization, hooks registration, and core functionality

**Key Methods:**
- `__construct()` - Plugin initialization
- `init()` - Hook registration and setup
- `admin_page()` - Main admin dashboard
- `report_page()` - Impact reporting interface
- `registration_page()` - OM Guarantee registration
- Shortcode rendering methods

**Hooks Registered:**
- `admin_menu` - Admin menu creation
- `admin_enqueue_scripts` - Asset loading
- `wp_enqueue_scripts` - Frontend assets
- `woocommerce_thankyou` - Post-purchase processing

#### 2. Admin Interface (`class-omg-woo-admin.php`)
**Purpose:** WordPress admin interface management

**Responsibilities:**
- Admin menu creation and management
- Settings page rendering
- Form handling and validation
- Admin-specific styling and scripts

#### 3. Frontend Display (`class-omg-woo-frontend.php`)
**Purpose:** Customer-facing functionality

**Responsibilities:**
- Shortcode rendering
- Frontend asset management
- Customer impact display
- Checkout integration display

#### 4. WooCommerce Integration (`class-omg-woo-woocommerce.php`)
**Purpose:** Deep integration with WooCommerce

**Responsibilities:**
- Order processing hooks
- Product meta management
- Checkout flow integration
- Cart and order calculations

#### 5. Every.org API (`class-omg-woo-every-org.php`)
**Purpose:** Charity data and donation processing

**Responsibilities:**
- API authentication and requests
- Charity search functionality
- Donation processing
- Error handling and retries

#### 6. Blockchain Integration (`class-omg-woo-blockchain.php`)
**Purpose:** Blockchain transparency features

**Responsibilities:**
- Transaction simulation (current)
- PolygonScan link generation
- Future: Real blockchain integration
- Verification link management

---


## API Integrations

### Every.org API Integration
**Base URL:** `https://partners.every.org/v0.2/`  
**Authentication:** API Key (Bearer token)  
**Rate Limits:** 1000 requests/hour per API key  

#### Key Endpoints Used:

##### 1. Charity Search
```http
GET /search/{query}
Authorization: Bearer {api_key}
```

**Parameters:**
- `query` (string): Search term for charity name
- `limit` (int): Number of results (default: 10)
- `verified` (boolean): Only verified charities (default: true)

**Response:**
```json
{
  "nonprofits": [
    {
      "id": "charity-id",
      "name": "Charity Name",
      "description": "Charity description",
      "verified": true,
      "website": "https://charity.org",
      "logo": "https://logo-url.jpg"
    }
  ]
}
```

##### 2. Donation Processing
```http
POST /donations
Authorization: Bearer {api_key}
Content-Type: application/json
```

**Payload:**
```json
{
  "nonprofit_id": "charity-id",
  "amount": 25.00,
  "currency": "USD",
  "donor_name": "Business Name",
  "donor_email": "business@example.com",
  "message": "Donation from customer purchase"
}
```

**Response:**
```json
{
  "donation_id": "donation-uuid",
  "status": "completed",
  "receipt_url": "https://receipt-url",
  "tax_receipt": "https://tax-receipt-url"
}
```

### Blockchain API (Future Implementation)
**Service:** Polygon Network  
**Provider:** OM Guarantee Managed Service  
**Authentication:** Subscription-based API key  

#### Planned Endpoints:

##### 1. Submit Transaction
```http
POST /api/v1/transactions
Authorization: Bearer {subscription_key}
```

##### 2. Verify Transaction
```http
GET /api/v1/transactions/{transaction_id}
Authorization: Bearer {subscription_key}
```

---

## Database Schema

### WordPress Options Used
The plugin stores configuration in WordPress options table:

```sql
-- Core Settings
wp_options.option_name = 'omg_woo_enabled' (boolean)
wp_options.option_name = 'omg_woo_business_name' (string)
wp_options.option_name = 'omg_woo_every_org_api_key' (string, encrypted)
wp_options.option_name = 'omg_woo_default_charity' (string)
wp_options.option_name = 'omg_woo_global_percentage' (float)
wp_options.option_name = 'omg_woo_omg_profile_url' (string)

-- Analytics Data
wp_options.option_name = 'omg_woo_total_donated' (float)
wp_options.option_name = 'omg_woo_total_orders' (int)
wp_options.option_name = 'omg_woo_blockchain_transactions' (int)

-- Cache Data
wp_options.option_name = 'omg_woo_charity_cache' (serialized array)
wp_options.option_name = 'omg_woo_cache_timestamp' (timestamp)
```

### Product Meta Fields
```sql
-- Product-specific settings stored in wp_postmeta
meta_key = '_omg_impact_enabled' (boolean)
meta_key = '_omg_impact_percentage' (float)
meta_key = '_omg_custom_charity' (string)
meta_key = '_omg_impact_description' (text)
```

### Order Meta Fields
```sql
-- Order tracking stored in wp_postmeta (WooCommerce orders)
meta_key = '_omg_donation_amount' (float)
meta_key = '_omg_charity_name' (string)
meta_key = '_omg_donation_id' (string)
meta_key = '_omg_blockchain_tx' (string)
meta_key = '_omg_verification_url' (string)
```

### Future Database Tables
For advanced features, custom tables may be needed:

```sql
-- Donation tracking table (future)
CREATE TABLE wp_omg_donations (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    order_id bigint(20) NOT NULL,
    charity_id varchar(255) NOT NULL,
    amount decimal(10,2) NOT NULL,
    currency varchar(3) DEFAULT 'USD',
    donation_id varchar(255),
    blockchain_tx varchar(255),
    status varchar(50) DEFAULT 'pending',
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY order_id (order_id),
    KEY charity_id (charity_id)
);

-- Charity cache table (future)
CREATE TABLE wp_omg_charities (
    id varchar(255) NOT NULL,
    name varchar(255) NOT NULL,
    description text,
    website varchar(255),
    logo varchar(255),
    verified boolean DEFAULT false,
    cached_at datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);
```

---

## Code Architecture Patterns

### WordPress Plugin Standards
- **Namespace:** All functions prefixed with `omg_woo_`
- **Security:** Nonce verification, input sanitization, output escaping
- **Hooks:** Proper use of WordPress action and filter hooks
- **Coding Standards:** WordPress PHP Coding Standards compliance

### Object-Oriented Design
```php
// Main plugin class structure
class OMG_WooCommerce_Plugin {
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init();
    }
    
    private function init() {
        // Hook registration
        add_action('init', array($this, 'load_textdomain'));
        add_action('admin_menu', array($this, 'admin_menu'));
        // ... more hooks
    }
}
```

### Error Handling Pattern
```php
// Consistent error handling throughout
try {
    $result = $this->api_call();
    if (is_wp_error($result)) {
        error_log('OMG WooCommerce: ' . $result->get_error_message());
        return false;
    }
    return $result;
} catch (Exception $e) {
    error_log('OMG WooCommerce Exception: ' . $e->getMessage());
    return false;
}
```

### AJAX Pattern
```php
// AJAX handling pattern
add_action('wp_ajax_omg_search_charities', array($this, 'ajax_search_charities'));
add_action('wp_ajax_nopriv_omg_search_charities', array($this, 'ajax_search_charities'));

public function ajax_search_charities() {
    check_ajax_referer('omg_admin_nonce', 'nonce');
    
    $query = sanitize_text_field($_POST['query']);
    $results = $this->search_charities($query);
    
    wp_send_json_success($results);
}
```

---


## Dependencies & Requirements

### PHP Dependencies
```json
{
  "require": {
    "php": ">=7.4",
    "wordpress": ">=5.0",
    "woocommerce": ">=4.0"
  },
  "suggest": {
    "php": ">=8.0",
    "wordpress": ">=6.0",
    "woocommerce": ">=7.0"
  }
}
```

### External Services
1. **Every.org API**
   - Purpose: Charity data and donation processing
   - Dependency Level: Critical
   - Fallback: Local charity database (future)
   - Rate Limits: 1000 requests/hour

2. **PolygonScan API**
   - Purpose: Blockchain verification links
   - Dependency Level: Optional
   - Fallback: Generic blockchain explorer

3. **OM Guarantee Managed Service** (Future)
   - Purpose: Real blockchain transactions
   - Dependency Level: Optional (subscription feature)
   - Fallback: Simulation mode

### WordPress Plugin Dependencies
- **Required:** WooCommerce (tested with 4.0+)
- **Conflicts:** Other donation plugins may cause conflicts
- **Recommendations:** Use with caching plugins for performance

---

## Security Implementation

### Data Protection
```php
// API key encryption
function omg_encrypt_api_key($key) {
    if (!function_exists('openssl_encrypt')) {
        return base64_encode($key); // Fallback
    }
    
    $cipher = 'AES-256-CBC';
    $iv = openssl_random_pseudo_bytes(16);
    $encrypted = openssl_encrypt($key, $cipher, AUTH_SALT, 0, $iv);
    
    return base64_encode($iv . $encrypted);
}

// Input sanitization
function omg_sanitize_percentage($value) {
    $value = floatval($value);
    return max(0, min(100, $value)); // Clamp between 0-100
}
```

### Nonce Verification
```php
// All admin forms use nonces
wp_nonce_field('omg_admin_action', 'omg_admin_nonce');

// Verification in processing
if (!wp_verify_nonce($_POST['omg_admin_nonce'], 'omg_admin_action')) {
    wp_die('Security check failed');
}
```

### SQL Injection Prevention
```php
// Using WordPress prepared statements
$wpdb->prepare(
    "SELECT * FROM {$wpdb->postmeta} WHERE meta_key = %s AND post_id = %d",
    '_omg_impact_enabled',
    $product_id
);
```

### XSS Protection
```php
// Output escaping
echo esc_html($business_name);
echo esc_url($verification_url);
echo esc_attr($css_class);
```

---

## Performance Optimization

### Caching Strategy
```php
// Charity data caching
function omg_get_cached_charities($query) {
    $cache_key = 'omg_charities_' . md5($query);
    $cached = get_transient($cache_key);
    
    if (false === $cached) {
        $cached = $this->api_search_charities($query);
        set_transient($cache_key, $cached, HOUR_IN_SECONDS);
    }
    
    return $cached;
}
```

### Database Optimization
```php
// Efficient meta queries
$products = get_posts(array(
    'post_type' => 'product',
    'meta_query' => array(
        array(
            'key' => '_omg_impact_enabled',
            'value' => '1',
            'compare' => '='
        )
    ),
    'fields' => 'ids' // Only get IDs for performance
));
```

### Asset Optimization
```php
// Conditional asset loading
function omg_enqueue_admin_assets($hook) {
    if (strpos($hook, 'omg-guarantee') === false) {
        return; // Only load on plugin pages
    }
    
    wp_enqueue_style('omg-admin-css', OMG_WOO_PLUGIN_URL . 'assets/css/admin.css');
    wp_enqueue_script('omg-admin-js', OMG_WOO_PLUGIN_URL . 'assets/js/admin.js');
}
```

---

## Testing Framework

### Unit Testing Structure
```php
// PHPUnit test structure (to be implemented)
class OMG_WooCommerce_Test extends WP_UnitTestCase {
    
    public function setUp() {
        parent::setUp();
        // Test setup
    }
    
    public function test_charity_search() {
        // Test charity search functionality
    }
    
    public function test_donation_calculation() {
        // Test donation amount calculations
    }
    
    public function test_shortcode_rendering() {
        // Test shortcode output
    }
}
```

### Integration Testing
- WooCommerce order processing
- Every.org API integration
- WordPress admin interface
- Frontend shortcode display

### Manual Testing Checklist
1. **Installation & Activation**
   - Fresh WordPress installation
   - WooCommerce compatibility
   - Plugin activation without errors

2. **Configuration**
   - API key validation
   - Settings save/load
   - Charity search functionality

3. **Order Processing**
   - Donation calculation accuracy
   - API call success/failure handling
   - Order meta data storage

4. **Frontend Display**
   - Shortcode rendering
   - Mobile responsiveness
   - Theme compatibility

---

## Deployment & Distribution

### WordPress Plugin Repository
```php
// Plugin header for WordPress.org
/*
Plugin Name: OM Guarantee for WooCommerce
Plugin URI: https://omguarantee.com/woocommerce
Description: Automate social impact for your WooCommerce store with verified charity donations and blockchain transparency.
Version: 3.0.8
Author: OM Guarantee
Author URI: https://omguarantee.com
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: omg-woocommerce
Domain Path: /languages
Requires at least: 5.0
Tested up to: 6.3
Requires PHP: 7.4
WC requires at least: 4.0
WC tested up to: 8.0
*/
```

### Version Control
- **Git Repository:** Private repository for development
- **Branching Strategy:** GitFlow (main, develop, feature branches)
- **Release Tags:** Semantic versioning (v3.0.8)
- **Changelog:** Detailed changelog for each release

### Build Process
```bash
# Build script for distribution
#!/bin/bash
# Remove development files
rm -rf .git .gitignore package.json webpack.config.js

# Optimize assets
npm run build

# Create distribution zip
zip -r omg-guarantee-for-woocommerce-v3.0.8.zip . -x "*.DS_Store" "node_modules/*"
```

### Environment Configuration
```php
// Environment-specific settings
if (defined('WP_DEBUG') && WP_DEBUG) {
    define('OMG_WOO_DEBUG', true);
    define('OMG_WOO_API_TIMEOUT', 30); // Longer timeout for debugging
} else {
    define('OMG_WOO_DEBUG', false);
    define('OMG_WOO_API_TIMEOUT', 10);
}
```

---

## Migration Notes for Cursor Development

### Code Quality
- **PSR Standards:** Code follows PSR-12 coding standards
- **Documentation:** Comprehensive inline documentation
- **Type Hints:** PHP 7.4+ type hints used throughout
- **Error Handling:** Consistent error handling patterns

### Development Environment Setup
1. **Local WordPress:** Use Local by Flywheel or similar
2. **WooCommerce:** Install latest version
3. **PHP Version:** 8.0+ recommended
4. **Debugging:** Enable WP_DEBUG and WP_DEBUG_LOG

### Key Files to Review First
1. `omg-woocommerce-plugin.php` - Main plugin logic
2. `assets/css/admin.css` - Admin styling (needs improvement)
3. Shortcode rendering functions - Need complete redesign
4. Every.org API integration - Core functionality

### Immediate Development Priorities
1. **Shortcode Redesign:** Professional, compact designs
2. **Mobile Optimization:** Responsive admin interface
3. **Error Handling:** Improved user feedback
4. **Performance:** Optimize API calls and caching

*This technical documentation provides the foundation for continuing development in Cursor. All code follows WordPress standards and is ready for professional development.*

