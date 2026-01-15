<?php
/**
 * UCP REST API Handler
 *
 * Handles all UCP REST API endpoints
 *
 * @package WooCommerce_UCP
 */

if (!defined('ABSPATH')) {
    exit;
}

class WC_UCP_API {
    /**
     * Singleton instance
     */
    private static $instance = null;

    /**
     * Namespace for REST API
     */
    private $namespace = 'ucp/v1';

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
     * Register REST API routes
     */
    public function register_routes() {
        // UCP Discovery endpoint
        register_rest_route('', '/.well-known/ucp', array(
            'methods'  => 'GET',
            'callback' => array($this, 'get_ucp_profile'),
            'permission_callback' => '__return_true'
        ));

        // Checkout sessions
        register_rest_route($this->namespace, '/checkout-sessions', array(
            'methods'  => 'POST',
            'callback' => array($this, 'create_checkout_session'),
            'permission_callback' => array($this, 'check_ucp_permissions'),
            'args'     => $this->get_create_session_args()
        ));

        register_rest_route($this->namespace, '/checkout-sessions/(?P<id>[a-zA-Z0-9-_]+)', array(
            array(
                'methods'  => 'GET',
                'callback' => array($this, 'get_checkout_session'),
                'permission_callback' => array($this, 'check_ucp_permissions')
            ),
            array(
                'methods'  => 'PUT',
                'callback' => array($this, 'update_checkout_session'),
                'permission_callback' => array($this, 'check_ucp_permissions')
            )
        ));

        register_rest_route($this->namespace, '/checkout-sessions/(?P<id>[a-zA-Z0-9-_]+)/complete', array(
            'methods'  => 'POST',
            'callback' => array($this, 'complete_checkout_session'),
            'permission_callback' => array($this, 'check_ucp_permissions')
        ));

        // Products endpoint
        register_rest_route($this->namespace, '/products', array(
            'methods'  => 'GET',
            'callback' => array($this, 'get_products'),
            'permission_callback' => '__return_true'
        ));

        // Orders endpoint
        register_rest_route($this->namespace, '/orders/(?P<id>[a-zA-Z0-9-_]+)', array(
            'methods'  => 'GET',
            'callback' => array($this, 'get_order'),
            'permission_callback' => array($this, 'check_ucp_permissions')
        ));
    }

    /**
     * Get UCP Profile (Discovery)
     */
    public function get_ucp_profile($request) {
        $site_url = get_site_url();

        $profile = array(
            'ucp' => array(
                'version' => '2026-01-11',
                'services' => array(
                    'dev.ucp.shopping' => array(
                        'version' => '2026-01-11',
                        'spec' => 'https://ucp.dev/specs/shopping',
                        'rest' => array(
                            'schema' => 'https://ucp.dev/services/shopping/openapi.json',
                            'endpoint' => $site_url . '/wp-json/ucp/v1/'
                        ),
                        'mcp' => null,
                        'a2a' => null,
                        'embedded' => null
                    )
                ),
                'capabilities' => array(
                    array(
                        'name' => 'dev.ucp.shopping.checkout',
                        'version' => '2026-01-11',
                        'spec' => 'https://ucp.dev/specs/shopping/checkout',
                        'schema' => 'https://ucp.dev/schemas/shopping/checkout.json',
                        'extends' => null,
                        'config' => null
                    ),
                    array(
                        'name' => 'dev.ucp.shopping.fulfillment',
                        'version' => '2026-01-11',
                        'spec' => 'https://ucp.dev/specs/shopping/fulfillment',
                        'schema' => 'https://ucp.dev/schemas/shopping/fulfillment.json',
                        'extends' => 'dev.ucp.shopping.checkout',
                        'config' => null
                    )
                )
            ),
            'payment' => array(
                'handlers' => $this->get_payment_handlers()
            ),
            'signing_keys' => null
        );

        return rest_ensure_response($profile);
    }

