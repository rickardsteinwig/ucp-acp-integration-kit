/**
 * Shopify Service
 *
 * Handles all interactions with Shopify Storefront and Admin APIs
 */

import axios, { AxiosInstance } from 'axios';

interface ShopifyConfig {
  shopDomain: string;
  storefrontAccessToken: string;
  adminAccessToken?: string;
}

export class ShopifyService {
  private storefrontClient: AxiosInstance;
  private adminClient?: AxiosInstance;
  private config: ShopifyConfig;

  constructor(config: ShopifyConfig) {
    this.config = config;

    // Storefront API client
    this.storefrontClient = axios.create({
      baseURL: `https://${config.shopDomain}/api/2024-01/graphql.json`,
      headers: {
        'Content-Type': 'application/json',
        'X-Shopify-Storefront-Access-Token': config.storefrontAccessToken
      }
    });

    // Admin API client (optional)
    if (config.adminAccessToken) {
      this.adminClient = axios.create({
        baseURL: `https://${config.shopDomain}/admin/api/2024-01`,
        headers: {
          'Content-Type': 'application/json',
          'X-Shopify-Access-Token': config.adminAccessToken
        }
      });
    }
  }

  /**
   * Get products from Shopify
   */
  async getProducts(limit: number = 10): Promise<any[]> {
    const query = `
      query GetProducts($first: Int!) {
        products(first: $first) {
          edges {
            node {
              id
              title
              description
              variants(first: 1) {
                edges {
                  node {
                    id
                    price {
                      amount
                      currencyCode
                    }
                    availableForSale
                  }
                }
              }
              images(first: 1) {
                edges {
                  node {
                    url
                  }
                }
              }
            }
          }
        }
      }
    `;

    try {
      const response = await this.storefrontClient.post('', {
        query,
        variables: { first: limit }
      });

      return response.data.data.products.edges.map((edge: any) => {
        const product = edge.node;
        const variant = product.variants.edges[0]?.node;
        const image = product.images.edges[0]?.node;

        return {
          id: this.extractId(product.id),
          title: product.title,
          description: product.description,
          price: variant ? Math.round(parseFloat(variant.price.amount) * 100) : 0,
          currency: variant?.price.currencyCode || 'USD',
          image_url: image?.url,
          available: variant?.availableForSale || false,
          variant_id: this.extractId(variant?.id)
        };
      });
    } catch (error) {
      console.error('Error fetching products from Shopify:', error);
      throw error;
    }
  }

  /**
   * Get a single product by ID
   */
  async getProduct(productId: string): Promise<any> {
    const gid = this.toGlobalId('Product', productId);

    const query = `
      query GetProduct($id: ID!) {
        product(id: $id) {
          id
          title
          description
          variants(first: 10) {
            edges {
              node {
                id
                title
                price {
                  amount
                  currencyCode
                }
                availableForSale
              }
            }
          }
          images(first: 5) {
            edges {
              node {
                url
              }
            }
          }
        }
      }
    `;

    try {
      const response = await this.storefrontClient.post('', {
        query,
        variables: { id: gid }
      });

      const product = response.data.data.product;
      if (!product) return null;

      return {
        id: this.extractId(product.id),
        title: product.title,
        description: product.description,
        variants: product.variants.edges.map((edge: any) => ({
          id: this.extractId(edge.node.id),
          title: edge.node.title,
          price: Math.round(parseFloat(edge.node.price.amount) * 100),
          currency: edge.node.price.currencyCode,
          available: edge.node.availableForSale
        })),
        images: product.images.edges.map((edge: any) => edge.node.url)
      };
    } catch (error) {
      console.error('Error fetching product from Shopify:', error);
      throw error;
    }
  }

