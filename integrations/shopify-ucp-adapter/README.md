# Shopify UCP Adapter

Connect your Shopify store to the Universal Commerce Protocol (UCP) for agentic commerce.

## Features

- ✅ Full UCP 2026-01-11 support
- ✅ Shopify Storefront API integration
- ✅ Automatic product syncing
- ✅ Checkout session management
- ✅ Shop Pay integration
- ✅ Real-time inventory
- 🌍 **Works globally including Sweden** (där Shopify är tillgängligt)

## Prerequisites

- Node.js 18+
- Shopify store (any plan)
- Shopify Storefront API access token
- (Optional) Shopify Admin API access token

## Quick Start

### 1. Get Shopify API Credentials

#### Storefront Access Token
1. Go to your Shopify Admin
2. Navigate to **Apps** > **Develop apps**
3. Create a new app
4. Enable **Storefront API**
5. Grant permissions: `unauthenticated_read_product_listings`, `unauthenticated_read_checkouts`
6. Copy the **Storefront Access Token**

#### Admin API Token (Optional)
1. In the same app, enable **Admin API**
2. Grant permissions: `read_products`, `write_checkouts`, `read_orders`
3. Copy the **Admin API access token**

### 2. Install Dependencies

```bash
npm install
```

### 3. Configure Environment

```bash
# Copy example env file
cp .env.example .env

# Edit with your credentials
nano .env
```

Add your Shopify credentials:
```env
SHOPIFY_SHOP_DOMAIN=your-store.myshopify.com
SHOPIFY_STOREFRONT_ACCESS_TOKEN=your_token_here
SHOPIFY_ADMIN_ACCESS_TOKEN=your_admin_token_here
SHOPIFY_SHOP_ID=your_shop_id
PORT=3000
```

### 4. Run the Server

```bash
# Development
npm run dev

# Production build
npm run build
npm start
```

Server runs on `http://localhost:3000`

## Testing

### 1. Check UCP Profile
```bash
curl http://localhost:3000/.well-known/ucp | jq
```

### 2. List Products
```bash
curl http://localhost:3000/products | jq
```

### 3. Create Checkout Session
```bash
curl -X POST http://localhost:3000/checkout-sessions \
  -H "Content-Type: application/json" \
  -H "idempotency-key: $(uuidgen)" \
  -H "request-id: $(uuidgen)" \
  -d '{
    "line_items": [
      {
        "item": {
          "id": "YOUR_PRODUCT_ID"
        },
        "quantity": 1
      }
    ],
    "buyer": {
      "email": "customer@example.com",
      "full_name": "Test Customer"
    },
    "currency": "SEK"
  }' | jq
```

## API Endpoints

### UCP Endpoints
- `GET /.well-known/ucp` - Discovery endpoint
- `POST /checkout-sessions` - Create checkout
- `GET /checkout-sessions/:id` - Get checkout
- `PUT /checkout-sessions/:id` - Update checkout
- `POST /checkout-sessions/:id/complete` - Complete checkout

### Helper Endpoints
- `GET /products` - List products
- `GET /products/:id` - Get product details
- `GET /health` - Health check

## Architecture

```
shopify-ucp-adapter/
├── src/
│   ├── server.ts              # Main Express server
│   ├── services/
│   │   ├── shopify.service.ts # Shopify API client
│   │   └── ucp.service.ts     # UCP translation layer
│   └── types/                 # TypeScript types
├── package.json
├── tsconfig.json
└── README.md
```

## Sverige-specifikt (Sweden-specific)

### Valuta
Adaptern stödjer SEK (svenska kronor):
```javascript
{
  "currency": "SEK"
}
```

### Shopify i Sverige
- Shopify fungerar helt i Sverige ✅
- Svensk kundtjänst tillgänglig
- Shop Pay fungerar med svenska banker
- Integrationer med Klarna, Swish möjliga

### AI-plattformar
- Google AI Mode: Kanske inte tillgängligt i Sverige än
- ChatGPT: USA först, men expanderar
- **Din UCP-server fungerar ändå** - redo när plattformarna lanserar i Sverige

## Deployment

### Docker

```dockerfile
FROM node:18-alpine

WORKDIR /app

COPY package*.json ./
RUN npm ci --production

COPY . .
RUN npm run build

EXPOSE 3000

CMD ["node", "dist/server.js"]
```

Build and run:
```bash
docker build -t shopify-ucp-adapter .
docker run -p 3000:3000 --env-file .env shopify-ucp-adapter
```

### Cloud Platforms

#### Railway
```bash
railway login
railway init
railway up
```

#### Heroku
```bash
heroku create your-app-name
git push heroku main
```

#### Vercel/Netlify
Requires serverless adapter - see documentation.

## Security

### Production Checklist

- [ ] Use HTTPS everywhere
- [ ] Enable rate limiting
- [ ] Implement request signature verification
- [ ] Store credentials in secure vault (not .env)
- [ ] Enable CORS for specific origins only
- [ ] Use Shopify webhook signatures
- [ ] Implement logging and monitoring

### Environment Variables Security

Never commit `.env` file to git:
```bash
# .gitignore
.env
.env.local
.env.production
```

Use environment variable management:
- Railway: Built-in env vars
- Heroku: Config vars
- Docker: Docker secrets
- K8s: ConfigMaps and Secrets

## Advanced Features

### Custom Payment Handlers

Add custom payment methods:
```typescript
// In ucp.service.ts
payment: {
  handlers: [
    {
      id: 'klarna',
      name: 'com.klarna.payment',
      version: '2026-01-11',
      // ...
    },
    {
      id: 'swish',
      name: 'com.swish.payment',
      version: '2026-01-11',
      // ...
    }
  ]
}
```

### Webhooks

Handle Shopify webhooks for order updates:
```typescript
app.post('/webhooks/shopify', async (req, res) => {
  const hmac = req.headers['x-shopify-hmac-sha256'];

  // Verify webhook
  if (verifyWebhook(req.body, hmac)) {
    // Process order update
    await handleOrderUpdate(req.body);
  }

  res.status(200).send();
});
```

### Multi-language Support

```typescript
const translations = {
  'sv-SE': {
    'checkout.title': 'Kassan',
    'checkout.complete': 'Slutför köp'
  },
  'en-US': {
    'checkout.title': 'Checkout',
    'checkout.complete': 'Complete Purchase'
  }
};
```

## Troubleshooting

### Common Issues

**Error: "Invalid Storefront Access Token"**
- Verify token in Shopify Admin
- Check token has correct permissions
- Ensure token is not expired

**Error: "Product not found"**
- Product must be published to Sales Channel
- Use correct product ID format
- Check product availability

**Error: "Checkout creation failed"**
- Verify variant IDs are correct
- Check product inventory
- Ensure valid quantity

### Debug Mode

Enable debug logging:
```env
DEBUG=shopify:*
NODE_ENV=development
```

View logs:
```bash
npm run dev
```

## Support

- Shopify API Docs: https://shopify.dev/api
- UCP Specification: https://ucp.dev
- GitHub Issues: Report in main project

## License

Apache 2.0
