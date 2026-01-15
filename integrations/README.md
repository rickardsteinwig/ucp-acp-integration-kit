# UCP Integrations

Three production-ready implementations for connecting e-commerce platforms to the Universal Commerce Protocol.

## 📦 What's Included

This directory contains three complete UCP integrations:

### 1. Custom UCP Server (Python/FastAPI)
**Path**: `custom-ucp-server/`

A flexible, standalone UCP server that can be adapted to any e-commerce backend.

**Features**:
- ✅ Full UCP 2026-01-11 support
- ✅ FastAPI REST API
- ✅ In-memory storage (easily replaceable)
- ✅ Payment handler integration
- ✅ Idempotency support
- 🌍 Works globally

**Best for**:
- Custom e-commerce platforms
- Headless commerce setups
- Microservices architecture
- Learning and prototyping

### 2. Shopify UCP Adapter (Node.js/TypeScript)
**Path**: `shopify-ucp-adapter/`

Connects Shopify stores to UCP via Storefront and Admin APIs.

**Features**:
- ✅ Shopify Storefront API integration
- ✅ Real-time product sync
- ✅ Shop Pay support
- ✅ TypeScript type safety
- 🌍 Global (works in Sweden)

**Best for**:
- Shopify merchants (complement to native UCP)
- Custom Shopify apps
- Multi-store management
- Advanced integrations

### 3. WooCommerce UCP Plugin (WordPress/PHP)
**Path**: `woocommerce-ucp-plugin/`

WordPress plugin that adds UCP support to WooCommerce stores.

**Features**:
- ✅ Native WordPress integration
- ✅ WooCommerce REST API
- ✅ Admin settings page
- ✅ Webhook support
- ✅ Swedish VAT support
- 🇸🇪 Swedish localization ready

**Best for**:
- WooCommerce merchants
- WordPress developers
- Small to medium businesses
- Swedish e-commerce (SEK support)

---

## 🚀 Quick Start

### Custom Server

```bash
cd custom-ucp-server
pip install -r requirements.txt
python server.py
```

Server: `http://localhost:8000`
Discovery: `http://localhost:8000/.well-known/ucp`

### Shopify Adapter

```bash
cd shopify-ucp-adapter
npm install
cp .env.example .env
# Edit .env with Shopify credentials
npm run dev
```

Server: `http://localhost:3000`
Discovery: `http://localhost:3000/.well-known/ucp`

### WooCommerce Plugin

```bash
# Copy to WordPress plugins directory
cp -r woocommerce-ucp-plugin /path/to/wordpress/wp-content/plugins/

# Activate in WordPress Admin → Plugins
```

Discovery: `https://your-site.com/.well-known/ucp`

---

## 📊 Comparison Matrix

| Feature | Custom Server | Shopify Adapter | WooCommerce Plugin |
|---------|--------------|-----------------|-------------------|
| **Language** | Python | Node.js/TypeScript | PHP |
| **Framework** | FastAPI | Express + Hono | WordPress |
| **Database** | In-memory/Custom | Shopify API | WordPress DB |
| **Setup Time** | 5 min | 10 min | 5 min |
| **Customization** | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐ | ⭐⭐⭐⭐ |
| **Production Ready** | ✅ (with DB) | ✅ | ✅ |
| **Complexity** | Medium | Medium | Low |
| **Best For** | Custom platforms | Shopify stores | WooCommerce stores |

---

## 🌍 Geographic Availability

### Protocol Support
All three implementations work globally. The UCP protocol itself has no geographic restrictions.

### Platform Limitations

**Google AI Mode** (UCP):
- Initially: USA
- Expansion: Gradual rollout
- Your implementation: Ready when available

**ChatGPT Shopping** (ACP):
- Initially: USA
- Expansion: Planned
- Your implementation: Ready when available

**Shopify/WooCommerce**:
- ✅ Global availability
- ✅ Works in Sweden (SEK, Swedish VAT)
- ✅ Works in EU (GDPR compliant)

---

## 🔧 Architecture Overview

