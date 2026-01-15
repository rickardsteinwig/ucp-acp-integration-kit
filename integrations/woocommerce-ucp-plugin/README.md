# WooCommerce UCP Plugin

Universal Commerce Protocol integration for WooCommerce stores. Enables your WordPress/WooCommerce shop to work with AI agents and agentic commerce platforms.

## Features

- ✅ Full UCP 2026-01-11 specification
- ✅ Seamless WooCommerce integration
- ✅ Product catalog exposure
- ✅ Checkout session management
- ✅ Order creation and tracking
- ✅ Payment gateway integration
- ✅ Order webhooks
- ✅ Admin settings page
- 🌍 **Fungerar i Sverige** (Works in Sweden) with SEK support

## Requirements

- WordPress 6.0+
- WooCommerce 7.0+
- PHP 7.4+
- Pretty permalinks enabled

## Installation

### Method 1: Manual Installation

1. Download the plugin ZIP file
2. Go to WordPress Admin → Plugins → Add New → Upload Plugin
3. Upload the ZIP file
4. Click "Install Now" and then "Activate"

### Method 2: FTP Upload

1. Extract the ZIP file
2. Upload `woocommerce-ucp-plugin` folder to `/wp-content/plugins/`
3. Activate the plugin through WordPress Admin → Plugins

### Method 3: Development Installation

```bash
cd /path/to/wordpress/wp-content/plugins/
git clone <your-repo> woocommerce-ucp
cd woocommerce-ucp
```

Then activate in WordPress Admin.

## Configuration

### 1. Enable Pretty Permalinks

Go to **Settings → Permalinks** and select any option except "Plain".

### 2. Configure Plugin

Go to **WooCommerce → UCP Integration**

Settings:
- **Enable UCP**: Turn integration on/off
- **Webhook URL**: URL for order status updates
- **API Key**: Optional API key for authentication

### 3. Test Discovery Endpoint

Visit: `https://your-site.com/.well-known/ucp`

You should see a JSON response with your store's UCP profile.

## Usage

### API Endpoints

#### Discovery
```
GET /.well-known/ucp
```
Returns UCP profile with capabilities and payment handlers.

#### Checkout Sessions
```
POST /wp-json/ucp/v1/checkout-sessions
GET /wp-json/ucp/v1/checkout-sessions/{id}
PUT /wp-json/ucp/v1/checkout-sessions/{id}
POST /wp-json/ucp/v1/checkout-sessions/{id}/complete
```

#### Products
```
GET /wp-json/ucp/v1/products
```

#### Orders
```
GET /wp-json/ucp/v1/orders/{id}
```

### Example: Create Checkout Session

```bash
curl -X POST https://yoursite.com/wp-json/ucp/v1/checkout-sessions \
  -H "Content-Type: application/json" \
  -H "idempotency-key: $(uuidgen)" \
  -H "request-id: $(uuidgen)" \
  -d '{
    "line_items": [
      {
        "item": {
          "id": "123"
        },
        "quantity": 2
      }
    ],
    "buyer": {
      "email": "customer@example.com",
      "full_name": "John Doe"
    },
    "currency": "SEK"
  }'
```

## Sverige-specifikt (Sweden-specific)

### Svenska kronor (SEK)
Pluginet stödjer SEK som standard när din WooCommerce-butik är konfigurerad för Sverige:

```json
{
  "currency": "SEK",
  "totals": [
    {
      "type": "tax",
      "display_text": "Moms (25%)",
      "amount": 2500
    }
  ]
}
```

### Svensk moms (Swedish VAT)
- Pluginet beräknar automatiskt svensk moms (25%)
- Integration med WooCommerce Tax Settings
- Stöd för olika momssatser per produktkategori

### Svenska betalmetoder
Stödjer vanliga svenska betalmetoder via WooCommerce:
- Klarna
- Swish
- Faktura
- Kort (Visa, Mastercard)

### Språkstöd
Plugin är redo för översättning till svenska. För att översätta:
1. Använd Loco Translate plugin
2. Eller skapa `.po` filer i `/languages/` mappen

## Development

### File Structure

```
woocommerce-ucp-plugin/
├── woocommerce-ucp.php              # Main plugin file
├── includes/
│   ├── class-wc-ucp-api.php         # REST API routes
│   ├── class-wc-ucp-checkout.php    # Checkout management
│   ├── class-wc-ucp-products.php    # Product handling
│   ├── class-wc-ucp-orders.php      # Order management
│   └── class-wc-ucp-admin.php       # Admin settings
├── languages/                        # Translation files
└── README.md                        # This file
```

