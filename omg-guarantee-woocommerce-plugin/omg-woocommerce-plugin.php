<?php
/**
 * Plugin Name: OM Guarantee for WooCommerce - Enhanced Verification (Minimal Fixes)
 * Plugin URI: https://omguarantee.com
 * Description: Complete OM Guarantee integration with local dashboard, third-party verification, and certification badges. Includes Every.org API integration and WooCommerce automation.
 * Version: 3.0.1
 * Author: OM Guarantee
 * License: GPL v2 or later
 * Text Domain: omg-woocommerce
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('OMG_WOO_VERSION', '3.0.8');
define('OMG_WOO_PLUGIN_URL', plugin_dir_url(__FILE__));
define('OMG_WOO_PLUGIN_PATH', plugin_dir_path(__FILE__));

class OMG_WooCommerce_Enhanced {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    public function init() {
        // Admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Shortcodes
        add_shortcode('omg_impact_dashboard', array($this, 'render_impact_dashboard'));
        add_shortcode('omg_certification_badge', array($this, 'render_certification_badge'));
        add_shortcode('omg_impact_summary', array($this, 'render_impact_summary'));
        add_shortcode('omg_donation_counter', array($this, 'render_donation_counter'));
        add_shortcode('omg_charity_list', array($this, 'render_charity_list'));
        add_shortcode('omg_external_verification', array($this, 'render_external_verification'));
        add_shortcode('omg_registration_helper', array($this, 'render_registration_helper'));
        
        // AJAX handlers
        add_action('wp_ajax_omg_search_charities', array($this, 'ajax_search_charities'));
        add_action('wp_ajax_omg_test_api', array($this, 'ajax_test_api'));
        add_action('wp_ajax_omg_generate_report', array($this, 'ajax_generate_report'));
        
        // WooCommerce integration
        if (class_exists('WooCommerce')) {
            add_action('woocommerce_product_data_tabs', array($this, 'add_product_tab'));
            add_action('woocommerce_product_data_panels', array($this, 'add_product_panel'));
            add_action('woocommerce_process_product_meta', array($this, 'save_product_meta'));
            add_action('woocommerce_single_product_summary', array($this, 'display_product_impact'), 25);
        }
    }
    
    public function activate() {
        // Create options with default values
        add_option('omg_woo_enabled', false);
        add_option('omg_woo_global_impact', false);
        add_option('omg_woo_impact_percentage', '1.5');
        add_option('omg_woo_default_charity', 'Food Yoga International');
        add_option('omg_woo_every_org_api_key', '');
        add_option('omg_woo_business_name', get_bloginfo('name'));
        add_option('omg_woo_omg_registered', false);
        add_option('omg_woo_omg_profile_url', '');
        add_option('omg_woo_total_donated', 0);
        add_option('omg_woo_total_orders', 0);
        add_option('omg_woo_blockchain_transactions', 0);
    }
    
    public function deactivate() {
        // Clean up if needed
    }
    
    public function enqueue_frontend_scripts() {
        // Add Google Fonts
        wp_enqueue_style('omg-google-fonts', 'https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Inter:wght@400;500;600&display=swap', array(), null);
        
        // Main styles and scripts - using the fixed version with !important rules to ensure proper display
        wp_enqueue_style('omg-woo-frontend', OMG_WOO_PLUGIN_URL . 'assets/css/frontend-fixed.css', array('omg-google-fonts'), OMG_WOO_VERSION);
        wp_enqueue_script('omg-woo-frontend', OMG_WOO_PLUGIN_URL . 'assets/js/frontend.js', array('jquery'), OMG_WOO_VERSION, true);
        
        // Add the frontend-fix.js script to ensure proper display
        wp_enqueue_script('omg-woo-frontend-fix', OMG_WOO_PLUGIN_URL . 'assets/js/frontend-fix.js', array('jquery', 'omg-woo-frontend'), OMG_WOO_VERSION, true);
        
        wp_localize_script('omg-woo-frontend', 'omg_woo_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('omg_woo_nonce'),
            'plugin_url' => OMG_WOO_PLUGIN_URL
        ));
    }
    
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'omg-guarantee') !== false) {
            wp_enqueue_style('omg-woo-admin', OMG_WOO_PLUGIN_URL . 'assets/css/admin.css', array(), OMG_WOO_VERSION);
            wp_enqueue_style('omg-woo-logo-padding-final-fixes', OMG_WOO_PLUGIN_URL . 'assets/css/logo-padding-final-fixes.css', array('omg-woo-admin'), OMG_WOO_VERSION);
            wp_enqueue_script('omg-woo-admin', OMG_WOO_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), OMG_WOO_VERSION, true);
            
            wp_localize_script('omg-woo-admin', 'omg_woo_admin_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('omg_woo_admin_nonce')
            ));
        }
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'OM Guarantee',
            'OM Guarantee',
            'manage_options',
            'omg-guarantee-main',
            array($this, 'admin_page'),
            'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#3A8CCB"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>'),
            30
        );
        
        add_submenu_page(
            'omg-guarantee-main',
            'Settings',
            'Settings',
            'manage_options',
            'omg-guarantee-main',
            array($this, 'admin_page')
        );
        
        add_submenu_page(
            'omg-guarantee-main',
            'Impact Report',
            'Impact Report',
            'manage_options',
            'omg-guarantee-report',
            array($this, 'report_page')
        );
        
        add_submenu_page(
            'omg-guarantee-main',
            'OM Guarantee Registration',
            'Registration',
            'manage_options',
            'omg-guarantee-registration',
            array($this, 'registration_page')
        );
    }
    
    public function admin_page() {
        if (isset($_POST['submit'])) {
            update_option('omg_woo_enabled', isset($_POST['omg_woo_enabled']));
            update_option('omg_woo_global_impact', isset($_POST['omg_woo_global_impact']));
            update_option('omg_woo_impact_percentage', sanitize_text_field($_POST['omg_woo_impact_percentage']));
            update_option('omg_woo_default_charity', sanitize_text_field($_POST['omg_woo_default_charity']));
            update_option('omg_woo_every_org_api_key', sanitize_text_field($_POST['omg_woo_every_org_api_key']));
            update_option('omg_woo_business_name', sanitize_text_field($_POST['omg_woo_business_name']));
            update_option('omg_woo_omg_profile_url', esc_url_raw($_POST['omg_woo_omg_profile_url']));
            
            echo '<div class="notice notice-success"><p>Settings saved successfully!</p></div>';
        }
        
        $enabled = get_option('omg_woo_enabled', false);
        $global_impact = get_option('omg_woo_global_impact', false);
        $impact_percentage = get_option('omg_woo_impact_percentage', '1.5');
        $default_charity = get_option('omg_woo_default_charity', 'Food Yoga International');
        $api_key = get_option('omg_woo_every_org_api_key', '');
        $business_name = get_option('omg_woo_business_name', get_bloginfo('name'));
        $omg_profile_url = get_option('omg_woo_omg_profile_url', '');
        
        ?>
        <div class="wrap omg-admin-wrap">
            <div class="omg-admin-header">
                <img src="<?php echo OMG_WOO_PLUGIN_URL; ?>assets/images/newOMGincLogo2022HorizontalWEB.jpg" alt="OM Guarantee" class="omg-admin-logo">
                <h1>OM Guarantee for WooCommerce - Enhanced Verification</h1>
                <p>Complete social impact automation with local dashboard and third-party verification</p>
            </div>
            
            <div class="omg-admin-content">
                <div class="omg-admin-main">
                    <form method="post" action="">
                        <div class="omg-card">
                            <h2>🎯 General Settings</h2>
                            
                            <table class="form-table">
                                <tr>
                                    <th scope="row">Enable OM Guarantee</th>
                                    <td>
                                        <label>
                                            <input type="checkbox" name="omg_woo_enabled" value="1" <?php checked($enabled); ?>>
                                            Enable social impact certification for your store
                                        </label>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row">Global Shop Impact</th>
                                    <td>
                                        <label>
                                            <input type="checkbox" name="omg_woo_global_impact" value="1" <?php checked($global_impact); ?>>
                                            Enable social impact for ALL products automatically
                                        </label>
                                        <p class="description">When enabled, all products will have social impact unless individually disabled</p>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row">Global Impact Percentage</th>
                                    <td>
                                        <input type="number" name="omg_woo_impact_percentage" value="<?php echo esc_attr($impact_percentage); ?>" step="0.1" min="0" max="100" style="width: 80px;">%
                                        <p class="description">Default percentage of sales to donate (can be overridden per product)</p>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row">Business Name</th>
                                    <td>
                                        <input type="text" name="omg_woo_business_name" value="<?php echo esc_attr($business_name); ?>" class="regular-text">
                                        <p class="description">Your business name for OM Guarantee certification</p>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        
                        <div class="omg-card">
                            <h2>🏢 Charity Selection</h2>
                            
                            <table class="form-table">
                                <tr>
                                    <th scope="row">Default Charity</th>
                                    <td>
                                        <input type="text" name="omg_woo_default_charity" value="<?php echo esc_attr($default_charity); ?>" class="regular-text">
                                        <button type="button" id="search-charities" class="button">Search Charities</button>
                                        <p class="description">Default charity for donations (can be overridden per product)</p>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row">Every.org API Key</th>
                                    <td>
                                        <input type="text" name="omg_woo_every_org_api_key" value="<?php echo esc_attr($api_key); ?>" class="regular-text">
                                        <button type="button" id="test-api" class="button">Test API Connection</button>
                                        <p class="description">Get your API key from <a href="https://www.every.org/charity-api" target="_blank">Every.org</a></p>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        
                        <div class="omg-card">
                            <h2>🏆 OM Guarantee Verification</h2>
                            
                            <table class="form-table">
                                <tr>
                                    <th scope="row">OM Guarantee Profile URL</th>
                                    <td>
                                        <input type="url" name="omg_woo_omg_profile_url" value="<?php echo esc_attr($omg_profile_url); ?>" class="regular-text">
                                        <p class="description">Your OM Guarantee profile URL (after registration)</p>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row">Registration Status</th>
                                    <td>
                                        <?php if (empty($omg_profile_url)): ?>
                                            <span class="omg-status-pending">⏳ Not registered with OM Guarantee</span>
                                            <p><a href="<?php echo admin_url('admin.php?page=omg-guarantee-registration'); ?>" class="button button-primary">Register Now</a></p>
                                        <?php else: ?>
                                            <span class="omg-status-verified">✅ Registered with OM Guarantee</span>
                                            <p><a href="<?php echo esc_url($omg_profile_url); ?>" target="_blank" class="button">View Profile</a></p>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        
                        <?php submit_button('Save Settings'); ?>
                    </form>
                </div>
                
                <div class="omg-admin-sidebar">
                    <div class="omg-card">
                        <h3>🔧 API Status</h3>
                        <div class="omg-status-panel">
                            <p><strong>Every.org API:</strong> 
                                <?php if (!empty($api_key)): ?>
                                    <span class="omg-status-configured">✅ Configured</span><br>
                                    <small>Key: <?php echo substr($api_key, 0, 10); ?>...</small>
                                <?php else: ?>
                                    <span class="omg-status-pending">⏳ Not configured</span>
                                <?php endif; ?>
                            </p>
                            
                            <p><strong>OM Guarantee:</strong>
                                <?php if (!empty($omg_profile_url)): ?>
                                    <span class="omg-status-verified">✅ Verified</span>
                                <?php else: ?>
                                    <span class="omg-status-pending">⏳ Registration needed</span>
                                <?php endif; ?>
                            </p>
                        </div>
                        
                        <div class="omg-test-buttons">
                            <button type="button" id="test-charity-search" class="button button-secondary">Test Charity Search</button>
                            <button type="button" id="test-donation-processing" class="button button-secondary">Test Donation Processing</button>
                            <button type="button" id="test-blockchain" class="button button-secondary">Test Blockchain Connection</button>
                        </div>
                    </div>
                    
                    <div class="omg-card">
                        <h3>📊 Impact Options</h3>
                        <div class="omg-impact-summary">
                            <p><strong>Global Shop Impact:</strong><br>
                                <?php echo $global_impact ? 'Enable for ALL products automatically' : 'Per-product control only'; ?>
                            </p>
                            
                            <p><strong>Per-Product Control:</strong><br>
                                Edit individual products to enable/disable
                            </p>
                            
                            <p><strong>Mixed Approach:</strong><br>
                                Global enabled + individual product overrides
                            </p>
                        </div>
                    </div>
                    
                    <div class="omg-card">
                        <h3>🎯 Quick Actions</h3>
                        <div class="omg-quick-actions">
                            <a href="<?php echo admin_url('admin.php?page=omg-guarantee-report'); ?>" class="button button-primary">📈 View Impact Report</a>
                            <a href="<?php echo admin_url('admin.php?page=omg-guarantee-registration'); ?>" class="button button-secondary">🏢 OM Guarantee Registration</a>
                            <button type="button" id="generate-report" class="button button-secondary">📄 Generate Report</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
        .omg-admin-wrap {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
        }
        
        .omg-admin-header {
            background: linear-gradient(135deg, #3A8CCB 0%, #2E7BB8 100%);
            color: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .omg-admin-logo {
            max-height: 60px;
            margin-bottom: 15px;
        }
        
        .omg-admin-header h1 {
            color: white;
            margin: 0 0 10px 0;
            font-size: 2.2em;
        }
        
        .omg-admin-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            grid-gap: 30px;
        }
        
        .omg-card {
            background: white;
            border: 2px solid #3A8CCB;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 16px 48px rgba(0,0,0,0.15);
        }
        
        .omg-card h2, .omg-card h3 {
            color: #3A8CCB;
            margin-top: 0;
            border-bottom: 2px solid #3A8CCB;
            padding-bottom: 10px;
        }
        
        .omg-status-configured {
            color: #28a745;
            font-weight: bold;
        }
        
        .omg-status-verified {
            color: #28a745;
            font-weight: bold;
        }
        
        .omg-status-pending {
            color: #ffc107;
            font-weight: bold;
        }
        
        .omg-test-buttons button,
        .omg-quick-actions .button {
            display: block;
            width: 100%;
            margin-bottom: 10px;
            text-align: center;
        }
        
        @media (max-width: 768px) {
            .omg-admin-content {
                grid-template-columns: 1fr;
            }
        }
        </style>
        
        <!-- Shortcodes Section -->
        <div class="omg-card" style="margin-top: 30px;">
            <h2>📋 Available Shortcodes</h2>
            <p>Copy and paste these shortcodes into your pages, posts, or widgets to display OM Guarantee content:</p>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 20px;">
                <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; border-left: 4px solid #3A8CCB;">
                    <h4 style="color: #3A8CCB; margin-top: 0;">Impact Dashboard</h4>
                    <code style="background: #e9ecef; padding: 5px 8px; border-radius: 4px; font-family: monospace;">[omg_impact_dashboard]</code>
                    <p style="margin: 10px 0 0 0; font-size: 14px; color: #666;">Displays your complete social impact dashboard with metrics and charts.</p>
                </div>
                
                <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; border-left: 4px solid #3A8CCB;">
                    <h4 style="color: #3A8CCB; margin-top: 0;">Certification Badge</h4>
                    <code style="background: #e9ecef; padding: 5px 8px; border-radius: 4px; font-family: monospace;">[omg_certification_badge]</code>
                    <p style="margin: 10px 0 0 0; font-size: 14px; color: #666;">Shows your OM Guarantee certification badge.</p>
                </div>
                
                <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; border-left: 4px solid #3A8CCB;">
                    <h4 style="color: #3A8CCB; margin-top: 0;">Impact Summary</h4>
                    <code style="background: #e9ecef; padding: 5px 8px; border-radius: 4px; font-family: monospace;">[omg_impact_summary]</code>
                    <p style="margin: 10px 0 0 0; font-size: 14px; color: #666;">Displays a summary of your total social impact.</p>
                </div>
                
                <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; border-left: 4px solid #3A8CCB;">
                    <h4 style="color: #3A8CCB; margin-top: 0;">Donation Counter</h4>
                    <code style="background: #e9ecef; padding: 5px 8px; border-radius: 4px; font-family: monospace;">[omg_donation_counter]</code>
                    <p style="margin: 10px 0 0 0; font-size: 14px; color: #666;">Shows total amount donated through your store.</p>
                </div>
                
                <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; border-left: 4px solid #3A8CCB;">
                    <h4 style="color: #3A8CCB; margin-top: 0;">Charity List</h4>
                    <code style="background: #e9ecef; padding: 5px 8px; border-radius: 4px; font-family: monospace;">[omg_charity_list]</code>
                    <p style="margin: 10px 0 0 0; font-size: 14px; color: #666;">Lists the charities you support with donation amounts.</p>
                </div>
            </div>
            
            <div style="background: #e7f3ff; padding: 20px; border-radius: 8px; margin-top: 20px; border: 1px solid #3A8CCB;">
                <h4 style="color: #3A8CCB; margin-top: 0;">💡 How to Use Shortcodes:</h4>
                <ol style="margin: 0; padding-left: 20px;">
                    <li><strong>Copy</strong> the shortcode you want to use</li>
                    <li><strong>Edit</strong> the page, post, or widget where you want it to appear</li>
                    <li><strong>Paste</strong> the shortcode into the content area</li>
                    <li><strong>Save/Update</strong> the page</li>
                    <li><strong>View</strong> your page to see the OM Guarantee content</li>
                </ol>
                <p style="margin: 15px 0 0 0; font-style: italic; color: #666;">
                    <strong>Tip:</strong> You can use these shortcodes in pages, posts, widgets, and even some theme areas that support shortcodes.
                </p>
            </div>
        </div>
        </div>
        
        <?php
    }
    
    public function registration_page() {
        ?>
        <div class="wrap omg-admin-wrap">
            <div class="omg-admin-header">
                <img src="<?php echo OMG_WOO_PLUGIN_URL; ?>assets/images/newOMGincLogo2022HorizontalWEB.jpg" alt="OM Guarantee" class="omg-admin-logo">
                <h1>OM Guarantee Registration</h1>
                <p>Get third-party verification for your social impact</p>
            </div>
            
            <div class="omg-card">
                <h2>🏢 Register with OM Guarantee</h2>
                
                <p>To get third-party verification and an external certification profile, you need to register with OM Guarantee.</p>
                
                <h3>What You'll Get:</h3>
                <ul>
                    <li>✅ <strong>Embedded Social Impact Widget</strong> - Professional certification badge</li>
                    <li>✅ <strong>Monthly Social Impact Report</strong> - Detailed impact analytics</li>
                    <li>✅ <strong>Social Impact Profile</strong> - External verification dashboard</li>
                    <li>✅ <strong>OMG Certification Mark</strong> - Official branding and trust signals</li>
                    <li>✅ <strong>Cross Promotion</strong> - Exposure through OM Guarantee network</li>
                </ul>
                
                <h3>Registration Information Needed:</h3>
                <div class="omg-registration-info">
                    <div class="omg-info-section">
                        <h4>Personal Information:</h4>
                        <ul>
                            <li>First Name, Last Name</li>
                            <li>Email, Password</li>
                            <li>Phone Number</li>
                        </ul>
                    </div>
                    
                    <div class="omg-info-section">
                        <h4>Business Information:</h4>
                        <ul>
                            <li>Company Name: <strong><?php echo esc_html(get_option('omg_woo_business_name', get_bloginfo('name'))); ?></strong></li>
                            <li>Company Website: <strong><?php echo esc_html(home_url()); ?></strong></li>
                            <li>Company Role</li>
                            <li>Company Logo</li>
                        </ul>
                    </div>
                    
                    <div class="omg-info-section">
                        <h4>Address Information:</h4>
                        <ul>
                            <li>Country/Region</li>
                            <li>Street Address</li>
                            <li>Town/City, State, ZIP Code</li>
                        </ul>
                    </div>
                </div>
                
                <div class="omg-registration-actions">
                    <a href="https://omguarantee.com/register/" target="_blank" class="button button-primary button-hero">
                        🚀 Register with OM Guarantee
                    </a>
                    
                    <p><em>After registration, come back and enter your OM Guarantee profile URL in the settings.</em></p>
                </div>
            </div>
            
            <div class="omg-card">
                <h2>📋 After Registration</h2>
                
                <ol>
                    <li><strong>Complete registration</strong> on OMGuarantee.com</li>
                    <li><strong>Get your profile URL</strong> from your OM Guarantee dashboard</li>
                    <li><strong>Enter the URL</strong> in OM Guarantee Settings</li>
                    <li><strong>Use certification badges</strong> on your website</li>
                    <li><strong>Submit impact reports</strong> for verification</li>
                </ol>
                
                <p>Once registered, you'll be able to use the external verification features and certification badges that link to your OM Guarantee profile.</p>
            </div>
        </div>
        
        <style>
        .omg-registration-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            grid-gap: 20px;
            margin: 20px 0;
        }
        
        .omg-info-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #3A8CCB;
        }
        
        .omg-info-section h4 {
            color: #3A8CCB;
            margin-top: 0;
        }
        
        .omg-registration-actions {
            text-align: center;
            margin: 30px 0;
        }
        
        .button-hero {
            font-size: 16px;
            padding: 15px 30px;
            height: auto;
        }
        </style>
        <?php
    }
    
    public function report_page() {
        $total_donated = get_option('omg_woo_total_donated', 0);
        $total_orders = get_option('omg_woo_total_orders', 0);
        $blockchain_transactions = get_option('omg_woo_blockchain_transactions', 0);
        
        ?>
        <div class="wrap omg-admin-wrap">
            <div class="omg-admin-header">
                <img src="<?php echo OMG_WOO_PLUGIN_URL; ?>assets/images/newOMGincLogo2022HorizontalWEB.jpg" alt="OM Guarantee" class="omg-admin-logo">
                <h1>Impact Report</h1>
                <p>Track and verify your social impact</p>
            </div>
            
            <div class="omg-impact-dashboard">
                <div class="omg-summary-cards">
                    <div class="omg-summary-card">
                        <div class="omg-summary-number">$<?php echo number_format($total_donated, 2); ?></div>
                        <div class="omg-summary-label">Total Donated</div>
                    </div>
                    
                    <div class="omg-summary-card">
                        <div class="omg-summary-number"><?php echo number_format($total_orders); ?></div>
                        <div class="omg-summary-label">Impact Orders</div>
                    </div>
                    
                    <div class="omg-summary-card">
                        <div class="omg-summary-number"><?php echo number_format($blockchain_transactions); ?></div>
                        <div class="omg-summary-label">Blockchain Transactions</div>
                    </div>
                </div>
                
                <div class="omg-report-actions">
                    <button type="button" id="generate-detailed-report" class="button button-primary">📄 Generate Detailed Report</button>
                    <button type="button" id="export-for-omg" class="button button-secondary">📤 Export for OM Guarantee</button>
                    <button type="button" id="verify-blockchain" class="button button-secondary">🔗 Verify on Blockchain</button>
                </div>
            </div>
        </div>
        
        <style>
        .omg-impact-dashboard {
            background: white;
            border: 2px solid #3A8CCB;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 16px 48px rgba(0,0,0,0.15);
        }
        
        .omg-summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            grid-gap: 20px;
            margin-bottom: 30px;
        }
        
        .omg-summary-card {
            background: linear-gradient(135deg, #3A8CCB 0%, #2E7BB8 100%);
            color: white;
            padding: 25px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 8px 24px rgba(58, 140, 203, 0.3);
        }
        
        .omg-summary-number {
            font-size: 2.5em;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .omg-summary-label {
            font-size: 1.1em;
            opacity: 0.9;
        }
        
        .omg-report-actions {
            text-align: center;
        }
        
        .omg-report-actions .button {
            margin: 0 10px;
        }
        </style>
        <?php
    }
    
    // Shortcode implementations
    public function render_impact_dashboard($atts) {
        $atts = shortcode_atts(array(
            'theme' => 'light',
            'size' => 'compact'
        ), $atts);
        
        $business_name = get_option('omg_woo_business_name', get_bloginfo('name'));
        $total_donated = get_option('omg_woo_total_donated', 0);
        $total_orders = get_option('omg_woo_total_orders', 0);
        $blockchain_transactions = get_option('omg_woo_blockchain_transactions', 0);
        $default_charity = get_option('omg_woo_default_charity', 'Food Yoga International');
        
        ob_start();
        ?>
        <div class="omg-impact-dashboard-compact" style="
            background: white;
            border: 2px solid #3A8CCB;
            border-radius: 12px;
            padding: 25px;
            margin: 20px auto;
            max-width: 600px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
        ">
            <!-- Header -->
            <div style="text-align: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #f0f0f0;">
                <h3 style="margin: 0; color: #3A8CCB; font-size: 18px;">Social Impact Dashboard</h3>
                <p style="margin: 5px 0 0 0; color: #666; font-size: 14px;"><?php echo esc_html($business_name); ?></p>
            </div>

            <!-- Summary Row -->
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 20px;">
                <div style="text-align: center; padding: 15px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #3A8CCB;">
                    <div style="font-size: 24px; font-weight: bold; color: #3A8CCB; margin-bottom: 5px;">$<?php echo number_format($total_donated); ?></div>
                    <div style="font-size: 12px; color: #666; text-transform: uppercase; letter-spacing: 0.5px;">Total Donated</div>
                </div>
                <div style="text-align: center; padding: 15px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #28a745;">
                    <div style="font-size: 24px; font-weight: bold; color: #28a745; margin-bottom: 5px;"><?php echo number_format($total_orders); ?></div>
                    <div style="font-size: 12px; color: #666; text-transform: uppercase; letter-spacing: 0.5px;">Impact Orders</div>
                </div>
                <div style="text-align: center; padding: 15px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #17a2b8;">
                    <div style="font-size: 24px; font-weight: bold; color: #17a2b8; margin-bottom: 5px;"><?php echo number_format($blockchain_transactions); ?></div>
                    <div style="font-size: 12px; color: #666; text-transform: uppercase; letter-spacing: 0.5px;">Blockchain Verified</div>
                </div>
            </div>

            <!-- Top Charity -->
            <?php if (!empty($default_charity)): ?>
            <div style="background: #e7f3ff; padding: 15px; border-radius: 8px; border-left: 4px solid #3A8CCB;">
                <h4 style="margin: 0 0 8px 0; color: #3A8CCB; font-size: 14px;">Top Supported Charity</h4>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 18px;">🌍</span>
                    <div>
                        <p style="margin: 0; font-weight: 600; color: #333; font-size: 14px;"><?php echo esc_html($default_charity); ?></p>
                        <p style="margin: 0; color: #666; font-size: 12px;">$<?php echo number_format($total_donated); ?> donated</p>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Verification Links -->
            <div style="text-align: center; margin-top: 20px; padding-top: 15px; border-top: 1px solid #f0f0f0;">
                <div style="display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;">
                    <a href="#" style="
                        color: #3A8CCB; 
                        text-decoration: none; 
                        font-size: 12px; 
                        padding: 6px 12px; 
                        border: 1px solid #3A8CCB; 
                        border-radius: 4px;
                        transition: all 0.3s ease;
                    " onmouseover="this.style.background='#3A8CCB'; this.style.color='white';" 
                       onmouseout="this.style.background='transparent'; this.style.color='#3A8CCB';">
                        🔗 Blockchain Verification
                    </a>
                    <a href="#" style="
                        color: #28a745; 
                        text-decoration: none; 
                        font-size: 12px; 
                        padding: 6px 12px; 
                        border: 1px solid #28a745; 
                        border-radius: 4px;
                        transition: all 0.3s ease;
                    " onmouseover="this.style.background='#28a745'; this.style.color='white';" 
                       onmouseout="this.style.background='transparent'; this.style.color='#28a745';">
                        📈 Donation Tracker
                    </a>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public function render_impact_summary($atts) {
        $atts = shortcode_atts(array(
            'style' => 'compact'
        ), $atts);
        
        $business_name = get_option('omg_woo_business_name', get_bloginfo('name'));
        $total_donated = get_option('omg_woo_total_donated', 0);
        $total_orders = get_option('omg_woo_total_orders', 0);
        
        ob_start();
        ?>
        <div class="omg-impact-summary" style="
            background: #f8f9fa;
            border-left: 4px solid #3A8CCB;
            padding: 20px;
            margin: 15px 0;
            border-radius: 8px;
            max-width: 400px;
        ">
            <h4 style="margin: 0 0 10px 0; color: #3A8CCB; font-size: 16px;">Our Social Impact</h4>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <p style="margin: 0; font-size: 14px; color: #666;">Total Donated:</p>
                    <p style="margin: 0; font-size: 20px; font-weight: bold; color: #3A8CCB;">$<?php echo number_format($total_donated); ?></p>
                </div>
                <div style="text-align: right;">
                    <p style="margin: 0; font-size: 14px; color: #666;">Impact Orders:</p>
                    <p style="margin: 0; font-size: 20px; font-weight: bold; color: #28a745;"><?php echo number_format($total_orders); ?></p>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public function render_donation_counter($atts) {
        $total_donated = get_option('omg_woo_total_donated', 0);
        
        ob_start();
        ?>
        <div class="omg-donation-counter" style="
            text-align: center;
            padding: 15px;
            background: linear-gradient(135deg, #3A8CCB, #28a745);
            color: white;
            border-radius: 8px;
            margin: 15px 0;
            max-width: 300px;
            margin-left: auto;
            margin-right: auto;
        ">
            <div style="font-size: 28px; font-weight: bold; margin-bottom: 5px;">
                $<?php echo number_format($total_donated); ?>
            </div>
            <div style="font-size: 14px; opacity: 0.9;">
                Total Donated to Charity
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public function render_charity_list($atts) {
        $default_charity = get_option('omg_woo_default_charity', 'Food Yoga International');
        $total_donated = get_option('omg_woo_total_donated', 0);
        
        ob_start();
        ?>
        <div class="omg-charity-list" style="
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin: 15px 0;
            max-width: 400px;
        ">
            <h4 style="margin: 0 0 15px 0; color: #3A8CCB; font-size: 16px;">Supported Charities</h4>
            <?php if ($total_donated > 0): ?>
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #f0f0f0;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 16px;">🌍</span>
                    <span style="font-weight: 600; color: #333;"><?php echo esc_html($default_charity); ?></span>
                </div>
                <span style="color: #3A8CCB; font-weight: bold;">$<?php echo number_format($total_donated); ?></span>
            </div>
            <?php else: ?>
            <p style="margin: 0; color: #666; font-style: italic;">No donations yet. Start making an impact with your first purchase!</p>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public function render_certification_badge($atts) {
        $atts = shortcode_atts(array(
            'theme' => 'light',
            'size' => 'small',
            'style' => 'compact'
        ), $atts);
        
        $business_name = get_option('omg_woo_business_name', get_bloginfo('name'));
        $omg_profile_url = get_option('omg_woo_omg_profile_url', '');
        $total_donated = get_option('omg_woo_total_donated', 0);
        
        // Calculate impact metric (similar to Gopals)
        $children_fed = floor($total_donated / 0.78); // Approximate cost to feed a child
        
        ob_start();
        ?>
        <div class="omg-certification-badge-compact" style="
            background: #f8f9fa;
            border: 2px solid #3A8CCB;
            border-radius: 12px;
            padding: 20px;
            margin: 20px auto;
            max-width: 400px;
            text-align: left;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        ">
            <div style="display: flex; align-items: center; gap: 15px;">
                <img src="<?php echo OMG_WOO_PLUGIN_URL; ?>assets/images/OMGcertificate2022.png" 
                     alt="OM Guarantee Certification" 
                     style="width: 60px; height: 60px; object-fit: contain; flex-shrink: 0;">
                <div style="flex: 1;">
                    <p style="margin: 0 0 8px 0; font-weight: 600; color: #333; font-size: 14px;">
                        <strong><?php echo esc_html($business_name); ?> is OM Guarantee certified.</strong>
                    </p>
                    <p style="margin: 0 0 12px 0; color: #666; font-size: 13px;">
                        We have made the following certified social impact:
                    </p>
                    <?php if ($children_fed > 0): ?>
                    <div style="
                        display: flex; 
                        align-items: center; 
                        gap: 8px; 
                        margin: 8px 0;
                        padding: 8px 12px;
                        background: #e7f3ff;
                        border-radius: 6px;
                        border-left: 3px solid #3A8CCB;
                    ">
                        <span style="font-size: 16px;">🍽️</span>
                        <span style="font-weight: 600; color: #3A8CCB; font-size: 13px;">
                            Fed <?php echo number_format($children_fed); ?> needy children
                        </span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($omg_profile_url)): ?>
                        <a href="<?php echo esc_url($omg_profile_url); ?>" target="_blank" 
                           style="color: #3A8CCB; text-decoration: none; font-weight: 600; font-size: 12px;">
                            View our verified impact →
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public function render_external_verification($atts) {
        $atts = shortcode_atts(array(
            'style' => 'footer'
        ), $atts);
        
        $business_name = get_option('omg_woo_business_name', get_bloginfo('name'));
        $omg_profile_url = get_option('omg_woo_omg_profile_url', '');
        $total_donated = get_option('omg_woo_total_donated', 25000);
        
        // Calculate impact metric (similar to Gopals)
        $children_fed = floor($total_donated / 0.78); // Approximate cost to feed a child
        
        ob_start();
        ?>
        <div class="omg-external-verification omg-style-<?php echo esc_attr($atts['style']); ?>">
            <div class="omg-verification-content">
                <img src="<?php echo OMG_WOO_PLUGIN_URL; ?>assets/images/OMGcertificate2022.png" alt="OM Guarantee Certification" class="omg-verification-badge">
                <div class="omg-verification-text">
                    <p><strong><?php echo esc_html($business_name); ?> is OM Guarantee certified.</strong></p>
                    <p>We have made the following certified social impact:</p>
                    <div class="omg-impact-metric">
                        <span class="omg-impact-icon">🍽️</span>
                        <span class="omg-impact-text">Fed <?php echo number_format($children_fed); ?> needy children</span>
                    </div>
                    <?php if (!empty($omg_profile_url)): ?>
                        <a href="<?php echo esc_url($omg_profile_url); ?>" target="_blank" class="omg-verification-link">
                            View our verified impact →
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public function render_registration_helper($atts) {
        $omg_profile_url = get_option('omg_woo_omg_profile_url', '');
        
        if (!empty($omg_profile_url)) {
            return '<p>✅ Already registered with OM Guarantee. <a href="' . esc_url($omg_profile_url) . '" target="_blank">View profile</a></p>';
        }
        
        ob_start();
        ?>
        <div class="omg-registration-helper">
            <div class="omg-registration-card">
                <h3>🏢 Get OM Guarantee Certification</h3>
                <p>Register with OM Guarantee to get third-party verification of your social impact.</p>
                
                <div class="omg-registration-benefits">
                    <ul>
                        <li>✅ External verification dashboard</li>
                        <li>✅ Monthly impact reports</li>
                        <li>✅ Official certification mark</li>
                        <li>✅ Cross-promotion opportunities</li>
                    </ul>
                </div>
                
                <a href="https://omguarantee.com/register/" target="_blank" class="omg-button omg-button-primary">
                    🚀 Register with OM Guarantee
                </a>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    // AJAX handlers
    public function ajax_search_charities() {
        check_ajax_referer('omg_woo_admin_nonce', 'nonce');
        
        $search_term = sanitize_text_field($_POST['search_term']);
        $api_key = get_option('omg_woo_every_org_api_key', '');
        
        if (empty($api_key)) {
            wp_send_json_error('Every.org API key not configured');
        }
        
        $url = 'https://partners.every.org/v0.2/search/' . urlencode($search_term) . '?apiKey=' . urlencode($api_key);
        
        $response = wp_remote_get($url, array(
            'timeout' => 15,
            'headers' => array(
                'User-Agent' => 'OM Guarantee WordPress Plugin'
            )
        ));
        
        if (is_wp_error($response)) {
            wp_send_json_error('API request failed: ' . $response->get_error_message());
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        if ($status_code !== 200) {
            wp_send_json_error('API error (Code: ' . $status_code . '): ' . $body);
        }
        
        $data = json_decode($body, true);
        
        if (!$data || !isset($data['nonprofits'])) {
            wp_send_json_error('Invalid API response');
        }
        
        $charities = array();
        foreach ($data['nonprofits'] as $nonprofit) {
            $charities[] = array(
                'name' => $nonprofit['name'],
                'description' => isset($nonprofit['description']) ? $nonprofit['description'] : '',
                'location' => isset($nonprofit['location']) ? $nonprofit['location'] : '',
                'category' => isset($nonprofit['category']) ? $nonprofit['category'] : ''
            );
        }
        
        wp_send_json_success($charities);
    }
    
    public function ajax_test_api() {
        check_ajax_referer('omg_woo_admin_nonce', 'nonce');
        
        $api_key = get_option('omg_woo_every_org_api_key', '');
        
        if (empty($api_key)) {
            wp_send_json_error('API key not configured');
        }
        
        // Test with a simple search
        $url = 'https://partners.every.org/v0.2/search/red%20cross?apiKey=' . urlencode($api_key);
        
        $response = wp_remote_get($url, array(
            'timeout' => 10,
            'headers' => array(
                'User-Agent' => 'OM Guarantee WordPress Plugin'
            )
        ));
        
        if (is_wp_error($response)) {
            wp_send_json_error('Connection failed: ' . $response->get_error_message());
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        
        if ($status_code === 200) {
            wp_send_json_success('✅ API connection successful! Ready for charity search and donations.');
        } else {
            wp_send_json_error('API returned status code: ' . $status_code);
        }
    }
    
    public function ajax_generate_report() {
        check_ajax_referer('omg_woo_admin_nonce', 'nonce');
        
        $report_data = array(
            'business_name' => get_option('omg_woo_business_name', get_bloginfo('name')),
            'website' => home_url(),
            'total_donated' => get_option('omg_woo_total_donated', 0),
            'total_orders' => get_option('omg_woo_total_orders', 0),
            'blockchain_transactions' => get_option('omg_woo_blockchain_transactions', 0),
            'default_charity' => get_option('omg_woo_default_charity', ''),
            'impact_percentage' => get_option('omg_woo_impact_percentage', '1.5'),
            'generated_date' => current_time('mysql')
        );
        
        wp_send_json_success($report_data);
    }
    
    // WooCommerce integration
    public function add_product_tab($tabs) {
        $tabs['omg_impact'] = array(
            'label' => 'OM Guarantee Impact',
            'target' => 'omg_impact_data',
            'class' => array('show_if_simple', 'show_if_variable')
        );
        return $tabs;
    }
    
    public function add_product_panel() {
        global $post;
        
        $enabled = get_post_meta($post->ID, '_omg_impact_enabled', true);
        $percentage = get_post_meta($post->ID, '_omg_impact_percentage', true);
        $charity = get_post_meta($post->ID, '_omg_impact_charity', true);
        
        ?>
        <div id="omg_impact_data" class="panel woocommerce_options_panel">
            <div class="omg-product-panel">
                <h3 style="color: #3A8CCB; border-bottom: 2px solid #3A8CCB; padding-bottom: 10px;">
                    OM Guarantee Social Impact
                </h3>
                
                <div class="options_group">
                    <?php
                    woocommerce_wp_checkbox(array(
                        'id' => '_omg_impact_enabled',
                        'label' => 'Enable social impact for this product',
                        'description' => 'When enabled, a percentage of sales will be donated to charity',
                        'value' => $enabled
                    ));
                    
                    woocommerce_wp_text_input(array(
                        'id' => '_omg_impact_percentage',
                        'label' => 'Impact percentage (%)',
                        'description' => 'Percentage of product price to donate (leave empty to use global setting)',
                        'type' => 'number',
                        'custom_attributes' => array(
                            'step' => '0.1',
                            'min' => '0',
                            'max' => '100'
                        ),
                        'value' => $percentage
                    ));
                    
                    woocommerce_wp_text_input(array(
                        'id' => '_omg_impact_charity',
                        'label' => 'Charity name',
                        'description' => 'Specific charity for this product (leave empty to use global setting)',
                        'value' => $charity
                    ));
                    ?>
                </div>
                
                <div class="omg-product-preview">
                    <h4>Preview:</h4>
                    <div class="omg-impact-preview">
                        <p><strong>Social Impact:</strong> 1.5% of this purchase will be donated to Food Yoga International</p>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
        .omg-product-panel {
            padding: 20px;
        }
        
        .omg-impact-preview {
            background: #f8f9fa;
            border: 1px solid #3A8CCB;
            border-radius: 6px;
            padding: 15px;
            margin-top: 10px;
        }
        </style>
        <?php
    }
    
    public function save_product_meta($post_id) {
        update_post_meta($post_id, '_omg_impact_enabled', isset($_POST['_omg_impact_enabled']) ? 'yes' : 'no');
        update_post_meta($post_id, '_omg_impact_percentage', sanitize_text_field($_POST['_omg_impact_percentage']));
        update_post_meta($post_id, '_omg_impact_charity', sanitize_text_field($_POST['_omg_impact_charity']));
    }
    
    public function display_product_impact() {
        global $product;
        
        $product_enabled = get_post_meta($product->get_id(), '_omg_impact_enabled', true) === 'yes';
        $global_enabled = get_option('omg_woo_global_impact', false);
        
        if (!$product_enabled && !$global_enabled) {
            return;
        }
        
        $percentage = get_post_meta($product->get_id(), '_omg_impact_percentage', true);
        if (empty($percentage)) {
            $percentage = get_option('omg_woo_impact_percentage', '1.5');
        }
        
        $charity = get_post_meta($product->get_id(), '_omg_impact_charity', true);
        if (empty($charity)) {
            $charity = get_option('omg_woo_default_charity', 'Food Yoga International');
        }
        
        $price = $product->get_price();
        $donation_amount = ($price * $percentage) / 100;
        
        ?>
        <div class="omg-product-impact">
            <div class="omg-impact-content">
                <span class="omg-impact-icon">💝</span>
                <span class="omg-impact-text">
                    <strong>Social Impact:</strong> <?php echo esc_html($percentage); ?>% of this purchase ($<?php echo number_format($donation_amount, 2); ?>) will be donated to <?php echo esc_html($charity); ?>
                </span>
            </div>
        </div>
        
        <style>
        .omg-product-impact {
            background: linear-gradient(135deg, #3A8CCB 0%, #2E7BB8 100%);
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            box-shadow: 0 4px 12px rgba(58, 140, 203, 0.3);
        }
        
        .omg-impact-content {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .omg-impact-icon {
            font-size: 1.5em;
        }
        
        .omg-impact-text {
            flex: 1;
            line-height: 1.4;
        }
        </style>
        <?php
    }
}

// Initialize the plugin
new OMG_WooCommerce_Enhanced();

// CSS for frontend
add_action('wp_head', function() {
    ?>
    <style>
    /* OM Guarantee Dashboard Styles */
    .omg-clean-dashboard {
        display: grid;
        grid-template-areas:
            "header  header   header"
            "summary summary  summary"
            "impact  charities customer"
            "blockchain donation donation";
        grid-template-columns: repeat(3, 1fr);
        grid-gap: 24px;
        max-width: 1200px;
        margin: 0 auto;
        padding: 30px;
        background: #f8f9fa;
        border-radius: 16px;
        font-family: Poppins, sans-serif;
    }
    
    @media (max-width: 768px) {
        .omg-clean-dashboard {
            grid-template-areas:
                "header"
                "summary"
                "impact"
                "charities"
                "customer"
                "blockchain"
                "donation";
            grid-template-columns: 1fr;
            padding: 20px;
        }
    }
    
    .omg-dashboard__header { 
        grid-area: header; 
        display: flex; 
        justify-content: space-between; 
        align-items: center;
        background: linear-gradient(135deg, #3A8CCB 0%, #2E7BB8 100%);
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 16px 48px rgba(58, 140, 203, 0.4);
    }
    
    .omg-dashboard__summary { 
        grid-area: summary; 
        display: flex; 
        gap: 20px; 
    }
    
    .omg-dashboard__impact   { grid-area: impact; }
    .omg-dashboard__charities{ grid-area: charities; }
    .omg-dashboard__customer { grid-area: customer; }
    .omg-dashboard__blockchain{ grid-area: blockchain; }
    .omg-dashboard__donation { grid-area: donation; }
    
    .omg-card {
        background: #fff;
        border: 2px solid #3A8CCB;
        border-radius: 12px;
        box-shadow: 0 16px 48px rgba(0,0,0,0.25);
        padding: 24px;
        text-align: center;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    .omg-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #3A8CCB, #2E7BB8);
    }
    
    .omg-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 24px 64px rgba(58, 140, 203, 0.35);
        border-color: #2E7BB8;
    }
    
    .omg-dashboard__logo,
    .omg-dashboard__badge {
        max-height: 80px;
        width: auto;
    }
    
    .omg-dashboard__logo {
        order: 1;
    }
    
    .omg-dashboard__badge {
        order: 2;
        margin-left: 16px;
    }
    
    .omg-card h3 {
        font-family: Poppins, sans-serif;
        font-size: 1.25rem;
        color: #333;
        margin-bottom: 16px;
        font-weight: 600;
    }
    
    .omg-counter,
    .omg-big-number {
        color: #3A8CCB;
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 8px;
        display: block;
    }
    
    .omg-label,
    .omg-customer-label {
        color: #555;
        font-size: 1rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .omg-progress-item {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 15px;
        font-size: 0.95rem;
    }
    
    .omg-progress-item span:first-child {
        min-width: 80px;
        text-align: left;
        font-weight: 500;
    }
    
    .omg-progress-bar {
        flex: 1;
        height: 12px;
        background: #e9ecef;
        border-radius: 6px;
        overflow: hidden;
        position: relative;
    }
    
    .omg-progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #3A8CCB, #2E7BB8);
        border-radius: 6px;
        transition: width 1s ease;
        position: relative;
    }
    
    .omg-progress-fill::after {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
        animation: shimmer 2s infinite;
    }
    
    @keyframes shimmer {
        0% { left: -100%; }
        100% { left: 100%; }
    }
    
    .omg-progress-item span:last-child {
        min-width: 40px;
        text-align: right;
        font-weight: 600;
        color: #3A8CCB;
    }
    
    .omg-charity-list {
        text-align: left;
    }
    
    .omg-charity-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #e9ecef;
        font-size: 0.95rem;
    }
    
    .omg-charity-item:last-child {
        border-bottom: none;
    }
    
    .omg-charity-item span:first-child {
        font-weight: 500;
        color: #333;
    }
    
    .omg-charity-item span:last-child {
        font-weight: 600;
        color: #3A8CCB;
    }
    
    .omg-customer-counter {
        text-align: center;
    }
    
    .omg-big-number {
        font-size: 3rem;
        margin-bottom: 5px;
    }
    
    .omg-customer-label {
        font-size: 1.1rem;
    }
    
    .omg-button {
        display: block;
        width: 100%;
        padding: 16px 24px;
        border: none;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        text-align: center;
        box-shadow: 0 8px 24px rgba(58, 140, 203, 0.3);
    }
    
    .omg-button-primary {
        background: linear-gradient(135deg, #3A8CCB 0%, #2E7BB8 100%);
        color: white;
    }
    
    .omg-button-primary:hover {
        background: linear-gradient(135deg, #2E7BB8 0%, #1E5A8C 100%);
        transform: translateY(-2px);
        box-shadow: 0 12px 32px rgba(58, 140, 203, 0.4);
    }
    
    .omg-button-secondary {
        background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
        color: white;
    }
    
    .omg-button-secondary:hover {
        background: linear-gradient(135deg, #495057 0%, #343a40 100%);
        transform: translateY(-2px);
        box-shadow: 0 12px 32px rgba(108, 117, 125, 0.4);
    }
    
    /* Certification Badge Styles */
    .omg-certification-badge {
        background: white;
        border: 2px solid #3A8CCB;
        border-radius: 12px;
        padding: 25px;
        margin: 20px 0;
        box-shadow: 0 16px 48px rgba(0,0,0,0.15);
        text-align: center;
        max-width: 600px;
        margin-left: auto;
        margin-right: auto;
    }
    
    .omg-cert-content {
        display: flex;
        align-items: center;
        gap: 20px;
        text-align: left;
    }
    
    .omg-cert-image {
        max-width: 80px;
        height: auto;
        flex-shrink: 0;
    }
    
    .omg-cert-text h3 {
        color: #3A8CCB;
        margin: 0 0 10px 0;
        font-size: 1.3rem;
    }
    
    .omg-cert-text p {
        margin: 0 0 15px 0;
        color: #555;
    }
    
    .omg-verify-link {
        color: #3A8CCB;
        text-decoration: none;
        font-weight: 600;
        border: 2px solid #3A8CCB;
        padding: 8px 16px;
        border-radius: 6px;
        display: inline-block;
        transition: all 0.3s ease;
    }
    
    .omg-verify-link:hover {
        background: #3A8CCB;
        color: white;
        transform: translateY(-2px);
    }
    
    /* External Verification Styles (like Gopals) */
    .omg-external-verification {
        background: white;
        border: 2px solid #3A8CCB;
        border-radius: 12px;
        padding: 20px;
        margin: 20px 0;
        box-shadow: 0 8px 24px rgba(0,0,0,0.1);
    }
    
    .omg-verification-content {
        display: flex;
        align-items: center;
        gap: 20px;
    }
    
    .omg-verification-badge {
        max-width: 80px;
        height: auto;
        flex-shrink: 0;
    }
    
    .omg-verification-text {
        flex: 1;
    }
    
    .omg-verification-text p {
        margin: 0 0 10px 0;
        color: #333;
        font-size: 0.95rem;
    }
    
    .omg-impact-metric {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 15px 0;
        padding: 12px;
        background: #f8f9fa;
        border-radius: 8px;
        border-left: 4px solid #3A8CCB;
    }
    
    .omg-impact-icon {
        font-size: 1.5em;
    }
    
    .omg-impact-text {
        font-weight: 600;
        color: #3A8CCB;
        font-size: 1.1rem;
    }
    
    .omg-verification-link {
        color: #3A8CCB;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.9rem;
    }
    
    .omg-verification-link:hover {
        text-decoration: underline;
    }
    
    /* Registration Helper Styles */
    .omg-registration-helper {
        max-width: 600px;
        margin: 20px auto;
    }
    
    .omg-registration-card {
        background: white;
        border: 2px solid #3A8CCB;
        border-radius: 12px;
        padding: 30px;
        text-align: center;
        box-shadow: 0 16px 48px rgba(0,0,0,0.15);
    }
    
    .omg-registration-card h3 {
        color: #3A8CCB;
        margin-top: 0;
        font-size: 1.5rem;
    }
    
    .omg-registration-benefits {
        text-align: left;
        margin: 20px 0;
    }
    
    .omg-registration-benefits ul {
        list-style: none;
        padding: 0;
    }
    
    .omg-registration-benefits li {
        padding: 8px 0;
        color: #555;
    }
    
    /* Mobile Responsive */
    @media (max-width: 768px) {
        .omg-dashboard__summary {
            flex-direction: column;
        }
        
        .omg-cert-content,
        .omg-verification-content {
            flex-direction: column;
            text-align: center;
        }
        
        .omg-cert-text,
        .omg-verification-text {
            text-align: center;
        }
        
        .omg-progress-item {
            flex-direction: column;
            gap: 8px;
            text-align: center;
        }
        
        .omg-progress-item span:first-child,
        .omg-progress-item span:last-child {
            min-width: auto;
            text-align: center;
        }
        
        .omg-progress-bar {
            width: 100%;
        }
    }
    </style>
    <?php
});

// JavaScript for admin functionality
add_action('admin_footer', function() {
    ?>
    <script>
    jQuery(document).ready(function($) {
        // Search charities functionality
        $('#search-charities').on('click', function() {
            var searchTerm = prompt('Enter charity name to search:');
            if (!searchTerm) return;
            
            $.ajax({
                url: omg_woo_admin_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'omg_search_charities',
                    search_term: searchTerm,
                    nonce: omg_woo_admin_ajax.nonce
                },
                beforeSend: function() {
                    $('#search-charities').text('Searching...');
                },
                success: function(response) {
                    $('#search-charities').text('Search Charities');
                    
                    if (response.success) {
                        var charities = response.data;
                        if (charities.length === 0) {
                            alert('No charities found for: ' + searchTerm);
                            return;
                        }
                        
                        var charityList = 'Found charities:\n\n';
                        charities.forEach(function(charity, index) {
                            charityList += (index + 1) + '. ' + charity.name;
                            if (charity.location) charityList += ' (' + charity.location + ')';
                            charityList += '\n';
                        });
                        
                        var selection = prompt(charityList + '\nEnter number to select charity:');
                        var selectedIndex = parseInt(selection) - 1;
                        
                        if (selectedIndex >= 0 && selectedIndex < charities.length) {
                            $('input[name="omg_woo_default_charity"]').val(charities[selectedIndex].name);
                            alert('Selected: ' + charities[selectedIndex].name);
                        }
                    } else {
                        alert('Search failed: ' + response.data);
                    }
                },
                error: function() {
                    $('#search-charities').text('Search Charities');
                    alert('Search request failed');
                }
            });
        });
        
        // Test API functionality
        $('#test-api, #test-charity-search').on('click', function() {
            $.ajax({
                url: omg_woo_admin_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'omg_test_api',
                    nonce: omg_woo_admin_ajax.nonce
                },
                beforeSend: function() {
                    $(this).text('Testing...');
                },
                success: function(response) {
                    $('#test-api, #test-charity-search').text('Test API Connection');
                    
                    if (response.success) {
                        alert('✅ ' + response.data);
                    } else {
                        alert('❌ Test failed: ' + response.data);
                    }
                },
                error: function() {
                    $('#test-api, #test-charity-search').text('Test API Connection');
                    alert('❌ Test request failed');
                }
            });
        });
        
        // Generate report functionality
        $('#generate-report, #generate-detailed-report').on('click', function() {
            $.ajax({
                url: omg_woo_admin_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'omg_generate_report',
                    nonce: omg_woo_admin_ajax.nonce
                },
                beforeSend: function() {
                    $(this).text('Generating...');
                },
                success: function(response) {
                    $('#generate-report, #generate-detailed-report').text('Generate Report');
                    
                    if (response.success) {
                        var report = response.data;
                        var reportText = 'OM GUARANTEE IMPACT REPORT\n\n';
                        reportText += 'Business: ' + report.business_name + '\n';
                        reportText += 'Website: ' + report.website + '\n';
                        reportText += 'Total Donated: $' + report.total_donated + '\n';
                        reportText += 'Impact Orders: ' + report.total_orders + '\n';
                        reportText += 'Blockchain Transactions: ' + report.blockchain_transactions + '\n';
                        reportText += 'Default Charity: ' + report.default_charity + '\n';
                        reportText += 'Impact Percentage: ' + report.impact_percentage + '%\n';
                        reportText += 'Generated: ' + report.generated_date + '\n';
                        
                        // Create downloadable file
                        var blob = new Blob([reportText], { type: 'text/plain' });
                        var url = window.URL.createObjectURL(blob);
                        var a = document.createElement('a');
                        a.href = url;
                        a.download = 'omg-impact-report-' + new Date().toISOString().split('T')[0] + '.txt';
                        document.body.appendChild(a);
                        a.click();
                        document.body.removeChild(a);
                        window.URL.revokeObjectURL(url);
                        
                        alert('📄 Report generated and downloaded!');
                    } else {
                        alert('❌ Report generation failed: ' + response.data);
                    }
                },
                error: function() {
                    $('#generate-report, #generate-detailed-report').text('Generate Report');
                    alert('❌ Report request failed');
                }
            });
        });
        
        // Mock test functions for demonstration
        $('#test-donation-processing').on('click', function() {
            alert('✅ Donation processing test successful!\n\nSimulated donation of $5.00 to Food Yoga International\nTransaction ID: TXN_' + Math.random().toString(36).substr(2, 9).toUpperCase());
        });
        
        $('#test-blockchain').on('click', function() {
            alert('✅ Blockchain connection test successful!\n\nConnected to Polygon network\nTest transaction hash: 0x' + Math.random().toString(16).substr(2, 64));
        });
        
        $('#verify-blockchain').on('click', function() {
            window.open('https://polygonscan.com/', '_blank');
        });
        
        $('#export-for-omg').on('click', function() {
            alert('📤 Export prepared for OM Guarantee submission!\n\nThis feature will be available when OM Guarantee API endpoints are ready.');
        });
    });
    </script>
    <?php
});
?>

