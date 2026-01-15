<?php
/**
 * UCP Checkout Handler
 *
 * Manages checkout sessions and maps to WooCommerce orders
 *
 * @package WooCommerce_UCP
 */

if (!defined('ABSPATH')) {
    exit;
}

class WC_UCP_Checkout {
    /**
     * Singleton instance
     */
    private static $instance = null;

    /**
     * Session storage key
     */
    private $session_meta_key = '_ucp_session_data';

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
     * Create checkout session
     */
    public function create_session($data, $idempotency_key) {
        global $wpdb;

        // Generate session ID
        $session_id = $this->generate_session_id();

        // Create WooCommerce cart
        $cart = $this->create_cart_from_line_items($data['line_items']);

        // Calculate totals
        $totals = $this->calculate_totals($cart);

        // Build UCP session
        $session = array(
            'ucp' => array(
                'version' => '2026-01-11',
                'capabilities' => array(
                    array(
                        'name' => 'dev.ucp.shopping.checkout',
                        'version' => '2026-01-11',
                        'spec' => null,
                        'schema' => null,
                        'extends' => null,
                        'config' => null
                    )
                )
            ),
            'id' => $session_id,
            'line_items' => $this->format_line_items($cart),
            'buyer' => isset($data['buyer']) ? $data['buyer'] : null,
            'status' => 'ready_for_complete',
            'currency' => isset($data['currency']) ? $data['currency'] : get_woocommerce_currency(),
            'totals' => $totals,
            'messages' => null,
            'links' => array(),
            'expires_at' => date('c', strtotime('+1 day')),
            'continue_url' => wc_get_checkout_url(),
            'payment' => array(
                'handlers' => array(),
                'selected_instrument_id' => null,
                'instruments' => array()
            ),
            'order_id' => null,
            'order_permalink_url' => null
        );

        // Store session
        $this->store_session($session_id, $session, $idempotency_key, $cart);

        return $session;
    }

    /**
     * Get checkout session
     */
    public function get_session($session_id) {
        $session_data = get_transient('ucp_session_' . $session_id);

        if (false === $session_data) {
            return null;
        }

        return $session_data['session'];
    }

    /**
     * Update checkout session
     */
    public function update_session($session_id, $updates) {
        $session_data = get_transient('ucp_session_' . $session_id);

        if (false === $session_data) {
            return null;
        }

        $session = $session_data['session'];
        $cart = $session_data['cart'];

        // Update buyer information
        if (isset($updates['buyer'])) {
            $session['buyer'] = array_merge(
                $session['buyer'] ?? array(),
                $updates['buyer']
            );
        }

        // Update line items
        if (isset($updates['line_items'])) {
            $cart = $this->create_cart_from_line_items($updates['line_items']);
            $session['line_items'] = $this->format_line_items($cart);
            $session['totals'] = $this->calculate_totals($cart);
        }

        // Update fulfillment address
        if (isset($updates['fulfillment_address'])) {
            $session['fulfillment_address'] = $updates['fulfillment_address'];
        }

        // Store updated session
        $session_data['session'] = $session;
        $session_data['cart'] = $cart;
        set_transient('ucp_session_' . $session_id, $session_data, DAY_IN_SECONDS);

        return $session;
    }

    /**
     * Complete checkout session
     */
    public function complete_session($session_id) {
        $session_data = get_transient('ucp_session_' . $session_id);

        if (false === $session_data) {
            return new WP_Error('not_found', 'Session not found');
        }

        $session = $session_data['session'];
        $cart = $session_data['cart'];

        if ($session['status'] === 'completed') {
            return $session;
        }

        try {
            // Create WooCommerce order
            $order = $this->create_order_from_session($session, $cart);

            if (is_wp_error($order)) {
                return $order;
            }

            // Update session
            $session['status'] = 'completed';
            $session['order_id'] = (string) $order->get_id();
            $session['order_permalink_url'] = $order->get_view_order_url();

            // Store updated session
            $session_data['session'] = $session;
            set_transient('ucp_session_' . $session_id, $session_data, DAY_IN_SECONDS);

            return $session;

        } catch (Exception $e) {
            return new WP_Error('order_creation_failed', $e->getMessage());
        }
    }

