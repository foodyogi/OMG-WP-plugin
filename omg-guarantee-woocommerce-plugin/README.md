# OM Guarantee for WooCommerce

**Professional social impact certification system for WooCommerce stores**

Transform your WooCommerce store into a force for good with automated charitable donations, blockchain transparency, and professional certification badges.

## 🌟 Features

### **Automated Social Impact**
- **Automatic donation calculation** from WooCommerce orders
- **Product-level control** - enable/disable impact per product
- **Flexible percentage settings** - global or per-product donation rates
- **Real-time impact tracking** with professional dashboards

### **Every.org Integration**
- **Access to 1+ million verified charities** via Every.org API
- **Real-time charity search** with autocomplete functionality
- **Automated donation processing** with batch scheduling
- **Professional charity verification** and impact tracking

### **Blockchain Transparency**
- **Polygon blockchain logging** for donation verification
- **Real PolygonScan links** to verify every transaction
- **Immutable donation records** for complete transparency
- **QR codes** for easy mobile verification

### **Professional Certification**
- **Official OM Guarantee badges** with light/dark themes
- **5 powerful shortcodes** for flexible display options
- **Mobile-responsive design** that works everywhere
- **Customizable impact statements** with real metrics

### **WooCommerce Integration**
- **Product page impact display** showing donation amounts
- **Cart and checkout integration** with impact calculations
- **Order completion processing** with automatic donation queuing
- **Email notifications** with social impact summaries
- **Admin order details** showing donation status

## 🚀 Quick Start

### **Requirements**
- WordPress 5.0 or higher
- WooCommerce 5.0 or higher
- PHP 7.4 or higher
- Every.org API key (free)

### **Installation**
1. **Upload** the plugin ZIP file via WordPress Admin → Plugins → Add New
2. **Activate** the plugin
3. **Navigate** to OM Guarantee in your WordPress admin menu
4. **Get your free API key** from [Every.org Charity API](https://www.every.org/charity-api)
5. **Configure** your settings and select charities
6. **Add shortcodes** to your pages

### **5-Minute Setup**
```
1. Install & activate plugin (2 minutes)
2. Add Every.org API key (1 minute)
3. Select default charity (1 minute)
4. Configure impact percentage (30 seconds)
5. Add certification badge to pages (30 seconds)
```

## 📋 Configuration

### **Global Settings**
- **Impact Percentage**: Default percentage of sales to donate (e.g., 1.5%)
- **Default Charity**: Primary charity for donations
- **Processing Frequency**: How often to process donations (daily/weekly/monthly)
- **Display Options**: Where to show impact information

### **Product Settings**
Each product can override global settings:
- **Enable/Disable Impact**: Turn social impact on/off per product
- **Custom Percentage**: Override global donation percentage
- **Specific Charity**: Choose different charity for this product

### **Every.org Integration**
- **API Key**: Your free Every.org API key
- **Charity Search**: Real-time search through 1+ million charities
- **Donation Processing**: Automated batch processing of donations

### **Blockchain Settings**
- **Enable Logging**: Turn blockchain transparency on/off
- **Network**: Polygon mainnet for low-cost transactions
- **Verification**: Automatic PolygonScan link generation

## 🎨 Shortcodes

### **Certification Badge**
```
[omg_certification_badge]
[omg_certification_badge theme="dark"]
[omg_certification_badge size="large"]
```

### **Impact Counter**
```
[omg_impact_counter]
[omg_impact_counter period="month"]
[omg_impact_counter type="total"]
```

### **Blockchain Verification**
```
[omg_blockchain_verification]
[omg_blockchain_verification limit="10"]
```

### **Customer Impact**
```
[omg_customer_impact]
[omg_customer_impact customer_id="123"]
```

### **Donation Tracker**
```
[omg_donation_tracker]
[omg_donation_tracker period="year"]
```

## 🔧 Advanced Configuration

### **Custom Impact Statements**
```php
// Override impact calculation
add_filter('omg_woo_impact_statement', function($statement, $charity, $amount) {
    return "Your purchase helped provide clean water to 5 families";
}, 10, 3);
```

### **Custom Charity Selection**
```php
// Add custom charity validation
add_filter('omg_woo_validate_charity', function($is_valid, $charity_id) {
    // Your custom validation logic
    return $is_valid;
}, 10, 2);
```

### **Donation Processing Hooks**
```php
// Before donation processing
add_action('omg_woo_before_donation_processing', function($donation) {
    // Your custom logic
});

// After successful donation
add_action('omg_woo_donation_completed', function($donation, $transaction_hash) {
    // Your custom logic
}, 10, 2);
```

## 📊 Analytics & Reporting

### **Admin Dashboard**
- **Total donations processed** with real-time updates
- **Charity breakdown** showing distribution of donations
- **Recent activity** with transaction links
- **Impact metrics** with meaningful statements

### **Donation History**
- **Complete transaction log** with status tracking
- **Blockchain verification** for each donation
- **Order correlation** linking donations to WooCommerce orders
- **Export functionality** for accounting and reporting

### **Impact Reporting**
- **Real impact statements** based on charity data
- **Verified metrics** from blockchain transactions
- **Customer impact tracking** for loyalty programs
- **Automated reporting** for stakeholders

## 🔒 Security & Privacy

### **Data Protection**
- **No sensitive data storage** - API keys encrypted
- **GDPR compliant** - minimal data collection
- **Secure API communication** with SSL/TLS
- **WordPress security standards** followed

### **Blockchain Security**
- **Immutable transaction records** on Polygon
- **Public verification** without exposing private data
- **Low-cost transactions** (typically under $0.01)
- **Decentralized verification** independent of any single party

## 🛠️ Troubleshooting

### **Common Issues**

**Shortcodes showing as text**
- Ensure plugin is activated
- Check if using HTML block (not Code block)
- Clear caching plugins

**API connection errors**
- Verify Every.org API key is correct
- Check internet connectivity
- Ensure API key has proper permissions

**Donations not processing**
- Check WooCommerce order status
- Verify cron jobs are running
- Review donation processing frequency

**Blockchain verification failing**
- Check if blockchain logging is enabled
- Verify Polygon network connectivity
- Review transaction gas settings

### **Debug Mode**
Enable WordPress debug mode to see detailed error logs:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## 🤝 Support

### **Documentation**
- **Complete setup guides** at [omguarantee.com/docs](https://omguarantee.com/docs)
- **Video tutorials** for common tasks
- **API reference** for developers
- **Best practices** for maximum impact

### **Community**
- **Support forum** for questions and discussions
- **Feature requests** and feedback
- **Developer resources** and code examples
- **Case studies** from successful implementations

## 📈 Roadmap

### **Upcoming Features**
- **Shopify integration** for multi-platform support
- **Advanced analytics** with impact visualization
- **Customer impact profiles** for loyalty programs
- **Multi-currency support** for global stores
- **API webhooks** for third-party integrations

### **Version History**
- **v1.0.0** - Initial release with core functionality
- **v1.1.0** - Enhanced blockchain integration (planned)
- **v1.2.0** - Advanced reporting features (planned)
- **v2.0.0** - Multi-platform support (planned)

## 📄 License

This plugin is licensed under the GPL v2 or later.

## 🙏 Credits

- **OM Guarantee Inc.** - Plugin development and certification system
- **Every.org** - Charity database and donation processing API
- **Polygon Network** - Blockchain infrastructure for transparency
- **WooCommerce** - E-commerce platform integration

---

**Transform your business into a force for good with OM Guarantee for WooCommerce.**

*Making social impact as easy as making a sale.*