```
┌─────────────────────────────────────────────────────────────┐
│                     AI Agents/Platforms                      │
│          (Google AI Mode, ChatGPT, Custom Agents)            │
└─────────────────────┬───────────────────────────────────────┘
                      │
                      │ UCP Protocol
                      │
        ┌─────────────┼─────────────┐
        │             │             │
        ▼             ▼             ▼
┌──────────────┐ ┌──────────────┐ ┌──────────────┐
│   Custom     │ │   Shopify    │ │ WooCommerce  │
│    Server    │ │   Adapter    │ │    Plugin    │
└──────┬───────┘ └──────┬───────┘ └──────┬───────┘
       │                │                │
       ▼                ▼                ▼
┌──────────────┐ ┌──────────────┐ ┌──────────────┐
│   Your DB    │ │  Shopify API │ │ WordPress DB │
│  (Any)       │ │              │ │              │
└──────────────┘ └──────────────┘ └──────────────┘
```

---

## 📝 Common Tasks

### Test Discovery Endpoint

All integrations expose the same discovery endpoint:

```bash
# Custom Server
curl http://localhost:8000/.well-known/ucp | jq

# Shopify Adapter
curl http://localhost:3000/.well-known/ucp | jq

# WooCommerce Plugin
curl https://yoursite.com/.well-known/ucp | jq
```

### Create Checkout Session

Same API across all platforms:

```bash
curl -X POST {BASE_URL}/checkout-sessions \
  -H "Content-Type: application/json" \
  -H "idempotency-key: $(uuidgen)" \
  -H "request-id: $(uuidgen)" \
  -d '{
    "line_items": [
      {
        "item": {"id": "product_id"},
        "quantity": 1
      }
    ],
    "buyer": {
      "email": "customer@example.com"
    },
    "currency": "SEK"
  }'
```

### List Products

```bash
curl {BASE_URL}/products | jq
```

---

## 🔐 Security Best Practices

### All Implementations

1. **HTTPS Only**: Always use HTTPS in production
2. **API Keys**: Implement proper authentication
3. **Rate Limiting**: Protect against abuse
4. **Input Validation**: Validate all inputs
5. **Logging**: Monitor all UCP requests
6. **CORS**: Configure appropriate CORS policies

### Custom Server
- Use environment variables for secrets
- Implement database connection pooling
- Enable request signature verification
- Use Gunicorn/Uvicorn workers for production

### Shopify Adapter
- Secure Shopify API tokens
- Validate webhook signatures
- Use HTTPS for callbacks
- Implement OAuth for multi-store

### WooCommerce Plugin
- Enable WordPress security plugins
- Use strong admin passwords
- Keep WordPress/WooCommerce updated
- Configure proper file permissions

---

## 🧪 Testing

### Unit Tests

Each implementation includes test stubs:

```bash
# Custom Server
cd custom-ucp-server
pytest tests/

# Shopify Adapter
cd shopify-ucp-adapter
npm test

# WooCommerce Plugin
cd woocommerce-ucp-plugin
# Use WordPress PHPUnit
```

### Integration Testing

Test the complete flow:

1. **Discovery**: Check UCP profile
2. **Products**: List available products
3. **Checkout**: Create session
4. **Update**: Modify session
5. **Complete**: Finalize order

### Example Test Script

```bash
#!/bin/bash
BASE_URL="http://localhost:8000"

echo "1. Testing Discovery..."
curl $BASE_URL/.well-known/ucp

echo "\n2. Testing Products..."
curl $BASE_URL/products

echo "\n3. Testing Checkout..."
SESSION=$(curl -X POST $BASE_URL/checkout-sessions \
  -H "Content-Type: application/json" \
  -H "idempotency-key: $(uuidgen)" \
  -H "request-id: $(uuidgen)" \
  -d '{"line_items":[{"item":{"id":"prod_001"},"quantity":1}],"currency":"SEK"}' \
  | jq -r '.id')

echo "\n4. Session ID: $SESSION"

echo "\n5. Getting Session..."
curl $BASE_URL/checkout-sessions/$SESSION

echo "\n6. Completing Session..."
curl -X POST $BASE_URL/checkout-sessions/$SESSION/complete \
  -H "idempotency-key: $(uuidgen)" \
  -H "request-id: $(uuidgen)"
```

