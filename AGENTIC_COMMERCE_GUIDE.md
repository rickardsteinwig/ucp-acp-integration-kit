# Agentic Commerce Protocols: Complete Guide

**Project**: ucp-try-1
**Created**: 2026-01-15
**Purpose**: Understanding and implementing agentic commerce protocols (UCP & ACP)

---

## Table of Contents

1. [Overview](#overview)
2. [UCP vs ACP Comparison](#ucp-vs-acp-comparison)
3. [UCP (Universal Commerce Protocol)](#ucp-universal-commerce-protocol)
4. [ACP (Agentic Commerce Protocol)](#acp-agentic-commerce-protocol)
5. [Integration Approaches](#integration-approaches)
6. [Shopify Integration](#shopify-integration)
7. [WooCommerce Integration](#woocommerce-integration)
8. [Next Steps](#next-steps)

---

## Overview

Agentic commerce protocols are standardized interfaces that enable AI agents to facilitate purchases on behalf of users. Two major protocols have emerged:

- **UCP (Universal Commerce Protocol)** - Led by Google with Shopify, Etsy, Wayfair, Target, Walmart
- **ACP (Agentic Commerce Protocol)** - Led by OpenAI and Stripe

Both aim to solve the same problem: enabling seamless commerce experiences where AI agents can discover products, manage carts, and complete purchases across different platforms without custom integrations.

---

## UCP vs ACP Comparison

| Aspect | UCP (Google) | ACP (OpenAI/Stripe) |
|--------|-------------|---------------------|
| **Status** | Released Jan 11, 2026 | Draft (Sep 2025) |
| **Maintainers** | Google + 20+ partners | OpenAI + Stripe |
| **Version** | 2026-01-11 | Draft |
| **License** | Apache 2.0 | Apache 2.0 |
| **Primary Use** | Google Search AI Mode, Gemini | ChatGPT Instant Checkout |
| **Transport** | REST, JSON-RPC, MCP, A2A | REST APIs |
| **Payment** | AP2 protocol, multiple handlers | Stripe-focused, flexible |
| **Discovery** | /.well-known/ucp manifest | Similar manifest approach |
| **Focus** | Universal standard, platform-agnostic | ChatGPT-first, expanding |
| **Adoption** | Shopify native, community growing | Etsy, Shopify merchants |
| **Interoperability** | Designed to work with other protocols | Compatible with ecosystem |

### Key Differences

1. **Governance**: UCP has broader industry consortium; ACP is led by OpenAI/Stripe
2. **Implementation**: UCP is production-ready with multiple platforms; ACP is ChatGPT-focused
3. **Compatibility**: Google states UCP can coexist with ACP and other agentic protocols
4. **Integration Complexity**: Both offer similar complexity, but tooling differs

---

## UCP (Universal Commerce Protocol)

### Architecture

UCP defines modular **Capabilities** that businesses can implement:

#### Core Capabilities

1. **Checkout**
   - Create and manage checkout sessions
   - Cart management with line items
   - Dynamic tax calculation
   - Support for complex fulfillment (shipping, pickup, digital)
   - Discount and loyalty integration
   - Native or embedded checkout UI

2. **Identity Linking**
   - OAuth 2.0 based authorization
   - Secure agent-to-business relationships
   - Scope-based permissions (e.g., `ucp:scopes:checkout_session`)

3. **Order Management**
   - Webhook-based order updates
   - Lifecycle events (shipped, delivered, returned)
   - Real-time tracking
   - Return processing

4. **Payment Token Exchange**
   - Integration with AP2 (Agent Payments Protocol)
   - Support for multiple payment handlers
   - Cryptographic proof of user consent
   - Secure token exchange between PSPs

### Technical Stack

```
Transports: REST APIs, JSON-RPC, MCP, A2A
Discovery: /.well-known/ucp
Payment: AP2 protocol with multiple handlers
Security: OAuth 2.0, request signatures, verifiable credentials
Data Format: JSON schemas
```

### Key Features

- **Merchant of Record**: Businesses retain full control
- **Extensible**: Capabilities can be extended with custom logic
- **Surface-Agnostic**: Works across chat, voice, visual interfaces
- **Open Ecosystem**: Open wallet support, provider choice

### Repository Structure

```
ucp/
├── spec/
│   ├── discovery/           # Profile schemas
│   ├── schemas/             # JSON schemas for types
│   ├── services/            # API service definitions
│   └── handlers/            # Payment handlers
├── docs/                    # Documentation
├── main.py                  # Reference implementation
├── generate_schemas.py      # Schema generation tools
└── README.md
```

### Sample UCP Profile

```json
{
  "ucp": {
    "version": "2026-01-11"
  },
  "capabilities": [
    {
      "type": "checkout",
      "binding": {
        "type": "rest",
        "base_url": "https://example.com/ucp"
      }
    },
    {
      "type": "identity_linking",
      "binding": {
        "type": "oauth2",
        "authorization_server": "https://example.com"
      }
    }
  ],
  "payment_handlers": [
    "google_pay",
    "shop_pay",
    "stripe"
  ]
}
```

---

## ACP (Agentic Commerce Protocol)

### Architecture

ACP focuses on streamlining the checkout flow between AI agents and merchants:

#### Core Components

1. **Agentic Checkout API**
   - Session creation and management
   - Cart operations
   - Address and payment collection
   - Order completion

2. **Delegate Payment**
   - Secure payment token passing
   - PayPal integration (primary)
   - Stripe support
   - Token exchange protocol

3. **Webhooks**
   - Order status updates
   - Fulfillment notifications
   - Return/refund events

### Technical Stack

```
Transports: REST APIs (OpenAPI 3.1)
Discovery: Merchant manifest
Payment: Delegated payment tokens
Security: Request signatures, HTTPS
Data Format: JSON Schema
```

### Key Features

- **Instant Checkout**: Complete purchases without leaving ChatGPT
- **Merchant Control**: Businesses remain merchant of record
- **Flexible Config**: Supports physical goods, digital, subscriptions
- **Production Ready**: Reference implementations from OpenAI and Stripe

### Repository Structure

```
acp/
├── spec/
│   ├── openapi/                      # OpenAPI specs
│   │   ├── openapi.agentic_checkout.yaml
│   │   ├── openapi.delegate_payment.yaml
│   │   └── openapi.agentic_checkout_webhook.yaml
│   └── json-schema/                  # JSON schemas
├── examples/                         # Example requests/responses
├── rfcs/                            # Design documents
├── docs/                            # Governance docs
└── README.md
```

### Sample ACP Checkout Session

```json
{
  "session_id": "sess_abc123",
  "merchant": {
    "name": "Example Store",
    "url": "https://example.com"
  },
  "cart": {
    "items": [
      {
        "id": "item_1",
        "name": "Product Name",
        "quantity": 1,
        "price": {
          "amount": 2999,
          "currency": "USD"
        }
      }
    ]
  },
  "payment": {
    "methods": ["paypal", "stripe"],
    "delegate_token": "tok_xyz789"
  },
  "shipping_address": {
    "name": "John Doe",
    "street": "123 Main St",
    "city": "San Francisco",
    "state": "CA",
    "postal_code": "94103",
    "country": "US"
  }
}
```

---

## Integration Approaches

### General Integration Strategy

Both protocols follow a similar pattern:

1. **Implement Manifest/Discovery**
   - Host a capability manifest at `/.well-known/` endpoint
   - Declare supported features

2. **Build API Endpoints**
   - Implement checkout session management
   - Handle cart operations
   - Support payment processing

3. **Add Security**
   - Implement OAuth 2.0 for identity (UCP)
   - Add request signature validation
   - Use HTTPS everywhere

4. **Test Integration**
   - Use conformance tests (UCP has official tests)
   - Test with AI platforms (ChatGPT, Google AI Mode)
   - Validate payment flows

### Technology Choices

#### For UCP
- **Python**: Use the reference implementation in `ucp/main.py`
- **Node.js**: Use TypeScript types generated from schemas
- **Any language**: Implement REST endpoints per spec

#### For ACP
- **OpenAI SDK**: Use OpenAI's reference implementation
- **Stripe SDK**: Use Stripe's agentic commerce tools
- **Custom**: Implement OpenAPI specs directly

---

## Shopify Integration

### Current Status

**UCP Integration**: ✅ Native Support (Jan 2026)

Shopify has native UCP support through **Agentic Storefronts**:

- Automatic integration with Google AI Mode and Gemini
- Managed from Shopify Admin (no custom apps needed)
- Embedded checkout experiences
- Support for Microsoft Copilot integration

### Implementation

For Shopify merchants:

1. **Enable in Admin**
   - Go to Shopify Admin > Agentic Storefronts
   - Enable Google AI Mode integration
   - Configure payment handlers (Shop Pay recommended)

2. **Configure Capabilities**
   - Shopify automatically exposes UCP endpoints
   - Products from Merchant Center are discoverable
   - Checkout flows are handled by Shopify

3. **Test**
   - Search for your products in Google AI Mode
   - Complete test purchases
   - Verify order flow in Shopify Admin

### For Custom Shopify Apps

If building a custom app that needs UCP:

1. Use Shopify's Storefront API
2. Implement UCP capabilities on top
3. Map Shopify's checkout to UCP checkout sessions
4. Use Shopify's payment gateway as UCP payment handler

**Reference**: [Shopify UCP Documentation](https://www.shopify.com/ucp)

---

## WooCommerce Integration

### Current Status

**UCP Integration**: ⚠️ Not Native (Community Requested)

WooCommerce does not yet have official UCP support, but integration is possible:

### Implementation Approaches

#### Option 1: Build Custom Plugin

Create a WordPress plugin that implements UCP:

```php
// Example structure
woocommerce-ucp-integration/
├── ucp-integration.php           # Main plugin file
├── includes/
│   ├── class-ucp-manifest.php    # /.well-known/ucp handler
│   ├── class-ucp-checkout.php    # Checkout API
│   ├── class-ucp-identity.php    # OAuth handler
│   └── class-ucp-order.php       # Order webhooks
├── api/
│   └── endpoints.php             # REST API routes
└── readme.txt
```

**Steps**:

1. **Add REST API Endpoints**
   ```php
   add_action('rest_api_init', function() {
       register_rest_route('ucp/v1', '/checkout/sessions', [
           'methods' => 'POST',
           'callback' => 'create_checkout_session',
           'permission_callback' => 'verify_ucp_request'
       ]);
   });
   ```

2. **Implement Manifest**
   ```php
   add_action('init', function() {
       add_rewrite_rule(
           '^\.well-known/ucp$',
           'index.php?ucp_manifest=1',
           'top'
       );
   });
   ```

3. **Map WooCommerce to UCP**
   - WooCommerce Cart → UCP Line Items
   - WooCommerce Checkout → UCP Checkout Session
   - WooCommerce Orders → UCP Order Updates

4. **Handle Payments**
   - Use WooCommerce payment gateways
   - Expose as UCP payment handlers
   - Implement token exchange if needed

#### Option 2: Use MCP Bridge

WooCommerce released MCP (Model Context Protocol) support in late 2025:

1. Use WooCommerce MCP as security proxy
2. Build UCP adapter on top of MCP
3. Translate UCP requests to MCP actions

#### Option 3: Wait for Official Support

Track the feature request:
- **WooCommerce Feature Request**: [Native UCP Support](https://woocommerce.com/feature-request/native-support-for-googles-universal-commerce-protocol-ucp-for-ai-agents/)

### WordPress UCP Implementation Guide

**Reference**: [WordPress UCP Guide 2026](https://wearepresta.com/universal-commerce-protocol-ucp-wordpress-2026-agentic-commerce/)

---

## Next Steps

### For This Project (ucp-try-1)

1. **Explore Reference Implementations**
   - Study `ucp/main.py` for UCP implementation
   - Review ACP OpenAPI specs for endpoint structure
   - Test with sample data

2. **Build Test Server**
   - Create a simple product catalog
   - Implement basic checkout flow
   - Add one payment handler

3. **Connect to Shopify (UCP)**
   - Use Shopify Admin for native integration
   - OR build custom app using Storefront API

4. **Build WooCommerce Plugin (UCP)**
   - Start with manifest endpoint
   - Add checkout session creation
   - Implement cart mapping

5. **Test with AI Platforms**
   - Google AI Mode (for UCP)
   - ChatGPT (for ACP)
   - Local testing with MCP tools

### Resources

#### UCP Resources
- **Docs**: https://ucp.dev
- **GitHub**: https://github.com/Universal-Commerce-Protocol/ucp
- **Samples**: https://github.com/Universal-Commerce-Protocol/samples
- **Conformance Tests**: https://github.com/Universal-Commerce-Protocol/conformance
- **Google Integration**: https://developers.google.com/merchant/ucp/

#### ACP Resources
- **Docs**: https://agenticcommerce.dev
- **GitHub**: https://github.com/agentic-commerce-protocol/agentic-commerce-protocol
- **OpenAI Docs**: https://developers.openai.com/commerce/
- **Stripe Docs**: https://docs.stripe.com/agentic-commerce

#### Additional Resources
- **AP2 Protocol**: https://ap2-protocol.org/
- **A2A Protocol**: https://a2a-protocol.org/
- **MCP**: https://modelcontextprotocol.io/

---

## Project Structure

```
ucp-try-1/
├── ucp/                          # Google UCP repository
│   ├── spec/                     # UCP specifications
│   ├── docs/                     # Documentation
│   └── main.py                   # Reference implementation
├── acp/                          # OpenAI ACP repository
│   ├── spec/                     # ACP specifications
│   └── examples/                 # Example requests
├── AGENTIC_COMMERCE_GUIDE.md     # This document
└── integrations/                 # Future: Custom integrations
    ├── shopify-ucp/              # Shopify UCP adapter
    └── woocommerce-ucp/          # WooCommerce UCP plugin
```

---

## Conclusion

Both UCP and ACP are pushing toward the same future: seamless agentic commerce. While they compete, they're designed to coexist. For maximum reach:

- **Implement both** if targeting multiple platforms
- **Start with UCP** for broader platform support (Google, Shopify ecosystem)
- **Add ACP** for ChatGPT integration

The protocol landscape is evolving rapidly. Stay updated with both communities and contribute to shaping the future of agentic commerce.

---

**Last Updated**: 2026-01-15
**Author**: ucp-try-1 project
