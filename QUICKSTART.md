# Quick Start Guide: Agentic Commerce Protocols

## Overview

This project contains:
- **UCP** (Universal Commerce Protocol) - Google's agentic commerce standard
- **ACP** (Agentic Commerce Protocol) - OpenAI/Stripe's agentic commerce standard

## What We've Set Up

```
ucp-try-1/
├── ucp/                           # Google UCP Repository
│   ├── spec/                      # JSON schemas & API specs
│   │   ├── discovery/             # Capability discovery
│   │   ├── schemas/shopping/      # Checkout, order schemas
│   │   └── services/              # API service definitions
│   ├── docs/                      # Full documentation
│   └── main.py                    # MkDocs plugin (doc generation)
│
├── acp/                           # OpenAI ACP Repository
│   ├── spec/
│   │   ├── openapi/               # OpenAPI 3.1 specs
│   │   │   ├── openapi.agentic_checkout.yaml
│   │   │   ├── openapi.delegate_payment.yaml
│   │   │   └── openapi.agentic_checkout_webhook.yaml
│   │   └── json-schema/           # JSON schema definitions
│   ├── examples/                  # Example requests/responses
│   └── rfcs/                      # Design documents
│
├── AGENTIC_COMMERCE_GUIDE.md      # Complete comparison guide
└── QUICKSTART.md                  # This file
```

## Getting Started

### 1. Explore the Documentation

#### UCP Documentation
```bash
cd ucp-try-1/ucp
cat README.md

# View specifications
ls spec/schemas/shopping/types/

# Read key schemas
cat spec/discovery/profile_schema.json
```

**Online**: https://ucp.dev

#### ACP Documentation
```bash
cd ucp-try-1/acp
cat README.md

# View OpenAPI specs
cat spec/openapi/openapi.agentic_checkout.yaml

# View examples
cat examples/examples.agentic_checkout.json
```

**Online**: https://agenticcommerce.dev

### 2. Understanding the Protocols

#### Key Differences

| Feature | UCP | ACP |
|---------|-----|-----|
| **Lead** | Google + 20 partners | OpenAI + Stripe |
| **Platform** | Google AI Mode, Gemini | ChatGPT |
| **Adoption** | Shopify native (Jan 2026) | Etsy, growing |
| **Specs** | JSON Schema + docs | OpenAPI 3.1 |

Both protocols enable AI agents to:
- Discover product capabilities
- Create checkout sessions
- Manage carts
- Process payments securely
- Track orders

### 3. Build a Simple Implementation

#### Option A: UCP Implementation

**For Python:**

```python
# Example: Minimal UCP profile endpoint
from flask import Flask, jsonify

app = Flask(__name__)

@app.route('/.well-known/ucp', methods=['GET'])
def ucp_manifest():
    return jsonify({
        "ucp": {
            "version": "2026-01-11"
        },
        "capabilities": [
            {
                "type": "checkout",
                "binding": {
                    "type": "rest",
                    "base_url": "https://yourstore.com/ucp"
                }
            }
        ],
        "payment_handlers": ["stripe", "paypal"]
    })

@app.route('/ucp/checkout/sessions', methods=['POST'])
def create_checkout_session():
    # Implement checkout session creation
    # Map to your e-commerce backend
    return jsonify({
        "id": "chk_123",
        "status": "ready_for_complete",
        "line_items": [],
        "totals": []
    }), 201

if __name__ == '__main__':
    app.run(port=8182)
```

**See full sample**: Clone https://github.com/Universal-Commerce-Protocol/samples

#### Option B: ACP Implementation

**For Node.js:**

```javascript
// Example: Minimal ACP checkout endpoint
const express = require('express');
const app = express();

app.use(express.json());

app.post('/checkout_sessions', async (req, res) => {
  const { items, fulfillment_details } = req.body;

  // Create checkout session in your system
  const session = {
    session_id: 'sess_' + Date.now(),
    merchant: {
      name: 'Your Store',
      url: 'https://yourstore.com'
    },
    cart: {
      items: items.map(item => ({
        id: item.id,
        quantity: item.quantity,
        // Fetch from your product DB
      }))
    },
    status: 'open'
  };

  res.status(201).json(session);
});

app.listen(3000, () => {
  console.log('ACP server running on port 3000');
});
```

**See full documentation**: https://developers.openai.com/commerce/

### 4. Integration Paths

#### Shopify (Easiest for UCP)

**Native Integration**: ✅
1. Go to Shopify Admin
2. Navigate to Agentic Storefronts
3. Enable Google AI Mode
4. Your store is now discoverable in Google Search AI Mode

**Custom App**:
1. Use Shopify Storefront API
2. Build UCP adapter
3. Map Shopify checkout to UCP sessions

#### WooCommerce (Requires Custom Plugin)

**Build Plugin**: ⚠️ No native support yet

1. Create WordPress plugin structure:
```
woocommerce-ucp/
├── woocommerce-ucp.php        # Main plugin
├── includes/
│   ├── class-manifest.php      # /.well-known/ucp
│   ├── class-checkout.php      # Checkout API
│   └── class-orders.php        # Order webhooks
└── api/
    └── endpoints.php           # REST routes
```

2. Register REST API endpoints
3. Map WooCommerce entities to UCP schemas
4. Implement OAuth for identity linking

**Watch for**: Official WooCommerce UCP support (requested by community)

