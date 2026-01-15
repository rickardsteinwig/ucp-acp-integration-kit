/**
 * UCP Service
 *
 * Translates between UCP protocol and Shopify API
 */

import { ShopifyService } from './shopify.service';
import { v4 as uuidv4 } from 'uuid';

interface CheckoutSession {
  [key: string]: any;
}

export class UCPService {
  private shopifyService: ShopifyService;
  private sessions: Map<string, CheckoutSession> = new Map();

  constructor(shopifyService: ShopifyService) {
    this.shopifyService = shopifyService;
  }

  /**
   * Get UCP Profile (Discovery)
   */
  async getUCPProfile(): Promise<any> {
    const shopDomain = process.env.SHOPIFY_SHOP_DOMAIN;

    return {
      ucp: {
        version: '2026-01-11',
        services: {
          'dev.ucp.shopping': {
            version: '2026-01-11',
            spec: 'https://ucp.dev/specs/shopping',
            rest: {
              schema: 'https://ucp.dev/services/shopping/openapi.json',
              endpoint: `https://${shopDomain}/`
            },
            mcp: null,
            a2a: null,
            embedded: null
          }
        },
        capabilities: [
          {
            name: 'dev.ucp.shopping.checkout',
            version: '2026-01-11',
            spec: 'https://ucp.dev/specs/shopping/checkout',
            schema: 'https://ucp.dev/schemas/shopping/checkout.json',
            extends: null,
            config: null
          },
          {
            name: 'dev.ucp.shopping.fulfillment',
            version: '2026-01-11',
            spec: 'https://ucp.dev/specs/shopping/fulfillment',
            schema: 'https://ucp.dev/schemas/shopping/fulfillment.json',
            extends: 'dev.ucp.shopping.checkout',
            config: null
          }
        ]
      },
      payment: {
        handlers: [
          {
            id: 'shop_pay',
            name: 'com.shopify.shop_pay',
            version: '2026-01-11',
            spec: 'https://shopify.dev/ucp/handlers/shop_pay',
            config_schema: 'https://shopify.dev/ucp/handlers/shop_pay/config.json',
            instrument_schemas: [
              'https://shopify.dev/ucp/handlers/shop_pay/instrument.json'
            ],
            config: {
              shop_id: process.env.SHOPIFY_SHOP_ID || 'shop_default'
            }
          }
        ]
      },
      signing_keys: null
    };
  }

  /**
   * Create UCP Checkout Session
   */
  async createCheckoutSession(data: any, idempotencyKey: string): Promise<any> {
    // Check if session already exists with this idempotency key
    for (const [id, session] of this.sessions.entries()) {
      if (session._idempotencyKey === idempotencyKey) {
        return session;
      }
    }

    // Map UCP line items to Shopify format
    const shopifyLineItems = await Promise.all(
      data.line_items.map(async (item: any) => {
        const product = await this.shopifyService.getProduct(item.item.id);
        return {
          variantId: product.variants[0].id,
          quantity: item.quantity
        };
      })
    );

    // Create Shopify checkout
    const shopifyCheckout = await this.shopifyService.createCheckout(shopifyLineItems);

    // Create UCP session
    const sessionId = this.extractId(shopifyCheckout.id);
    const ucpSession = this.mapShopifyToUCP(shopifyCheckout, data);

    ucpSession._idempotencyKey = idempotencyKey;
    ucpSession._shopifyCheckoutId = shopifyCheckout.id;

    // Store session
    this.sessions.set(sessionId, ucpSession);

    return ucpSession;
  }

  /**
   * Get UCP Checkout Session
   */
  async getCheckoutSession(sessionId: string): Promise<any> {
    const session = this.sessions.get(sessionId);

    if (!session) {
      // Try to fetch from Shopify
      try {
        const shopifyCheckout = await this.shopifyService.getCheckout(sessionId);
        if (shopifyCheckout) {
          const ucpSession = this.mapShopifyToUCP(shopifyCheckout, {});
          this.sessions.set(sessionId, ucpSession);
          return ucpSession;
        }
      } catch (error) {
        return null;
      }
    }

    return session;
  }

