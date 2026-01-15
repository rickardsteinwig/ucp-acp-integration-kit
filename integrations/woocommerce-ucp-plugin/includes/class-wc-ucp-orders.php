<?php
/**
 * UCP Orders Handler
 *
 * Manages order retrieval and updates for UCP
 *
 * @package WooCommerce_UCP
 */

if (!defined('ABSPATH')) {
    exit;
}

class WC_UCP_Orders {
    /**
     * Singleton instance
     */
    private static $instance = null;

    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialize
     */
    public function __construct() {
        // Hook into order status changes for webhooks
        add_action('woocommerce_order_status_changed', array($this, 'order_status_changed'), 10, 4);
    }

    /**
     * Get order
     */
    public function get_order($order_id) {
        $order = wc_get_order($order_id);

        if (!$order) {
            return null;
        }

        return $this->format_order($order);
    }

    /**
     * Format order for UCP
     */
    private function format_order($order) {
        $line_items = array();

        foreach ($order->get_items() as $item) {
            $product = $item->get_product();
            $price = (int) ($item->get_total() * 100 / $item->get_quantity());

            $line_items[] = array(
                'id' => (string) $item->get_id(),
                'item' => array(
                    'id' => (string) $product->get_id(),
                    'title' => $item->get_name(),
                    'price' => $price,
                    'image_url' => wp_get_attachment_url($product->get_image_id())
                ),
                'quantity' => $item->get_quantity(),
                'totals' => array(
                    array('type' => 'total', 'amount' => (int) ($item->get_total() * 100))
                )
            );
        }

        $totals = array(
            array('type' => 'subtotal', 'amount' => (int) ($order->get_subtotal() * 100)),
            array('type' => 'tax', 'amount' => (int) ($order->get_total_tax() * 100)),
            array('type' => 'shipping', 'amount' => (int) ($order->get_shipping_total() * 100)),
            array('type' => 'total', 'amount' => (int) ($order->get_total() * 100))
        );

        return array(
            'ucp' => array(
                'version' => '2026-01-11'
            ),
            'id' => (string) $order->get_id(),
            'checkout_id' => $order->get_meta('_ucp_session_id'),
            'status' => $this->map_order_status($order->get_status()),
            'created_at' => $order->get_date_created()->format('c'),
            'line_items' => $line_items,
            'buyer' => array(
                'email' => $order->get_billing_email(),
                'first_name' => $order->get_billing_first_name(),
                'last_name' => $order->get_billing_last_name(),
                'phone_number' => $order->get_billing_phone()
            ),
            'totals' => $totals,
            'currency' => $order->get_currency(),
            'permalink_url' => $order->get_view_order_url(),
            'fulfillment' => $this->get_fulfillment_info($order)
        );
    }

    /**
     * Get fulfillment information
     */
    private function get_fulfillment_info($order) {
        return array(
            'method_type' => 'shipping',
            'destination' => array(
                'full_name' => $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name(),
                'street_address' => $order->get_shipping_address_1(),
                'address_locality' => $order->get_shipping_city(),
                'address_region' => $order->get_shipping_state(),
                'postal_code' => $order->get_shipping_postcode(),
                'address_country' => $order->get_shipping_country()
            ),
            'status' => $this->get_fulfillment_status($order),
            'tracking_number' => $order->get_meta('_tracking_number'),
            'tracking_url' => $order->get_meta('_tracking_url')
        );
    }

    /**
     * Get fulfillment status
     */
    private function get_fulfillment_status($order) {
        $status = $order->get_status();

        switch ($status) {
            case 'pending':
            case 'processing':
                return 'pending';
            case 'on-hold':
                return 'on_hold';
            case 'completed':
                return 'delivered';
            case 'cancelled':
                return 'cancelled';
            case 'refunded':
                return 'returned';
            default:
                return 'pending';
        }
    }

    /**
     * Map WooCommerce order status to UCP
     */
    private function map_order_status($wc_status) {
        $status_map = array(
            'pending' => 'pending',
            'processing' => 'confirmed',
            'on-hold' => 'on_hold',
            'completed' => 'completed',
            'cancelled' => 'cancelled',
            'refunded' => 'refunded',
            'failed' => 'failed'
        );

        return isset($status_map[$wc_status]) ? $status_map[$wc_status] : 'pending';
    }

    /**
     * Handle order status changes (for webhooks)
     */
    public function order_status_changed($order_id, $old_status, $new_status, $order) {
        // Get webhook URL if configured
        $webhook_url = get_option('wc_ucp_webhook_url');

        if (!$webhook_url) {
            return;
        }

        // Format order data
        $order_data = $this->format_order($order);

        // Send webhook
        $this->send_webhook($webhook_url, array(
            'event' => 'order.status_changed',
            'order' => $order_data,
            'old_status' => $old_status,
            'new_status' => $new_status,
            'timestamp' => current_time('c')
        ));
    }

    /**
     * Send webhook
     */
    private function send_webhook($url, $data) {
        $response = wp_remote_post($url, array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'X-WC-UCP-Event' => $data['event']
            ),
            'body' => wp_json_encode($data),
            'timeout' => 15
        ));

        if (is_wp_error($response)) {
            error_log('WC UCP Webhook Error: ' . $response->get_error_message());
        }
    }
}