### 5. Testing Your Implementation

#### For UCP

**Use Conformance Tests**:
```bash
git clone https://github.com/Universal-Commerce-Protocol/conformance
cd conformance
# Follow test instructions
```

**Test in Google AI Mode**:
- Requires merchant center account
- Products must be approved
- Test checkout flow end-to-end

#### For ACP

**Test with ChatGPT**:
- Apply for merchant access at developers.openai.com
- Implement required endpoints
- Test instant checkout in ChatGPT

**Use Stripe Tools**:
- Stripe provides testing tools for ACP
- See: https://docs.stripe.com/agentic-commerce

### 6. Key Resources

#### Documentation
- **UCP Spec**: https://ucp.dev/specification/overview
- **ACP Spec**: https://agenticcommerce.dev
- **OpenAI Commerce Docs**: https://developers.openai.com/commerce/
- **Stripe Agentic**: https://docs.stripe.com/agentic-commerce

#### GitHub Repositories
- **UCP Core**: https://github.com/Universal-Commerce-Protocol/ucp
- **UCP Samples**: https://github.com/Universal-Commerce-Protocol/samples
- **ACP Core**: https://github.com/agentic-commerce-protocol/agentic-commerce-protocol

#### Integration Guides
- **Shopify UCP**: https://www.shopify.com/ucp
- **Google Merchant**: https://developers.google.com/merchant/ucp/
- **WordPress UCP**: https://wearepresta.com/universal-commerce-protocol-ucp-wordpress-2026-agentic-commerce/

### 7. Next Steps for This Project

Choose your path:

#### Path 1: Learn by Exploring
1. Read through both protocol specifications
2. Compare schema structures
3. Understand capability discovery
4. Study payment flows

#### Path 2: Build a Test Server
1. Clone UCP samples repo
2. Set up a basic product catalog (SQLite)
3. Implement checkout endpoint
4. Test with local tools

#### Path 3: Production Integration
1. Choose Shopify (easiest) or WooCommerce (requires plugin)
2. For Shopify: Use native integration
3. For WooCommerce: Build custom plugin
4. Test with Google AI Mode or ChatGPT

#### Path 4: Contribute to Ecosystem
1. Join GitHub discussions
2. Report issues or suggest improvements
3. Build plugins/adapters for other platforms
4. Share learnings with community

### 8. Common Commands

```bash
# Explore UCP schemas
cd ucp-try-1/ucp
find spec -name "*.json" | xargs grep -l "checkout"

# View ACP OpenAPI spec
cd ucp-try-1/acp
cat spec/openapi/openapi.agentic_checkout.yaml | less

# Search for specific capabilities
grep -r "payment_handler" ucp/spec/
grep -r "delegate_payment" acp/spec/

# Install dependencies for samples (when cloned)
# For Python UCP samples:
pip install -r requirements.txt

# For Node.js examples:
npm install
```

### 9. Important Concepts

#### Capability Discovery
Both protocols use discovery manifests:
- **UCP**: `/.well-known/ucp` returns JSON with capabilities
- **ACP**: Similar merchant manifest approach

#### Checkout Flow
1. **Discovery**: Agent finds your store capabilities
2. **Session Create**: Agent creates checkout session
3. **Cart Management**: Add/update items, calculate totals
4. **Payment**: Secure payment token exchange
5. **Complete**: Finalize order
6. **Order Updates**: Webhooks for fulfillment events

#### Security
- HTTPS required everywhere
- Request signature validation
- OAuth 2.0 for identity (UCP)
- Payment token encryption
- Agent authentication

### 10. Support & Community

#### UCP Community
- **Discussions**: https://github.com/Universal-Commerce-Protocol/ucp/discussions
- **Issues**: https://github.com/Universal-Commerce-Protocol/ucp/issues

#### ACP Community
- **Discussions**: https://github.com/agentic-commerce-protocol/agentic-commerce-protocol/discussions
- **Issues**: https://github.com/agentic-commerce-protocol/agentic-commerce-protocol/issues

#### Get Help
- Review examples in both repos
- Check existing issues on GitHub
- Read the full specification docs
- Join discussions in respective communities

---

## Quick Reference

### UCP Endpoints (Example)
```
GET  /.well-known/ucp                    # Discovery
POST /ucp/checkout/sessions              # Create session
GET  /ucp/checkout/sessions/{id}         # Get session
POST /ucp/checkout/sessions/{id}         # Update session
POST /ucp/checkout/sessions/{id}/complete # Complete
```

### ACP Endpoints (Example)
```
POST /checkout_sessions                  # Create
GET  /checkout_sessions/{id}             # Retrieve
POST /checkout_sessions/{id}             # Update
POST /checkout_sessions/{id}/complete    # Complete
POST /checkout_sessions/{id}/cancel      # Cancel
```

### Key Schema Files

**UCP**:
- `spec/discovery/profile_schema.json` - Capability manifest
- `spec/schemas/shopping/checkout_session_resp.json` - Checkout response
- `spec/schemas/shopping/types/line_item.json` - Cart items

**ACP**:
- `spec/openapi/openapi.agentic_checkout.yaml` - Full API spec
- `examples/examples.agentic_checkout.json` - Request/response examples

---

**Ready to dive deeper?** Read `AGENTIC_COMMERCE_GUIDE.md` for comprehensive comparison and integration strategies.
