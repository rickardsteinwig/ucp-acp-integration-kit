<?php
/**
 * Plugin Name: WooCommerce UCP Integration
 * Plugin URI: https://github.com/your-repo/woocommerce-ucp
 * Description: Universal Commerce Protocol integration for WooCommerce stores. Enables agentic commerce with AI platforms.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://yourwebsite.com
 * License: Apache-2.0
 * License URI: https://www.apache.org/licenses/LICENSE-2.0
 * Text Domain: woocommerce-ucp
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * WC requires at least: 7.0
 * WC tested up to: 8.5
 *
 * @package WooCommerce_UCP
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define plugin constants
define('WC_UCP_VERSION', '1.0.0');
define('WC_UCP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WC_UCP_PLUGIN_URL', plugin_dir_url(__FILE__));

// Check if WooCommerce is active
if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    add_action('admin_notices', 'wc_ucp_woocommerce_missing_notice');
    return;
}

function wc_ucp_woocommerce_missing_notice() {
    ?>
    <div class="error">
        <p><?php esc_html_e('WooCommerce UCP requires WooCommerce to be installed and active.', 'woocommerce-ucp'); ?></p>
    </div>
    <?php
}

/**
 * Main WooCommerce UCP Class
 */
class WC_UCP {
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
     * Constructor
     */
    private function __construct() {
        $this->includes();
        $this->init_hooks();
    }

    /**
     * Include required files
     */
    private function includes() {
        require_once WC_UCP_PLUGIN_DIR . 'includes/class-wc-ucp-api.php';
        require_once WC_UCP_PLUGIN_DIR . 'includes/class-wc-ucp-checkout.php';
        require_once WC_UCP_PLUGIN_DIR . 'includes/class-wc-ucp-products.php';
        require_once WC_UCP_PLUGIN_DIR . 'includes/class-wc-ucp-orders.php';
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('init', array($this, 'init'));
        add_action('rest_api_init', array($this, 'register_rest_routes'));

        // Add settings page
        if (is_admin()) {
            require_once WC_UCP_PLUGIN_DIR . 'includes/class-wc-ucp-admin.php';
            new WC_UCP_Admin();
        }
    }

    /**
     * Initialize plugin
     */
    public function init() {
        // Load text domain
        load_plugin_textdomain('woocommerce-ucp', false, dirname(plugin_basename(__FILE__)) . '/languages');

        // Initialize classes
        WC_UCP_API::get_instance();
        WC_UCP_Checkout::get_instance();
        WC_UCP_Products::get_instance();
        WC_UCP_Orders::get_instance();
    }

    /**
     * Register REST API routes
     */
    public function register_rest_routes() {
        WC_UCP_API::get_instance()->register_routes();
    }
}

// Initialize plugin
function wc_ucp() {
    return WC_UCP::get_instance();
}

// Start plugin
add_action('plugins_loaded', 'wc_ucp');

/**
 * Activation hook
 */
register_activation_hook(__FILE__, 'wc_ucp_activate');

function wc_ucp_activate() {
    // Create database tables if needed
    // Flush rewrite rules
    flush_rewrite_rules();

    // Set default options
    if (!get_option('wc_ucp_enabled')) {
        update_option('wc_ucp_enabled', 'yes');
    }
}

/**
 * Deactivation hook
 */
register_deactivation_hook(__FILE__, 'wc_ucp_deactivate');

function wc_ucp_deactivate() {
    // Flush rewrite rules
    flush_rewrite_rules();
}
