"""
Custom UCP Server Implementation
A flexible, production-ready UCP server that can be adapted to any e-commerce backend.
"""

from fastapi import FastAPI, HTTPException, Header, Request
from fastapi.responses import JSONResponse
from pydantic import BaseModel, Field
from typing import List, Optional, Dict, Any
from datetime import datetime, timedelta
import uuid
import logging

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

app = FastAPI(
    title="Custom UCP Server",
    description="Universal Commerce Protocol implementation",
    version="2026-01-11"
)

# ============================================================================
# Data Models
# ============================================================================

class UCPVersion(BaseModel):
    version: str = "2026-01-11"

class Capability(BaseModel):
    name: str
    version: str
    spec: Optional[str] = None
    schema: Optional[str] = None
    extends: Optional[str] = None
    config: Optional[Dict[str, Any]] = None

class ServiceBinding(BaseModel):
    schema_: Optional[str] = Field(None, alias="schema")
    endpoint: str

class Service(BaseModel):
    version: str
    spec: str
    rest: ServiceBinding
    mcp: Optional[Any] = None
    a2a: Optional[Any] = None
    embedded: Optional[Any] = None

class PaymentHandler(BaseModel):
    id: str
    name: str
    version: str
    spec: str
    config_schema: str
    instrument_schemas: List[str]
    config: Dict[str, Any]

class UCPProfile(BaseModel):
    ucp: Dict[str, Any]
    payment: Dict[str, List[PaymentHandler]]
    signing_keys: Optional[Any] = None

class Item(BaseModel):
    id: str
    title: str
    price: Optional[int] = None
    image_url: Optional[str] = None

class LineItem(BaseModel):
    id: Optional[str] = None
    item: Item
    quantity: int
    totals: Optional[List[Dict[str, Any]]] = None
    parent_id: Optional[str] = None

class Buyer(BaseModel):
    first_name: Optional[str] = None
    last_name: Optional[str] = None
    full_name: Optional[str] = None
    email: Optional[str] = None
    phone_number: Optional[str] = None

class PostalAddress(BaseModel):
    full_name: Optional[str] = None
    street_address: str
    address_locality: str
    address_region: str
    postal_code: str
    address_country: str

class PaymentInstrument(BaseModel):
    id: str
    handler_id: str
    data: Dict[str, Any]

class Payment(BaseModel):
    handlers: List[PaymentHandler]
    selected_instrument_id: Optional[str] = None
    instruments: List[PaymentInstrument]

class CheckoutSessionCreate(BaseModel):
    line_items: List[LineItem]
    buyer: Optional[Buyer] = None
    currency: str = "USD"
    payment: Optional[Payment] = None

class CheckoutSession(BaseModel):
    ucp: Dict[str, Any]
    id: str
    line_items: List[LineItem]
    buyer: Optional[Buyer] = None
    status: str
    currency: str
    totals: List[Dict[str, Any]]
    messages: Optional[List[Any]] = None
    links: List[Any] = []
    expires_at: Optional[str] = None
    continue_url: Optional[str] = None
    payment: Payment
    order_id: Optional[str] = None
    order_permalink_url: Optional[str] = None

# ============================================================================
# In-Memory Storage (Replace with your database)
# ============================================================================

class Store:
    def __init__(self):
        self.sessions: Dict[str, CheckoutSession] = {}
        self.products: Dict[str, Dict[str, Any]] = {}
        self.orders: Dict[str, Dict[str, Any]] = {}
        self._init_sample_products()

    def _init_sample_products(self):
        """Initialize with sample products"""
        self.products = {
            "prod_001": {
                "id": "prod_001",
                "title": "Sample Product 1",
                "price": 2999,  # Price in cents
                "description": "A great product",
                "image_url": "https://example.com/product1.jpg",
                "stock": 100
            },
            "prod_002": {
                "id": "prod_002",
                "title": "Sample Product 2",
                "price": 4999,
                "description": "Another great product",
                "image_url": "https://example.com/product2.jpg",
                "stock": 50
            }
        }

store = Store()

# ============================================================================
# Helper Functions
# ============================================================================

def calculate_totals(line_items: List[LineItem]) -> List[Dict[str, Any]]:
    """Calculate checkout totals"""
    subtotal = 0
    for item in line_items:
        if item.item.price:
            subtotal += item.item.price * item.quantity

    tax = int(subtotal * 0.1)  # 10% tax example
    total = subtotal + tax

    return [
        {"type": "subtotal", "display_text": "Subtotal", "amount": subtotal},
        {"type": "tax", "display_text": "Tax", "amount": tax},
        {"type": "total", "display_text": "Total", "amount": total}
    ]

