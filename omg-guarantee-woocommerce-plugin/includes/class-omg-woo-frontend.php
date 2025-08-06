<?php
/**
 * Frontend functionality for OM Guarantee WooCommerce plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class OMG_WooCommerce_Frontend {
    
    public function __construct() {
        // WooCommerce hooks for displaying impact information
        add_action('woocommerce_single_product_summary', array($this, 'show_product_impact'), 25);
        add_action('woocommerce_cart_totals_after_order_total', array($this, 'show_cart_impact'));
        add_action('woocommerce_review_order_after_order_total', array($this, 'show_checkout_impact'));
        add_action('woocommerce_thankyou', array($this, 'show_thankyou_impact'));
    }
    
    public function render_certification_badge($atts) {
        $theme = $atts['theme'];
        $size = $atts['size'];
        $business = $atts['business'];
        $impact = $atts['impact'];
        
        // Get settings
        $enabled = get_option('omg_woo_enabled', 'yes');
        if ($enabled !== 'yes') {
            return '';
        }
        
        // Determine image based on theme
        $cert_image = ($theme === 'dark') ? 'OMGcertificate2022INVERTED-366.png' : 'OMGcertificate2022.png';
        $cert_url = OMG_WOO_PLUGIN_URL . 'assets/images/' . $cert_image;
        
        // Get impact statement
        if (empty($impact)) {
            $impact = $this->get_impact_statement();
        }
        
        // Size classes
        $size_class = 'omg-badge-' . $size;
        $theme_class = 'omg-badge-' . $theme;
        
        ob_start();
        ?>
        <div class="omg-certification-badge <?php echo esc_attr($size_class . ' ' . $theme_class); ?>">
            <div class="omg-badge-container">
                <div class="omg-badge-image">
                    <img src="<?php echo esc_url($cert_url); ?>" alt="OM Guarantee Certification" class="omg-cert-image">
                </div>
                <div class="omg-badge-content">
                    <div class="omg-badge-text">
                        <strong><?php echo esc_html($business); ?></strong> is OM Guarantee certified. We have made the following certified social impact:
                    </div>
                    <div class="omg-impact-statement">
                        <?php echo esc_html($impact); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public function render_impact_counter($atts) {
        $type = $atts['type'];
        $period = $atts['period'];
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'omg_woo_donations';
        
        // Build query based on parameters
        $where_clause = "WHERE status = 'completed'";
        
        if ($period !== 'all') {
            switch ($period) {
                case 'month':
                    $where_clause .= " AND created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
                    break;
                case 'year':
                    $where_clause .= " AND created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
                    break;
                case 'week':
                    $where_clause .= " AND created_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
                    break;
            }
        }
        
        if ($type === 'total') {
            $total = $wpdb->get_var("SELECT SUM(amount) FROM $table_name $where_clause");
            $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name $where_clause");
        } else {
            $total = $wpdb->get_var("SELECT SUM(amount) FROM $table_name $where_clause");
            $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name $where_clause");
        }
        
        ob_start();
        ?>
        <div class="omg-impact-counter">
            <div class="omg-counter-stats">
                <div class="omg-stat">
                    <div class="omg-stat-value">$<?php echo number_format($total ?: 0, 2); ?></div>
                    <div class="omg-stat-label">Total Donated</div>
                </div>
                <div class="omg-stat">
                    <div class="omg-stat-value"><?php echo number_format($count ?: 0); ?></div>
                    <div class="omg-stat-label">Orders with Impact</div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public function render_blockchain_verification($atts) {
        $show_latest = $atts['show_latest'] === 'true';
        $limit = intval($atts['limit']);
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'omg_woo_donations';
        
        $transactions = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE status = 'completed' AND transaction_hash != '' ORDER BY processed_at DESC LIMIT %d",
            $limit
        ));
        
        ob_start();
        ?>
        <div class="omg-blockchain-verification">
            <h3>Blockchain Verification</h3>
            <?php if (empty($transactions)): ?>
                <p>No verified transactions yet. Transactions will appear here after donations are processed.</p>
            <?php else: ?>
                <div class="omg-transactions">
                    <?php foreach ($transactions as $transaction): ?>
                        <div class="omg-transaction">
                            <div class="omg-tx-info">
                                <strong>$<?php echo number_format($transaction->amount, 2); ?></strong>
                                donated to <?php echo esc_html($transaction->charity_name); ?>
                            </div>
                            <div class="omg-tx-date">
                                <?php echo date('M j, Y', strtotime($transaction->processed_at)); ?>
                            </div>
                            <div class="omg-tx-link">
                                <a href="https://polygonscan.com/tx/<?php echo esc_attr($transaction->transaction_hash); ?>" 
                                   target="_blank" class="omg-blockchain-link">
                                    View on PolygonScan →
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public function render_customer_impact($atts) {
        $order_id = $atts['order_id'];
        $customer_id = $atts['customer_id'];
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'omg_woo_donations';
        
        $where_clause = "WHERE status = 'completed'";
        $params = array();
        
        if (!empty($order_id)) {
            $where_clause .= " AND order_id = %d";
            $params[] = $order_id;
        } elseif (!empty($customer_id)) {
            // Get orders for this customer
            $orders = wc_get_orders(array(
                'customer_id' => $customer_id,
                'limit' => -1,
                'return' => 'ids'
            ));
            
            if (!empty($orders)) {
                $order_ids = implode(',', array_map('intval', $orders));
                $where_clause .= " AND order_id IN ($order_ids)";
            } else {
                $where_clause .= " AND order_id = 0"; // No orders found
            }
        }
        
        if (!empty($params)) {
            $donations = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $table_name $where_clause ORDER BY created_at DESC",
                ...$params
            ));
        } else {
            $donations = $wpdb->get_results("SELECT * FROM $table_name $where_clause ORDER BY created_at DESC");
        }
        
        $total_impact = array_sum(array_column($donations, 'amount'));
        
        ob_start();
        ?>
        <div class="omg-customer-impact">
            <h3>Your Social Impact</h3>
            <?php if (empty($donations)): ?>
                <p>No impact recorded yet. Your future purchases will contribute to social causes!</p>
            <?php else: ?>
                <div class="omg-impact-summary">
                    <div class="omg-total-impact">
                        <strong>Total Impact: $<?php echo number_format($total_impact, 2); ?></strong>
                    </div>
                    <div class="omg-impact-details">
                        <?php foreach ($donations as $donation): ?>
                            <div class="omg-impact-item">
                                <span class="omg-amount">$<?php echo number_format($donation->amount, 2); ?></span>
                                donated to <strong><?php echo esc_html($donation->charity_name); ?></strong>
                                <small>(<?php echo date('M j, Y', strtotime($donation->created_at)); ?>)</small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public function render_donation_tracker($atts) {
        $charity = $atts['charity'];
        $period = $atts['period'];
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'omg_woo_donations';
        
        $where_clause = "WHERE status = 'completed'";
        $params = array();
        
        if (!empty($charity)) {
            $where_clause .= " AND charity_id = %s";
            $params[] = $charity;
        }
        
        // Add period filter
        switch ($period) {
            case 'week':
                $where_clause .= " AND created_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
                break;
            case 'month':
                $where_clause .= " AND created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
                break;
            case 'year':
                $where_clause .= " AND created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
                break;
        }
        
        if (!empty($params)) {
            $donations = $wpdb->get_results($wpdb->prepare(
                "SELECT charity_name, SUM(amount) as total_amount, COUNT(*) as donation_count FROM $table_name $where_clause GROUP BY charity_id ORDER BY total_amount DESC",
                ...$params
            ));
        } else {
            $donations = $wpdb->get_results("SELECT charity_name, SUM(amount) as total_amount, COUNT(*) as donation_count FROM $table_name $where_clause GROUP BY charity_id ORDER BY total_amount DESC");
        }
        
        ob_start();
        ?>
        <div class="omg-donation-tracker">
            <h3>Donation Tracking - <?php echo ucfirst($period); ?></h3>
            <?php if (empty($donations)): ?>
                <p>No donations tracked for this period.</p>
            <?php else: ?>
                <div class="omg-charity-list">
                    <?php foreach ($donations as $donation): ?>
                        <div class="omg-charity-item">
                            <div class="omg-charity-name"><?php echo esc_html($donation->charity_name); ?></div>
                            <div class="omg-charity-stats">
                                <span class="omg-amount">$<?php echo number_format($donation->total_amount, 2); ?></span>
                                <span class="omg-count">(<?php echo $donation->donation_count; ?> donations)</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public function show_product_impact() {
        if (get_option('omg_woo_show_on_product_page') !== 'yes') {
            return;
        }
        
        global $product;
        
        if (!$product) {
            return;
        }
        
        $product_id = $product->get_id();
        $enable_impact = get_post_meta($product_id, '_omg_enable_impact', true);
        
        // Check if impact is enabled for this product
        if ($enable_impact === 'no') {
            return;
        }
        
        // If not explicitly disabled and global is enabled, show impact
        if ($enable_impact !== 'yes' && get_option('omg_woo_enabled') !== 'yes') {
            return;
        }
        
        $percentage = get_post_meta($product_id, '_omg_impact_percentage', true);
        if (empty($percentage)) {
            $percentage = get_option('omg_woo_global_percentage', '1.5');
        }
        
        $charity_id = get_post_meta($product_id, '_omg_charity', true);
        if (empty($charity_id)) {
            $charity_id = get_option('omg_woo_default_charity');
        }
        
        // Get charity name
        $charity_name = $this->get_charity_name($charity_id);
        
        if (empty($charity_name)) {
            $charity_name = 'selected charity';
        }
        
        $price = $product->get_price();
        $impact_amount = ($price * $percentage) / 100;
        
        ?>
        <div class="omg-product-impact">
            <div class="omg-impact-badge">
                <span class="omg-impact-icon">💝</span>
                <div class="omg-impact-text">
                    <strong><?php echo $percentage; ?>%</strong> of this purchase 
                    (<strong>$<?php echo number_format($impact_amount, 2); ?></strong>) 
                    will be donated to <strong><?php echo esc_html($charity_name); ?></strong>
                </div>
            </div>
        </div>
        <?php
    }
    
    public function show_cart_impact() {
        if (get_option('omg_woo_show_in_cart') !== 'yes') {
            return;
        }
        
        $impact_data = $this->calculate_cart_impact();
        
        if ($impact_data['total_impact'] <= 0) {
            return;
        }
        
        ?>
        <tr class="omg-cart-impact">
            <th>Social Impact</th>
            <td>
                <div class="omg-cart-impact-details">
                    <strong>$<?php echo number_format($impact_data['total_impact'], 2); ?></strong>
                    will be donated to support social causes
                    <?php if (count($impact_data['charities']) === 1): ?>
                        <br><small>Benefiting: <?php echo esc_html(array_keys($impact_data['charities'])[0]); ?></small>
                    <?php elseif (count($impact_data['charities']) > 1): ?>
                        <br><small>Benefiting <?php echo count($impact_data['charities']); ?> charities</small>
                    <?php endif; ?>
                </div>
            </td>
        </tr>
        <?php
    }
    
    public function show_checkout_impact() {
        if (get_option('omg_woo_show_at_checkout') !== 'yes') {
            return;
        }
        
        $impact_data = $this->calculate_cart_impact();
        
        if ($impact_data['total_impact'] <= 0) {
            return;
        }
        
        ?>
        <tr class="omg-checkout-impact">
            <th>Your Social Impact</th>
            <td>
                <div class="omg-checkout-impact-details">
                    <div class="omg-impact-amount">
                        $<?php echo number_format($impact_data['total_impact'], 2); ?>
                    </div>
                    <div class="omg-impact-description">
                        This purchase will make a real difference!
                        <?php if (!empty($impact_data['charities'])): ?>
                            <br><small>Supporting: <?php echo implode(', ', array_keys($impact_data['charities'])); ?></small>
                        <?php endif; ?>
                    </div>
                </div>
            </td>
        </tr>
        <?php
    }
    
    public function show_thankyou_impact($order_id) {
        if (empty($order_id)) {
            return;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'omg_woo_donations';
        
        $donations = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE order_id = %d",
            $order_id
        ));
        
        if (empty($donations)) {
            return;
        }
        
        $total_impact = array_sum(array_column($donations, 'amount'));
        
        ?>
        <div class="omg-thankyou-impact">
            <h2>🎉 Thank You for Making an Impact!</h2>
            <div class="omg-impact-summary">
                <p>Your purchase has contributed <strong>$<?php echo number_format($total_impact, 2); ?></strong> to social causes:</p>
                <ul class="omg-donation-list">
                    <?php foreach ($donations as $donation): ?>
                        <li>
                            <strong>$<?php echo number_format($donation->amount, 2); ?></strong> 
                            to <?php echo esc_html($donation->charity_name); ?>
                            <?php if ($donation->status === 'completed' && !empty($donation->transaction_hash)): ?>
                                <a href="https://polygonscan.com/tx/<?php echo esc_attr($donation->transaction_hash); ?>" 
                                   target="_blank" class="omg-verify-link">Verify on Blockchain</a>
                            <?php elseif ($donation->status === 'pending'): ?>
                                <small>(Processing...)</small>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <p><small>Your donations will be processed within 24 hours and recorded on the blockchain for transparency.</small></p>
            </div>
        </div>
        <?php
    }
    
    private function calculate_cart_impact() {
        $cart = WC()->cart;
        $total_impact = 0;
        $charities = array();
        
        if (!$cart || $cart->is_empty()) {
            return array('total_impact' => 0, 'charities' => array());
        }
        
        foreach ($cart->get_cart() as $cart_item) {
            $product_id = $cart_item['product_id'];
            $quantity = $cart_item['quantity'];
            $line_total = $cart_item['line_total'];
            
            // Check if impact is enabled for this product
            $enable_impact = get_post_meta($product_id, '_omg_enable_impact', true);
            
            if ($enable_impact === 'no') {
                continue;
            }
            
            if ($enable_impact !== 'yes' && get_option('omg_woo_enabled') !== 'yes') {
                continue;
            }
            
            // Get impact percentage
            $percentage = get_post_meta($product_id, '_omg_impact_percentage', true);
            if (empty($percentage)) {
                $percentage = get_option('omg_woo_global_percentage', '1.5');
            }
            
            // Get charity
            $charity_id = get_post_meta($product_id, '_omg_charity', true);
            if (empty($charity_id)) {
                $charity_id = get_option('omg_woo_default_charity');
            }
            
            $charity_name = $this->get_charity_name($charity_id);
            
            if (!empty($charity_name)) {
                $impact_amount = ($line_total * $percentage) / 100;
                $total_impact += $impact_amount;
                
                if (!isset($charities[$charity_name])) {
                    $charities[$charity_name] = 0;
                }
                $charities[$charity_name] += $impact_amount;
            }
        }
        
        return array(
            'total_impact' => $total_impact,
            'charities' => $charities
        );
    }
    
    private function get_charity_name($charity_id) {
        if (empty($charity_id)) {
            return '';
        }
        
        // Try to get from Every.org API
        $every_org = new OMG_WooCommerce_EveryOrg();
        $charity = $every_org->get_charity_by_id($charity_id);
        
        if (!empty($charity['name'])) {
            return $charity['name'];
        }
        
        // Fallback to stored name or default
        return 'Selected Charity';
    }
    
    private function get_impact_statement() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'omg_woo_donations';
        
        $total_donated = $wpdb->get_var("SELECT SUM(amount) FROM $table_name WHERE status = 'completed'");
        $total_orders = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE status = 'completed'");
        
        if ($total_donated > 0) {
            if ($total_orders > 1) {
                return "Donated $" . number_format($total_donated, 2) . " across " . $total_orders . " orders";
            } else {
                return "Donated $" . number_format($total_donated, 2) . " to social causes";
            }
        }
        
        // Default impact statement
        $percentage = get_option('omg_woo_global_percentage', '1.5');
        $charity_id = get_option('omg_woo_default_charity');
        $charity_name = $this->get_charity_name($charity_id);
        
        if (!empty($charity_name)) {
            return "Donates " . $percentage . "% of revenue to " . $charity_name;
        }
        
        return "Committed to donating " . $percentage . "% of revenue to social causes";
    }
}