  /**
   * Update UCP Checkout Session
   */
  async updateCheckoutSession(sessionId: string, updates: any): Promise<any> {
    const session = this.sessions.get(sessionId);

    if (!session) {
      throw new Error('Session not found');
    }

    // Map UCP updates to Shopify format
    const shopifyUpdates: any = {};

    if (updates.buyer?.email) {
      shopifyUpdates.email = updates.buyer.email;
    }

    if (updates.fulfillment_address) {
      shopifyUpdates.shippingAddress = {
        address1: updates.fulfillment_address.street_address,
        city: updates.fulfillment_address.address_locality,
        province: updates.fulfillment_address.address_region,
        zip: updates.fulfillment_address.postal_code,
        country: updates.fulfillment_address.address_country
      };
    }

    // Update in Shopify
    await this.shopifyService.updateCheckout(sessionId, shopifyUpdates);

    // Update local session
    if (updates.buyer) {
      session.buyer = { ...session.buyer, ...updates.buyer };
    }

    return session;
  }

  /**
   * Complete UCP Checkout Session
   */
  async completeCheckoutSession(sessionId: string): Promise<any> {
    const session = this.sessions.get(sessionId);

    if (!session) {
      throw new Error('Session not found');
    }

    if (session.status === 'completed') {
      return session;
    }

    // In Shopify, completion happens via the webUrl
    // For UCP, we mark as ready and provide the completion URL
    session.status = 'completed';
    session.order_id = `order_${sessionId}`;
    session.order_permalink_url = session.continue_url;

    return session;
  }

  /**
   * Map Shopify checkout to UCP format
   */
  private mapShopifyToUCP(shopifyCheckout: any, originalData: any): any {
    const lineItems = shopifyCheckout.lineItems.edges.map((edge: any, index: number) => {
      const item = edge.node;
      const variant = item.variant;

      return {
        id: this.extractId(item.id),
        item: {
          id: this.extractId(variant.id),
          title: item.title,
          price: Math.round(parseFloat(variant.price.amount) * 100),
          image_url: null
        },
        quantity: item.quantity,
        totals: [
          {
            type: 'subtotal',
            amount: Math.round(parseFloat(variant.price.amount) * item.quantity * 100)
          },
          {
            type: 'total',
            amount: Math.round(parseFloat(variant.price.amount) * item.quantity * 100)
          }
        ]
      };
    });

    const subtotal = Math.round(parseFloat(shopifyCheckout.subtotalPrice.amount) * 100);
    const tax = Math.round(parseFloat(shopifyCheckout.totalTax.amount) * 100);
    const total = Math.round(parseFloat(shopifyCheckout.totalPrice.amount) * 100);

    return {
      ucp: {
        version: '2026-01-11',
        capabilities: [
          {
            name: 'dev.ucp.shopping.checkout',
            version: '2026-01-11',
            spec: null,
            schema: null,
            extends: null,
            config: null
          }
        ]
      },
      id: this.extractId(shopifyCheckout.id),
      line_items: lineItems,
      buyer: originalData.buyer || {
        email: shopifyCheckout.email,
        full_name: null
      },
      status: 'ready_for_complete',
      currency: shopifyCheckout.subtotalPrice.currencyCode,
      totals: [
        { type: 'subtotal', display_text: 'Subtotal', amount: subtotal },
        { type: 'tax', display_text: 'Tax', amount: tax },
        { type: 'total', display_text: 'Total', amount: total }
      ],
      messages: null,
      links: [],
      expires_at: new Date(Date.now() + 24 * 60 * 60 * 1000).toISOString(),
      continue_url: shopifyCheckout.webUrl,
      payment: {
        handlers: [],
        selected_instrument_id: null,
        instruments: []
      },
      order_id: null,
      order_permalink_url: null
    };
  }

  /**
   * Extract ID from Shopify Global ID
   */
  private extractId(gid: string): string {
    if (!gid) return '';
    const parts = gid.split('/');
    return parts[parts.length - 1];
  }
}