def enrich_line_items(line_items: List[LineItem]) -> List[LineItem]:
    """Enrich line items with product details"""
    enriched_items = []
    for item in line_items:
        if not item.id:
            item.id = str(uuid.uuid4())

        # Get product details from store
        product = store.products.get(item.item.id)
        if product:
            item.item.title = product["title"]
            item.item.price = product["price"]
            item.item.image_url = product.get("image_url")

        # Calculate item totals
        if item.item.price:
            item_subtotal = item.item.price * item.quantity
            item.totals = [
                {"type": "subtotal", "amount": item_subtotal},
                {"type": "total", "amount": item_subtotal}
            ]

        enriched_items.append(item)

    return enriched_items

def validate_request_headers(
    ucp_agent: Optional[str] = None,
    request_signature: Optional[str] = None,
    idempotency_key: Optional[str] = None,
    request_id: Optional[str] = None
):
    """Validate required UCP headers"""
    # In production, implement proper signature verification
    if not idempotency_key:
        raise HTTPException(status_code=400, detail="Missing idempotency-key header")
    if not request_id:
        raise HTTPException(status_code=400, detail="Missing request-id header")

# ============================================================================
# API Endpoints
# ============================================================================

@app.get("/.well-known/ucp")
async def get_ucp_profile():
    """
    UCP Discovery Endpoint
    Returns the server's capabilities and payment handlers
    """
    profile = {
        "ucp": {
            "version": "2026-01-11",
            "services": {
                "dev.ucp.shopping": {
                    "version": "2026-01-11",
                    "spec": "https://ucp.dev/specs/shopping",
                    "rest": {
                        "schema": "https://ucp.dev/services/shopping/openapi.json",
                        "endpoint": "http://localhost:8000/"
                    },
                    "mcp": None,
                    "a2a": None,
                    "embedded": None
                }
            },
            "capabilities": [
                {
                    "name": "dev.ucp.shopping.checkout",
                    "version": "2026-01-11",
                    "spec": "https://ucp.dev/specs/shopping/checkout",
                    "schema": "https://ucp.dev/schemas/shopping/checkout.json",
                    "extends": None,
                    "config": None
                },
                {
                    "name": "dev.ucp.shopping.fulfillment",
                    "version": "2026-01-11",
                    "spec": "https://ucp.dev/specs/shopping/fulfillment",
                    "schema": "https://ucp.dev/schemas/shopping/fulfillment.json",
                    "extends": "dev.ucp.shopping.checkout",
                    "config": None
                }
            ]
        },
        "payment": {
            "handlers": [
                {
                    "id": "stripe",
                    "name": "com.stripe.payment",
                    "version": "2026-01-11",
                    "spec": "https://stripe.com/ucp/spec",
                    "config_schema": "https://stripe.com/ucp/config.json",
                    "instrument_schemas": [
                        "https://ucp.dev/schemas/shopping/types/card_payment_instrument.json"
                    ],
                    "config": {
                        "publishable_key": "pk_test_example"
                    }
                }
            ]
        },
        "signing_keys": None
    }

    return JSONResponse(content=profile)

@app.post("/checkout-sessions", response_model=CheckoutSession)
async def create_checkout_session(
    session_data: CheckoutSessionCreate,
    ucp_agent: Optional[str] = Header(None, alias="UCP-Agent"),
    request_signature: Optional[str] = Header(None, alias="request-signature"),
    idempotency_key: Optional[str] = Header(None, alias="idempotency-key"),
    request_id: Optional[str] = Header(None, alias="request-id")
):
    """
    Create a new checkout session
    """
    validate_request_headers(ucp_agent, request_signature, idempotency_key, request_id)

    logger.info(f"Creating checkout session with idempotency key: {idempotency_key}")

    # Check idempotency
    for session in store.sessions.values():
        if hasattr(session, '_idempotency_key') and session._idempotency_key == idempotency_key:
            logger.info(f"Returning cached session for idempotency key: {idempotency_key}")
            return session

    # Enrich line items with product details
    enriched_items = enrich_line_items(session_data.line_items)

    # Calculate totals
    totals = calculate_totals(enriched_items)

    # Create session
    session_id = str(uuid.uuid4())

    session = CheckoutSession(
        ucp={
            "version": "2026-01-11",
            "capabilities": [
                {
                    "name": "dev.ucp.shopping.checkout",
                    "version": "2026-01-11",
                    "spec": None,
                    "schema": None,
                    "extends": None,
                    "config": None
                }
            ]
        },
        id=session_id,
        line_items=enriched_items,
        buyer=session_data.buyer,
        status="ready_for_complete",
        currency=session_data.currency,
        totals=totals,
        payment=session_data.payment or Payment(handlers=[], instruments=[]),
        expires_at=(datetime.now() + timedelta(hours=1)).isoformat()
    )

    # Store session
    session._idempotency_key = idempotency_key
    store.sessions[session_id] = session

    logger.info(f"Created session {session_id}")

    return session

