/**
 * Shopify UCP Adapter
 *
 * Connects Shopify stores to the Universal Commerce Protocol
 * Uses Shopify's Storefront API to expose UCP endpoints
 */

import express, { Request, Response } from 'express';
import dotenv from 'dotenv';
import { ShopifyService } from './services/shopify.service';
import { UCPService } from './services/ucp.service';

dotenv.config();

const app = express();
app.use(express.json());

// Initialize services
const shopifyService = new ShopifyService({
  shopDomain: process.env.SHOPIFY_SHOP_DOMAIN!,
  storefrontAccessToken: process.env.SHOPIFY_STOREFRONT_ACCESS_TOKEN!,
  adminAccessToken: process.env.SHOPIFY_ADMIN_ACCESS_TOKEN
});

const ucpService = new UCPService(shopifyService);

// ============================================================================
// Middleware
// ============================================================================

const validateUCPHeaders = (req: Request, res: Response, next: Function) => {
  const requiredHeaders = ['idempotency-key', 'request-id'];

  for (const header of requiredHeaders) {
    if (!req.headers[header]) {
      return res.status(400).json({
        error: 'missing_header',
        message: `Missing required header: ${header}`
      });
    }
  }

  next();
};

// ============================================================================
// UCP Discovery Endpoint
// ============================================================================

app.get('/.well-known/ucp', async (req: Request, res: Response) => {
  try {
    const profile = await ucpService.getUCPProfile();
    res.json(profile);
  } catch (error: any) {
    console.error('Error getting UCP profile:', error);
    res.status(500).json({
      error: 'internal_error',
      message: error.message
    });
  }
});

// ============================================================================
// Checkout Session Endpoints
// ============================================================================

app.post('/checkout-sessions', validateUCPHeaders, async (req: Request, res: Response) => {
  try {
    const session = await ucpService.createCheckoutSession(
      req.body,
      req.headers['idempotency-key'] as string
    );
    res.status(201).json(session);
  } catch (error: any) {
    console.error('Error creating checkout session:', error);
    res.status(500).json({
      error: 'internal_error',
      message: error.message
    });
  }
});

app.get('/checkout-sessions/:sessionId', async (req: Request, res: Response) => {
  try {
    const session = await ucpService.getCheckoutSession(req.params.sessionId);

    if (!session) {
      return res.status(404).json({
        error: 'not_found',
        message: 'Checkout session not found'
      });
    }

    res.json(session);
  } catch (error: any) {
    console.error('Error getting checkout session:', error);
    res.status(500).json({
      error: 'internal_error',
      message: error.message
    });
  }
});

app.put('/checkout-sessions/:sessionId', validateUCPHeaders, async (req: Request, res: Response) => {
  try {
    const session = await ucpService.updateCheckoutSession(
      req.params.sessionId,
      req.body
    );

    res.json(session);
  } catch (error: any) {
    console.error('Error updating checkout session:', error);
    res.status(500).json({
      error: 'internal_error',
      message: error.message
    });
  }
});

app.post('/checkout-sessions/:sessionId/complete', validateUCPHeaders, async (req: Request, res: Response) => {
  try {
    const result = await ucpService.completeCheckoutSession(req.params.sessionId);
    res.json(result);
  } catch (error: any) {
    console.error('Error completing checkout session:', error);
    res.status(500).json({
      error: 'internal_error',
      message: error.message
    });
  }
});

// ============================================================================
// Product Endpoints (Helper)
// ============================================================================

app.get('/products', async (req: Request, res: Response) => {
  try {
    const products = await shopifyService.getProducts();
    res.json({ products });
  } catch (error: any) {
    console.error('Error getting products:', error);
    res.status(500).json({
      error: 'internal_error',
      message: error.message
    });
  }
});

app.get('/products/:productId', async (req: Request, res: Response) => {
  try {
    const product = await shopifyService.getProduct(req.params.productId);

    if (!product) {
      return res.status(404).json({
        error: 'not_found',
        message: 'Product not found'
      });
    }

    res.json(product);
  } catch (error: any) {
    console.error('Error getting product:', error);
    res.status(500).json({
      error: 'internal_error',
      message: error.message
    });
  }
});

// ============================================================================
// Health Check
// ============================================================================

app.get('/health', (req: Request, res: Response) => {
  res.json({
    status: 'healthy',
    version: '2026-01-11',
    shopify_connected: !!process.env.SHOPIFY_SHOP_DOMAIN
  });
});

// ============================================================================
// Start Server
// ============================================================================

const PORT = process.env.PORT || 3000;

app.listen(PORT, () => {
  console.log(`🚀 Shopify UCP Adapter running on port ${PORT}`);
  console.log(`📦 Shop: ${process.env.SHOPIFY_SHOP_DOMAIN}`);
  console.log(`🔍 Discovery: http://localhost:${PORT}/.well-known/ucp`);
});

export default app;
