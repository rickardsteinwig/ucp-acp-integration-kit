<?php
/**
 * UCP Products Handler
 *
 * Manages product listings for UCP
 *
 * @package WooCommerce_UCP
 */

if (!defined('ABSPATH')) {
    exit;
}

class WC_UCP_Products {
    /**
     * Singleton instance
     */
    private static $instance = null;

    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get products
     */
    public function get_products($params = array()) {
        $args = array(
            'status' => 'publish',
            'limit' => isset($params['limit']) ? intval($params['limit']) : 10,
            'offset' => isset($params['offset']) ? intval($params['offset']) : 0
        );

        $products = wc_get_products($args);
        $formatted_products = array();

        foreach ($products as $product) {
            $formatted_products[] = $this->format_product($product);
        }

        return $formatted_products;
    }

    /**
     * Get single product
     */
    public function get_product($product_id) {
        $product = wc_get_product($product_id);

        if (!$product) {
            return null;
        }

        return $this->format_product($product);
    }

    /**
     * Format product for UCP
     */
    private function format_product($product) {
        $price = (int) ($product->get_price() * 100); // Convert to cents

        return array(
            'id' => (string) $product->get_id(),
            'title' => $product->get_name(),
            'description' => $product->get_description(),
            'short_description' => $product->get_short_description(),
            'price' => $price,
            'currency' => get_woocommerce_currency(),
            'image_url' => wp_get_attachment_url($product->get_image_id()),
            'available' => $product->is_in_stock(),
            'stock_quantity' => $product->get_stock_quantity(),
            'sku' => $product->get_sku(),
            'permalink' => get_permalink($product->get_id()),
            'categories' => $this->get_product_categories($product),
            'attributes' => $this->get_product_attributes($product)
        );
    }

    /**
     * Get product categories
     */
    private function get_product_categories($product) {
        $categories = array();
        $terms = get_the_terms($product->get_id(), 'product_cat');

        if ($terms && !is_wp_error($terms)) {
            foreach ($terms as $term) {
                $categories[] = array(
                    'id' => $term->term_id,
                    'name' => $term->name,
                    'slug' => $term->slug
                );
            }
        }

        return $categories;
    }

    /**
     * Get product attributes
     */
    private function get_product_attributes($product) {
        $attributes = array();

        foreach ($product->get_attributes() as $attribute) {
            if ($attribute->is_taxonomy()) {
                $terms = wp_get_post_terms($product->get_id(), $attribute->get_name());
                $values = array();

                foreach ($terms as $term) {
                    $values[] = $term->name;
                }

                $attributes[] = array(
                    'name' => wc_attribute_label($attribute->get_name()),
                    'values' => $values
                );
            } else {
                $attributes[] = array(
                    'name' => $attribute->get_name(),
                    'values' => $attribute->get_options()
                );
            }
        }

        return $attributes;
    }
}