  /**
   * Create a Shopify checkout
   */
  async createCheckout(lineItems: any[]): Promise<any> {
    const mutation = `
      mutation CreateCheckout($input: CheckoutCreateInput!) {
        checkoutCreate(input: $input) {
          checkout {
            id
            webUrl
            lineItems(first: 10) {
              edges {
                node {
                  id
                  title
                  quantity
                  variant {
                    id
                    title
                    price {
                      amount
                      currencyCode
                    }
                  }
                }
              }
            }
            subtotalPrice {
              amount
              currencyCode
            }
            totalTax {
              amount
              currencyCode
            }
            totalPrice {
              amount
              currencyCode
            }
          }
          checkoutUserErrors {
            field
            message
          }
        }
      }
    `;

    const input = {
      lineItems: lineItems.map(item => ({
        variantId: this.toGlobalId('ProductVariant', item.variantId),
        quantity: item.quantity
      }))
    };

    try {
      const response = await this.storefrontClient.post('', {
        query: mutation,
        variables: { input }
      });

      if (response.data.data.checkoutCreate.checkoutUserErrors.length > 0) {
        throw new Error(
          response.data.data.checkoutCreate.checkoutUserErrors[0].message
        );
      }

      return response.data.data.checkoutCreate.checkout;
    } catch (error) {
      console.error('Error creating checkout in Shopify:', error);
      throw error;
    }
  }

  /**
   * Update Shopify checkout
   */
  async updateCheckout(checkoutId: string, updates: any): Promise<any> {
    const gid = this.toGlobalId('Checkout', checkoutId);

    // Update email if provided
    if (updates.email) {
      const mutation = `
        mutation UpdateCheckoutEmail($checkoutId: ID!, $email: String!) {
          checkoutEmailUpdateV2(checkoutId: $checkoutId, email: $email) {
            checkout {
              id
              email
            }
            checkoutUserErrors {
              field
              message
            }
          }
        }
      `;

      await this.storefrontClient.post('', {
        query: mutation,
        variables: { checkoutId: gid, email: updates.email }
      });
    }

    // Update shipping address if provided
    if (updates.shippingAddress) {
      const mutation = `
        mutation UpdateShippingAddress($checkoutId: ID!, $shippingAddress: MailingAddressInput!) {
          checkoutShippingAddressUpdateV2(checkoutId: $checkoutId, shippingAddress: $shippingAddress) {
            checkout {
              id
            }
            checkoutUserErrors {
              field
              message
            }
          }
        }
      `;

      await this.storefrontClient.post('', {
        query: mutation,
        variables: { checkoutId: gid, shippingAddress: updates.shippingAddress }
      });
    }

    return this.getCheckout(checkoutId);
  }

  /**
   * Get checkout by ID
   */
  async getCheckout(checkoutId: string): Promise<any> {
    const gid = this.toGlobalId('Checkout', checkoutId);

    const query = `
      query GetCheckout($id: ID!) {
        node(id: $id) {
          ... on Checkout {
            id
            webUrl
            email
            lineItems(first: 10) {
              edges {
                node {
                  id
                  title
                  quantity
                  variant {
                    id
                    title
                    price {
                      amount
                      currencyCode
                    }
                  }
                }
              }
            }
            subtotalPrice {
              amount
              currencyCode
            }
            totalTax {
              amount
              currencyCode
            }
            totalPrice {
              amount
              currencyCode
            }
          }
        }
      }
    `;

    try {
      const response = await this.storefrontClient.post('', {
        query,
        variables: { id: gid }
      });

      return response.data.data.node;
    } catch (error) {
      console.error('Error fetching checkout from Shopify:', error);
      throw error;
    }
  }

  /**
   * Helper: Convert local ID to Shopify Global ID
   */
  private toGlobalId(type: string, id: string): string {
    if (id.startsWith('gid://')) {
      return id;
    }
    return `gid://shopify/${type}/${id}`;
  }

  /**
   * Helper: Extract numeric ID from Shopify Global ID
   */
  private extractId(gid: string): string {
    if (!gid) return '';
    const parts = gid.split('/');
    return parts[parts.length - 1];
  }
}