@app.get("/checkout-sessions/{session_id}", response_model=CheckoutSession)
async def get_checkout_session(
    session_id: str,
    ucp_agent: Optional[str] = Header(None, alias="UCP-Agent"),
    request_signature: Optional[str] = Header(None, alias="request-signature")
):
    """
    Get checkout session by ID
    """
    session = store.sessions.get(session_id)
    if not session:
        raise HTTPException(status_code=404, detail="Session not found")

    logger.info(f"Retrieved session {session_id}")
    return session

@app.put("/checkout-sessions/{session_id}", response_model=CheckoutSession)
async def update_checkout_session(
    session_id: str,
    update_data: Dict[str, Any],
    ucp_agent: Optional[str] = Header(None, alias="UCP-Agent"),
    request_signature: Optional[str] = Header(None, alias="request-signature"),
    idempotency_key: Optional[str] = Header(None, alias="idempotency-key"),
    request_id: Optional[str] = Header(None, alias="request-id")
):
    """
    Update checkout session
    """
    validate_request_headers(ucp_agent, request_signature, idempotency_key, request_id)

    session = store.sessions.get(session_id)
    if not session:
        raise HTTPException(status_code=404, detail="Session not found")

    # Update line items if provided
    if "line_items" in update_data:
        line_items = [LineItem(**item) for item in update_data["line_items"]]
        session.line_items = enrich_line_items(line_items)
        session.totals = calculate_totals(session.line_items)

    # Update buyer if provided
    if "buyer" in update_data:
        session.buyer = Buyer(**update_data["buyer"])

    # Update payment if provided
    if "payment" in update_data:
        session.payment = Payment(**update_data["payment"])

    logger.info(f"Updated session {session_id}")

    return session

@app.post("/checkout-sessions/{session_id}/complete")
async def complete_checkout_session(
    session_id: str,
    ucp_agent: Optional[str] = Header(None, alias="UCP-Agent"),
    request_signature: Optional[str] = Header(None, alias="request-signature"),
    idempotency_key: Optional[str] = Header(None, alias="idempotency-key"),
    request_id: Optional[str] = Header(None, alias="request-id")
):
    """
    Complete checkout and create order
    """
    validate_request_headers(ucp_agent, request_signature, idempotency_key, request_id)

    session = store.sessions.get(session_id)
    if not session:
        raise HTTPException(status_code=404, detail="Session not found")

    if session.status == "completed":
        logger.info(f"Session {session_id} already completed")
        return session

    # Create order
    order_id = str(uuid.uuid4())
    order = {
        "id": order_id,
        "session_id": session_id,
        "status": "confirmed",
        "created_at": datetime.now().isoformat(),
        "line_items": [item.dict() for item in session.line_items],
        "buyer": session.buyer.dict() if session.buyer else None,
        "totals": session.totals
    }

    store.orders[order_id] = order

    # Update session
    session.status = "completed"
    session.order_id = order_id
    session.order_permalink_url = f"http://localhost:8000/orders/{order_id}"

    logger.info(f"Completed session {session_id}, created order {order_id}")

    return session

@app.get("/orders/{order_id}")
async def get_order(order_id: str):
    """
    Get order details
    """
    order = store.orders.get(order_id)
    if not order:
        raise HTTPException(status_code=404, detail="Order not found")

    return JSONResponse(content=order)

@app.get("/products")
async def list_products():
    """
    List available products
    """
    return JSONResponse(content={"products": list(store.products.values())})

@app.get("/health")
async def health_check():
    """
    Health check endpoint
    """
    return {"status": "healthy", "version": "2026-01-11"}

# ============================================================================
# Main
# ============================================================================

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=8000)
