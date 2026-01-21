<?php
/**
 * WooCommerce Auto Schema Integration
 *
 * @package Schema Pro
 * @since 1.2.0
 */

if ( ! class_exists( 'BSF_AIOSRS_Pro_WooCommerce' ) ) {

	/**
	 * WooCommerce Auto Schema Class
	 *
	 * @since 1.2.0
	 */
	class BSF_AIOSRS_Pro_WooCommerce {

		/**
		 * Instance
		 *
		 * @var object
		 */
		private static $instance;

		/**
		 * Initiator
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Constructor
		 */
		public function __construct() {
			add_filter( 'wp_schema_pro_schema_product', array( $this, 'auto_fill_product_schema' ), 10, 3 );
			add_action( 'wp_head', array( $this, 'woocommerce_auto_schema' ), 5 );
		}

		/**
		 * Check if WooCommerce is active
		 *
		 * @return bool
		 */
		public static function is_woocommerce_active() {
			return class_exists( 'WooCommerce' );
		}

		/**
		 * Output auto WooCommerce schema on product pages
		 *
		 * @return void
		 */
		public function woocommerce_auto_schema() {
			if ( ! self::is_woocommerce_active() ) {
				return;
			}

			$settings = BSF_AIOSRS_Pro_Admin::get_options();
			$auto_woo = isset( $settings['woocommerce-auto-schema'] ) ? $settings['woocommerce-auto-schema'] : '1';

			if ( '1' !== $auto_woo ) {
				return;
			}

			// Product page schema
			if ( is_product() ) {
				$this->render_product_schema();
			}

			// Category/Archive page schema
			if ( is_product_category() || is_product_tag() || is_shop() ) {
				$this->render_category_schema();
			}
		}

		/**
		 * Render Product Schema
		 *
		 * @return void
		 */
		private function render_product_schema() {
			global $product;

			if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
				$product = wc_get_product( get_the_ID() );
			}

			if ( ! $product ) {
				return;
			}

			$schema = $this->get_product_schema_data( $product );

			if ( ! empty( $schema ) ) {
				echo '<!-- WooCommerce Product Schema by Schema Pro -->';
				echo '<script type="application/ld+json">';
				echo wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
				echo '</script>';
				echo '<!-- / WooCommerce Product Schema -->';
			}
		}

		/**
		 * Get Product Schema Data
		 *
		 * @param WC_Product $product Product object.
		 * @return array
		 */
		public function get_product_schema_data( $product ) {
			$schema = array(
				'@context' => 'https://schema.org',
				'@type'    => 'Product',
			);

			// Name
			$schema['name'] = wp_strip_all_tags( $product->get_name() );

			// Description
			$description = $product->get_short_description();
			if ( empty( $description ) ) {
				$description = $product->get_description();
			}
			if ( ! empty( $description ) ) {
				$schema['description'] = wp_strip_all_tags( $description );
			}

			// SKU
			$sku = $product->get_sku();
			if ( ! empty( $sku ) ) {
				$schema['sku'] = $sku;
			}

			// Image
			$image_id = $product->get_image_id();
			if ( $image_id ) {
				$image_url = wp_get_attachment_url( $image_id );
				if ( $image_url ) {
					$schema['image'] = $image_url;
				}
			}

			// Gallery images
			$gallery_ids = $product->get_gallery_image_ids();
			if ( ! empty( $gallery_ids ) ) {
				$images = array( $schema['image'] ?? '' );
				foreach ( $gallery_ids as $gid ) {
					$gurl = wp_get_attachment_url( $gid );
					if ( $gurl ) {
						$images[] = $gurl;
					}
				}
				$schema['image'] = array_filter( $images );
			}

			// URL
			$schema['url'] = get_permalink( $product->get_id() );

			// Brand
			$brands = wp_get_post_terms( $product->get_id(), 'product_brand', array( 'fields' => 'names' ) );
			if ( ! is_wp_error( $brands ) && ! empty( $brands ) ) {
				$schema['brand'] = array(
					'@type' => 'Brand',
					'name'  => $brands[0],
				);
			}

			// Aggregate Rating
			$rating = $this->get_aggregate_rating( $product );
			if ( ! empty( $rating ) ) {
				$schema['aggregateRating'] = $rating;
			}

			// Offers
			$schema['offers'] = $this->get_offers( $product );

			return apply_filters( 'wp_schema_pro_woocommerce_product_schema', $schema, $product );
		}

		/**
		 * Get Aggregate Rating
		 *
		 * @param WC_Product $product Product object.
		 * @return array
		 */
		private function get_aggregate_rating( $product ) {
			$rating = array();

			// Try KK Star Rating first
			$kk_rating = $this->get_kk_star_rating( $product->get_id() );
			if ( ! empty( $kk_rating ) ) {
				return $kk_rating;
			}

			// Try Global Star Rating from Schema Pro
			$rating_settings = BSF_AIOSRS_Pro_Admin::get_options( 'wp-schema-pro-ratings-settings' );
			if ( '1' == $rating_settings['enable-star-ratings'] ) {
				$score = get_post_meta( $product->get_id(), 'bsf-schema-pro-rating-0', true );
				$count = get_post_meta( $product->get_id(), 'bsf-schema-pro-review-counts-0', true );
				if ( ! empty( $count ) && ! empty( $score ) ) {
					return array(
						'@type'       => 'AggregateRating',
						'ratingValue' => round( floatval( $score ), 1 ),
						'ratingCount' => absint( $count ),
						'bestRating'  => '5',
						'worstRating' => '1',
					);
				}
			}

			// Fallback to WooCommerce reviews
			$review_count = $product->get_review_count();
			$avg_rating   = $product->get_average_rating();

			if ( $review_count > 0 && $avg_rating > 0 ) {
				$rating = array(
					'@type'       => 'AggregateRating',
					'ratingValue' => round( $avg_rating, 1 ),
					'reviewCount' => $review_count,
					'bestRating'  => '5',
					'worstRating' => '1',
				);
			}

			return $rating;
		}

		/**
		 * Get KK Star Rating data
		 *
		 * @param int $post_id Post ID.
		 * @return array
		 */
		private function get_kk_star_rating( $post_id ) {
			// Try various meta keys used by different versions of KK Star Ratings
			$count = get_post_meta( $post_id, '_kksr_casts', true );
			$score = get_post_meta( $post_id, '_kksr_avg', true );

			if ( empty( $count ) || empty( $score ) ) {
				$count = get_post_meta( $post_id, '_kk_star_ratings_counts', true );
				$score = get_post_meta( $post_id, '_kk_star_ratings', true );
			}

			if ( empty( $count ) || empty( $score ) ) {
				$count = get_post_meta( $post_id, '_kk_star_ratings_casts', true );
			}

			if ( empty( $count ) || empty( $score ) ) {
				return array();
			}

			// Calculate average if score is total
			if ( $score > 5 && ! empty( $count ) ) {
				$score = round( $score / $count, 1 );
			}

			return array(
				'@type'       => 'AggregateRating',
				'ratingValue' => round( floatval( $score ), 1 ),
				'ratingCount' => absint( $count ),
				'bestRating'  => '5',
				'worstRating' => '1',
			);
		}

		/**
		 * Get Offers schema
		 *
		 * @param WC_Product $product Product object.
		 * @return array
		 */
		private function get_offers( $product ) {
			$offers = array(
				'@type'         => 'Offer',
				'url'           => get_permalink( $product->get_id() ),
				'priceCurrency' => get_woocommerce_currency(),
				'price'         => $product->get_price(),
				'priceValidUntil' => date( 'Y-m-d', strtotime( '+1 year' ) ),
			);

			// Availability
			if ( $product->is_in_stock() ) {
				$offers['availability'] = 'https://schema.org/InStock';
			} else {
				$offers['availability'] = 'https://schema.org/OutOfStock';
			}

			// Seller
			$general_settings = BSF_AIOSRS_Pro_Admin::get_options( 'wp-schema-pro-general-settings' );
			$site_name = isset( $general_settings['site-name'] ) && ! empty( $general_settings['site-name'] )
				? $general_settings['site-name']
				: get_bloginfo( 'name' );

			$offers['seller'] = array(
				'@type' => 'Organization',
				'name'  => $site_name,
			);

			return $offers;
		}

		/**
		 * Render Category Schema (CollectionPage + ItemList)
		 *
		 * @return void
		 */
		private function render_category_schema() {
			$schema = array();

			// CollectionPage
			$collection = $this->get_collection_page_schema();
			if ( ! empty( $collection ) ) {
				$schema[] = $collection;
			}

			// ItemList
			$item_list = $this->get_item_list_schema();
			if ( ! empty( $item_list ) ) {
				$schema[] = $item_list;
			}

			if ( ! empty( $schema ) ) {
				echo '<!-- WooCommerce Category Schema by Schema Pro -->';
				foreach ( $schema as $s ) {
					echo '<script type="application/ld+json">';
					echo wp_json_encode( $s, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
					echo '</script>';
				}
				echo '<!-- / WooCommerce Category Schema -->';
			}
		}

		/**
		 * Get CollectionPage Schema
		 *
		 * @return array
		 */
		private function get_collection_page_schema() {
			$term = get_queried_object();

			if ( ! $term || ! is_a( $term, 'WP_Term' ) ) {
				return array();
			}

			$schema = array(
				'@context'    => 'https://schema.org',
				'@type'       => 'CollectionPage',
				'name'        => wp_strip_all_tags( $term->name ),
				'url'         => get_term_link( $term ),
			);

			if ( ! empty( $term->description ) ) {
				$schema['description'] = wp_strip_all_tags( $term->description );
			}

			// Get category thumbnail
			$thumbnail_id = get_term_meta( $term->term_id, 'thumbnail_id', true );
			if ( $thumbnail_id ) {
				$image_url = wp_get_attachment_url( $thumbnail_id );
				if ( $image_url ) {
					$schema['image'] = $image_url;
				}
			}

			return apply_filters( 'wp_schema_pro_woocommerce_collection_page', $schema, $term );
		}

		/**
		 * Get ItemList Schema
		 *
		 * @return array
		 */
		private function get_item_list_schema() {
			global $wp_query;

			$products = $wp_query->posts;
			if ( empty( $products ) ) {
				return array();
			}

			$term = get_queried_object();
			$name = is_a( $term, 'WP_Term' ) ? $term->name : __( 'Products', 'wp-schema-pro' );

			$schema = array(
				'@context'        => 'https://schema.org',
				'@type'           => 'ItemList',
				'name'            => wp_strip_all_tags( $name ),
				'itemListElement' => array(),
			);

			foreach ( $products as $index => $post ) {
				$product = wc_get_product( $post->ID );
				if ( ! $product ) {
					continue;
				}

				$item = array(
					'@type'    => 'ListItem',
					'position' => $index + 1,
					'url'      => get_permalink( $post->ID ),
					'name'     => wp_strip_all_tags( $product->get_name() ),
				);

				// Add image
				$image_id = $product->get_image_id();
				if ( $image_id ) {
					$item['image'] = wp_get_attachment_url( $image_id );
				}

				$schema['itemListElement'][] = $item;
			}

			return apply_filters( 'wp_schema_pro_woocommerce_item_list', $schema );
		}

		/**
		 * Auto-fill Product schema with WooCommerce data
		 * Filter hook for existing schema
		 *
		 * @param array $schema Existing schema.
		 * @param array $data Schema data.
		 * @param array $post Post data.
		 * @return array
		 */
		public function auto_fill_product_schema( $schema, $data, $post ) {
			if ( ! self::is_woocommerce_active() ) {
				return $schema;
			}

			$product = wc_get_product( $post['ID'] );
			if ( ! $product ) {
				return $schema;
			}

			// Auto-fill empty fields
			if ( empty( $schema['name'] ) ) {
				$schema['name'] = wp_strip_all_tags( $product->get_name() );
			}

			if ( empty( $schema['description'] ) ) {
				$desc = $product->get_short_description();
				if ( empty( $desc ) ) {
					$desc = $product->get_description();
				}
				$schema['description'] = wp_strip_all_tags( $desc );
			}

			if ( empty( $schema['sku'] ) ) {
				$schema['sku'] = $product->get_sku();
			}

			if ( empty( $schema['image'] ) ) {
				$image_id = $product->get_image_id();
				if ( $image_id ) {
					$schema['image'] = wp_get_attachment_url( $image_id );
				}
			}

			// Auto-fill offers
			if ( ! isset( $schema['offers']['price'] ) || '0' === $schema['offers']['price'] ) {
				$schema['offers']['price']         = $product->get_price();
				$schema['offers']['priceCurrency'] = get_woocommerce_currency();
				$schema['offers']['availability']  = $product->is_in_stock()
					? 'https://schema.org/InStock'
					: 'https://schema.org/OutOfStock';
			}

			// Auto-fill rating
			if ( empty( $schema['aggregateRating'] ) ) {
				$rating = $this->get_aggregate_rating( $product );
				if ( ! empty( $rating ) ) {
					$schema['aggregateRating'] = $rating;
				}
			}

			return $schema;
		}
	}
}

/**
 * Initialize WooCommerce integration
 */
if ( class_exists( 'WooCommerce' ) || defined( 'WC_VERSION' ) ) {
	BSF_AIOSRS_Pro_WooCommerce::get_instance();
} else {
	add_action( 'woocommerce_init', function() {
		BSF_AIOSRS_Pro_WooCommerce::get_instance();
	} );
}