### Hooks and Filters

#### Actions
```php
// Before creating checkout session
do_action('wc_ucp_before_create_session', $data);

// After creating checkout session
do_action('wc_ucp_after_create_session', $session);

// Before completing order
do_action('wc_ucp_before_complete_order', $session_id);

// After completing order
do_action('wc_ucp_after_complete_order', $order_id);
```

#### Filters
```php
// Modify UCP profile
add_filter('wc_ucp_profile', function($profile) {
    // Customize profile
    return $profile;
});

// Modify product data
add_filter('wc_ucp_product_data', function($product_data, $product) {
    // Customize product format
    return $product_data;
}, 10, 2);

// Modify payment handlers
add_filter('wc_ucp_payment_handlers', function($handlers) {
    // Add custom payment handlers
    return $handlers;
});
```

### Custom Payment Handlers

Add custom payment handlers:

```php
add_filter('wc_ucp_payment_handlers', function($handlers) {
    $handlers[] = array(
        'id' => 'swish',
        'name' => 'se.swish.payment',
        'version' => '2026-01-11',
        'spec' => 'https://swish.se/ucp/spec',
        'config_schema' => 'https://swish.se/ucp/config.json',
        'instrument_schemas' => array(
            'https://swish.se/ucp/instrument.json'
        ),
        'config' => array(
            'merchant_id' => get_option('swish_merchant_id')
        )
    );

    return $handlers;
});
```

## Security

### Production Checklist

- [ ] Enable HTTPS on your site
- [ ] Configure API key authentication
- [ ] Implement rate limiting
- [ ] Enable request signature verification
- [ ] Monitor webhook endpoints
- [ ] Keep WordPress and WooCommerce updated
- [ ] Use strong passwords
- [ ] Enable two-factor authentication

### Request Validation

The plugin validates:
- Required UCP headers (idempotency-key, request-id)
- Product availability and stock
- Valid product IDs
- Buyer information format
- Currency codes

## Troubleshooting

### Common Issues

**"Endpoint not found"**
- Check that pretty permalinks are enabled
- Go to Settings → Permalinks and click "Save Changes"
- Test: `curl https://yoursite.com/wp-json/`

**"WooCommerce is required"**
- Install and activate WooCommerce plugin
- Version 7.0 or higher required

**"Product not found"**
- Ensure product is published
- Check product ID is correct
- Verify product is in stock

**"Checkout creation failed"**
- Check WooCommerce settings
- Verify payment gateways are configured
- Check PHP error logs

### Debug Mode

Enable WordPress debug mode in `wp-config.php`:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

View logs: `/wp-content/debug.log`

### Test Endpoints

```bash
# Test discovery
curl https://yoursite.com/.well-known/ucp

# Test products endpoint
curl https://yoursite.com/wp-json/ucp/v1/products

# Test with verbose output
curl -v https://yoursite.com/.well-known/ucp
```

## Performance

### Optimization Tips

1. **Caching**: Use object caching (Redis/Memcached)
2. **CDN**: Serve static assets via CDN
3. **Database**: Optimize WooCommerce database tables
4. **Sessions**: Use transients for temporary session storage
5. **Rate Limiting**: Implement rate limiting for API endpoints

### Recommended Plugins

- **WP Rocket** - Caching
- **Query Monitor** - Performance debugging
- **Redis Object Cache** - Object caching
- **Cloudflare** - CDN and security

## Support

- **UCP Specification**: https://ucp.dev
- **WooCommerce Docs**: https://woocommerce.com/documentation/
- **WordPress Codex**: https://codex.wordpress.org/
- **GitHub Issues**: Report issues in your repository

## Roadmap

- [ ] OAuth 2.0 authentication
- [ ] Advanced webhook management
- [ ] Multi-currency support
- [ ] Product variants support
- [ ] Subscription products
- [ ] Advanced shipping options
- [ ] Klarna integration
- [ ] Swish integration
- [ ] Translation to Swedish

## Contributing

Contributions welcome! Please:
1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Submit a pull request

## License

Apache License 2.0

## Credits

Built with the Universal Commerce Protocol specification.
