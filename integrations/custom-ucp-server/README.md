# Custom UCP Server

A flexible, production-ready Universal Commerce Protocol server that can be adapted to any e-commerce backend.

## Features

- ✅ Full UCP 2026-01-11 specification support
- ✅ Checkout session management
- ✅ Product catalog integration
- ✅ Order creation and tracking
- ✅ Payment handler support (Stripe example)
- ✅ Idempotency key handling
- ✅ Request signature validation
- ✅ In-memory storage (easily replaceable with database)

## Quick Start

### Installation

```bash
# Install dependencies
pip install -r requirements.txt
```

### Run the Server

```bash
# Start the server
python server.py

# Server runs on http://localhost:8000
```

### Test the Server

```bash
# 1. Check UCP profile (discovery)
curl http://localhost:8000/.well-known/ucp | jq

# 2. List available products
curl http://localhost:8000/products | jq

# 3. Create a checkout session
curl -X POST http://localhost:8000/checkout-sessions \
  -H "Content-Type: application/json" \
  -H "UCP-Agent: profile=\"https://agent.example/profile\"" \
  -H "request-signature: test" \
  -H "idempotency-key: $(uuidgen)" \
  -H "request-id: $(uuidgen)" \
  -d '{
    "line_items": [
      {
        "item": {
          "id": "prod_001",
          "title": "Sample Product"
        },
        "quantity": 2
      }
    ],
    "buyer": {
      "full_name": "John Doe",
      "email": "john@example.com"
    },
    "currency": "USD"
  }' | jq

# 4. Get checkout session
curl http://localhost:8000/checkout-sessions/{session_id} \
  -H "UCP-Agent: profile=\"https://agent.example/profile\"" \
  -H "request-signature: test" | jq

# 5. Complete checkout
curl -X POST http://localhost:8000/checkout-sessions/{session_id}/complete \
  -H "UCP-Agent: profile=\"https://agent.example/profile\"" \
  -H "request-signature: test" \
  -H "idempotency-key: $(uuidgen)" \
  -H "request-id: $(uuidgen)" | jq
```

## Architecture

```
custom-ucp-server/
├── server.py              # Main FastAPI application
├── requirements.txt       # Python dependencies
├── README.md             # This file
└── config.py             # Configuration (optional)
```

## Customization

### Connect to Your Database

Replace the in-memory `Store` class with your database:

```python
# Example with PostgreSQL
from sqlalchemy import create_engine
from sqlalchemy.orm import sessionmaker

DATABASE_URL = "postgresql://user:password@localhost/dbname"
engine = create_engine(DATABASE_URL)
SessionLocal = sessionmaker(bind=engine)

# Replace store.products with database queries
def get_product(product_id: str):
    db = SessionLocal()
    return db.query(Product).filter(Product.id == product_id).first()
```

### Add Payment Integration

```python
# Example Stripe integration
import stripe

stripe.api_key = "sk_test_your_key"

@app.post("/checkout-sessions/{session_id}/complete")
async def complete_checkout_session(session_id: str, ...):
    session = store.sessions.get(session_id)

    # Process payment with Stripe
    payment_intent = stripe.PaymentIntent.create(
        amount=session.totals[-1]["amount"],
        currency=session.currency.lower(),
        metadata={"session_id": session_id}
    )

    # Create order after successful payment
    # ...
```

### Add Webhook Support

```python
@app.post("/webhooks/order-updates")
async def receive_webhook(webhook_url: str, data: Dict[str, Any]):
    """Send order updates to platform"""
    async with httpx.AsyncClient() as client:
        await client.post(webhook_url, json=data)
```

## API Endpoints

### Discovery
- `GET /.well-known/ucp` - Get UCP profile

### Checkout
- `POST /checkout-sessions` - Create checkout session
- `GET /checkout-sessions/{id}` - Get session
- `PUT /checkout-sessions/{id}` - Update session
- `POST /checkout-sessions/{id}/complete` - Complete checkout

### Orders
- `GET /orders/{id}` - Get order details

### Products
- `GET /products` - List products

### Health
- `GET /health` - Health check

## Configuration

Create a `.env` file:

```env
# Server
HOST=0.0.0.0
PORT=8000
DEBUG=False

# Database
DATABASE_URL=postgresql://user:password@localhost/dbname

# Payment
STRIPE_API_KEY=sk_test_...
STRIPE_PUBLISHABLE_KEY=pk_test_...

# Security
SECRET_KEY=your-secret-key
SIGNATURE_VERIFICATION=True
```

## Production Deployment

### Docker

```dockerfile
FROM python:3.11-slim

WORKDIR /app
COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

COPY . .

CMD ["uvicorn", "server:app", "--host", "0.0.0.0", "--port", "8000"]
```

### Using Gunicorn

```bash
pip install gunicorn

gunicorn server:app \
  --workers 4 \
  --worker-class uvicorn.workers.UvicornWorker \
  --bind 0.0.0.0:8000
```

### Environment Variables

```bash
export DATABASE_URL="postgresql://..."
export STRIPE_API_KEY="sk_live_..."
python server.py
```

## Integration Examples

### Connect to Existing E-commerce Platform

```python
# Example: WooCommerce API integration
from woocommerce import API

wcapi = API(
    url="https://yourstore.com",
    consumer_key="ck_...",
    consumer_secret="cs_...",
    version="wc/v3"
)

def get_product(product_id: str):
    """Get product from WooCommerce"""
    response = wcapi.get(f"products/{product_id}")
    return response.json()
```

### Connect to Custom Backend

```python
# Example: Custom REST API
import httpx

async def get_product(product_id: str):
    async with httpx.AsyncClient() as client:
        response = await client.get(
            f"https://your-api.com/products/{product_id}",
            headers={"Authorization": f"Bearer {API_TOKEN}"}
        )
        return response.json()
```

## Testing

```bash
# Run with pytest
pip install pytest pytest-asyncio httpx

pytest tests/
```

## Security Notes

### In Production

1. **Enable Signature Verification**
   - Implement proper request signature validation
   - Use public key cryptography

2. **Use HTTPS**
   - All endpoints must use HTTPS
   - Set up SSL certificates

3. **Rate Limiting**
   - Implement rate limiting per agent
   - Use Redis for distributed rate limiting

4. **Authentication**
   - Validate agent credentials
   - Implement OAuth 2.0 for identity linking

5. **Database**
   - Use connection pooling
   - Implement proper indexes
   - Enable query logging for debugging

## Support

- UCP Specification: https://ucp.dev
- GitHub Issues: Report issues in the main project
- Community: https://github.com/Universal-Commerce-Protocol/ucp/discussions

## License

Apache 2.0
