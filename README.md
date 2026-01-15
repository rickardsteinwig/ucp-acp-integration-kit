# Agentic Commerce Toolkit

> **⚠️ DISCLAIMER: This code is NOT TESTED and provided AS-IS for educational and experimental purposes only.**

Reference implementations of UCP and ACP protocols for AI-powered commerce. Includes Shopify adapter, WooCommerce plugin, and custom server examples. Educational resource - not production-ready.

## 🎯 About This Project

This repository contains reference implementations and learning materials for building agentic commerce systems using:
- **UCP** (Universal Commerce Protocol) - Google's open standard
- **ACP** (Agentic Commerce Protocol) - OpenAI/Stripe's open standard

The code here was created to help the community understand and experiment with these emerging protocols. It includes working examples for Shopify, WooCommerce, and custom e-commerce platforms.

## ⚠️ Important Notes

### NOT PRODUCTION READY
- This code has **NOT been tested** in production environments
- Use at your own risk
- No warranties or guarantees provided
- Security features may be incomplete
- Performance has not been optimized

### No Support
- **NO support is provided** for this code
- **NO questions will be answered** in issues or discussions
- This is a learning resource, not a supported product
- You are responsible for adapting and testing the code for your needs

### Contact
For business inquiries only: **rickard@steinwig.se**

---

## 📦 What's Included

### 1. Complete Documentation
- **AGENTIC_COMMERCE_GUIDE.md** - Deep dive into UCP vs ACP
- **QUICKSTART.md** - Quick reference guide
- **Integration READMEs** - Detailed setup for each platform

