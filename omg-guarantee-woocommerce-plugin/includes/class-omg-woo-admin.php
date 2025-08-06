<?php
/**
 * Admin functionality for OM Guarantee WooCommerce plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class OMG_WooCommerce_Admin {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'init_settings'));
        add_action('woocommerce_product_options_general_product_data', array($this, 'add_product_fields'));
        add_action('woocommerce_process_product_meta', array($this, 'save_product_fields'));
        add_action('wp_ajax_omg_woo_test_charity_search', array($this, 'ajax_test_charity_search'));
        add_action('wp_ajax_omg_woo_test_donation', array($this, 'ajax_test_donation'));
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'OM Guarantee',
            'OM Guarantee',
            'manage_options',
            'omg-guarantee',
            array($this, 'admin_page'),
            'dashicons-heart',
            30
        );
        
        add_submenu_page(
            'omg-guarantee',
            'Settings',
            'Settings',
            'manage_options',
            'omg-guarantee',
            array($this, 'admin_page')
        );
        
        add_submenu_page(
            'omg-guarantee',
            'Donations',
            'Donations',
            'manage_options',
            'omg-guarantee-donations',
            array($this, 'donations_page')
        );
        
        add_submenu_page(
            'omg-guarantee',
            'Impact Report',
            'Impact Report',
            'manage_options',
            'omg-guarantee-impact',
            array($this, 'impact_page')
        );
    }
    
    public function init_settings() {
        register_setting('omg_woo_settings', 'omg_woo_enabled');
        register_setting('omg_woo_settings', 'omg_woo_global_percentage');
        register_setting('omg_woo_settings', 'omg_woo_default_charity');
        register_setting('omg_woo_settings', 'omg_woo_every_org_api_key');
        register_setting('omg_woo_settings', 'omg_woo_processing_frequency');
        register_setting('omg_woo_settings', 'omg_woo_blockchain_enabled');
        register_setting('omg_woo_settings', 'omg_woo_show_on_product_page');
        register_setting('omg_woo_settings', 'omg_woo_show_in_cart');
        register_setting('omg_woo_settings', 'omg_woo_show_at_checkout');
        register_setting('omg_woo_settings', 'omg_woo_badge_theme');
        register_setting('omg_woo_settings', 'omg_woo_badge_size');
    }
    
    public function admin_page() {
        $every_org = new OMG_WooCommerce_EveryOrg();
        $charities = array();
        
        // Get charities if API key is set
        $api_key = get_option('omg_woo_every_org_api_key');
        if (!empty($api_key)) {
            $charities = $every_org->search_charities('', 20);
        }
        
        ?>
        <div class="wrap omg-admin-wrap">
            <div class="omg-header">
                <img src="<?php echo OMG_WOO_PLUGIN_URL; ?>assets/images/newOMGincLogo2022Horizontal.png" alt="OM Guarantee" class="omg-logo">
                <h1>OM Guarantee for WooCommerce</h1>
                <p class="omg-subtitle">Professional social impact certification for your WooCommerce store</p>
            </div>
            
            <?php if (isset($_GET['settings-updated'])): ?>
                <div class="notice notice-success is-dismissible">
                    <p>Settings saved successfully!</p>
                </div>
            <?php endif; ?>
            
            <div class="omg-admin-content">
                <div class="omg-main-settings">
                    <form method="post" action="options.php">
                        <?php settings_fields('omg_woo_settings'); ?>
                        
                        <div class="omg-section">
                            <h2>General Settings</h2>
                            
                            <table class="form-table">
                                <tr>
                                    <th scope="row">Enable OM Guarantee</th>
                                    <td>
                                        <label>
                                            <input type="checkbox" name="omg_woo_enabled" value="yes" <?php checked(get_option('omg_woo_enabled'), 'yes'); ?>>
                                            Enable social impact certification for your store
                                        </label>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row">Global Impact Percentage</th>
                                    <td>
                                        <input type="number" name="omg_woo_global_percentage" value="<?php echo esc_attr(get_option('omg_woo_global_percentage', '1.5')); ?>" step="0.1" min="0" max="100" class="small-text">%
                                        <p class="description">Default percentage of sales to donate (can be overridden per product)</p>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        
                        <div class="omg-section">
                            <h2>Every.org Integration</h2>
                            
                            <table class="form-table">
                                <tr>
                                    <th scope="row">API Key</th>
                                    <td>
                                        <input type="text" name="omg_woo_every_org_api_key" value="<?php echo esc_attr(get_option('omg_woo_every_org_api_key')); ?>" class="regular-text" placeholder="pk_live_...">
                                        <p class="description">
                                            Get your free API key from <a href="https://www.every.org/charity-api" target="_blank">Every.org Charity API</a>
                                        </p>
                                        <?php if (!empty($api_key)): ?>
                                            <p class="omg-api-status omg-api-connected">✅ API Key Connected</p>
                                        <?php else: ?>
                                            <p class="omg-api-status omg-api-disconnected">❌ API Key Required</p>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row">Default Charity</th>
                                    <td>
                                        <select name="omg_woo_default_charity" id="omg_charity_select" class="regular-text">
                                            <option value="">Select a charity...</option>
                                            <?php foreach ($charities as $charity): ?>
                                                <option value="<?php echo esc_attr($charity['slug']); ?>" <?php selected(get_option('omg_woo_default_charity'), $charity['slug']); ?>>
                                                    <?php echo esc_html($charity['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <p class="description">Default charity for donations (can be overridden per product)</p>
                                        
                                        <div class="omg-charity-search">
                                            <input type="text" id="charity_search" placeholder="Search for charities..." class="regular-text">
                                            <button type="button" id="test_charity_search" class="button">Test Search</button>
                                            <div id="charity_search_results"></div>
                                        </div>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row">Donation Processing</th>
                                    <td>
                                        <select name="omg_woo_processing_frequency">
                                            <option value="daily" <?php selected(get_option('omg_woo_processing_frequency'), 'daily'); ?>>Daily</option>
                                            <option value="weekly" <?php selected(get_option('omg_woo_processing_frequency'), 'weekly'); ?>>Weekly</option>
                                            <option value="monthly" <?php selected(get_option('omg_woo_processing_frequency'), 'monthly'); ?>>Monthly</option>
                                        </select>
                                        <p class="description">How often to process pending donations</p>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        
                        <div class="omg-section">
                            <h2>Display Settings</h2>
                            
                            <table class="form-table">
                                <tr>
                                    <th scope="row">Show Impact On</th>
                                    <td>
                                        <label>
                                            <input type="checkbox" name="omg_woo_show_on_product_page" value="yes" <?php checked(get_option('omg_woo_show_on_product_page'), 'yes'); ?>>
                                            Product pages
                                        </label><br>
                                        <label>
                                            <input type="checkbox" name="omg_woo_show_in_cart" value="yes" <?php checked(get_option('omg_woo_show_in_cart'), 'yes'); ?>>
                                            Shopping cart
                                        </label><br>
                                        <label>
                                            <input type="checkbox" name="omg_woo_show_at_checkout" value="yes" <?php checked(get_option('omg_woo_show_at_checkout'), 'yes'); ?>>
                                            Checkout page
                                        </label>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row">Badge Theme</th>
                                    <td>
                                        <select name="omg_woo_badge_theme">
                                            <option value="light" <?php selected(get_option('omg_woo_badge_theme'), 'light'); ?>>Light</option>
                                            <option value="dark" <?php selected(get_option('omg_woo_badge_theme'), 'dark'); ?>>Dark</option>
                                        </select>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row">Badge Size</th>
                                    <td>
                                        <select name="omg_woo_badge_size">
                                            <option value="small" <?php selected(get_option('omg_woo_badge_size'), 'small'); ?>>Small</option>
                                            <option value="medium" <?php selected(get_option('omg_woo_badge_size'), 'medium'); ?>>Medium</option>
                                            <option value="large" <?php selected(get_option('omg_woo_badge_size'), 'large'); ?>>Large</option>
                                        </select>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        
                        <div class="omg-section">
                            <h2>Blockchain Settings</h2>
                            
                            <table class="form-table">
                                <tr>
                                    <th scope="row">Blockchain Logging</th>
                                    <td>
                                        <label>
                                            <input type="checkbox" name="omg_woo_blockchain_enabled" value="yes" <?php checked(get_option('omg_woo_blockchain_enabled'), 'yes'); ?>>
                                            Enable blockchain transaction logging for transparency
                                        </label>
                                        <p class="description">Records donation transactions on Polygon blockchain for verification</p>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        
                        <?php submit_button('Save Settings'); ?>
                    </form>
                </div>
                
                <div class="omg-sidebar">
                    <div class="omg-widget">
                        <h3>Quick Actions</h3>
                        <p>
                            <button type="button" id="test_donation_processing" class="button button-secondary">Test Donation Processing</button>
                        </p>
                        <p>
                            <button type="button" id="test_blockchain_connection" class="button button-secondary">Test Blockchain Connection</button>
                        </p>
                        <div id="test_results"></div>
                    </div>
                    
                    <div class="omg-widget">
                        <h3>Recent Activity</h3>
                        <?php $this->show_recent_donations(); ?>
                    </div>
                    
                    <div class="omg-widget">
                        <h3>Shortcodes</h3>
                        <p><strong>Certification Badge:</strong><br>
                        <code>[omg_certification_badge]</code></p>
                        
                        <p><strong>Impact Counter:</strong><br>
                        <code>[omg_impact_counter]</code></p>
                        
                        <p><strong>Blockchain Verification:</strong><br>
                        <code>[omg_blockchain_verification]</code></p>
                        
                        <p><strong>Donation Tracker:</strong><br>
                        <code>[omg_donation_tracker]</code></p>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    public function donations_page() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'omg_woo_donations';
        $donations = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC LIMIT 50");
        
        ?>
        <div class="wrap omg-admin-wrap">
            <div class="omg-header">
                <img src="<?php echo OMG_WOO_PLUGIN_URL; ?>assets/images/newOMGincLogo2022Horizontal.png" alt="OM Guarantee" class="omg-logo">
                <h1>Donation History</h1>
                <p class="omg-subtitle">Track all donations and their processing status</p>
            </div>
            
            <div class="omg-donations-table">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Charity</th>
                            <th>Amount</th>
                            <th>Percentage</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Processed</th>
                            <th>Blockchain</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($donations)): ?>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 40px;">
                                    <p>No donations yet. Donations will appear here after WooCommerce orders are processed.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($donations as $donation): ?>
                                <tr>
                                    <td><a href="<?php echo admin_url('post.php?post=' . $donation->order_id . '&action=edit'); ?>">#<?php echo $donation->order_id; ?></a></td>
                                    <td><?php echo esc_html($donation->charity_name); ?></td>
                                    <td>$<?php echo number_format($donation->amount, 2); ?></td>
                                    <td><?php echo $donation->percentage; ?>%</td>
                                    <td>
                                        <span class="omg-status omg-status-<?php echo $donation->status; ?>">
                                            <?php echo ucfirst($donation->status); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($donation->created_at)); ?></td>
                                    <td><?php echo $donation->processed_at ? date('M j, Y', strtotime($donation->processed_at)) : '-'; ?></td>
                                    <td>
                                        <?php if (!empty($donation->transaction_hash)): ?>
                                            <a href="https://polygonscan.com/tx/<?php echo $donation->transaction_hash; ?>" target="_blank" class="button button-small">View</a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }
    
    public function impact_page() {
        global $wpdb;
        
        // Get impact statistics
        $donations_table = $wpdb->prefix . 'omg_woo_donations';
        $impact_table = $wpdb->prefix . 'omg_woo_impact';
        
        $total_donated = $wpdb->get_var("SELECT SUM(amount) FROM $donations_table WHERE status = 'completed'");
        $total_orders = $wpdb->get_var("SELECT COUNT(*) FROM $donations_table");
        $total_charities = $wpdb->get_var("SELECT COUNT(DISTINCT charity_id) FROM $donations_table");
        
        ?>
        <div class="wrap omg-admin-wrap">
            <div class="omg-header">
                <img src="<?php echo OMG_WOO_PLUGIN_URL; ?>assets/images/newOMGincLogo2022Horizontal.png" alt="OM Guarantee" class="omg-logo">
                <h1>Impact Report</h1>
                <p class="omg-subtitle">See the real-world impact of your social commerce</p>
            </div>
            
            <div class="omg-impact-stats">
                <div class="omg-stat-card">
                    <h3>Total Donated</h3>
                    <div class="omg-stat-value">$<?php echo number_format($total_donated ?: 0, 2); ?></div>
                </div>
                
                <div class="omg-stat-card">
                    <h3>Orders with Impact</h3>
                    <div class="omg-stat-value"><?php echo number_format($total_orders ?: 0); ?></div>
                </div>
                
                <div class="omg-stat-card">
                    <h3>Charities Supported</h3>
                    <div class="omg-stat-value"><?php echo number_format($total_charities ?: 0); ?></div>
                </div>
            </div>
            
            <div class="omg-impact-preview">
                <h2>Certification Badge Preview</h2>
                <div class="omg-badge-preview">
                    <?php echo do_shortcode('[omg_certification_badge]'); ?>
                </div>
            </div>
        </div>
        <?php
    }
    
    public function add_product_fields() {
        global $post;
        
        echo '<div class="options_group">';
        
        woocommerce_wp_checkbox(array(
            'id' => '_omg_enable_impact',
            'label' => 'Enable OM Guarantee Impact',
            'description' => 'Enable social impact donation for this product'
        ));
        
        woocommerce_wp_text_input(array(
            'id' => '_omg_impact_percentage',
            'label' => 'Impact Percentage (%)',
            'placeholder' => get_option('omg_woo_global_percentage', '1.5'),
            'description' => 'Override global percentage for this product',
            'type' => 'number',
            'custom_attributes' => array(
                'step' => '0.1',
                'min' => '0',
                'max' => '100'
            )
        ));
        
        // Get charities for dropdown
        $every_org = new OMG_WooCommerce_EveryOrg();
        $charities = array();
        $api_key = get_option('omg_woo_every_org_api_key');
        if (!empty($api_key)) {
            $charities = $every_org->search_charities('', 20);
        }
        
        $charity_options = array('' => 'Use default charity');
        foreach ($charities as $charity) {
            $charity_options[$charity['slug']] = $charity['name'];
        }
        
        woocommerce_wp_select(array(
            'id' => '_omg_charity',
            'label' => 'Charity',
            'description' => 'Override default charity for this product',
            'options' => $charity_options
        ));
        
        echo '</div>';
    }
    
    public function save_product_fields($post_id) {
        $enable_impact = isset($_POST['_omg_enable_impact']) ? 'yes' : 'no';
        update_post_meta($post_id, '_omg_enable_impact', $enable_impact);
        
        if (isset($_POST['_omg_impact_percentage'])) {
            update_post_meta($post_id, '_omg_impact_percentage', sanitize_text_field($_POST['_omg_impact_percentage']));
        }
        
        if (isset($_POST['_omg_charity'])) {
            update_post_meta($post_id, '_omg_charity', sanitize_text_field($_POST['_omg_charity']));
        }
    }
    
    public function ajax_test_charity_search() {
        check_ajax_referer('omg_woo_admin_nonce', 'nonce');
        
        $search_term = sanitize_text_field($_POST['search_term']);
        $every_org = new OMG_WooCommerce_EveryOrg();
        $charities = $every_org->search_charities($search_term, 10);
        
        wp_send_json_success($charities);
    }
    
    public function ajax_test_donation() {
        check_ajax_referer('omg_woo_admin_nonce', 'nonce');
        
        $every_org = new OMG_WooCommerce_EveryOrg();
        $result = $every_org->test_donation_processing();
        
        wp_send_json_success($result);
    }
    
    private function show_recent_donations() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'omg_woo_donations';
        $recent_donations = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC LIMIT 5");
        
        if (empty($recent_donations)) {
            echo '<p>No recent donations</p>';
            return;
        }
        
        echo '<ul class="omg-recent-list">';
        foreach ($recent_donations as $donation) {
            echo '<li>';
            echo '<strong>$' . number_format($donation->amount, 2) . '</strong> ';
            echo 'to ' . esc_html($donation->charity_name);
            echo '<br><small>' . date('M j, Y', strtotime($donation->created_at)) . '</small>';
            echo '</li>';
        }
        echo '</ul>';
    }
}