---

## 🚢 Deployment

### Custom Server

**Docker**:
```dockerfile
FROM python:3.11-slim
WORKDIR /app
COPY requirements.txt .
RUN pip install -r requirements.txt
COPY . .
CMD ["uvicorn", "server:app", "--host", "0.0.0.0", "--port", "8000"]
```

**Cloud Platforms**:
- Railway
- Heroku
- Google Cloud Run
- AWS ECS
- DigitalOcean App Platform

### Shopify Adapter

**Docker**:
```dockerfile
FROM node:18-alpine
WORKDIR /app
COPY package*.json ./
RUN npm ci --production
COPY . .
RUN npm run build
CMD ["node", "dist/server.js"]
```

**Cloud Platforms**:
- Railway
- Heroku
- Vercel (serverless)
- Netlify Functions
- AWS Lambda

### WooCommerce Plugin

**Installation**:
1. ZIP the plugin directory
2. Upload via WordPress Admin
3. Or deploy via FTP/SSH

**Managed WordPress**:
- WP Engine
- Kinsta
- Cloudways
- SiteGround

---

## 📈 Monitoring

### Key Metrics

Track these metrics:

1. **Request Volume**: Total UCP API calls
2. **Response Time**: P50, P95, P99 latencies
3. **Error Rate**: 4xx, 5xx errors
4. **Checkout Completion**: Success rate
5. **Order Creation**: Orders from UCP

### Logging

Log format example:

```json
{
  "timestamp": "2026-01-15T10:30:00Z",
  "method": "POST",
  "endpoint": "/checkout-sessions",
  "status": 201,
  "duration_ms": 145,
  "idempotency_key": "abc-123",
  "session_id": "sess_xyz"
}
```

### Monitoring Tools

- **New Relic**: APM monitoring
- **Datadog**: Infrastructure & logs
- **Sentry**: Error tracking
- **Grafana**: Custom dashboards
- **CloudWatch**: AWS monitoring

---

## 🛠️ Customization Guide

### Adding Custom Payment Handlers

**Custom Server**:
```python
PAYMENT_HANDLERS.append({
    'id': 'swish',
    'name': 'se.swish.payment',
    'version': '2026-01-11',
    # ...
})
```

**Shopify Adapter**:
```typescript
handlers.push({
  id: 'klarna',
  name: 'com.klarna.payment',
  version: '2026-01-11',
  // ...
});
```

**WooCommerce Plugin**:
```php
add_filter('wc_ucp_payment_handlers', function($handlers) {
    $handlers[] = array(
        'id' => 'swish',
        'name' => 'se.swish.payment',
        // ...
    );
    return $handlers;
});
```

### Adding Custom Capabilities

Extend the UCP profile to add custom capabilities:

```python
capabilities.append({
    'name': 'com.yourcompany.loyalty',
    'version': '1.0.0',
    'spec': 'https://yoursite.com/specs/loyalty',
    'extends': 'dev.ucp.shopping.checkout'
})
```

---

## 🆘 Support & Resources

### Documentation
- **UCP Spec**: https://ucp.dev
- **Project Guide**: See main README.md
- **Quick Start**: See QUICKSTART.md

### Community
- **UCP Discussions**: https://github.com/Universal-Commerce-Protocol/ucp/discussions
- **Issues**: Report in respective implementation READMEs

### Commercial Support
- Shopify: https://help.shopify.com
- WooCommerce: https://woocommerce.com/support/
- Custom: Contact your development team

---

## 📄 License

All implementations: Apache 2.0 License

---

## 🎯 Next Steps

1. **Choose** the implementation that fits your platform
2. **Install** following the Quick Start guide
3. **Test** using the testing guide
4. **Deploy** to production
5. **Monitor** performance and errors
6. **Iterate** based on usage

**Ready to start?** Pick your integration and dive into its README!
