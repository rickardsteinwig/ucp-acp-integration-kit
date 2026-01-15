<?php
/**
 * Admin Settings
 *
 * Handles plugin admin settings page
 *
 * @package WooCommerce_UCP
 */

if (!defined('ABSPATH')) {
    exit;
}

class WC_UCP_Admin {
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'woocommerce',
            __('UCP Settings', 'woocommerce-ucp'),
            __('UCP Integration', 'woocommerce-ucp'),
            'manage_woocommerce',
            'wc-ucp-settings',
            array($this, 'settings_page')
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('wc_ucp_settings', 'wc_ucp_enabled');
        register_setting('wc_ucp_settings', 'wc_ucp_webhook_url');
        register_setting('wc_ucp_settings', 'wc_ucp_api_key');
    }

    /**
     * Settings page
     */
    public function settings_page() {
        $ucp_url = get_site_url() . '/.well-known/ucp';
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('WooCommerce UCP Integration', 'woocommerce-ucp'); ?></h1>

            <div class="notice notice-info">
                <p>
                    <strong><?php esc_html_e('UCP Discovery URL:', 'woocommerce-ucp'); ?></strong>
                    <code><?php echo esc_url($ucp_url); ?></code>
                    <a href="<?php echo esc_url($ucp_url); ?>" target="_blank" class="button button-small">
                        <?php esc_html_e('Test', 'woocommerce-ucp'); ?>
                    </a>
                </p>
            </div>

            <form method="post" action="options.php">
                <?php settings_fields('wc_ucp_settings'); ?>

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="wc_ucp_enabled">
                                <?php esc_html_e('Enable UCP', 'woocommerce-ucp'); ?>
                            </label>
                        </th>
                        <td>
                            <input type="checkbox" id="wc_ucp_enabled" name="wc_ucp_enabled" value="yes"
                                <?php checked(get_option('wc_ucp_enabled'), 'yes'); ?>>
                            <p class="description">
                                <?php esc_html_e('Enable Universal Commerce Protocol integration', 'woocommerce-ucp'); ?>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="wc_ucp_webhook_url">
                                <?php esc_html_e('Webhook URL', 'woocommerce-ucp'); ?>
                            </label>
                        </th>
                        <td>
                            <input type="url" id="wc_ucp_webhook_url" name="wc_ucp_webhook_url"
                                value="<?php echo esc_attr(get_option('wc_ucp_webhook_url')); ?>"
                                class="regular-text">
                            <p class="description">
                                <?php esc_html_e('URL to receive order status updates', 'woocommerce-ucp'); ?>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="wc_ucp_api_key">
                                <?php esc_html_e('API Key', 'woocommerce-ucp'); ?>
                            </label>
                        </th>
                        <td>
                            <input type="text" id="wc_ucp_api_key" name="wc_ucp_api_key"
                                value="<?php echo esc_attr(get_option('wc_ucp_api_key')); ?>"
                                class="regular-text">
                            <p class="description">
                                <?php esc_html_e('API key for authenticating UCP requests (optional)', 'woocommerce-ucp'); ?>
                            </p>
                        </td>
                    </tr>
                </table>

                <?php submit_button(); ?>
            </form>

            <hr>

            <h2><?php esc_html_e('Testing', 'woocommerce-ucp'); ?></h2>

            <p><?php esc_html_e('Test your UCP integration with these curl commands:', 'woocommerce-ucp'); ?></p>

            <h3><?php esc_html_e('1. Check UCP Profile', 'woocommerce-ucp'); ?></h3>
            <pre><code>curl <?php echo esc_url($ucp_url); ?> | jq</code></pre>

            <h3><?php esc_html_e('2. List Products', 'woocommerce-ucp'); ?></h3>
            <pre><code>curl <?php echo esc_url(get_rest_url(null, 'ucp/v1/products')); ?> | jq</code></pre>

            <h3><?php esc_html_e('3. Create Checkout Session', 'woocommerce-ucp'); ?></h3>
            <pre><code>curl -X POST <?php echo esc_url(get_rest_url(null, 'ucp/v1/checkout-sessions')); ?> \
  -H "Content-Type: application/json" \
  -H "idempotency-key: $(uuidgen)" \
  -H "request-id: $(uuidgen)" \
  -d '{
    "line_items": [
      {
        "item": {"id": "PRODUCT_ID"},
        "quantity": 1
      }
    ],
    "currency": "SEK"
  }' | jq</code></pre>

            <h2><?php esc_html_e('Documentation', 'woocommerce-ucp'); ?></h2>
            <p>
                <a href="https://ucp.dev" target="_blank">
                    <?php esc_html_e('UCP Specification', 'woocommerce-ucp'); ?>
                </a>
                |
                <a href="https://github.com/Universal-Commerce-Protocol/ucp" target="_blank">
                    <?php esc_html_e('GitHub Repository', 'woocommerce-ucp'); ?>
                </a>
            </p>
        </div>
        <?php
    }
}
