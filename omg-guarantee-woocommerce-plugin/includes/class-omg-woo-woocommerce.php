<?php
/**
 * WooCommerce integration for OM Guarantee plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class OMG_WooCommerce_Integration {
    
    public function __construct() {
        // Order processing hooks
        add_action('woocommerce_checkout_order_processed', array($this, 'process_order_impact'), 10, 3);
        add_action('woocommerce_order_status_completed', array($this, 'finalize_order_impact'));
        add_action('woocommerce_order_status_cancelled', array($this, 'cancel_order_impact'));
        add_action('woocommerce_order_status_refunded', array($this, 'refund_order_impact'));
        
        // Product page hooks
        add_filter('woocommerce_get_price_html', array($this, 'add_impact_to_price'), 10, 2);
        
        // Cart and checkout hooks
        add_action('woocommerce_cart_calculate_fees', array($this, 'add_impact_fee_display'));
        
        // Admin order hooks
        add_action('woocommerce_admin_order_data_after_billing_address', array($this, 'display_order_impact_admin'));
        
        // Email hooks
        add_action('woocommerce_email_after_order_table', array($this, 'add_impact_to_emails'), 10, 4);
    }
    
    public function process_order_impact($order_id, $posted_data, $order) {
        if (!$order) {
            return;
        }
        
        // Check if OM Guarantee is enabled
        if (get_option('omg_woo_enabled') !== 'yes') {
            return;
        }
        
        $total_impact = 0;
        $donations = array();
        
        // Process each order item
        foreach ($order->get_items() as $item_id => $item) {
            $product_id = $item->get_product_id();
            $quantity = $item->get_quantity();
            $line_total = $item->get_total();
            
            // Check if impact is enabled for this product
            $enable_impact = get_post_meta($product_id, '_omg_enable_impact', true);
            
            if ($enable_impact === 'no') {
                continue;
            }
            
            // If not explicitly disabled and global is enabled, process impact
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
            
            if (empty($charity_id)) {
                continue; // Skip if no charity selected
            }
            
            // Calculate impact amount
            $impact_amount = ($line_total * floatval($percentage)) / 100;
            
            if ($impact_amount > 0) {
                $total_impact += $impact_amount;
                
                // Group by charity
                if (!isset($donations[$charity_id])) {
                    $donations[$charity_id] = array(
                        'charity_id' => $charity_id,
                        'amount' => 0,
                        'percentage' => $percentage,
                        'items' => array()
                    );
                }
                
                $donations[$charity_id]['amount'] += $impact_amount;
                $donations[$charity_id]['items'][] = array(
                    'product_id' => $product_id,
                    'item_id' => $item_id,
                    'amount' => $impact_amount,
                    'percentage' => $percentage
                );
            }
        }
        
        // Save donations to database
        if (!empty($donations)) {
            $this->save_order_donations($order_id, $donations);
            
            // Add order meta
            $order->update_meta_data('_omg_total_impact', $total_impact);
            $order->update_meta_data('_omg_donation_count', count($donations));
            $order->save();
            
            // Log the impact
            error_log("OM Guarantee: Order #{$order_id} processed with ${total_impact} total impact across " . count($donations) . " charities");
        }
    }
    
    public function finalize_order_impact($order_id) {
        // When order is completed, mark donations as ready for processing
        global $wpdb;
        $table_name = $wpdb->prefix . 'omg_woo_donations';
        
        $wpdb->update(
            $table_name,
            array('status' => 'ready'),
            array('order_id' => $order_id, 'status' => 'pending'),
            array('%s'),
            array('%d', '%s')
        );
        
        error_log("OM Guarantee: Order #{$order_id} completed, donations marked as ready for processing");
    }
    
    public function cancel_order_impact($order_id) {
        // Cancel pending donations
        global $wpdb;
        $table_name = $wpdb->prefix . 'omg_woo_donations';
        
        $wpdb->update(
            $table_name,
            array('status' => 'cancelled'),
            array('order_id' => $order_id),
            array('%s'),
            array('%d')
        );
        
        error_log("OM Guarantee: Order #{$order_id} cancelled, donations cancelled");
    }
    
    public function refund_order_impact($order_id) {
        // Handle refunded orders
        global $wpdb;
        $table_name = $wpdb->prefix . 'omg_woo_donations';
        
        $wpdb->update(
            $table_name,
            array('status' => 'refunded'),
            array('order_id' => $order_id),
            array('%s'),
            array('%d')
        );
        
        error_log("OM Guarantee: Order #{$order_id} refunded, donations marked as refunded");
    }
    
    public function add_impact_to_price($price_html, $product) {
        if (!is_admin() && get_option('omg_woo_show_on_product_page') === 'yes') {
            $product_id = $product->get_id();
            $enable_impact = get_post_meta($product_id, '_omg_enable_impact', true);
            
            // Check if impact is enabled
            if ($enable_impact === 'no') {
                return $price_html;
            }
            
            if ($enable_impact !== 'yes' && get_option('omg_woo_enabled') !== 'yes') {
                return $price_html;
            }
            
            $percentage = get_post_meta($product_id, '_omg_impact_percentage', true);
            if (empty($percentage)) {
                $percentage = get_option('omg_woo_global_percentage', '1.5');
            }
            
            $price = $product->get_price();
            if ($price > 0) {
                $impact_amount = ($price * floatval($percentage)) / 100;
                $price_html .= '<br><small class="omg-price-impact">+$' . number_format($impact_amount, 2) . ' social impact</small>';
            }
        }
        
        return $price_html;
    }
    
    public function add_impact_fee_display() {
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }
        
        if (get_option('omg_woo_show_in_cart') !== 'yes') {
            return;
        }
        
        $cart = WC()->cart;
        if (!$cart || $cart->is_empty()) {
            return;
        }
        
        $total_impact = $this->calculate_cart_impact_amount();
        
        if ($total_impact > 0) {
            // Add a virtual fee for display purposes (not charged)
            $cart->add_fee('Social Impact (included)', 0);
        }
    }
    
    public function display_order_impact_admin($order) {
        $total_impact = $order->get_meta('_omg_total_impact');
        $donation_count = $order->get_meta('_omg_donation_count');
        
        if ($total_impact > 0) {
            ?>
            <div class="omg-admin-order-impact">
                <h3>OM Guarantee Impact</h3>
                <p><strong>Total Social Impact:</strong> $<?php echo number_format($total_impact, 2); ?></p>
                <p><strong>Charities Benefited:</strong> <?php echo intval($donation_count); ?></p>
                
                <?php
                // Show detailed donations
                global $wpdb;
                $table_name = $wpdb->prefix . 'omg_woo_donations';
                $donations = $wpdb->get_results($wpdb->prepare(
                    "SELECT * FROM $table_name WHERE order_id = %d",
                    $order->get_id()
                ));
                
                if (!empty($donations)) {
                    echo '<ul>';
                    foreach ($donations as $donation) {
                        echo '<li>';
                        echo '$' . number_format($donation->amount, 2) . ' to ' . esc_html($donation->charity_name);
                        echo ' <small>(' . $donation->status . ')</small>';
                        if (!empty($donation->transaction_hash)) {
                            echo ' <a href="https://polygonscan.com/tx/' . esc_attr($donation->transaction_hash) . '" target="_blank">View Transaction</a>';
                        }
                        echo '</li>';
                    }
                    echo '</ul>';
                }
                ?>
            </div>
            <style>
            .omg-admin-order-impact {
                background: #f9f9f9;
                padding: 15px;
                margin: 15px 0;
                border-radius: 5px;
                border-left: 4px solid #667eea;
            }
            </style>
            <?php
        }
    }
    
    public function add_impact_to_emails($order, $sent_to_admin, $plain_text, $email) {
        $total_impact = $order->get_meta('_omg_total_impact');
        
        if ($total_impact > 0) {
            if ($plain_text) {
                echo "\n\n" . "SOCIAL IMPACT" . "\n";
                echo "Your purchase contributed $" . number_format($total_impact, 2) . " to social causes!" . "\n";
                echo "Thank you for making a difference!" . "\n";
            } else {
                ?>
                <div style="background: #f9f9f9; padding: 20px; margin: 20px 0; border-radius: 5px; border-left: 4px solid #667eea;">
                    <h3 style="margin: 0 0 10px 0; color: #333;">🎉 Social Impact</h3>
                    <p style="margin: 0;">Your purchase contributed <strong>$<?php echo number_format($total_impact, 2); ?></strong> to social causes!</p>
                    <p style="margin: 5px 0 0 0; font-size: 14px; color: #666;">Thank you for making a difference!</p>
                </div>
                <?php
            }
        }
    }
    
    private function save_order_donations($order_id, $donations) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'omg_woo_donations';
        
        foreach ($donations as $charity_id => $donation_data) {
            // Get charity name
            $every_org = new OMG_WooCommerce_EveryOrg();
            $charity = $every_org->get_charity_by_id($charity_id);
            $charity_name = !empty($charity['name']) ? $charity['name'] : 'Unknown Charity';
            
            $wpdb->insert(
                $table_name,
                array(
                    'order_id' => $order_id,
                    'charity_id' => $charity_id,
                    'charity_name' => $charity_name,
                    'amount' => $donation_data['amount'],
                    'percentage' => $donation_data['percentage'],
                    'status' => 'pending',
                    'created_at' => current_time('mysql')
                ),
                array('%d', '%s', '%s', '%f', '%f', '%s', '%s')
            );
        }
    }
    
    private function calculate_cart_impact_amount() {
        $cart = WC()->cart;
        $total_impact = 0;
        
        if (!$cart || $cart->is_empty()) {
            return 0;
        }
        
        foreach ($cart->get_cart() as $cart_item) {
            $product_id = $cart_item['product_id'];
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
            
            $impact_amount = ($line_total * floatval($percentage)) / 100;
            $total_impact += $impact_amount;
        }
        
        return $total_impact;
    }
    
    public function get_order_impact_details($order_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'omg_woo_donations';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE order_id = %d",
            $order_id
        ));
    }
    
    public function get_customer_total_impact($customer_id) {
        $orders = wc_get_orders(array(
            'customer_id' => $customer_id,
            'limit' => -1,
            'return' => 'ids'
        ));
        
        if (empty($orders)) {
            return 0;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'omg_woo_donations';
        $order_ids = implode(',', array_map('intval', $orders));
        
        return $wpdb->get_var("SELECT SUM(amount) FROM $table_name WHERE order_id IN ($order_ids) AND status = 'completed'");
    }
}

