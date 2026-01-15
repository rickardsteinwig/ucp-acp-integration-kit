# Frequently Asked Questions (FAQ)

> **Note**: These FAQs are for educational purposes. No support is provided. Contact rickard@steinwig.se for business inquiries only.

---

## 1. Will Google AI Mode work in Sweden/Europe?

**Short answer**: Not initially, but your UCP server will be ready when it does.

**Details**:
- Google AI Mode is initially launching in the USA
- Gradual international rollout expected
- Your UCP implementation will work globally
- When Google expands to Sweden/Europe, you'll be ready
- The protocol itself has no geographic restrictions

**Workaround**: Implement UCP now, test with custom agents, and be first in line when platforms expand to your region.

---

## 2. What's the difference between UCP and ACP?

**UCP (Universal Commerce Protocol)**:
- Led by Google + 20+ partners (Shopify, Etsy, Walmart, Target)
- Production-ready since January 2026
- Used by Google AI Mode, Gemini
- Broader ecosystem support
- Multiple transport layers (REST, MCP, A2A)

**ACP (Agentic Commerce Protocol)**:
- Led by OpenAI + Stripe
- Draft specification
- Used by ChatGPT Instant Checkout
- Focused on ChatGPT integration
- REST API primary transport

**Bottom line**: Both solve the same problem. UCP has broader adoption, ACP is ChatGPT-focused. They're designed to coexist.

---

## 3. Do I need both Shopify native UCP AND this adapter?

**No, you don't need both.**

**Shopify Native UCP** (built-in):
- Available in Shopify Admin → Agentic Storefronts
- Automatic integration with Google AI Mode
- No coding required
- Best for most Shopify merchants

**This Shopify Adapter** (custom):
- For advanced use cases
- Custom integrations beyond native support
- Multi-store management
- When you need more control

**Recommendation**: Use Shopify's native UCP unless you have specific custom requirements.

---

## 4. Can I use this code in production?

**Short answer**: NOT as-is. Significant work required.

**What's missing**:
- ❌ Security audits
- ❌ Production testing
- ❌ Performance optimization
- ❌ Rate limiting
- ❌ Proper authentication
- ❌ Database optimization
- ❌ Monitoring and logging
- ❌ Error handling completeness

**Before production**:
1. Complete security review
2. Add proper authentication (OAuth 2.0)
3. Implement rate limiting
4. Use production-grade database
5. Add comprehensive logging
6. Performance testing
7. Load testing
8. Compliance review (GDPR, PCI-DSS)

**Use this as**: Learning resource and starting point, not production solution.

---

## 5. Which platform should I integrate with?

**Choose based on your current setup**:

**Use Custom Server if**:
- You have a custom e-commerce platform
- You're building headless commerce
- You want maximum flexibility
- You have Python/backend developers

**Use Shopify Adapter if**:
- You're on Shopify
- You need custom functionality beyond native UCP
- You're building a Shopify app
- You have Node.js developers

**Use WooCommerce Plugin if**:
- You're on WordPress/WooCommerce
- You want WordPress Admin integration
- You have PHP/WordPress developers
- You need Sweden-specific features (SEK, VAT)

---

## 6. What payment methods are supported?

**UCP implementations**:
- Any payment gateway via handlers
- Stripe (example included)
- PayPal (configurable)
- Shop Pay (Shopify)
- Swedish methods: Klarna, Swish (you add)
- Credit cards (via payment processors)

**How payment works**:
1. Your UCP server lists payment handlers in discovery
2. AI agent reads available methods
3. Agent collects payment from user
4. Agent sends payment token to your server
5. You process payment with your gateway

**Swedish payments**:
- Klarna: Add handler configuration
- Swish: Add handler configuration
- Both integrate via existing WooCommerce/Shopify plugins

---

## 7. How do product feeds work with UCP?

**UCP doesn't use traditional product feeds.**

**Traditional approach** (old):
- XML/CSV feed files
- Scheduled uploads
- Batch processing
- Google Merchant Center ingestion

**UCP approach** (new):
- Real-time API queries
- AI agent discovers products via `/products` endpoint
- Dynamic availability checks
- No static feed files needed

**Your responsibility**:
1. Expose products via UCP REST API
2. Return accurate inventory
3. Keep prices updated in real-time
4. Provide product metadata

**Google Merchant Center**:
- Still required for Google AI Mode
- But UCP provides dynamic updates
- No more feed synchronization issues

---

## 8. What about product attributes and schema.org?

**Product attributes in UCP**:
```json
{
  "id": "prod_123",
  "title": "Product Name",
  "description": "Description",
  "price": 9999,  // cents
  "currency": "SEK",
  "image_url": "https://...",
  "available": true,
  "attributes": [
    {"name": "Color", "value": "Blue"},
    {"name": "Size", "value": "Large"}
  ]
}
```

**Schema.org compatibility**:
- UCP uses simplified schema
- Maps to schema.org Product type
- AI agents understand both
- No need for JSON-LD markup

**Rich product data**:
- Add custom attributes
- Include variant information
- Provide category taxonomy
- Add structured metadata

**SEO impact**:
- Keep existing schema.org markup
- UCP is for agent commerce, not SEO
- Both can coexist

---

## 9. Do I need separate implementations for different countries?

**No, single implementation works globally.**

**Currency handling**:
```json
{
  "currency": "SEK",  // Sweden
  "currency": "EUR",  // Eurozone
  "currency": "USD"   // USA
}
```

**Localization**:
- Set currency per request
- Calculate tax based on location
- Adapt shipping to country
- Translate product descriptions

**Multi-country setup**:
1. Detect buyer location from request
2. Return prices in local currency
3. Calculate appropriate taxes (VAT, sales tax)
4. Show relevant shipping options
5. Handle local payment methods

**This code includes**:
- ✅ SEK support (Sweden)
- ✅ Swedish VAT (25%)
- ✅ Multi-currency ready
- ✅ Localization framework

---

## 10. What AI platforms will support UCP/ACP?

**Currently available** (January 2026):

**UCP Support**:
- ✅ Google AI Mode (USA)
- ✅ Google Gemini (USA)
- ✅ Shopify native (global)
- 🔄 Expanding to other regions

**ACP Support**:
- ✅ ChatGPT (USA)
- 🔄 Expanding to other regions

**Coming soon** (expected):
- Microsoft Copilot (via Shopify partnership)
- Claude (Anthropic)
- Perplexity AI
- Other AI assistants

**Building your own**:
- You can build custom agents
- Both protocols are open standards
- Connect any AI to your commerce backend
- No platform lock-in

**Future ecosystem**:
- More AI platforms will adopt UCP/ACP
- Expect rapid expansion in 2026-2027
- Your implementation will work with all compatible platforms
- First-mover advantage for early adopters

**Recommendation**: Implement now, be ready when platforms expand to your market.

---

## Additional Questions?

Remember:
- ⚠️ This code is NOT tested
- ❌ No support provided
- 📧 Business inquiries only: rickard@steinwig.se

For protocol questions:
- **UCP**: https://github.com/Universal-Commerce-Protocol/ucp/discussions
- **ACP**: https://github.com/agentic-commerce-protocol/agentic-commerce-protocol/discussions

---

**Last updated**: 2026-01-15