    /**
     * Create WooCommerce cart from line items
     */
    private function create_cart_from_line_items($line_items) {
        $cart = array();

        foreach ($line_items as $item) {
            $product_id = $item['item']['id'];
            $product = wc_get_product($product_id);

            if (!$product) {
                continue;
            }

            $cart[] = array(
                'product' => $product,
                'quantity' => $item['quantity'],
                'item_id' => isset($item['id']) ? $item['id'] : wp_generate_uuid4()
            );
        }

        return $cart;
    }

    /**
     * Format line items for UCP
     */
    private function format_line_items($cart) {
        $items = array();

        foreach ($cart as $cart_item) {
            $product = $cart_item['product'];
            $quantity = $cart_item['quantity'];

            $price = (int) ($product->get_price() * 100); // Convert to cents
            $subtotal = $price * $quantity;

            $items[] = array(
                'id' => $cart_item['item_id'],
                'item' => array(
                    'id' => (string) $product->get_id(),
                    'title' => $product->get_name(),
                    'price' => $price,
                    'image_url' => wp_get_attachment_url($product->get_image_id())
                ),
                'quantity' => $quantity,
                'totals' => array(
                    array('type' => 'subtotal', 'amount' => $subtotal),
                    array('type' => 'total', 'amount' => $subtotal)
                ),
                'parent_id' => null
            );
        }

        return $items;
    }

    /**
     * Calculate totals
     */
    private function calculate_totals($cart) {
        $subtotal = 0;

        foreach ($cart as $item) {
            $product = $item['product'];
            $price = (int) ($product->get_price() * 100);
            $subtotal += $price * $item['quantity'];
        }

        // Calculate tax (simplified - use WooCommerce tax calculations in production)
        $tax_rate = 0.25; // 25% Swedish VAT
        $tax = (int) ($subtotal * $tax_rate);
        $total = $subtotal + $tax;

        return array(
            array('type' => 'subtotal', 'display_text' => 'Subtotal', 'amount' => $subtotal),
            array('type' => 'tax', 'display_text' => 'Tax (VAT)', 'amount' => $tax),
            array('type' => 'total', 'display_text' => 'Total', 'amount' => $total)
        );
    }

    /**
     * Create WooCommerce order from session
     */
    private function create_order_from_session($session, $cart) {
        $order = wc_create_order();

        if (is_wp_error($order)) {
            return $order;
        }

        // Add products to order
        foreach ($cart as $item) {
            $order->add_product($item['product'], $item['quantity']);
        }

        // Set buyer information
        if ($session['buyer']) {
            $buyer = $session['buyer'];

            if (isset($buyer['email'])) {
                $order->set_billing_email($buyer['email']);
            }

            if (isset($buyer['full_name'])) {
                $name_parts = explode(' ', $buyer['full_name'], 2);
                $order->set_billing_first_name($name_parts[0]);
                if (isset($name_parts[1])) {
                    $order->set_billing_last_name($name_parts[1]);
                }
            }
        }

        // Set fulfillment address if provided
        if (isset($session['fulfillment_address'])) {
            $address = $session['fulfillment_address'];
            $order->set_shipping_address_1($address['street_address'] ?? '');
            $order->set_shipping_city($address['address_locality'] ?? '');
            $order->set_shipping_state($address['address_region'] ?? '');
            $order->set_shipping_postcode($address['postal_code'] ?? '');
            $order->set_shipping_country($address['address_country'] ?? '');
        }

        // Calculate totals
        $order->calculate_totals();

        // Set order status
        $order->set_status('pending');

        // Save order
        $order->save();

        // Store UCP session ID in order meta
        $order->update_meta_data('_ucp_session_id', $session['id']);
        $order->save_meta_data();

        return $order;
    }

    /**
     * Store session
     */
    private function store_session($session_id, $session, $idempotency_key, $cart) {
        $session_data = array(
            'session' => $session,
            'cart' => $cart,
            'idempotency_key' => $idempotency_key,
            'created_at' => time()
        );

        set_transient('ucp_session_' . $session_id, $session_data, DAY_IN_SECONDS);
    }

    /**
     * Generate session ID
     */
    private function generate_session_id() {
        return 'ucp_' . wp_generate_uuid4();
    }
}
