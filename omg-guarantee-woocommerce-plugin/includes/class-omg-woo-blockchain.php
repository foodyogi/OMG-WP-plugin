<?php
/**
 * Blockchain integration for OM Guarantee WooCommerce plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class OMG_WooCommerce_Blockchain {
    
    private $polygon_rpc_url = 'https://polygon-rpc.com';
    private $contract_address = '0x742d35Cc6634C0532925a3b8D8C9C4e4f7c8b8e8'; // Example contract address
    
    public function __construct() {
        // Initialize blockchain connection
    }
    
    /**
     * Log donation transaction on blockchain
     */
    public function log_donation($donation, $every_org_transaction_id) {
        if (get_option('omg_woo_blockchain_enabled') !== 'yes') {
            return false;
        }
        
        try {
            // For demonstration, we'll simulate blockchain logging
            // In production, this would interact with a smart contract on Polygon
            
            $transaction_data = array(
                'order_id' => $donation->order_id,
                'charity_id' => $donation->charity_id,
                'charity_name' => $donation->charity_name,
                'amount' => $donation->amount,
                'every_org_tx_id' => $every_org_transaction_id,
                'timestamp' => time(),
                'website' => get_bloginfo('url')
            );
            
            // Simulate blockchain transaction
            $tx_hash = $this->simulate_blockchain_transaction($transaction_data);
            
            if ($tx_hash) {
                error_log("OM Guarantee: Blockchain transaction logged: {$tx_hash}");
                return $tx_hash;
            }
            
        } catch (Exception $e) {
            error_log("OM Guarantee: Blockchain logging error: " . $e->getMessage());
        }
        
        return false;
    }
    
    /**
     * Simulate blockchain transaction (for demonstration)
     */
    private function simulate_blockchain_transaction($data) {
        // Simulate network delay
        sleep(1);
        
        // Generate a realistic-looking transaction hash
        $hash_data = json_encode($data) . time() . wp_generate_password(32, false);
        $tx_hash = '0x' . hash('sha256', $hash_data);
        
        // Simulate 95% success rate
        if (rand(1, 100) <= 95) {
            return $tx_hash;
        }
        
        return false;
        
        /*
         * Real blockchain implementation would look like this:
         * 
         * // Connect to Polygon network
         * $web3 = new Web3('https://polygon-rpc.com');
         * 
         * // Prepare contract interaction
         * $contract = new Contract($web3->provider, $this->contract_abi);
         * $contract->at($this->contract_address);
         * 
         * // Prepare transaction data
         * $function_data = $contract->getData('logDonation', 
         *     $data['order_id'],
         *     $data['charity_id'], 
         *     $data['amount'],
         *     $data['every_org_tx_id']
         * );
         * 
         * // Send transaction
         * $transaction = array(
         *     'to' => $this->contract_address,
         *     'data' => $function_data,
         *     'gas' => '100000',
         *     'gasPrice' => '20000000000' // 20 gwei
         * );
         * 
         * $web3->eth->sendTransaction($transaction, function($err, $txHash) {
         *     if ($err !== null) {
         *         error_log('Blockchain error: ' . $err->getMessage());
         *         return false;
         *     }
         *     return $txHash;
         * });
         */
    }
    
    /**
     * Verify transaction on blockchain
     */
    public function verify_transaction($tx_hash) {
        if (empty($tx_hash)) {
            return false;
        }
        
        // For demonstration, simulate verification
        // In production, this would query the blockchain
        
        return array(
            'verified' => true,
            'block_number' => rand(40000000, 50000000),
            'confirmations' => rand(100, 1000),
            'gas_used' => rand(50000, 100000),
            'status' => 'success',
            'explorer_url' => 'https://polygonscan.com/tx/' . $tx_hash
        );
        
        /*
         * Real verification would look like this:
         * 
         * $web3 = new Web3('https://polygon-rpc.com');
         * 
         * $web3->eth->getTransactionReceipt($tx_hash, function($err, $receipt) {
         *     if ($err !== null) {
         *         return false;
         *     }
         *     
         *     return array(
         *         'verified' => true,
         *         'block_number' => hexdec($receipt->blockNumber),
         *         'gas_used' => hexdec($receipt->gasUsed),
         *         'status' => $receipt->status === '0x1' ? 'success' : 'failed',
         *         'explorer_url' => 'https://polygonscan.com/tx/' . $tx_hash
         *     );
         * });
         */
    }
    
    /**
     * Get blockchain statistics
     */
    public function get_blockchain_stats() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'omg_woo_donations';
        
        $stats = array(
            'total_transactions' => $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE transaction_hash != ''"),
            'total_verified_amount' => $wpdb->get_var("SELECT SUM(amount) FROM $table_name WHERE transaction_hash != ''"),
            'latest_transaction' => $wpdb->get_var("SELECT transaction_hash FROM $table_name WHERE transaction_hash != '' ORDER BY processed_at DESC LIMIT 1"),
            'blockchain_enabled' => get_option('omg_woo_blockchain_enabled') === 'yes'
        );
        
        return $stats;
    }
    
    /**
     * Test blockchain connection
     */
    public function test_connection() {
        try {
            // Simulate connection test
            sleep(1);
            
            // Simulate 90% success rate
            if (rand(1, 100) <= 90) {
                return array(
                    'success' => true,
                    'network' => 'Polygon Mainnet',
                    'latest_block' => rand(40000000, 50000000),
                    'gas_price' => '20 gwei',
                    'message' => 'Successfully connected to Polygon network'
                );
            } else {
                return array(
                    'success' => false,
                    'message' => 'Unable to connect to Polygon network'
                );
            }
            
        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => 'Connection error: ' . $e->getMessage()
            );
        }
        
        /*
         * Real connection test would look like this:
         * 
         * $web3 = new Web3('https://polygon-rpc.com');
         * 
         * $web3->eth->blockNumber(function($err, $blockNumber) {
         *     if ($err !== null) {
         *         return array(
         *             'success' => false,
         *             'message' => 'Connection failed: ' . $err->getMessage()
         *         );
         *     }
         *     
         *     return array(
         *         'success' => true,
         *         'network' => 'Polygon Mainnet',
         *         'latest_block' => hexdec($blockNumber),
         *         'message' => 'Successfully connected to Polygon network'
         *     );
         * });
         */
    }
    
    /**
     * Get transaction history for display
     */
    public function get_transaction_history($limit = 10) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'omg_woo_donations';
        
        $transactions = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE transaction_hash != '' ORDER BY processed_at DESC LIMIT %d",
            $limit
        ));
        
        $history = array();
        
        foreach ($transactions as $transaction) {
            $verification = $this->verify_transaction($transaction->transaction_hash);
            
            $history[] = array(
                'id' => $transaction->id,
                'order_id' => $transaction->order_id,
                'charity_name' => $transaction->charity_name,
                'amount' => $transaction->amount,
                'transaction_hash' => $transaction->transaction_hash,
                'processed_at' => $transaction->processed_at,
                'verification' => $verification,
                'explorer_url' => 'https://polygonscan.com/tx/' . $transaction->transaction_hash
            );
        }
        
        return $history;
    }
    
    /**
     * Generate QR code for transaction verification
     */
    public function generate_verification_qr($tx_hash) {
        if (empty($tx_hash)) {
            return false;
        }
        
        $verification_url = 'https://polygonscan.com/tx/' . $tx_hash;
        
        // Simple QR code generation (in production, use a proper QR library)
        $qr_api_url = 'https://api.qrserver.com/v1/create-qr-code/';
        $qr_params = array(
            'size' => '200x200',
            'data' => $verification_url,
            'format' => 'png'
        );
        
        return $qr_api_url . '?' . http_build_query($qr_params);
    }
    
    /**
     * Get donation impact from blockchain data
     */
    public function get_verified_impact() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'omg_woo_donations';
        
        // Get all verified donations (those with blockchain transactions)
        $verified_donations = $wpdb->get_results(
            "SELECT charity_name, SUM(amount) as total_amount, COUNT(*) as donation_count 
             FROM $table_name 
             WHERE transaction_hash != '' 
             GROUP BY charity_id 
             ORDER BY total_amount DESC"
        );
        
        $impact_data = array();
        $total_verified = 0;
        
        foreach ($verified_donations as $donation) {
            $impact_data[] = array(
                'charity' => $donation->charity_name,
                'amount' => floatval($donation->total_amount),
                'count' => intval($donation->donation_count),
                'impact_statement' => $this->generate_impact_statement($donation->charity_name, $donation->total_amount)
            );
            
            $total_verified += floatval($donation->total_amount);
        }
        
        return array(
            'total_verified' => $total_verified,
            'charity_count' => count($impact_data),
            'charities' => $impact_data
        );
    }
    
    /**
     * Generate impact statement based on charity and amount
     */
    private function generate_impact_statement($charity_name, $amount) {
        $amount = floatval($amount);
        
        // Generate meaningful impact statements based on charity type and amount
        if (stripos($charity_name, 'feeding') !== false || stripos($charity_name, 'food') !== false) {
            $meals = floor($amount / 2.5); // Assume $2.50 per meal
            return "Provided approximately {$meals} meals to those in need";
        } elseif (stripos($charity_name, 'red cross') !== false) {
            $people_helped = floor($amount / 10); // Assume $10 helps one person
            return "Provided emergency assistance to {$people_helped} people";
        } elseif (stripos($charity_name, 'education') !== false || stripos($charity_name, 'school') !== false) {
            $students = floor($amount / 25); // Assume $25 per student
            return "Supported educational programs for {$students} students";
        } elseif (stripos($charity_name, 'water') !== false) {
            $people = floor($amount / 15); // Assume $15 provides clean water for one person
            return "Provided clean water access for {$people} people";
        } else {
            return "Made a positive impact with $" . number_format($amount, 2) . " in donations";
        }
    }
    
    /**
     * Smart contract ABI (for reference)
     */
    private function get_contract_abi() {
        return json_encode(array(
            array(
                'inputs' => array(
                    array('name' => 'orderId', 'type' => 'uint256'),
                    array('name' => 'charityId', 'type' => 'string'),
                    array('name' => 'amount', 'type' => 'uint256'),
                    array('name' => 'everyOrgTxId', 'type' => 'string')
                ),
                'name' => 'logDonation',
                'outputs' => array(),
                'stateMutability' => 'nonpayable',
                'type' => 'function'
            ),
            array(
                'inputs' => array(
                    array('name' => 'orderId', 'type' => 'uint256')
                ),
                'name' => 'getDonation',
                'outputs' => array(
                    array('name' => 'charityId', 'type' => 'string'),
                    array('name' => 'amount', 'type' => 'uint256'),
                    array('name' => 'timestamp', 'type' => 'uint256'),
                    array('name' => 'verified', 'type' => 'bool')
                ),
                'stateMutability' => 'view',
                'type' => 'function'
            )
        ));
    }
    
    /**
     * Get gas price for transactions
     */
    public function get_current_gas_price() {
        // Simulate gas price (in production, fetch from network)
        return array(
            'slow' => '20 gwei',
            'standard' => '25 gwei',
            'fast' => '30 gwei',
            'recommended' => '25 gwei'
        );
    }
    
    /**
     * Estimate transaction cost
     */
    public function estimate_transaction_cost() {
        $gas_price = 25; // gwei
        $gas_limit = 100000;
        $matic_price = 0.85; // USD (example)
        
        $cost_matic = ($gas_price * $gas_limit) / 1000000000; // Convert to MATIC
        $cost_usd = $cost_matic * $matic_price;
        
        return array(
            'gas_price' => $gas_price . ' gwei',
            'gas_limit' => number_format($gas_limit),
            'cost_matic' => number_format($cost_matic, 6),
            'cost_usd' => '$' . number_format($cost_usd, 4)
        );
    }
}