    /**
     * Get available payment handlers
     */
    private function get_payment_handlers() {
        $handlers = array();

        // Get active WooCommerce payment gateways
        $gateways = WC()->payment_gateways->get_available_payment_gateways();

        foreach ($gateways as $gateway_id => $gateway) {
            $handlers[] = array(
                'id' => $gateway_id,
                'name' => 'com.woocommerce.' . $gateway_id,
                'version' => '2026-01-11',
                'spec' => 'https://woocommerce.com/ucp/handlers/' . $gateway_id,
                'config_schema' => 'https://woocommerce.com/ucp/handlers/' . $gateway_id . '/config.json',
                'instrument_schemas' => array(
                    'https://ucp.dev/schemas/shopping/types/card_payment_instrument.json'
                ),
                'config' => array(
                    'title' => $gateway->get_title(),
                    'description' => $gateway->get_description()
                )
            );
        }

        return $handlers;
    }

    /**
     * Create checkout session
     */
    public function create_checkout_session($request) {
        try {
            // Validate required headers
            $this->validate_headers($request);

            $idempotency_key = $request->get_header('idempotency-key');

            // Check for existing session with this idempotency key
            $existing_session = $this->get_session_by_idempotency_key($idempotency_key);
            if ($existing_session) {
                return rest_ensure_response($existing_session);
            }

            // Create session
            $checkout = WC_UCP_Checkout::get_instance();
            $session = $checkout->create_session($request->get_json_params(), $idempotency_key);

            return new WP_REST_Response($session, 201);

        } catch (Exception $e) {
            return new WP_Error(
                'ucp_error',
                $e->getMessage(),
                array('status' => 500)
            );
        }
    }

    /**
     * Get checkout session
     */
    public function get_checkout_session($request) {
        $session_id = $request['id'];
        $checkout = WC_UCP_Checkout::get_instance();
        $session = $checkout->get_session($session_id);

        if (!$session) {
            return new WP_Error(
                'not_found',
                'Checkout session not found',
                array('status' => 404)
            );
        }

        return rest_ensure_response($session);
    }

    /**
     * Update checkout session
     */
    public function update_checkout_session($request) {
        $session_id = $request['id'];
        $updates = $request->get_json_params();

        $checkout = WC_UCP_Checkout::get_instance();
        $session = $checkout->update_session($session_id, $updates);

        if (!$session) {
            return new WP_Error(
                'not_found',
                'Checkout session not found',
                array('status' => 404)
            );
        }

        return rest_ensure_response($session);
    }

    /**
     * Complete checkout session
     */
    public function complete_checkout_session($request) {
        $session_id = $request['id'];

        $checkout = WC_UCP_Checkout::get_instance();
        $result = $checkout->complete_session($session_id);

        if (is_wp_error($result)) {
            return $result;
        }

        return rest_ensure_response($result);
    }

    /**
     * Get products
     */
    public function get_products($request) {
        $products = WC_UCP_Products::get_instance();
        $items = $products->get_products($request->get_params());

        return rest_ensure_response(array('products' => $items));
    }

    /**
     * Get order
     */
    public function get_order($request) {
        $order_id = $request['id'];
        $orders = WC_UCP_Orders::get_instance();
        $order = $orders->get_order($order_id);

        if (!$order) {
            return new WP_Error(
                'not_found',
                'Order not found',
                array('status' => 404)
            );
        }

        return rest_ensure_response($order);
    }

    /**
     * Check UCP permissions
     */
    public function check_ucp_permissions($request) {
        // In production, implement proper authentication
        // For now, allow all requests
        return true;
    }

    /**
     * Validate required headers
     */
    private function validate_headers($request) {
        $required = array('idempotency-key', 'request-id');

        foreach ($required as $header) {
            if (!$request->get_header($header)) {
                throw new Exception(sprintf('Missing required header: %s', $header));
            }
        }
    }

    /**
     * Get session by idempotency key
     */
    private function get_session_by_idempotency_key($key) {
        // Query WooCommerce sessions by meta key
        // Implementation depends on your session storage strategy
        return null;
    }

    /**
     * Get create session arguments
     */
    private function get_create_session_args() {
        return array(
            'line_items' => array(
                'required' => true,
                'type' => 'array'
            ),
            'buyer' => array(
                'required' => false,
                'type' => 'object'
            ),
            'currency' => array(
                'required' => false,
                'type' => 'string',
                'default' => get_woocommerce_currency()
            )
        );
    }
}