### 2. Protocol Specifications
- **ucp/** - Google's Universal Commerce Protocol (cloned)
- **acp/** - OpenAI's Agentic Commerce Protocol (cloned)
- **ucp-samples/** - Official UCP reference implementations

### 3. Three Integration Examples
- **Custom UCP Server** (Python/FastAPI) - Flexible standalone server
- **Shopify UCP Adapter** (Node.js/TypeScript) - Shopify integration
- **WooCommerce UCP Plugin** (WordPress/PHP) - WooCommerce plugin

---

## 🚀 Quick Start

### Repository Structure
```
agentic-commerce-toolkit/
├── README.md                          # This file
├── AGENTIC_COMMERCE_GUIDE.md          # Complete guide
├── QUICKSTART.md                      # Quick reference
│
├── ucp/                               # UCP specification (cloned)
├── acp/                               # ACP specification (cloned)
├── ucp-samples/                       # UCP samples (cloned)
│
└── integrations/                      # Implementation examples
    ├── custom-ucp-server/             # Python/FastAPI
    ├── shopify-ucp-adapter/           # Node.js/TypeScript
    └── woocommerce-ucp-plugin/        # WordPress/PHP
```

### Getting Started

1. **Clone this repository**
   ```bash
   git clone https://github.com/rickardsteinwig/agentic-commerce-toolkit.git
   cd agentic-commerce-toolkit
   ```

2. **Read the guides**
   - Start with this README
   - Read QUICKSTART.md for quick reference
   - Dive into AGENTIC_COMMERCE_GUIDE.md for deep understanding

3. **Choose an integration**
   - See `integrations/README.md` for comparison
   - Each integration has its own detailed README

4. **Experiment and adapt**
   - These are reference implementations
   - Modify to fit your needs
   - Test thoroughly before any production use

---

## 🌍 Geographic Availability

### Protocol Support
- **UCP & ACP**: Work globally (open standards)
- **Implementations**: Work anywhere

### Platform Limitations
- **Google AI Mode**: Initially USA, expanding gradually
- **ChatGPT Shopping**: Initially USA, expansion planned
- **Your Server**: Ready for when platforms become available in your region

### Sweden-Specific
All implementations include:
- ✅ SEK (Swedish Krona) support
- ✅ Swedish VAT (25%) handling
- ✅ Ready for Swedish e-commerce
- ✅ Swedish localization (WooCommerce plugin)

---

## 📚 Learning Resources

### Official Documentation
- **UCP Specification**: https://ucp.dev
- **ACP Specification**: https://agenticcommerce.dev
- **OpenAI Commerce**: https://developers.openai.com/commerce/
- **Stripe Agentic**: https://docs.stripe.com/agentic-commerce

### GitHub Repositories
- **UCP Core**: https://github.com/Universal-Commerce-Protocol/ucp
- **ACP Core**: https://github.com/agentic-commerce-protocol/agentic-commerce-protocol
- **UCP Samples**: https://github.com/Universal-Commerce-Protocol/samples

### Platform Integration
- **Shopify UCP**: https://www.shopify.com/ucp
- **Google Merchant**: https://developers.google.com/merchant/ucp/

---

## 🔐 Security Warning

### Before Using This Code

**CRITICAL**: This code includes placeholder values and simplified security. Before any production use:

1. **Never commit secrets**
   - Use `.env` files (already in .gitignore)
   - Use environment variables
   - Use secret management services

2. **Implement proper authentication**
   - API key validation
   - OAuth 2.0 where applicable
   - Request signature verification

3. **Enable HTTPS**
   - All endpoints must use HTTPS
   - No exceptions in production

4. **Add rate limiting**
   - Protect against abuse
   - Use Redis or similar

5. **Validate all inputs**
   - Never trust user input
   - Sanitize and validate everything

6. **Test thoroughly**
   - Unit tests
   - Integration tests
   - Security audits

---

## 📋 Features

### Custom UCP Server
- Full UCP 2026-01-11 specification
- Python/FastAPI REST API
- In-memory storage (replaceable)
- Payment handler integration
- Idempotency support

### Shopify UCP Adapter
- Shopify Storefront API integration
- Real-time product sync
- Shop Pay support
- TypeScript type safety
- Global compatibility

### WooCommerce UCP Plugin
- Native WordPress integration
- WooCommerce REST API
- Admin settings page
- Webhook support
- Swedish VAT support
- Translation ready

---

---

## 🚫 What This Is NOT

- ❌ A production-ready solution
- ❌ A supported product
- ❌ Security-audited code
- ❌ Performance-optimized
- ❌ Maintained or updated regularly
- ❌ Suitable for production without significant modifications

---

## ✅ What This IS

- ✅ Educational resource
- ✅ Learning materials
- ✅ Reference implementations
- ✅ Starting point for experimentation
- ✅ Protocol exploration toolkit
- ✅ Community contribution

---

## 📖 How to Use This Repository

### For Learning
1. Read the documentation
2. Study the protocol specifications
3. Review the implementation examples
4. Experiment with the code locally

### For Development
1. Choose an integration that fits your platform
2. Copy the relevant code
3. Modify for your specific needs
4. Add proper security measures
5. Test thoroughly
6. Deploy to your infrastructure

### For Reference
1. Compare UCP vs ACP implementations
2. Understand protocol structure
3. See API endpoint patterns
4. Learn integration approaches

---

## 🤝 Contributing

This is a personal project shared for educational purposes.

**No pull requests or contributions are being accepted.**

However, you are free to:
- Fork this repository
- Modify the code
- Use it in your own projects
- Share your learnings

---

## 📜 License

Apache License 2.0

Copyright 2026 Rickard Steinwig

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.

See the LICENSE file for the full license text.

---

## 📧 Contact

**For business inquiries ONLY**: rickard@steinwig.se

**Please do NOT contact for**:
- Support questions
- Bug reports
- Feature requests
- Implementation help
- General questions

This is provided as-is for educational purposes.

---

## 🙏 Acknowledgments

This project uses and references:
- **Universal Commerce Protocol** by Google and partners
- **Agentic Commerce Protocol** by OpenAI and Stripe
- Official UCP samples and specifications
- Open source libraries and frameworks

All protocols and specifications are the property of their respective owners and are used here for educational purposes under their respective open source licenses.

---

**Last Updated**: 2026-01-15

**Remember**: This code is NOT tested. Use at your own risk. No support provided.
